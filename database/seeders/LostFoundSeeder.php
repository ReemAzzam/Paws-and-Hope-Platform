<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LostFound;
use App\Models\LostFoundPhoto;
use Illuminate\Support\Facades\DB;

class LostFoundSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        LostFoundPhoto::truncate();
        LostFound::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $posts = [
            // 1. Max - Lost Dog
            [
                'post_type'            => 'lost',
                'animal_type'          => 'dog',
                'name'                 => 'Max',
                'breed'                => 'Golden Retriever',
                'gender'               => 'male',
                'size'                 => 'large',
                'age'                  => '2 Years',
                'color'                => 'Golden',
                'description'          => 'Max is a very friendly and energetic golden retriever. He was last seen running towards the main road near Al Mazzeh park while wearing a blue collar. He responds to his name and loves people.',
                'location_description' => 'Al Mazzeh Park, Damascus, Syria',
                'latitude'             => 33.5102,
                'longitude'            => 36.2625,
                'contact_phone'        => '+963 944 123 456',
                'distinctive_marks'    => 'White spot on front chest',
                'collar_tags'          => 'Blue collar, no tag',
                'microchipped'         => false,
                'neutered'             => true,
                'temperament'          => 'Friendly, Playful',
                'user_id'              => 1,
                'created_at'           => now()->subDays(50),
            ],

            // 2. Unknown Cat - Found
            [
                'post_type'            => 'found',
                'animal_type'          => 'cat',
                'name'                 => null,
                'breed'                => 'Shirazi',
                'gender'               => 'female',
                'size'                 => 'small',
                'age'                  => '6 Months',
                'color'                => 'White/Grey',
                'description'          => 'Found this beautiful cat wandering alone. Very clean, seems like a house pet. Safe with us for now.',
                'location_description' => 'Abu Rummaneh, Damascus, Syria',
                'latitude'             => 33.5192,
                'longitude'            => 36.2835,
                'contact_phone'        => '+963 933 777 888',
                'distinctive_marks'    => 'Green eyes, very fluffy tail',
                'collar_tags'          => null,
                'microchipped'         => false,
                'neutered'             => false,
                'temperament'          => 'Scared, Quiet',
                'user_id'              => 2,
                'created_at'           => now()->subDays(30),
            ],

            // 3. Bunny - Lost Rabbit
            [
                'post_type'            => 'lost',
                'animal_type'          => 'rabbit',
                'name'                 => 'Bunny',
                'breed'                => 'Angora Rabbit',
                'gender'               => 'male',
                'size'                 => 'small',
                'age'                  => '1 Year',
                'color'                => 'Pure White',
                'description'          => 'Our beloved Angora rabbit Bunny went missing from our home backyard in Dummar.',
                'location_description' => 'Dummar Project, Damascus, Syria',
                'latitude'             => 33.5284,
                'longitude'            => 36.2301,
                'contact_phone'        => '+963 955 444 333',
                'distinctive_marks'    => 'Long floppy ears, very soft long fur',
                'collar_tags'          => null,
                'microchipped'         => false,
                'neutered'             => false,
                'temperament'          => 'Gentle, Timid',
                'user_id'              => 3,
                'created_at'           => now()->subDays(12),
            ],

            // 4. Rocky - Found Dog
            [
                'post_type'            => 'found',
                'animal_type'          => 'dog',
                'name'                 => 'Rocky',
                'breed'                => 'German Shepherd',
                'gender'               => 'male',
                'size'                 => 'large',
                'age'                  => '3 Years',
                'color'                => 'Black & Tan',
                'description'          => 'Found this majestic German Shepherd tonight near Cham City Center.',
                'location_description' => 'Kfar Souseh, Damascus, Syria',
                'latitude'             => 33.5015,
                'longitude'            => 36.2692,
                'contact_phone'        => '+963 988 555 222',
                'distinctive_marks'    => 'Very well trained, wears a leather collar',
                'collar_tags'          => 'Wears a heavy leather collar but the nameplate is scratched',
                'microchipped'         => true,
                'neutered'             => true,
                'temperament'          => 'Calm, Alert, Obedient',
                'user_id'              => 4,
                'created_at'           => now()->subDays(10),
            ],

            // 5. Bella - Lost Cat
            [
                'post_type'            => 'lost',
                'animal_type'          => 'cat',
                'name'                 => 'Bella',
                'breed'                => 'Siamese',
                'gender'               => 'female',
                'size'                 => 'small',
                'age'                  => '1.5 Years',
                'color'                => 'Cream/Brown',
                'description'          => 'Bella is a beautiful Siamese cat with distinctive blue eyes.',
                'location_description' => 'Al-Shaalan, Damascus, Syria',
                'latitude'             => 33.5156,
                'longitude'            => 36.2895,
                'contact_phone'        => '+963 933 111 222',
                'distinctive_marks'    => 'Deep blue eyes and dark brown paws',
                'collar_tags'          => 'Pink collar with a small bell',
                'microchipped'         => true,
                'neutered'             => true,
                'temperament'          => 'Vocal, Affectionate, Curious',
                'user_id'              => 5,
                'created_at'           => now()->subDays(8),
            ],

            // 6. Husky - Found Dog
            [
                'post_type'            => 'found',
                'animal_type'          => 'dog',
                'name'                 => null,
                'breed'                => 'Siberian Husky',
                'gender'               => 'male',
                'size'                 => 'large',
                'age'                  => 'About 1 Year',
                'color'                => 'Black & White',
                'description'          => 'Found this gorgeous Husky puppy wandering alone in Dummar.',
                'location_description' => 'Project Dummar, Damascus, Syria',
                'latitude'             => 33.5312,
                'longitude'            => 36.2245,
                'contact_phone'        => '+963 955 888 999',
                'distinctive_marks'    => 'One blue eye and one brown eye',
                'collar_tags'          => 'Red nylon collar',
                'microchipped'         => false,
                'neutered'             => false,
                'temperament'          => 'Energetic, Playful, Friendly',
                'user_id'              => 6,
                'created_at'           => now()->subDays(6),
            ],

            // 7. Oliver - Lost Cat
            [
                'post_type'            => 'lost',
                'animal_type'          => 'cat',
                'name'                 => 'Oliver',
                'breed'                => 'British Shorthair',
                'gender'               => 'male',
                'size'                 => 'medium',
                'age'                  => '3 Years',
                'color'                => 'Solid Grey',
                'description'          => 'Our beloved grey British Shorthair Oliver went missing last night.',
                'location_description' => 'Malki, Damascus, Syria',
                'latitude'             => 33.5188,
                'longitude'            => 36.2762,
                'contact_phone'        => '+963 944 555 111',
                'distinctive_marks'    => 'Chubby cheeks and intense amber eyes',
                'collar_tags'          => null,
                'microchipped'         => true,
                'neutered'             => true,
                'temperament'          => 'Calm, Lazy, Independent',
                'user_id'              => 7,
                'created_at'           => now()->subDays(5),
            ],

            // 8. Charlie - Found Parrot
            [
                'post_type'            => 'found',
                'animal_type'          => 'bird',
                'name'                 => 'Charlie',
                'breed'                => 'Cockatiel',
                'gender'               => 'male',
                'size'                 => 'small',
                'age'                  => null,
                'color'                => 'Grey & Yellow',
                'description'          => 'A friendly yellow and grey Cockatiel parrot flew straight into our house.',
                'location_description' => 'Bab Touma, Damascus, Syria',
                'latitude'             => 33.5134,
                'longitude'            => 36.3123,
                'contact_phone'        => '+963 999 222 333',
                'distinctive_marks'    => 'Bright orange circular patches on cheeks',
                'collar_tags'          => 'Small silver ring on left leg',
                'microchipped'         => false,
                'neutered'             => false,
                'temperament'          => 'Whistles melodies, Gentle',
                'user_id'              => 8,
                'created_at'           => now()->subDays(4),
            ],
        ];

        foreach ($posts as $index => $data) {
            $post = LostFound::create($data);

            // إضافة الصور
            $imageList = $this->getMockImages($index + 1);
            foreach ($imageList as $i => $imgPath) {
                LostFoundPhoto::create([
                    'lost_found_id' => $post->id,
                    'photo_url'     => $imgPath,
                    'is_main'       => $i === 0,
                    'order_number'  => $i,
                ]);
            }
        }


    }

    private function getMockImages($postId)
    {
        $images = [
            1 => ['/images/max1.png', '/images/max2.png', '/images/max3.png'],
            2 => ['/images/catshirazi1.png', '/images/catshirazi2.png', '/images/catshirazi3.png', '/images/catshirazi4.png'],
            3 => ['/images/angorarabbit2.png', '/images/angorarabbit3.png', '/images/angorarabbit4.png'],
            4 => ['/images/german1.png', '/images/german2.png', '/images/german3.png', '/images/german4.png'],
            5 => ['/images/bella1.png', '/images/bella2.png', '/images/bella3.png', '/images/bella4.png'],
            6 => ['/images/husky1.png', '/images/husky2.png', '/images/husky3.png', '/images/husky4.png'],
            7 => ['/images/oliver1.png', '/images/oliver2.png', '/images/oliver3.png', '/images/oliver4.png'],
            8 => ['/images/Charlie1.png', '/images/Charlie2.png', '/images/Charlie3.png', '/images/Charlie4.png'],
        ];

        return $images[$postId] ?? [];
    }
}
