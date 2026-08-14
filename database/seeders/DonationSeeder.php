<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Donation;
use App\Models\User;

class DonationSeeder extends Seeder
{
    public function run(): void
    {
        // جلب جميع الـ IDs المتاحة للمستخدمين
        $userIds = User::pluck('id')->toArray();

        // إذا لم يوجد أي مستخدم في قاعدة البيانات، نستخدم null لمنع حدوث Crash
        if (empty($userIds)) {
            $this->command->warn('⚠️ No users found in database. Setting user_id to null for donations.');
        }

        // دالة مساعدة لاختيار user_id موجود بالفعل أو null
        $getRandomUserId = function () use ($userIds) {
            return !empty($userIds) ? $userIds[array_rand($userIds)] : null;
        };

        Donation::insert([
            [
                'user_id' => $getRandomUserId(),
                'amount' => 500000,
                'currency' => 'SYP',
                'gateway_type' => 'syriatel_cash',
                'transaction_number' => 'STC-2026-001',
                'receipt_image_path' => '/storage/donation_receipts/d1.jpg',
                'status' => 'verified',
                'rejection_reason' => null,
                'is_anonymous' => false,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(9),
            ],
            [
                'user_id' => $getRandomUserId(),
                'amount' => 250000,
                'currency' => 'SYP',
                'gateway_type' => 'mtn_cash',
                'transaction_number' => 'MTN-2026-002',
                'receipt_image_path' => '/storage/donation_receipts/d2.jpg',
                'status' => 'pending',
                'rejection_reason' => null,
                'is_anonymous' => false,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_id' => $getRandomUserId(),
                'amount' => 100,
                'currency' => 'USD',
                'gateway_type' => 'paypal',
                'transaction_number' => 'PAYPAL-2026-003',
                'receipt_image_path' => '/storage/donation_receipts/d3.jpg',
                'status' => 'verified',
                'rejection_reason' => null,
                'is_anonymous' => false,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(6),
            ],
            [
                'user_id' => $getRandomUserId(),
                'amount' => 50,
                'currency' => 'USD',
                'gateway_type' => 'western_union',
                'transaction_number' => 'WU-2026-004',
                'receipt_image_path' => '/storage/donation_receipts/d4.jpg',
                'status' => 'pending',
                'rejection_reason' => null,
                'is_anonymous' => false,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => $getRandomUserId(),
                'amount' => 100000,
                'currency' => 'SYP',
                'gateway_type' => 'al_haram',
                'transaction_number' => 'HARAM-2026-005',
                'receipt_image_path' => '/storage/donation_receipts/d5.jpg',
                'status' => 'rejected',
                'rejection_reason' => 'رقم الحوالة غير صحيح',
                'is_anonymous' => false,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => $getRandomUserId(),
                'amount' => 75000,
                'currency' => 'SYP',
                'gateway_type' => 'hand_delivery',
                'transaction_number' => null,
                'receipt_image_path' => '/storage/donation_receipts/d6.jpg',
                'status' => 'verified',
                'rejection_reason' => null,
                'is_anonymous' => true,
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(14),
            ],
            [
                'user_id' => $getRandomUserId(),
                'amount' => 200,
                'currency' => 'USD',
                'gateway_type' => 'external',
                'transaction_number' => 'EXT-2026-007',
                'receipt_image_path' => '/storage/donation_receipts/d7.jpg',
                'status' => 'verified',
                'rejection_reason' => null,
                'is_anonymous' => true,
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(19),
            ],
        ]);
    }
}