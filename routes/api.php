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
use App\Http\Controllers\LostFoundController;

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
   Route::get('/animals', [AnimalController::class, 'index']);
   Route::get('/animals/{animal}', [AnimalController::class, 'show']);

    // Public Rescue Reports
    Route::post('/rescue/reports', [RescueReportController::class, 'store']);

    Route::prefix('financial')->group(function () {
        Route::get('/summary', [TransparencyDashboardController::class, 'getFinancialSummary']);
        Route::get('/expenses', [TransparencyDashboardController::class, 'getPublicExpenses']);
    });

    // ====================== Protected Routes (Sanctum) ======================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [LoginController::class, 'logout']);

        // OTP Routes
        Route::post('/verify-otp', [OTPController::class, 'verify']);
        Route::post('/resend-otp', [OTPController::class, 'resend']);

        // Forgot Password Routes
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
        Route::post('/reset-password',  [ForgotPasswordController::class, 'reset']);

        // Profile Routes
        Route::prefix('profile')->group(function () {
            Route::get('/show', [ProfileController::class, 'show']);
            Route::put('/update', [ProfileController::class, 'update']);
            Route::put('/change-password', [ProfileController::class, 'changePassword']);
        });

        Route::get('/my-donations', [TransparencyDashboardController::class, 'getMyDonations']);

        // ====================== ميزات تتبع بلاغات الطوارئ المحدثة ======================
        Route::prefix('rescue/reports')->group(function () {
            Route::get('/{id}/track', [RescueReportController::class, 'track']);
            Route::patch('/{id}/status', [RescueReportController::class, 'updateStatus'])
                ->middleware('role:Volunteer');
            Route::patch('/{id}/accept', [RescueReportController::class, 'acceptReport'])
                ->middleware('role:Volunteer');
            Route::get('/{id}/consultations', [RescueConsultationController::class, 'getReportConsultations'])
                ->middleware('role:Volunteer');
        });

        // ====================== مسارات المتطوع الميداني اللحظية ======================
        Route::middleware(['auth:sanctum', 'role:Volunteer'])->prefix('volunteer')->group(function () {
            Route::post('/update-location', [RescueReportController::class, 'updateVolunteerLocation']);
            Route::get('/available-reports', [RescueReportController::class, 'availableReports']);

            Route::post('/backup-requests', [BackupRequestController::class, 'store']);
            Route::put('/backup-requests/{id}/accept', [BackupRequestController::class, 'acceptBackup']);

            Route::get('/backup-requests/available', [BackupRequestController::class, 'getAvailableBackupRequests']);

            Route::post('/rescue-consultations', [RescueConsultationController::class, 'store']);
        });

        Route::group(['middleware' => ['auth:sanctum', 'role:Veterinarian']], function () {
            // الرد على استشارة ميدانية
            Route::put('/rescue-consultations/{id}/answer', [RescueConsultationController::class, 'answer']);
            Route::get('/rescue-consultations/pending', [RescueConsultationController::class, 'getPendingConsultations']);
        });

        // ====================== موديول التبرعات والشفافية المالية ======================
        Route::prefix('donations')->group(function () {
            // مسار إرسال التبرع المالي ورفع وصل الحوالة للمستخدم المسجل
            Route::post('/store', [DonationController::class, 'store']);
        });

        // ====================== موديول نظام الكفالة الكاملة المستقل (المستحدث) ======================
        Route::prefix('sponsorships')->group(function () {
            Route::post('/request', [SponsorshipController::class, 'requestSponsorship']);
            Route::post('/{id}/renew', [SponsorshipController::class, 'renewPayment']);
            Route::get('/my-sponsorships', [SponsorshipController::class, 'mySponsorships']);

            Route::get('/available-animals', [SponsorshipController::class, 'availableAnimalsForSponsorship']);
        });

        // ====================== لوحة تحكم الإدارة المالية (SuperAdmin) ======================
        Route::middleware('role:SuperAdmin')->prefix('admin')->group(function () {
            // إدارة التبرعات العامة
            Route::get('/donations/pending', [DonationController::class, 'getPendingDonations']);
            Route::patch('/donations/{id}/approve', [DonationController::class, 'approveDonation']);
            Route::patch('/donations/{id}/reject', [DonationController::class, 'rejectDonation']);

            // إدارة ودراسة دفعات الكفالة الكاملة
            Route::post('/sponsorship-payments/{id}/verify', [SponsorshipController::class, 'verifyPayment']);
            Route::get('/sponsorships', [SponsorshipController::class, 'index']);
            Route::get('/sponsorships/{id}', [SponsorshipController::class, 'show']);

            // مسار لرفع تحديث جديد لحيوان معين بناءً على معرف الحيوان (ID)
            Route::post('/animals/{animal}/updates', [AnimalUpdateController::class, 'store']);

            // التقارير المالية والمصاريف
            Route::get('/donations/financial-report/data', [TransparencyDashboardController::class, 'getFinancialReportData']);
            Route::post('/expenses/store', [ExpenseController::class, 'store']);

            // عرض كافة طلبات التبني في النظام مع الفلاتر والصفحات
            Route::get('/applications', [AdoptionApplicationController::class, 'index']);

            // عرض تفاصيل طلب تبني محدد بشكل تفصيلي
            Route::get('/applications/{application}', [AdoptionApplicationController::class, 'show']);

            // قبول طلب التبني مباشرة وتحرير الحيوان من الكفالة النشطة تلقائياً
            Route::post('/applications/{application}/approve', [AdoptionApplicationController::class, 'approve']);

            // رفض طلب التبني
            Route::post('/applications/{application}/reject', [AdoptionApplicationController::class, 'reject']);

            // تغيير حالة الطلب بشكل عام (approved, rejected, in_trial)
            Route::patch('/applications/{application}/status', [AdoptionApplicationController::class, 'changeStatus']);
        });

        // ====================== موديول طلبات التبني (Adoption Applications) ======================

        Route::prefix('adoption')->group(function () {
            Route::post('/store', [AdoptionApplicationController::class, 'store']);

            Route::get('/my-applications', [AdoptionApplicationController::class, 'myApplications']);

            Route::put('/applications/{application}', [AdoptionApplicationController::class, 'update']);

            Route::delete('/applications/{application}', [AdoptionApplicationController::class, 'destroy']);
        });

        // ====================== Animal Management Routes ======================
        Route::prefix('animals')->group(function () {

            // only SuperAdmin can add animals
            Route::post('/add', [AnimalController::class, 'store'])
                ->middleware('role:SuperAdmin');

            //  SuperAdmin + Veterinarian can edit animals
            Route::put('/edit/{animal}', [AnimalController::class, 'update'])
                ->middleware('role:SuperAdmin,Veterinarian');

            // only SuperAdmin can delete animals
            Route::delete('/{animal}', [AnimalController::class, 'destroy'])
                ->middleware('role:SuperAdmin');

            // only SuperAdmin + Veterinarian can delete photos
            Route::delete('/photos/{photo}', [AnimalController::class, 'deletePhoto'])
                ->middleware('role:SuperAdmin,Veterinarian');
        });
         // ====================== Lost & Found Routes ======================
    Route::prefix('lost-found')->group(function () {

    // Public Routes (يمكن للجميع الوصول)
       Route::get('/', [LostFoundController::class, 'index']);                    // قائمة المنشورات + فلاتر
       Route::get('/nearby', [LostFoundController::class, 'searchNearby']);       // بحث حسب الموقع
       Route::get('/{lostFound}', [LostFoundController::class, 'show']);          // تفاصيل منشور
       Route::get('/{lostFound}/similar', [LostFoundController::class, 'similarPosts']); // منشورات مشابهة

    // Protected Routes (تحتاج تسجيل دخول)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [LostFoundController::class, 'store']);                    // إنشاء منشور جديد
        Route::put('/{lostFound}/status', [LostFoundController::class, 'updateStatus']); // تغيير الحالة
        Route::delete('/{lostFound}', [LostFoundController::class, 'destroy']);     // حذف المنشور
       });
    });
  });
});
