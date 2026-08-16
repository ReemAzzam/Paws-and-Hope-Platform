<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\RescueReport;
use App\Models\RescueReportImage;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Animal;

class RescueReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        RescueReportImage::truncate();
        RescueReport::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | Regular Users
        |--------------------------------------------------------------------------
        */

        $regularUserIds = User::role('regular_user')
            ->pluck('id')
            ->toArray();

        if (empty($regularUserIds)) {
            $this->command->error(
                'No regular users found. Please run UserRoleSeeder first.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Approved Volunteers
        |
        | IMPORTANT:
        | rescue_reports.volunteer_id references users.id
        | so we use volunteers.user_id
        |--------------------------------------------------------------------------
        */

        $volunteerUserIds = Volunteer::where('is_approved', true)
            ->pluck('user_id')
            ->toArray();

        if (empty($volunteerUserIds)) {
            $this->command->warn(
                'No approved volunteers found. Assigned reports will have volunteer_id = null.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Animals
        |--------------------------------------------------------------------------
        */

        $animals = Animal::query()
            ->orderBy('id')
            ->get();

        if ($animals->count() < 60) {
            $this->command->error(
                "At least 60 animals are required. Found: {$animals->count()}"
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Damascus Rescue Areas
        |--------------------------------------------------------------------------
        */

        $locations = [
            [
                'address' => 'دمشق - المزة - قرب مشفى المواساة',
                'lat' => 33.513800,
                'lng' => 36.276500,
            ],
            [
                'address' => 'دمشق - المزة - الفيلات الغربية',
                'lat' => 33.509000,
                'lng' => 36.278000,
            ],
            [
                'address' => 'دمشق - كفرسوسة - قرب المجمع الحكومي',
                'lat' => 33.498100,
                'lng' => 36.281000,
            ],
            [
                'address' => 'دمشق - المالكي - قرب حديقة الجاحظ',
                'lat' => 33.519500,
                'lng' => 36.284200,
            ],
            [
                'address' => 'دمشق - الشعلان - قرب حديقة السبكي',
                'lat' => 33.516000,
                'lng' => 36.291100,
            ],
            [
                'address' => 'دمشق - أبو رمانة - قرب ساحة الروضة',
                'lat' => 33.524100,
                'lng' => 36.289000,
            ],
            [
                'address' => 'دمشق - القصاع - منطقة مار ميخائيل',
                'lat' => 33.514000,
                'lng' => 36.312000,
            ],
            [
                'address' => 'دمشق - باب توما - قرب الساحة',
                'lat' => 33.511500,
                'lng' => 36.307200,
            ],
            [
                'address' => 'دمشق - الميدان - قرب الصالة الرياضية',
                'lat' => 33.504200,
                'lng' => 36.292100,
            ],
            [
                'address' => 'دمشق - ركن الدين - منطقة ابن النفيس',
                'lat' => 33.538000,
                'lng' => 36.299100,
            ],
            [
                'address' => 'دمشق - دمر - المشروع',
                'lat' => 33.535000,
                'lng' => 36.242000,
            ],
            [
                'address' => 'دمشق - دمر - الجزيرة الأولى',
                'lat' => 33.533000,
                'lng' => 36.251000,
            ],
            [
                'address' => 'دمشق - قدسيا',
                'lat' => 33.549000,
                'lng' => 36.235000,
            ],
            [
                'address' => 'دمشق - جرمانا - المنطقة الغربية',
                'lat' => 33.486000,
                'lng' => 36.346000,
            ],
            [
                'address' => 'ريف دمشق - صحنايا',
                'lat' => 33.444000,
                'lng' => 36.237000,
            ],
            [
                'address' => 'ريف دمشق - داريا',
                'lat' => 33.458000,
                'lng' => 36.242000,
            ],
            [
                'address' => 'ريف دمشق - قدسيا - المنطقة السكنية',
                'lat' => 33.555000,
                'lng' => 36.232000,
            ],
            [
                'address' => 'ريف دمشق - المزة جبل',
                'lat' => 33.505000,
                'lng' => 36.264000,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Report Distribution
        |--------------------------------------------------------------------------
        |
        | 20 resolved
        | 10 reported
        | 10 dispatched
        | 10 on_site
        | 10 in_clinic
        |--------------------------------------------------------------------------
        */

        $statuses = array_merge(
            array_fill(0, 20, 'resolved'),
            array_fill(0, 10, 'reported'),
            array_fill(0, 10, 'dispatched'),
            array_fill(0, 10, 'on_site'),
            array_fill(0, 10, 'in_clinic')
        );

        /*
        |--------------------------------------------------------------------------
        | Health statuses
        |--------------------------------------------------------------------------
        */

        $healthStatuses = [
            'bleeding',
            'fracture',
            'poisoning',
            'other',
        ];

        /*
        |--------------------------------------------------------------------------
        | Severity
        |--------------------------------------------------------------------------
        */

        $severityLevels = [
            'normal',
            'urgent',
            'critical',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Reports
        |--------------------------------------------------------------------------
        */

        foreach ($statuses as $index => $status) {

            /*
            | Use a different animal for every report.
            | This makes the demo data easier to understand.
            */

            $animal = $animals[$index];

            $location = $locations[$index % count($locations)];

            $healthStatus = $healthStatuses[$index % count($healthStatuses)];

            /*
            | Make severity somewhat realistic depending on health status.
            */

            if ($healthStatus === 'bleeding') {
                $severity = $index % 3 === 0
                    ? 'critical'
                    : 'urgent';
            } elseif ($healthStatus === 'fracture') {
                $severity = $index % 2 === 0
                    ? 'urgent'
                    : 'normal';
            } elseif ($healthStatus === 'poisoning') {
                $severity = 'urgent';
            } else {
                $severity = $severityLevels[$index % count($severityLevels)];
            }

            /*
            |--------------------------------------------------------------------------
            | Reporter
            |--------------------------------------------------------------------------
            */

            $userId = $regularUserIds[
                $index % count($regularUserIds)
            ];

            /*
            |--------------------------------------------------------------------------
            | Volunteer assignment
            |
            | reported -> no volunteer
            | other statuses -> assigned volunteer
            |--------------------------------------------------------------------------
            */

            $volunteerId = null;

            if ($status !== 'reported' && !empty($volunteerUserIds)) {
                $volunteerId = $volunteerUserIds[
                    $index % count($volunteerUserIds)
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Realistic descriptions
            |--------------------------------------------------------------------------
            */

            $description = $this->generateDescription(
                $animal->type,
                $healthStatus,
                $status
            );

            /*
            |--------------------------------------------------------------------------
            | Date/time
            |--------------------------------------------------------------------------
            */

            $createdAt = now()->subDays(
                rand(0, 14)
            )->subHours(
                rand(0, 18)
            )->subMinutes(
                rand(0, 59)
            );

            $updatedAt = $createdAt->copy();

            if ($status === 'resolved') {
                $updatedAt = $createdAt->copy()->addHours(
                    rand(2, 24)
                );
            } elseif ($status === 'dispatched') {
                $updatedAt = $createdAt->copy()->addMinutes(
                    rand(10, 90)
                );
            } elseif ($status === 'on_site') {
                $updatedAt = $createdAt->copy()->addHours(
                    rand(1, 4)
                );
            } elseif ($status === 'in_clinic') {
                $updatedAt = $createdAt->copy()->addHours(
                    rand(2, 8)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create report
            |--------------------------------------------------------------------------
            */

            $report = RescueReport::create([
                'user_id' => $userId,

                'volunteer_id' => $volunteerId,

                'latitude' => $location['lat'],

                'longitude' => $location['lng'],

                'location_address' => $location['address'],

                'severity_level' => $severity,

                'animal_type' => $animal->type,

                'health_status' => $healthStatus,

                'description' => $description,

                'status' => $status,

                'created_at' => $createdAt,

                'updated_at' => $updatedAt,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach animal images
            |
            | Folder:
            | storage/app/public/rescue-reports/{animal_id}/
            |--------------------------------------------------------------------------
            */

            $this->attachAnimalImages(
                $report->id,
                $animal->id,
                $createdAt
            );
        }

        $this->command->info(
            '✅ Rescue reports seeded successfully.'
        );

        $this->command->info(
            '60 reports created: 20 resolved, 10 reported, 10 dispatched, 10 on_site, 10 in_clinic.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attach Images
    |--------------------------------------------------------------------------
    */

    private function attachAnimalImages(
        int $reportId,
        int $animalId,
        $createdAt
    ): void {

        $directory = storage_path(
            "app/public/rescue-reports/{$animalId}"
        );

        if (!File::exists($directory)) {
            $this->command->warn(
                "No image folder found for animal ID {$animalId}: {$directory}"
            );

            return;
        }

        /*
        | Get only image files.
        */

        $files = File::files($directory);

        $imageFiles = collect($files)
            ->filter(function ($file) {
                return in_array(
                    strtolower($file->getExtension()),
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp',
                        'gif',
                        'avif',
                    ]
                );
            })
            ->values();

        if ($imageFiles->isEmpty()) {
            $this->command->warn(
                "No images found for animal ID {$animalId}."
            );

            return;
        }

        /*
        | The animal folder contains 2-3 images.
        | We attach all existing images.
        */

        foreach ($imageFiles as $file) {

            $fileName = $file->getFilename();

            $relativePath =
                "rescue-reports/{$animalId}/{$fileName}";

            $imageUrl = asset(
                "storage/{$relativePath}"
            );

            RescueReportImage::create([
                'rescue_report_id' => $reportId,

                'image_path' => $imageUrl,

                'created_at' => $createdAt,

                'updated_at' => $createdAt,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Realistic Descriptions
    |--------------------------------------------------------------------------
    */

    private function generateDescription(
        string $animalType,
        string $healthStatus,
        string $status
    ): string {

        $descriptions = [

            'bleeding' => [
                "The {$animalType} was found with visible bleeding and signs of weakness. Immediate assistance was requested.",
                "A {$animalType} was reported after being injured and found bleeding near a residential area.",
                "The injured {$animalType} has active bleeding and requires prompt medical attention.",
            ],

            'fracture' => [
                "The {$animalType} was found unable to use one of its limbs and appears to have a possible fracture.",
                "A {$animalType} was reported after an apparent fall with difficulty walking and suspected bone injury.",
                "The {$animalType} is showing signs of severe pain and difficulty moving, with a suspected fracture.",
            ],

            'poisoning' => [
                "The {$animalType} was found extremely weak with suspected poisoning and requires urgent veterinary assessment.",
                "A {$animalType} was reported after showing vomiting, weakness, and other possible poisoning symptoms.",
                "The {$animalType} appears to have ingested an unknown substance and needs immediate veterinary evaluation.",
            ],

            'other' => [
                "The {$animalType} was found in distress and appears unable to safely move without assistance.",
                "A {$animalType} was reported in need of rescue after being found in an unsafe location.",
                "The {$animalType} was found abandoned and visibly exhausted and requires assistance.",
            ],
        ];

        return $descriptions[$healthStatus][
            rand(
                0,
                count($descriptions[$healthStatus]) - 1
            )
        ];
    }
}