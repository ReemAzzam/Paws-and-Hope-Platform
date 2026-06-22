<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class QuizQuestionsSeeder extends Seeder
{
    public function run()
    {
        DB::table('matching_quiz')->truncate();// حذف الأسئلة القديمة

        $questions = [
            // Step 1: Pet Preference
            ['step_id' => 1, 'question_order' => 1, 'question_text' => "What type of pet are you looking for?", 'options' => json_encode(["Dog", "Cat", "Doesn't matter"]), 'key' => 'preferred_animal_type', 'hint' => 'This helps us narrow down the core species.'],
            ['step_id' => 1, 'question_order' => 2, 'question_text' => "What age of pet do you prefer?", 'options' => json_encode(["Puppy/Kitten", "Adult", "Senior"]), 'key' => 'preferred_age', 'hint' => 'Age determines energy levels and training needs.'],
            ['step_id' => 1, 'question_order' => 3, 'question_text' => "What size pet do you prefer?", 'options' => json_encode(["Large", "Medium", "Small", "Doesn't matter"]), 'key' => 'preferred_size', 'hint' => "Don't worry! This helps us suggest the best furry friend for you."],

            // Step 2: Home & Lifestyle
            ['step_id' => 2, 'question_order' => 4, 'question_text' => "Do you live in an apartment or a house?", 'options' => json_encode(["Apartment", "House with yard", "House without yard"]), 'key' => 'housing_type', 'hint' => 'Some pets need larger spaces to run around.'],
            ['step_id' => 2, 'question_order' => 5, 'question_text' => "How active is your lifestyle?", 'options' => json_encode(["Very Active", "Moderately Active", "Sedentary"]), 'key' => 'activity_level', 'hint' => 'We match active pets with active owners!'],
            ['step_id' => 2, 'question_order' => 6, 'question_text' => "How many hours will the pet be alone daily?", 'options' => json_encode(["Less than 2 hours", "2 - 5 hours", "More than 5 hours"]), 'key' => 'hours_alone_daily', 'hint' => 'Certain breeds handle alone time better than others.'],
            ['step_id' => 2, 'question_order' => 7, 'question_text' => "Are there children in your household?", 'options' => json_encode(["Yes, toddlers", "Yes, older children", "No"]), 'key' => 'children_status', 'hint' => 'Ensures safety and friendliness alignment.'],

            // Step 3: Personality & Feelings
            ['step_id' => 3, 'question_order' => 8, 'question_text' => "What personality trait do you value most?", 'options' => json_encode(["Affectionate", "Independent", "Protective", "Playful"]), 'key' => 'preferred_personality', 'hint' => 'Matches the psychological vibe.'],
            ['step_id' => 3, 'question_order' => 9, 'question_text' => "How do you feel about pet shedding/hair?", 'options' => json_encode(["No shedding preferred", "A little is fine", "I don't mind cleaning"]), 'key' => 'shedding_tolerance', 'hint' => 'Important for allergies and grooming time.'],
            ['step_id' => 3, 'question_order' => 10, 'question_text' => "Do you own other pets currently?", 'options' => json_encode(["Yes, dogs", "Yes, cats", "Yes, other", "No"]), 'key' => 'has_other_pets', 'hint' => 'Helps ensure social compatibility.'],
            ['step_id' => 3, 'question_order' => 11, 'question_text' => "What is your primary goal for getting a pet?", 'options' => json_encode(["Companionship", "Guard duty", "Therapy/Support", "For children"]), 'key' => 'primary_goal', 'hint' => 'Aligns expectation with pet character.'],

            // Step 4: Commitment
            ['step_id' => 4, 'question_order' => 12, 'question_text' => "Are you ready for financial commitment (Food, Vets)?", 'options' => json_encode(["Completely ready", "Budgeting carefully", "Unsure"]), 'key' => 'financial_readiness', 'hint' => 'Pets require stable financial care.'],
            ['step_id' => 4, 'question_order' => 13, 'question_text' => "Have you ever owned a pet before?", 'options' => json_encode(["Yes, experienced", "First time owner", "Family had pets"]), 'key' => 'previous_experience', 'hint' => 'Tailors the difficulty curve of pet care.'],
            ['step_id' => 4, 'question_order' => 14, 'question_text' => "Are you ready for long-term commitment (10+ years)?", 'options' => json_encode(["Yes, fully committed", "I hope so", "Unsure"]), 'key' => 'long_term_commitment', 'hint' => 'Adoption is for a lifetime.'],
        ];
       foreach ($questions as $q) {
           DB::table('matching_quiz')->insert($q);
        }

        $this->command->info('✅ Matching Quiz Questions seeded successfully! (14 questions)');
    }
}
