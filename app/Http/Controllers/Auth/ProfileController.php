<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
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

        $rescueReportsCount = \App\Models\RescueReport::where('user_id', $user->id)
            ->count();
        $GeneralConsultationsCount = \App\Models\GeneralConsultation::where('user_id', $user->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [

                'userInfo' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'photo' => $user->photo
                        ? asset('storage/'.$user->photo)
                        : null,
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

                    'general_consultations' => $GeneralConsultationsCount,
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
            'full_name'     => 'sometimes|string|max:255',
            'country_code'  => 'sometimes|string|max:5',
            'phone_number'  => [
                'sometimes',
                'required_with:country_code',
                'phone:' . $request->input('country_code', $request->user()->country_code),
            ],
            'governorate'   => 'sometimes|string|max:100',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // تحديث الصورة
        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('users', 'public');
        }

        // تحديث باقي الحقول
        $user->fill($request->only([
            'full_name',
            'country_code',
            'phone_number',
            'governorate',
        ]));

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'           => $user->id,
                'full_name'    => $user->full_name,
                'email'        => $user->email,
                'country_code' => $user->country_code,
                'phone_number' => $user->phone_number,
                'governorate'  => $user->governorate,
                'photo'        => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
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
            'veterinarian.awarenessPosts' => function ($query) {
                $query->latest();
            },
            'veterinarian.animals' => function ($query) {
                $query->select(
                    'animals.id',
                    'animals.name',
                    'animals.type',
                    'animals.health_status',
                    'animals.vet_id'
                );
            }
        ]);

        $vet = $user->veterinarian;

        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinarian profile has not been completed yet.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'id'              => $user->id,
                    'full_name'       => $user->full_name,
                    'email'           => $user->email,
                    'phone_number'    => $user->phone_number,
                    'country_code'    => $user->country_code,
                    'governorate'     => $user->governorate,

                    // User photo
                    'photo'           => $user->photo
                        ? asset('storage/' . $user->photo)
                        : null,

                    // Veterinarian data
                    'specialization'  => $vet->specialization,
                    'clinic_location' => $vet->clinic_location,
                    'working_hours'   => $vet->working_hours,
                    'license_number'  => $vet->license_number,
                    'experience_years'=> $vet->experience_years,
                    'about'           => $vet->about,
                    'bio'             => $vet->bio,
                    'is_approved'     => $vet->is_approved,
                    'approved_at'     => $vet->approved_at,
                ],

                'my_posts' => $vet->awarenessPosts ?? [],

                'my_patients' => $vet->animals ?? []
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
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. This route is for veterinarians only.'
        ], 403);
    }

    $vet = $user->veterinarian;

    if (!$vet) {
        return response()->json([
            'success' => false,
            'message' => 'Veterinarian profile has not been completed yet.'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        // User fields
        'full_name'       => 'sometimes|required|string|max:255',
        'phone_number'    => 'sometimes|required|string|max:20',
        'country_code'    => 'sometimes|string|max:5',
        'governorate'     => 'sometimes|required|string|max:100',
        'photo'           => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',

        // Veterinarian fields
        'specialization'  => 'sometimes|required|string|max:255',
        'clinic_location' => 'sometimes|required|string|max:255',
        'license_number'  => 'sometimes|required|string|max:255|unique:veterinarians,license_number,' . $vet->id,
        'working_hours'   => 'sometimes|nullable|string|max:255',
        'experience_years'=> 'sometimes|nullable|integer|min:0|max:100',
        'about'           => 'sometimes|nullable|string',
        'bio'             => 'sometimes|nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    DB::transaction(function () use ($request, $user, $vet) {

        /*
        |--------------------------------------------------------------------------
        | Update user data
        |--------------------------------------------------------------------------
        */

        $userData = $request->only([
            'full_name',
            'phone_number',
            'country_code',
            'governorate',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update profile photo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('photo')) {

            if (
                $user->photo &&
                Storage::disk('public')->exists($user->photo)
            ) {
                Storage::disk('public')->delete($user->photo);
            }

            $userData['photo'] = $request
                ->file('photo')
                ->store('users', 'public');
        }

        $user->update($userData);

        /*
        |--------------------------------------------------------------------------
        | Update veterinarian data
        |--------------------------------------------------------------------------
        */

        $vet->update($request->only([
            'specialization',
            'clinic_location',
            'license_number',
            'working_hours',
            'experience_years',
            'about',
            'bio',
        ]));
    });

    $user->load('veterinarian');

    $vet = $user->veterinarian;

    return response()->json([
        'success' => true,
        'message' => 'Veterinarian profile updated successfully.',
        'data' => [
            'id'               => $user->id,
            'full_name'        => $user->full_name,
            'email'            => $user->email,
            'phone_number'     => $user->phone_number,
            'country_code'     => $user->country_code,
            'governorate'      => $user->governorate,

            'photo'            => $user->photo
                ? asset('storage/' . $user->photo)
                : null,

            'specialization'   => $vet->specialization,
            'clinic_location'  => $vet->clinic_location,
            'working_hours'    => $vet->working_hours,
            'license_number'   => $vet->license_number,
            'experience_years' => $vet->experience_years,
            'about'            => $vet->about,
            'bio'              => $vet->bio,
            'is_approved'      => $vet->is_approved,
            'approved_at'      => $vet->approved_at,
        ]
    ], 200);
}

    public function updateVolunteerProfile(Request $request)
{
    $user = $request->user();

    if (!$user->hasRole('volunteer')) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. This route is for volunteers only.'
        ], 403);
    }

    $volunteer = $user->volunteer;

    $validator = Validator::make($request->all(), [
        'full_name'       => 'sometimes|required|string|max:255',
        'phone_number'    => 'sometimes|required|string|max:20',
        'governorate'     => 'sometimes|required|string|max:100',
        'photo'           => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        'available_hours' => 'sometimes|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $photoPath = $user->photo; // الاحتفاظ بالمسار القديم افتراضياً

    // معالجة رفع الصورة
    if ($request->hasFile('photo')) {
        // 1. حذف الصورة القديمة إذا كانت موجودة
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // 2. حفظ الصورة الجديدة وأخذ مسارها النصي
        $path = $request->file('photo')->store('users', 'public');
        $photoPath = Storage::url($path); // أو $path حسب كيفية تخزين المسارات لديك في المشروع
    }

    // التحديث داخل الـ Transaction
    DB::transaction(function () use ($request, $user, $volunteer, $photoPath) {
        $userData = $request->only(['full_name', 'phone_number', 'governorate']);

        // إسناد مسار الصورة النصي
        if ($request->hasFile('photo')) {
            $userData['photo'] = $photoPath;
        }

        $user->update($userData);

        if ($volunteer) {
            $volunteer->update($request->only(['available_hours']));
        }
    });

    $user->load('volunteer');

    return response()->json([
        'success' => true,
        'message' => 'Volunteer profile updated successfully.',
        'data'    => $user
    ], 200);
}

    public function deletePhoto(User $photo)
    {
        $relativePath = str_replace('/storage/', '', $photo->photo_url);
        Storage::disk('public')->delete($relativePath);
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media asset dropped successfully.'
        ]);
    }

    //============ ADMIN PROFILE ==============

    /**
 * عرض بروفايل الأدمن
 * GET /api/v1/admin/profile
 */
    public function showAdminProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('SuperAdmin') && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admins only.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'personal_information' => [
                    'id'         => $user->id,
                    'full_name'  => $user->full_name,
                    'email'      => $user->email,
                    'phone'      => trim(($user->country_code ?? '') . ' ' . ($user->phone_number ?? '')),
                    'country_code'=> $user->country_code,
                    'phone_number'=> $user->phone_number,
                    'role'       => $user->getRoleNames()->first() ?? 'SuperAdmin',
                    'department' => 'Management', // ثابت مؤقتًا لأن ما في عمود
                    'joined_on'  => optional($user->created_at)->format('d F Y'),
                    'last_login' => null, // يحتاج عمود last_login_at
                    'location'   => $user->governorate,
                    'photo'      => $user->photo
                        ? asset('storage/' . ltrim($user->photo, '/'))
                        : null,
                ],
                'security_access' => [
                    'two_factor_enabled' => (bool) $user->two_factor_enabled,
                    'account_status'     => $user->account_status,
                    'active_sessions'    => $user->tokens()->count(),
                ],
            ]
        ]);
    }


    public function updateAdminProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('SuperAdmin') && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admins only.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'full_name'     => 'sometimes|string|max:255',
            'country_code'  => 'sometimes|string|max:5',
            'phone_number'  => 'sometimes|string|max:20',
            'governorate'   => 'sometimes|string|max:100',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,avif|max:2048',
            'two_factor_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // تحديث الصورة
        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('users', 'public');
        }

        $user->fill($request->only([
            'full_name',
            'country_code',
            'phone_number',
            'governorate',
            'two_factor_enabled',
        ]));

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Admin profile updated successfully',
            'data' => [
                'id'            => $user->id,
                'full_name'     => $user->full_name,
                'email'         => $user->email,
                'country_code'  => $user->country_code,
                'phone_number'  => $user->phone_number,
                'governorate'   => $user->governorate,
                'two_factor_enabled' => (bool) $user->two_factor_enabled,
                'account_status'=> $user->account_status,
                'photo'         => $user->photo
                    ? asset('storage/' . ltrim($user->photo, '/'))
                    : null,
            ]
        ]);
    }

    /**
 * حيوانات الطبيب البيطري الحالي
 * GET /api/v1/vet/my-animals
 */
    public function myAnimals(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('veterinarian')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. This route is for veterinarians only.'
            ], 403);
        }

        $vet = $user->veterinarian;

        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinarian profile not completed yet.'
            ], 404);
        }

        $animals = \App\Models\Animal::with('photos')
            ->where('vet_id', $vet->id)
            ->latest()
            ->get()
            ->map(function ($animal) {
                return [
                    'id'                  => $animal->id,
                    'name'                => $animal->name,
                    'type'                => $animal->type,
                    'gender'              => $animal->gender,
                    'age'                 => $animal->age,
                    'size'                => $animal->size,
                    'health_status'       => $animal->health_status,
                    'availability_status' => $animal->availability_status,
                    'is_urgent'           => (bool) $animal->is_urgent,
                    'photo'               => optional($animal->photos->first())->photo_url
                        ? (str_starts_with($animal->photos->first()->photo_url, 'http')
                            ? $animal->photos->first()->photo_url
                            : asset('storage/' . ltrim($animal->photos->first()->photo_url, '/')))
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total'   => $animals->count(),
                'animals' => $animals,
            ]
        ]);
    }


}
