<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommunityPost;
use App\Models\PostLike;
use App\Models\User;
use App\Models\Animal;
use App\Models\PostCategory;

class CommunityPostSeeder extends Seeder
{
    public function run(): void
    {
        $users      = User::pluck('id')->toArray();      // 95 users
        $animals    = Animal::pluck('id')->toArray();
        $categories = PostCategory::pluck('id')->toArray();

        if (empty($users) || empty($animals) || empty($categories)) {
            $this->command->error('❌ Missing required data: users, animals, or categories.');
            return;
        }

        $pick = fn($arr) => $arr[array_rand($arr)];

        // 40 English post titles
        $titles = [
            'Rescue Mission: Injured Cat Saved Near Riverside',
            'Weekly Feeding Campaign for Stray Animals',
            'Success Story: Rocky the Dog Fully Recovered',
            'Lost Cat Found in Rose District',
            'Summer Pet Care Tips to Prevent Heatstroke',
            'Emergency Case: Dog Hit by a Car',
            'Health Update: Puppy Lulu After Surgery',
            'Volunteers Visit Local Animal Shelter',
            'Park Cleanup Campaign to Protect Wildlife',
            'Urgent: Donations Needed for Mimi the Cat',
            'Adoption Success: Sugar the Cat Found a Home',
            'Important Vaccination Tips for Cats',
            'Small Dog Needs Temporary Foster Home',
            'Winter Blanket Donation Campaign',
            'Duck Rescued from Fishing Net',
            'Awareness: Dangers of Leaving Pets in Cars',
            'Health Update: Max the Dog After Treatment',
            'New Volunteer Joined the Rescue Team',
            'Heartwarming Photos of Rescued Animals',
            'Urgent: Looking for a Home for Lucy the Cat',
            'Free Medical Checkup Campaign for Pets',
            'Turtle Rescued from Polluted Pond',
            'How to Safely Approach Stray Animals',
            'Health Update: Tommy the Cat After Surgery',
            'Beach Cleanup to Protect Marine Animals',
            'Urgent: Injured Dog Needs Immediate Transport',
            'Success Story: Thunder the Cat Recovered',
            'Volunteers Deliver Meals to Park Animals',
            'Photos from the Latest Vaccination Drive',
            'Lost Dog Found in Jasmine District',
            'Kids Awareness Workshop About Animal Care',
            'Cat Rescued from Rooftop After Heavy Rain',
            'Health Update: Rico the Dog After Operation',
            'Urgent: Donations Needed for Injured Dog',
            'Street Cleanup to Remove Glass Harmful to Pets',
            'Adoption Success: Lulu the Dog Found a Family',
            'Volunteers Visit Animal Shelter — Photo Highlights',
            'Urgent: Looking for a Home for Shadow the Cat',
            'Food Donation Campaign for Needy Animals'
        ];

        $postsData = [];

        foreach ($titles as $index => $title) {
            $postsData[] = [
                'user_id'     => $pick($users),
                'animal_id'   => $pick($animals),
                'category_id' => $pick($categories),
                'title'       => $title,
                'content'     => 'This post includes detailed information about the case, actions taken, and the role of volunteers in supporting the animal.',
                'image_path'  => "/storage/community_posts/post_" . ($index + 1) . ".jpg",
                'created_at'  => now()->subDays(rand(1, 40)),
                'updated_at'  => now()->subDays(rand(1, 40)),
            ];
        }

        CommunityPost::insert($postsData);

        // Insert Likes
        $posts = CommunityPost::pluck('id')->toArray();

        foreach ($posts as $postId) {
            $likesCount = rand(5, 25); // realistic like count

            $randomUsers = collect($users)->shuffle()->take($likesCount);

            foreach ($randomUsers as $userId) {
                PostLike::create([
                    'user_id' => $userId,
                    'post_id' => $postId,
                ]);
            }
        }

        $this->command->info('✅ 40 Community Posts + Realistic Likes inserted successfully!');
    }
}
