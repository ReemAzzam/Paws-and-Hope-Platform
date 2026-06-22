<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RescueConsultation;
use App\Models\RescueReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Veterinarian;
use Illuminate\Support\Facades\Auth;

class RescueConsultationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rescue_report_id' => 'required|exists:rescue_reports,id',
            'question'         => 'required|string|min:10',
        ]);

        $report = RescueReport::find($request->rescue_report_id);
        $user = auth()->user();
        $volunteer = $user->volunteer;

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا تمتلك ملف متطوع نشط لنظام الإنقاذ.'
            ], 403);
        }

        if ($report->volunteer_id !== $volunteer->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك طلب استشارة طبيّة لبلاغ لست مسؤولاً عنه ميدانياً.'
            ], 403);
        }

        $consultation = RescueConsultation::create([
            'rescue_report_id' => $report->id,
            'volunteer_id'     => $volunteer->id,
            'question'         => $request->question,
            'status'           => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الاستشارة العاجلة بنجاح، وجاري تنبيه الأطباء المناوبين.',
            'data'    => $consultation->load('rescueReport')
        ], 201);
    }

    public function answer(Request $request, $id)
    {
        $request->validate([
            'medical_advice' => 'required|string|min:10',
        ]);

        $consultation = RescueConsultation::find($id);

        if (!$consultation) {
            return response()->json([
                'success' => false,
                'message' => 'الاستشارة الطبية غير موجودة.'
            ], 404);
        }

        if ($consultation->status === 'answered') {
            return response()->json([
                'success' => false,
                'message' => 'تمت الإجابة على هذه الاستشارة مسبقاً من قبل طبيب آخر.'
            ], 400);
        }

        $user = auth()->user();
        $vet = $user->veterinarian; // بافتراض وجود علاقة veterinarian في موديل User لملف الطبيب

        if (!$vet || !$vet->is_approved) {
            return response()->json(['success' => false, 'message' => 'عذراً، يجب أن يكون حسابك الطبي معتمداً من قبل الإدارة لتتمكن من الإجابة.'], 403);
        }

        $consultation->update([
            'veterinarian_id' => $vet->id,
            'medical_advice'  => $request->medical_advice,
            'status'          => 'answered'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التوجيهات الطبية بنجاح إلى المتطوع في الميدان.',
            'data'    => $consultation->load(['volunteer.user', 'veterinarian'])
        ], 200);
    }

    public function getPendingConsultations(Request $request)
    {
        $user = Auth::user();

        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();
        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، هذا المسار مخصص للأطباء البيطريين المعتمدين فقط.'
            ], 403);
        }

        $consultations = RescueConsultation::with('rescueReport')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc') // الأقدم أولاً لسرعة الاستجابة
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الاستشارات الطبية المعلقة بنجاح.',
            'count' => $consultations->count(),
            'data' => $consultations
        ], 200);
    }

    public function getReportConsultations($reportId)
    {
        $report = RescueReport::find($reportId);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، البلاغ المطلوب غير موجود.'
            ], 404);
        }

        $consultations = RescueConsultation::with('veterinarian.user')
            ->where('rescue_report_id', $reportId)
            ->orderBy('created_at', 'desc') 
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تاريخ الاستشارات الطبية للبلاغ بنجاح.',
            'count' => $consultations->count(),
            'data' => $consultations
        ], 200);
    }
}