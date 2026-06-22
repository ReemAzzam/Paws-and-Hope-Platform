<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendPasswordResetOTPNotification;

class ForgotPasswordController extends Controller
{
    /**
     * طلب إعادة تعيين كلمة المرور (إرسال OTP)
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::whereEmail($request->email)->firstOrFail();

        // إرسال OTP لإعادة تعيين كلمة المرور
        $user->notify(new SendPasswordResetOTPNotification());

        return response()->json([
            'success' => true,
            'message' => 'Password reset OTP has been sent to your email.'
        ]);
    }

    /**
     * التحقق من OTP وتغيير كلمة المرور
     */
    public function reset(Request $request)
{
    // 1) Validate Request
    $validator = Validator::make($request->all(), [
        'email'                 => 'required|email|exists:users,email',
        'otp'                   => 'required|string|size:6',
        'password'              => [
            'required',
            'confirmed',
            'min:8',
            'regex:/[A-Z]/',      // حرف كبير
            'regex:/[a-z]/',      // حرف صغير
            'regex:/[0-9]/',      // رقم
            'regex:/[@$!%*#?&]/', // رمز
        ],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    // 2) Check OTP from Cache
    $cachedOtp = Cache::get('reset_otp_' . $request->email);

    if (!$cachedOtp) {
        return response()->json([
            'success' => false,
            'message' => 'OTP expired. Please request a new one.'
        ], 400);
    }

    if ($cachedOtp != $request->otp) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP code.'
        ], 400);
    }

    // 3) OTP is valid → delete it
    Cache::forget('reset_otp_' . $request->email);

    // 4) Update password securely
    $user->update([
        'password' => bcrypt($request->password),
    ]);

    // 5) Delete all tokens (force logout everywhere)
    $user->tokens()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Password has been reset successfully. Please login again.',
    ]);
}

}
