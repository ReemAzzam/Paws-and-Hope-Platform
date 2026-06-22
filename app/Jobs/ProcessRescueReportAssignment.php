<?php

namespace App\Jobs;

use App\Models\RescueReport;
use App\Models\Volunteer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRescueReportAssignment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $report;

    public function __construct(RescueReport $report)
    {
        $this->report = $report;
    }

    public function handle(): void
    {
        $reportLat = $this->report->latitude;
        $reportLng = $this->report->longitude;
        $severity  = $this->report->severity_level; // normal, urgent, critical
        
        $radiusInMeters = 5000; // نطاق البحث: 5 كيلومترات

        $nearbyVolunteers = Volunteer::with('user')
            ->where('is_approved', true) // متطوع معتمد
            ->whereRaw("ST_Distance_Sphere(point(current_longitude, current_latitude), point(?, ?)) <= ?", [
                $reportLng,
                $reportLat,
                $radiusInMeters
            ])->get();

        if ($nearbyVolunteers->isEmpty()) {
            Log::info("البلاغ رقم {$this->report->id}: لا يوجد متطوعين في المحيط الجغرافي حالياً.");
            return;
        }

        $targetVolunteers = $nearbyVolunteers->filter(function ($volunteer) use ($severity) {

            if ($severity === 'critical') {
                return $volunteer->experience_level === 'advanced';
            }

            if ($severity === 'urgent') {
                return in_array($volunteer->experience_level, ['intermediate', 'advanced']);
            }

            return true; 
        });

        // ... داخل دالة handle() بعد تصفية المتطوعين ($targetVolunteers)

        Log::info("=== بدء معالجة البلاغ رقم: {$this->report->id} ===");
        Log::info("خطورة البلاغ الحالي: {$severity}");
        Log::info("عدد المتطوعين المتواجدين جغرافياً في المحيط: " . $nearbyVolunteers->count());
        Log::info("عدد المتطوعين الذين تطابقوا مع مستوى الخطورة: " . $targetVolunteers->count());

        foreach ($targetVolunteers as $volunteer) {
            Log::info("🚨 تم تحديد المتطوع المناسب للإشعار: ", [
                'volunteer_id' => $volunteer->id,
                'name' => $volunteer->user->full_name ?? 'بدون اسم',
                'experience_level' => $volunteer->experience_level
            ]);
        }
        Log::info("=== نهاية معالجة البلاغ ===");

        if ($targetVolunteers->isEmpty()) {
            Log::info("البلاغ رقم {$this->report->id}: يوجد متطوعين قريبيين، ولكن لا أحد يطابق مستوى الخبرة المطلوب لخطورة الحالة.");
            return;
        }

        foreach ($targetVolunteers as $volunteer) {
            $user = $volunteer->user;

            if ($user && $user->fcm_token) {
                $this->sendFcmNotification($user->fcm_token, $this->report);
            }

        }
    }


    private function sendFcmNotification($fcmToken, $report)
    {
        Log::info("تم إرسال إشعار FCM للمتطوع ذو التوكن: {$fcmToken} عن بلاغ نوع: {$report->animal_type}");
    }
}