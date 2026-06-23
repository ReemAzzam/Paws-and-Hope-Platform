<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. إدخال حيوانات تجريبية في جدول animals
        $animals = [
            [
                'name' => 'روكي',
                'type' => 'dog',
                'gender' => 'male',
                'age' => 2,
                'size' => 'medium',
                'weight' => 15.50,
                'description' => 'كلب ليريف أليف جداً ويحب اللعب مع الأطفال.',
                'story' => 'تم إنقاذه من الشارع بعد تعرضه لحادث بسيط في قدمه وهو الآن بكامل صحته.',
                'health_status' => 'healthy',
                'is_vaccinated' => true,
                'is_neutered' => true,
                'availability_status' => 'available', // متاح للكفالة أو التبني
                'is_urgent' => false,
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'vet_id' => null, // يمكنك ربطه بـ id طبيب إن وجد
                'rescue_report_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'لوسي',
                'type' => 'cat',
                'gender' => 'female',
                'age' => 1,
                'size' => 'small',
                'weight' => 3.20,
                'description' => 'قطة شيرازية هادئة جداً وتبحث عن منزل دافئ.',
                'story' => 'تخلّى عنها أصحابها في الشارع ولم تكن معتادة على الأجواء الخارجية.',
                'health_status' => 'recovering',
                'is_vaccinated' => true,
                'is_neutered' => false,
                'availability_status' => 'available',
                'is_urgent' => true, // حالة مستعجلة لجذب الكفلاء
                'latitude' => 33.5102,
                'longitude' => 36.2845,
                'vet_id' => null,
                'rescue_report_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'بندق',
                'type' => 'rabbit',
                'gender' => 'male',
                'age' => 3,
                'size' => 'small',
                'weight' => 1.80,
                'description' => 'أرنب نشيط يحب أكل الجزر والخضار الورقية.',
                'story' => 'عثر عليه أحد المتطوعين في حديقة عامة.',
                'health_status' => 'healthy',
                'is_vaccinated' => false,
                'is_neutered' => false,
                'availability_status' => 'sponsored', // معلّم كـ مكفول مسبقاً لتنويع البيانات
                'is_urgent' => false,
                'latitude' => 33.5210,
                'longitude' => 36.2910,
                'vet_id' => null,
                'rescue_report_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ماكس',
                'type' => 'dog',
                'gender' => 'male',
                'age' => 4,
                'size' => 'large',
                'weight' => 28.00,
                'description' => 'كلب حراسة قوي ومطيع جداً لصاحبه.',
                'story' => 'تم إنقاذه من موقع بناء مهجور وكان يعاني من سوء التغذية.',
                'health_status' => 'sick', // تم التعديل هنا من under_treatment إلى sick لتتوافق مع الـ Migration
                'is_vaccinated' => false,
                'is_neutered' => false,
                'availability_status' => 'under_treatment', // هذا الحقل سليم ويقبل under_treatment
                'is_urgent' => true,
                'latitude' => 33.4980,
                'longitude' => 36.2520,
                'vet_id' => null,
                'rescue_report_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // إدراج البيانات والحصول على أول ID تم إنشاؤه لربط الصور بدقة
        DB::table('animals')->insert($animals);

        // جلب معرفات الحيوانات التي تم إدخالها حديثاً
        $insertedAnimalIds = DB::table('animals')->pluck('id')->toArray();

        // 2. إدخال صور تجريبية مرتبطة بالحيوانات في جدول animal_photos
        $photos = [];
        foreach ($insertedAnimalIds as $index => $animalId) {
            // صورة أساسية (Main Photo) لكل حيوان
            $photos[] = [
                'animal_id' => $animalId,
                'photo_url' => "https://via.placeholder.com/600x400.png?text=Animal+" . ($index + 1) . "+Main",
                'is_main' => true,
                'order_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // صورة إضافية ثانية للحيوان للتأكد من عمل الـ Sliders أو الاستعراض الكامل
            $photos[] = [
                'animal_id' => $animalId,
                'photo_url' => "https://via.placeholder.com/600x400.png?text=Animal+" . ($index + 1) . "+Detail",
                'is_main' => false,
                'order_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('animal_photos')->insert($photos);
    }
}