<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SponsorshipPayment;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SponsorshipPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $sponsorships = Sponsorship::all();
        $adminIds = User::role('SuperAdmin', 'api')->pluck('id')->toArray();

        if (empty($adminIds)) {
            $adminIds = User::pluck('id')->toArray();
        }

        if ($sponsorships->isEmpty()) {
            $this->command->warn('⚠️ Skipping SponsorshipPaymentSeeder: No sponsorships found.');
            return;
        }

        $defaultReceiptPath = $this->ensureReceiptImageExists();

        $paymentMethods = [
            'al_haram', 'al_fouad', 'syriatel_cash', 
            'mtn_cash', 'western_union', 'paypal', 
            'gofundme', 'hand_delivery', 'external'
        ];

        $verificationStatuses = ['pending', 'verified', 'rejected'];

        $rejectionReasons = [
            'Payment receipt image is unclear or unreadable.',
            'The payment amount does not match the required monthly sponsorship fee.',
            'Transaction number could not be verified with the provider.',
            'Incomplete payment details attached to the receipt.',
        ];

        $payments = [];

        for ($i = 1; $i <= 40; $i++) {
            /** @var Sponsorship $sponsorship */
            $sponsorship = $sponsorships->random();
            $status = $verificationStatuses[array_rand($verificationStatuses)];

            $isVerified = $status === 'verified';
            $isRejected = $status === 'rejected';

            $adminId = ($isVerified || $isRejected) && !empty($adminIds) 
                ? $adminIds[array_rand($adminIds)] 
                : null;

            $payments[] = [
                'sponsorship_id'      => $sponsorship->id,
                // المطابقة التامة مع الكفالة المرتبطة
                'amount'              => $sponsorship->monthly_amount,
                'currency'            => $sponsorship->currency,
                'payment_method'      => $paymentMethods[array_rand($paymentMethods)],
                'transaction_number'  => 'TXN-SP-' . sprintf('%04d', $i) . '-' . rand(100, 999),
                'receipt_image_url'   => $defaultReceiptPath,
                'verification_status' => $status,
                'verified_by'         => $adminId,
                'verified_at'         => ($isVerified || $isRejected) ? now()->subDays(rand(1, 15)) : null,
                // سبب الرفض باللغة الإنكليزية
                'rejection_reason'    => $isRejected ? $rejectionReasons[array_rand($rejectionReasons)] : null,
                'created_at'          => now()->subDays(rand(1, 60)),
                'updated_at'          => now()->subDays(rand(0, 10)),
            ];
        }

        SponsorshipPayment::insert($payments);
    }

    private function ensureReceiptImageExists(): string
    {
        $relativePath = 'sponsorship_receipts/receipt.jpeg';
        $sourcePath = database_path('seeders/assets/sponsorship_receipts/receipt.jpeg');

        if (File::exists($sourcePath)) {
            Storage::disk('public')->put($relativePath, File::get($sourcePath));
        } else {
            $altSourcePath = database_path('seeders/assets/donation_receipt/receipt.jpeg');
            if (File::exists($altSourcePath)) {
                Storage::disk('public')->put($relativePath, File::get($altSourcePath));
            }
        }

        return $relativePath;
    }
}