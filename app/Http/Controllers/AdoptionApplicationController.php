<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\UserMatchingPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdoptionApplicationController extends Controller
{
    /**
     * تقديم طلب تبني جديد (من المستخدم العادي)
     */
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

        if ($animal->availability_status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحيوان غير متاح للتبني حالياً.'
            ], 422);
        }

        $application = AdoptionApplication::create([
            'user_id'                  => $request->user()->id,
            'animal_id'                => $request->animal_id,
            'reason_for_adoption'      => $request->reason_for_adoption,
            'has_other_pets'           => $request->has_other_pets,
            'other_pets_info'          => $request->other_pets_info,
            'housing_type'             => $request->housing_type,
            'has_garden'               => $request->has_garden,
            'family_members_count'     => $request->family_members_count,
            'children_under_10'        => $request->children_under_10,
            'work_schedule'            => $request->work_schedule,
            'experience_with_animals'  => $request->experience_with_animals,
            'commitment_declaration'   => true,
            'emergency_contact_name'   => $request->emergency_contact_name,
            'emergency_contact_phone'  => $request->emergency_contact_phone,
            'status'                   => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم طلب التبني بنجاح. سيتم مراجعته من قبل الإدارة.',
            'data'    => $application->load('animal')
        ], 201);
    }

    /**
     * طلبات التبني الخاصة بالمستخدم
     */
    public function myApplications(Request $request)
    {
        $applications = AdoptionApplication::where('user_id', $request->user()->id)
            ->with(['animal:id,name,type,health_status'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $applications
        ]);
    }

    /**
     * عرض كل طلبات التبني (للأدمن) - مع فلاتر
     */
    public function index(Request $request)
    {
        $query = AdoptionApplication::with(['user:id,full_name,email', 'animal:id,name,type']);

        // فلاتر
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('animal_id')) {
            $query->where('animal_id', $request->animal_id);
        }

        $applications = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $applications
        ]);
    }

    /**
     * عرض طلب تبني معين (للأدمن)
     */
    public function show(AdoptionApplication $application)
    {
        $application->load(['user', 'animal', 'approvedBy']);
        return response()->json([
            'success' => true,
            'data'    => $application
        ]);
    }

    /**
     * قبول طلب التبني (للأدمن فقط)
     */
   public function approve(Request $request, AdoptionApplication $application)
    {
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن قبول هذا الطلب في حالته الحالية.'
            ], 422);
        }

        $application->update([
            'status'       => 'approved',
            'approved_at'  => now(),
            'approved_by'  => $request->user()->id,
        ]);

        $application->animal()->update(['availability_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'تم قبول طلب التبني بنجاح.',
            'data'    => $application
        ]);
    }

    /**
     * رفض طلب التبني (للأدمن فقط)
     */
  public function reject(Request $request, AdoptionApplication $application)
    {
        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن رفض هذا الطلب في حالته الحالية.'
            ], 422);
        }

        $application->update([
            'status'      => 'rejected',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض طلب التبني.',
            'data'    => $application
        ]);
    }

    /**
     * تعديل بيانات طلب تبني (للمستخدم العادي أو الأدمن)
     */
    public function update(Request $request, AdoptionApplication $application)
    {
        if ($application->user_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذا الطلب'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'لا يمكن تعديل طلب تمت معالجته'], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason_for_adoption'     => 'string|min:30',
            'has_other_pets'          => 'boolean',
            'other_pets_info'         => 'nullable|string',
            'housing_type'            => 'in:house,apartment,villa',
            'has_garden'              => 'boolean',
            'family_members_count'     => 'integer|min:1',
            'children_under_10'        => 'boolean',
            'work_schedule'            => 'string',
            'experience_with_animals'  => 'string|min:20',
            'commitment_declaration'   => 'accepted',
            'emergency_contact_name'   => 'string|max:255',
            'emergency_contact_phone'  => 'string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $application->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الطلب بنجاح',
            'data'    => $application
        ]);
    }

    /**
     * حذف طلب تبني
     */
    public function destroy(Request $request, AdoptionApplication $application)
    {
        if ($application->user_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف طلب التبني بنجاح'
        ]);
    }

    /**
     * تغيير حالة الطلب (للأدمن)
     */
    public function changeStatus(Request $request, AdoptionApplication $application)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,in_trial'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($application->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'لا يمكن تغيير حالة طلب تمت معالجته'], 422);
        }

        $newStatus = $request->status;

        $application->update([
            'status'       => $newStatus,
            'approved_at'  => now(),
            'approved_by'  => $request->user()->id,
        ]);

        if ($newStatus === 'approved') {
            $application->animal()->update(['availability_status' => 'pending']);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة الطلب بنجاح',
            'data'    => $application
        ]);
    }

}
