<?php

namespace App\Http\Controllers;

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
     * Display a listing of animals with real-time filtering (Type, Size, Gender, Age, Weight, Urgent).
     */
    public function index(Request $request)
    {
        $query = Animal::with(['photos', 'vet' , 'medicalConditions']);

        // Normalize inputs
        $type   = strtolower($request->input('type'));
        $gender = strtolower($request->input('gender'));
        $status = strtolower($request->input('status'));
        $size   = strtolower($request->input('size'));
        $urgent = $request->input('urgent');

        // Allowed ENUM values
        $allowedTypes   = ['dog', 'cat', 'bird', 'rabbit', 'other'];
        $allowedGender  = ['male', 'female', 'unknown'];
        $allowedStatus  = ['available', 'pending', 'adopted', 'sponsored', 'under_treatment'];
        $allowedSizes   = ['small', 'medium', 'large'];

        // فلترة النوع
        $query->when(in_array($type, $allowedTypes), function ($q) use ($type) {
            $q->where('type', $type);
        });

        // فلترة الجنس
        $query->when(in_array($gender, $allowedGender), function ($q) use ($gender) {
            $q->where('gender', $gender);
        });

        // فلترة الحجم (دمج ميزتكِ بهيكل الفلترة المتقدم)
        $query->when(in_array($size, $allowedSizes), function ($q) use ($size) {
            $q->where('size', $size);
        });

        // فلترة حالة الحيوان
        $query->when(in_array($status, $allowedStatus), function ($q) use ($status) {
            $q->where('availability_status', $status);
        });

        // فلترة المستعجل
        $query->when($urgent !== null, function ($q) use ($urgent) {
            $q->where('is_urgent', filter_var($urgent, FILTER_VALIDATE_BOOLEAN));
        });

        // فلترة العمر (اختياري - حدود دنيا وعليا)
        $query->when($request->filled('min_age'), function ($q) use ($request) {
            $q->where('age', '>=', $request->min_age);
        });

        $query->when($request->filled('max_age'), function ($q) use ($request) {
            $q->where('age', '<=', $request->max_age);
        });

        // فلترة الوزن (اختياري - حدود دنيا وعليا)
        $query->when($request->filled('min_weight'), function ($q) use ($request) {
            $q->where('weight', '>=', $request->min_weight);
        });

        $query->when($request->filled('max_weight'), function ($q) use ($request) {
            $q->where('weight', '<=', $request->max_weight);
        });

        // تنفيذ الاستعلام مع الترقيم بـ 12 عنصر في الصفحة
        $animals = $query->latest()->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $animals
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
        $animal->load(['photos', 'vet' , 'medicalConditions', 'behavioralAttributes', 'vaccinations']);

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
