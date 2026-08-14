<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommunityPost;
use App\Models\User;
use App\Models\Animal;
use App\Models\PostCategory;

class CommunityPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Retrieve the first user available in the users table
        $adminUser = User::first();

        if (!$adminUser) {
            $this->command->error('❌ No users found in the users table! Please run UserSeeder first.');
            return;
        }

        // 2. Fetch available IDs for animals and post categories
        $animalIds   = Animal::pluck('id')->toArray();
        $categoryIds = PostCategory::pluck('id')->toArray();

        if (empty($animalIds) || empty($categoryIds)) {
            $this->command->error('❌ Please ensure there is data in both animals and post_categories tables before running this Seeder.');
            return;
        }

        // Helper function to pick a random ID
        $getRandomId = fn(array $arr) => $arr[array_rand($arr)];

        $posts = [
            [
                'user_id'     => $adminUser->id,
                'animal_id'   => $getRandomId($animalIds),
                'category_id' => $getRandomId($categoryIds),
                'title'       => 'Success Story: Lily the Cat\'s Full Recovery',
                'content'     => 'Thanks to your generous donations and continuous support, Lily successfully passed her critical stage following her surgery and treatment. She is now in great health and ready for adoption!',
                'image_path'  => '/storage/community_posts/posts_1.jpg',
                'created_at'  => now()->subDays(10),
                'updated_at'  => now()->subDays(10),
            ],
            [
                'user_id'     => $adminUser->id,
                'animal_id'   => $getRandomId($animalIds),
                'category_id' => $getRandomId($categoryIds),
                'title'       => 'Stray Animal Feeding Campaign This Week',
                'content'     => 'Our team distributed over 100 nutritious meals to stray dogs and cats across the city streets. A huge thank you to everyone who contributed and supported this campaign.',
                'image_path'  => '/storage/community_posts/posts_2.jpg',
                'created_at'  => now()->subDays(6),
                'updated_at'  => now()->subDays(6),
            ],
            [
                'user_id'     => $adminUser->id,
                'animal_id'   => $getRandomId($animalIds),
                'category_id' => $getRandomId($categoryIds),
                'title'       => 'Emergency Case: Injured Puppy Rescued from Main Road',
                'content'     => 'The puppy was immediately rushed to the veterinary clinic for emergency care after a traffic accident. We urgently need your financial support to cover his ongoing medical treatment.',
                'image_path'  => '/storage/community_posts/posts_3.jpg',
                'created_at'  => now()->subDays(3),
                'updated_at'  => now()->subDays(3),
            ],
            [
                'user_id'     => $adminUser->id,
                'animal_id'   => $getRandomId($animalIds),
                'category_id' => $getRandomId($categoryIds),
                'title'       => 'Essential Summer Pet Care Tips',
                'content'     => 'Always ensure your pet has access to clean, cool water to prevent heatstroke. Avoid walking your dogs on hot pavement during peak afternoon temperatures.',
                'image_path'  => '/storage/community_posts/posts_4.jpg',
                'created_at'  => now()->subDay(),
                'updated_at'  => now()->subDay(),
            ],
        ];

        CommunityPost::insert($posts);

        $this->command->info('✅ Community posts inserted successfully by Admin (ID: ' . $adminUser->id . ')!');
    }
}