<?php

namespace App\Http\Controllers;

use App\Models\Veterinarian;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVerificationController extends Controller
{
    public function approveVeterinarian(Request $request, $id)
    {
        $vet = Veterinarian::findOrFail($id);

        if ($vet->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This veterinarian is already verified and active in the system.'
            ], 400);
        }

        $vet->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => "The professional account for Dr. ({$vet->professional_name}) has been successfully approved.",
            'data'    => $vet
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
        $volunteer = Volunteer::findOrFail($id);

        if ($volunteer->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This volunteer is already approved.'
            ], 400);
        }

        $volunteer->update([
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Volunteer application approved successfully. Field privileges are now active.',
            'data'    => $volunteer
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
        $vet = Veterinarian::findOrFail($id);

        if (!$vet->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This account is already blocked or inactive in the system.'
            ], 400);
        }

        $vet->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => "The professional account for Dr. ({$vet->professional_name}) has been suspended. Veterinarian features are no longer accessible.",
            'data'    => $vet
        ], 200);
    }

    public function blockVolunteer(Request $request, $id)
    {
        $volunteer = Volunteer::findOrFail($id);

        if (!$volunteer->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This volunteer account is already inactive.'
            ], 400);
        }

        $volunteer->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Volunteer field privileges have been suspended and the account is now locked.',
            'data' => $volunteer,
        ], 200);
    }


    public function getApprovedCounts()
    {
        $approvedVets = DB::table('veterinarians')->where('is_approved', true)->count();
        $approvedVolunteers = DB::table('volunteers')->where('is_approved', true)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'approved_veterinarians_count' => $approvedVets,
                'approved_volunteers_count'   => $approvedVolunteers,
            ]
        ], 200);
    }

    public function unblockVeterinarian(Request $request, $id)
    {
        $vet = Veterinarian::findOrFail($id);

        if ($vet->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This veterinarian is already active and approved.'
            ], 400);
        }

        $vet->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "The veterinarian account for Dr. ({$vet->user->full_name}) has been unblocked and is now active.",
            'data' => $vet->load('user:id,full_name,email,photo'),
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

        $volunteer->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Volunteer account has been unblocked and field privileges are now active.',
            'data' => $volunteer->load('user:id,full_name,email,photo'),
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
}
