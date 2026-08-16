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
                // مستخدم جديد → بدون رول + إيميل موثّق بدون OTP
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

                // تأكيد أن الإيميل موثّق لمستخدمي Google
                if (is_null($user->email_verified_at)) {
                    $user->update(['email_verified_at' => now()]);
                }

                // إذا ما عنده أي رول → لازم يختار
                $needsRoleSelection = $user->roles()->count() === 0;
            }

            $token = $user->createToken('google_token')->plainTextToken;
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');

            if ($needsRoleSelection) {
                return redirect()->away(
                    $frontendUrl . '/auth/select-role?token=' . urlencode($token) . '&requires_otp=0&email_verified=1'
                );
            }

            return redirect()->away(
                $frontendUrl . '/auth/success?token=' . urlencode($token) . '&requires_otp=0&email_verified=1'
            );

        } catch (\Exception $e) {
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
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

        // Google Login already verifies the email,
        // so the account becomes active for all roles.
        $user->update([
            'account_status'    => 'active',
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if ($role === 'regular_user') {

            DB::table('regular_users')->insert([
                'user_id'    => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($role === 'volunteer') {

            DB::table('volunteers')->insert([
                'user_id'    => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // account_status = active
            // is_approved = false
            // Admin approval is still required
        }

        if ($role === 'veterinarian') {

            DB::table('veterinarians')->insert([
                'user_id'    => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // account_status = active
            // is_approved = false
            // Admin approval is still required
        }
    });

        $user->refresh();

    return response()->json([
        'success' => true,
        'message' => 'Role selected successfully',
        'data' => [
            'role' => $role,
            'account_status' => $user->account_status,
            'email_verified' => !is_null($user->email_verified_at),
            'requires_otp' => false,
            'next' => match ($role) {
                'regular_user' => 'home',
                'veterinarian' => 'complete_vet_profile',
                'volunteer'    => 'complete_volunteer_profile',
            },
            'message_for_user' => match ($role) {
                'regular_user' => 'Account activated successfully',
                'veterinarian' => 'Please complete your veterinarian profile. Account stays pending until admin approval',
                'volunteer'    => 'Please complete your volunteer profile. Account stays pending until admin approval',
            },
        ]
    ]);
    }

    /**
     * حالة المستخدم الحالية - مفيدة للفرونت لتقرير الخطوة التالية
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['roles', 'veterinarian', 'volunteer']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $user->id,
                'full_name'      => $user->full_name,
                'email'          => $user->email,
                'roles'          => $user->getRoleNames(),
                'account_status' => $user->account_status,
                'email_verified' => !is_null($user->email_verified_at),
                'requires_otp'   => is_null($user->email_verified_at),
                'next'           => $this->resolveNextStep($user),
            ]
        ]);
    }

    private function resolveNextStep(User $user): string
    {
        // 1) إذا الإيميل غير موثّق → OTP (مسار التسجيل العادي فقط)
        if (is_null($user->email_verified_at)) {
            return 'verify_otp';
        }

        // 2) إذا ما في رول → اختيار رول
        if ($user->roles()->count() === 0) {
            return 'select_role';
        }

        // 3) بيطري بدون ملف مكتمل
        if ($user->hasRole('veterinarian') && !$user->veterinarian) {
            return 'complete_vet_profile';
        }

        // 4) متطوع بدون بيانات أساسية
        if ($user->hasRole('volunteer')) {
            $volunteer = $user->volunteer;
            if (!$volunteer || is_null($volunteer->vol_type)) {
                return 'complete_volunteer_profile';
            }
        }

        // 5) جاهز
        return 'home';
    }
}
