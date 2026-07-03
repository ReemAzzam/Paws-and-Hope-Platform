<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnimalMedicalCondition;

class AnimalMedicalConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            // ==================== روكي ====================
            ['animal_id' => 1, 'condition' => 'Ear Mites', 'treatment' => 'Deworming medication + ear drops', 'start_date' => '2026-03-05', 'end_date' => '2026-03-20', 'notes' => 'تحسن ملحوظ بعد أسبوعين'],
            ['animal_id' => 1, 'condition' => 'Minor Skin Allergy', 'treatment' => 'Antihistamine + special shampoo', 'start_date' => '2026-04-01', 'end_date' => null, 'notes' => 'حساسية من نوع معين من الطعام'],

            // ==================== ماكس ====================
            ['animal_id' => 2, 'condition' => 'Broken Nail', 'treatment' => 'Bandage + pain relief medication', 'start_date' => '2026-01-20', 'end_date' => '2026-01-28', 'notes' => 'إصابة أثناء اللعب خارج المنزل'],
            ['animal_id' => 2, 'condition' => 'Mild Gastroenteritis', 'treatment' => 'Probiotics + soft diet', 'start_date' => '2026-02-10', 'end_date' => '2026-02-18', 'notes' => 'التهاب معوي خفيف بسبب أكل شيء غير مناسب'],

            // ==================== بيلا ====================
            ['animal_id' => 3, 'condition' => 'Eye Infection', 'treatment' => 'Antibiotic eye drops', 'start_date' => '2026-03-12', 'end_date' => '2026-03-25', 'notes' => 'إلتهاب خفيف في العين اليسرى'],
            ['animal_id' => 3, 'condition' => 'Dental Tartar Buildup', 'treatment' => 'Dental cleaning', 'start_date' => '2026-05-05', 'end_date' => null, 'notes' => 'يحتاج تنظيف أسنان دوري'],

            // ==================== لوسي ====================
            ['animal_id' => 7, 'condition' => 'Upper Respiratory Infection', 'treatment' => 'Antibiotics + supportive care', 'start_date' => '2026-02-15', 'end_date' => '2026-03-02', 'notes' => 'كانت تعاني من إفرازات أنفية'],
            ['animal_id' => 7, 'condition' => 'Fleas Infestation', 'treatment' => 'Flea treatment + environmental cleaning', 'start_date' => '2026-04-10', 'end_date' => '2026-04-20', 'notes' => 'علاج وقائي بعد الإصابة'],

            // ==================== ميشو ====================
            ['animal_id' => 8, 'condition' => 'Abscess from Fight', 'treatment' => 'Drainage + antibiotics', 'start_date' => '2026-01-25', 'end_date' => '2026-02-05', 'notes' => 'إصابة من قتال مع قط آخر'],
            ['animal_id' => 8, 'condition' => 'Diarrhea', 'treatment' => 'Diet change + probiotics', 'start_date' => '2026-03-18', 'end_date' => '2026-03-25', 'notes' => 'تغير في الطعام'],

            // ==================== زوزو ====================
            ['animal_id' => 10, 'condition' => 'Arthritis (Age Related)', 'treatment' => 'Joint supplements + pain management', 'start_date' => '2025-12-01', 'end_date' => null, 'notes' => 'مشكلة مزمنة بسبب العمر'],
            ['animal_id' => 10, 'condition' => 'Constipation', 'treatment' => 'Laxative + high fiber diet', 'start_date' => '2026-04-05', 'end_date' => '2026-04-12', 'notes' => 'مشكلة شائعة عند القطط الكبيرة'],

            // ==================== تويتو ====================
            ['animal_id' => 11, 'condition' => 'Feather Plucking', 'treatment' => 'Stress reduction + environmental enrichment', 'start_date' => '2026-02-20', 'end_date' => null, 'notes' => 'سلوك ناتج عن الملل'],
            ['animal_id' => 11, 'condition' => 'Vitamin Deficiency', 'treatment' => 'Multivitamin supplement', 'start_date' => '2026-03-15', 'end_date' => '2026-04-01', 'notes' => 'نقص فيتامين D'],

            // ==================== باقي الحيوانات ====================
            ['animal_id' => 4, 'condition' => 'Worm Infestation', 'treatment' => 'Broad spectrum dewormer', 'start_date' => '2026-01-30', 'end_date' => '2026-02-10', 'notes' => 'علاج وقائي دوري'],
            ['animal_id' => 5, 'condition' => 'Sprained Leg', 'treatment' => 'Rest + anti-inflammatory', 'start_date' => '2026-05-05', 'end_date' => '2026-05-18', 'notes' => 'إصابة أثناء الجري'],
            ['animal_id' => 9, 'condition' => 'Ringworm', 'treatment' => 'Antifungal cream', 'start_date' => '2026-04-12', 'end_date' => '2026-05-01', 'notes' => 'فطريات على الجلد'],
            ['animal_id' => 13, 'condition' => 'Overgrown Teeth', 'treatment' => 'Dental trimming', 'start_date' => '2026-03-20', 'end_date' => '2026-03-22', 'notes' => 'مشكلة شائعة عند الأرانب'],
            ['animal_id' => 15, 'condition' => 'Minor Wound', 'treatment' => 'Cleaning + antibiotic ointment', 'start_date' => '2026-02-28', 'end_date' => '2026-03-08', 'notes' => 'جرح سطحي من الشارع'],
            ['animal_id' => 6, 'condition' => 'Hot Spot', 'treatment' => 'Topical cream + cone collar', 'start_date' => '2026-05-10', 'end_date' => '2026-05-20', 'notes' => 'التهاب جلدي حاد'],
        ];

        foreach ($conditions as $cond) {
            AnimalMedicalCondition::create($cond);
        }

        $this->command->info('✅ تم إضافة 20 حالة طبية واقعية بنجاح!');
    }
}
