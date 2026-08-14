<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Models\RescueReport;
use App\Models\RescueReportImage;
use App\Events\ReportStatusUpdated;
use App\Events\VolunteerLocationUpdated;
use App\Jobs\ProcessRescueReportAssignment;
use App\Jobs\BroadcastReportToAllVolunteers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Support\NotificationTemplates;
use App\Events\SendNotificationEvent;
use App\Models\User;

class RescueReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'latitude'         => 'required|numeric|between:-90,90',
            'longitude'        => 'required|numeric|between:-180,180',
            'location_address' => 'required|string|max:255',
            'severity_level'   => 'required|in:normal,urgent,critical',
            'animal_type'      => 'required|string|max:50',
            'health_status'    => 'required|in:bleeding,fracture,poisoning,other',
            'description'      => 'nullable|string|max:1000',
            'images'           => 'required|array|min:1',
            'images.*'         => 'image|mimes:jpeg,png,jpg|max:15360',
        ]);

        DB::beginTransaction();

        try {
            $report = RescueReport::create([
                'user_id'          => auth()->id(),
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'location_address' => $request->location_address,
                'severity_level'   => $request->severity_level,
                'animal_type'      => $request->animal_type,
                'health_status'    => $request->health_status,
                'description'      => $request->description,
                'status'           => 'reported',
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('rescue_reports', 'public');

                    RescueReportImage::create([
                        'rescue_report_id' => $report->id,
                        'image_path'       => Storage::url($path),
                    ]);
                }
            }

            $template = NotificationTemplates::newRescueReport($report);

            // المتطوعون ضمن 5 كم
            $nearbyVolunteers = User::role('volunteer')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->filter(function ($volunteer) use ($report) {

                    return $this->calculateDistance(
                        $report->latitude,
                        $report->longitude,
                        $volunteer->latitude,
                        $volunteer->longitude
                    ) <= 5;
                });

            foreach ($nearbyVolunteers as $volunteer) {

                SendNotificationEvent::dispatch(
                    $volunteer,
                    $template['title'],
                    $template['body'],
                    $template['data']
                );
            }
            DB::commit();

            ProcessRescueReportAssignment::dispatch($report);

            BroadcastReportToAllVolunteers::dispatch($report)->delay(now()->addMinutes(5));

            return response()->json([
                'success' => true,
                'message' => 'Emergency report registered successfully. Rescuers are being dispatched.',
                'data'    => $report->load('images')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Emergency Report Blueprint Failure: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the report, please try again.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function track($id)
    {
        $report = RescueReport::with(['images', 'volunteer.user'])->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'This rescue report does not exist in the system.'
            ], 404);
        }

        $statusTimeline = [
            'reported'   => true,
            'dispatched' => in_array($report->status, ['dispatched', 'on_site', 'in_clinic', 'resolved']),
            'on_site'    => in_array($report->status, ['on_site', 'in_clinic', 'resolved']),
            'in_clinic'  => in_array($report->status, ['in_clinic', 'resolved']),
            'resolved'   => $report->status === 'resolved',
        ];

        $reportData = $report->toArray();

        if ($report->volunteer) {
            $reportData['live_location'] = [
                'latitude'  => $report->volunteer->current_latitude,
                'longitude' => $report->volunteer->current_longitude,
            ];
        } else {
            $reportData['live_location'] = null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'report'          => $reportData,
                'status_timeline' => $statusTimeline
            ]
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:reported,dispatched,on_site,in_clinic,resolved'
        ]);

        $report = RescueReport::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'The report does not exist.'
            ], 404);
        }

        $user = auth()->user();
        $volunteerProfile = $user->volunteer;

        if ($report->volunteer_id && (!$volunteerProfile || $report->volunteer_id !== $volunteerProfile->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you do not have permission to update the status of this report because you are not the assigned volunteer.'
            ], 403);
        }

        $oldStatus = $report->status;
        $report->status = $request->status;

        if ($request->status === 'dispatched' && !$report->volunteer_id) {
            if (!$volunteerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, you do not have an active volunteer profile.'
                ], 403);
            }
            $report->volunteer_id = $volunteerProfile->id;
        }

        DB::beginTransaction();
        try {
            $report->save();
            $template = NotificationTemplates::rescueStatusUpdated(
                    $report,
                    $request->status
                );

                SendNotificationEvent::dispatch(
                    $report->user,
                    $template['title'],
                    $template['body'],
                    $template['data']
                );
            

            if ($request->status === 'resolved' && $oldStatus !== 'resolved') {

                $mappedType = strtolower($report->animal_type);
                $allowedTypes = ['dog', 'cat', 'bird', 'rabbit', 'other'];
                if (!in_array($mappedType, $allowedTypes)) {
                    $mappedType = 'other';
                }

                $reportHealth = strtolower($report->health_status);
                $allowedHealth = ['healthy', 'sick', 'injured', 'critical', 'recovering'];

                $finalHealthStatus = in_array($reportHealth, $allowedHealth)
                                     ? $reportHealth
                                     : 'injured';

                $mainImage = $report->images()->first();

                \App\Models\Animal::create([
                    'name'                => 'Animal_' . $report->id,
                    'type'                => $mappedType,
                    'health_status'       => $finalHealthStatus,
                    'availability_status' => 'under_treatment',
                    'description'         => $report->description,
                    'story'               => "Successfully rescued via emergency report. Field status details: " . $report->description,
                    'image_path'          => $mainImage ? $mainImage->image_path : null,
                    'rescue_report_id'    => $report->id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to resolve rescue report or create animal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving changes.',
                'error'   => $e->getMessage()
            ], 500);
        }

        broadcast(new ReportStatusUpdated($report))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Report status updated successfully, and the case has been transferred to the organization.',
            'data'    => $report->load(['images', 'volunteer.user'])
        ], 200);
    }

    public function acceptReport($id)
    {
        $user = auth()->user();
        $volunteerProfile = $user->volunteer;

        if (!$volunteerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, no active volunteer profile was found for this account.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $report = RescueReport::lockForUpdate()->find($id);

            if (!$report) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, the report does not exist.'
                ], 404);
            }

            if ($report->status !== 'reported') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, this report has already been accepted by another volunteer and is being processed.'
                ], 400);
            }

            $report->update([
                'status'       => 'dispatched',
                'volunteer_id' => $volunteerProfile->id,
            ]);

            if ($report->user) {
                $template = NotificationTemplates::rescueAccepted($report);
                SendNotificationEvent::dispatch(
                    $report->user,
                    $template['title'],
                    $template['body'],
                    $template['data']
                );
            }

            DB::commit();

            broadcast(new ReportStatusUpdated($report))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Task accepted successfully, you are now on your way to rescue the case.',
                'data'    => [
                    'report_id' => $report->id,
                    'status'    => $report->status,
                    'assigned_volunteer' => [
                        'volunteer_profile_id' => $volunteerProfile->id,
                        'full_name'            => $user->full_name
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the report acceptance, please try again.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function cancelAcceptance(Request $request, $id)
    {
        $user = auth()->user();
        $volunteerProfile = $user->volunteer;

        if (!$volunteerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا يوجد ملف متطوع نشط لهذا الحساب.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $report = RescueReport::lockForUpdate()->find($id);

            if (!$report) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، البلاغ غير موجود.'
                ], 404);
            }

            if ($report->volunteer_id !== $volunteerProfile->id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، لا يمكنك إلغاء هذا البلاغ لأنك لست المتطوع الموكل به.'
                ], 403);
            }

            if ($report->status !== 'dispatched') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء قبول البلاغ في هذه المرحلة.'
                ], 400);
            }

            $report->update([
                'status'       => 'reported',
                'volunteer_id' => null,
            ]);

            
            $template = NotificationTemplates::newRescueReport($report);

            // المتطوعون ضمن 5 كم
            $nearbyVolunteers = User::role('volunteer')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->filter(function ($volunteer) use ($report) {

                    return $this->calculateDistance(
                        $report->latitude,
                        $report->longitude,
                        $volunteer->latitude,
                        $volunteer->longitude
                    ) <= 5;
                });

            foreach ($nearbyVolunteers as $volunteer) {

                SendNotificationEvent::dispatch(
                    $volunteer,
                    $template['title'],
                    $template['body'],
                    $template['data']
                );
            }


            DB::commit();

            ProcessRescueReportAssignment::dispatch($report);
            BroadcastReportToAllVolunteers::dispatch($report)->delay(now()->addMinutes(5));

            broadcast(new ReportStatusUpdated($report))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء قبول البلاغ بنجاح وإعادة إتاحته للمتطوعين الآخرين.',
                'data'    => [
                    'report_id' => $report->id,
                    'status'    => $report->status,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء إلغاء قبول البلاغ: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء قبول البلاغ، يرجى المحاولة لاحقاً.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updateVolunteerLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_latitude'  => 'required|numeric|between:-90,90',
            'current_longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $volunteerProfile = $user->volunteer;

        if (!$volunteerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you do not have a volunteer account to update its location.'
            ], 403);
        }

        $updated = DB::table('volunteers')
            ->where('user_id', $user->id)
            ->update([
                'current_latitude'  => $request->current_latitude,
                'current_longitude' => $request->current_longitude,
                'updated_at'        => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location, please check account permissions.'
            ], 400);
        }

        $activeReport = RescueReport::where('volunteer_id', $volunteerProfile->id)
            ->whereIn('status', ['dispatched', 'on_site', 'in_clinic'])
            ->first();

        if ($activeReport) {
            broadcast(new VolunteerLocationUpdated(
                $activeReport->id,
                $request->current_latitude,
                $request->current_longitude
            ))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Your field location has been successfully updated.',
            'current_location' => [
                'latitude'  => $request->current_latitude,
                'longitude' => $request->current_longitude,
            ]
        ], 200);
    }

    public function availableReports(Request $request)
    {
        $user = Auth::user();


        $volunteer = Volunteer::where('user_id', $user->id)
            ->where('is_approved', true)
            ->first();

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، هذه الواجهة مخصصة للمتطوعين المعتمدين فقط.'
            ], 403);
        }

        $allReportedReports = RescueReport::where('status', 'reported')
            ->with('images')
            ->orderBy('created_at', 'desc')
            ->get();

        $filteredReports = $allReportedReports->filter(function ($report) use ($volunteer) {
            $severity = $report->severity_level;
            $expLevel = $volunteer->experience_level;

            if ($severity === 'critical') {
                return $expLevel === 'advanced';
            }

            if ($severity === 'urgent') {
                return in_array($expLevel, ['intermediate', 'advanced']);
            }

            return true; 
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البلاغات المتاحة المتوافقة مع مستوى خبرتك بنجاح.',
            'count'   => $filteredReports->count(),
            'data'    => $filteredReports->values() 
        ], 200);
    }

    // My rescue reports
    public function myRescueReports(Request $request)
    {
        $reports = RescueReport::where('user_id', $request->user()->id)
            ->with(['images', 'volunteer.user:id,full_name'])
            ->latest()
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'animal_type' => ucfirst($report->animal_type),
                    'severity' => ucfirst($report->severity_level),
                    'status' => ucfirst(str_replace('_', ' ', $report->status)),
                    'location' => $report->location_address,
                    'reported_at' => $report->created_at->format('M d, Y'),
                    'volunteer' => $report->volunteer?->user?->full_name,
                    'images' => $report->images->pluck('image_path')->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }


    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Returns distance in KM
    }

    public function assignedReports(Request $request)
    {
        $user = Auth::user();

        $volunteer = Volunteer::where('user_id', $user->id)
            ->where('is_approved', true)
            ->first();

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، هذه الواجهة مخصصة للمتطوعين المعتمدين فقط.'
            ], 403);
        }

        $assignedReports = RescueReport::where('volunteer_id', $volunteer->id)
            ->whereIn('status', ['dispatched', 'on_site', 'in_clinic'])
            ->with([
                'images',
                'user:id,full_name,phone_number' 
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البلاغات الموكلة إليك بنجاح.',
            'count'   => $assignedReports->count(),
            'data'    => $assignedReports
        ], 200);
    }

    public function rescueHistory(Request $request)
    {
        $user = Auth::user();

        $volunteer = Volunteer::where('user_id', $user->id)
            ->where('is_approved', true)
            ->first();

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، هذه الواجهة مخصصة للمتطوعين المعتمدين فقط.'
            ], 403);
        }

        $history = RescueReport::where('volunteer_id', $volunteer->id)
            ->where('status', 'resolved')
            ->with(['images'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $formattedData = $history->map(function ($report) {
            return [
                'id'               => $report->id,
                'rescued_at'       => $report->updated_at, 
                'latitude'         => $report->latitude,
                'longitude'        => $report->longitude,
                'location_address' => $report->location_address, // الموقع
                'status'           => $report->status,           // الحالة (resolved)
                'animal_type'      => $report->animal_type,      // نوع الحيوان/الإنقاذ
                'severity_level'   => $report->severity_level,   // درجة الخطورة
                'health_status'    => $report->health_status,    // الحالة الصحية
                'description'      => $report->description,      // وصف الحالة
                'images'           => $report->images            // صور الحالة
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب سجل الحالات التي تم إنقاذها بنجاح.',
            'count'   => $formattedData->count(),
            'data'    => $formattedData
        ], 200);
    }

    public function volunteerStats(Request $request)
    {
        $user = Auth::user();

        $volunteer = Volunteer::where('user_id', $user->id)
            ->where('is_approved', true)
            ->first();

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، هذه الواجهة مخصصة للمتطوعين المعتمدين فقط.'
            ], 403);
        }

        $availableReportsCount = RescueReport::where('status', 'reported')->count();

        $activeMissionsCount = RescueReport::where('volunteer_id', $volunteer->id)
            ->whereIn('status', ['dispatched', 'on_site', 'in_clinic'])
            ->count();

        $completedRescueReportsCount = RescueReport::where('volunteer_id', $volunteer->id)
            ->where('status', 'resolved')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب إحصائيات المتطوع بنجاح.',
            'data'    => [
                'available_reports'        => $availableReportsCount,
                'active_missions'          => $activeMissionsCount,
                'completed_rescue_reports' => $completedRescueReportsCount,
            ]
        ], 200);
    }

<<<<<<< Updated upstream
        public function activeRescueReportsCount()
    {
        $count = RescueReport::whereIn('status', [
            'dispatched',
            'on_site',
            'in_clinic'
        ])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'active_rescue_reports' => $count
            ]
=======
    public function getUserRescueReports($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $reports = RescueReport::where('user_id', $userId)
            ->with(['images', 'volunteer.user:id,full_name'])
            ->latest()
            ->get()
            ->map(function ($report) {
                return [
                    'id'          => $report->id,
                    'animal_type' => ucfirst($report->animal_type),
                    'severity'    => ucfirst($report->severity_level),
                    'status'      => ucfirst(str_replace('_', ' ', $report->status)),
                    'location'    => $report->location_address,
                    'reported_at' => $report->created_at->format('M d, Y'),
                    'volunteer'   => $report->volunteer?->user?->full_name,
                    'images'      => $report->images->pluck('image_path')->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $reports->count(),
            'data'    => $reports
>>>>>>> Stashed changes
        ], 200);
    }
}
