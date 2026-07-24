<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Veterinarian;
use App\Notifications\SendOTPNotification;
use FontLib\Table\Type\name;
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
//                 \Log::info($request->role);
// \Log::info($user->roles->pluck('name'));
                $user->notify(new SendOTPNotification());


                if ($request->role === 'volunteer') {
                    DB::table('volunteers')->insert([
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($request->role === 'veterinarian') {
                    // DB::table('veterinarians')->insert([
                    //     'user_id'    => $user->id,
                    //     'created_at' => now(),
                    //     'updated_at' => now(),
                    // ]);
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

    // complete the veterinarian profile
        public function completeVetProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('veterinarian')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. This route is for veterinarians only.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'specialization'    => 'required|string|max:255',
            'clinic_location'   => 'required|string|max:255',
           'license_number' => 'required|string|max:255|unique:veterinarians,license_number',
           'working_hours'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

            $vet = Veterinarian::updateOrCreate(
        ['user_id' => $user->id],
        [
            'specialization'    => $request->specialization,
            'clinic_location'   => $request->clinic_location,
            'license_number'    => $request->license_number,
            'working_hours'     => $request->working_hours,
        ]
    );

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian profile completed successfully.',
            'data'    => [
                'professional_name' => $user->full_name,
                'specialization'    => $vet->specialization,
                'clinic_location'   => $vet->clinic_location,
                'license_number'    => $vet->license_number,
                'working_hours'     => $vet->working_hours,
                'is_approved'       => $vet->is_approved,
            ]
        ], 200);
    }

    // complete the volunteer profile
        public function completeVolunteerProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('volunteer')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. This route is for volunteers only.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'detailed_address'   => 'required|string|max:255',
            'age'                => 'required|integer|min:18|max:70',

            'vol_type' => 'required|in:field,photography,transportation,other',

            'experience_level' => 'required|in:beginner,intermediate,advanced',

            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:100',

            'current_latitude' => 'required|numeric|between:-90,90',
            'current_longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $volunteer = $user->volunteer;

        $volunteer->update([
            'detailed_address'  => $request->detailed_address,
            'age'               => $request->age,
            'vol_type'          => $request->vol_type,
            'experience_level'  => $request->experience_level,
            'equipment'         => $request->equipment,
            'current_latitude'  => $request->current_latitude,
            'current_longitude' => $request->current_longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Volunteer profile completed successfully.',
            'data' => [
                'detailed_address'  => $volunteer->detailed_address,
                'age'               => $volunteer->age,
                'vol_type'          => $volunteer->vol_type,
                'experience_level'  => $volunteer->experience_level,
                'equipment'         => $volunteer->equipment,
                'current_latitude'  => $volunteer->current_latitude,
                'current_longitude' => $volunteer->current_longitude,
                'is_approved'       => $volunteer->is_approved,
            ]
        ], 200);
    }
}
