<?php

namespace App\Support;

use App\Models\Animal;
use App\Models\AdoptionApplication;
use App\Models\RescueReport;

class NotificationTemplates
{
    // ================ ADOPTION NOTIFICATIONS ================
    /**
     * إشعار للكفيل عند تقديم طلب تبني جديد
     */
    public static function newAdoptionRequest(
        Animal $animal,
        AdoptionApplication $application
    ): array {
        return [
       'title' => '❤️ Someone Loves Your Sponsored Pet!',
       'body' => "Someone has submitted an adoption request for {$animal->name}. Thank you for helping {$animal->name} get one step closer to a forever home!",
            'data' => [
                'type' => 'new_adoption_request',
                'animal_id' => (string) $animal->id,
                'application_id' => (string) $application->id,
            ]
        ];
    }

    /**
     * إشعار لمقدم الطلب عند الموافقة
     */
    public static function adoptionApproved(
        Animal $animal,
        AdoptionApplication $application
    ): array {
        return [
        'title' => '🎉 Adoption Request Approved!',
        'body' => "Congratulations! Your adoption request for {$animal->name} has been approved. The shelter will contact you soon.",
            'data' => [
                'type' => 'adoption_approved',
                'animal_id' => (string) $animal->id,
                'application_id' => (string) $application->id,
            ]
        ];
    }

    /**
     * إشعار لمقدم الطلب عند الرفض
     */
    public static function adoptionRejected(
        Animal $animal,
        AdoptionApplication $application
    ): array {
        return [
        'title' => 'Adoption Request Update',
        'body' => "Unfortunately, your adoption request for {$animal->name} was not approved this time. We encourage you to keep exploring other wonderful pets waiting for a loving home.",
            'data' => [
                'type' => 'adoption_rejected',
                'animal_id' => (string) $animal->id,
                'application_id' => (string) $application->id,
            ]
        ];
    }

    /**
     * إشعار للكفيل عند تبني الحيوان
     */
    public static function sponsorshipCompleted(
        Animal $animal
    ): array {
        return [
        'title' => '🎉 Your Sponsored Pet Found a Home!',
        'body' => "Lovely {$animal->name} has officially been adopted and found a forever home. Thank you for your kindness and support throughout this journey. Your sponsorship has now been completed.",
            'data' => [
                'type' => 'sponsorship_completed',
                'animal_id' => (string) $animal->id,
            ]
        ];
    }
    // ================ RESCUE NOTIFICATIONS ================
        /**
     * إشعار للمتطوعين عند إنشاء بلاغ جديد
     */
    public static function newRescueReport(
      RescueReport $report
    ): array {
        return [
            'title' => '🚨 Emergency Rescue Needed',
            'body' => "A {$report->severity_level} rescue case has been reported near your location. Your help could save a life.",
            'data' => [
                'type' => 'new_rescue_report',
                'report_id' => (string) $report->id,
            ]
        ];
    }

    /**
     * إشعار لصاحب البلاغ عند قبول المهمة
     */
    public static function rescueAccepted(
        RescueReport $report
    ): array {
        return [
            'title' => '🚑 Volunteer Assigned',
            'body' => 'A volunteer has accepted your rescue report and is on the way.',
            'data' => [
                'type' => 'rescue_accepted',
                'report_id' => (string) $report->id,
            ]
        ];
    }

    /**
     * إشعار لصاحب البلاغ عند تحديث حالة البلاغ
     */
    public static function rescueStatusUpdated(
        RescueReport $report,
        string $status
    ): array {

        $messages = [

            'on_the_way' => [
                'title' => '🚑 Volunteer On The Way',
                'body' => 'A volunteer is on the way to the rescue location.',
            ],

            'on_site' => [
                'title' => '📍 Volunteer Arrived',
                'body' => 'The volunteer has arrived at the rescue location.',
            ],

            'in_clinic' => [
                'title' => '🏥 Animal Reached Clinic',
                'body' => 'The rescued animal has safely arrived at the veterinary clinic.',
            ],

            'resolved' => [
                'title' => '❤️ Rescue Completed',
                'body' => 'The rescue case has been successfully completed. Thank you for caring. you saved a life!',
            ],
        ];

        $notification = $messages[$status] ?? [
            'title' => 'Rescue Update',
            'body' => 'Your rescue report status has been updated.',
        ];

        return [
            'title' => $notification['title'],
            'body' => $notification['body'],
            'data' => [
                'type' => 'rescue_status_updated',
                'status' => $status,
                'report_id' => (string) $report->id,
            ]
        ];
    }

    // ==================LOST AND FOUND NOTIFICATIONS ==================

    public static function lostFoundMatch(string $animalName, int $score): array
    {
        return [
            'title' => '🐾 Potential Match Found!',
            'body'  => "We found a {$score}% match for {$animalName}. Check it now!",
            'data' => [
                'type' => 'lost_found_match',
                'score' => (string)$score,
                'animal_name' => $animalName,
            ]
        ];
    }

    // ================== DONATION NOTIFICATIONS ==================

    public static function newDonation(
        string $userName,
        float $amount
    ): array {
        return [
            'title' => '💰 New Donation Received',
            'body'  => "{$userName} submitted a donation of {$amount}. Please review the receipt.",
            'data' => [
                'type' => 'new_donation',
                'amount' => (string)$amount,
            ]
        ];
    }

    // ================== SPONSORSHIP NOTIFICATIONS ==================
    public static function newSponsorshipRequest(
        string $userName,
        string $animalName
    ): array {
        return [
            'title' => '❤️ New Sponsorship Request',
            'body'  => "{$userName} submitted a sponsorship request for {$animalName}. Please verify the payment receipt.",
            'data' => [
                'type' => 'new_sponsorship_request',
                'animal_name' => $animalName,
            ]
        ];
    }

    public static function sponsorshipRenewal(
        string $userName,
        string $animalName
    ): array {
        return [
            'title' => '🔄 Sponsorship Renewal',
            'body'  => "{$userName} uploaded a sponsorship renewal payment for {$animalName}.",
            'data' => [
                'type' => 'sponsorship_renewal',
                'animal_name' => $animalName,
            ]
        ];
    }

}
