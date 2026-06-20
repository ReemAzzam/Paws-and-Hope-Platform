<?php

namespace App\Http\Controllers;

use App\Models\UserMatchingPreference;
use App\Models\Animal;
use App\Models\MatchingQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchingController extends Controller
{
     public function getQuestions()
    {
        $questions = MatchingQuiz::orderBy('step_id')
                                 ->orderBy('question_order')
                                 ->get([
                                     'id',
                                     'step_id as stepId',
                                     'question_text as questionText',
                                     'options',
                                     'hint',
                                     'key'
                                 ]);

        // ترتيب الخطوات (Steps)
        $steps = [
            ['id' => 1, 'title' => "Pet Preference", 'rangeText' => "Questions 1 - 3"],
            ['id' => 2, 'title' => "Home & Lifestyle", 'rangeText' => "Questions 4 - 7"],
            ['id' => 3, 'title' => "Personality & Feelings", 'rangeText' => "Questions 8 - 11"],
            ['id' => 4, 'title' => "Commitment & Details", 'rangeText' => "Questions 12 - 14"],
        ];

        return response()->json([
            'success' => true,
            'steps' => $steps,
            'questions' => $questions
        ]);
    }

    /**
     * حفظ إجابات الاستمارة + حساب المطابقة
     */
    public function storePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferred_animal_type' => 'required|string',
            'preferred_age'         => 'required|string',
            'preferred_size'        => 'required|string',
            'housing_type'          => 'required|string',
            'activity_level'        => 'required|string',
            'hours_alone_daily'     => 'nullable|integer|min:0',
            'children_status'       => 'required|string',
            'preferred_personality' => 'nullable|string',
            'has_other_pets'        => 'boolean',
            'long_term_commitment'  => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $preference = UserMatchingPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->all()
        );

        $recommendations = $this->calculateMatching($preference);

        $preference->update([
            'matching_results' => $recommendations,
            'highest_score'    => collect($recommendations)->max('matchPercentage') ?? 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حساب المطابقة بنجاح',
            'data' => [
                'overallScore' => $preference->highest_score,
                'summary'      => $this->generateSummary($recommendations),
                'topMatches'   => $recommendations
            ]
        ]);
    }

    /**
     * جلب آخر Matching Test للمستخدم
     */
    public function getLastMatching(Request $request)
    {
        $preference = UserMatchingPreference::where('user_id', $request->user()->id)
                        ->latest()
                        ->first();

        if (!$preference) {
            return response()->json([
                'success' => false,
                'message' => 'لم تقم بإجراء اختبار مطابقة بعد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'overallScore' => $preference->highest_score,
                'summary'      => $this->generateSummary($preference->matching_results),
                'topMatches'   => $preference->matching_results ?? [],
                'created_at'   => $preference->created_at
            ]
        ]);
    }

    /**
     * جلب كل Matching Tests للمستخدم (التاريخ)
     */
    public function getAllMatchings(Request $request)
    {
        $preferences = UserMatchingPreference::where('user_id', $request->user()->id)
                        ->latest()
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

      // ====================== Logic حساب النقاط ======================
    private function calculateMatching($preference)
    {
        $animals = Animal::where('availability_status', 'available')
                         ->with('photos')
                         ->get();

        $results = [];

        foreach ($animals as $animal) {
            $score = 0;
            $reasons = [];
            $negativeReasons = [];

            // ====================== Group 1: Pet Preference (30 نقاط) ======================
            if ($preference->preferred_animal_type === "Doesn't matter" ||
                strtolower($preference->preferred_animal_type) === strtolower($animal->type)) {
                $score += 12;
                $reasons[] = "نوع الحيوان يطابق تفضيلك";
            } else {
                $score -= 8;
                $negativeReasons[] = "نوع الحيوان مختلف عن تفضيلك";
            }

            // سؤال 2: العمر
            if ($animal->age !== null) {
                $ageGroup = $this->getAgeGroup($animal->age);
                if ($preference->preferred_age === "Any" || $preference->preferred_age === $ageGroup) {
                    $score += 10;
                    $reasons[] = "العمر مناسب";
                }
            }

            // سؤال 3: الحجم
            if ($preference->preferred_size === "Doesn't matter" ||
                strtolower($preference->preferred_size) === strtolower($animal->size ?? '')) {
                $score += 8;
                $reasons[] = "الحجم مناسب";
            }

            // ====================== Group 2: Home & Lifestyle (30 نقاط) ======================
            if ($preference->housing_type === "House with yard" && in_array($animal->size, ['medium', 'large'])) {
                $score += 9;
                $reasons[] = "منزل مع حديقة مناسب لحجم الحيوان";
            } elseif ($preference->housing_type === "Apartment" && $animal->size === 'small') {
                $score += 8;
                $reasons[] = "شقة مناسبة لحجم الحيوان الصغير";
            }

            if ($preference->hours_alone_daily !== null) {
                if ($preference->hours_alone_daily <= 4) {
                    $score += 7;
                    $reasons[] = "الحيوان لن يبقى وحده كثيراً";
                } elseif ($preference->hours_alone_daily > 8) {
                    $score -= 6;
                    $negativeReasons[] = "الحيوان سيكون وحده لفترة طويلة";
                }
            }

            // ====================== Group 3: Personality & Compatibility (25 نقاط) ======================
            if ($preference->preferred_personality) {
                $score += 8;
                $reasons[] = "الشخصية تتوافق";
            }

            if ($preference->has_other_pets) {
                $score += 7;
                $reasons[] = "توافق مع وجود حيوانات أخرى";
            }

            if ($preference->children_status !== "No" && $animal->is_urgent == false) {
                $score += 7;
                $reasons[] = "مناسب لوجود أطفال";
            }

            // ====================== Group 4: Commitment (15 نقاط) ======================
            if ($preference->long_term_commitment) {
                $score += 10;
                $reasons[] = "مستعد للالتزام طويل الأمد";
            }

            $finalScore = max(0, min(100, round($score)));

            $results[] = [
                'id'              => $animal->id,
                'name'            => $animal->name ?? 'Unknown',
                'breed'           => $animal->type,
                'age'             => $animal->age ? $animal->age . ' سنة' : 'غير معروف',
                'gender'          => $animal->gender,
                'matchPercentage' => $finalScore,
                'imageUrl'        => $animal->photos->first()?->photo_url ?? '/images/default-pet.jpg',
                'tags'            => $reasons,
                'negativeTags'    => $negativeReasons,
            ];
        }

        // ترتيب تنازلي
        usort($results, fn($a, $b) => $b['matchPercentage'] <=> $a['matchPercentage']);

        return array_slice($results, 0, 3);
    }

    /**
     * مساعد لتحديد فئة العمر
     */
    private function getAgeGroup($age)
    {
        if ($age <= 2) return "Puppy/Kitten";
        if ($age <= 7) return "Adult";
        return "Senior";
    }

    private function generateSummary($recommendations)
    {
        if (empty($recommendations)) {
            return "لم نجد مطابقات قوية حالياً، حاول تعديل إجاباتك.";
        }
        return "Based on your answers, we found great matches for you!";
    }

}
