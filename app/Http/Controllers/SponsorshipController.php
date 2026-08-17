<?php

namespace App\Http\Controllers;

use App\Models\Sponsorship;
use App\Models\SponsorshipPayment;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Events\SendNotificationEvent;
use App\Support\NotificationTemplates;

class SponsorshipController extends Controller
{
public function requestSponsorship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_id'      => 'required|exists:animals,id',
            'monthly_amount' => 'required|numeric|min:0',
            'currency'       => 'required|in:SYP,USD',
            'notes'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $animal = Animal::find($request->animal_id);

        if ($animal->availability_status === 'adopted') {
            return response()->json(['message' => 'This animal has already been adopted.'], 400);
        }

        if ($animal->sponsorships()->whereIn('status', ['active', 'pending'])->exists()) {
            return response()->json(['message' => 'This animal is currently sponsored or has a pending sponsorship request.'], 400);
        }

        DB::beginTransaction();
        try {
            $sponsorship = Sponsorship::create([
                'user_id'          => Auth::id(),
                'animal_id'        => $request->animal_id,
                'monthly_amount'   => $request->monthly_amount,
                'currency'         => $request->currency,
                'status'           => 'pending',
                'payment_due_date' => Carbon::now()->addDays(5),
                'notes'            => $request->notes,
            ]);

            // إرسال إشعار للأدمن
           /* $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'SuperAdmin']);
            })->get();

            foreach ($admins as $admin) {
                $notification = NotificationTemplates::newSponsorshipRequest(Auth::user()->full_name, $animal->name);
                event(new SendNotificationEvent($admin, $notification['title'], $notification['body'], $notification['data']));
            }*/

            DB::commit();

            return response()->json([
                'message'     => 'Sponsorship request created. You have 5 days to complete the first payment before it expires.',
                'sponsorship' => $sponsorship
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

    // 2. رفع إيصال دفع
    public function renewPayment(Request $request, $sponsorshipId)
    {
        $sponsorship = Sponsorship::findOrFail($sponsorshipId);

        if ($sponsorship->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (in_array($sponsorship->status, ['cancelled', 'paused'])) {
            return response()->json(['message' => 'Cannot upload payment for a cancelled or paused sponsorship.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount'             => 'required|numeric|min:0',
            'currency'           => 'required|in:SYP,USD',
            'payment_method'     => 'required|string',
            'transaction_number' => 'required|string|unique:sponsorship_payments,transaction_number|max:12',
            'receipt_image'      => 'required|image|mimes:jpeg,png,jpg,avif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');

            $payment = SponsorshipPayment::create([
                'sponsorship_id'      => $sponsorship->id,
                'amount'              => $request->amount,
                'currency'            => $request->currency,
                'payment_method'      => $request->payment_method,
                'transaction_number'  => $request->transaction_number,
                'receipt_image_url'   => $receiptPath,
                'verification_status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Payment receipt uploaded successfully.',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

    // 3. توقيف الكفالة يدوياً من الكفيل
    public function pauseSponsorship($id)
    {
        $sponsorship = Sponsorship::findOrFail($id);

        if ($sponsorship->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($sponsorship->status === 'paused') {
            return response()->json(['message' => 'Sponsorship is already paused.'], 400);
        }

        if ($sponsorship->status === 'cancelled') {
            return response()->json(['message' => 'Cannot pause a cancelled sponsorship.'], 400);
        }

        $sponsorship->update(['status' => 'paused']);

        return response()->json(['success' => true, 'message' => 'Sponsorship paused successfully.'], 200);
    }

    // 4. مراجعة الدفعة من الأدمن
public function verifyPayment(Request $request, $paymentId)
{
    $validator = Validator::make($request->all(), [
        'status'           => 'required|in:verified,rejected',
        'rejection_reason' => 'required_if:status,rejected|string|nullable',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $payment = SponsorshipPayment::findOrFail($paymentId);

    // Prevent modification if already verified or rejected
    if ($payment->verification_status === 'verified') {
        return response()->json([
            'success' => false,
            'message' => 'This payment has already been verified and cannot be modified or rejected.'
        ], 400);
    }

    if ($payment->verification_status === 'rejected') {
        return response()->json([
            'success' => false,
            'message' => 'This payment has already been rejected and cannot be verified.'
        ], 400);
    }

    $sponsorship = $payment->sponsorship;

    DB::beginTransaction();
    try {
        if ($request->status === 'verified') {
            // 1. Update payment status
            $payment->update([
                'verification_status' => 'verified',
                'verified_by'         => Auth::id(),
                'verified_at'         => now(),
            ]);

            // 2. Update sponsorship status and due dates
            $sponsorship->update([
                'status'           => 'active',
                'start_date'       => $sponsorship->start_date ?? now()->toDateString(),
                'next_payment_due' => Carbon::now()->addMonth()->toDateString(),
                'payment_due_date' => null, // Clear initial temporary deadline
            ]);

            // 3. Update animal availability status to 'sponsored'
            if ($sponsorship->animal) {
                $sponsorship->animal->update([
                    'availability_status' => 'sponsored'
                ]);
            }

        } else {
            // Rejection logic
            $payment->update([
                'verification_status' => 'rejected',
                'rejection_reason'    => $request->rejection_reason,
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully.'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating payment status.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    public function mySponsorships()
    {
        $sponsorships = Sponsorship::where('user_id', Auth::id())
            ->with([
                'animal.photos',
                'animal.updates' => function ($query) {
                    $query->latest();
                },
                'payments' => function ($query) {
                    $query->latest();
                }
            ])
            ->latest()
            ->get();

        $sponsorships->each(function ($sponsorship) {
            if ($sponsorship->animal && $sponsorship->animal->availability_status === 'adopted') {
                $sponsorship->adoption_message = "خبر سعيد! الحيوان " . $sponsorship->animal->name . " الذي كنت تكفله تم تبنيه بنجاح ووجد عائلة دافئة تحبه. شكرًا لك على حبك ودعمك الجميل خلال رحلته!";
            } else {
                $sponsorship->adoption_message = null;
            }

            // ضبط مسارات الإيصالات
            if ($sponsorship->payments) {
                $sponsorship->payments->each(function ($payment) {
                    if ($payment->receipt_image_url && !filter_var($payment->receipt_image_url, FILTER_VALIDATE_URL)) {
                        $path = ltrim($payment->receipt_image_url, '/');
                        $path = preg_replace('#^storage/#', '', $path);
                        $payment->receipt_image_url = asset('storage/' . $path);
                    }
                });
            }
        });

        return response()->json([
            'success'      => true,
            'message'      => 'Sponsorship dashboard data retrieved successfully.',
            'sponsorships' => $sponsorships
        ], 200);
    }


    public function availableAnimalsForSponsorship(Request $request)
    {
        $request->validate([
            'type'     => 'nullable|string|in:dogs,cats,other',
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
            'data'    => $animals->items(),
            'meta'    => [
                'current_page' => $animals->currentPage(),
                'last_page'    => $animals->lastPage(),
                'per_page'     => $animals->perPage(),
                'total'        => $animals->total(),
            ]
        ], 200);
    }

    public function index(Request $request)
    {
        if (!Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $query = Sponsorship::with(['sponsor:id,full_name,email', 'animal:id,name', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sponsorships = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $sponsorships
        ], 200);
    }

    public function show($id)
    {
        $sponsorship = Sponsorship::with(['sponsor', 'animal', 'payments.verifiedBy'])->findOrFail($id);

        if ($sponsorship->user_id !== Auth::id() && !Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'You are not authorized to view this data.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $sponsorship
        ], 200);
    }

    public function search(Request $request)
    {
        if (!Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $request->validate([
            'status' => 'nullable|string|in:all,pending,active,cancelled,paused',
            'search' => 'nullable|string|max:255',
        ]);

        $query = Sponsorship::with([
            'sponsor:id,full_name,email',
            'animal:id,name',
            'payments' => function ($q) {
                $q->latest();
            }
        ]);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('sponsor', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('full_name', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('animal', function ($animalQuery) use ($searchTerm) {
                    $animalQuery->where('name', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('payments', function ($paymentQuery) use ($searchTerm) {
                    $paymentQuery->where('transaction_number', 'like', "%{$searchTerm}%");
                });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $sponsorships = $query->latest()->get();

        $formattedData = $sponsorships->map(function ($sponsorship) {
            $lastPayment = $sponsorship->payments->first();

            return [
                'id'                   => $sponsorship->id,
                'sponsor_name'         => $sponsorship->sponsor ? $sponsorship->sponsor->full_name : null,
                'sponsor_email'        => $sponsorship->sponsor ? $sponsorship->sponsor->email : null,
                'animal_name'          => $sponsorship->animal ? $sponsorship->animal->name : null,
                'amount_with_currency' => $sponsorship->monthly_amount . ' ' . $sponsorship->currency,
                'payment_method'       => $lastPayment ? $lastPayment->payment_method : null,
                'transaction_number'   => $lastPayment ? $lastPayment->transaction_number : null,
                'status'               => $sponsorship->status,
                'created_at'           => $sponsorship->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Sponsorships search results retrieved successfully.',
            'data'    => $formattedData,
            'total'   => $formattedData->count(),
        ], 200);
    }
}