<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LostFound;
use App\Models\LostFoundPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LostFoundSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get users
        |--------------------------------------------------------------------------
        |
        | We use existing users instead of creating fake users.
        |
        */

        $users = User::limit(5)->get();

        if ($users->isEmpty()) {
            $this->command->error(
                'No users found. Please create users before running LostFoundSeeder.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Clear old Lost & Found data
        |--------------------------------------------------------------------------
        */

        LostFoundPhoto::query()->delete();
        LostFound::query()->delete();

        /*
        |--------------------------------------------------------------------------
        | Remove old Lost & Found images
        |--------------------------------------------------------------------------
        */

        Storage::disk('public')->deleteDirectory('lost-found');

        /*
        |--------------------------------------------------------------------------
        | Lost & Found Posts
        |--------------------------------------------------------------------------
        */

        $posts = [

            [
                'post_type' => 'lost',
                'animal_type' => 'dog',
                'name' => 'Max',
                'breed' => 'Golden Retriever',
                'gender' => 'male',
                'size' => 'large',
                'age' => '3 years',
                'color' => 'Golden',
                'description' =>
                    'Max went missing from the neighborhood yesterday evening. ' .
                    'He is friendly and usually responds when his name is called. ' .
                    'Please contact us if you have seen him anywhere nearby.',
                'location_description' => 'Near Central Park',
                'incident_at' =>'2026-06-04 12:30:00',
                'latitude' => 52.3676,
                'longitude' => 4.9041,
                'contact_phone' => '+31612345678',
                'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'Small white mark on his chest',
                'collar_tags' => 'Blue collar with metal tag',
                'microchipped' => true,
                'neutered' => true,
                'temperament' => 'Friendly and playful',
                'images' => [
                    'dog1.avif',
                    'dog2.avif',
                ],
            ],

            [
                'post_type' => 'found',
                'animal_type' => 'cat',
                'name' => 'Unknown',
                'breed' => 'British Shorthair',
                'gender' => 'female',
                'size' => 'medium',
                'age' => 'Approximately 2 years',
                'color' => 'Gray',
                'description' =>
                    'A friendly gray cat was found near the residential area. ' .
                    'She appears healthy and well cared for. ' .
                    'We are looking for her owner.',
                  'contact_email' => 'rima@platform.com',
                'location_description' => 'Residential Street',
                'incident_at' =>'2026-07-07 18:30:00',
                'latitude' => 52.3702,
                'longitude' => 4.8952,
                'contact_phone' => '+31623456789',
                'distinctive_marks' => 'White patch under the chin',
                'collar_tags' => null,
                'microchipped' => false,
                'neutered' => true,
                'temperament' => 'Calm and affectionate',
                'images' => [
                    'cat1.avif',
                    'cat2.avif',
                    'cat3.avif',
                ],
            ],

            [
                'post_type' => 'lost',
                'animal_type' => 'cat',
                'name' => 'Luna',
                'breed' => 'Persian',
                'gender' => 'female',
                'size' => 'medium',
                'age' => '4 years',
                'color' => 'White',
                'description' =>
                    'Luna disappeared from our home two days ago. ' .
                    'She has long white fur and is usually shy around strangers. ' .
                    'Any information about her location would be greatly appreciated.',
                'contact_email' => 'rima@platform.com',
                'location_description' => 'Near the City Center',
                'incident_at' =>'2026-08-04 18:30:00',
                'latitude' => 52.3738,
                'longitude' => 4.8910,
                'contact_phone' => '+31634567890',
                'distinctive_marks' => 'Long white fur and blue eyes',
                'collar_tags' => 'Pink collar',
                'microchipped' => true,
                'neutered' => true,
                'temperament' => 'Shy',
                'images' => [
                    'cat2.avif',
                ],
            ],

            [
                'post_type' => 'found',
                'animal_type' => 'dog',
                'name' => 'Unknown',
                'breed' => 'Labrador',
                'gender' => 'male',
                'size' => 'large',
                'age' => 'Approximately 5 years',
                'color' => 'Black',
                'description' =>
                    'A black Labrador was found walking alone near the main road. ' .
                    'He is very friendly and seems comfortable around people. ' .
                    'We are trying to find his owner.',
                'contact_email' => 'rima@platform.com',
                'location_description' => 'Main Road',
                'incident_at' =>'2026-01-01 18:30:00',
                'latitude' => 52.3600,
                'longitude' => 4.9150,
                'contact_phone' => '+31645678901',
                'distinctive_marks' => 'White spot on the front paw',
                'collar_tags' => 'Black collar',
                'microchipped' => false,
                'neutered' => false,
                'temperament' => 'Friendly',
                'images' => [
                    'dog2.avif',
                    'dog3.avif',
                ],
            ],

            [
                'post_type' => 'lost',
                'animal_type' => 'bird',
                'name' => 'Kiwi',
                'breed' => 'Parakeet',
                'gender' => 'unknown',
                'size' => 'small',
                'age' => '1 year',
                'color' => 'Green and yellow',
                'description' =>
                    'Kiwi flew away from home while the window was open. ' .
                    'He is a small green and yellow parakeet and may be scared. ' .
                    'Please contact us if you spot him.',
                'location_description' => 'Residential Area',
                'incident_at' =>'2026-08-01 18:30:00',
                'latitude' => 52.3615,
                'longitude' => 4.9000,
                'contact_phone' => '+31656789012',
                'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'Yellow feathers around the head',
                'collar_tags' => null,
                'microchipped' => false,
                'neutered' => false,
                'temperament' => 'Active',
                'images' => [
                    'bird1.avif',
                    'bird2.avif',
                ],
            ],

            [
                'post_type' => 'found',
                'animal_type' => 'rabbit',
                'name' => 'Unknown',
                'breed' => 'Mini Lop',
                'gender' => 'female',
                'size' => 'small',
                'age' => 'Approximately 1 year',
                'color' => 'Brown and white',
                'description' =>
                    'A small rabbit was found in a public garden. ' .
                    'It appears to be domesticated and comfortable around people. ' .
                    'We are looking for the owner.',
                'location_description' => 'Public Garden',
                'incident_at' =>'2026-08-02 01:00:00',
                'latitude' => 52.3650,
                'longitude' => 4.9100,
                'contact_phone' => '+31667890123',
                'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'White stripe on forehead',
                'collar_tags' => null,
                'microchipped' => false,
                'neutered' => false,
                'temperament' => 'Calm',
                'images' => [
                    'rabbit1.avif',
                ],
            ],

            [
                'post_type' => 'lost',
                'animal_type' => 'dog',
                'name' => 'Buddy',
                'breed' => 'Beagle',
                'gender' => 'male',
                'size' => 'medium',
                'age' => '2 years',
                'color' => 'Brown, white and black',
                'description' =>
                    'Buddy went missing while walking with his owner. ' .
                    'He is energetic but friendly and may approach people looking for food. ' .
                    'Please contact us immediately if you see him.',
                'location_description' => 'Near Riverside',
                'incident_at' =>'2026-08-04 10:15:00',
                'latitude' => 52.3550,
                'longitude' => 4.9050,
                'contact_phone' => '+31678901234',
                'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'Brown patch around right eye',
                'collar_tags' => 'Red collar',
                'microchipped' => true,
                'neutered' => true,
                'temperament' => 'Energetic',
                'images' => [
                    'dog1.avif',
                    'dog3.avif',
                ],
            ],

            [
                'post_type' => 'found',
                'animal_type' => 'cat',
                'name' => 'Unknown',
                'breed' => 'Domestic Shorthair',
                'gender' => 'male',
                'size' => 'small',
                'age' => 'Approximately 1 year',
                'color' => 'Orange',
                'description' =>
                    'A young orange cat was found near a shopping area. ' .
                    'He is very friendly and seems used to living with people. ' .
                    'The owner is currently unknown.',
                'location_description' => 'Shopping Area',
                'incident_at' =>'2026-08-02 11:30:00',
                'latitude' => 52.3680,
                'longitude' => 4.9120,
                'contact_phone' => '+31689012345',
                'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'White paws',
                'collar_tags' => null,
                'microchipped' => false,
                'neutered' => false,
                'temperament' => 'Very friendly',
                'images' => [
                    'cat1.avif',
                    'cat3.avif',
                ],
            ],

            [
                'post_type' => 'lost',
                'animal_type' => 'other',
                'name' => 'Coco',
                'breed' => 'Guinea Pig',
                'gender' => 'female',
                'size' => 'small',
                'age' => '8 months',
                'color' => 'White and brown',
                'description' =>
                    'Coco escaped from her enclosure and may be hiding nearby. ' .
                    'She is small and usually calm around people. ' .
                    'Please contact us if you find her.',
                'location_description' => 'Near Residential Buildings',
                'incident_at' =>'2026-07-29 18:20:00',
                'latitude' => 52.3590,
                'longitude' => 4.8950,
                'contact_phone' => '+31690123456',
                'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'Brown patch on the back',
                'collar_tags' => null,
                'microchipped' => false,
                'neutered' => false,
                'temperament' => 'Calm',
                'images' => [
                    'rabbit2.avif',
                ],
            ],

            [
                'post_type' => 'found',
                'animal_type' => 'dog',
                'name' => 'Unknown',
                'breed' => 'Mixed Breed',
                'gender' => 'female',
                'size' => 'medium',
                'age' => 'Approximately 3 years',
                'color' => 'Brown',
                'description' =>
                    'A brown mixed-breed dog was found near a local park. ' .
                    'She is calm and appears healthy. ' .
                    'We are searching for her owner and would appreciate any information.',
                'location_description' => 'Local Park',
                'incident_at' =>'2026-08-04 18:30:00',
                'latitude' => 52.3710,
                'longitude' => 4.9180,
                'contact_phone' => '+31601234567',
                  'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'White mark on the nose',
                'collar_tags' => 'Green collar',
                'microchipped' => false,
                'neutered' => true,
                'temperament' => 'Calm and friendly',
                'images' => [
                    'dog2.avif',
                    'dog3.avif',
                ],
            ],

            [
                'post_type' => 'lost',
                'animal_type' => 'cat',
                'name' => 'Milo',
                'breed' => 'Siamese',
                'gender' => 'male',
                'size' => 'medium',
                'age' => '3 years',
                'color' => 'Cream and brown',
                'description' =>
                    'Milo disappeared after going outside in the afternoon. ' .
                    'He has distinctive dark ears and blue eyes. ' .
                    'He may be hiding around nearby houses.',
                'location_description' => 'Near Residential Area',
                'incident_at' =>'2026-08-04 18:30:00',
                'latitude' => 52.3630,
                'longitude' => 4.9070,
                'contact_phone' => '+31612398765',
                  'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'Blue eyes and dark ears',
                'collar_tags' => 'Blue collar',
                'microchipped' => true,
                'neutered' => true,
                'temperament' => 'Quiet',
                'images' => [
                    'cat1.avif',
                    'cat2.avif',
                ],
            ],

            [
                'post_type' => 'found',
                'animal_type' => 'bird',
                'name' => 'Unknown',
                'breed' => 'Cockatiel',
                'gender' => 'unknown',
                'size' => 'small',
                'age' => 'Unknown',
                'color' => 'Gray and yellow',
                'description' =>
                    'A cockatiel was found near a residential building. ' .
                    'The bird appears domesticated and comfortable around people. ' .
                    'We are looking for its owner.',
                'location_description' => 'Residential Building',
                'incident_at' =>'2026-08-04 17:00:00',
                'latitude' => 52.3690,
                'longitude' => 4.9000,
                'contact_phone' => '+31623498765',
                  'contact_email' => 'rima@platform.com',
                'distinctive_marks' => 'Yellow face and orange cheeks',
                'collar_tags' => null,
                'microchipped' => false,
                'neutered' => false,
                'temperament' => 'Calm',
                'images' => [
                    'bird1.avif',
                ],
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Posts + Photos
        |--------------------------------------------------------------------------
        */

        foreach ($posts as $index => $postData) {

            $imageFiles = $postData['images'];

            unset($postData['images']);

            // Assign users in rotation
            $user = $users[$index % $users->count()];

            $postData['user_id'] = $user->id;
            $postData['status'] = 'open';
            $postData['views'] = rand(0, 50);

            $post = LostFound::create($postData);

            /*
            |--------------------------------------------------------------------------
            | Copy images to storage
            |--------------------------------------------------------------------------
            */

            foreach ($imageFiles as $imageIndex => $imageFile) {

                $sourcePath = database_path(
                    'seeders/assets/lost-found/' . $imageFile
                );

                if (!file_exists($sourcePath)) {
                    $this->command->warn(
                        "Image not found: {$sourcePath}"
                    );

                    continue;
                }

                $extension = pathinfo($imageFile, PATHINFO_EXTENSION);

                $fileName = uniqid() . '.' . $extension;

                $storagePath = 'lost-found/' . $post->id . '/' . $fileName;

                Storage::disk('public')->put(
                    $storagePath,
                    file_get_contents($sourcePath)
                );

                LostFoundPhoto::create([
                    'lost_found_id' => $post->id,
                    'photo_url' => $storagePath,
                    'is_main' => $imageIndex === 0,
                    'order_number' => $imageIndex,
                ]);
            }
        }

        $this->command->info(
            'Lost & Found posts and photos seeded successfully.'
        );
    }
}