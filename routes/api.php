<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\RescueReportController;
use App\Http\Controllers\BackupRequestController;
use App\Http\Controllers\RescueConsultationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TransparencyDashboardController;
use App\Http\Controllers\SponsorshipController;
use App\Http\Controllers\AnimalUpdateController;
use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\AwarenessPostController;
use App\Http\Controllers\GeneralConsultationController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\CommunityPostController
;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ====================== Public Routes ======================
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login',    [LoginController::class, 'login']);

    // Public Animals Routes (all can view)
    Route::get('/view_animals', [AnimalController::class, 'index']);     
    Route::get('/view_animal_details/{animal}', [AnimalController::class, 'show']); 
    
    // Public Rescue Reports
    Route::post('/rescue/reports', [RescueReportController::class, 'store']);

    // Public Financial Transparency Routes
    Route::prefix('financial')->group(function () {
        Route::get('/summary', [TransparencyDashboardController::class, 'getFinancialSummary']);
        Route::get('/expenses', [TransparencyDashboardController::class, 'getPublicExpenses']);
    });

    // Public Awareness Educational Posts Routes
    Route::get('/awareness-posts', [AwarenessPostController::class, 'index']);
    Route::get('/awareness-posts/{id}/likers', [AwarenessPostController::class, 'getPostLikers']);
    Route::get('/awareness-likes-count/{id}', [AwarenessPostController::class, 'getPostAwarenessLikesCount']);

    // Public routes available to all authenticated users (View and Interact)
    Route::get('/community/posts', [CommunityPostController::class, 'index']);
    Route::get('/community/categories', [CommunityPostController::class, 'categories']);
    Route::post('/community/posts/{id}/toggle-like', [CommunityPostController::class, 'toggleLike']);
    Route::get('/community/posts/{id}/likes', [CommunityPostController::class, 'getPostLikesData']);
    Route::get('/community/categories', [CommunityPostController::class, 'categories']);
    // ====================== Protected Routes (Sanctum) ======================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [LoginController::class, 'logout']);

        // OTP Verification Routes
        Route::post('/verify-otp', [OTPController::class, 'verify']);
        Route::post('/resend-otp', [OTPController::class, 'resend']);

        // Forgot Password Routes
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
        Route::post('/reset-password',  [ForgotPasswordController::class, 'reset']);

        // User Profile Management Routes
        Route::prefix('profile')->group(function () {
            Route::get('/show', [ProfileController::class, 'show']);
            Route::put('/update', [ProfileController::class, 'update']);
            Route::put('/change-password', [ProfileController::class, 'changePassword']);

            Route::get('/vets/{id}', [ProfileController::class, 'getVetProfile']);
            Route::get('/volunteers/{id}', [ProfileController::class, 'getVolunteerProfile']);
            Route::post('/vet/update', [ProfileController::class, 'updateVetProfile']);
            Route::post('/volunteer/update', [ProfileController::class, 'updateVolunteerProfile']);
        });

        // Personal Donation History
        Route::get('/my-donations', [TransparencyDashboardController::class, 'getMyDonations']);

        // Awareness Posts Engagement (Like / Unlike)
        Route::post('/awareness-posts/{id}/like', [AwarenessPostController::class, 'toggleLike']);

        // General Consultations Management (Regular User Side)
        Route::post('/general-consultations/store', [GeneralConsultationController::class, 'store']);
        Route::put('/general-consultations/{id}/update-question', [GeneralConsultationController::class, 'updateQuestion']);
        Route::delete('/general-consultations/{id}/delete-question', [GeneralConsultationController::class, 'destroyQuestion']);

        Route::get('/admin/approved-counts', [AdminVerificationController::class, 'getApprovedCounts']);

        // ====================== Emergency Rescue Reports Tracking ======================
        Route::prefix('rescue/reports')->group(function () {
            Route::get('/{id}/track', [RescueReportController::class, 'track']);
            Route::patch('/{id}/status', [RescueReportController::class, 'updateStatus'])
                ->middleware('role:Volunteer');
            Route::patch('/{id}/accept', [RescueReportController::class, 'acceptReport'])
                ->middleware('role:Volunteer');
            Route::get('/{id}/consultations', [RescueConsultationController::class, 'getReportConsultations'])
                ->middleware('role:Volunteer');
        });

        // ====================== Field Volunteer Live Actions ======================
        Route::middleware(['auth:sanctum', 'role:volunteer'])->prefix('volunteer')->group(function () {
            Route::post('/update-location', [RescueReportController::class, 'updateVolunteerLocation']);
            Route::get('/available-reports', [RescueReportController::class, 'availableReports']);

            Route::post('/backup-requests', [BackupRequestController::class, 'store']);
            Route::put('/backup-requests/{id}/accept', [BackupRequestController::class, 'acceptBackup']);

            Route::get('/backup-requests/available', [BackupRequestController::class, 'getAvailableBackupRequests']);

            Route::post('/rescue-consultations', [RescueConsultationController::class, 'store']);
        });

        // ====================== Professional Veterinarian Work Station ======================
        Route::group(['middleware' => ['auth:sanctum', 'role:veterinarian']], function () {
            // Field Emergency Rescue Consultations
            Route::put('/rescue-consultations/{id}/answer', [RescueConsultationController::class, 'answer']);
            Route::get('/rescue-consultations/pending', [RescueConsultationController::class, 'getPendingConsultations']);  
            
            // Educational Articles & Management
            Route::post('/awareness-posts/store', [AwarenessPostController::class, 'store']);
            Route::post('/awareness-posts/update/{id}', [AwarenessPostController::class, 'update']);
            Route::delete('/awareness-posts/delete/{id}', [AwarenessPostController::class, 'destroy']);

            // Public "Ask a Doctor" Consultation Module
            Route::get('/general-consultations/doctor-list', [GeneralConsultationController::class, 'getDoctorConsultations']);
            Route::put('/general-consultations/{id}/answer', [GeneralConsultationController::class, 'answer']);
            Route::put('/general-consultations/{id}/update-answer', [GeneralConsultationController::class, 'updateAnswer']);
            Route::delete('/general-consultations/{id}/delete-answer', [GeneralConsultationController::class, 'destroyAnswer']);
        });

        // ====================== Donations and Receipts Submission ======================
        Route::prefix('donations')->group(function () {
            Route::post('/store', [DonationController::class, 'store']);
        });

        // ====================== Independent Full Sponsorship System ======================
        Route::prefix('sponsorships')->group(function () {
            Route::post('/request', [SponsorshipController::class, 'requestSponsorship']);
            Route::post('/{id}/renew', [SponsorshipController::class, 'renewPayment']); 
            Route::get('/my-sponsorships', [SponsorshipController::class, 'mySponsorships']);

            Route::get('/available-animals', [SponsorshipController::class, 'availableAnimalsForSponsorship']);
        });

        // ====================== SuperAdmin Central Command & Financial Dashboard ======================
        Route::middleware('role:admin|SuperAdmin|super_admin')->prefix('admin')->group(function () {
            // Professional Professional Account Status Approvals & Verification
            Route::patch('/veterinarians/{id}/approve', [AdminVerificationController::class, 'approveVeterinarian']);
            Route::patch('/volunteers/{id}/approve', [AdminVerificationController::class, 'approveVolunteer']);            

            // Professional Account Suspensions & Blocks
            Route::patch('/veterinarians/{id}/block',   [AdminVerificationController::class, 'blockVeterinarian']);
            Route::patch('/volunteers/{id}/block',      [AdminVerificationController::class, 'blockVolunteer']);

            Route::post('/community/posts', [CommunityPostController::class, 'store']);
            Route::post('/community/posts/{id}', [CommunityPostController::class, 'update']);
            Route::delete('/community/posts/{id}', [CommunityPostController::class, 'destroy']);

            // Public Donation Audit Logs Management
            Route::get('/donations/pending', [DonationController::class, 'getPendingDonations']);
            Route::patch('/donations/{id}/approve', [DonationController::class, 'approveDonation']);
            Route::patch('/donations/{id}/reject', [DonationController::class, 'rejectDonation']);
            
            // Full Sponsorship Verification & Instalment Auditing
            Route::post('/sponsorship-payments/{id}/verify', [SponsorshipController::class, 'verifyPayment']);
            Route::get('/sponsorships', [SponsorshipController::class, 'index']);
            Route::get('/sponsorships/{id}', [SponsorshipController::class, 'show']);

            // Animal Treatment Timeline Updates
            Route::post('/animals/{animal}/updates', [AnimalUpdateController::class, 'store']);

            // Shelter Financial and Expense Management
            Route::get('/donations/financial-report/data', [TransparencyDashboardController::class, 'getFinancialReportData']);
            Route::post('/expenses/store', [ExpenseController::class, 'store']);

            // Adoption Application Processing Flow
            Route::get('/applications', [AdoptionApplicationController::class, 'index']);
            Route::get('/applications/{application}', [AdoptionApplicationController::class, 'show']);
            Route::post('/applications/{application}/approve', [AdoptionApplicationController::class, 'approve']);
            Route::post('/applications/{application}/reject', [AdoptionApplicationController::class, 'reject']);
            Route::patch('/applications/{application}/status', [AdoptionApplicationController::class, 'changeStatus']);

        });

        // ====================== Public/User Adoption Flow ======================
        Route::prefix('adoption')->group(function () {
            Route::post('/store', [AdoptionApplicationController::class, 'store']);
            Route::get('/my-applications', [AdoptionApplicationController::class, 'myApplications']);
            Route::put('/applications/{application}', [AdoptionApplicationController::class, 'update']);
            Route::delete('/applications/{application}', [AdoptionApplicationController::class, 'destroy']);
        });

        // ====================== Internal Animal Catalog Management ======================
        Route::prefix('animals')->group(function () {
            // Add new shelter animals
            Route::post('/add', [AnimalController::class, 'store'])
                ->middleware('role:SuperAdmin');

            // Update baseline animal profiling details
            Route::put('/edit/{animal}', [AnimalController::class, 'update'])
                ->middleware('role:SuperAdmin|Veterinarian');

            // Delete animal files
            Route::delete('/delete/{animal}', [AnimalController::class, 'destroy'])
                ->middleware('role:SuperAdmin');

            // Purge specific media photos
            Route::delete('/photos/{photo}', [AnimalController::class, 'deletePhoto'])
                ->middleware('role:SuperAdmin|Veterinarian');
        });

    });
});