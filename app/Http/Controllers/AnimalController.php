<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Veterinarian;
use App\Models\AnimalUpdate;
use App\Models\AnimalPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AnimalController extends Controller
{
/**
     * Display a listing of animals with real-time filtering (Type, Size, Gender, Age).
     */
    public function index(Request $request)
    {
        $query = Animal::with(['photos', 'vet']);

        // 1. الفلترة حسب النوع (Type)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 2. الفلترة حسب الحجم (Size)
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        // 3. الفلترة حسب الجنس (Gender)
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // 4. الفلترة حسب العمر (Age)
        if ($request->filled('age')) {
            $query->where('age', $request->age);
        }

        $animals = $query->latest()->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $animals
        ]);
    }

    /**
     * Store a newly created animal in the system.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'                => 'required|in:dog,cat,bird,rabbit,other',
            'name'                => 'nullable|string|max:100',
            'age'                 => 'nullable|integer|min:0',
            'size'                => 'nullable|in:small,medium,large',
            'gender'              => 'required|in:male,female,unknown',
            'weight'              => 'nullable|numeric|min:0',
            'health_status'       => 'required|in:healthy,sick,injured,critical,recovering',
            'story'               => 'nullable|string',
            'description'         => 'nullable|string',
            'vet_id'              => 'nullable|exists:veterinarians,id',
            'rescue_report_id'    => 'nullable|exists:rescue_reports,id',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'availability_status' => 'in:available,pending,adopted,sponsored,under_treatment',
            'is_urgent'           => 'boolean',
            'is_vaccinated'       => 'boolean',
            'is_neutered'         => 'boolean',
            'photos.*'            => 'image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $animal = Animal::create($request->except('photos'));

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('animals/' . $animal->id, 'public');

                AnimalPhoto::create([
                    'animal_id'    => $animal->id,
                    'photo_url'    => Storage::url($path),
                    'is_main'      => $index === 0,   
                    'order_number' => $index,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Animal profile successfully added to records.',
            'data'    => $animal->load('photos')
        ], 201);
    }

    /**
     * Display the specified animal details.
     */
    public function show(Animal $animal)
    {
        $animal->load(['photos', 'vet']);

        return response()->json([
            'success' => true,
            'data'    => $animal
        ]);
    }

    /**
     * Update the specified animal profile in storage.
     */
    public function update(Request $request, Animal $animal)
    {
        $user = $request->user();
        $currentVetId = null;

        if ($user->hasRole('Veterinarian')) {
            $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();
            
            if (!$vet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Your professional medical account must be verified and active to modify health statuses.'
                ], 403);
            }
            $currentVetId = $vet->id;
        }

        $validator = Validator::make($request->all(), [
            'type'                => 'in:dog,cat,bird,rabbit,other',
            'name'                => 'nullable|string|max:100',
            'age'                 => 'nullable|integer|min:0',
            'size'                => 'in:small,medium,large',
            'gender'              => 'in:male,female,unknown',
            'weight'              => 'nullable|numeric|min:0',
            'health_status'       => 'in:healthy,sick,injured,critical,recovering',
            'story'               => 'nullable|string',
            'description'         => 'nullable|string',
            'vet_id'              => 'nullable|exists:veterinarians,id',
            'availability_status' => 'in:available,pending,adopted,sponsored,under_treatment',
            'is_urgent'           => 'boolean',
            'is_vaccinated'       => 'boolean',
            'is_neutered'         => 'boolean',
            'health_update_title' => 'nullable|string|max:255',
            'health_update_note'  => 'nullable|string', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $oldHealthStatus = $animal->health_status;
        $oldAvailability = $animal->availability_status;

        $updateData = $request->all();

        if ($currentVetId) {
            $updateData['vet_id'] = $currentVetId;
        }

        DB::beginTransaction();
        try {
            $animal->update($updateData);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store('animals/' . $animal->id, 'public');

                    AnimalPhoto::create([
                        'animal_id'    => $animal->id,
                        'photo_url'    => Storage::url($path),
                        'is_main'      => false,
                        'order_number' => $animal->photos()->count() + $index,
                    ]);
                }
            }

            $healthChanged = ($oldHealthStatus !== $animal->health_status);
            $statusChanged = ($oldAvailability !== $animal->availability_status);

            if ($healthChanged || $statusChanged || $request->filled('health_update_note')) {

                $title = $request->input('health_update_title') ?? 'Medical status report from supervising veterinarian';
                
                $content = "The attending veterinarian has updated the animal medical profile.\n";
                if ($healthChanged) {
                    $content .= "• Current Health Status: " . $animal->health_status . " (Was: " . $oldHealthStatus . ").\n";
                }
                if ($statusChanged) {
                    $content .= "• Shelter Availability Status: " . $animal->availability_status . ".\n";
                }
                if ($request->filled('health_update_note')) {
                    $content .= "• Vet Notes: " . $request->health_update_note;
                }

                AnimalUpdate::create([
                    'animal_id' => $animal->id,
                    'title'     => $title,
                    'content'   => $content,
                    'type'      => 'health', 
                    'media_url' => $animal->photos()->first()?->photo_url 
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Animal details updated successfully. Health report posted to sponsor timeline.',
                'data'    => $animal->load('photos')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during profile updates.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified animal and associated assets from storage.
     */
    public function destroy(Animal $animal)
    {
        foreach ($animal->photos as $photo) {
            $relativePath = str_replace('/storage/', '', $photo->photo_url);
            Storage::disk('public')->delete($relativePath);
            $photo->delete();
        }

        $animal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Animal logs and associated media assets deleted successfully.'
        ]);
    }

    /**
     * Delete a single isolated animal image.
     */
    public function deletePhoto(AnimalPhoto $photo)
    {
        $relativePath = str_replace('/storage/', '', $photo->photo_url);
        Storage::disk('public')->delete($relativePath);
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media asset dropped successfully.'
        ]);
    }
}