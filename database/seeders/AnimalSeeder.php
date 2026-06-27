<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimalSeeder extends Seeder
{
    public function run()
    {
       DB::statement('SET FOREIGN_KEY_CHECKS=0;');

      DB::table('animal_photos')->truncate();
      DB::table('animals')->truncate();

      DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    $animalsData = $this->getFullAnimalsList();

        foreach ($animalsData as $data) {
            $animal = Animal::create($data);

            // إضافة 1 أو 2 صورة لكل حيوان
            $photoCount = rand(1, 2);
            for ($i = 1; $i <= $photoCount; $i++) {
                AnimalPhoto::create([
                    'animal_id'   => $animal->id,
                    'photo_url'   => "animals/{$animal->id}/photo{$i}.jpg",
                    'is_main'     => $i === 1,
                    'order_number'=> $i,
                ]);
            }
        }

        $this->command->info('✅ تم إنشاء 30 حيوان مع صورهم بنجاح!');
    }

    private function getFullAnimalsList()
    {
        return [
            // 1-12: الكلاب
            ['name' => 'روكي', 'type' => 'dog', 'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 15.50, 'description' => 'كلب لابرادور أليف جداً ويحب اللعب مع الأطفال.', 'story' => 'تم إنقاذه من الشارع بعد تعرضه لحادث بسيط.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5138, 'longitude' => 36.2765, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ماكس', 'type' => 'dog', 'gender' => 'male', 'age' => 4, 'size' => 'large', 'weight' => 28.0, 'description' => 'كلب ألماني شيبرد ذكي.', 'story' => 'كان كلب عائلة وأصبح بحاجة منزل جديد.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5100, 'longitude' => 36.2800, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بيلا', 'type' => 'dog', 'gender' => 'female', 'age' => 3, 'size' => 'small', 'weight' => 8.5, 'description' => 'كلبة بودل ذكية ومرحة.', 'story' => 'تم إعادتها بعد تبني سابق.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5180, 'longitude' => 36.2720, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تومي', 'type' => 'dog', 'gender' => 'male', 'age' => 1, 'size' => 'medium', 'weight' => 12.0, 'description' => 'كلب جولدن ريتريفر ودود.', 'story' => 'وجد في منطقة ريفية.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5150, 'longitude' => 36.2780, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'لونا', 'type' => 'dog', 'gender' => 'female', 'age' => 5, 'size' => 'large', 'weight' => 22.0, 'description' => 'كلبة هاسكي سيبيري جميلة.', 'story' => 'تم إنقاذها من مزرعة مهجورة.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'pending', 'is_urgent' => false, 'latitude' => 33.5120, 'longitude' => 36.2740, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شارلي', 'type' => 'dog', 'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 18.0, 'description' => 'كلب لابرادور مرح.', 'story' => 'تم إنقاذه من مأوى.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5125, 'longitude' => 36.2775, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // 13-22: القطط (10)
            [
                'name' => 'لوسي', 'type' => 'cat', 'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 3.20,
                'description' => 'قطة شيرازية هادئة جداً وتبحث عن منزل دائم.',
                'story' => 'تم التخلي عنها أصحابها في الشارع ولم تكن معتادة على الأجواء الخارجية.',
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => true,
                'latitude' => 33.5102, 'longitude' => 36.2845, 'vet_id' => null, 'rescue_report_id' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            ['name' => 'ميشو', 'type' => 'cat', 'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 4.1, 'description' => 'قط سيامي فضولي ومرح.', 'story' => 'تم إنقاذه من سطح مبنى.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5140, 'longitude' => 36.2750, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ميمي', 'type' => 'cat', 'gender' => 'female', 'age' => 3, 'size' => 'small', 'weight' => 3.5, 'description' => 'قطة بريطانية قصيرة الشعر هادئة.', 'story' => 'كانت قطة منزلية.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5160, 'longitude' => 36.2730, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'زوزو', 'type' => 'cat', 'gender' => 'female', 'age' => 4, 'size' => 'small', 'weight' => 4.0, 'description' => 'قطة عجوز حنونة.', 'story' => 'كانت تعيش مع جدة توفيت.', 'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => true, 'latitude' => 33.5170, 'longitude' => 36.2740, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شيري', 'type' => 'cat', 'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 3.8, 'description' => 'قطة كاليكو ملونة.', 'story' => 'وجدت في حارة ضيقة.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5135, 'longitude' => 36.2760, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // 23-26: الطيور
            ['name' => 'تويتو', 'type' => 'bird', 'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 0.15, 'description' => 'ببغاء كاسكو ذكي.', 'story' => 'تم التخلي عنه.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false, 'availability_status' => 'available', 'is_urgent' => true, 'latitude' => 33.5150, 'longitude' => 36.2750, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'كيكي', 'type' => 'bird', 'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 0.12, 'description' => 'كناري مغرد.', 'story' => 'وجد في قفص مهجور.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5110, 'longitude' => 36.2770, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // 27-30: أرانب وآخرى
            ['name' => 'باندا', 'type' => 'rabbit', 'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 2.1, 'description' => 'أرنب أبيض لطيف.', 'story' => 'تم شراؤه كهدية.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5190, 'longitude' => 36.2710, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'سكر', 'type' => 'rabbit', 'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.8, 'description' => 'أرنب رمادي مرح.', 'story' => 'تم إنقاذه من سوق.', 'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5130, 'longitude' => 36.2790, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'فلفل', 'type' => 'other', 'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 1.2, 'description' => 'سلحفاة برية صغيرة.', 'story' => 'تم العثور عليها في حديقة.', 'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false, 'availability_status' => 'available', 'is_urgent' => false, 'latitude' => 33.5145, 'longitude' => 36.2765, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'نونو', 'type' => 'bird', 'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 0.08, 'description' => 'حمامة بيضاء.', 'story' => 'أصيبت بجناحها.', 'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => false, 'availability_status' => 'available', 'is_urgent' => true, 'latitude' => 33.5165, 'longitude' => 36.2735, 'vet_id' => 1, 'rescue_report_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            [ 'name' => 'سيمو','type' => 'dog',  'gender' => 'male','age' => 2, 'size' => 'medium',  'weight' => 14.2,  'description' => 'كلب مرح يحب اللعب.', 'story' => 'تم العثور عليه في حديقة عامة وهو يبحث عن الطعام بشكل مستمر.', 'health_status' => 'healthy',    'is_vaccinated' => true,  'is_neutered' => false,'availability_status' => 'available','is_urgent' => false,'latitude' => 33.5141,'longitude' => 36.2793,'vet_id' => 1,'rescue_report_id' => null,'created_at' => now(),  'updated_at' => now(),],

            ];
    }
}
