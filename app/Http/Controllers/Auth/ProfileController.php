<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * عرض بيانات المستخدم الحالي
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'success' => true,
            'user' => $user->only([
                'id',
                'full_name',
                'email',
                'country_code',
                'phone_number',
                'governorate',
                'account_status',
                'email_verified_at',
            ]),
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    /**
     * تحديث بيانات الملف الشخصي
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'    => 'string|max:255',
            'country_code' => 'string|max:5',
            'phone_number' => 'string|max:15',
            'governorate'  => 'string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only(['full_name', 'country_code', 'phone_number', 'governorate']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => $user->fresh()->only([
                'id', 'full_name', 'email', 'country_code',
                'phone_number', 'governorate'
            ])
        ]);
    }

    /**
     * تغيير كلمة المرور
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // حذف كل التوكنز عدا التوكن الحالي
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
