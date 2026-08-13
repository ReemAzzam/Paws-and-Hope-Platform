<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

     public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // جلب المستخدم
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // التحقق من كلمة المرور
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // التحقق من حالة الحساب
        if ($user->account_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not activated.',
            ], 403);
        }

        // إنشاء توكن
        $token = $user->createToken('auth_token')->plainTextToken;

       return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'user'    => [
                'id'           => $user->id,
                'full_name'    => $user->full_name,
                'email'        => $user->email,
                'country_code' => $user->country_code,
                'phone_number' => $user->phone_number,
                'governorate'  => $user->governorate,
                'photo_url'    => $user->photo_url,
            ],
            'roles' => $user->getRoleNames(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
