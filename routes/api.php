<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\RescueReportController;
use App\Http\Controllers\BackupRequestController;
use App\Http\Controllers\RescueConsultationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TransparencyDashboardController;
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

         // ====================== موديول التبرعات والشفافية المالية (المستحدث) ======================
        Route::prefix('donations')->group(function () {
            // مسار إرسال التبرع المالي ورفع وصل الحوالة للمستخدم المسجل
            Route::post('/store', [DonationController::class, 'store']);
        });

        // ====================== لوحة تحكم الإدارة المالية (SuperAdmin) ======================
        Route::middleware('role:SuperAdmin')->prefix('admin/donations')->group(function () {
            Route::get('/pending', [DonationController::class, 'getPendingDonations']);
            Route::patch('/{id}/approve', [DonationController::class, 'approveDonation']);
            Route::patch('/{id}/reject', [DonationController::class, 'rejectDonation']);
            // تحميل التقارير المالية 
            Route::get('/financial-report/data', [TransparencyDashboardController::class, 'getFinancialReportData']);
            
        });

        Route::middleware('role:SuperAdmin')->prefix('admin/expenses')->group(function () {
            Route::post('/store', [ExpenseController::class, 'store']);
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
            Route::delete('/delete/{animal}', [AnimalController::class, 'destroy'])
                ->middleware('role:SuperAdmin');

            // only SuperAdmin + Veterinarian can delete photos
            Route::delete('/photos/{photo}', [AnimalController::class, 'deletePhoto'])
                ->middleware('role:SuperAdmin,Veterinarian');
        });

    });
});