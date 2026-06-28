<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RescueConsultation;
use App\Models\RescueReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Veterinarian;
use Illuminate\Support\Facades\Auth;

class RescueConsultationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rescue_report_id' => 'required|exists:rescue_reports,id',
            'question'         => 'required|string|min:10',
        ]);

        $report = RescueReport::find($request->rescue_report_id);
        $user = auth()->user();
        $volunteer = $user->volunteer;

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you do not have an active volunteer profile for the rescue system.'
            ], 403);
        }

        if ($report->volunteer_id !== $volunteer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot request a medical consultation for a report you are not assigned to field-wise.'
            ], 403);
        }

        $consultation = RescueConsultation::create([
            'rescue_report_id' => $report->id,
            'volunteer_id'     => $volunteer->id,
            'question'         => $request->question,
            'status'           => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'The urgent consultation request has been successfully sent, and on-duty doctors are being alerted.',
            'data'    => $consultation->load('rescueReport')
        ], 201);
    }

    public function answer(Request $request, $id)
    {
        $request->validate([
            'medical_advice' => 'required|string|min:10',
        ]);

        $consultation = RescueConsultation::find($id);

        if (!$consultation) {
            return response()->json([
                'success' => false,
                'message' => 'The medical consultation was not found.'
            ], 404);
        }

        if ($consultation->status === 'answered') {
            return response()->json([
                'success' => false,
                'message' => 'This consultation has already been answered by another veterinarian.'
            ], 400);
        }

        $user = auth()->user();
        $vet = $user->veterinarian; 

        if (!$vet || !$vet->is_approved) {
            return response()->json(['success' => false, 'message' => 'Sorry, your medical account must be approved by the administration to be able to answer.'], 403);
        }

        $consultation->update([
            'veterinarian_id' => $vet->id,
            'medical_advice'  => $request->medical_advice,
            'status'          => 'answered'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medical guidelines have been successfully sent to the volunteer in the field.',
            'data'    => $consultation->load(['volunteer.user', 'veterinarian'])
        ], 200);
    }

    public function getPendingConsultations(Request $request)
    {
        $user = Auth::user();
        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();
        
        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this route is restricted to approved veterinarians only.'
            ], 403);
        }

        $consultations = RescueConsultation::with('rescueReport')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc') 
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pending medical consultations fetched successfully.',
            'count'   => $consultations->count(),
            'data'    => $consultations
        ], 200);
    }

    public function getReportConsultations($reportId)
    {
        $report = RescueReport::find($reportId);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the requested report does not exist.'
            ], 404);
        }

        $consultations = RescueConsultation::with('veterinarian.user')
            ->where('rescue_report_id', $reportId)
            ->orderBy('created_at', 'desc') 
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Medical consultation history for the report fetched successfully.',
            'count'   => $consultations->count(),
            'data'    => $consultations
        ], 200);
    }
}