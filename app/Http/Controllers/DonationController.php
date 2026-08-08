<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Events\SendNotificationEvent;
use App\Support\NotificationTemplates;

class DonationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'             => 'required|numeric|min:1',
            'currency'           => 'required|in:SYP,USD',
            'gateway_type'       => 'required|in:al_haram,al_fouad,syriatel_cash,mtn_cash,western_union,paypal,gofundme,hand_delivery,external',
            'transaction_number' => 'nullable|string|max:100',
            'receipt_image'      => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'is_anonymous'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // التحقق من تكرار رقم العملية فقط إذا تم إرساله
        if ($request->filled('transaction_number')) {
            $exists = Donation::where('transaction_number', $request->transaction_number)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This transaction identifier has already been processed.'
                ], 400);
            }
        }

        try {
            $receiptUrl = null;
            if ($request->hasFile('receipt_image')) {
                $path = $request->file('receipt_image')->store('donation_receipts', 'public');
                $receiptUrl = Storage::url($path);
            }

            $userId = Auth::check() ? Auth::id() : null;

            $donation = Donation::create([
                'user_id'            => $userId,
                'amount'             => $request->amount,
                'currency'           => $request->currency,
                'gateway_type'       => $request->gateway_type,
                'transaction_number' => $request->transaction_number,
                'receipt_image_path' => $receiptUrl,
                'status'             => 'pending',
                'is_anonymous'       => $request->has('is_anonymous') ? (bool)$request->is_anonymous : false,
            ]);

            // إشعار الأدمن بالتبرع الجديد مع إضافة العملة
           // $admins = User::role(['admin', 'SuperAdmin'])->get();

            foreach ($admins as $admin) {
                $notification = NotificationTemplates::newDonation(
                    Auth::user()?->full_name ?? 'Anonymous User',
                    $donation->amount . ' ' . $donation->currency
                );

                event(new SendNotificationEvent(
                    $admin,
                    $notification['title'],
                    $notification['body'],
                    $notification['data']
                ));
            }

            return response()->json([
                'success' => true,
                'message' => 'Donation logged successfully. Status held at pending manual auditing verification.',
                'data'    => $donation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while compiling the gateway tracking trace.',
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
            return response()->json(['success' => false, 'message' => 'Donation ledger record not found.'], 404);
        }

        if ($donation->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This transaction record has already been processed.'], 400);
        }

        $donation->update([
            'status' => 'verified'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction statement verified successfully. Funds cleared and committed to dashboard tracking logs.',
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
            return response()->json(['success' => false, 'message' => 'Donation record not found.'], 404);
        }

        if ($donation->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This transaction has already been resolved.'], 400);
        }

        $donation->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donation record rejected successfully. Audit tracking metrics saved.',
            'data'    => $donation
        ], 200);
    }

    public function index(Request $request)
    {
        $statusInput = $request->query('status', $request->input('status'));
        $searchInput = $request->query('search', $request->input('search'));

        $validator = Validator::make([
            'status' => $statusInput,
            'search' => $searchInput,
        ], [
            'status' => 'nullable|in:all,pending,verified,rejected',
            'search' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $query = Donation::with(['user:id,full_name,email']);

        if (!empty($searchInput)) {
            $query->where(function ($q) use ($searchInput) {
                $q->where('transaction_number', 'like', "%{$searchInput}%")
                ->orWhereHas('user', function ($userQuery) use ($searchInput) {
                    $userQuery->where('full_name', 'like', "%{$searchInput}%");
                });
            });
        }
        
        if (!empty($statusInput) && $statusInput !== 'all') {
            $query->where('status', $statusInput);
        }

        $donations = $query->orderBy('created_at', 'desc')->get();

        $formattedData = $donations->map(function ($donation) {
            return [
                'id'                 => $donation->id,
                'donor_name'         => $donation->is_anonymous 
                                        ? 'Anonymous Donor' 
                                        : ($donation->user?->full_name ?? 'Guest Donor'),
                'email'              => $donation->is_anonymous 
                                        ? null 
                                        : ($donation->user?->email ?? null),
                'gateway_type'       => $donation->gateway_type,
                'transaction_number' => $donation->transaction_number ?? 'N/A',
                'amount'             => "{$donation->amount} {$donation->currency}",
                'status'             => $donation->status,
                'receipt_image_path' => $donation->receipt_image_path,
                'created_at'         => $donation->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ], 200);
    }
}