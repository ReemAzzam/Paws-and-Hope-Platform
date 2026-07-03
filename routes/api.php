<?php

use Illuminate\Support\Facades\Route;

// ====================== Auth Controllers ======================
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProfileController;

// ====================== Main Controllers ======================
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AnimalMedicalConditionController;
use App\Http\Controllers\BehavioralAttributeController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\RescueReportController;
use App\Http\Controllers\BackupRequestController;
use App\Http\Controllers\RescueConsultationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TransparencyDashboardController;
use App\Http\Controllers\SponsorshipController;
use App\Http\Controllers\AnimalUpdateController;
use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\AwarenessPostController;
use App\Http\Controllers\GeneralConsultationController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\LostFoundController;
use App\Http\Controllers\Api\FcmTokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ====================== Public Routes ======================
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login',    [LoginController::class, 'login']);

    // Public Animal Data
    Route::get('/animals', [AnimalController::class, 'index']);
    Route::get('/animals/{animal}', [AnimalController::class, 'show']);

    Route::get('/animals/{animal}/vaccinations', [VaccinationController::class, 'showByAnimal']);
    Route::get('/animals/{animal}/behavioral-attributes', [BehavioralAttributeController::class, 'showByAnimal']);
    Route::get('/animals/{animal_id}/medical-conditions', [AnimalMedicalConditionController::class, 'index']);

    // Public Lost & Found
    Route::prefix('lost-found')->group(function () {
       
        Route::get('/', [LostFoundController::class, 'index']);
        Route::get('/nearby', [LostFoundController::class, 'searchNearby']);
        Route::get('/{lostFound}', [LostFoundController::class, 'show']);
        Route::get('/{lostFound}/similar', [LostFoundController::class, 'similarPosts']);
    });

    // Public Transparency
    Route::prefix('financial')->group(function () {
        Route::get('/summary', [TransparencyDashboardController::class, 'getFinancialSummary']);
        Route::get('/expenses', [TransparencyDashboardController::class, 'getPublicExpenses']);
    });

    // ====================== Protected Routes ======================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [LoginController::class, 'logout']);

        // OTP
        Route::post('/verify-otp', [OTPController::class, 'verify']);
        Route::post('/resend-otp', [OTPController::class, 'resend']);

        // Forgot Password
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
        Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

        Route::prefix('lost-found')->group(function () {
            Route::post('/', [LostFoundController::class, 'store']);
    });
        // FCM
        Route::post('/fcm-token', [FcmTokenController::class, 'store']);

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::put('/change-password', [ProfileController::class, 'changePassword']);
        });

        // Matching
        Route::prefix('matching')->group(function () {
            Route::get('/quiz/questions', [MatchingController::class, 'getQuestions']);
            Route::post('/preferences', [MatchingController::class, 'storePreferences']);
            Route::get('/last', [MatchingController::class, 'getLastMatching']);
            Route::get('/history', [MatchingController::class, 'getAllMatchings']);
        });

        // Adoption
        Route::prefix('adoption')->group(function () {
            Route::post('/apply', [AdoptionApplicationController::class, 'store']);
            Route::get('/my-applications', [AdoptionApplicationController::class, 'myApplications']);
        });

        // Donations & Sponsorships
        Route::post('/donations/store', [DonationController::class, 'store']);
        Route::post('/sponsorships/request', [SponsorshipController::class, 'requestSponsorship']);

        // Rescue
        Route::post('/rescue/reports', [RescueReportController::class, 'store']);

        // ====================== Role-Based Routes ======================

        // Volunteer Routes
        Route::middleware('role:volunteer')->prefix('volunteer')->group(function () {
            Route::post('/update-location', [RescueReportController::class, 'updateVolunteerLocation']);
            Route::post('/backup-requests', [BackupRequestController::class, 'store']);
            Route::put('/backup-requests/{id}/accept', [BackupRequestController::class, 'acceptBackup']);
            Route::post('/rescue-consultations', [RescueConsultationController::class, 'store']);
        });

        // Veterinarian Routes
        Route::middleware('role:veterinarian')->group(function () {
            Route::put('/rescue-consultations/{id}/answer', [RescueConsultationController::class, 'answer']);
            Route::get('/rescue-consultations/pending', [RescueConsultationController::class, 'getPendingConsultations']);
        });

        // SuperAdmin Routes
        Route::middleware('role:SuperAdmin')->prefix('admin')->group(function () {
            Route::get('/approved-counts', [AdminVerificationController::class, 'getApprovedCounts']);

            Route::patch('/veterinarians/{id}/approve', [AdminVerificationController::class, 'approveVeterinarian']);
            Route::patch('/volunteers/{id}/approve', [AdminVerificationController::class, 'approveVolunteer']);
            Route::patch('/veterinarians/{id}/block', [AdminVerificationController::class, 'blockVeterinarian']);
            Route::patch('/volunteers/{id}/block', [AdminVerificationController::class, 'blockVolunteer']);

            Route::get('/donations/pending', [DonationController::class, 'getPendingDonations']);
            Route::patch('/donations/{id}/approve', [DonationController::class, 'approveDonation']);
            Route::patch('/donations/{id}/reject', [DonationController::class, 'rejectDonation']);
        });
    });
});
