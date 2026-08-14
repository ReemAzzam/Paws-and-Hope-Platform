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
        $animals->getCollection()->each(function ($animal) {
       // $this->formatAnimalPhotos($animal);
        });

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
            'availability_status' => 'in:available,pending,adopted,sponsored,under_treatment',
            'is_urgent'           => 'boolean',
            'is_vaccinated'       => 'boolean',
            'is_neutered'         => 'boolean',
            'photos.*'            => 'image|mimes:jpeg,png,jpg,gif,avif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('photos');

        $data['age_recorded_at'] = $request->filled('age')
            ? now()->toDateString()
            : null;

        $animal = Animal::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('animals/' . $animal->id, 'public');

                AnimalPhoto::create([
                    'animal_id'    => $animal->id,
                    // 'photo_url'    => Storage::url($path),
                    'photo_url' => $path,
                    'is_main'      => $index === 0,
                    'order_number' => $index,
                ]);
            }
        }
            $animal->load('photos');

          //  $this->formatAnimalPhotos($animal);

            return response()->json([
                'success' => true,
                'message' => 'Animal profile successfully added to records.',
                'data'    => $animal
            ], 201);
    }

    /**
     * Display the specified animal details.
     */
    public function show($id)
    {
        $animal = Animal::findOrFail($id);

        $animal->load([
            'photos',
            'vet',
            'medicalConditions',
            'behavioralAttributes',
            'vaccinations'
            ]);

       // $this->formatAnimalPhotos($animal);

        return response()->json([
            'success' => true,
            'attributes' => $animal->getAttributes(),
            'data' => $animal
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

        // الطبيب المسؤول فقط يستطيع التعديل
        if ($animal->vet_id !== $currentVetId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You are not the veterinarian responsible for this animal.'
            ], 403);
        }
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

       $updateData = $request->except([
            'photos',
            'health_update_title',
            'health_update_note',
        ]);

        if (
            $request->filled('age') &&
            (int) $request->age !== (int) $animal->getRawOriginal('age')
        ) {
            $updateData['age_recorded_at'] = now();
        }


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
                        // 'photo_url'    => Storage::url($path),
                        'photo_url' => $path,
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

            $animal->load('photos');

         //   $this->formatAnimalPhotos($animal);

            return response()->json([
                'success' => true,
                'message' => 'Animal details updated successfully. Health report posted to sponsor timeline.',
                'data'    => $animal
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

    private function formatAnimalPhotos($animal)
{
    if ($animal->relationLoaded('photos')) {
        $animal->photos->each(function ($photo) {

            if ($photo->photo_url) {

                // إذا كانت الصورة مخزنة كـ:
                // /storage/animals/1/photo.jpg
                $path = ltrim($photo->photo_url, '/');

                // إذا كانت أصلًا URL كامل
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    $photo->photo_url = $path;
                } else {
                    $photo->photo_url = asset($path);
                }
            }
        });
    }

    return $animal;
}
}
