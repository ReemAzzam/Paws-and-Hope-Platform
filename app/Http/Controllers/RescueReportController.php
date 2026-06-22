<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Models\RescueReport;
use App\Models\RescueReportImage;
use App\Events\ReportStatusUpdated;
use App\Events\VolunteerLocationUpdated;
use App\Jobs\ProcessRescueReportAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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

            DB::commit();

            ProcessRescueReportAssignment::dispatch($report);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل بلاغ الطوارئ بنجاح، وجاري توجيه المنقذين.',
                'data'    => $report->load('images')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Emergency Report Blueprint Failure: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة البلاغ، يرجى المحاولة مجدداً.',
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
                'message' => 'بلاغ الإنقاذ هذا غير موجود في النظام.'
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
                'message' => 'البلاغ غير موجود.'
            ], 404);
        }

        $user = auth()->user();
        $volunteerProfile = $user->volunteer;

        if ($report->volunteer_id && (!$volunteerProfile || $report->volunteer_id !== $volunteerProfile->id)) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا تمتلك الصلاحية لتحديث حالة هذا البلاغ لأنك لست المتطوع المسؤول عنه.'
            ], 403);
        }

        $oldStatus = $report->status;
        $report->status = $request->status;

        if ($request->status === 'dispatched' && !$report->volunteer_id) {
            if (!$volunteerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، لا تمتلك ملف متطوع نشط.'
                ], 403);
            }
            $report->volunteer_id = $volunteerProfile->id; 
        }

        DB::beginTransaction();
        try {
            $report->save();

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
                    'availability_status' => 'under_treatment', // تم تعديل اسم الحقل ليطابق الميجريشن الحالي      
                    'description'         => $report->description,
                    'story'               => "تم إنقاذه بنجاح عبر بلاغ طوارئ. تفاصيل الحالة الميدانية: " . $report->description,
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
                'message' => 'حدث خطأ أثناء حفظ التغييرات.',
                'error'   => $e->getMessage()
            ], 500);
        }

        broadcast(new ReportStatusUpdated($report))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة البلاغ بنجاح، وتحويل الحالة إلى المنظمة.',
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
                'message' => 'عذراً، لم يتم العثور على ملف متطوع نشط لهذا الحساب.'
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
            if ($report->status !== 'reported') {
                DB::rollBack(); 
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، تم استلام هذا البلاغ بالفعل من قبل متطوع آخر وهو قيد المعالجة.'
                ], 400);
            }

            $report->update([
                'status'       => 'dispatched',
                'volunteer_id' => $volunteerProfile->id,
            ]);

            DB::commit();

            broadcast(new ReportStatusUpdated($report))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'تم قبول المهمة بنجاح، أنت الآن في طريقك لإنقاذ الحالة.',
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
                'message' => 'حدث خطأ ما أثناء معالجة قبول البلاغ، يرجى المحاولة مجدداً.',
                "error" => $e->getMessage()
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
                'message' => 'عذراً، لا تمتلك حساب متطوع لتحديث موقعه.'
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
                'message' => 'فشل تحديث الموقع، يرجى التأكد من صلاحيات الحساب.'
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
            'message' => 'تم تحديث موقعك الميداني بنجاح.',
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
                'message' => 'عذراً، هذا المسار مخصص للمتطوعين المعتمدين فقط.'
            ], 403);
        }

        $volLat = $volunteer->current_latitude;
        $volLng = $volunteer->current_longitude;
        $radiusInMeters = 5000; // 5 كيلومتر

        if (!$volLat || !$volLng) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى تحديث موقعك الجغرافي (GPS) أولاً لعرض البلاغات القريبة.'
            ], 400);
        }

        $nearbyReports = RescueReport::where('status', 'reported')
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [
                $volLng,
                $volLat,
                $radiusInMeters
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $filteredReports = $nearbyReports->filter(function ($report) use ($volunteer) {
            $severity = $report->severity_level;

            if ($severity === 'critical') {
                return $volunteer->experience_level === 'advanced';
            }

            if ($severity === 'urgent') {
                return in_array($volunteer->experience_level, ['intermediate', 'advanced']);
            }

            return true; // الحالات العادية مبروحة للجميع
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البلاغات المتاحة المتطابقة مع موقعك وخبرتك بنجاح.',
            'count' => $filteredReports->count(),
            'data' => $filteredReports->values() // values() لإعادة ترقيم المصفوفة بعد الفلترة
        ], 200);
    }
}