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

        // Clean old public images
        Storage::disk('public')->deleteDirectory('rescue_reports');
        Storage::disk('public')->makeDirectory('rescue_reports');

        /*
        |--------------------------------------------------------------------------
        | Regular Users
        |--------------------------------------------------------------------------
        */
        $regularUserIds = User::role('regular_user')
            ->pluck('id')
            ->toArray();

        if (empty($regularUserIds)) {
            $this->command->error('No regular users found. Please run UserRoleSeeder first.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Approved Volunteers (volunteer_id -> users.id)
        |--------------------------------------------------------------------------
        */
        $volunteerUserIds = Volunteer::where('is_approved', true)
            ->pluck('user_id')
            ->toArray();

        if (empty($volunteerUserIds)) {
            $this->command->warn('No approved volunteers found. Assigned reports will have volunteer_id = null.');
        }

        /*
        |--------------------------------------------------------------------------
        | Animals
        |--------------------------------------------------------------------------
        */
        $animals = Animal::query()->orderBy('id')->get();

        if ($animals->count() < 60) {
            $this->command->error("At least 60 animals are required. Found: {$animals->count()}");
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Damascus Rescue Areas
        |--------------------------------------------------------------------------
        */
        $locations = [
            ['address' => 'Damascus - Mezzeh near Al Mouwasat Hospital', 'lat' => 33.513800, 'lng' => 36.276500],
            ['address' => 'Damascus - Mezzeh Western Villas', 'lat' => 33.509000, 'lng' => 36.278000],
            ['address' => 'Damascus - Kafar Souseh near Government Complex', 'lat' => 33.498100, 'lng' => 36.281000],
            ['address' => 'Damascus - Malki near Al Jahiz Park', 'lat' => 33.519500, 'lng' => 36.284200],
            ['address' => 'Damascus - Shaalan near Al Sibki Park', 'lat' => 33.516000, 'lng' => 36.291100],
            ['address' => 'Damascus - Abu Rummaneh near Al Rawda Square', 'lat' => 33.524100, 'lng' => 36.289000],
            ['address' => 'Damascus - Qassaa Mar Mikhail Area', 'lat' => 33.514000, 'lng' => 36.312000],
            ['address' => 'Damascus - Bab Touma near the Square', 'lat' => 33.511500, 'lng' => 36.307200],
            ['address' => 'Damascus - Midan near Sports Hall', 'lat' => 33.504200, 'lng' => 36.292100],
            ['address' => 'Damascus - Rukn Al Din Ibn Al Nafis', 'lat' => 33.538000, 'lng' => 36.299100],
            ['address' => 'Damascus - Dummar Project', 'lat' => 33.535000, 'lng' => 36.242000],
            ['address' => 'Damascus - Dummar First Island', 'lat' => 33.533000, 'lng' => 36.251000],
            ['address' => 'Damascus - Qudsaya', 'lat' => 33.549000, 'lng' => 36.235000],
            ['address' => 'Damascus Countryside - Jaramana West', 'lat' => 33.486000, 'lng' => 36.346000],
            ['address' => 'Damascus Countryside - Sahnaya', 'lat' => 33.444000, 'lng' => 36.237000],
            ['address' => 'Damascus Countryside - Daraya', 'lat' => 33.458000, 'lng' => 36.242000],
            ['address' => 'Damascus Countryside - Qudsaya Residential Area', 'lat' => 33.555000, 'lng' => 36.232000],
            ['address' => 'Damascus Countryside - Mezzeh Mountain', 'lat' => 33.505000, 'lng' => 36.264000],
        ];

        /*
        |--------------------------------------------------------------------------
        | Report Distribution
        | 20 resolved | 10 reported | 10 dispatched | 10 on_site | 10 in_clinic
        |--------------------------------------------------------------------------
        */
        $statuses = array_merge(
            array_fill(0, 20, 'resolved'),
            array_fill(0, 10, 'reported'),
            array_fill(0, 10, 'dispatched'),
            array_fill(0, 10, 'on_site'),
            array_fill(0, 10, 'in_clinic')
        );

        $healthStatuses = ['bleeding', 'fracture', 'poisoning', 'other'];
        $severityLevels = ['normal', 'urgent', 'critical'];

        foreach ($statuses as $index => $status) {
            $animal = $animals[$index];
            $location = $locations[$index % count($locations)];
            $healthStatus = $healthStatuses[$index % count($healthStatuses)];

            if ($healthStatus === 'bleeding') {
                $severity = $index % 3 === 0 ? 'critical' : 'urgent';
            } elseif ($healthStatus === 'fracture') {
                $severity = $index % 2 === 0 ? 'urgent' : 'normal';
            } elseif ($healthStatus === 'poisoning') {
                $severity = 'urgent';
            } else {
                $severity = $severityLevels[$index % count($severityLevels)];
            }

            $userId = $regularUserIds[$index % count($regularUserIds)];

            $volunteerId = null;
            if ($status !== 'reported' && !empty($volunteerUserIds)) {
                $volunteerId = $volunteerUserIds[$index % count($volunteerUserIds)];
            }

            $description = $this->generateDescription(
                $animal->type,
                $healthStatus,
                $status
            );

            $createdAt = now()
                ->subDays(rand(0, 14))
                ->subHours(rand(0, 18))
                ->subMinutes(rand(0, 59));

            $updatedAt = $createdAt->copy();

            if ($status === 'resolved') {
                $updatedAt = $createdAt->copy()->addHours(rand(2, 24));
            } elseif ($status === 'dispatched') {
                $updatedAt = $createdAt->copy()->addMinutes(rand(10, 90));
            } elseif ($status === 'on_site') {
                $updatedAt = $createdAt->copy()->addHours(rand(1, 4));
            } elseif ($status === 'in_clinic') {
                $updatedAt = $createdAt->copy()->addHours(rand(2, 8));
            }

            $report = RescueReport::create([
                'user_id'          => $userId,
                'volunteer_id'     => $volunteerId,
                'latitude'         => $location['lat'],
                'longitude'        => $location['lng'],
                'location_address' => $location['address'],
                'severity_level'  => $severity,
                'animal_type'      => $animal->type,
                'health_status'    => $healthStatus,
                'description'      => $description,
                'status'           => $status,
                'created_at'       => $createdAt,
                'updated_at'       => $updatedAt,
            ]);

            // Images under: rescue_reports/{report_id}/
            $this->attachReportImages($report->id, $animal->id, $createdAt);
        }

        $this->command->info('✅ Rescue reports seeded successfully.');
        $this->command->info('60 reports created: 20 resolved, 10 reported, 10 dispatched, 10 on_site, 10 in_clinic.');
    }

    /*
    |--------------------------------------------------------------------------
    | Attach Images
    | Target structure:
    | storage/app/public/rescue_reports/{report_id}/photo1.jpg
    |--------------------------------------------------------------------------
    */
    private function attachReportImages(int $reportId, int $animalId, $createdAt): void
    {
        // Source folders (first existing wins)
        $sourceCandidates = [
            database_path("seeders/assets/rescue-reports/{$animalId}"),
            storage_path("app/public/rescue_reports_source/{$animalId}"),
            database_path("seeders/assets/rescue_reports/{$animalId}"),
        ];

        $sourceDir = null;
        foreach ($sourceCandidates as $dir) {
            if (File::isDirectory($dir)) {
                $sourceDir = $dir;
                break;
            }
        }

        if (!$sourceDir) {
            $this->command->warn("No source image folder found for animal ID {$animalId}");
            return;
        }

        $files = collect(File::files($sourceDir))
            ->filter(function ($file) {
                return in_array(strtolower($file->getExtension()), [
                    'jpg', 'jpeg', 'png', 'webp', 'gif', 'avif',
                ]);
            })
            ->values();

        if ($files->isEmpty()) {
            $this->command->warn("No images found for animal ID {$animalId}");
            return;
        }

        $targetDir = "rescue_reports/{$reportId}";
        Storage::disk('public')->makeDirectory($targetDir);

        foreach ($files as $index => $file) {
            $extension  = strtolower($file->getExtension());
            $fileName   = 'photo' . ($index + 1) . '.' . $extension;
            $targetPath = "{$targetDir}/{$fileName}";

            Storage::disk('public')->put(
                $targetPath,
                File::get($file->getPathname())
            );

            RescueReportImage::create([
                'rescue_report_id' => $reportId,
                'image_path'       => $targetPath, // relative path
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Realistic Descriptions
    |--------------------------------------------------------------------------
    */
    private function generateDescription(string $animalType, string $healthStatus, string $status): string
    {
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

        $list = $descriptions[$healthStatus] ?? $descriptions['other'];

        return $list[array_rand($list)];
    }
}
