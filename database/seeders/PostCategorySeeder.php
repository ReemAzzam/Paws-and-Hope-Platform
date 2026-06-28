<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('post_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            [
                'name_en'    => 'Daily Routine',
                'slug'       => 'daily-routine',
                'icon'       => 'sun-icon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en'    => 'Funny Moments',
                'slug'       => 'funny-moments',
                'icon'       => 'laugh-icon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en'    => 'Training Progress',
                'slug'       => 'training-progress',
                'icon'       => 'award-icon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en'    => 'Health & Care',
                'slug'       => 'health-care',
                'icon'       => 'heart-med-icon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en'    => 'Happy Tails',
                'slug'       => 'happy-tails',
                'icon'       => 'paw-icon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_en'    => 'Shelter News',
                'slug'       => 'shelter-news',
                'icon'       => 'newspaper-icon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('post_categories')->insert($categories);

        $this->command->info('✅ Community post categories seeded successfully!');
    }
}