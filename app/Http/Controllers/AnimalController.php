<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AnimalController extends Controller
{
    /**
     * عرض قائمة الحيوانات مع الفلترة اللحظية
     */
    public function index(Request $request)
    {
        $query = Animal::with(['photos', 'vet']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->has('status')) {
            $query->where('availability_status', $request->status);
        }
        if ($request->has('urgent')) {
            $query->where('is_urgent', true);
        }

        $animals = $query->latest()->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $animals
        ]);
    }

    /**
     * إضافة حيوان جديد للنظام (يدوياً من الأدمن أو النظام)
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
            'message' => 'تم إضافة الحيوان إلى السجلات بنجاح.',
            'data'    => $animal->load('photos')
        ], 201);
    }

    /**
     * عرض تفاصيل حيوان محدد
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
     * تحديث بيانات الحيوان
     */
    public function update(Request $request, Animal $animal)
    {
        $validator = Validator::make($request->all(), [
            'type'                => 'in:dog,cat,bird,rabbit,other',
            'name'                => 'nullable|string|max:100',
            'age'                 => 'nullable|integer|min:0',
            'size'                => 'nullable|in:small,medium,large',
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $animal->update($request->all());

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

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الحيوان بنجاح.',
            'data'    => $animal->load('photos')
        ]);
    }

    /**
     * حذف الحيوان نهائياً وحذف صوره من السيرفر
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
            'message' => 'تم حذف سجل الحيوان وصوره بنجاح.'
        ]);
    }

    /**
     * حذف صورة فردية للحيوان
     */
    public function deletePhoto(AnimalPhoto $photo)
    {
        $relativePath = str_replace('/storage/', '', $photo->photo_url);
        Storage::disk('public')->delete($relativePath);
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصورة بنجاح.'
        ]);
    }
}