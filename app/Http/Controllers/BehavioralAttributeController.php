<?php

namespace App\Http\Controllers;

use App\Models\BehavioralAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Animal;

class BehavioralAttributeController extends Controller
{
    /**
     * إضافة صفة سلوكية لحيوان
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_id'      => 'required|exists:animals,id',
            'attribute_name' => 'required|string|max:255',
            'intensity'      => 'required|in:low,medium,high',
            'description'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $attribute = BehavioralAttribute::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Behavioral attribute added successfully',
            'data'    => $attribute
        ], 201);
    }

    /**
     * عرض الصفات السلوكية لحيوان
     */
    public function showByAnimal($animal_id)
   {
    $features = BehavioralAttribute::where('animal_id', $animal_id)
        ->pluck('attribute_name');

    return response()->json([
        'success' => true,
        'features' => $features
    ]);
   }
}
