<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendPasswordResetOTPNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /**
     * طلب إعادة تعيين كلمة المرور
     * OTP يُرسل فقط إلى نفس إيميل الحساب المسجل
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // البحث عن الحساب بنفس الإيميل المسجل فقط
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account is registered with this email address.'
            ], 404);
        }

        // حماية إضافية: الحساب لازم يكون موجود وفعال (اختياري)
        if (in_array($user->account_status, ['rejected', 'suspended'])) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset is not allowed for this account.'
            ], 403);
        }

        // إرسال OTP إلى نفس إيميل الحساب فقط
        $user->notify(new SendPasswordResetOTPNotification());

        return response()->json([
            'success' => true,
            'message' => 'Password reset OTP has been sent to your registered email.'
        ]);
    }

    /**
     * التحقق من OTP وتغيير كلمة المرور
     * الإيميل هنا يجب أن يكون نفس إيميل الحساب + نفس OTP المرسل له
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ], [
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // المستخدم صاحب هذا الإيميل فقط
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account is registered with this email address.'
            ], 404);
        }

        // OTP مربوط حصريًا بهذا الإيميل
        $cacheKey = 'reset_otp_' . $user->email;
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please request a new one.'
            ], 400);
        }

        if ((string) $cachedOtp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code.'
            ], 400);
        }

        // حذف OTP بعد التحقق
        Cache::forget($cacheKey);

        // تحديث باسورد نفس المستخدم فقط
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // تسجيل خروج من كل الجلسات
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. Please login again.',
        ]);
    }
}