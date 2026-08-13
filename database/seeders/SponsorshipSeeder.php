<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sponsorship;

class SponsorshipSeeder extends Seeder
{
    public function run(): void
    {
        Sponsorship::insert([
            [
                'user_id' => 7,
                'animal_id' => 1,
                'monthly_amount' => 25,
                'currency' => 'USD',
                'status' => 'active',
                'start_date' => now()->subMonths(3)->toDateString(),
                'next_payment_due' => now()->addDays(10)->toDateString(),
                'notes' => 'Monthly sponsorship for Max.',
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8,
                'animal_id' => 2,
                'monthly_amount' => 150000,
                'currency' => 'SYP',
                'status' => 'active',
                'start_date' => now()->subMonths(2)->toDateString(),
                'next_payment_due' => now()->addDays(5)->toDateString(),
                'notes' => 'Regular monthly sponsorship.',
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ],
            [
                'user_id' => 9,
                'animal_id' => 3,
                'monthly_amount' => 30,
                'currency' => 'USD',
                'status' => 'pending',
                'start_date' => null,
                'next_payment_due' => null,
                'notes' => 'Waiting for sponsorship approval.',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => 9,
                'animal_id' => 4,
                'monthly_amount' => 200000,
                'currency' => 'SYP',
                'status' => 'paused',
                'start_date' => now()->subMonths(5)->toDateString(),
                'next_payment_due' => now()->addDays(20)->toDateString(),
                'notes' => 'Sponsorship temporarily paused.',
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subDays(10),
            ],
            [
                'user_id' => 8,
                'animal_id' => 5,
                'monthly_amount' => 40,
                'currency' => 'USD',
                'status' => 'cancelled',
                'start_date' => now()->subMonths(6)->toDateString(),
                'next_payment_due' => null,
                'notes' => 'Sponsorship cancelled by sponsor.',
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonth(),
            ],
        ]);
    }
}
