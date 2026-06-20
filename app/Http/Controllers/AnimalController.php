<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AnimalController extends Controller
{
    /**
     * Display a listing of animals
     */
    public function index(Request $request)
    {
        $query = Animal::with(['photos', 'vet']);

        // Advanced Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }
          if ($request->has('status')) {
            $query->where('availability_status', $request->status);
        }
        if ($request->filled('urgent')) {
            $query->where('is_urgent', true);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%'.$request->search.'%')
                  ->orWhere('description', 'LIKE', '%'.$request->search.'%');
            });
        }

        $animals = $query->latest()->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $animals
        ]);
    }

    /**
     * Store a newly created animal
     */
   public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'nullable|string|max:100',
            'type'                  => 'required|in:dog,cat,bird,rabbit,other',
            'gender'                => 'required|in:male,female',
            'age'                   => 'nullable|integer|min:0',
            'size'                  => 'nullable|in:small,medium,large',
            'weight'                => 'nullable|numeric|min:0',
            'description'           => 'required|string',
            'story'                 => 'nullable|string',
            'health_status'         => 'required|in:healthy,injured,critical,recovering',
            'availability_status'   => 'required|in:available,pending,adopted,sponsored',
            'is_vaccinated'         => 'boolean',
            'is_neutered'           => 'boolean',
            'is_urgent'             => 'boolean',
            'latitude'              => 'nullable|numeric',
            'longitude'             => 'nullable|numeric',
            'vet_id'                => 'nullable|exists:veterinarians,id',
            'rescue_report_id'    => 'nullable|exists:rescue_reports,id',
            'photos.*'              => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $animal = Animal::create($request->except('photos'));

        // Handle Photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('animals/' . $animal->id, 'public');

                AnimalPhoto::create([
                    'animal_id'    => $animal->id,
                    'photo_url'    => $path,
                    'is_main'      => $index === 0,
                    'order_number' => $index,
                ]);
            }
        }

        $animal->load('photos');

        return response()->json([
            'success' => true,
            'message' => 'Animal created and added to logs successfully',
            'data'    => $animal->load('photos')
        ], 201);
    }

    /**
     * Display the specified animal
     */
    public function show(Animal $animal)
    {
        $animal->load(['photos', 'vet', 'vaccinations', 'behavioralAttributes']);

        return response()->json([
            'success' => true,
            'data'    => $animal
        ]);
    }

    /**
     * Update the specified animal
     */
    public function update(Request $request, Animal $animal)
    {
        $validator = Validator::make($request->all(), [
            'type'                  => 'string|max:50',
            'name'                  => 'nullable|string|max:100',
            'age'                   => 'nullable|string|max:30',
            'size'                  => 'nullable|string|max:30',
            'gender'                => 'in:Male,Female,Unknown',
            'weight'                => 'nullable|numeric|min:0',
            'health_status'         => 'in:Healthy,Sick,Critical',
            'availability_status'   => 'in:Available,Pending,Adopted,Sponsored',
            'is_vaccinated'         => 'boolean',
            'is_neutered'           => 'boolean',
            'story'                 => 'nullable|string',
            'suitable_for_children' => 'boolean',
            'vet_id'                => 'nullable|exists:veterinarians,id',
            'rescue_report_id'    => 'nullable|exists:rescue_reports,id',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $animal->update($request->all());

        // Handle new photos if uploaded
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('animals/' . $animal->id, 'public');

                AnimalPhoto::create([
                    'animal_id'    => $animal->id,
                    'photo_url'    => $path,
                    'is_main'      => false,
                    'order_number' => $animal->photos()->count() + $index,
                ]);
            }
        }

        $animal->load('photos');

        return response()->json([
            'success' => true,
            'message' => 'Animal updated successfully',
            'data'    => $animal
        ]);
    }

    /**
     * Remove the specified animal
     */
    public function destroy(Animal $animal)
    {
        // Delete all photos from storage
        foreach ($animal->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_url);
        }

        $animal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Animal deleted successfully'
        ]);
    }

    /**
     * Delete single photo
     */
    public function deletePhoto(AnimalPhoto $photo)
    {
        Storage::disk('public')->delete($photo->photo_url);
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
    }
}
