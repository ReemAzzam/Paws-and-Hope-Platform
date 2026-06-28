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
            'is_approved' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Volunteer field privileges have been suspended and the account is now locked.',
            'data'    => $volunteer
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
}