<?php

namespace Database\Seeders;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdoptionApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role('regular_user')->take(10)->get();
        $animals = Animal::where('availability_status', 'available')->take(20)->get();

        if ($users->isEmpty() || $animals->isEmpty()) {
            $this->command->warn('No enough users or animals found. Seed them first.');
            return;
        }

        $statuses = ['pending', 'pending', 'pending', 'approved', 'rejected', 'in_trial'];

        $detailsList = [
            "--- Adoption Application Form ---\n" .
            "• Reason for adoption: I want to provide a safe and loving home for a pet.\n" .
            "• Other pets at home? No (Details: None)\n" .
            "• Housing type: apartment\n" .
            "• Has garden/yard? No\n" .
            "• Family members count: 3\n" .
            "• Children under 10? No\n" .
            "• Work schedule: Work from home most days\n" .
            "• Experience with animals: 2 years of previous cat care experience\n" .
            "• Emergency contact: John Smith (0999999999)",

            "--- Adoption Application Form ---\n" .
            "• Reason for adoption: Our family is ready to care for a medium-sized dog.\n" .
            "• Other pets at home? Yes (Details: One 1-year-old cat)\n" .
            "• Housing type: house\n" .
            "• Has garden/yard? Yes\n" .
            "• Family members count: 5\n" .
            "• Children under 10? Yes\n" .
            "• Work schedule: Office hours 9 to 5\n" .
            "• Experience with animals: Previously raised dogs and follow regular vet checkups\n" .
            "• Emergency contact: Sarah Brown (0988888888)",

            "--- Adoption Application Form ---\n" .
            "• Reason for adoption: Looking for a calm companion suitable for apartment living.\n" .
            "• Other pets at home? No (Details: None)\n" .
            "• Housing type: villa\n" .
            "• Has garden/yard? Yes\n" .
            "• Family members count: 2\n" .
            "• Children under 10? No\n" .
            "• Work schedule: Flexible / freelance\n" .
            "• Experience with animals: Basic experience with rabbits and birds\n" .
            "• Emergency contact: Karim Hasan (0977777777)",

            "--- Adoption Application Form ---\n" .
            "• Reason for adoption: I can offer full-time attention and a stable environment.\n" .
            "• Other pets at home? Yes (Details: One small dog)\n" .
            "• Housing type: house\n" .
            "• Has garden/yard? Yes\n" .
            "• Family members count: 4\n" .
            "• Children under 10? No\n" .
            "• Work schedule: Hybrid work schedule\n" .
            "• Experience with animals: More than 3 years with pets at home\n" .
            "• Emergency contact: Emily Davis (0966666666)",
        ];

        $created = 0;

        for ($i = 0; $i < 12; $i++) {
            $user = $users[$i % $users->count()];
            $animal = $animals[$i % $animals->count()];

            $exists = AdoptionApplication::where('user_id', $user->id)
                ->where('animal_id', $animal->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $status = $statuses[$i % count($statuses)];

            AdoptionApplication::create([
                'user_id'             => $user->id,
                'animal_id'           => $animal->id,
                'application_details' => $detailsList[$i % count($detailsList)],
                'status'              => $status,
                'approved_at'         => in_array($status, ['approved', 'in_trial']) ? now()->subDays(rand(1, 7)) : null,
                'created_at'          => now()->subDays(rand(0, 15)),
                'updated_at'          => now()->subDays(rand(0, 5)),
            ]);

            $created++;
        }

        $this->command->info("Created {$created} adoption applications for testing");
    }
}
