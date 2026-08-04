<?php

namespace App\Jobs;

use App\Models\RescueReport;
use App\Models\User;
use App\Support\NotificationTemplates;
use App\Events\SendNotificationEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastReportToAllVolunteers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $report;

    public function __construct(RescueReport $report)
    {
        $this->report = $report;
    }

    public function handle(): void
    {
        // إعادة جلب البلاغ للتأكد من حالته الحالية
        $report = RescueReport::find($this->report->id);

        // إذا تم قبول البلاغ أو لم يعد بحالة 'reported'، نتوقف عن البث
        if (!$report || $report->status !== 'reported' || $report->volunteer_id !== null) {
            Log::info("متابعة البلاغ رقم {$this->report->id}: البلاغ تم قبوله أو معالجته، لن يتم الإرسال الشامل.");
            return;
        }

        Log::info("🚨 البلاغ رقم {$report->id} لم يُقبل. بدء الإرسال الشامل لجميع المتطوعين دون التقيّد بالمسافة...");

        $severity = $report->severity_level;

        // جلب كافّة المتطوعين بغض النظر عن موقعهم الجغرافي
        $allVolunteers = User::role('volunteer')->get();

        // تصفية المتطوعين بناءً على مستوى الخبرة المطلوب فقط
        $targetVolunteers = $allVolunteers->filter(function ($user) use ($severity) {
            $volunteer = $user->volunteer;
            if (!$volunteer || !$volunteer->is_approved) {
                return false;
            }

            if ($severity === 'critical') {
                return $volunteer->experience_level === 'advanced';
            }

            if ($severity === 'urgent') {
                return in_array($volunteer->experience_level, ['intermediate', 'advanced']);
            }

            return true; 
        });

        if ($targetVolunteers->isEmpty()) {
            Log::info("البلاغ رقم {$report->id}: لا يوجد متطوعين يتوافقون مع مستوى الخبرة المطلوب للإرسال الشامل.");
            return;
        }

        // 🔕 إرسال الإشعارات الشاملة لكل المتطوعين المناسبين (معلقة للتجربة)
        /*
        $template = NotificationTemplates::newRescueReport($report);

        foreach ($targetVolunteers as $volunteer) {
            SendNotificationEvent::dispatch(
                $volunteer,
                $template['title'],
                $template['body'],
                $template['data']
            );
        }
        */

        Log::info("تم تنفيذ الإرسال الشامل لـ {$targetVolunteers->count()} متطوع للبلاغ رقم {$report->id}.");
    }
}