<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneralConsultation;
use App\Models\User;
use App\Models\Veterinarian;

class GeneralConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // جلب المستخدمين الذين يملكون دور regular_user
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'regular_user');
        })->get();

        if ($users->isEmpty()) {
            $this->command->warn('Skip seeding consultations: No regular users found.');
            return;
        }

        // جلب الأطباء البيطريين المعتمدين
        $vets = Veterinarian::where('is_approved', true)->get();

        // قائمة بأسئلة وأجوبة بيطرية
        $sampleConsultations = [
            [
                'question' => 'My cat has lost her appetite and has been lethargic for two days. What could be the cause and what should I do?',
                'answer'   => 'Loss of appetite in cats can indicate a fever or gastrointestinal inflammation. Try offering soft, highly palatable food. If complete anorexia lasts more than 24 hours, a veterinary examination is required.',
                'status'   => 'answered',
            ],
            [
                'question' => 'What core vaccinations are required for a 3-month-old puppy, and what is the recommended schedule?',
                'answer'   => 'At 3 months, puppies typically need the DHPP combination vaccine (distemper, hepatitis, parvovirus, parainfluenza) and a Rabies vaccine. A booster shot is usually given 3-4 weeks later.',
                'status'   => 'answered',
            ],
            [
                'question' => 'I noticed severe hair loss and white crusts around my rabbit\'s ears. Is this ear mites or a fungal infection?',
                'answer'   => 'These symptoms strongly suggest ear mites (Psoroptes cuniculi) or ringworm. Avoid forcefully removing the crusts, keep the habitat clean, and apply anti-parasitic drops under veterinary guidance.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My small dog accidentally ate a piece of dark chocolate an hour ago. Is chocolate toxic to dogs?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'What is the best way to clean a cat\'s teeth at home to prevent tartar buildup and bad breath?',
                'answer'   => null,
                'status'   => 'pending',
            ],
        ];

        foreach ($sampleConsultations as $index => $data) {
            // ربط الاستشارة بمستخدم عادي بشكل تتابعي
            $user = $users[$index % $users->count()];

            $vetId = null;
            if ($data['status'] === 'answered' && $vets->isNotEmpty()) {
                $vetId = $vets->random()->id;
            }

            GeneralConsultation::create([
                'user_id'         => $user->id,
                'veterinarian_id' => $vetId,
                'question'        => $data['question'],
                'answer'          => $data['answer'],
                'status'          => $data['status'],
                'created_at'      => now()->subDays(rand(1, 10)),
                'updated_at'      => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info('✅ GeneralConsultationSeeder executed successfully!');
    }
}