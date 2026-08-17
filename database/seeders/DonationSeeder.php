<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch IDs of users with the 'regular_user' role only
        $regularUserIds = User::role('regular_user', 'api')->pluck('id')->toArray();

        if (empty($regularUserIds)) {
            $this->command->warn('⚠️ No regular users found with role "regular_user". Falling back to all users.');
            $regularUserIds = User::pluck('id')->toArray();
        }

        $getRandomUserId = function () use ($regularUserIds) {
            return !empty($regularUserIds) ? $regularUserIds[array_rand($regularUserIds)] : null;
        };

        // 2. Ensure default receipt image exists in public storage
        $defaultReceiptImage = $this->ensureReceiptImageExists();

        $donationTypes = [
            'food_and_feeding',
            'surgery_and_neutering',
            'emergency_treatment',
            'general_donation',
            'transport_and_rescue',
            'shelter_and_housing',
        ];

        $gatewayTypes = [
            'al_haram',
            'al_fouad',
            'syriatel_cash',
            'mtn_cash',
            'western_union',
            'paypal',
            'gofundme',
            'hand_delivery',
            'external',
        ];

        $statuses = ['verified', 'pending', 'rejected'];

        $donations = [];

        // 3. Dynamically generate 40 donation records
        for ($i = 1; $i <= 40; $i++) {
            $currency = $i % 3 === 0 ? 'USD' : 'SYP';
            $gateway  = $gatewayTypes[array_rand($gatewayTypes)];
            $status   = $statuses[$i % 3];

            // رقم المعاملة بحيث لا يتجاوز 12 حرفاً ليتوافق مع max:12
            $transactionNo = $gateway === 'hand_delivery' 
                ? null 
                : 'TXN' . str_pad((string)$i, 3, '0', STR_PAD_LEFT) . rand(1000, 9999);

            $donations[] = [
                'user_id'            => $getRandomUserId(),
                'amount'             => $currency === 'USD' ? rand(10, 300) : rand(25, 500) * 1000,
                'currency'           => $currency,
                'donation_type'      => $donationTypes[array_rand($donationTypes)],
                'gateway_type'       => $gateway,
                'transaction_number' => $transactionNo,
                'receipt_image_path' => $defaultReceiptImage,
                'status'             => $status,
                'rejection_reason'   => $status === 'rejected' ? 'Invalid transaction number or receipt image is unclear.' : null,
                'is_anonymous'       => (bool)($i % 5 === 0),
                'created_at'         => now()->subDays(rand(1, 30)),
                'updated_at'         => now()->subDays(rand(0, 29)),
            ];
        }

        Donation::insert($donations);

        $this->command->info('✅ Successfully seeded 40 donations associated exclusively with regular users.');
    }

    /**
     * Copy the default receipt image to public storage if available.
     */
    private function ensureReceiptImageExists(): string
    {
        $relativePath = 'donation_receipt/receipt.jpeg';
        
        // المسار في مجلد assets الخاص بالـ Seeders
        $sourcePath = database_path('seeders/assets/donation_receipt/receipt.jpeg');

        if (File::exists($sourcePath)) {
            Storage::disk('public')->put($relativePath, File::get($sourcePath));
        } else {
            // محاولة البحث عن امتداد .jpg في حال لم يوجد .jpeg
            $altSourcePath = database_path('seeders/assets/donation_receipt/receipt.jpg');
            if (File::exists($altSourcePath)) {
                Storage::disk('public')->put($relativePath, File::get($altSourcePath));
            }
        }

        return $relativePath;
    }
}