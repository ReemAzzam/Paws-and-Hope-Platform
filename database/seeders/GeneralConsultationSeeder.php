<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneralConsultation;
use App\Models\User;
use App\Models\Veterinarian;

class GeneralConsultationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // جلب المستخدمين العاديين (أول 5 مثلاً)
        $users = User::limit(5)->get();
        // جلب الأطباء البيطريين المعتمدين
        $vets = Veterinarian::where('is_approved', true)->get();

        if ($users->isEmpty()) {
            $this->command->info('Skip seeding consultations: No users found.');
            return;
        }

        // قائمة بأسئلة شائعة وأجوبة بيطرية واقعية للاختبار
        $sampleConsultations = [
            [
                'question' => 'قطتي تعاني من فقدان الشهية والخمول منذ يومين، ما السبب المحتمل وماذا يجب أن أفعل؟',
                'answer'   => 'فقدان الشهية لدى القطط قد يكون مؤشراً على التهاب معوي أو ارتفاع في الحرارة. يُفضل تقييم درجات حرارتها وتقديم طعام رطب سهل الهضم، وإذا استمر الامتناع أكثر من 24 ساعة يجب فحصها بيطرياً.',
                'status'   => 'answered',
            ],
            [
                'question' => 'ماهي التطعيمات الأساسية للكلاب في عمر 3 أشهر؟ وما هو جدول التكرار المناسب؟',
                'answer'   => 'في هذا العمر، يحتاج الكلب للتطعيم الخماسي/الثماني (DHPP) للوقاية من البارفو والديدان، بالإضافة إلى تطعيم السعار (Rabies). يتم إعطاء جرعة تنشيطية بعد 3-4 أسابيع.',
                'status'   => 'answered',
            ],
            [
                'question' => 'لاحظت وجود تساقط شعر كثيف حول أذني الأرنب مع وجود قشور بيضاء، هل هذه جرب أم فطريات؟',
                'answer'   => 'هذه الأعراض ترجّح الإصابة بالطفيليات الخارجية مثل عث الأذن (Ear Mites) أو الفطريات. يُنصح بتنظيف المكان جيداً وعدم محاولة إزالة القشور بقوة، واستخدام قطرات مضادة للطفيليات تحت إشراف الطبيب.',
                'status'   => 'answered',
            ],
            [
                'question' => 'عندي كلب صغير تناوَل قطعة شوكولاتة بالخطأ قبل ساعة، هل الشوكولاتة سامة للكلاب؟',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'ما هي أفضل طريقة لتنظيف أسنان القطط في المنزل للوقاية من التكلس ورائحة الفم الكريهة؟',
                'answer'   => null,
                'status'   => 'pending',
            ],
        ];

        foreach ($sampleConsultations as $index => $data) {
            // اختيار مستخدم بشكل تتابعي
            $user = $users[$index % $users->count()];

            // إذا كانت الاستشارة مجابة وكان هناك أطباء، نربطها بالطبيب
            $vetId = null;
            if ($data['status'] === 'answered' && $vets->isNotEmpty()) {
                $vetId = $vets->random()->id;
            }

            GeneralConsultation::create([
                'user_id'         => $user->id,
                'veterinarian_id' => $vetId, // قد تكون null أو معينة لطبيب
                'question'        => $data['question'],
                'answer'          => $data['answer'],
                'status'          => $data['status'],
                'created_at'      => now()->subDays(rand(1, 10)),
                'updated_at'      => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info('GeneralConsultationSeeder executed successfully!');
    }
}