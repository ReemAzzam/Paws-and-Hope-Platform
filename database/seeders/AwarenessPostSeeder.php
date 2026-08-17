<?php

namespace Database\Seeders;

use App\Models\AwarenessPost;
use App\Models\Veterinarian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AwarenessPostSeeder extends Seeder
{
    public function run(): void
    {
        $vets = Veterinarian::where('is_approved', true)
            ->orderBy('id')
            ->limit(20)
            ->pluck('id')
            ->values();

        if ($vets->count() < 20) {
            $this->command->error('Need at least 20 approved veterinarians. Seed users/vets first.');
            return;
        }

        // Clear old posts (optional for clean demo)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('awareness_posts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $posts = $this->posts();

        // Distribution plan
        $plan = [
            1 => 10,
            2 => 10,
            3 => 6,
            4 => 6,
            5 => 6,
        ];
        for ($i = 6; $i <= 20; $i++) {
            $plan[$i] = 3;
        }

        $postIndex = 0;
        $createdId = 1;

        foreach ($plan as $vetOrder => $count) {
            $vetId = $vets[$vetOrder - 1]; // 1-based plan → 0-based array

            for ($j = 0; $j < $count; $j++) {
                if (!isset($posts[$postIndex])) {
                    break 2;
                }

                $item = $posts[$postIndex];

                AwarenessPost::create([
                    'id'              => $createdId,
                    'veterinarian_id' => $vetId,
                    'title'           => $item['title'],
                    'content'         => $item['content'],
                    'image_url'       => "awareness_posts/post_{$createdId}.jpg",
                    'created_at'      => now()->subDays(83 - $createdId),
                    'updated_at'      => now()->subDays(83 - $createdId),
                ]);

                $createdId++;
                $postIndex++;
            }
        }

        $this->command->info('✅ Awareness posts seeded: ' . ($createdId - 1));
        $this->command->info('Images expected at: storage/app/public/awareness_posts/post_{id}.jpg');
    }

    private function posts(): array
    {
        return [
            // ===== Vet 1 (10) =====
            [
                'title' => 'Why Annual Vaccinations Matter',
                'content' => 'Annual vaccines protect pets from preventable diseases such as rabies, distemper, and parvovirus. Even indoor animals can be exposed through open windows, visitors, or short outdoor walks. A consistent vaccination schedule is one of the simplest ways to keep your pet safe long-term.',
            ],
            [
                'title' => 'Recognizing Early Signs of Dehydration',
                'content' => 'Dry gums, reduced energy, and fewer bathroom visits can indicate dehydration. In hot climates, pets lose fluids quickly. Offer fresh water throughout the day and contact a veterinarian if symptoms continue for more than a few hours.',
            ],
            [
                'title' => 'Dental Care Is Not Optional',
                'content' => 'Bad breath, red gums, and difficulty chewing often point to dental disease. Left untreated, oral infections can affect the heart and kidneys. Brushing a few times each week and scheduling professional cleanings can prevent painful complications.',
            ],
            [
                'title' => 'How to Prepare for a Veterinary Visit',
                'content' => 'Bring a short history of symptoms, recent diet changes, and any medications. For cats, use a secure carrier. For dogs, use a well-fitted leash. Calm preparation helps your veterinarian reach an accurate diagnosis faster.',
            ],
            [
                'title' => 'Parasite Prevention All Year Round',
                'content' => 'Fleas, ticks, and intestinal worms are not limited to one season. Monthly prevention is safer and less expensive than treating advanced infestations. Ask your veterinarian which product fits your pet’s age, weight, and lifestyle.',
            ],
            [
                'title' => 'Safe Foods vs Dangerous Foods',
                'content' => 'Chocolate, grapes, onions, garlic, and xylitol can be toxic to pets. If accidental ingestion happens, note the amount and time, then seek veterinary help immediately. Quick action often improves outcomes significantly.',
            ],
            [
                'title' => 'Managing Separation Anxiety',
                'content' => 'Destructive chewing, excessive barking, and pacing may signal separation anxiety. Gradual alone-time training, enrichment toys, and predictable routines usually help. Severe cases may need a tailored behavioral plan.',
            ],
            [
                'title' => 'Post-Surgery Home Care Tips',
                'content' => 'Keep your pet in a quiet space, prevent licking of the wound, and follow medication timing exactly. Restricted activity is essential for proper healing. Call the clinic if swelling, discharge, or loss of appetite appears.',
            ],
            [
                'title' => 'Understanding Spay and Neuter Benefits',
                'content' => 'Spaying and neutering reduce certain cancers, prevent unwanted litters, and can improve long-term community animal welfare. The ideal age depends on species, breed, and health status, so personalized advice is important.',
            ],
            [
                'title' => 'When Vomiting Becomes an Emergency',
                'content' => 'Occasional mild vomiting may follow dietary changes, but repeated vomiting, blood, lethargy, or refusal to drink water requires urgent care. These signs can indicate obstruction, infection, or toxin exposure.',
            ],

            // ===== Vet 2 (10) =====
            [
                'title' => 'Building a Healthy Daily Routine for Dogs',
                'content' => 'Dogs thrive on structure: fixed meal times, daily walks, and short training sessions. A stable routine lowers stress and supports better behavior. Adjust activity intensity according to age and medical condition.',
            ],
            [
                'title' => 'Cat Litter Box Problems Explained',
                'content' => 'Avoidance of the litter box is often medical or stress-related, not “spite.” Urinary pain, dirty boxes, or household changes can all trigger the issue. Rule out medical causes first before behavioral training.',
            ],
            [
                'title' => 'Heatstroke Warning Signs',
                'content' => 'Heavy panting, drooling, weakness, and collapse can indicate heatstroke. Move the animal to shade, offer small amounts of water, and seek emergency care. Never leave pets in parked cars, even briefly.',
            ],
            [
                'title' => 'Choosing the Right Diet for Senior Pets',
                'content' => 'Older pets often need fewer calories but higher-quality protein and joint support. Sudden weight loss or gain should be evaluated. A senior diet works best when combined with regular bloodwork and mobility checks.',
            ],
            [
                'title' => 'First Aid Kit Essentials for Pet Owners',
                'content' => 'A basic kit should include gauze, saline, an Elizabethan collar, a digital thermometer, and your clinic’s emergency number. First aid supports recovery, but it does not replace professional examination.',
            ],
            [
                'title' => 'Why Regular Weight Checks Matter',
                'content' => 'Obesity increases risk of diabetes, arthritis, and heart strain. Monthly weight tracking at home helps detect trends early. Portion control and measured treats are often more effective than extreme diet changes.',
            ],
            [
                'title' => 'Traveling Safely with Pets',
                'content' => 'Use secure carriers or seat-belt harnesses, pack water, and avoid feeding large meals right before travel. For long trips, plan rest stops and confirm local emergency clinics along the route.',
            ],
            [
                'title' => 'Skin Allergies in Dogs and Cats',
                'content' => 'Itching, redness, and recurrent ear infections may come from food, pollen, or flea sensitivity. Diagnosis usually requires a stepwise approach. Avoid random supplement trials without veterinary guidance.',
            ],
            [
                'title' => 'Microchipping: A Simple Life-Saving Step',
                'content' => 'A microchip greatly improves the chance of reuniting lost pets with their families. Keep registration details updated after moving or changing phone numbers. It works best together with a visible collar tag.',
            ],
            [
                'title' => 'Care After Adoption Day',
                'content' => 'The first two weeks in a new home are an adjustment period. Limit visitors, keep routines simple, and schedule a wellness exam early. Patience during this stage builds long-term trust and stability.',
            ],

            // ===== Vet 3 (6) =====
            [
                'title' => 'Rabbit Digestive Health Basics',
                'content' => 'Rabbits need constant access to hay to keep their digestive system moving. A sudden stop in droppings is an emergency. Fresh water, safe greens, and monitoring appetite are daily responsibilities for rabbit owners.',
            ],
            [
                'title' => 'Common Myths About Indoor Cats',
                'content' => 'Indoor cats still need vaccination, parasite control, and mental stimulation. Boredom can lead to obesity and stress behaviors. Window perches, play sessions, and climbing spaces improve quality of life.',
            ],
            [
                'title' => 'Limping Is Never “Just a Small Thing”',
                'content' => 'Limping can result from soft tissue strain, joint disease, or fracture. Rest alone is not always enough. If lameness lasts more than a day, or if your pet cannot bear weight, book an examination.',
            ],
            [
                'title' => 'Safe Outdoor Time for Puppies',
                'content' => 'Until vaccination courses are complete, avoid high-traffic dog areas. Carry puppies in public spaces when needed and focus on controlled, clean environments. Early socialization should never compromise disease protection.',
            ],
            [
                'title' => 'Ear Infections: What Owners Miss',
                'content' => 'Head shaking, odor, and dark discharge are classic signs. Moisture after bathing and untreated allergies often contribute. Home cleaning helps only when guided by a veterinarian after proper diagnosis.',
            ],
            [
                'title' => 'Nutrition During Recovery',
                'content' => 'Recovering animals may need highly digestible food in smaller, frequent meals. Appetite stimulants are sometimes required. Track water intake closely, especially after gastrointestinal illness or surgery.',
            ],

            // ===== Vet 4 (6) =====
            [
                'title' => 'Poisoning Emergencies at Home',
                'content' => 'Household cleaners, rodenticides, and some houseplants are common toxins. If exposure is suspected, do not induce vomiting unless instructed. Bring the product label to the clinic when possible.',
            ],
            [
                'title' => 'Benefits of Early Socialization',
                'content' => 'Positive exposure to people, sounds, and handling during early development reduces fear later in life. Keep sessions short and calm. Socialization works best when paired with health protection.',
            ],
            [
                'title' => 'Managing Chronic Kidney Disease',
                'content' => 'Increased thirst, weight loss, and reduced appetite can signal kidney disease, especially in older cats. Early diagnosis allows dietary therapy and monitoring that slow progression and protect comfort.',
            ],
            [
                'title' => 'Why Bloodwork Matters Before Anesthesia',
                'content' => 'Pre-anesthetic testing helps detect hidden organ issues and improves safety planning. It is especially important for seniors and pets with previous medical conditions. Screening supports informed clinical decisions.',
            ],
            [
                'title' => 'Seasonal Shedding and Skin Support',
                'content' => 'Extra brushing during heavy shed seasons reduces matts and skin irritation. Omega fatty acids may help some animals, but persistent itching still needs medical evaluation rather than grooming alone.',
            ],
            [
                'title' => 'Bird Care: Humidity and Stress',
                'content' => 'Pet birds are sensitive to smoke, strong fumes, and sudden environmental changes. Stable routines, clean water, and species-appropriate diets are essential. Appetite loss in birds should be treated urgently.',
            ],

            // ===== Vet 5 (6) =====
            [
                'title' => 'Orthopedic Care After Injury',
                'content' => 'Fractures and ligament injuries require controlled recovery. Restricted movement, pain management, and follow-up imaging are often necessary. Returning to full activity too soon can reverse progress.',
            ],
            [
                'title' => 'Diabetes Warning Signs in Pets',
                'content' => 'Excessive thirst, frequent urination, and weight loss despite a good appetite can indicate diabetes. With insulin therapy and diet control, many pets live comfortably for years after diagnosis.',
            ],
            [
                'title' => 'How Noise Affects Anxious Animals',
                'content' => 'Fireworks, storms, and construction noise can trigger intense fear responses. Create a quiet safe space and avoid punishment. In severe cases, preventive medication plans can be discussed in advance.',
            ],
            [
                'title' => 'Wound Care Mistakes to Avoid',
                'content' => 'Hydrogen peroxide and harsh antiseptics can damage tissue if used incorrectly. Clean only as directed and prevent licking. Any wound with deep tissue exposure needs professional assessment.',
            ],
            [
                'title' => 'Senior Cat Wellness Checklist',
                'content' => 'Twice-yearly exams, dental evaluation, mobility review, and blood screening are valuable for older cats. Subtle changes in jumping, grooming, or litter habits often reveal important early clues.',
            ],
            [
                'title' => 'Responsible Antibiotic Use',
                'content' => 'Antibiotics should target confirmed or strongly suspected bacterial infections. Stopping treatment early encourages resistance. Never reuse leftover medication from a previous illness or another animal.',
            ],

            // ===== Vets 6-20 (3 each = 45) + remaining unique content =====
            [
                'title' => 'Hydration Tips During Summer',
                'content' => 'Provide multiple water stations and consider wet food for cats that drink little. Outdoor walks should be scheduled in cooler hours. Early fatigue and heavy panting are signals to stop activity.',
            ],
            [
                'title' => 'Choosing Toys That Are Actually Safe',
                'content' => 'Avoid toys with small detachable parts, long strings, or weak seams. Supervise play, especially with new items. Destroyed toys should be discarded before pieces are swallowed.',
            ],
            [
                'title' => 'Kennel Cough Prevention',
                'content' => 'Boarding facilities and dog parks increase exposure risk. Vaccination reduces severity but does not eliminate all risk. Isolate coughing dogs and contact your clinic if breathing effort increases.',
            ],
            [
                'title' => 'Urinary Blockage in Male Cats',
                'content' => 'Straining in the litter box with little or no urine is an emergency. Male cats can deteriorate quickly from urinary obstruction. Immediate veterinary care is required.',
            ],
            [
                'title' => 'Feeding Frequency by Life Stage',
                'content' => 'Puppies and kittens usually need more frequent meals than adults. Free-feeding can hide appetite changes and encourage obesity. Measured meals make health monitoring easier.',
            ],
            [
                'title' => 'Handling Aggressive Behavior Safely',
                'content' => 'Aggression can stem from pain, fear, or territorial stress. Do not punish reactive behavior without assessment. A veterinary exam should come before intensive training plans.',
            ],
            [
                'title' => 'Eye Discharge: Mild or Serious?',
                'content' => 'Clear mild discharge can follow dust exposure, but yellow discharge, squinting, or pawing at the eye needs prompt care. Eye conditions can worsen within hours.',
            ],
            [
                'title' => 'Transportation Stress in Cats',
                'content' => 'Leave the carrier out before travel day so it feels familiar. Covering part of the carrier can reduce visual stress. Calm handling matters more than rushing the process.',
            ],
            [
                'title' => 'Post-Rescue Health Screening',
                'content' => 'Newly rescued animals should be checked for parasites, wounds, dehydration, and infectious disease. Early screening protects the animal and any other pets in the home.',
            ],
            [
                'title' => 'Joint Support for Active Dogs',
                'content' => 'Warm-up walks, controlled exercise, and weight management protect joints. Large breeds benefit from early discussion about long-term mobility support strategies.',
            ],
            [
                'title' => 'Understanding Appetite Loss',
                'content' => 'A pet that skips one meal may be stressed, but multi-day appetite loss is clinically important. Note any vomiting, diarrhea, or behavior changes when you call the clinic.',
            ],
            [
                'title' => 'Safe Introduction of a New Pet',
                'content' => 'Introduce animals gradually through scent and short supervised sessions. Rushing face-to-face meetings increases conflict risk. Separate feeding areas reduce early tension.',
            ],
            [
                'title' => 'Why Lab Tests Follow Treatment',
                'content' => 'Follow-up tests confirm whether organs are responding and whether medication doses remain appropriate. This is especially important for long-term thyroid, kidney, and seizure therapies.',
            ],
            [
                'title' => 'Grooming Without Injury',
                'content' => 'Use the correct brush for coat type and avoid forcing mats out roughly. Severe mats may need professional clipping. Grooming should never cause open skin wounds.',
            ],
            [
                'title' => 'Coughing in Older Dogs',
                'content' => 'Persistent cough in seniors may relate to heart disease, infections, or airway conditions. Nighttime cough and exercise intolerance are useful details to report during examination.',
            ],
            [
                'title' => 'Pet-Proofing Your Home',
                'content' => 'Secure trash bins, hide electric cords, and store medications out of reach. Small objects on the floor are common intestinal foreign bodies in puppies and young cats.',
            ],
            [
                'title' => 'What “Normal” Vital Signs Mean',
                'content' => 'Knowing your pet’s normal energy level, breathing pattern, and bathroom habits helps you notice changes early. Home observation is a valuable part of preventive care.',
            ],
            [
                'title' => 'Caring for Pregnant Dogs',
                'content' => 'Nutrition quality, parasite control, and a clean whelping area are essential. Unexpected weakness, green discharge, or prolonged straining during labor requires emergency support.',
            ],
            [
                'title' => 'Flea Allergy Dermatitis',
                'content' => 'One flea bite can trigger intense itching in sensitive animals. Year-round prevention and environmental cleaning are both needed. Treating only the pet is rarely enough.',
            ],
            [
                'title' => 'When to Use an Emergency Clinic',
                'content' => 'Difficulty breathing, uncontrolled bleeding, seizures, suspected poisoning, and inability to urinate are true emergencies. Immediate care is more important than waiting for regular hours.',
            ],
            [
                'title' => 'Training and Health Go Together',
                'content' => 'Pain can look like stubbornness. A pet that suddenly refuses commands or becomes irritable deserves a physical exam. Behavior and medical health are closely linked.',
            ],
            [
                'title' => 'Monitoring After Starting New Medication',
                'content' => 'Watch for vomiting, rash, marked sedation, or appetite changes during the first days of a new drug. Report reactions quickly rather than adjusting doses on your own.',
            ],
            [
                'title' => 'Water Safety for Dogs',
                'content' => 'Not all dogs are natural swimmers. Use life vests for boat trips and rinse skin after exposure to chlorinated or salty water. Supervise every session near pools.',
            ],
            [
                'title' => 'Shelter Pets and Hidden Stress',
                'content' => 'Animals from shelters may need longer adjustment periods. Quiet rooms and predictable handling reduce stress hormones and support better immune recovery after adoption.',
            ],
            [
                'title' => 'Night-Time Restlessness in Seniors',
                'content' => 'Disorientation at night can relate to pain, cognitive changes, or sensory decline. A senior wellness exam helps separate behavioral issues from medical causes.',
            ],
            [
                'title' => 'Choosing Boarding Responsibly',
                'content' => 'Ask about vaccination requirements, emergency protocols, and separation of aggressive or ill animals. A clean facility with trained staff is more important than luxury amenities.',
            ],
            [
                'title' => 'Paw Care on Hot Pavement',
                'content' => 'If pavement is too hot for your hand, it is too hot for paws. Walk during cooler hours and check for burns or cracks after outings in peak summer heat.',
            ],
            [
                'title' => 'Constipation in Cats',
                'content' => 'Infrequent hard stools, straining, and reduced appetite can indicate constipation. Hydration, diet changes, and medical evaluation prevent progression to more serious obstruction.',
            ],
            [
                'title' => 'Why Quarantine New Arrivals',
                'content' => 'Isolating a new pet for a short period protects resident animals from contagious illness. Use this time for veterinary screening and gradual introduction planning.',
            ],
            [
                'title' => 'Enrichment for High-Energy Dogs',
                'content' => 'Puzzle feeders, scent games, and structured training burn mental energy. Physical walks alone may not prevent boredom-related destruction in working breeds.',
            ],
            [
                'title' => 'Seasonal Allergy Management',
                'content' => 'Paw chewing and face rubbing often increase in allergy seasons. Early treatment prevents skin infections. Not every itchy pet needs the same medication plan.',
            ],
            [
                'title' => 'Basic Bandaging Principles',
                'content' => 'A bandage should control bleeding without cutting off circulation. If toes swell or the animal becomes more distressed, remove the bandage and seek veterinary care.',
            ],
            [
                'title' => 'Feeding Stray Animals Responsibly',
                'content' => 'Offer water and appropriate food, then contact local rescue networks for medical support and sterilization options. Feeding alone does not solve long-term community animal health needs.',
            ],
            [
                'title' => 'Understanding Zoonotic Risks',
                'content' => 'Some parasites and infections can transfer between animals and humans. Hand washing after cleaning litter areas and prompt treatment of pet illness protect the whole household.',
            ],
            [
                'title' => 'Care for Newly Neutered Pets',
                'content' => 'Restrict jumping, keep the incision dry, and use an e-collar if licking starts. Mild tiredness can occur after anesthesia, but complete refusal to eat should be reported.',
            ],
            [
                'title' => 'How Weather Changes Affect Joints',
                'content' => 'Cold and damp conditions can increase stiffness in arthritic pets. Shorter outings, warm resting areas, and prescribed pain control improve comfort during weather shifts.',
            ],
            [
                'title' => 'Identifying Respiratory Distress',
                'content' => 'Open-mouth breathing in cats, blue-tinged gums, or exaggerated chest effort are emergency signs. Minimize stress and transport the animal for urgent oxygen support.',
            ],
            [
                'title' => 'Why Follow-Up Appointments Matter',
                'content' => 'Many conditions improve gradually. Follow-up visits allow dose adjustments and confirm healing. Skipping rechecks can allow partial recovery to relapse.',
            ],
            [
                'title' => 'Houseplants That Put Pets at Risk',
                'content' => 'Lilies, philodendron, and oleander are among plants that can harm pets. Identify every plant in the home and move toxic species out of reach.',
            ],
            [
                'title' => 'Supporting Pets During Family Moves',
                'content' => 'Keep feeding schedules stable and set up a quiet room first in the new home. Familiar blankets and gradual exploration reduce relocation stress.',
            ],
            [
                'title' => 'When Diarrhea Needs Testing',
                'content' => 'Short mild diarrhea may settle with a bland diet, but blood, fever, repeated vomiting, or diarrhea lasting beyond a day needs diagnostics and targeted treatment.',
            ],
            [
                'title' => 'Working With Your Veterinary Team',
                'content' => 'Clear communication about symptoms, budget limits, and home constraints leads to better care plans. Veterinary treatment is most successful as a partnership with owners.',
            ],
            [
                'title' => 'Preventing Leash Injuries',
                'content' => 'Retractable leashes can cause burns and loss of control. A standard leash and well-fitted harness offer safer daily walks, especially for strong or reactive dogs.',
            ],
            [
                'title' => 'Care Tips for Adopted Senior Pets',
                'content' => 'Older adopted animals deserve baseline bloodwork, dental checks, and time to decompress. With proper care, senior pets often settle into affectionate, rewarding companions.',
            ],
            [
                'title' => 'Why Pain Management Is Essential',
                'content' => 'Untreated pain slows healing and changes behavior. Modern veterinary medicine offers multiple options for short-term and chronic pain. No pet should “just endure it.”',
            ],

            // Arabic posts mixed in (realistic veterinary content)
            [
                'title' => 'أهمية توفير مياه نظيفة طوال اليوم',
                'content' => 'نقص المياه يؤثر بسرعة على الكلى والهضم، خصوصًا في الصيف. تأكد من تنظيف وعاء الماء يوميًا ووضع أكثر من مصدر في المنزل، وراقب أي انخفاض مفاجئ في الشرب.',
            ],
            [
                'title' => 'علامات تستدعي زيارة بيطرية عاجلة',
                'content' => 'صعوبة التنفس، النزيف غير المتوقف، التشنجات، التسمم المحتمل، أو التوقف عن التبول حالات طارئة. التدخل السريع قد ينقذ حياة الحيوان ويمنع المضاعفات الخطيرة.',
            ],
            [
                'title' => 'العناية بالحيوانات بعد الإنقاذ',
                'content' => 'بعد الإنقاذ يحتاج الحيوان إلى فحص شامل للطفيليات والجروح والجفاف. العزل المؤقت عن باقي الحيوانات في المنزل خطوة مهمة حتى تظهر نتائج الفحص الأولي.',
            ],
            [
                'title' => 'التغذية السليمة للقطط المنزلية',
                'content' => 'القطط تحتاج بروتينًا عالي الجودة ومصادر مياه كافية. الاعتماد على بقايا الطعام البشري قد يسبب نقصًا غذائيًا أو مشاكل هضمية. استشر الطبيب قبل أي تغيير كبير في النظام الغذائي.',
            ],
            [
                'title' => 'الوقاية أفضل من العلاج في موسم البراغيث',
                'content' => 'علاج البراغيث لا يتوقف على الحيوان فقط، بل يشمل البيئة المحيطة. الوقاية الشهرية أقل تكلفة وأأمن من مواجهة التهابات الجلد الثانوية الناتجة عن الحكة الشديدة.',
            ],
            [
                'title' => 'كيف تلاحظ ألم المفاصل مبكرًا',
                'content' => 'التردد في الصعود أو النزول، التيبس بعد الراحة، وقلة اللعب قد تشير إلى ألم مفصلي. التشخيص المبكر يساعد على وضع خطة علاج تحافظ على حركة الحيوان وجودة حياته.',
            ],
            [
                'title' => 'أخطاء شائعة عند إعطاء الدواء',
                'content' => 'لا توقف المضاد الحيوي مبكرًا، ولا تستخدم دواء إنسان أو دواء حيوان آخر دون استشارة. الجرعة الخاطئة قد تكون غير فعالة أو سامة حسب وزن الحيوان وحالته.',
            ],
            [
                'title' => 'الاستعداد لفصل الصيف مع حيواناتك',
                'content' => 'تجنب المشي في أوقات الذروة، افحص حرارة الأرض، ووفر مناطق ظل وماء باردة. الإجهاد الحراري يتطور بسرعة ويمكن أن يتحول إلى حالة طارئة خلال وقت قصير.',
            ],
        ];
    }
}