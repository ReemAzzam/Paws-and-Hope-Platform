<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sponsorship;
use App\Models\User;
use App\Models\Animal;

class SponsorshipSeeder extends Seeder
{
    public function run(): void
    {
        // جلب المستخدمين بحساب regular_user حصراً
        $regularUserIds = User::role('regular_user', 'api')->pluck('id')->toArray();

        if (empty($regularUserIds)) {
            $this->command->warn('⚠️ No regular users found with role "regular_user". Falling back to all users.');
            $regularUserIds = User::pluck('id')->toArray();
        }

        $animalIds = Animal::pluck('id')->toArray();

        if (empty($regularUserIds) || empty($animalIds)) {
            $this->command->warn('⚠️ Skipping SponsorshipSeeder: No users or animals found.');
            return;
        }

        $statuses = ['pending', 'active', 'cancelled', 'paused'];
        $currencies = ['SYP', 'USD'];

        $sponsorships = [];

        for ($i = 1; $i <= 25; $i++) {
            $currency = $currencies[array_rand($currencies)];
            $status = $statuses[array_rand($statuses)];
            $amount = $currency === 'USD' ? rand(15, 100) : rand(50, 300) * 1000;

            $startDate = in_array($status, ['active', 'paused', 'cancelled']) 
                ? now()->subMonths(rand(1, 12))->toDateString() 
                : null;

            $nextPaymentDue = ($status === 'active' || $status === 'paused') 
                ? now()->addDays(rand(1, 30))->toDateString() 
                : null;

            $sponsorships[] = [
                'user_id'          => $regularUserIds[array_rand($regularUserIds)],
                'animal_id'        => $animalIds[array_rand($animalIds)],
                'monthly_amount'   => $amount,
                'currency'         => $currency,
                'status'           => $status,
                'start_date'       => $startDate,
                'next_payment_due' => $nextPaymentDue,
                'payment_due_date' => $nextPaymentDue,
                'notes'            => "Sponsorship record #{$i}",
                'created_at'       => now()->subMonths(rand(1, 6)),
                'updated_at'       => now(),
            ];
        }

        Sponsorship::insert($sponsorships);
    }
}