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
        /*
        |--------------------------------------------------------------------------
        | Get Regular Users
        |--------------------------------------------------------------------------
        */

        $users = User::role('regular_user')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Get Available Animals
        |--------------------------------------------------------------------------
        */

        $animals = Animal::where('availability_status', 'available')
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $this->command->warn(
                'No regular users found. Please run UserRoleSeeder first.'
            );

            return;
        }

        if ($animals->isEmpty()) {
            $this->command->warn(
                'No available animals found. Please run AnimalSeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Adoption Application Data
        |--------------------------------------------------------------------------
        */

        $applications = [

            // ================================================================
            // 1 - 10 : PENDING
            // ================================================================

            [
                'details' =>
                    "Reason for adoption: I am looking for a friendly companion for my family.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Hybrid, mostly from home.\n" .
                    "Animal experience: 3 years caring for a family cat.\n" .
                    "Emergency contact: Sarah Miller - 0998123456",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I would like to provide a permanent and loving home to a rescued animal.\n" .
                    "Other pets at home: Yes, one vaccinated cat.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Office work from 9 AM to 4 PM.\n" .
                    "Animal experience: Previous experience raising dogs.\n" .
                    "Emergency contact: Omar Hassan - 0987456123",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: Looking for a calm companion suitable for apartment living.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Freelance and flexible.\n" .
                    "Animal experience: Basic experience with cats and birds.\n" .
                    "Emergency contact: Lina Ahmad - 0976543210",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to give a rescued animal a safe home after learning about animal welfare.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 5.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Full-time office work.\n" .
                    "Animal experience: Four years of experience with family pets.\n" .
                    "Emergency contact: Rami Saleh - 0965432187",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I am interested in adopting a young animal and providing proper veterinary care.\n" .
                    "Other pets at home: Yes, one small vaccinated dog.\n" .
                    "Housing type: Villa.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Remote work.\n" .
                    "Animal experience: More than five years with pets.\n" .
                    "Emergency contact: Nour Khalil - 0956781234",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I want a companion animal and can provide daily care and attention.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 1.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Flexible working hours.\n" .
                    "Animal experience: Previously cared for two cats.\n" .
                    "Emergency contact: Maya Ibrahim - 0943215678",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: My children have requested a family pet and we are prepared to take full responsibility.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 5.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Parents work alternating shifts.\n" .
                    "Animal experience: Basic family pet experience.\n" .
                    "Emergency contact: Tarek Mansour - 0934567891",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I would like to adopt an older animal that needs a quiet environment.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Retired and available at home.\n" .
                    "Animal experience: Long-term experience with cats.\n" .
                    "Emergency contact: Hala Youssef - 0923456781",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to provide a stable home for an animal currently waiting for adoption.\n" .
                    "Other pets at home: Yes, one rabbit.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Remote work three days per week.\n" .
                    "Animal experience: Experience caring for rabbits and cats.\n" .
                    "Emergency contact: Samer Nasser - 0912345678",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I am ready to take responsibility for an animal and cover food and veterinary expenses.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Full-time but with a family member at home during the day.\n" .
                    "Animal experience: Previously owned a dog for six years.\n" .
                    "Emergency contact: Dana Ali - 0909876543",
                'status' => 'pending',
            ],

            // ================================================================
            // 11 - 15 : PENDING
            // ================================================================

            [
                'details' =>
                    "Reason for adoption: Looking for a gentle companion for an elderly family member.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Family members share daily care responsibilities.\n" .
                    "Animal experience: Experience with calm indoor cats.\n" .
                    "Emergency contact: Fadi George - 0991234567",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to rescue an animal and give it a permanent home rather than purchasing one.\n" .
                    "Other pets at home: Yes, one vaccinated cat.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Hybrid.\n" .
                    "Animal experience: Seven years of experience with family pets.\n" .
                    "Emergency contact: Reem Hassan - 0981234567",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I have enough time and resources to care for a companion animal.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Student with flexible schedule.\n" .
                    "Animal experience: Previously cared for a rescued kitten.\n" .
                    "Emergency contact: Jad Karim - 0971234568",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to introduce a pet into our family and teach our children responsible animal care.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 5.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Parents work standard daytime hours.\n" .
                    "Animal experience: Previous experience with dogs.\n" .
                    "Emergency contact: Yasmin Saleh - 0961234567",
                'status' => 'pending',
            ],

            [
                'details' =>
                    "Reason for adoption: I prefer adoption because I want to help an animal in need.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 1.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Remote software developer.\n" .
                    "Animal experience: Four years caring for indoor cats.\n" .
                    "Emergency contact: Karim Nabil - 0951234567",
                'status' => 'pending',
            ],

            // ================================================================
            // 16 - 25 : APPROVED
            // ================================================================

            [
                'details' =>
                    "Reason for adoption: Provide a permanent home and regular veterinary care.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Hybrid.\n" .
                    "Animal experience: 5 years.\n" .
                    "Emergency contact: Ahmad Faris - 0991112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: Looking for a family companion.\n" .
                    "Other pets at home: One vaccinated cat.\n" .
                    "Housing type: Villa.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Flexible.\n" .
                    "Animal experience: Experienced dog owner.\n" .
                    "Emergency contact: Salma Nouri - 0981112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to provide a safe environment for a rescued animal.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Remote.\n" .
                    "Animal experience: 3 years with cats.\n" .
                    "Emergency contact: Lina Omar - 0971112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: I am ready to take full responsibility for an adopted pet.\n" .
                    "Other pets at home: Yes, one rabbit.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Office hours.\n" .
                    "Animal experience: Experienced with small animals.\n" .
                    "Emergency contact: Rayan Haddad - 0961112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: Looking for a calm companion.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Freelance.\n" .
                    "Animal experience: Previous cat owner.\n" .
                    "Emergency contact: Jana Sami - 0951112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: Give a rescued animal a stable home.\n" .
                    "Other pets at home: One small dog.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 5.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Hybrid.\n" .
                    "Animal experience: 8 years.\n" .
                    "Emergency contact: Walid Samir - 0941112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: I have always preferred adoption over purchasing pets.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 1.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Remote.\n" .
                    "Animal experience: 2 years.\n" .
                    "Emergency contact: Nour Salem - 0931112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: Family is prepared for long-term pet ownership.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Full-time with family support.\n" .
                    "Animal experience: Previously raised two dogs.\n" .
                    "Emergency contact: Hiba Tarek - 0921112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: Provide companionship and daily care.\n" .
                    "Other pets at home: One vaccinated cat.\n" .
                    "Housing type: Villa.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Flexible.\n" .
                    "Animal experience: 6 years.\n" .
                    "Emergency contact: Basel Ahmad - 0911112233",
                'status' => 'approved',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to provide a safe and permanent home.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Hybrid.\n" .
                    "Animal experience: Previous experience with rescued animals.\n" .
                    "Emergency contact: Mira Khaled - 0901112233",
                'status' => 'approved',
            ],

            // ================================================================
            // 26 - 35 : REJECTED
            // ================================================================

            [
                'details' =>
                    "Reason for adoption: I want a pet mainly for entertainment.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Long shifts away from home.\n" .
                    "Animal experience: Limited.\n" .
                    "Emergency contact: Adam Kareem - 0995551001",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I would like a pet for my child.\n" .
                    "Other pets at home: Yes, an unvaccinated dog.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Full-time with no regular caregiver.\n" .
                    "Animal experience: Very limited.\n" .
                    "Emergency contact: Mona Khalil - 0985551002",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to try having a pet.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Temporary rental.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 1.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Frequent travel.\n" .
                    "Animal experience: None.\n" .
                    "Emergency contact: Sami George - 0975551003",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: My children want a dog.\n" .
                    "Other pets at home: Two cats that are not vaccinated.\n" .
                    "Housing type: Small apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 5.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Both parents work full-time.\n" .
                    "Animal experience: Basic.\n" .
                    "Emergency contact: Rasha Nabil - 0965551004",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: Looking for a companion while I am studying.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Shared apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 1.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: University student with irregular schedule.\n" .
                    "Animal experience: Limited.\n" .
                    "Emergency contact: Firas Ali - 0955551005",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I want an animal mainly as a guard for my property.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Outdoor property.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Full-time.\n" .
                    "Animal experience: Basic dog ownership.\n" .
                    "Emergency contact: Nader Hassan - 0945551006",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I would like to surprise a family member with a pet.\n" .
                    "Other pets at home: Unknown.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Full-time.\n" .
                    "Animal experience: None.\n" .
                    "Emergency contact: Dana Samir - 0935551007",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I want a pet but have not planned the monthly expenses.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Irregular.\n" .
                    "Animal experience: Minimal.\n" .
                    "Emergency contact: Jad Omar - 0925551008",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I want to adopt because the animal looks cute.\n" .
                    "Other pets at home: Yes, two cats.\n" .
                    "Housing type: Small apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Long daily shifts.\n" .
                    "Animal experience: Basic.\n" .
                    "Emergency contact: Hanan Fadi - 0915551009",
                'status' => 'rejected',
            ],

            [
                'details' =>
                    "Reason for adoption: I am interested in having an animal temporarily.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Rental apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 1.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Frequent travel outside the city.\n" .
                    "Animal experience: Limited.\n" .
                    "Emergency contact: Karim Fares - 0905551010",
                'status' => 'rejected',
            ],

            // ================================================================
            // 36 - 40 : COMPLETED
            // ================================================================

            [
                'details' =>
                    "Reason for adoption: I wanted to give a rescued animal a permanent family home.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 4.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Hybrid.\n" .
                    "Animal experience: 7 years of experience with dogs.\n" .
                    "Emergency contact: Ahmad Saleh - 0997001001",
                'status' => 'completed',
            ],

            [
                'details' =>
                    "Reason for adoption: I wanted a calm companion and was prepared for long-term responsibility.\n" .
                    "Other pets at home: One vaccinated cat.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 2.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Remote.\n" .
                    "Animal experience: Five years with cats.\n" .
                    "Emergency contact: Rima Hassan - 0987001002",
                'status' => 'completed',
            ],

            [
                'details' =>
                    "Reason for adoption: We wanted to provide a permanent home for a rescued animal.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: Villa.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 5.\n" .
                    "Children under 10: Yes.\n" .
                    "Work schedule: Family members share daily care.\n" .
                    "Animal experience: Previous experience with dogs and cats.\n" .
                    "Emergency contact: Samer Khalil - 0977001003",
                'status' => 'completed',
            ],

            [
                'details' =>
                    "Reason for adoption: I wanted to rescue an animal and give it a stable environment.\n" .
                    "Other pets at home: No.\n" .
                    "Housing type: House.\n" .
                    "Garden or yard: Yes.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Flexible.\n" .
                    "Animal experience: Four years caring for pets.\n" .
                    "Emergency contact: Nour Ahmad - 0967001004",
                'status' => 'completed',
            ],

            [
                'details' =>
                    "Reason for adoption: I wanted to adopt instead of buying a pet and provide lifelong care.\n" .
                    "Other pets at home: One rabbit.\n" .
                    "Housing type: Apartment.\n" .
                    "Garden or yard: No.\n" .
                    "Family members: 3.\n" .
                    "Children under 10: No.\n" .
                    "Work schedule: Hybrid.\n" .
                    "Animal experience: More than six years with small animals.\n" .
                    "Emergency contact: Lina George - 0957001005",
                'status' => 'completed',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Applications
        |--------------------------------------------------------------------------
        */

        $created = 0;
        $userCount = $users->count();
        $animalCount = $animals->count();

        foreach ($applications as $index => $application) {

            /*
             * Generate different user/animal combinations.
             * This avoids repeating the exact same user + animal pair.
             */
            $user = $users[$index % $userCount];

            $animalIndex = intdiv($index, $userCount) % $animalCount;
            $animal = $animals[$animalIndex];

            $exists = AdoptionApplication::where('user_id', $user->id)
                ->where('animal_id', $animal->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $status = $application['status'];

            $approvedAt = in_array($status, [
                'approved',
                'completed'
            ])
                ? now()->subDays(rand(1, 12))
                : null;

            AdoptionApplication::create([
                'user_id'             => $user->id,
                'animal_id'           => $animal->id,
                'application_details' => $application['details'],
                'status'              => $status,
                'approved_at'         => $approvedAt,
                'created_at'          => now()->subDays(rand(1, 20)),
                'updated_at'          => now()->subDays(rand(0, 7)),
            ]);

            $created++;
        }

        $this->command->info(
            "Created {$created} adoption applications successfully."
        );
    }
}
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
