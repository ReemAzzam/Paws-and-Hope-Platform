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
        $users = User::role('regular_user')->limit(15)->get();

        if ($users->isEmpty()) {
            $users = User::limit(10)->get();
        }

        if ($users->isEmpty()) {
            $this->command->error('No users found. Seed users first.');
            return;
        }

        LostFoundPhoto::query()->delete();
        LostFound::query()->delete();
        Storage::disk('public')->deleteDirectory('lost-found');

        $posts = $this->posts();

        foreach ($posts as $index => $postData) {
            $imageFiles = $postData['images'];
            unset($postData['images']);

            $user = $users[$index % $users->count()];

            $postData['user_id'] = $user->id;
            $postData['status'] = $postData['status'] ?? 'open';
            $postData['views'] = $postData['views'] ?? rand(3, 80);

            $post = LostFound::create($postData);

            foreach ($imageFiles as $imageIndex => $imageFile) {
                $sourcePath = $this->resolveImagePath($imageFile);

                if (!$sourcePath) {
                    $this->command->warn("Image not found: {$imageFile}");
                    continue;
                }

                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                $fileName = uniqid() . '.' . $extension;
                $storagePath = 'lost-found/' . $post->id . '/' . $fileName;

                Storage::disk('public')->put(
                    $storagePath,
                    file_get_contents($sourcePath)
                );

                LostFoundPhoto::create([
                    'lost_found_id' => $post->id,
                    'photo_url'     => $storagePath,
                    'is_main'       => $imageIndex === 0,
                    'order_number'  => $imageIndex,
                ]);
            }
        }

        $this->command->info('✅ 35 Lost & Found posts seeded successfully.');
    }

    private function resolveImagePath(string $imageFile): ?string
    {
        $base = database_path('seeders/assets/lost-found/');
        $name = pathinfo($imageFile, PATHINFO_FILENAME);

        foreach ([$imageFile, $name . '.png', $name . '.jpg', $name . '.jpeg', $name . '.avif', $name . '.webp'] as $candidate) {
            $full = $base . $candidate;
            if (file_exists($full)) {
                return $full;
            }
        }

        return null;
    }

    private function posts(): array
    {
        // Damascus area coordinates
        $locations = [
            ['Damascus - Mezzeh', 33.5100, 36.2600],
            ['Damascus - Abu Rummaneh', 33.5200, 36.2800],
            ['Damascus - Midan', 33.5000, 36.2900],
            ['Damascus - Qassaa', 33.5150, 36.3100],
            ['Damascus - Kafar Souseh', 33.5050, 36.2700],
            ['Jaramana', 33.4870, 36.3450],
            ['Qudsaya', 33.5400, 36.2200],
            ['Harasta', 33.5580, 36.3650],
            ['Damascus - Shaalan', 33.5180, 36.2950],
            ['Damascus - Rukn Al Din', 33.5350, 36.3050],
        ];

        $data = [
            // 1-10
            ['lost', 'dog', 'Max', 'Golden Retriever', 'male', 'large', '3 years', 'Golden', 'Max went missing near the park yesterday evening. He is friendly and responds to his name.', 'Small white mark on chest', 'Blue collar with metal tag', true, true, 'Friendly and playful', ['dog1', 'dog2'], 'open'],
            ['found', 'cat', 'Unknown', 'British Shorthair', 'female', 'medium', 'About 2 years', 'Gray', 'A calm gray cat was found near residential buildings. She seems healthy and well cared for.', 'White patch under the chin', null, false, true, 'Calm and affectionate', ['cat1', 'cat2', 'cat3'], 'open'],
            ['lost', 'cat', 'Luna', 'Persian', 'female', 'medium', '4 years', 'White', 'Luna disappeared from home two days ago. She has long white fur and is shy around strangers.', 'Long white fur and blue eyes', 'Pink collar', true, true, 'Shy', ['cat2', 'cat5'], 'open'],
            ['found', 'dog', 'Unknown', 'Labrador', 'male', 'large', 'About 5 years', 'Black', 'A black Labrador was found walking alone near the main road. Very friendly with people.', 'White spot on front paw', 'Black collar', false, false, 'Friendly', ['dog2', 'dog3'], 'open'],
            ['lost', 'bird', 'Kiwi', 'Parakeet', 'unknown', 'small', '1 year', 'Green and yellow', 'Kiwi flew out of an open window. Small green-yellow parakeet, may be scared.', 'Yellow feathers around the head', null, false, false, 'Active', ['bird1', 'bird2'], 'open'],
            ['found', 'rabbit', 'Unknown', 'Mini Lop', 'female', 'small', 'About 1 year', 'Brown and white', 'A small domestic rabbit was found in a public garden and appears comfortable around people.', 'White stripe on forehead', null, false, false, 'Calm', ['rabbit1'], 'open'],
            ['lost', 'dog', 'Buddy', 'Beagle', 'male', 'medium', '2 years', 'Brown, white and black', 'Buddy slipped the leash during a morning walk. Energetic but friendly.', 'Brown patch around right eye', 'Red collar', true, true, 'Energetic', ['dog12', 'dog14'], 'open'],
            ['found', 'cat', 'Unknown', 'Domestic Shorthair', 'male', 'small', 'About 1 year', 'Orange', 'Young orange cat found near a shopping street. Very friendly and approachable.', 'White paws', null, false, false, 'Very friendly', ['cat8', 'cat13'], 'open'],
            ['lost', 'rabbit', 'Snow', 'Holland Lop', 'female', 'small', '10 months', 'White', 'Snow escaped from a backyard enclosure and may be hiding nearby.', 'Pink nose and floppy ears', null, false, false, 'Gentle', ['rabbit2', 'rabbit3'], 'open'],
            ['found', 'dog', 'Unknown', 'Mixed Breed', 'female', 'medium', 'About 3 years', 'Brown', 'Brown mixed-breed dog found near a local park. Calm and appears healthy.', 'White mark on the nose', 'Green collar', false, true, 'Calm and friendly', ['dog15', 'dog18'], 'open'],

            // 11-20
            ['lost', 'cat', 'Milo', 'Siamese', 'male', 'medium', '3 years', 'Cream and brown', 'Milo left through a balcony door and has not returned. Distinctive dark ears and blue eyes.', 'Blue eyes and dark ears', 'Blue collar', true, true, 'Quiet', ['cat15', 'cat17'], 'open'],
            ['found', 'bird', 'Unknown', 'Cockatiel', 'unknown', 'small', 'Unknown', 'Gray and yellow', 'A cockatiel was found near a residential building. Appears domesticated.', 'Yellow face and orange cheeks', null, false, false, 'Calm', ['bird2', 'bird3'], 'open'],
            ['lost', 'dog', 'Rocky', 'German Shepherd', 'male', 'large', '4 years', 'Black and tan', 'Rocky ran after a street noise and did not come back. Trained but nervous in traffic.', 'Scar on left hind leg', 'Thick black collar', true, true, 'Loyal and alert', ['dog20', 'dog1'], 'open'],
            ['found', 'cat', 'Unknown', 'Tabby', 'female', 'medium', 'About 2 years', 'Brown tabby', 'Tabby cat found sitting outside a bakery for hours. No collar visible.', 'M-shaped marking on forehead', null, false, true, 'Friendly', ['cat3', 'cat5'], 'open'],
            ['lost', 'bird', 'Sunny', 'Lovebird', 'unknown', 'small', '2 years', 'Peach and green', 'Sunny escaped during cage cleaning. Very vocal and may stay near trees.', 'Bright peach face', null, false, false, 'Social', ['bird1', 'bird3'], 'open'],
            ['found', 'dog', 'Unknown', 'Husky Mix', 'male', 'large', 'About 3 years', 'Gray and white', 'Husky-mix found near highway exit. Needs secure space and owner contact.', 'One blue eye', 'Broken chain collar', false, false, 'Active', ['dog3', 'dog12'], 'open'],
            ['lost', 'cat', 'Nala', 'Maine Coon Mix', 'female', 'large', '5 years', 'Brown and cream', 'Nala is an indoor cat that slipped out during maintenance work.', 'Tufted ears and bushy tail', 'Purple collar', true, true, 'Gentle', ['cat1', 'cat8'], 'open'],
            ['found', 'rabbit', 'Unknown', 'Rex', 'male', 'small', 'About 2 years', 'Gray', 'Rex-type rabbit found near apartment gardens. Soft coat and calm behavior.', 'Darker ears', null, false, false, 'Calm', ['rabbit1', 'rabbit2'], 'resolved'],
            ['lost', 'dog', 'Daisy', 'Cocker Spaniel', 'female', 'medium', '6 years', 'Golden', 'Daisy went missing after fireworks noise. May hide under cars or stairs.', 'Long ears and soft coat', 'Yellow tag collar', true, true, 'Sensitive', ['dog14', 'dog15'], 'open'],
            ['found', 'cat', 'Unknown', 'Black Domestic', 'male', 'medium', 'About 4 years', 'Black', 'Black cat found in a garage overnight. Healthy appetite and calm.', 'Yellow eyes', null, false, true, 'Calm', ['cat13', 'cat15'], 'open'],

            // 21-30
            ['lost', 'dog', 'Charlie', 'Poodle Mix', 'male', 'small', '2 years', 'Cream', 'Charlie ran out of a building entrance and is not used to busy streets.', 'Curly coat', 'Light blue collar', true, false, 'Playful', ['dog18', 'dog2'], 'open'],
            ['found', 'bird', 'Unknown', 'Budgerigar', 'unknown', 'small', 'Unknown', 'Blue and white', 'Blue budgie found on a balcony. Appears tame and approaches hands.', 'Blue body with white head', null, false, false, 'Tame', ['bird2'], 'open'],
            ['lost', 'cat', 'Oliver', 'Orange Tabby', 'male', 'medium', '1 year', 'Orange', 'Oliver is microchipped and disappeared after a family visit.', 'Striped tail', 'Orange collar', true, false, 'Curious', ['cat17', 'cat2'], 'open'],
            ['found', 'dog', 'Unknown', 'Street Dog', 'female', 'medium', 'About 2 years', 'Tan', 'Friendly tan dog found near school gate. Looking for owner or shelter help.', 'Cropped left ear tip', null, false, false, 'Friendly', ['dog1', 'dog20'], 'open'],
            ['lost', 'rabbit', 'Mocha', 'Lionhead', 'female', 'small', '1 year', 'Brown', 'Mocha escaped while the hutch was being cleaned. Indoor rabbit.', 'Mane-like fur around head', null, false, false, 'Shy', ['rabbit3'], 'open'],
            ['found', 'cat', 'Unknown', 'Calico', 'female', 'medium', 'About 3 years', 'White, orange and black', 'Calico cat found near clinic parking. Well-groomed, likely has a home.', 'Tricolor coat', 'Broken pink collar', false, true, 'Affectionate', ['cat5', 'cat8'], 'open'],
            ['lost', 'dog', 'Bruno', 'Boxer', 'male', 'large', '5 years', 'Brown', 'Bruno went missing during a power cut when the gate was left open.', 'White chest blaze', 'Thick brown collar', true, true, 'Protective', ['dog12', 'dog3'], 'closed'],
            ['found', 'bird', 'Unknown', 'Canary', 'unknown', 'small', 'Unknown', 'Yellow', 'Yellow canary found indoors after flying through an open shop door.', 'Bright yellow plumage', null, false, false, 'Active', ['bird1'], 'open'],
            ['lost', 'cat', 'Bella', 'White Domestic', 'female', 'small', '2 years', 'White', 'Bella has asthma history and needs medication. Urgent if seen.', 'Pink nose and pale eyes', 'Medical alert tag', true, true, 'Quiet', ['cat1', 'cat3'], 'open'],
            ['found', 'dog', 'Unknown', 'Jack Russell Mix', 'male', 'small', 'About 4 years', 'White and brown', 'Small energetic dog found near market. No microchip detected yet.', 'Brown patches on back', 'Red harness remnant', false, true, 'Energetic', ['dog14', 'dog18'], 'open'],

            // 31-35
            ['lost', 'dog', 'Rex', 'Rottweiler', 'male', 'large', '3 years', 'Black and tan', 'Rex is trained but was startled by construction noise and bolted.', 'Tan eyebrow marks', 'Heavy duty collar', true, true, 'Obedient', ['dog20', 'dog15'], 'open'],
            ['found', 'cat', 'Unknown', 'Gray Tabby', 'unknown', 'medium', 'Unknown', 'Gray', 'Gray tabby found sleeping under a parked car. Cautious but not aggressive.', 'White belly', null, false, false, 'Cautious', ['cat13', 'cat15'], 'open'],
            ['lost', 'rabbit', 'Pepper', 'Dutch', 'male', 'small', '1.5 years', 'Black and white', 'Pepper has distinctive Dutch markings and is missing from a rooftop garden.', 'White blaze and black saddle', null, false, false, 'Friendly', ['rabbit1', 'rabbit2'], 'open'],
            ['found', 'dog', 'Unknown', 'Spaniel Mix', 'female', 'medium', 'About 6 years', 'Brown and white', 'Older spaniel-mix found near river walk. Limping slightly on rear leg.', 'Soft long ears', 'Faded ID tag', false, true, 'Gentle', ['dog2', 'dog15'], 'resolved'],
            ['lost', 'cat', 'Simba', 'Ginger', 'male', 'large', '7 years', 'Ginger', 'Simba is an older indoor cat that slipped out at night. Needs soft food diet.', 'Scar on right ear', 'Green reflective collar', true, true, 'Calm', ['cat17', 'cat8'], 'open'],
        ];

        $result = [];

        foreach ($data as $i => $row) {
            $loc = $locations[$i % count($locations)];

            $result[] = [
                'post_type'            => $row[0],
                'animal_type'          => $row[1],
                'name'                 => $row[2],
                'breed'                => $row[3],
                'gender'               => $row[4],
                'size'                 => $row[5],
                'age'                  => $row[6],
                'color'                => $row[7],
                'description'          => $row[8] . ' Please contact us with any information.',
                'location_description' => $loc[0],
                'incident_at'          => now()->subDays(rand(1, 40))->setTime(rand(7, 21), rand(0, 59)),
                'latitude'             => $loc[1] + (rand(-20, 20) / 10000),
                'longitude'            => $loc[2] + (rand(-20, 20) / 10000),
                'contact_phone'        => '09' . rand(10000000, 99999999),
                'contact_email'        => 'lostfound' . ($i + 1) . '@platform.com',
                'distinctive_marks'    => $row[9],
                'collar_tags'          => $row[10],
                'microchipped'         => $row[11],
                'neutered'             => $row[12],
                'temperament'          => $row[13],
                'status'               => $row[15],
                'views'                => rand(5, 90),
                'images'               => $row[14],
            ];
        }

        return $result;
    }
}