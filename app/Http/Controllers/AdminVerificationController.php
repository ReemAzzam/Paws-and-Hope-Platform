<?php

namespace App\Http\Controllers;

use App\Models\Veterinarian;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminVerificationController extends Controller
{

    public function approveVeterinarian(Request $request, $id)
    {
        $vet = Veterinarian::with('user')->findOrFail($id);

        if ($vet->is_approved && $vet->user?->account_status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This veterinarian is already verified and active.'
            ], 400);
        }

        DB::transaction(function () use ($vet, $request) {

            $vet->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ]);

            $vet->user?->update([
                'account_status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian approved successfully.',
            'data' => $vet->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }

    public function rejectVeterinarian(Request $request, $id)
    {
        $vet = Veterinarian::with('user')->findOrFail($id);

        if ($vet->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This veterinarian is already approved. Use the block action instead.'
            ], 400);
        }

        DB::transaction(function () use ($vet) {
            $vet->update([
                'is_approved' => false,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            if ($vet->user) {
                $vet->user->update([
                    'account_status' => 'rejected',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "The veterinarian application for Dr. ({$vet->user?->full_name}) has been rejected.",
            'data' => $vet->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }

    public function approveVolunteer(Request $request, $id)
    {
        $volunteer = Volunteer::with('user')->findOrFail($id);

        if ($volunteer->is_approved && $volunteer->user?->account_status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This volunteer is already approved.'
            ], 400);
        }

        DB::transaction(function () use ($volunteer, $request) {

            $volunteer->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ]);

            $volunteer->user?->update([
                'account_status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Volunteer approved successfully.',
            'data' => $volunteer->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }

    public function rejectVolunteer(Request $request, $id)
    {
        $volunteer = Volunteer::with('user')->findOrFail($id);

        if ($volunteer->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This volunteer is already approved. Use the block action instead.'
            ], 400);
        }

        DB::transaction(function () use ($volunteer) {
            $volunteer->update([
                'is_approved' => false,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            if ($volunteer->user) {
                $volunteer->user->update([
                    'account_status' => 'rejected',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'The volunteer application has been rejected successfully.',
            'data' => $volunteer->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }

    public function blockVeterinarian(Request $request, $id)
    {
        $vet = Veterinarian::with('user')->findOrFail($id);

        if (!$vet->is_approved || $vet->user?->account_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This account is already blocked or inactive.'
            ], 400);
        }

        DB::transaction(function () use ($vet) {

            $vet->update([
                'is_approved' => false,
            ]);

            if ($vet->user) {
                $vet->user->update([
                    'account_status' => 'suspended',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian account has been blocked successfully.',
            'data' => $vet->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }

   public function blockVolunteer(Request $request, $id)
    {
        $volunteer = Volunteer::with('user')->findOrFail($id);

        if (!$volunteer->is_approved || $volunteer->user?->account_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This volunteer account is already blocked or inactive.'
            ], 400);
        }

        DB::transaction(function () use ($volunteer) {

            $volunteer->update([
                'is_approved' => false,
            ]);

            if ($volunteer->user) {
                $volunteer->user->update([
                    'account_status' => 'suspended',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Volunteer account has been blocked successfully.',
            'data' => $volunteer->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }

    public function getStaffCounts()
    {
        $approvedVets = Veterinarian::where('is_approved', true)
            ->whereHas('user', function ($query) {
                $query->where('account_status', 'active');
            })
            ->count();

        $pendingVets = Veterinarian::where('is_approved', false)
            ->whereHas('user', function ($query) {
                $query->where('account_status', 'pending');
            })
            ->count();

        $approvedVolunteers = Volunteer::where('is_approved', true)
            ->whereHas('user', function ($query) {
                $query->where('account_status', 'active');
            })
            ->count();

        $pendingVolunteers = Volunteer::where('is_approved', false)
            ->whereHas('user', function ($query) {
                $query->where('account_status', 'pending');
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_veterinarians'  => $pendingVets,
                'pending_volunteers'     => $pendingVolunteers,
                'approved_veterinarians' => $approvedVets,
                'approved_volunteers'    => $approvedVolunteers,
            ]
        ], 200);
    }

    public function unblockVeterinarian(Request $request, $id)
    {
        $vet = Veterinarian::with('user')->findOrFail($id);

        if ($vet->user?->account_status !== 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'This veterinarian is not blocked.'
            ], 400);
        }

        DB::transaction(function () use ($vet) {

            $vet->update([
                'is_approved' => true,
            ]);

            $vet->user?->update([
                'account_status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian account has been unblocked successfully.',
            'data' => $vet->load('user:id,full_name,email,photo,account_status'),
        ], 200);
    }
    public function unblockVolunteer(Request $request, $id)
    {
        $volunteer = Volunteer::findOrFail($id);

        if ($volunteer->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This volunteer account is already active and approved.'
            ], 400);
        }

         DB::transaction(function () use ($volunteer) {

            $volunteer->update([
                'is_approved' => true,
            ]);

            $volunteer->user?->update([
                'account_status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Volunteer account has been unblocked and field privileges are now active.',
            'data' => $volunteer->load('user:id,full_name,email,photo'),
        ], 200);
    }

    public function getApprovedVeterinarians()
    {
        $veterinarians = Veterinarian::with([
            'user:id,full_name,email,phone_number,country_code,governorate,photo,account_status',
        ])
        ->where('is_approved', true)
        ->whereHas('user', function ($query) {
            $query->where('account_status', 'active');
        })
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'count'   => $veterinarians->count(),
            'data'    => $veterinarians->map(function ($vet) {
                return [
                    'id' => $vet->id,

                    'user_id' => $vet->user?->id,

                    'photo' => $vet->user?->photo
                        ? asset('storage/' . $vet->user->photo)
                        : null,

                    'full_name' => $vet->user?->full_name,
                    'email' => $vet->user?->email,
                    'phone_number' => $vet->user?->phone_number,
                    'country_code' => $vet->user?->country_code,
                    'governorate' => $vet->user?->governorate,

                    'specialization' => $vet->specialization,
                    'clinic_location' => $vet->clinic_location,
                    'license_number' => $vet->license_number,
                    'working_hours' => $vet->working_hours,
                    'experience_years' => $vet->experience_years,
                    'about' => $vet->about,
                    'bio' => $vet->bio,

                    'is_approved' => $vet->is_approved,
                    'approved_at' => $vet->approved_at,
                ];
            })->values(),
        ], 200);
    }

    
    public function getPendingVeterinarians()
    {
        $veterinarians = Veterinarian::with([
            'user:id,full_name,email,phone_number,country_code,governorate,photo,account_status',
        ])
        ->where('is_approved', false)
        ->whereHas('user', function ($query) {
            $query->where('account_status', 'pending');
        })
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'count'   => $veterinarians->count(),
            'data'    => $veterinarians->map(function ($vet) {
                return [
                    'id' => $vet->id,

                    'user_id' => $vet->user?->id,

                    'photo' => $vet->user?->photo
                        ? asset('storage/' . $vet->user->photo)
                        : null,

                    'full_name' => $vet->user?->full_name,
                    'email' => $vet->user?->email,
                    'phone_number' => $vet->user?->phone_number,
                    'country_code' => $vet->user?->country_code,
                    'governorate' => $vet->user?->governorate,

                    'specialization' => $vet->specialization,
                    'clinic_location' => $vet->clinic_location,
                    'license_number' => $vet->license_number,
                    'working_hours' => $vet->working_hours,
                    'experience_years' => $vet->experience_years,
                    'about' => $vet->about,
                    'bio' => $vet->bio,

                    'is_approved' => $vet->is_approved,
                    'approved_at' => $vet->approved_at,
                ];
            })->values(),
        ], 200);
    }


    public function getApprovedVolunteers()
    {
        $volunteers = Volunteer::with([
            'user:id,full_name,email,phone_number,country_code,governorate,photo,account_status',
        ])
        ->where('is_approved', true)
        ->whereHas('user', function ($query) {
            $query->where('account_status', 'active');
        })
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'count'   => $volunteers->count(),
            'data'    => $volunteers->map(function ($volunteer) {
                return [
                    'id' => $volunteer->id,

                    'user_id' => $volunteer->user?->id,

                    'photo' => $volunteer->user?->photo
                        ? asset('storage/' . $volunteer->user->photo)
                        : null,

                    'full_name' => $volunteer->user?->full_name,
                    'email' => $volunteer->user?->email,
                    'phone_number' => $volunteer->user?->phone_number,
                    'country_code' => $volunteer->user?->country_code,
                    'governorate' => $volunteer->user?->governorate,

                    'detailed_address' => $volunteer->detailed_address,
                    'age' => $volunteer->age,
                    'vol_type' => $volunteer->vol_type,
                    'experience_level' => $volunteer->experience_level,
                    'equipment' => $volunteer->equipment,

                    'current_latitude' => $volunteer->current_latitude,
                    'current_longitude' => $volunteer->current_longitude,

                    'is_approved' => $volunteer->is_approved,
                    'approved_at' => $volunteer->approved_at,
                ];
            })->values(),
        ], 200);
    }

        public function getPendingVolunteers()
    {
        $volunteers = Volunteer::with([
            'user:id,full_name,email,phone_number,country_code,governorate,photo,account_status',
        ])
        ->where('is_approved', false)
        ->whereHas('user', function ($query) {
            $query->where('account_status', 'pending');
        })
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'count'   => $volunteers->count(),
            'data'    => $volunteers->map(function ($volunteer) {
                return [
                    'id' => $volunteer->id,

                    'user_id' => $volunteer->user?->id,

                    'photo' => $volunteer->user?->photo
                        ? asset('storage/' . $volunteer->user->photo)
                        : null,

                    'full_name' => $volunteer->user?->full_name,
                    'email' => $volunteer->user?->email,
                    'phone_number' => $volunteer->user?->phone_number,
                    'country_code' => $volunteer->user?->country_code,
                    'governorate' => $volunteer->user?->governorate,

                    'detailed_address' => $volunteer->detailed_address,
                    'age' => $volunteer->age,
                    'vol_type' => $volunteer->vol_type,
                    'experience_level' => $volunteer->experience_level,
                    'equipment' => $volunteer->equipment,

                    'current_latitude' => $volunteer->current_latitude,
                    'current_longitude' => $volunteer->current_longitude,

                    'is_approved' => $volunteer->is_approved,
                    'approved_at' => $volunteer->approved_at,
                ];
            })->values(),
        ], 200);
    }

    //filtering according to status and approval for veterinarians
    public function filterVeterinarians(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:all,pending,approved,rejected,blocked',
            'search' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Veterinarian::with([
            'user:id,full_name,email,phone_number,country_code,governorate,photo,account_status',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        */

        switch ($request->status) {

            case 'pending':
                $query->where('is_approved', false)
                    ->whereHas('user', function ($q) {
                        $q->where('account_status', 'pending');
                    });
                break;

            case 'approved':
                $query->where('is_approved', true)
                    ->whereHas('user', function ($q) {
                        $q->where('account_status', 'active');
                    });
                break;

            case 'rejected':
                $query->whereHas('user', function ($q) {
                    $q->where('account_status', 'rejected');
                });
                break;

            case 'blocked':
                $query->whereHas('user', function ($q) {
                    $q->where('account_status', 'suspended');
                });
                break;

            case 'all':
        break;
        }

        /*
        |--------------------------------------------------------------------------
        | Search by name or email
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $veterinarians = $query->latest()->get();

        return response()->json([
            'success' => true,
            'count'   => $veterinarians->count(),
            'data'    => $veterinarians->map(function ($vet) {

                return [
                    'id' => $vet->id,
                    'user_id' => $vet->user?->id,

                    'photo' => $vet->user?->photo
                        ? asset('storage/' . $vet->user->photo)
                        : null,

                    'full_name' => $vet->user?->full_name,
                    'email' => $vet->user?->email,
                    'phone_number' => $vet->user?->phone_number,
                    'country_code' => $vet->user?->country_code,
                    'governorate' => $vet->user?->governorate,

                    'specialization' => $vet->specialization,
                    'clinic_location' => $vet->clinic_location,
                    'license_number' => $vet->license_number,
                    'working_hours' => $vet->working_hours,
                    'experience_years' => $vet->experience_years,
                    'about' => $vet->about,
                    'bio' => $vet->bio,

                    'is_approved' => $vet->is_approved,
                    'approved_at' => $vet->approved_at,
                    'account_status' => $vet->user?->account_status,
                ];
            })->values(),
        ], 200);
    }

    //filtering according to status and approval for volunteers
    public function filterVolunteers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:all,pending,approved,rejected,blocked',
            'search' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Volunteer::with([
            'user:id,full_name,email,phone_number,country_code,governorate,photo,account_status',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        */

        switch ($request->status) {

            case 'pending':
                $query->where('is_approved', false)
                    ->whereHas('user', function ($q) {
                        $q->where('account_status', 'pending');
                    });
                break;

            case 'approved':
                $query->where('is_approved', true)
                    ->whereHas('user', function ($q) {
                        $q->where('account_status', 'active');
                    });
                break;

            case 'rejected':
                $query->whereHas('user', function ($q) {
                    $q->where('account_status', 'rejected');
                });
                break;

            case 'blocked':
                $query->whereHas('user', function ($q) {
                    $q->where('account_status', 'suspended');
                });
                break;

            case 'all':
        break;
        }

        /*
        |--------------------------------------------------------------------------
        | Search by name or email
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'count'   => $volunteers->count(),
            'data'    => $volunteers->map(function ($volunteer) {

                return [
                    'id' => $volunteer->id,
                    'user_id' => $volunteer->user?->id,

                    'photo' => $volunteer->user?->photo
                        ? asset('storage/' . $volunteer->user->photo)
                        : null,

                    'full_name' => $volunteer->user?->full_name,
                    'email' => $volunteer->user?->email,
                    'phone_number' => $volunteer->user?->phone_number,
                    'country_code' => $volunteer->user?->country_code,
                    'governorate' => $volunteer->user?->governorate,

                    'detailed_address' => $volunteer->detailed_address,
                    'age' => $volunteer->age,
                    'vol_type' => $volunteer->vol_type,
                    'experience_level' => $volunteer->experience_level,
                    'equipment' => $volunteer->equipment,

                    'current_latitude' => $volunteer->current_latitude,
                    'current_longitude' => $volunteer->current_longitude,

                    'is_approved' => $volunteer->is_approved,
                    'approved_at' => $volunteer->approved_at,
                    'account_status' => $volunteer->user?->account_status,
                ];
            })->values(),
        ], 200);
    }
    //-------------------------------------------------------------
    //the rest of admin dashboard
    public function getRegularUsers()
    {
        $users = User::role('regular_user')
            ->select([
                'id',
                'full_name',
                'email',
                'phone_number',
                'country_code',
                'governorate',
                'photo',
            ])
            ->latest()
            ->get();

        $users->transform(function ($user) {
            return [
                'id'           => $user->id,
                'full_name'    => $user->full_name,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
                'country_code' => $user->country_code,
                'address'      => $user->governorate,
                'photo'        => $user->photo
                    ? asset('storage/' . $user->photo)
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $users->count(),
            'data'    => $users,
        ], 200);
    }


    public function getVeterinarians()
    {
        $veterinarians = Veterinarian::with([
            'user:id,full_name,email,photo',
            'animals:id,name,type,health_status,vet_id'
        ])
        ->where('is_approved', true)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $veterinarians->map(function ($vet) {
                return [
                    'id' => $vet->id,

                    'photo' => $vet->user?->photo
                        ? asset('storage/' . $vet->user->photo)
                        : null,

                    'full_name' => $vet->user?->full_name,
                    'email' => $vet->user?->email,

                    'specialization' => $vet->specialization,
                    'clinic_location' => $vet->clinic_location,

                    'cases' => $vet->animals->map(function ($animal) {
                        return [
                            'id' => $animal->id,
                            'name' => $animal->name,
                            'type' => $animal->type,
                            'health_status' => $animal->health_status,
                        ];
                    })->values(),
                ];
            })->values(),
        ], 200);
    }
    //====================== الرسم البياني =========================
    public function getVerificationChart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|in:this_month,last_month',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $period = $request->input('period', 'this_month');

        if ($period === 'last_month') {
            $startDate = now()->subMonthNoOverflow()->startOfMonth();
            $endDate   = now()->subMonthNoOverflow()->endOfMonth();
        } else {
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
        }

        /*
        |--------------------------------------------------------------------------
        | Get veterinarians
        |--------------------------------------------------------------------------
        */

        $veterinarians = Veterinarian::whereBetween('created_at', [
            $startDate,
            $endDate
        ])
        ->get()
        ->groupBy(function ($vet) {
            return $vet->created_at->format('Y-m-d');
        });

        /*
        |--------------------------------------------------------------------------
        | Get volunteers
        |--------------------------------------------------------------------------
        */

        $volunteers = Volunteer::whereBetween('created_at', [
            $startDate,
            $endDate
        ])
        ->get()
        ->groupBy(function ($volunteer) {
            return $volunteer->created_at->format('Y-m-d');
        });

        /*
        |--------------------------------------------------------------------------
        | Generate chart points
        |--------------------------------------------------------------------------
        */

        $xAxis = [];
        $veterinarianData = [];
        $volunteerData = [];

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {

            $dateKey = $currentDate->format('Y-m-d');

            $xAxis[] = $currentDate->format('M j');

            $veterinarianData[] =
                $veterinarians->get($dateKey)?->count() ?? 0;

            $volunteerData[] =
                $volunteers->get($dateKey)?->count() ?? 0;

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'monthLabel' => $startDate->format('F'),

                'xAxis' => $xAxis,

                'veterinarians' => $veterinarianData,

                'volunteers' => $volunteerData,
            ]
        ], 200);
    }
}
