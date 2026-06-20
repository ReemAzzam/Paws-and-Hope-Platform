<?php

use Illuminate\Support\Facades\Route;

// Auth Controllers
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProfileController;

// Other Controllers
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\BehavioralAttributeController;
use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\MatchingController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ====================== Public Routes ======================
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login',    [LoginController::class, 'login']);

      // OTP
    Route::post('/verify-otp', [OTPController::class, 'verify']);
    Route::post('/resend-otp', [OTPController::class, 'resend']);

    // Public Animal Routes
    Route::get('/animals', [AnimalController::class, 'index']);
    Route::get('/animals/{animal}', [AnimalController::class, 'show']);

    Route::get('/quiz/questions', [MatchingController::class, 'getQuestions']);

    // ====================== Protected Routes ======================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [LoginController::class, 'logout']);



        // Forgot Password
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
        Route::post('/reset-password',  [ForgotPasswordController::class, 'reset']);

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::put('/change-password', [ProfileController::class, 'changePassword']);
        });

        // ====================== Animals Management ======================
        Route::prefix('animals')->group(function () {

            Route::post('/add', [AnimalController::class, 'store'])
                ->middleware('role:super_admin');

            Route::put('/{animal}', [AnimalController::class, 'update'])
                ->middleware('role:super_admin,veterinarian');

            Route::delete('/{animal}', [AnimalController::class, 'destroy'])
                ->middleware('role:super_admin');

            Route::delete('/photos/{photo}', [AnimalController::class, 'deletePhoto'])
                ->middleware('role:super_admin,veterinarian');

            // Vaccinations & Behavioral Attributes
            Route::post('/{animal_id}/vaccinations', [VaccinationController::class, 'store'])
                ->middleware('role:super_admin');

            Route::get('/{animal_id}/vaccinations', [VaccinationController::class, 'showByAnimal']);

            Route::post('/{animal_id}/behavioral-attributes', [BehavioralAttributeController::class, 'store'])
                ->middleware('role:super_admin');

            Route::get('/{animal_id}/behavioral-attributes', [BehavioralAttributeController::class, 'showByAnimal']);
        });

        // ====================== Adoption Routes ======================
        Route::prefix('adoption')->group(function () {

            Route::post('/apply', [AdoptionApplicationController::class, 'store']);

            Route::get('/my-applications', [AdoptionApplicationController::class, 'myApplications']);

            // User can update or delete his own application
            Route::put('/applications/{application}', [AdoptionApplicationController::class, 'update']);
            Route::delete('/applications/{application}', [AdoptionApplicationController::class, 'destroy']);

            // Admin Routes
            Route::middleware('role:super_admin')->group(function () {
                Route::get('/applications', [AdoptionApplicationController::class, 'index']);
                Route::get('/applications/{application}', [AdoptionApplicationController::class, 'show']);

                Route::put('/applications/{application}/approve', [AdoptionApplicationController::class, 'approve']);
                Route::put('/applications/{application}/reject', [AdoptionApplicationController::class, 'reject']);
                Route::put('/applications/{application}/change-status', [AdoptionApplicationController::class, 'changeStatus']);
            });
        });
        // ====================== Smart Matching Routes ======================
            Route::prefix('matching')->group(function () {
              Route::post('/preferences', [MatchingController::class, 'storePreferences']);
              Route::get('/last', [MatchingController::class, 'getLastMatching']);
              Route::get('/quiz/questions', [MatchingController::class, 'getQuestions']);
              Route::get('/history', [MatchingController::class, 'getAllMatchings']);
        });

    });
});
