<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\Sponsorship;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\SendNotificationEvent;
use App\Support\NotificationTemplates;
use Illuminate\Support\Facades\Log;

class AdoptionApplicationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_id'                => 'required|exists:animals,id',
            'reason_for_adoption'      => 'required|string|min:30',
            'has_other_pets'           => 'required|boolean',
            'other_pets_info'          => 'required_if:has_other_pets,true|string|nullable',
            'housing_type'             => 'required|in:house,apartment,villa',
            'has_garden'               => 'required|boolean',
            'family_members_count'     => 'required|integer|min:1',
            'children_under_10'        => 'required|boolean',
            'work_schedule'            => 'required|string',
            'experience_with_animals'  => 'required|string|min:20',
            'commitment_declaration'   => 'required|accepted',
            'emergency_contact_name'   => 'required|string|max:255',
            'emergency_contact_phone'  => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $animal = Animal::findOrFail($request->animal_id);

        $alreadyApproved = AdoptionApplication::where('animal_id', $animal->id)
            ->where('status', 'approved')
            ->exists();

        if ($alreadyApproved) {
            return response()->json([
                'success' => false,
                'message' => 'This animal is currently reserved pending completion of an adoption process.'
            ], 422);
        }

        if ($animal->availability_status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'This animal is not available for adoption at the moment.'
            ], 422);
        }

        $otherPetsText = $request->has_other_pets ? $request->other_pets_info : 'None';
        $gardenText = $request->has_garden ? 'Yes' : 'No';
        $childrenText = $request->children_under_10 ? 'Yes' : 'No';

        $applicationDetails =
            "--- Detailed Adoption Application ---\n" .
            "• Reason for adoption: " . $request->reason_for_adoption . "\n" .
            "• Has other pets? " . ($request->has_other_pets ? 'Yes' : 'No') . " (Details: " . $otherPetsText . ")\n" .
            "• Housing type: " . $request->housing_type . "\n" .
            "• Has a garden? " . $gardenText . "\n" .
            "• Family members count: " . $request->family_members_count . "\n" .
            "• Children under 10? " . $childrenText . "\n" .
            "• Work schedule: " . $request->work_schedule . "\n" .
            "• Experience with animals: " . $request->experience_with_animals . "\n" .
            "• Emergency contact: " . $request->emergency_contact_name . " (" . $request->emergency_contact_phone . ")";

        $application = AdoptionApplication::create([
            'user_id'             => $request->user()->id,
            'animal_id'           => $request->animal_id,
            'application_details' => $applicationDetails,
            'status'              => 'pending',
        ]);

        $activeSponsorship = Sponsorship::with('sponsor')
            ->where('animal_id', $animal->id)
            ->where('status', 'active')
            ->first();

        if ($activeSponsorship) {
            $template = NotificationTemplates::newAdoptionRequest($animal, $application);

            SendNotificationEvent::dispatch(
                $activeSponsorship->sponsor,
                $template['title'],
                $template['body'],
                $template['data']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Adoption application submitted successfully. It will be reviewed by the administration.',
            'data'    => $application->load('animal')
        ], 201);
    }

    public function myApplications(Request $request)
    {
        $applications = AdoptionApplication::where('user_id', $request->user()->id)
            ->with([
                'animal:id,name,type,health_status',
                'animal.photos'
            ])
            ->latest()
            ->get();

        $applications->each(function ($application) {
            if ($application->animal) {
                $mainPhoto = $application->animal->photos
                    ->where('is_main', true)
                    ->first();

                $application->animal->photo = $mainPhoto
                    ? asset('storage/' . ltrim($mainPhoto->photo_url, '/'))
                    : null;

                unset($application->animal->photos);
            }
        });

        return response()->json([
            'success' => true,
            'data'    => $applications
        ]);
    }

    public function index(Request $request)
    {
        $query = AdoptionApplication::with(['user:id,full_name,email', 'animal:id,name,type']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('animal_id')) {
            $query->where('animal_id', $request->animal_id);
        }

        $applications = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $applications
        ]);
    }

    public function show(AdoptionApplication $application)
    {
        $application->load(['user', 'animal']);

        return response()->json([
            'success' => true,
            'data'    => $application
        ]);
    }

    public function approve(Request $request, AdoptionApplication $application)
    {
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application cannot be approved in its current status.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $application->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            $animal = $application->animal;

            $animal->update([
                'availability_status' => 'reserved',
                'reserved_until'      => now()->addDays(5),
            ]);

            $template = NotificationTemplates::adoptionApproved($animal, $application);

            SendNotificationEvent::dispatch(
                $application->user,
                $template['title'],
                $template['body'],
                $template['data']
            );

            $activeSponsorship = Sponsorship::with('sponsor')
                ->where('animal_id', $animal->id)
                ->where('status', 'active')
                ->first();

            if ($activeSponsorship) {
                $template = NotificationTemplates::sponsorshipCompleted($animal);

                SendNotificationEvent::dispatch(
                    $activeSponsorship->sponsor,
                    $template['title'],
                    $template['body'],
                    $template['data']
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Adoption application approved. The animal is reserved for 5 days to complete the adoption process.',
                'data'    => $application->load('animal')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the application.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, AdoptionApplication $application)
    {
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application cannot be rejected in its current status.'
            ], 422);
        }

        $application->update([
            'status' => 'rejected',
        ]);

        $template = NotificationTemplates::adoptionRejected(
            $application->animal,
            $application
        );

        SendNotificationEvent::dispatch(
            $application->user,
            $template['title'],
            $template['body'],
            $template['data']
        );

        return response()->json([
            'success' => true,
            'message' => 'Adoption application has been rejected.',
            'data'    => $application
        ]);
    }

    public function update(Request $request, AdoptionApplication $application)
    {
        if ($application->user_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this application.'
            ], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Processed applications cannot be updated.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason_for_adoption'     => 'string|min:30',
            'has_other_pets'          => 'boolean',
            'other_pets_info'         => 'nullable|string',
            'housing_type'            => 'in:house,apartment,villa',
            'has_garden'              => 'boolean',
            'family_members_count'    => 'integer|min:1',
            'children_under_10'       => 'boolean',
            'work_schedule'           => 'string',
            'experience_with_animals' => 'string|min:20',
            'commitment_declaration'  => 'accepted',
            'emergency_contact_name'  => 'string|max:255',
            'emergency_contact_phone' => 'string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $application->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Application updated successfully.',
            'data'    => $application
        ]);
    }

    public function destroy(Request $request, AdoptionApplication $application)
    {
        if ($application->user_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Adoption application deleted successfully.'
        ]);
    }

    public function changeStatus(Request $request, AdoptionApplication $application)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'The status of a processed application cannot be changed.'
            ], 422);
        }

        if ($request->status === 'approved') {
            return $this->approve($request, $application);
        }

        return $this->reject($request, $application);
    }

    public function complete(Request $request, AdoptionApplication $application)
    {
        if ($application->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This adoption cannot be completed in its current status.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $animal = $application->animal;

            if ($animal->availability_status !== 'reserved') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'This animal is no longer reserved for this application.'
                ], 422);
            }

            $application->update([
                'status' => 'completed',
            ]);

            $animal->update([
                'availability_status' => 'adopted',
                'reserved_until'      => null,
            ]);

            $activeSponsorship = Sponsorship::where('animal_id', $animal->id)
                ->where('status', 'active')
                ->first();

            if ($activeSponsorship) {
                $template = NotificationTemplates::sponsorshipCompleted($animal);

                SendNotificationEvent::dispatch(
                    $activeSponsorship->sponsor,
                    $template['title'],
                    $template['body'],
                    $template['data']
                );

                $activeSponsorship->update([
                    'status' => 'cancelled',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Adoption process completed successfully.',
                'data'    => $application->load('animal')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete adoption: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the adoption process.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}