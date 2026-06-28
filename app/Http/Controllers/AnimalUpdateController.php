<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AnimalUpdateController extends Controller
{
    public function store(Request $request, $animalId): JsonResponse
    {
        $animal = Animal::findOrFail($animalId);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:health,media,general',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov|max:15360', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $mediaUrl = null;

            if ($request->hasFile('media_file')) {
                $filePath = $request->file('media_file')->store('animal_updates', 'public');
                $mediaUrl = asset('storage/' . $filePath);
            }

            $update = AnimalUpdate::create([
                'animal_id' => $animal->id,
                'title' => $request->title,
                'content' => $request->content,
                'type' => $request->type,
                'media_url' => $mediaUrl,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exclusive timeline log successfully posted and linked to active sponsor tracks.',
                'update' => $update
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while compiling the updates trace.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}