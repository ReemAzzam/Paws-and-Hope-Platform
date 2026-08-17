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
        // جلب المستخدمين العاديين
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'regular_user');
        })->get();

        if ($users->isEmpty()) {
            $this->command->warn('Skip seeding consultations: No regular users found.');
            return;
        }

        // جلب الأطباء البيطريين المعتمدين فقط
        $vets = Veterinarian::where('is_approved', true)->get();

        // 50 سؤالاً وجواباً بيطرياً متنوعاً
        $sampleConsultations = [
            // --- Cat Consultations ---
            [
                'question' => 'My cat has lost her appetite and has been lethargic for two days. What could be the cause?',
                'answer'   => 'Loss of appetite and lethargy in cats can indicate a fever, viral infection, or gastrointestinal distress. Ensure she stays hydrated and visit a vet if she hasn\'t eaten in 24 hours.',
                'status'   => 'answered',
            ],
            [
                'question' => 'What is the best way to clean a cat\'s teeth at home to prevent tartar buildup and bad breath?',
                'answer'   => 'Use a soft enzymatic toothpaste designed specifically for pets along with a finger toothbrush. Introduce brushing gradually with positive reinforcement.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My outdoor cat returned home limping on his front right leg. How can I assess if it is broken?',
                'answer'   => 'Check for swelling, heat, or open wounds. Avoid pressing hard. Keep the cat confined in a small room or carrier to limit movement and seek immediate veterinary evaluation.',
                'status'   => 'answered',
            ],
            [
                'question' => 'How often should I deworm my adult indoor cat, and what are the symptoms of internal parasites?',
                'answer'   => 'Indoor cats should be dewormed every 6 months. Common signs include vomiting, diarrhea, a bloated abdomen, dull coat, or visible worms in stools.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My kitten keeps scratching her ears and shaking her head. I noticed dark brown discharge.',
                'answer'   => 'This appearance strongly suggests ear mites (Otodectes cynotis). A veterinarian can confirm this via ear swab and prescribe targeted ear drops.',
                'status'   => 'answered',
            ],
            [
                'question' => 'Is it safe to give my cat commercial cow\'s milk as a treat?',
                'answer'   => 'No, most adult cats are lactose intolerant. Drinking cow\'s milk often leads to stomach upset, gas, and severe diarrhea.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My male cat is straining in the litter box and crying in pain. What should I do urgently?',
                'answer'   => 'This is a critical emergency indicative of a urethral blockage. Immediate veterinary intervention is required within hours to prevent kidney damage or death.',
                'status'   => 'answered',
            ],
            [
                'question' => 'Why is my Persian cat developing tear staining under her eyes, and how can I clean it?',
                'answer'   => 'Brachycephalic breeds often have shallow eye sockets leading to tear duct blockage. Clean gently daily with warm water or a specialized pet eye wipe.',
                'status'   => 'answered',
            ],
            [
                'question' => 'At what age should I get my female kitten spayed?',
                'answer'   => 'Sterilization is generally recommended between 4 to 6 months of age, ideally before her first heat cycle.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My cat has started drinking excessive amounts of water and urinating frequently. What could this mean?',
                'answer'   => 'Increased thirst and urination (polydipsia/polyuria) can be signs of chronic kidney disease, diabetes mellitus, or hyperthyroidism.',
                'status'   => 'answered',
            ],

            // --- Dog Consultations ---
            [
                'question' => 'What core vaccinations are required for a 3-month-old puppy, and what is the recommended schedule?',
                'answer'   => 'Core vaccinations include the DHPP combination (Distemper, Hepatitis, Parvovirus, Parainfluenza) and Rabies. Booster shots are required every 3-4 weeks until 16 weeks of age.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My small dog accidentally ate a piece of dark chocolate an hour ago. Is chocolate toxic to dogs?',
                'answer'   => 'Yes, dark chocolate contains high levels of theobromine which is toxic. Depending on the dog\'s weight and amount ingested, inducing vomiting by a vet may be necessary.',
                'status'   => 'answered',
            ],
            [
                'question' => 'How can I prevent flea and tick infestations on my Golden Retriever during summer?',
                'answer'   => 'Use veterinary-approved monthly topical treatments, oral chewables, or tick collars. Routinely inspect your dog after walks in tall grass.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My puppy eats his own stool. Why does he do this and how can I stop this habit?',
                'answer'   => 'Coprophagia can stem from behavioral curiosity, nutritional deficiency, or digestive issues. Clean up waste immediately and train with positive reinforcement.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My German Shepherd has red, inflamed skin and licks his paws constantly. Could it be food allergies?',
                'answer'   => 'Yes, paw licking and dermatitis are classic allergy signs. An elimination diet trial under veterinary supervision can help identify food sensitivities.',
                'status'   => 'answered',
            ],
            [
                'question' => 'How do I safely trim my dog\'s dark nails without cutting the quick?',
                'answer'   => 'Trim small amounts at a time from the tip. Look for a dark spot in the center of the cross-section which indicates you are approaching the quick.',
                'status'   => 'answered',
            ],
            [
                'question' => 'What are the early warning signs of hip dysplasia in growing large dog breeds?',
                'answer'   => 'Signs include a bunny-hopping gait, difficulty standing up, reluctance to jump or climb stairs, stiffness in hind legs, and loss of muscle mass in thighs.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My dog is coughing with a dry, honking sound after staying at a boarding kennel.',
                'answer'   => 'This classic "honking" cough suggests Kennel Cough (Infectious Tracheobronchitis). Keep him isolated and consult a vet for anti-tussives or antibiotics.',
                'status'   => 'answered',
            ],
            [
                'question' => 'Can I give human ibuprofen or paracetamol to my dog for pain relief?',
                'answer'   => 'NO! Human pain medications like ibuprofen and paracetamol are extremely toxic to dogs and can cause kidney failure or severe stomach ulcers.',
                'status'   => 'answered',
            ],
            [
                'question' => 'How much exercise does a 1-year-old Labrador Retriever need daily?',
                'answer'   => 'High-energy breeds like Labradors require about 60 to 90 minutes of active exercise daily, including physical play and mental stimulation.',
                'status'   => 'answered',
            ],

            // --- Rabbit & Exotic Pet Consultations ---
            [
                'question' => 'I noticed severe hair loss and white crusts around my rabbit\'s ears. Is this ear mites or a fungal infection?',
                'answer'   => 'These symptoms strongly suggest ear mites (Psoroptes cuniculi) or ringworm. Avoid removing crusts forcefully and consult a vet for anti-parasitic treatment.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My rabbit has stopped eating and hasn\'t produced fecal pellets for 12 hours. Is this an emergency?',
                'answer'   => 'YES, this is Gastrointestinal (GI) Stasis, a life-threatening medical emergency in rabbits. Immediate emergency veterinary care is critical.',
                'status'   => 'answered',
            ],
            [
                'question' => 'What is the recommended dietary breakdown for a healthy adult pet rabbit?',
                'answer'   => 'An adult rabbit diet should consist of 80-85% fresh grass hay (e.g., Timothy), 10-15% fresh leafy greens, and a minimal amount (5%) of high-fiber pellets.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My pet parrot is plucking his own feathers on his chest. What causes this self-mutilation?',
                'answer'   => 'Feather plucking can be caused by medical issues (parasites, nutritional imbalances) or psychological distress like boredom, anxiety, and lack of social interaction.',
                'status'   => 'answered',
            ],
            [
                'question' => 'Is alfalfa hay suitable for adult pet rabbits as a daily food source?',
                'answer'   => 'No, alfalfa hay is too high in protein and calcium for adult rabbits and can cause bladder stones. It should only be fed to young, growing bunnies.',
                'status'   => 'answered',
            ],

            // --- Bird & Small Animal Consultations ---
            [
                'question' => 'My budgie is sitting on the bottom of the cage with puffed-up feathers and eyes closed.',
                'answer'   => 'Birds mask illness until severe. Fluffed feathers and staying at the bottom of the cage indicate a critical condition requiring urgent veterinary warmth and care.',
                'status'   => 'answered',
            ],
            [
                'question' => 'How can I trim the overgrown beak of my pet turtle safely?',
                'answer'   => 'Do not attempt to trim a turtle\'s beak at home without training. A vet can safely trim and shape it using specialized dental tools.',
                'status'   => 'answered',
            ],
            [
                'question' => 'My hamster has developed a wet tail and severe diarrhea. What immediate care is needed?',
                'answer'   => 'Wet Tail (pro proliferative ileitis) is a contagious and bacterial disease in hamsters. It requires swift veterinary administration of antibiotics and hydration.',
                'status'   => 'answered',
            ],

            // --- Pending Consultations (Questions without answers) ---
            [
                'question' => 'My dog has a small pink bump on his elbow that appeared last week. Should I be worried?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'What are the best environmental enrichment toys for an indoor cat that gets bored easily?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My cat started hyperventilating and open-mouth breathing after a quick play session. Is that normal?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'How do I safely transition my puppy from milk formula to solid kibble food?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My cockatiel is sneezing repeatedly and has slight nostril discharge. What antibiotics can I buy?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'Is it safe to use lavender essential oil diffusers in the same room as my pet rabbit?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My dog ate a rubber ball piece. He vomited once but is acting normal now. Should I bring him in?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'How can I tell if my senior cat is suffering from arthritis in her lower back?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My dog\'s eyes look cloudy in the center. Is this cataracts or nuclear sclerosis?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'What is the safe dosage of probiotics for a dog suffering from acute mild diarrhea?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My cat keeps scratching her neck until it bleeds. Flea treatment didn\'t help. What next?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'Why does my dog lick his paws obsessively every evening before sleeping?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'Can domestic cats get cold or flu infections from their human owners?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'How often should I clean my guinea pig\'s cage to avoid respiratory infections?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My dog\'s nose is dry and cracking. Is this a sign of dehydration or fever?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'What precautions should I take when introducing a new kitten to an older resident dog?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My rabbit\'s urine turned bright reddish-orange today. Is there blood in her urine?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'What dietary adjustments should I make for a dog recently diagnosed with early stage renal failure?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My puppy ingested a small coin. Should I wait for it to pass or go to the emergency clinic?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'Why is my cat suddenly skipping the litter box and urinating on soft blankets?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'Are human fish oil omega-3 supplements safe for dogs with dry skin?',
                'answer'   => null,
                'status'   => 'pending',
            ],
            [
                'question' => 'My canary stopped singing and is losing feathers around its neck outside molting season.',
                'answer'   => null,
                'status'   => 'pending',
            ],
        ];

        foreach ($sampleConsultations as $index => $data) {
            // توزيع الأسئلة بالتساوي والتتابع على مستخدمي النظام العاديين
            $user = $users[$index % $users->count()];

            $vetId = null;
            if ($data['status'] === 'answered' && $vets->isNotEmpty()) {
                // اختيار طبيب بيطري معتمد عشوائياً للإجابة
                $vetId = $vets->random()->id;
            }

            GeneralConsultation::create([
                'user_id'         => $user->id,
                'veterinarian_id' => $vetId,
                'question'        => $data['question'],
                'answer'          => $data['answer'],
                'status'          => $data['status'],
                'created_at'      => now()->subDays(rand(1, 30)),
                'updated_at'      => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info('✅ GeneralConsultationSeeder executed successfully with 50 entries!');
    }
}