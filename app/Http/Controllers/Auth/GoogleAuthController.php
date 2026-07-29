<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();

            if (!$user) {
                // مستخدم جديد
                $user = DB::transaction(function () use ($googleUser) {
                    $user = User::create([
                        'full_name'         => $googleUser->getName(),
                        'email'             => $googleUser->getEmail(),
                        'google_id'         => $googleUser->getId(),
                        'password'          => Hash::make(Str::random(32)),
                        'account_status'    => 'active',
                        'email_verified_at' => now(),
                        'two_factor_enabled'=> false,
                    ]);

                    $user->assignRole('regular_user');

                    DB::table('regular_users')->insert([
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return $user;
                });
            } else {
                // مستخدم موجود → تحديث google_id لو مش موجود
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            // إنشاء التوكن
            $token = $user->createToken('google_token')->plainTextToken;

            // إعادة التوجيه للفرونت مع التوكن
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            return redirect()->away($frontendUrl . '/auth/success?token=' . $token);

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away($frontendUrl . '/auth/error?message=' . urlencode('فشل تسجيل الدخول عبر Google'));
        }
    }
}
