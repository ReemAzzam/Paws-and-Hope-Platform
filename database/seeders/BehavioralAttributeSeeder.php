<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BehavioralAttribute;

class BehavioralAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            // ==================== روكي (Dog) ====================
            ['animal_id' => 1, 'attribute_name' => 'Playfulness', 'intensity' => 'high', 'description' => 'يحب اللعب كثيراً مع الكرة والحبال'],
            ['animal_id' => 1, 'attribute_name' => 'Friendliness', 'intensity' => 'high', 'description' => 'ودود جداً مع الغرباء والأطفال'],
            ['animal_id' => 1, 'attribute_name' => 'Energy Level', 'intensity' => 'medium', 'description' => 'نشيط صباحاً ويهدأ بعد المشي'],

            // ==================== ماكس (Dog) ====================
            ['animal_id' => 2, 'attribute_name' => 'Protectiveness', 'intensity' => 'high', 'description' => 'حارس جيد ويتنبه للأصوات الغريبة'],
            ['animal_id' => 2, 'attribute_name' => 'Trainability', 'intensity' => 'high', 'description' => 'ذكي ويتعلم الأوامر بسرعة'],

            // ==================== بيلا (Dog) ====================
            ['animal_id' => 3, 'attribute_name' => 'Affection', 'intensity' => 'high', 'description' => 'تحب الجلوس على الحضن والمداعبة'],
            ['animal_id' => 3, 'attribute_name' => 'Calmness', 'intensity' => 'medium', 'description' => 'هادئة نسبياً داخل المنزل'],

            // ==================== لوسي (Cat) ====================
            ['animal_id' => 7, 'attribute_name' => 'Independence', 'intensity' => 'high', 'description' => 'تحب قضاء وقت لوحدها أحياناً'],
            ['animal_id' => 7, 'attribute_name' => 'Affection', 'intensity' => 'medium', 'description' => 'حنونة لكن بطريقتها الخاصة'],
            ['animal_id' => 7, 'attribute_name' => 'Playfulness', 'intensity' => 'medium', 'description' => 'تلعب مع الليزر والكرات الصغيرة'],

            // ==================== ميشو (Cat) ====================
            ['animal_id' => 8, 'attribute_name' => 'Curiosity', 'intensity' => 'high', 'description' => 'فضولي جداً ويستكشف كل شيء'],
            ['animal_id' => 8, 'attribute_name' => 'Vocal', 'intensity' => 'high', 'description' => 'كثير الكلام (مواء)'],

            // ==================== زوزو (Cat) ====================
            ['animal_id' => 10, 'attribute_name' => 'Calmness', 'intensity' => 'high', 'description' => 'هادئة جداً وتفضل الراحة'],
            ['animal_id' => 10, 'attribute_name' => 'Laziness', 'intensity' => 'high', 'description' => 'تنام معظم اليوم على السرير'],

            // ==================== تويتو (Bird) ====================
            ['animal_id' => 11, 'attribute_name' => 'Talkativeness', 'intensity' => 'high', 'description' => 'يتعلم كلمات وأصوات جديدة بسرعة'],
            ['animal_id' => 11, 'attribute_name' => 'Intelligence', 'intensity' => 'high', 'description' => 'ذكي ويحل الألغاز البسيطة'],

            // ==================== باندا (Rabbit) ====================
            ['animal_id' => 13, 'attribute_name' => 'Gentleness', 'intensity' => 'high', 'description' => 'لطيف ولا يعض'],
            ['animal_id' => 13, 'attribute_name' => 'Activity', 'intensity' => 'medium', 'description' => 'يحب القفز والاستكشاف'],

            // ==================== إضافات أخرى ====================
            ['animal_id' => 4, 'attribute_name' => 'Sociability', 'intensity' => 'high', 'description' => 'يحب وجود حيوانات أخرى معه'],
            ['animal_id' => 5, 'attribute_name' => 'Shyness', 'intensity' => 'medium', 'description' => 'يحتاج وقت ليثق بالغرباء'],
            ['animal_id' => 9, 'attribute_name' => 'Territorial', 'intensity' => 'medium', 'description' => 'يحمي منطقته داخل المنزل'],
            ['animal_id' => 12, 'attribute_name' => 'Adaptability', 'intensity' => 'high', 'description' => 'يتأقلم بسرعة مع البيئة الجديدة'],
            ['animal_id' => 15, 'attribute_name' => 'Food Motivation', 'intensity' => 'high', 'description' => 'يحب الطعام جداً ويتعلم من أجله'],
        ];

        foreach ($attributes as $attr) {
            BehavioralAttribute::create($attr);
        }

        $this->command->info('✅ تم إضافة 20 صفة سلوكية بنجاح!');
    }
}
