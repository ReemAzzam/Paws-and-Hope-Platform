<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => ['required', 'confirmed', Password::defaults()],
            'country_code' => 'required|string|max:5',
            'phone_number' => 'required|string|max:15',
            'governorate'  => 'required|string|max:100',
            'role'         => 'required|in:regular_user,veterinarian,volunteer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $user = DB::transaction(function () use ($request) {
                
                $user = User::create([
                    'full_name'          => $request->full_name,
                    'email'              => $request->email,
                    'password'           => Hash::make($request->password),
                    'country_code'       => $request->country_code,
                    'phone_number'       => $request->phone_number,
                    'governorate'        => $request->governorate,
                    'account_status'     => 'pending', 
                    'two_factor_enabled' => true,
                ]);

                $user->assignRole($request->role);

                if ($request->role === 'volunteer') {
                    DB::table('volunteers')->insert([
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($request->role === 'veterinarian') {
                    DB::table('veterinarians')->insert([
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($request->role === 'regular_user') {
                    DB::table('regular_users')->insert([
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $user;
            });

            $token = $user->createToken('register_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please verify your email with the OTP sent.',
                'user'    => $user->only([
                    'id', 'full_name', 'email', 'country_code',
                    'phone_number', 'governorate'
                ]),
                'token'   => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ ما أثناء التسجيل، يرجى المحاولة لاحقاً.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
