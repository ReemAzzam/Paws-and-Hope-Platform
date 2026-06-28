<?php

namespace App\Services;

use App\Models\LostFound;

class LostFoundMatcher
{
    /**
     * حساب المطابقة بين منشور Lost و Found
     */
    public function calculateMatch(LostFound $lost, LostFound $found): array
    {
        $score = 0;
        $reasons = [];
        $negativeReasons = [];

        // ====================== 1. الموقع الجغرافي (35 نقطة) ======================
        $distance = $this->calculateDistance(
            $lost->latitude, $lost->longitude,
            $found->latitude, $found->longitude
        );

        if ($distance <= 5) {
            $score += 35;
            $reasons[] = "الموقع قريب جداً ({$distance} كم)";
        } elseif ($distance <= 15) {
            $score += 25;
            $reasons[] = "الموقع قريب ({$distance} كم)";
        } elseif ($distance <= 30) {
            $score += 15;
            $reasons[] = "في نفس المدينة";
        } else {
            $negativeReasons[] = "المسافة بعيدة ({$distance} كم)";
        }

        // ====================== 2. نوع الحيوان (25 نقطة) ======================
        if ($lost->animal_type === $found->animal_type) {
            $score += 25;
            $reasons[] = "نوع الحيوان مطابق";
        } else {
            $score -= 10;
            $negativeReasons[] = "نوع الحيوان مختلف";
        }

        // ====================== 3. الوصف والعلامات (20 نقطة) ======================
        $similarity = $this->textSimilarity($lost->description, $found->description);
        $score += (int)($similarity * 20);

        if ($similarity > 0.6) {
            $reasons[] = "الوصف يتطابق بشكل كبير";
        }

        // ====================== 4. التاريخ (10 نقاط) ======================
        $daysDiff = abs($lost->created_at->diffInDays($found->created_at));
        if ($daysDiff <= 3) {
            $score += 10;
            $reasons[] = "تم العثور عليه قريباً من تاريخ الفقدان";
        } elseif ($daysDiff <= 10) {
            $score += 5;
            $reasons[] = "الزمن معقول";
        } else {
            $score -= 5;
            $negativeReasons[] = "الزمن بعيد";
        }

        $finalScore = max(0, min(100, round($score)));

        return [
            'score' => $finalScore,
            'match_level' => $this->getMatchLevel($finalScore),
            'reasons' => $reasons,
            'negativeReasons' => $negativeReasons,
            'distance_km' => round($distance, 2)
        ];
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function textSimilarity($text1, $text2)
    {
        $text1 = strtolower($text1);
        $text2 = strtolower($text2);
        similar_text($text1, $text2, $percent);
        return $percent / 100;
    }

    private function getMatchLevel($score)
    {
        if ($score >= 80) return 'excellent';
        if ($score >= 65) return 'high';
        if ($score >= 45) return 'medium';
        return 'low';
    }
}
