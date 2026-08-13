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
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Events\SendNotificationEvent;
use App\Support\NotificationTemplates;

class SponsorshipController extends Controller
{
    public function requestSponsorship(Request $request)
    {
        $allowedPaymentMethods = [
            'al_haram',
            'al_fouad',
            'syriatel_cash',
            'mtn_cash',
            'western_union',
            'paypal',
            'gofundme',
            'hand_delivery',
            'external'
        ];

        $validator = Validator::make($request->all(), [
            'animal_id'          => 'required|exists:animals,id',
            'monthly_amount'     => 'required|numeric|min:0',
            'currency'           => 'required|in:SYP,USD',
            'payment_method'     => ['required', 'string', Rule::in($allowedPaymentMethods)],
            'transaction_number' => 'required|string|unique:sponsorship_payments,transaction_number|digits:12',
            'receipt_image'      => 'required|image|mimes:jpeg,png,jpg|max:4096',
            'notes'              => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $animal = Animal::find($request->animal_id);
        if ($animal->sponsorships()->where('status', 'active')->exists()) {
            return response()->json(['message' => 'This animal is currently sponsored by another individual.'], 400);
        }

        DB::beginTransaction();
        try {
            $sponsorship = Sponsorship::create([
                'user_id'        => Auth::id(),
                'animal_id'      => $request->animal_id,
                'monthly_amount' => $request->monthly_amount,
                'currency'       => $request->currency,
                'status'         => 'pending',
                'notes'          => $request->notes,
            ]);

            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');

            SponsorshipPayment::create([
                'sponsorship_id'      => $sponsorship->id,
                'amount'              => $request->monthly_amount,
                'currency'            => $request->currency,
                'payment_method'      => $request->payment_method,
                'transaction_number'  => $request->transaction_number,
                'receipt_image_url'   => $receiptPath,
                'verification_status' => 'pending',
            ]);
            $admins = User::role(['admin', 'SuperAdmin'])->get();

            foreach ($admins as $admin) {
                $notification = NotificationTemplates::newSponsorshipRequest(
                    Auth::user()->full_name,
                    $animal->name
                );

                event(new SendNotificationEvent(
                    $admin,
                    $notification['title'],
                    $notification['body'],
                    $notification['data']
                ));
            }

            DB::commit();

            return response()->json([
                'message'     => 'Sponsorship request submitted successfully and is currently under administrative review.',
                'sponsorship' => $sponsorship->load('payments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing the request.', 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyPayment(Request $request, $paymentId)
    {
        if (!Auth::user()->hasRole('admin', 'api') && !Auth::user()->hasRole('SuperAdmin', 'api')) {
            return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status'           => 'required|in:verified,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = SponsorshipPayment::findOrFail($paymentId);
        $sponsorship = $payment->sponsorship;

        if ($payment->verification_status !== 'pending') {
            return response()->json(['message' => 'This payment has already been processed.'], 400);
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'verified') {
                $payment->update([
                    'verification_status' => 'verified',
                    'verified_by'         => Auth::id(),
                    'verified_at'         => now(),
                ]);

                $sponsorship->update([
                    'status'           => 'active',
                    'start_date'       => now()->toDateString(),
                    'next_payment_due' => Carbon::now()->addMonth()->toDateString(),
                ]);

            } else {
                $payment->update([
                    'verification_status' => 'rejected',
                    'rejection_reason'    => $request->rejection_reason,
                ]);

                $sponsorship->update([
                    'status' => 'cancelled'
                ]);
            }

            DB::commit();
            return response()->json([
                'message'     => $request->status === 'verified' ? 'Sponsorship has been successfully activated for the animal.' : 'Sponsorship receipt has been successfully rejected.',
                'sponsorship' => $sponsorship->load('payments')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing the operation.', 'error' => $e->getMessage()], 500);
        }
    }

    public function renewPayment(Request $request, $sponsorshipId)
    {
        $sponsorship = Sponsorship::findOrFail($sponsorshipId);

        if ($sponsorship->user_id !== Auth::id()) {
            return response()->json(['message' => 'You are not authorized to renew this sponsorship.'], 403);
        }

        $allowedPaymentMethods = [
            'al_haram',
            'al_fouad',
            'syriatel_cash',
            'mtn_cash',
            'western_union',
            'paypal',
            'gofundme',
            'hand_delivery',
            'external'
        ];

        $validator = Validator::make($request->all(), [
            'amount'             => 'required|numeric|min:0',
            'currency'           => 'required|in:SYP,USD',
            'payment_method'     => ['required', 'string', Rule::in($allowedPaymentMethods)], // 🟢 التحقق من الطرق المحددة
            'transaction_number' => 'required|string|unique:sponsorship_payments,transaction_number',
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

            $animal = $sponsorship->animal;
           $admins = User::whereHas('roles', function ($query) {
    $query->whereIn('name', ['admin', 'SuperAdmin']);
})->get();
            foreach ($admins as $admin) {
                $notification = NotificationTemplates::sponsorshipRenewal(
                    Auth::user()->full_name,
                    $animal->name
                );

                event(new SendNotificationEvent(
                    $admin,
                    $notification['title'],
                    $notification['body'],
                    $notification['data']
                ));
            }

            return response()->json([
                'message' => 'Renewal receipt uploaded successfully and is awaiting admin approval to extend the sponsorship.',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while processing the renewal payment.', 'error' => $e->getMessage()], 500);
        }
    }

 
        public function mySponsorships()
    {
        $sponsorships = Sponsorship::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with([
                'animal.photos',
                'animal.updates' => function ($query) {
                    $query->latest();
                },
                'payments' => function ($query) {
                    $query->latest();
                }
            ])
            ->get();

        $sponsorships->each(function ($sponsorship) {

            // AnimalPhoto model handles the animal photo URL
            // through the "url" accessor.

            // Normalize payment receipt URLs
            $sponsorship->payments->each(function ($payment) {

                if (!$payment->receipt_image_url) {
                    return;
                }

                if (!filter_var($payment->receipt_image_url, FILTER_VALIDATE_URL)) {
                    $path = ltrim($payment->receipt_image_url, '/');
                    $path = preg_replace('#^storage/#', '', $path);

                    $payment->receipt_image_url = asset('storage/' . $path);
                }
            });
        });

        return response()->json([
            'success'      => true,
            'message'      => 'Sponsorship dashboard data retrieved successfully.',
            'sponsorships' => $sponsorships
        ], 200);
    }
    //     public function mySponsorships()
    // {
    //     $sponsorships = Sponsorship::where('user_id', Auth::id())
    //         ->where('status', 'active')
    //         ->with([
    //             'animal.photos',
    //             'animal.updates' => function ($query) {
    //                 $query->latest();
    //             },
    //             'payments' => function ($query) {
    //                 $query->latest();
    //             }
    //         ])
    //         ->get();

    //     $sponsorships->each(function ($sponsorship) {

    //         if ($sponsorship->animal && $sponsorship->animal->photos) {

    //             $sponsorship->animal->photos->each(function ($photo) {

    //                 if ($photo->photo_url) {
    //                     $photo->photo_url = asset('storage/' . ltrim($photo->photo_url, '/'));
    //                 }

    //             });

    //         }

    //         // إذا كنت تريد أيضاً تحويل صور إيصالات الدفع إلى URL كامل
    //         if ($sponsorship->payments) {

    //             $sponsorship->payments->each(function ($payment) {

    //                 if ($payment->receipt_image_url) {

    //                     // إذا كانت القيمة أصلاً URL كامل لا نضيف storage مرة ثانية
    //                     if (!filter_var($payment->receipt_image_url, FILTER_VALIDATE_URL)) {
    //                         $payment->receipt_image_url = asset(
    //                             'storage/' . ltrim($payment->receipt_image_url, '/')
    //                         );
    //                     }

    //                 }

    //             });

    //         }
    //     });

    //     return response()->json([
    //         'success'      => true,
    //         'message'      => 'Sponsorship dashboard data retrieved successfully.',
    //         'sponsorships' => $sponsorships
    //     ], 200);
    // }

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

        $query = Sponsorship::with(['user:id,full_name,email', 'animal:id,name', 'payments']);

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
        $sponsorship = Sponsorship::with(['user', 'animal', 'payments.verifiedBy'])->findOrFail($id);

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
