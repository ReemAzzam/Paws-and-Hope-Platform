<?php

namespace Database\Seeders;

use App\Models\AwarenessPost;
use App\Models\Veterinarian;
use Illuminate\Database\Seeder;

class AwarenessPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch approved veterinarians only
        $vets = Veterinarian::where('is_approved', true)->get();

        if ($vets->isEmpty()) {
            $this->command->warn('No approved veterinarians found (is_approved = true). Please seed veterinarians first.');
            return;
        }

        // Sample awareness posts with local storage image paths
        $samplePosts = [
            [
                'title'     => 'The Importance of Regular Pet Vaccinations',
                'content'   => 'Vaccinations are the first line of defense in protecting your pet from severe viral diseases like Rabies and Parvovirus. Ensure regular visits to your veterinarian to maintain their immunization schedule.',
                'image_url' => '/storage/awareness_posts/post_1.jpg',
            ],
            [
                'title'     => 'Balanced Nutrition Guide for Cats and Dogs',
                'content'   => 'Proper nutrition directly impacts coat health, digestive efficiency, and energy levels. Avoid feeding toxic human foods such as chocolate, onions, and grapes.',
                'image_url' => '/storage/awareness_posts/post_2.jpg',
            ],
            [
                'title'     => 'How to Handle Home Medical Emergencies',
                'content'   => 'If your pet suffers an injury or ingests a toxic substance, remain calm, administer basic first aid if safe, and contact the nearest veterinary clinic immediately.',
                'image_url' => '/storage/awareness_posts/post_3.jpg',
            ],
            [
                'title'     => 'Essential Dental Care Routine for Pets',
                'content'   => 'Plaque accumulation can lead to periodontal disease and systemic complications. Brush your pet\'s teeth regularly using pet-safe toothpaste and dental wipes.',
                'image_url' => '/storage/awareness_posts/post_4.jpg',
            ],
            [
                'title'     => 'Benefits of Daily Exercise and Mental Stimulation',
                'content'   => 'Daily physical activity prevents obesity and reduces anxiety-driven behavioral issues. Engage your pets with interactive toys and routine exercise.',
                'image_url' => '/storage/awareness_posts/post_5.jpg',
            ],
        ];

        // Assign posts randomly among approved veterinarians
        foreach ($samplePosts as $postData) {
            AwarenessPost::create([
                'veterinarian_id' => $vets->random()->id,
                'title'           => $postData['title'],
                'content'         => $postData['content'],
                'image_url'       => $postData['image_url'],
            ]);
        }

        $this->command->info('Educational awareness posts seeded with local storage image paths successfully!');
    }
}