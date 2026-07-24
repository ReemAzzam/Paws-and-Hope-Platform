<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * عرض بيانات المستخدم الحالي
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $adoptionsCount = \App\Models\AdoptionApplication::where('user_id', $user->id)
            ->count();

        $verifiedDonations = \App\Models\Donation::where('user_id', $user->id)
            ->where('status', 'verified');

        $donationsCount = (clone $verifiedDonations)->count();
        $donationsTotal = (clone $verifiedDonations)->sum('amount');

        $sponsorshipsCount = \App\Models\Sponsorship::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $rescueReportsCount = \App\Models\RescueReport::where('reporter_id', $user->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [

                'userInfo' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'country_code' => $user->country_code,
                    'address' => $user->governorate,
                    'account_status' => $user->account_status,
                    'email_verified_at' => $user->email_verified_at,
                    'profile_created' => $user->created_at,
                ],

                'impactDashboard' => [

                    'adoptions' => $adoptionsCount,

                    'donations' => [
                        'total_amount' => $donationsTotal,
                    ],

                    'sponsorships' => $sponsorshipsCount,

                    'rescue_reports' => $rescueReportsCount,
                ]

            ]
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


    public function getVetProfile($id)
    {
        $user = User::findOrFail($id);

        if (!$user->hasRole('veterinarian')) {
            return response()->json([
                'success' => false,
                'message' => 'The requested profile is not a veterinarian.'
            ], 404);
        }

        $user->load([
            'veterinarian',
            'veterinarian.awarenessPosts' => function($query) {
                $query->latest();
            },
            'veterinarian.animals' => function($query) {
                $query->select('animals.id', 'animals.name', 'animals.type', 'animals.health_status', 'animals.vet_id');
            }
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'profile' => [
                    'id'           => $user->id,
                    'full_name'    => $user->full_name,
                    'email'        => $user->email,
                    'phone_number' => $user->phone_number,
                    'governorate'  => $user->governorate,
                    'specialization'   => $user->veterinarian->specialization,
                    'clinic_location'  => $user->veterinarian->clinic_location,
                    'working_hours'    => $user->veterinarian->working_hours,
                    'license_number'   => $user->veterinarian->license_number,
                    'is_approved'      => $user->veterinarian->is_approved,
                ],
                'my_posts'  => $user->veterinarian->awarenessPosts ?? [],

                'my_patients' => $user->veterinarian->animals ?? []
            ]
        ], 200);
    }

    public function getVolunteerProfile(Request $request, $id)
    {
        $currentUser = $request->user();

        if (!$currentUser->hasRole('super_admin') && !$currentUser->hasRole('veterinarian') && !$currentUser->hasRole('volunteer')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only administrators and verified veterinarians can view volunteer profiles.'
            ], 403);
        }

        $user = User::findOrFail($id);

        if (!$user->hasRole('volunteer')) {
            return response()->json([
                'success' => false,
                'message' => 'The requested profile is not a volunteer.'
            ], 404);
        }

        $user->load('volunteer');

        return response()->json([
            'success' => true,
            'data'    => $user
        ], 200);
    }

    public function updateVetProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('veterinarian')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. This route is for veterinarians only.'], 403);
        }

        $vet = $user->veterinarian;

        $validator = Validator::make($request->all(), [
            'full_name'    => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20',
            'governorate'  => 'sometimes|required|string|max:100',
            'clinic_address' => 'sometimes|string|max:255',
            'specialization' => 'sometimes|string|max:255',
            'bio'            => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $user, $vet) {
            $user->update($request->only(['full_name', 'phone_number', 'governorate']));
            if ($vet) {
                $vet->update($request->only(['clinic_address', 'specialization', 'bio']));
            }
        });

        $user->load('veterinarian');

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian profile updated successfully.',
            'data'    => $user
        ], 200);
    }

    public function updateVolunteerProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('volunteer')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. This route is for volunteers only.'], 403);
        }

        $volunteer = $user->volunteer;

        $validator = Validator::make($request->all(), [
            'full_name'    => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20',
            'governorate'  => 'sometimes|required|string|max:100',
            'skills'            => 'sometimes|string',
            'available_hours'   => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $user, $volunteer) {
            $user->update($request->only(['full_name', 'phone_number', 'governorate']));

            if ($volunteer) {
                $volunteer->update($request->only(['skills', 'available_hours']));
            }
        });

        $user->load('volunteer');

        return response()->json([
            'success' => true,
            'message' => 'Volunteer profile updated successfully.',
            'data'    => $user
        ], 200);
    }
}
