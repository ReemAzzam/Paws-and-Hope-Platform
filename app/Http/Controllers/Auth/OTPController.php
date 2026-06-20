<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Notifications\SendOTPNotification;

class OTPController extends Controller
{
    // VERIFY OTP
  public function verify(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp'   => 'required|string|size:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    $cachedOtp = Cache::get('otp_' . $request->email);

    if (!$cachedOtp || $cachedOtp != $request->otp) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP code'
        ], 400);
    }

    // تفعيل الحساب
    $user->update([
        'email_verified_at' => now(),
        'account_status'    => 'active',
    ]);

    Cache::forget('otp_' . $request->email);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Email verified successfully. Your account is now active.',
        'user'    => $user->only(['id', 'full_name', 'email']),
        'token'   => $token,
    ]);
}

   //RESEND OTP
    public function resend(Request $request)
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

        $user = User::where('email', $request->email)->first();

        if ($user->account_status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is already verified.'
            ], 400);
        }

        // إرسال OTP جديد
        $user->notify(new SendOTPNotification());

        return response()->json([
            'success' => true,
            'message' => 'A new OTP has been sent to your email.'
        ]);
    }
}
