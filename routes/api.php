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

    // ====================== Auth Public ======================
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login',    [LoginController::class, 'login']);

    // OTP
    Route::post('/verify-otp', [OTPController::class, 'verify']);
    Route::post('/resend-otp', [OTPController::class, 'resend']);

    // Forgot Password
    Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/password/reset',  [ForgotPasswordController::class, 'reset']);

    Route::prefix('animals')->group(function () {
    // ======== Public Animal Routes ========
    Route::get('/', [AnimalController::class, 'index']);
    Route::get('/{animal_id}', [AnimalController::class, 'show']);
     // ======== Public Medical Conditions ========
    Route::get('/{animal_id}/medical-conditions', [AnimalMedicalConditionController::class, 'index']);
    Route::get('/{animal_id}/medical-conditions/{id}', [AnimalMedicalConditionController::class, 'show']);
     Route::get('/{animal_id}/vaccinations', [VaccinationController::class, 'showByAnimal']);
    Route::get('/{animal_id}/behavioral-attributes', [BehavioralAttributeController::class, 'showByAnimal']);
     });

    Route::prefix('rescue/reports')->group(function () {

    Route::post('/', [RescueReportController::class, 'store']);

    // تتبع بلاغ معيّن
    Route::get('/{id}/track', [RescueReportController::class, 'track']);
    });

     // Public Lost & Found
    Route::prefix('lost-found')->group(function () {
        Route::get('/', [LostFoundController::class, 'index']);
        Route::get('/nearby', [LostFoundController::class, 'searchNearby']);
        Route::get('/{lostFound}', [LostFoundController::class, 'show']);
        Route::get('/{lostFound}/similar', [LostFoundController::class, 'similarPosts']);
    });

   // Available Animals For Sponsorship
        Route::get('/available-animals', [SponsorshipController::class, 'availableAnimalsForSponsorship']);

    // ================Community==========
    Route::prefix('community')->group(function () {
    // عرض المنشورات مع الفلاتر
         Route::get('/posts', [CommunityPostController::class, 'index']);

    // عرض تصنيفات المنشورات
         Route::get('/categories', [CommunityPostController::class, 'categories']);

    // عرض بيانات الإعجابات لمنشور معيّن
        Route::get('/posts/{id}/likes', [CommunityPostController::class, 'getPostLikesData']);
   });

    // ================Awareness Posts==========
    Route::prefix('awareness-posts')->group(function () {

        Route::get('/', [AwarenessPostController::class, 'index']);

        Route::get('/{id}/likes-count', [AwarenessPostController::class, 'getPostAwarenessLikesCount']);

        Route::get('/{id}/likers', [AwarenessPostController::class, 'getPostLikers']);
   });

      // ================ Public Transparency =========
    Route::prefix('financial')->group(function () {
        Route::get('/summary', [TransparencyDashboardController::class, 'getFinancialSummary']);
        Route::get('/expenses', [TransparencyDashboardController::class, 'getPublicExpenses']);
        Route::get('/donations', [TransparencyDashboardController::class, 'getMyDonations']);
        Route::get('/report_data',[TransparencyDashboardController::class, 'getFinancialReportData']);
    });

    // ====================== Auth Protected ======================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [LoginController::class, 'logout']);

        // Profile
        Route::prefix('profile')->group(function(){
            //Regular user Profile
            Route::get('/regular-user', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::put('/change-password', [ProfileController::class, 'changePassword']);

            // Veterinarian Profile
            Route::put('/vet/complete',[RegisterController::class,'completeVetProfile'])
            ->middleware('role:veterinarian');
            Route::get('/veterinarians/{id}', [ProfileController::class, 'getVetProfile']);
            Route::put('/veterinarians/update', [ProfileController::class, 'updateVetProfile']);

            // Volunteer Profile
            Route::get('/volunteers/{id}', [ProfileController::class, 'getVolunteerProfile'])
                ->middleware('role:SuperAdmin|veterinarian|volunteer');
            Route::put('/volunteers/complete',[RegisterController::class,'completeVolunteerProfile'])
                ->middleware('role:volunteer');

            Route::put('/volunteers/update', [ProfileController::class, 'updateVolunteerProfile'])
                ->middleware('role:volunteer');
        });
         Route::prefix('lost-found')->group(function () {
            Route::post('/', [LostFoundController::class, 'store']);
            Route::put('/{lostFound}', [LostFoundController::class, 'update']);
            Route::delete('/{lostFound}', [LostFoundController::class, 'destroy']);
       });

         // Matching
        Route::prefix('matching')->group(function () {
            Route::get('/quiz/questions', [MatchingController::class, 'getQuestions']);
            Route::post('/preferences', [MatchingController::class, 'storePreferences']);
            Route::get('/last', [MatchingController::class, 'getLastMatching']);
            Route::get('/history', [MatchingController::class, 'getAllMatchings']);
        });

        // FCM
        Route::post('/fcm-token', [FcmTokenController::class, 'store']);

        //  ======== sponsorships Management ========
        Route::prefix('sponsorships')->group(function () {
            Route::post('/request', [SponsorshipController::class, 'requestSponsorship']);
            // تجديد دفعة الرعاية
            Route::post('/{id}/renew', [SponsorshipController::class, 'renewPayment']);
            Route::get('/my', [SponsorshipController::class, 'mySponsorships']);
        });

       // ======== Donation Management ========
        Route::prefix('Donation')->group(function () {
            Route::post('/donations/store', [DonationController::class, 'store']);
        });

        Route::middleware(['role:SuperAdmin'])->prefix('admin')->group(function () {

            // ======== Verification Counts ========
            Route::get('/approved-counts', [AdminVerificationController::class, 'getApprovedCounts']);

            // ======== Veterinarian Verification ========
            Route::patch('/veterinarians/{id}/approve', [AdminVerificationController::class, 'approveVeterinarian']);
            Route::patch('/veterinarians/{id}/block',   [AdminVerificationController::class, 'blockVeterinarian']);

            // ======== Volunteer Verification ========
            Route::patch('/volunteers/{id}/approve', [AdminVerificationController::class, 'approveVolunteer']);
            Route::patch('/volunteers/{id}/block',   [AdminVerificationController::class, 'blockVolunteer']);

            // ======== Donation Management ========
            Route::get('/donations/pending', [DonationController::class, 'getPendingDonations']);
            Route::patch('/donations/{id}/approve', [DonationController::class, 'approveDonation']);
            Route::patch('/donations/{id}/reject',  [DonationController::class, 'rejectDonation']);

            //  ======== sponsorships Management ========
                Route::get('/sponsorships', [SponsorshipController::class, 'index']);
                Route::get('/sponsorships/{id}', [SponsorshipController::class, 'show']);
                // التحقق من دفعة رعاية
                Route::patch('/sponsorships/payments/{paymentId}/verify', [SponsorshipController::class, 'verifyPayment']);
        });

       // ============ Adoption ==========
        Route::prefix('adoption')->group(function () {
            Route::post('/apply', [AdoptionApplicationController::class, 'store']);
            Route::put('/{id}', [AdoptionApplicationController::class, 'update']);
            Route::get('/my-applications', [AdoptionApplicationController::class, 'myApplications']);
            Route::delete('/{id}', [AdoptionApplicationController::class, 'destroy']);
        });
       Route::middleware('role:SuperAdmin')->prefix('adoption')->group(function (){

            Route::get('/applications', [AdoptionApplicationController::class, 'index']);
            Route::put('/{id}/approve', [AdoptionApplicationController::class, 'approve']);
            Route::put('/{id}/reject', [AdoptionApplicationController::class, 'reject']);
            Route::put('/applications/{id}/change-status',[AdoptionApplicationController::class, 'changeStatus']);
            Route::get('/applications/{id}',[AdoptionApplicationController::class, 'show']);
        });


            //  ======== Rescue Management ========
        Route::prefix('rescue/reports')->group(function () {
            Route::get('/my', [RescueReportController::class, 'myRescueReports']);
            // تحديث حالة البلاغ (المتطوع المكلّف فقط)
            Route::put('/{id}/status', [RescueReportController::class, 'updateStatus'])
                ->middleware('role:volunteer');

            // قبول البلاغ (المتطوع فقط)
            Route::post('/{id}/accept', [RescueReportController::class, 'acceptReport'])
                ->middleware('role:volunteer');

            // عرض البلاغات القريبة من المتطوع
            Route::get('/available', [RescueReportController::class, 'availableReports'])
                ->middleware('role:volunteer');

        });
        // تحديث موقع المتطوع أثناء المهمة
            Route::post('/volunteer/update-location', [RescueReportController::class, 'updateVolunteerLocation'])
                ->middleware('role:volunteer');
        // عرض الاستشارات المعلّقة للطبيب
            Route::get('/rescue/consultations/pending', [RescueConsultationController::class, 'getPendingConsultations'])
                ->middleware('role:veterinarian');
        // إنشاء استشارة طبية (المتطوع المكلّف فقط)
            Route::post('/rescue/consultations', [RescueConsultationController::class, 'store'])
                ->middleware('role:volunteer');

            // إجابة الطبيب على الاستشارة
            Route::put('/rescue/consultations/{id}/answer', [RescueConsultationController::class, 'answer'])
                ->middleware('role:veterinarian');
            // عرض كل الاستشارات الخاصة ببلاغ معيّن
            Route::get('/rescue/reports/{reportId}/consultations', [RescueConsultationController::class, 'getReportConsultations']);

        //  ======== Animals  ========
        Route::prefix('animals')->group(function(){
            // Create Animal (Vet only)
                Route::post('/', [AnimalController::class, 'store'])
                    ->middleware('role:veterinarian|SuperAdmin');

                // Update Animal (Vet only)
                Route::put('/{animal}', [AnimalController::class, 'update'])
                    ->middleware('role:veterinarian|SuperAdmin');

                // Delete Animal (SuperAdmin only)
                Route::delete('/{animal_id}', [AnimalController::class, 'destroy'])
                    ->middleware('role:SuperAdmin');

            // ======== Protected Medical Conditions ========
            Route::middleware(['role:veterinarian|SuperAdmin'])->group(function () {

                Route::post('/{animal_id}/medical-conditions', [AnimalMedicalConditionController::class, 'store']);

                Route::put('/{animal_id}/medical-conditions/{id}', [AnimalMedicalConditionController::class, 'update']);

                Route::delete('/{animal_id}/medical-conditions/{id}', [AnimalMedicalConditionController::class, 'destroy']);
                Route::post('/{animal_id}/vaccinations', [VaccinationController::class, 'store']);
            Route::post('/{animal_id}/behavioral-attributes', [BehavioralAttributeController::class, 'store']);
                });
        });

        // ================Backup Requests ==========
        Route::prefix('backup-requests')->middleware(['role:volunteer'])->group(function () {
            // إنشاء طلب دعم طارئ
            Route::post('/', [BackupRequestController::class, 'store']);
            // قبول طلب دعم طارئ
            Route::put('/{id}/accept', [BackupRequestController::class, 'acceptBackup']);
            // عرض طلبات الدعم القريبة
            Route::get('/available', [BackupRequestController::class, 'getAvailableBackupRequests']);
        });

    //  ======== General Consultation Management ========
        Route::prefix('consultations/general')->middleware('auth:sanctum')->group(function () {
            // ======== User  ========
                Route::post('/', [GeneralConsultationController::class, 'store']);
            Route::put('/{id}/update-question', [GeneralConsultationController::class, 'updateQuestion']);
            Route::delete('/{id}/delete-question', [GeneralConsultationController::class, 'destroyQuestion']);

            // ======== Veterinarian  ========

            Route::get('/pending', [GeneralConsultationController::class, 'getDoctorConsultations'])
                ->middleware('role:veterinarian');

            Route::put('/{id}/answer', [GeneralConsultationController::class, 'answer'])
                ->middleware('role:veterinarian');

            Route::put('/{id}/update-answer', [GeneralConsultationController::class, 'updateAnswer'])
                ->middleware('role:veterinarian');

            Route::delete('/{id}/delete-answer', [GeneralConsultationController::class, 'destroyAnswer'])
                ->middleware('role:veterinarian');
        });

        //  ======== Community posts Management ========
        Route::prefix('community/posts')->group(function () {
                // like / unlike
                Route::post('/{id}/toggle-like', [CommunityPostController::class, 'toggleLike']);
                // ======== Admin ========
                Route::middleware('role:SuperAdmin')->group(function () {
                    // new post
                    Route::post('/', [CommunityPostController::class, 'store']);
                    // update post
                    Route::put('/{id}', [CommunityPostController::class, 'update']);
                    // delete post
                    Route::delete('/{id}', [CommunityPostController::class, 'destroy']);
                });
            });

        // ================Awareness Posts==========
        Route::prefix('awareness-posts')->group(function () {

                Route::post('/{id}/toggle-like', [AwarenessPostController::class, 'toggleLike']);
                // ======== Veterinarian Routes ========
                Route::middleware('role:veterinarian')->group(function () {
                    Route::post('/', [AwarenessPostController::class, 'store']);
                    Route::put('/{id}', [AwarenessPostController::class, 'update']);
                    Route::delete('/{id}', [AwarenessPostController::class, 'destroy']);
                });
            });
        });


});
