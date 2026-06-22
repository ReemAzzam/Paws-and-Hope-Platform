<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackupRequest;
use App\Models\RescueReport;
use App\Models\Volunteer;
use App\Events\EmergencyBackupRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $currentVolunteer = Volunteer::where('user_id', $user->id)->where('is_approved', true)->first();
        if (!$currentVolunteer) {
            return response()->json(['success' => false, 'message' => 'عذراً، هذا الإجراء مخصص للمتطوعين المعتمدين فقط.'], 403);
        }

        $request->validate([
            'rescue_report_id' => 'required|exists:rescue_reports,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'reason' => 'nullable|string',
        ]);

        $backupRequest = BackupRequest::create([
            'rescue_report_id' => $request->rescue_report_id,
            'volunteer_id' => $currentVolunteer->id, // المتطوع المستغيث
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $radiusInMeters = 5000; // نطاق 5 كيلومتر

        $nearbyVolunteers = Volunteer::with('user')
            ->where('is_approved', true)
            ->where('id', '!=', $currentVolunteer->id) // استثناء المتطوع الذي طلب المساعدة
            ->whereRaw("ST_Distance_Sphere(point(current_longitude, current_latitude), point(?, ?)) <= ?", [
                $request->longitude,
                $request->latitude,
                $radiusInMeters
            ])->get();

        Log::info("🔍 فحص الاستغاثة الميدانية - عدد المتطوعين القريبين جغرافياً الذين تم العثور عليهم: " . $nearbyVolunteers->count());

        foreach ($nearbyVolunteers as $volunteer) {
            $nearbyUser = $volunteer->user;

            if ($nearbyUser) {
                $this->sendBackupNotification($nearbyUser->fcm_token ?? 'no_token_yet', $currentVolunteer, $request->reason);
            }
        }

        broadcast(new EmergencyBackupRequested($backupRequest))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال نداء الاستغاثة بنجاح وإشعار المتطوعين القريبين منك في المحيط الجغرافي.',
            'data' => $backupRequest
        ], 201);
    }

    private function sendBackupNotification($fcmToken, $senderVolunteer, $reason)
    {
        $senderName = $senderVolunteer->user->full_name ?? 'متطوع زميل';
        Log::info("🚨 إشعار استغاثة ميداني: تم إرسال إشعار FCM إلى التوكن ({$fcmToken}). المحتوى: المتطوع ({$senderName}) يحتاج إلى دعم عاجل! السبب: {$reason}");
    }

    public function acceptBackup($id)
    {
        $backupRequest = BackupRequest::find($id);

        if (!$backupRequest) {
            return response()->json([
                'success' => false,
                'message' => 'طلب الدعم هذا غير موجود.'
            ], 404);
        }

        if ($backupRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'تمت تلبية طلب الدعم هذا بالفعل أو تم إلغاؤه.'
            ], 400);
        }

        $user = Auth::user();
        $volunteerProfile = $user->volunteer; // جلب ملف المتطوع الحالي

        $backupRequest->update([
            'status' => 'responded',
            'accepted_volunteer_id' => $volunteerProfile->id // إذا كان الحقل موجوداً بالـ Migration
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم قبول تلبية نداء الاستغاثة، جاري توجيهك لموقع زميلك.',
            'data'    => $backupRequest
        ], 200);
    }


    public function getAvailableBackupRequests(Request $request)
    {
        $user = Auth::user();

        // 1. التأكد أن المستخدم متطوع معتمد
        $volunteer = Volunteer::where('user_id', $user->id)->where('is_approved', true)->first();
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
                'message' => 'يرجى تحديث موقعك الجغرافي أولاً لرؤية نداءات الاستغاثة القريبة.'
            ], 400);
        }

        $backupRequests = BackupRequest::with('volunteer.user', 'rescueReport')
            ->where('status', 'pending')
            ->where('volunteer_id', '!=', $volunteer->id) 
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [
                $volLng,
                $volLat,
                $radiusInMeters
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب نداءات الاستغاثة القريبة والمفتوحة بنجاح.',
            'count' => $backupRequests->count(),
            'data' => $backupRequests
        ], 200);
    }
}