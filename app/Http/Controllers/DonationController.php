<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'             => 'required|numeric|min:500',
            'gateway_type'       => 'required|in:al_haram,al_fouad,syriatel_cash,mtn_cash',
            'transaction_number' => 'required|string|max:100|digits:12',
            'receipt_image'      => 'required|image|mimes:jpeg,png,jpg|max:10240', 
            'is_anonymous'       => 'nullable|boolean', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }
        $exists = Donation::where('transaction_number', $request->transaction_number)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'رقم المعاملة المالية مستخدم مسبقاً، يرجى التحقق.'
            ], 400);
        }

        try {
            if ($request->hasFile('receipt_image')) {
                $path = $request->file('receipt_image')->store('donation_receipts', 'public');
                $receiptUrl = Storage::url($path);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء رفع صورة الإشعار، يرجى المحاولة مجدداً.'
                ], 400);
            }
            $userId = Auth::check() ? Auth::id() : null;
            $donation = Donation::create([
                'user_id'             => $userId,
                'amount'              => $request->amount,
                'gateway_type'        => $request->gateway_type,
                'transaction_number'  => $request->transaction_number,
                'receipt_image_path'  => $receiptUrl,
                'status'              => 'pending', // الحالة الافتراضية قيد التحقق اليدوي
                'is_anonymous'        => $request->has('is_anonymous') ? (bool)$request->is_anonymous : false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم رفع بيانات وإشعار التحويل بنجاح، طلبك الآن قيد التحقق والمطابقة اليدوية من قبل إدارة المنصة.',
                'data'    => $donation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع أثناء معالجة طلب التبرع المالي.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getPendingDonations()
    {
        $pendingDonations = Donation::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $pendingDonations
        ], 200);
    }

    public function approveDonation($id)
    {
        $donation = Donation::find($id);

        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'طلب التبرع غير موجود.'], 404);
        }

        if ($donation->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'تمت معالجة هذا الطلب مسبقاً.'], 400);
        }

        $donation->update([
            'status' => 'verified'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تأكيد استلام الحوالة بنجاح، وتحويل حالة التبرع إلى معتمد وموثق.',
            'data'    => $donation
        ], 200);
    }

    public function rejectDonation(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);

        $donation = Donation::find($id);

        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'طلب التبرع غير موجود.'], 404);
        }

        if ($donation->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'تمت معالجة هذا الطلب مسبقاً.'], 400);
        }

        $donation->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض طلب التبرع المالي وتسجيل سبب الرفض بنجاح.',
            'data'    => $donation
        ], 200);
    }
}