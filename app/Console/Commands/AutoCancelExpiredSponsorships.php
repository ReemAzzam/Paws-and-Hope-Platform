<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sponsorship;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCancelExpiredSponsorships extends Command
{
    /**
     * اسم الأمر الذي سيتم استدعاؤه
     *
     * @var string
     */
    protected $signature = 'sponsorships:auto-cancel-expired';

    /**
     * وصف بسيط للأمر
     *
     * @var string
     */
    protected $description = 'Cancel pending sponsorship requests that exceeded the 5-day payment deadline.';

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        // 1. البحث عن الكفالات المنتظرة (pending) التي انتهت مهلة دفعها ولم يتم رفع إيصال لها
        $expiredSponsorships = Sponsorship::where('status', 'pending')
            ->whereNotNull('payment_due_date')
            ->where('payment_due_date', '<', Carbon::now())
            ->whereDoesntHave('payments', function ($query) {
                // التأكد من أنه لم يقم برفع أي إيصال بانتظار المراجعة أو مقبول
                $query->whereIn('verification_status', ['pending', 'verified']);
            })
            ->get();

        if ($expiredSponsorships->isEmpty()) {
            $this->info('No expired sponsorship requests found.');
            return 0;
        }

        $count = 0;

        // 2. تحديث حالة كل كفالة إلى cancelled
        foreach ($expiredSponsorships as $sponsorship) {
            $sponsorship->update([
                'status' => 'cancelled',
            ]);
            $count++;
        }

        $message = "Successfully cancelled {$count} expired sponsorship request(s).";

        $this->info($message);
        Log::info("[Cron Job] " . $message);

        return 0;
    }
}