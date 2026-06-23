<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\UserMatchingPreference;
use App\Models\Sponsorship;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdoptionApplicationController extends Controller
{
 
    public function store(Request $request)
    {
        // 1. التحقق من البيانات القادمة من الطلب (Request Validation)
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

        // 2. التحقق من حالة توفر الحيوان
        $animal = Animal::findOrFail($request->animal_id);

        if ($animal->availability_status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحيوان غير متاح للتبني حالياً.'
            ], 422);
        }

        // 3. تجميع تفاصيل الاستمارة في نص منسق لحقنه في الحقل الإجباري بقاعدة البيانات
        $otherPetsText = $request->has_other_pets ? $request->other_pets_info : 'لا يوجد';
        $gardenText = $request->has_garden ? 'نعم' : 'لا';
        $childrenText = $request->children_under_10 ? 'نعم' : 'لا';

        $applicationDetails = "--- استمارة طلب التبني التفصيلية ---\n" .
                              "• سبب رغبة التبني: " . $request->reason_for_adoption . "\n" .
                              "• هل يوجد حيوانات أخرى؟ " . ($request->has_other_pets ? 'نعم' : 'لا') . " (تفاصيل: " . $otherPetsText . ")\n" .
                              "• نوع السكن: " . $request->housing_type . "\n" .
                              "• هل يحتوي السكن على حديقة؟ " . $gardenText . "\n" .
                              "• عدد أفراد العائلة: " . $request->family_members_count . "\n" .
                              "• هل يوجد أطفال تحت سن الـ 10 سنوات؟ " . $childrenText . "\n" .
                              "• طبيعة وجدول العمل: " . $request->work_schedule . "\n" .
                              "• الخبرة السابقة مع الحيوانات: " . $request->experience_with_animals . "\n" .
                              "• جهة اتصال الطوارئ: " . $request->emergency_contact_name . " (" . $request->emergency_contact_phone . ")";

        // 4. إدخال السجل بنجاح مع تخطي خطأ الـ General error: 1364
        $application = AdoptionApplication::create([
            'user_id'             => $request->user()->id,
            'animal_id'           => $request->animal_id,
            'application_details' => $applicationDetails, // الحقل الذي كان يسبب المشكلة تم ملؤه هنا
            'status'              => 'pending',
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

        DB::beginTransaction();

        try {
            // تم إزالة حقل approved_by لتجنب خطأ Column not found تماماً
            $application->update([
                'status'       => 'approved',
                'approved_at'  => now(),
            ]);

            $animal = $application->animal;
            $animal->update(['availability_status' => 'adopted']);

            $activeSponsorship = Sponsorship::where('animal_id', $animal->id)
                ->where('status', 'active')
                ->first();

            $sponsorshipMessage = "";
            if ($activeSponsorship) {
                $activeSponsorship->update([
                    'status' => 'cancelled',
                    'notes' => ($activeSponsorship->notes ? $activeSponsorship->notes . "\n" : "") . 
                               "[نظام أتمتة الـ SRS]: تم تحرير الحيوان من الكفالة بنجاح بسبب انتقاله إلى منزل دائم وتبنيه تبنياً كاملاً من قبل مستخدم آخر بتاريخ " . now()->toDateString() . "."
                ]);

                $sponsorshipMessage = " وتم تحرير الحيوان من الكفالة النشطة وتنبيه الكفيل بنجاح.";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم قبول طلب التبني بنجاح' . $sponsorshipMessage,
                'data'    => $application->load('animal')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب وتحرير الحيوان.',
                'error'   => $e->getMessage()
            ], 500);
        }
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

        if ($newStatus === 'approved') {
            return $this->approve($request, $application);
        }

        $application->update([
            'status'       => $newStatus,
            'approved_at'  => now(),
            'approved_by'  => $request->user()->id,
        ]);

        if ($newStatus === 'in_trial') {
            $application->animal()->update(['availability_status' => 'under_trial']); // أو حسب تدوين الـ enum لديكِ
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة الطلب بنجاح.',
            'data'    => $application
        ]);
    }

}
