<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RescueReport;
use App\Models\RescueReportImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class RescueReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        RescueReportImage::truncate();
        RescueReport::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $userIds = User::pluck('id')->toArray();
        
        $animalImageFiles = [
            'dog'      => ['dog1.jpg', 'dog2.jpg', 'dog_accident.jpg'],
            'cat'      => ['cat1.jpg', 'cat_fracture.jpg', 'cat3.jpg'],
            'bird'     => ['bird1.jpg', 'pigeon.jpg'],
            'turtle'   => ['turtle.jpg'],
            'hedgehog' => ['hedgehog.jpg'],
            'donkey'   => ['donkey.jpg'],
            'rabbit'   => ['rabbit.jpg'],
        ];

        $reportsData = $this->getFullReportsList();

        foreach ($reportsData as $data) {
            $data['user_id'] = !empty($userIds) ? $userIds[array_rand($userIds)] : null;
            
            if ($data['status'] !== 'reported' && !empty($userIds)) {
                $data['volunteer_id'] = $userIds[array_rand($userIds)];
            } else {
                $data['volunteer_id'] = null;
            }

            $report = RescueReport::create($data);

            $animalType = $report->animal_type;
            $availableImages = $animalImageFiles[$animalType] ?? ['default.jpg'];

            $photoCount = rand(1, 2);
            $selectedImages = Arr::random($availableImages, min($photoCount, count($availableImages)));
            $selectedImages = is_array($selectedImages) ? $selectedImages : [$selectedImages];

            $destinationPath = storage_path("app/public/rescue_reports/{$report->id}");

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            foreach ($selectedImages as $imageFileName) {
                $sourceFile = storage_path("app/public/rescue_reports/{$imageFileName}");

                if (File::exists($sourceFile)) {
                    File::copy($sourceFile, "{$destinationPath}/{$imageFileName}");
                }

                $fullUrl = asset("storage/rescue_reports/{$report->id}/{$imageFileName}");

                RescueReportImage::create([
                    'rescue_report_id' => $report->id,
                    'image_path'       => $fullUrl,
                    'created_at'       => $report->created_at,
                    'updated_at'       => $report->created_at,
                ]);
            }
        }

        $this->command->info('✅ 30 Rescue reports seeded successfully with folder structures!');
    }

    private function getFullReportsList(): array
    {
        return [
            // ====================== DOG REPORTS (10) ======================
            [
                'latitude'         => 33.513800,
                'longitude'        => 36.276500,
                'location_address' => 'Damascus - Mazzeh - Near Al-Mawasat Hospital',
                'severity_level'   => 'critical',
                'animal_type'      => 'dog',
                'health_status'    => 'bleeding',
                'description'      => 'Dog injured in a car accident, bleeding from back leg, needs urgent intervention.',
                'status'           => 'dispatched',
                'created_at'       => now()->subHours(5),
                'updated_at'       => now()->subHours(4),
            ],
            [
                'latitude'         => 33.510200,
                'longitude'        => 36.285100,
                'location_address' => 'Damascus - Baramkeh - Next to SANA Agency',
                'severity_level'   => 'urgent',
                'animal_type'      => 'dog',
                'health_status'    => 'poisoning',
                'description'      => 'Suspected poisoning of a stray dog, extremely weak and unable to move.',
                'status'           => 'on_site',
                'created_at'       => now()->subHours(3),
                'updated_at'       => now()->subHours(2),
            ],
            [
                'latitude'         => 33.498100,
                'longitude'        => 36.281000,
                'location_address' => 'Damascus - Kafr Sousa - Near Government Complex',
                'severity_level'   => 'normal',
                'animal_type'      => 'dog',
                'health_status'    => 'other',
                'description'      => 'Dog trapped in a construction pit and cannot get out.',
                'status'           => 'reported',
                'created_at'       => now()->subMinutes(45),
                'updated_at'       => now()->subMinutes(45),
            ],
            [
                'latitude'         => 33.522000,
                'longitude'        => 36.311000,
                'location_address' => 'Damascus - Tijara - Towers Street',
                'severity_level'   => 'urgent',
                'animal_type'      => 'dog',
                'health_status'    => 'other',
                'description'      => 'Abandoned newborn puppies suffering severe dehydration in a building under construction.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subDays(1),
                'updated_at'       => now()->subHours(10),
            ],
            [
                'latitude'         => 33.504200,
                'longitude'        => 36.292100,
                'location_address' => 'Damascus - Midan - Near the Sports Hall',
                'severity_level'   => 'normal',
                'animal_type'      => 'dog',
                'health_status'    => 'fracture',
                'description'      => 'Dog clearly limping and showing severe exhaustion and fatigue.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(3),
                'updated_at'       => now()->subDays(2),
            ],
            [
                'latitude'         => 33.531100,
                'longitude'        => 36.241000,
                'location_address' => 'Damascus - Dummar Village - Near the Bridge',
                'severity_level'   => 'critical',
                'animal_type'      => 'dog',
                'health_status'    => 'bleeding',
                'description'      => 'Large dog struck by a speeding vehicle, lying on the road shoulder.',
                'status'           => 'dispatched',
                'created_at'       => now()->subHours(1),
                'updated_at'       => now()->subMinutes(15),
            ],
            [
                'latitude'         => 33.519500,
                'longitude'        => 36.284200,
                'location_address' => 'Damascus - Malki - Near Al-Jahiz Park',
                'severity_level'   => 'normal',
                'animal_type'      => 'dog',
                'health_status'    => 'other',
                'description'      => 'Lost pet dog with a collar found wandering alone in the park, visibly exhausted.',
                'status'           => 'reported',
                'created_at'       => now()->subMinutes(20),
                'updated_at'       => now()->subMinutes(20),
            ],
            [
                'latitude'         => 33.516000,
                'longitude'        => 36.291100,
                'location_address' => 'Damascus - Shaalan - Near Al-Subki Park',
                'severity_level'   => 'urgent',
                'animal_type'      => 'dog',
                'health_status'    => 'poisoning',
                'description'      => 'Dog showing severe lethargy and vomiting symptoms near dumpsters.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subHours(7),
                'updated_at'       => now()->subHours(3),
            ],
            [
                'latitude'         => 33.524100,
                'longitude'        => 36.289000,
                'location_address' => 'Damascus - Abu Rummaneh - Near Al-Rawda Square',
                'severity_level'   => 'normal',
                'animal_type'      => 'dog',
                'health_status'    => 'other',
                'description'      => 'Stray dog tied up tightly outside an abandoned storefront.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(4),
                'updated_at'       => now()->subDays(3),
            ],
            [
                'latitude'         => 33.538000,
                'longitude'        => 36.299100,
                'location_address' => 'Damascus - Rukn Al-Din - Ibn Al-Nafis',
                'severity_level'   => 'critical',
                'animal_type'      => 'dog',
                'health_status'    => 'fracture',
                'description'      => 'Injured dog trapped near a steep slope unable to stand up.',
                'status'           => 'on_site',
                'created_at'       => now()->subHours(2),
                'updated_at'       => now()->subMinutes(40),
            ],

            // ====================== CAT REPORTS (10) ======================
            [
                'latitude'         => 33.511500,
                'longitude'        => 36.307200,
                'location_address' => 'Damascus - Bab Touma - Near the Square',
                'severity_level'   => 'urgent',
                'animal_type'      => 'cat',
                'health_status'    => 'fracture',
                'description'      => 'Kitten with a fractured leg, limping and unable to walk properly.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subHours(8),
                'updated_at'       => now()->subHours(6),
            ],
            [
                'latitude'         => 33.489000,
                'longitude'        => 36.299000,
                'location_address' => 'Damascus - Midan - Near the Hall',
                'severity_level'   => 'normal',
                'animal_type'      => 'cat',
                'health_status'    => 'other',
                'description'      => 'Cat trapped on a high tree for two days, making distress cries.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(2),
                'updated_at'       => now()->subDays(1),
            ],
            [
                'latitude'         => 33.528000,
                'longitude'        => 36.301000,
                'location_address' => 'Damascus - Adawi Highway - Next to Al-Tijara Park',
                'severity_level'   => 'critical',
                'animal_type'      => 'cat',
                'health_status'    => 'bleeding',
                'description'      => 'Cat with an eye injury, needs immediate veterinary examination.',
                'status'           => 'dispatched',
                'created_at'       => now()->subHours(2),
                'updated_at'       => now()->subMinutes(30),
            ],
            [
                'latitude'         => 33.535000,
                'longitude'        => 36.242000,
                'location_address' => 'Damascus - Dummar Project - Island 2',
                'severity_level'   => 'normal',
                'animal_type'      => 'cat',
                'health_status'    => 'other',
                'description'      => 'Newborn kitten abandoned on the side of the street, suffering from severe weakness.',
                'status'           => 'reported',
                'created_at'       => now()->subMinutes(15),
                'updated_at'       => now()->subMinutes(15),
            ],
            [
                'latitude'         => 33.518100,
                'longitude'        => 36.292000,
                'location_address' => 'Damascus - Salhiyah - Near the Parliament',
                'severity_level'   => 'urgent',
                'animal_type'      => 'cat',
                'health_status'    => 'other',
                'description'      => 'Cat suffering from severe respiratory infection and refusing food.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subHours(12),
                'updated_at'       => now()->subHours(4),
            ],
            [
                'latitude'         => 33.523000,
                'longitude'        => 36.287000,
                'location_address' => 'Damascus - Afif - Near the French Hospital',
                'severity_level'   => 'critical',
                'animal_type'      => 'cat',
                'health_status'    => 'bleeding',
                'description'      => 'Cat stuck in a car engine compartment and bleeding after attempt to flee.',
                'status'           => 'on_site',
                'created_at'       => now()->subHours(1),
                'updated_at'       => now()->subMinutes(10),
            ],
            [
                'latitude'         => 33.509000,
                'longitude'        => 36.278000,
                'location_address' => 'Damascus - Mazzeh - Western Villas',
                'severity_level'   => 'normal',
                'animal_type'      => 'cat',
                'health_status'    => 'other',
                'description'      => 'Pregnant cat sheltering in a wet basement needing safe environment.',
                'status'           => 'reported',
                'created_at'       => now()->subMinutes(50),
                'updated_at'       => now()->subMinutes(50),
            ],
            [
                'latitude'         => 33.514000,
                'longitude'        => 36.312000,
                'location_address' => 'Damascus - Qassaa - St. Michael Church Area',
                'severity_level'   => 'urgent',
                'animal_type'      => 'cat',
                'health_status'    => 'fracture',
                'description'      => 'Cat fallen from a second-floor balcony with suspected pelvic fracture.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subDays(1),
                'updated_at'       => now()->subHours(8),
            ],
            [
                'latitude'         => 33.501200,
                'longitude'        => 36.289100,
                'location_address' => 'Damascus - Zahrani - Near Al-Ehsan Mosque',
                'severity_level'   => 'normal',
                'animal_type'      => 'cat',
                'health_status'    => 'other',
                'description'      => 'Blind elderly cat wandering near a busy main street in extreme exhaustion.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(5),
                'updated_at'       => now()->subDays(4),
            ],
            [
                'latitude'         => 33.529000,
                'longitude'        => 36.275000,
                'location_address' => 'Damascus - Muhajreen - 4th Station',
                'severity_level'   => 'critical',
                'animal_type'      => 'cat',
                'health_status'    => 'poisoning',
                'description'      => 'Multiple kittens showing severe illness symptoms in a residential alley.',
                'status'           => 'dispatched',
                'created_at'       => now()->subHours(3),
                'updated_at'       => now()->subHours(1),
            ],

            // ====================== BIRD REPORTS (6) ======================
            [
                'latitude'         => 33.521000,
                'longitude'        => 36.291000,
                'location_address' => 'Damascus - Shaalan - Near Al-Subki Park',
                'severity_level'   => 'urgent',
                'animal_type'      => 'bird',
                'health_status'    => 'fracture',
                'description'      => 'Bird with a broken wing, unable to fly.',
                'status'           => 'on_site',
                'created_at'       => now()->subHours(4),
                'updated_at'       => now()->subHours(1),
            ],
            [
                'latitude'         => 33.518000,
                'longitude'        => 36.288000,
                'location_address' => 'Damascus - Abu Rummaneh - Near Al-Rawda Square',
                'severity_level'   => 'normal',
                'animal_type'      => 'bird',
                'health_status'    => 'other',
                'description'      => 'Pigeon injured by a metal wire wrapped tightly around its leg.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(3),
                'updated_at'       => now()->subDays(2),
            ],
            [
                'latitude'         => 33.502000,
                'longitude'        => 36.280000,
                'location_address' => 'Damascus - Kafr Sousa - Park Alleyway',
                'severity_level'   => 'critical',
                'animal_type'      => 'bird',
                'health_status'    => 'bleeding',
                'description'      => 'Small falcon severely weak and bleeding from wing fallen in school yard.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subHours(10),
                'updated_at'       => now()->subHours(5),
            ],
            [
                'latitude'         => 33.512000,
                'longitude'        => 36.298000,
                'location_address' => 'Damascus - Hijaz Square - Main Station',
                'severity_level'   => 'normal',
                'animal_type'      => 'bird',
                'health_status'    => 'other',
                'description'      => 'Exotic pet parrot trapped inside a high building mesh wire.',
                'status'           => 'dispatched',
                'created_at'       => now()->subHours(2),
                'updated_at'       => now()->subMinutes(20),
            ],
            [
                'latitude'         => 33.526000,
                'longitude'        => 36.315000,
                'location_address' => 'Damascus - Zablatani - Near Wholesale Market',
                'severity_level'   => 'urgent',
                'animal_type'      => 'bird',
                'health_status'    => 'other',
                'description'      => 'Seagull entangled in discarded plastic net unable to move.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(2),
                'updated_at'       => now()->subDays(1),
            ],
            [
                'latitude'         => 33.533000,
                'longitude'        => 36.251000,
                'location_address' => 'Damascus - Dummar - Island 1 Center',
                'severity_level'   => 'normal',
                'animal_type'      => 'bird',
                'health_status'    => 'other',
                'description'      => 'Baby bird fallen from nest onto pedestrian path, dehydrated.',
                'status'           => 'reported',
                'created_at'       => now()->subMinutes(30),
                'updated_at'       => now()->subMinutes(30),
            ],

            // ====================== SPECIFIC OTHER ANIMAL REPORTS (4) ======================
            [
                'latitude'         => 33.531000,
                'longitude'        => 36.295000,
                'location_address' => 'Damascus - Rukn Al-Din - Ibn Al-Nafis',
                'severity_level'   => 'normal',
                'animal_type'      => 'turtle',
                'health_status'    => 'fracture',
                'description'      => 'Turtle with a cracked shell due to a light vehicle run-over.',
                'status'           => 'in_clinic',
                'created_at'       => now()->subDays(1),
                'updated_at'       => now()->subHours(5),
            ],
            [
                'latitude'         => 33.517000,
                'longitude'        => 36.273000,
                'location_address' => 'Damascus - Mezzah - Old City Area',
                'severity_level'   => 'urgent',
                'animal_type'      => 'hedgehog',
                'health_status'    => 'other',
                'description'      => 'Injured hedgehog stuck in drainage grate along the sidewalk.',
                'status'           => 'on_site',
                'created_at'       => now()->subHours(3),
                'updated_at'       => now()->subHours(1),
            ],
            [
                'latitude'         => 33.492000,
                'longitude'        => 36.310000,
                'location_address' => 'Damascus - Babbila Road - Outer Boundary',
                'severity_level'   => 'critical',
                'animal_type'      => 'donkey',
                'health_status'    => 'bleeding',
                'description'      => 'Donkey struck by heavy truck needing urgent field medical team.',
                'status'           => 'dispatched',
                'created_at'       => now()->subHours(1),
                'updated_at'       => now()->subMinutes(10),
            ],
            [
                'latitude'         => 33.525000,
                'longitude'        => 36.281000,
                'location_address' => 'Damascus - Rawda - Park Gardens',
                'severity_level'   => 'normal',
                'animal_type'      => 'rabbit',
                'health_status'    => 'other',
                'description'      => 'Small rabbit abandoned in a box inside the public garden.',
                'status'           => 'resolved',
                'created_at'       => now()->subDays(3),
                'updated_at'       => now()->subDays(2),
            ],
        ];
    }
}