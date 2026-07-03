<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalConditionRequest;
use App\Http\Requests\UpdateMedicalConditionRequest;
use App\Models\AnimalMedicalCondition;
use Illuminate\Http\Request;

class AnimalMedicalConditionController extends Controller
{
    /**
     * عرض كل الحالات الطبية لحيوان معين
     */
    public function index($animal_id)
    {
        $conditions = AnimalMedicalCondition::where('animal_id', $animal_id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conditions
        ]);
    }

    /**
     * إضافة حالة طبية جديدة
     */
    public function store(StoreMedicalConditionRequest $request, $animal_id)
    {
        $condition = AnimalMedicalCondition::create([
            'animal_id'   => $animal_id,
            'condition'   => $request->condition,
            'treatment'   => $request->treatment,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'notes'       => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الحالة الطبية بنجاح',
            'data'    => $condition
        ], 201);
    }

    /**
     * عرض حالة طبية واحدة
     */
    public function show($id)
    {
        $condition = AnimalMedicalCondition::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $condition
        ]);
    }

    /**
     * تعديل حالة طبية
     */
    public function update(UpdateMedicalConditionRequest $request, $id)
    {
        $condition = AnimalMedicalCondition::findOrFail($id);
        $condition->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحالة الطبية بنجاح',
            'data'    => $condition
        ]);
    }

    /**
     * حذف حالة طبية
     */
    public function destroy($id)
    {
        $condition = AnimalMedicalCondition::findOrFail($id);
        $condition->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الحالة الطبية بنجاح'
        ]);
    }
}
