<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnimalMedicalCondition;
use App\Models\Animal;

class AnimalMedicalConditionSeeder extends Seeder
{
    public function run(): void
    {
        $animalIds = Animal::pluck('id')->toArray();

        if (count($animalIds) < 50) {
            $this->command->error('❌ You need at least 50 animals to generate medical conditions.');
            return;
        }

        // Pick 50 random animals
        $selectedAnimals = collect($animalIds)->shuffle()->take(50)->toArray();

        // Possible conditions
        $conditionsList = [
            [
                'condition' => 'Ear Infection',
                'treatment' => 'Antibiotic ear drops + cleaning',
                'notes'     => 'Responding well to treatment.'
            ],
            [
                'condition' => 'Skin Allergy',
                'treatment' => 'Antihistamines + hypoallergenic diet',
                'notes'     => 'Allergy triggered by food.'
            ],
            [
                'condition' => 'Gastroenteritis',
                'treatment' => 'Probiotics + soft diet',
                'notes'     => 'Mild inflammation due to spoiled food.'
            ],
            [
                'condition' => 'Minor Wound',
                'treatment' => 'Cleaning + antibiotic ointment',
                'notes'     => 'Superficial wound from outdoor activity.'
            ],
            [
                'condition' => 'Fleas Infestation',
                'treatment' => 'Flea shampoo + environmental cleaning',
                'notes'     => 'Preventive treatment recommended.'
            ],
            [
                'condition' => 'Ringworm',
                'treatment' => 'Antifungal cream',
                'notes'     => 'Skin fungus, improving gradually.'
            ],
            [
                'condition' => 'Dental Tartar',
                'treatment' => 'Dental cleaning',
                'notes'     => 'Requires regular dental checkups.'
            ],
            [
                'condition' => 'Respiratory Infection',
                'treatment' => 'Antibiotics + steam therapy',
                'notes'     => 'Mild nasal discharge observed.'
            ],
            [
                'condition' => 'Broken Nail',
                'treatment' => 'Bandage + pain relief',
                'notes'     => 'Injury caused during running.'
            ],
            [
                'condition' => 'Hot Spot (Skin Inflammation)',
                'treatment' => 'Topical cream + cone collar',
                'notes'     => 'Acute skin irritation.'
            ],
            [
                'condition' => 'Vitamin Deficiency',
                'treatment' => 'Multivitamin supplements',
                'notes'     => 'Low vitamin D levels detected.'
            ],
            [
                'condition' => 'Arthritis',
                'treatment' => 'Joint supplements + pain management',
                'notes'     => 'Age-related chronic condition.'
            ],
            [
                'condition' => 'Diarrhea',
                'treatment' => 'Diet adjustment + hydration',
                'notes'     => 'Likely caused by sudden food change.'
            ],
            [
                'condition' => 'Abscess',
                'treatment' => 'Drainage + antibiotics',
                'notes'     => 'Caused by a fight with another animal.'
            ],
            [
                'condition' => 'Constipation',
                'treatment' => 'Laxatives + fiber-rich diet',
                'notes'     => 'Common in older animals.'
            ],
        ];

        $records = [];

        foreach ($selectedAnimals as $animalId) {
            // Each animal gets 1–3 conditions
            $numConditions = rand(1, 3);

            $chosenConditions = collect($conditionsList)->shuffle()->take($numConditions);

            foreach ($chosenConditions as $cond) {
                $start = now()->subDays(rand(10, 120))->format('Y-m-d');
                $end   = rand(0, 1) ? now()->subDays(rand(1, 9))->format('Y-m-d') : null;

                $records[] = [
                    'animal_id'   => $animalId,
                    'condition'   => $cond['condition'],
                    'treatment'   => $cond['treatment'],
                    'start_date'  => $start,
                    'end_date'    => $end,
                    'notes'       => $cond['notes'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        AnimalMedicalCondition::insert($records);

        $this->command->info('✅ 50 animals assigned realistic medical conditions successfully!');
    }
}
