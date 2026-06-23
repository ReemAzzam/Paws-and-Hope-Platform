<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sponsorship;
use App\Models\Animal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelOverdueSponsorships extends Command
{
    /**
     * الاسم البرمجي للأمر الذي سنشغله في الـ Terminal
     */
    protected $signature = 'sponsorships:cancel-overdue';

    /**
     * وصف الأمر
     */
    protected $description = 'إلغاء الكفالات تلقائياً في حال تأخر الكفيل عن الدفع لأكثر من 45 يوماً';

    /**
     * منطق المعالجة والتنفيذ
     */
    public function handle()
    {
        $this->info('بدء فحص الكفالات المتأخرة...');

        // حساب تاريخ المهلة (اليوم ناقص 45 يوماً)
        // أي كفالة تاريخ استحقاقها أصغر من أو يساوي هذا التاريخ تعتبر ملغاة
        $deadline = Carbon::now()->subDays(45)->toDateString();

        // جلب الكفالات النشطة التي تجاوزت مهلة الـ 45 يوماً
        $overdueSponsorships = Sponsorship::where('status', 'active')
            ->where('next_payment_due', '<=', $deadline)
            ->get();

        if ($overdueSponsorships->isEmpty()) {
            $this->info('لا توجد أي كفالات متأخرة حالياً.');
            return 0;
        }

        $count = 0;

        foreach ($overdueSponsorships as $sponsorship) {
            DB::beginTransaction();
            try {
                // 1. تحديث حالة عقد الكفالة إلى ملغي
                $sponsorship->update([
                    'status' => 'cancelled',
                    'notes' => $sponsorship->notes . "\n[تم إلغاء الكفالة تلقائياً لتخلف الكفيل عن الدفع لمدة 45 يوماً بتاريخ " . now()->toDateString() . "]"
                ]);

                // 2. تحديث حالة الحيوان ليعود متاحاً للكفالة من جديد
                if ($sponsorship->animal) {
                    $sponsorship->animal->update([
                        'availability_status' => 'available'
                    ]);
                }

                DB::commit();
                $count++;

                Log::info("تم إلغاء الكfالة رقم {$sponsorship->id} المرتبطة بالحيوان رقم {$sponsorship->animal_id} تلقائياً.");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("خطأ أثناء إلغاء الكفالة رقم {$sponsorship->id}: " . $e->getMessage());
            }
        }

        $this->info("تم بنجاح إلغاء ({$count}) كفالة متأخرة وتحديث حالة الحيوانات المرتبطة بها.");
        return 0;
    }
}