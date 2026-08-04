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

        $rescueReportsCount = \App\Models\RescueReport::where('user_id', $user->id)
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
    //     public function update(Request $request)
    // {
    //     \Log::info('Has file?', [$request->hasFile('photo')]);
    // \Log::info('All files', $request->allFiles());
    //     $validator = Validator::make($request->all(), [
    //         'full_name'    => 'sometimes|string|max:255',
    //         'country_code' => 'sometimes|string|max:5',
    //         'phone_number' => 'sometimes|string|max:15',
    //         'governorate'  => 'sometimes|string|max:100',
    //         'photo'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors'  => $validator->errors()
    //         ], 422);
    //     }

    //     $user = $request->user();

    //     // تحديث الصورة لو موجودة
    //     if ($request->hasFile('photo')) {
    //         // حذف الصورة القديمة
    //         if ($user->photo && Storage::disk('public')->exists($user->photo)) {
    //             Storage::disk('public')->delete($user->photo);
    //         }

    //         // حفظ الجديدة
    //         $user->photo = $request->file('photo')->store('users', 'public');
    //     }

    //     // تحديث باقي الحقول (بدون photo)
    //     $user->fill($request->only([
    //         'full_name',
    //         'country_code',
    //         'phone_number',
    //         'governorate',
    //     ]));

    //     $user->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Profile updated successfully',
    //         'user'    => [
    //             'id'           => $user->id,
    //             'full_name'    => $user->full_name,
    //             'email'        => $user->email,
    //             'country_code' => $user->country_code,
    //             'phone_number' => $user->phone_number,
    //             'governorate'  => $user->governorate,
    //             'photo'        => $user->photo ? asset('storage/' . $user->photo) : null,
    //         ]
    //     ]);
    // }

//     public function update(Request $request)
// {
//     // // ====================== Debugging كامل ======================
//     // \Log::info('========== START DEBUG ==========');
//     // \Log::info('Method: ' . $request->method());
//     // \Log::info('Content-Type: ' . $request->header('Content-Type'));
//     // \Log::info('All Headers:', $request->headers->all());
//     // \Log::info('All Input:', $request->all());
//     // \Log::info('All Files:', $request->allFiles());
//     // \Log::info('Has file photo?: ' . ($request->hasFile('photo') ? 'YES' : 'NO'));
//     // \Log::info('File photo exists?: ' . ($request->file('photo') ? 'YES' : 'NO'));

//     // if ($request->file('photo')) {
//     //     \Log::info('File details:', [
//     //         'original_name' => $request->file('photo')->getClientOriginalName(),
//     //         'mime' => $request->file('photo')->getMimeType(),
//     //         'size' => $request->file('photo')->getSize(),
//     //         'error' => $request->file('photo')->getError(),
//     //         'isValid' => $request->file('photo')->isValid(),
//     //     ]);
//     // }

//     // \Log::info('php.ini upload_max_filesize: ' . ini_get('upload_max_filesize'));
//     // \Log::info('php.ini post_max_size: ' . ini_get('post_max_size'));
//     // \Log::info('========== END DEBUG ==========');

//     // // باقي الكود العادي...
//     $user = $request->user();

//     if ($request->hasFile('photo')) {
//         if ($user->photo && Storage::disk('public')->exists($user->photo)) {
//             Storage::disk('public')->delete($user->photo);
//         }
//         $user->photo = $request->file('photo')->store('users', 'public');
//     }

//     $user->fill($request->only([
//         'full_name',
//         'country_code',
//         'phone_number',
//         'governorate',
//     ]));

//     $user->save();

//     return response()->json([
//         'success' => true,
//         'message' => 'Profile updated successfully',
//         'user' => [
//             'id'           => $user->id,
//             'full_name'    => $user->full_name,
//             'photo'        => $user->photo ? asset('storage/' . $user->photo) : null,
//         ]
//     ]);
// }
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
                    'photo'        => $user->photo ? asset('storage/' . $user->photo) : null,
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
        return response()->json([
            'success' => false, 
            'message' => 'Unauthorized. This route is for veterinarians only.'
        ], 403);
    }

    $vet = $user->veterinarian;

    $validator = Validator::make($request->all(), [
        'full_name'      => 'sometimes|required|string|max:255',
        'phone_number'   => 'sometimes|required|string|max:20',
        'photo'          => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        'governorate'    => 'sometimes|required|string|max:100',
        'clinic_address' => 'sometimes|string|max:255',
        'specialization' => 'sometimes|string|max:255',
        'bio'            => 'sometimes|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $photoPath = $user->photo; // الاحتفاظ بالمسار الحالي افتراضياً

    // معالجة رفع الصورة إذا تم إرسال ملف جديد
    if ($request->hasFile('photo')) {
        // 1. حذف الصورة القديمة من القرص إذا كانت موجودة
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // 2. رفع الصورة الجديدة وجلب المسار النصي
        $path = $request->file('photo')->store('users', 'public');
        $photoPath = Storage::url($path);
    }

    // إجراء التعديلات داخل المعاملة (Transaction)
    DB::transaction(function () use ($request, $user, $vet, $photoPath) {
        $userData = $request->only(['full_name', 'phone_number', 'governorate']);

        // تعيين المسار النصي للصورة في مصفوفة التحديث
        if ($request->hasFile('photo')) {
            $userData['photo'] = $photoPath;
        }

        $user->update($userData);

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

    // public function updateVetProfile(Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user->hasRole('veterinarian')) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized. This route is for veterinarians only.'], 403);
    //     }

    //     $vet = $user->veterinarian;

    //     $validator = Validator::make($request->all(), [
    //         'full_name'    => 'sometimes|required|string|max:255',
    //         'phone_number' => 'sometimes|required|string|max:20',
    //         'photo'          => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
    //         'governorate'  => 'sometimes|required|string|max:100',
    //         'clinic_address' => 'sometimes|string|max:255',
    //         'specialization' => 'sometimes|string|max:255',
    //         'bio'            => 'sometimes|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     DB::transaction(function () use ($request, $user, $vet) {
    //         $user->update($request->only(['full_name', 'phone_number', 'governorate' , 'photo']));
    //         if ($vet) {
    //             $vet->update($request->only(['clinic_address', 'specialization', 'bio']));
    //         }
    //     });

    //     $user->load('veterinarian');

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Veterinarian profile updated successfully.',
    //         'data'    => $user
    //     ], 200);
    // }

    // public function updateVolunteerProfile(Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user->hasRole('volunteer')) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized. This route is for volunteers only.'], 403);
    //     }

    //     $volunteer = $user->volunteer;

    //     $validator = Validator::make($request->all(), [
    //         'full_name'    => 'sometimes|required|string|max:255',
    //         'phone_number' => 'sometimes|required|string|max:20',
    //         'governorate'  => 'sometimes|required|string|max:100',
    //         'photo'          => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
    //         'skills'            => 'sometimes|string',
    //         'available_hours'   => 'sometimes|integer',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    //     }
    //     if ($request->hasFile('photo')) {

    // // حذف الصورة القديمة
    //     if ($user->photo && Storage::disk('public')->exists($user->photo)) {
    //         Storage::disk('public')->delete($user->photo);
    //     }

    //     // حفظ الجديدة
    //     $user->photo = $request->file('photo')->store('users', 'public');
    // }

    //     DB::transaction(function () use ($request, $user, $volunteer) {
    //         $user->update($request->only(['full_name', 'phone_number', 'governorate' , 'photo']));

    //         if ($volunteer) {
    //             $volunteer->update($request->only(['skills', 'available_hours']));
    //         }
    //     });

    //     $user->load('volunteer');

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Volunteer profile updated successfully.',
    //         'data'    => $user
    //     ], 200);
    // }
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
        'skills'          => 'sometimes|string',
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
