<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vaccination;

class VaccinationSeeder extends Seeder
{
    public function run(): void
    {
        $vaccinations = [
            [
                'animal_id' => 1,
                'vaccine_name' => 'Rabies',
                'vaccine_type' => 'Core',
                'vaccination_date' => '2026-01-10',
                'notes' => 'Annual rabies vaccination.'
            ],
            [
                'animal_id' => 1,
                'vaccine_name' => 'DHPP',
                'vaccine_type' => 'Core',
                'vaccination_date' => '2026-01-10',
                'notes' => 'Protects against Distemper, Hepatitis, Parvovirus and Parainfluenza.'
            ],
            [
                'animal_id' => 2,
                'vaccine_name' => 'Bordetella',
                'vaccine_type' => 'Non-Core',
                'vaccination_date' => '2026-02-05',
                'notes' => 'Recommended for dogs visiting shelters or kennels.'
            ],
            [
                'animal_id' => 2,
                'vaccine_name' => 'Leptospirosis',
                'vaccine_type' => 'Non-Core',
                'vaccination_date' => '2026-02-15',
                'notes' => 'Protection against Leptospira bacteria.'
            ],
            [
                'animal_id' => 3,
                'vaccine_name' => 'Canine Influenza',
                'vaccine_type' => 'Non-Core',
                'vaccination_date' => '2026-03-01',
                'notes' => 'Recommended for dogs in high-risk environments.'
            ],
            [
                'animal_id' => 3,
                'vaccine_name' => 'FVRCP',
                'vaccine_type' => 'Core',
                'vaccination_date' => '2026-03-12',
                'notes' => 'Core vaccine for cats.'
            ],
            [
                'animal_id' => 4,
                'vaccine_name' => 'Rabies',
                'vaccine_type' => 'Core',
                'vaccination_date' => '2026-03-18',
                'notes' => 'Mandatory rabies vaccination.'
            ],
            [
                'animal_id' => 4,
                'vaccine_name' => 'FeLV',
                'vaccine_type' => 'Non-Core',
                'vaccination_date' => '2026-04-01',
                'notes' => 'Recommended for outdoor cats.'
            ],
            [
                'animal_id' => 5,
                'vaccine_name' => 'FIV',
                'vaccine_type' => 'Optional',
                'vaccination_date' => '2026-04-08',
                'notes' => 'Feline Immunodeficiency Virus vaccine.'
            ],
            [
                'animal_id' => 5,
                'vaccine_name' => 'DHPP Booster',
                'vaccine_type' => 'Booster',
                'vaccination_date' => '2026-04-20',
                'notes' => 'Annual booster dose.'
            ],
            [
                'animal_id' => 6,
                'vaccine_name' => 'Rabies Booster',
                'vaccine_type' => 'Booster',
                'vaccination_date' => '2026-05-01',
                'notes' => 'Rabies booster vaccination.'
            ],
            [
                'animal_id' => 6,
                'vaccine_name' => 'Lyme Disease',
                'vaccine_type' => 'Non-Core',
                'vaccination_date' => '2026-05-15',
                'notes' => 'Recommended for tick-prone areas.'
            ],
            [
                'animal_id' => 7,
                'vaccine_name' => 'Kennel Cough',
                'vaccine_type' => 'Non-Core',
                'vaccination_date' => '2026-06-01',
                'notes' => 'Protection against kennel cough.'
            ],
            [
                'animal_id' => 8,
                'vaccine_name' => 'FVRCP Booster',
                'vaccine_type' => 'Booster',
                'vaccination_date' => '2026-06-12',
                'notes' => 'Annual booster for FVRCP.'
            ],
            [
                'animal_id' => 9,
                'vaccine_name' => 'Canine Coronavirus',
                'vaccine_type' => 'Optional',
                'vaccination_date' => '2026-06-25',
                'notes' => 'Optional vaccine for dogs.'
            ],
            // أضف هذه السجلات داخل $vaccinations array
            [
               'animal_id' => 10,
               'vaccine_name' => 'Distemper',
               'vaccine_type' => 'Core',
               'vaccination_date' => '2026-02-20',
               'notes' => 'Very important core vaccine.'
           ],
           [
              'animal_id' => 15,
              'vaccine_name' => 'Parvovirus',
              'vaccine_type' => 'Core',
              'vaccination_date' => '2026-03-05',
              'notes' => 'Given to puppies usually.'
            ],
        ];

        foreach ($vaccinations as $vaccination) {
            Vaccination::create($vaccination);
        }
    }
}
