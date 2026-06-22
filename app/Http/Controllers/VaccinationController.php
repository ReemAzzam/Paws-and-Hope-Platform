<?php

namespace App\Http\Controllers;

use App\Models\Vaccination;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VaccinationController extends Controller
{
    /**
     * إضافة لقاح جديد لحيوان
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_id'        => 'required|exists:animals,id',
            'vaccine_name'     => 'required|string|max:255',
            'vaccine_type'     => 'nullable|string|max:255',
            'vaccination_date' => 'required|date',
            'notes'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $vaccination = Vaccination::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Vaccination added successfully',
            'data'    => $vaccination
        ], 201);
    }

    /**
     * عرض لقاحات حيوان معين
     */
    public function showByAnimal($animal_id)
    {
        $vaccinations = Vaccination::where('animal_id', $animal_id)->get();

        return response()->json([
            'success' => true,
            'data'    => $vaccinations
        ]);
    }
}
