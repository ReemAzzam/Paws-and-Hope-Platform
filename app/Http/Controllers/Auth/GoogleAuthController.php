<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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

            $needsRoleSelection = false;

            if (!$user) {
                // مستخدم جديد → بدون رول
                $user = DB::transaction(function () use ($googleUser) {
                    return User::create([
                        'full_name'          => $googleUser->getName(),
                        'email'              => $googleUser->getEmail(),
                        'google_id'          => $googleUser->getId(),
                        'password'           => Hash::make(Str::random(32)),
                        'account_status'     => 'pending',
                        'email_verified_at'  => now(),
                        'two_factor_enabled' => false,
                    ]);
                });

                $needsRoleSelection = true;
            } else {
                // مستخدم موجود
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }

                // إذا ما عنده أي رول → لازم يختار
                $needsRoleSelection = $user->roles()->count() === 0;
            }

            $token = $user->createToken('google_token')->plainTextToken;
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            if ($needsRoleSelection) {
                return redirect()->away($frontendUrl . '/auth/select-role?token=' . $token);
            }

            return redirect()->away($frontendUrl . '/auth/success?token=' . $token);

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away(
                $frontendUrl . '/auth/error?message=' . urlencode('فشل تسجيل الدخول عبر Google')
            );
        }
    }

    /**
     * اختيار الرول بعد Google Login
     * POST /api/v1/auth/select-role
     * Header: Authorization: Bearer {token}
     * Body: { "role": "regular_user" | "veterinarian" | "volunteer" }
     */
    public function selectRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:regular_user,veterinarian,volunteer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if ($user->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Role already selected'
            ], 400);
        }

        $role = $request->role;

        DB::transaction(function () use ($user, $role) {
            $user->assignRole($role);

            if ($role === 'regular_user') {
                DB::table('regular_users')->insert([
                    'user_id'    => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user->update(['account_status' => 'active']);
            }

            if ($role === 'volunteer') {
                DB::table('volunteers')->insert([
                    'user_id'    => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // يبقى pending إلى أن يكمل الملف + موافقة الأدمن
            }

            if ($role === 'veterinarian') {
                // ما مننشئ سجل veterinarians هون
                // بيتكمّل عبر completeVetProfile
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Role selected successfully',
            'data'    => [
                'role' => $role,
                'next' => match ($role) {
                    'regular_user' => 'home',
                    'veterinarian' => 'complete_vet_profile',
                    'volunteer'    => 'complete_volunteer_profile',
                },
            ]
        ]);
    }
}
