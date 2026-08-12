<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SponsorshipPayment;

class SponsorshipPaymentSeeder extends Seeder
{
    public function run(): void
    {
        SponsorshipPayment::insert([
            [
                'sponsorship_id' => 1,
                'amount' => 25,
                'currency' => 'USD',
                'payment_method' => 'paypal',
                'transaction_number' => 'PAY-SP-001',
                'receipt_image_url' => '/storage/sponsorship_receipts/s1.jpg',
                'verification_status' => 'verified',
                'verified_by' => 1,
                'verified_at' => now()->subDays(2),
                'rejection_reason' => null,
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(2),
            ],
            [
                'sponsorship_id' => 1,
                'amount' => 25,
                'currency' => 'USD',
                'payment_method' => 'paypal',
                'transaction_number' => 'PAY-SP-002',
                'receipt_image_url' => '/storage/sponsorship_receipts/s2.png',
                'verification_status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'sponsorship_id' => 2,
                'amount' => 150000,
                'currency' => 'SYP',
                'payment_method' => 'syriatel_cash',
                'transaction_number' => 'STC-SP-003',
                'receipt_image_url' => '/storage/sponsorship_receipts/s3.png',
                'verification_status' => 'verified',
                'verified_by' => 1,
                'verified_at' => now()->subDays(5),
                'rejection_reason' => null,
                'created_at' => now()->subDays(35),
                'updated_at' => now()->subDays(5),
            ],
            [
                'sponsorship_id' => 2,
                'amount' => 150000,
                'currency' => 'SYP',
                'payment_method' => 'mtn_cash',
                'transaction_number' => 'MTN-SP-004',
                'receipt_image_url' => '/storage/sponsorship_receipts/s4.jpg',
                'verification_status' => 'rejected',
                'verified_by' => 1,
                'verified_at' => now()->subDays(3),
                'rejection_reason' => 'إيصال الدفع غير واضح.',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(3),
            ],
            [
                'sponsorship_id' => 3,
                'amount' => 30,
                'currency' => 'USD',
                'payment_method' => 'western_union',
                'transaction_number' => 'WU-SP-005',
                'receipt_image_url' => '/storage/sponsorship_receipts/s5.png',
                'verification_status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ]);
    }
}
