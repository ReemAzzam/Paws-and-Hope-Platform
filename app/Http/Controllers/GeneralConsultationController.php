<?php

namespace App\Http\Controllers;

use App\Models\GeneralConsultation;
use App\Models\Veterinarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralConsultationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question'        => 'required|string|max:2000',
            'veterinarian_id' => 'nullable|exists:veterinarians,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $consultation = GeneralConsultation::create([
            'user_id'         => $request->user()->id,
            'veterinarian_id' => $request->veterinarian_id,
            'question'        => $request->question,
            'status'          => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->veterinarian_id 
                ? 'Your question has been successfully submitted to the selected veterinarian.' 
                : 'Your general question has been published and will be answered by the first available veterinarian.',
            'data'    => $consultation
        ], 201);
    }

    public function getDoctorConsultations(Request $request)
    {
        $user = $request->user();
        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();

        if (!$vet) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to these medical cases.'], 403);
        }

        $consultations = GeneralConsultation::with('user:id,full_name')
            ->where('status', 'pending')
            ->where(function($query) use ($vet) {
                $query->where('veterinarian_id', $vet->id)
                      ->orWhereNull('veterinarian_id');
            })
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $consultations
        ], 200);
    }

    public function answer(Request $request, $id)
    {
        $user = $request->user();
        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();

        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this action is restricted to approved veterinarians only.'
            ], 403);
        }

        $consultation = GeneralConsultation::findOrFail($id);

        if ($consultation->status === 'answered') {
            return response()->json([
                'success' => false,
                'message' => 'This consultation has already been answered.'
            ], 400);
        }

        if ($consultation->veterinarian_id !== null && $consultation->veterinarian_id !== $vet->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this consultation is directed to another specific veterinarian.'
            ], 403);
        }

        $request->validate([
            'answer' => 'required|string|max:3000'
        ]);

        $consultation->update([
            'answer'          => $request->answer,
            'status'          => 'answered',
            'veterinarian_id' => $consultation->veterinarian_id ?? $vet->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'The answer has been successfully sent to the user and the consultation status has been updated.',
            'data'    => $consultation
        ], 200);
    }

    public function updateQuestion(Request $request, $id)
    {
        $consultation = GeneralConsultation::findOrFail($id);

        if ($consultation->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to modify this question.'], 403);
        }

        if ($consultation->status === 'answered') {
            return response()->json(['success' => false, 'message' => 'You cannot modify the question after it has been answered by the veterinarian.'], 400);
        }

        $request->validate([
            'question' => 'required|string|max:2000'
        ]);

        $consultation->update([
            'question' => $request->question
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your question has been successfully updated.',
            'data'    => $consultation
        ], 200);
    }

    public function destroyQuestion(Request $request, $id)
    {
        $consultation = GeneralConsultation::findOrFail($id);

        if ($consultation->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to delete this question.'], 403);
        }

        $consultation->delete();

        return response()->json([
            'success' => true,
            'message' => 'The medical consultation has been successfully deleted from the system.'
        ], 200);
    }

    public function updateAnswer(Request $request, $id)
    {
        $user = $request->user();
        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();

        if (!$vet) {
            return response()->json(['success' => false, 'message' => 'This action is restricted to approved veterinarians only.'], 403);
        }

        $consultation = GeneralConsultation::findOrFail($id);

        if ($consultation->veterinarian_id !== $vet->id || $consultation->status !== 'answered') {
            return response()->json(['success' => false, 'message' => 'You are not authorized to modify this response.'], 403);
        }

        $request->validate([
            'answer' => 'required|string|max:3000'
        ]);

        $consultation->update([
            'answer' => $request->answer
        ]);

        return response()->json([
            'success' => true,
            'message' => 'The medical response has been successfully updated.',
            'data'    => $consultation
        ], 200);
    }

    public function destroyAnswer(Request $request, $id)
    {
        $user = $request->user();
        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();

        if (!$vet) {
            return response()->json(['success' => false, 'message' => 'This action is restricted to approved veterinarians only.'], 403);
        }

        $consultation = GeneralConsultation::findOrFail($id);

        if ($consultation->veterinarian_id !== $vet->id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to delete this response.'], 403);
        }

        $consultation->update([
            'answer'          => null,
            'status'          => 'pending',
            'veterinarian_id' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'The medical response has been withdrawn and deleted, and the consultation has been returned to the pending list.',
            'data'    => $consultation
        ], 200);
    }
}