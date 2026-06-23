<?php

namespace App\Http\Controllers;

use App\Models\Sponsorship;
use App\Models\SponsorshipPayment;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SponsorshipController extends Controller
{
    public function requestSponsorship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_id' => 'required|exists:animals,id',
            'monthly_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_number' => 'required|string|unique:sponsorship_payments,transaction_number|digits:12',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:4096', // حد أقصى 4 ميغا
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $animal = Animal::find($request->animal_id);
        if ($animal->sponsorships()->where('status', 'active')->exists()) {
            return response()->json(['message' => 'هذا الحيوان مكفول حالياً من قبل شخص آخر.'], 400);
        }

        DB::beginTransaction();
        try {
            $sponsorship = Sponsorship::create([
                'user_id' => Auth::id(),
                'animal_id' => $request->animal_id,
                'monthly_amount' => $request->monthly_amount,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
            $receiptUrl = asset('storage/' . $imagePath);

            SponsorshipPayment::create([
                'sponsorship_id' => $sponsorship->id,
                'amount' => $request->monthly_amount,
                'payment_method' => $request->payment_method,
                'transaction_number' => $request->transaction_number,
                'receipt_image_url' => $receiptUrl,
                'verification_status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم تقديم طلب الكفالة بنجاح، وهو قيد المراجعة الإدارية حالياً.',
                'sponsorship' => $sponsorship->load('payments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ أثناء معالجة الطلب.', 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyPayment(Request $request, $paymentId)
    {
        if (!Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'غير مصرح لك بالقيام بهذا الإجراء.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = SponsorshipPayment::findOrFail($paymentId);
        $sponsorship = $payment->sponsorship;

        if ($payment->verification_status !== 'pending') {
            return response()->json(['message' => 'تمت معالجة هذه الدفعة مسبقاً.'], 400);
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'verified') {
                $payment->update([
                    'verification_status' => 'verified',
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                ]);

                $sponsorship->update([
                    'status' => 'active',
                    'start_date' => now()->toDateString(),
                    'next_payment_due' => Carbon::now()->addMonth()->toDateString(),
                ]);

            } else {
                $payment->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ]);

                $sponsorship->update([
                    'status' => 'cancelled'
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => $request->status === 'verified' ? 'تم تفعيل الكفالة بنجاح للحيوان.' : 'تم رفض إيصال الكفالة بنجاح.',
                'sponsorship' => $sponsorship->load('payments')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ أثناء معالجة العملية.', 'error' => $e->getMessage()], 500);
        }
    }

    public function renewPayment(Request $request, $sponsorshipId)
    {
        $sponsorship = Sponsorship::findOrFail($sponsorshipId);

        if ($sponsorship->user_id !== Auth::id()) {
            return response()->json(['message' => 'غير مصرح لك بتجديد هذه الكفالة.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'transaction_number' => 'required|string|unique:sponsorship_payments,transaction_number',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
            $receiptUrl = asset('storage/' . $imagePath);

            $payment = SponsorshipPayment::create([
                'sponsorship_id' => $sponsorship->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'transaction_number' => $request->transaction_number,
                'receipt_image_url' => $receiptUrl,
                'verification_status' => 'pending',
            ]);

            return response()->json([
                'message' => 'تم رفع إيصال التجديد بنجاح، وبانتظار موافقة الإدارة لتمديد الكفالة.',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء معالجة دفعة التجديد.', 'error' => $e->getMessage()], 500);
        }
    }

    public function mySponsorships()
    {
        $sponsorships = Sponsorship::where('user_id', Auth::id())
            ->where('status', 'active') 
            ->with([
                'animal.photos', 
                'animal.updates' => function($query) {
                    $query->latest();
                }, 
                'payments' => function($query) {
                    $query->latest(); 
                }
            ])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Sponsorship dashboard data retrieved successfully.',
            'sponsorships' => $sponsorships
        ], 200);
    }

    public function availableAnimalsForSponsorship(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|in:dogs,cats,other',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        $query = Animal::query()
            ->whereIn('availability_status', ['available', 'under_treatment'])
            ->whereDoesntHave('sponsorships', function ($q) {
                $q->where('status', 'active');
            });

        if ($request->has('type')) {
            $type = $request->input('type');
            
            if ($type === 'other') {
                $query->whereNotIn('type', ['dog', 'cat']);
            } else {
                $singularType = rtrim($type, 's'); 
                $query->where('type', $singularType);
            }
        }

        $perPage = $request->input('per_page', 12);
        $animals = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Available animals retrieved successfully.',
            'data' => $animals->items(),
            'meta' => [
                'current_page' => $animals->currentPage(),
                'last_page' => $animals->lastPage(),
                'per_page' => $animals->perPage(),
                'total' => $animals->total(),
            ]
        ], 200);
    }

    /**
     * عرض جميع طلبات الكفالات (للأدمن فقط لمراجعتها)
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'غير مصرح لك بالقيام بهذا الإجراء.'], 403);
        }

        $query = Sponsorship::with(['user:id,full_name,email', 'animal:id,name', 'payments']);

        // فلترة الكفالات حسب الحالة (pending, active, cancelled)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sponsorships = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $sponsorships
        ], 200);
    }

    /**
     * عرض تفاصيل كفالة معينة مع سجل المدفوعات التابع لها
     */
    public function show($id)
    {
        $sponsorship = Sponsorship::with(['user', 'animal', 'payments.verifiedBy'])->findOrFail($id);

        // حماية: الكفيل نفسه أو الأدمن فقط من يستطيع العرض
        if ($sponsorship->user_id !== Auth::id() && !Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'غير مصرح لك باستعراض هذه البيانات.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $sponsorship
        ], 200);
    }
}

