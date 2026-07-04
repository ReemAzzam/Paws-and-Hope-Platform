<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransparencyDashboardController extends Controller
{
    public function getFinancialSummary()
    {
        try {
            $totalDonations = Donation::where('status', 'verified')->sum('amount');

            $totalExpenses = Expense::sum('amount');

            $currentBalance = $totalDonations - $totalExpenses;

            $latestDonations = Donation::with('user:id,full_name')
                ->where('status', 'verified')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($donation) {
                    return [
                        'id'           => $donation->id,
                        'amount'       => $donation->amount,
                        'gateway_type' => $donation->gateway_type,
                        'created_at'   => $donation->created_at,
                        'donor_name'   => $donation->is_anonymous ? 'Anonymous Donor' : ($donation->user ? $donation->user->full_name : 'Generous Donor'),
                    ];
                });

            $latestExpenses = Expense::orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'summary' => [
                        'total_donations' => $totalDonations,
                        'total_expenses'  => $totalExpenses,
                        'current_balance' => $currentBalance,
                    ],
                    'latest_donations' => $latestDonations,
                    'latest_expenses'  => $latestExpenses
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while loading financial transparency dashboard data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getFinancialReportData()
    {
        try {
            $totalDonations = Donation::where('status', 'verified')->sum('amount');
            $totalExpenses = Expense::sum('amount');
            $currentBalance = $totalDonations - $totalExpenses;

            $latestDonations = Donation::with('user:id,full_name')
                ->where('status', 'verified')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($donation) {
                    return [
                        'id'                 => $donation->id,
                        'amount'             => $donation->amount,
                        'gateway_type'       => $donation->gateway_type,
                        'transaction_number' => $donation->transaction_number,
                        'donor_name'         => $donation->is_anonymous
                            ? ($donation->user ? $donation->user->full_name . ' (Anonymous Donor)' : 'Anonymous Donor')
                            : ($donation->user ? $donation->user->full_name : 'Generous Donor'),
                        'created_at'         => $donation->created_at->format('Y-m-d H:i'),
                    ];
                });

            $latestExpenses = Expense::orderBy('created_at', 'desc')
                ->get()
                ->map(function ($expense) {
                    return [
                        'id'            => $expense->id,
                        'title'         => $expense->title,
                        'category'      => $expense->category,
                        'amount'        => $expense->amount,
                        'invoice_image' => $expense->invoice_image_path,
                        'created_at'    => $expense->created_at->format('Y-m-d H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'report_metadata' => [
                    'organization' => 'Paws & Hope Animal Rescue and Care Platform',
                    'generated_at' => now()->format('Y-m-d H:i'),
                    'currency'     => 'SYP'
                ],
                'summary' => [
                    'total_donations' => $totalDonations,
                    'total_expenses'  => $totalExpenses,
                    'current_balance' => $currentBalance,
                ],
                'detailed_donations' => $latestDonations,
                'detailed_expenses'  => $latestExpenses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while preparing financial report data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getPublicExpenses()
    {
        try {
            $expenses = Expense::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data'    => $expenses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving public expenses log.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getMyDonations()
    {
        try {
            $myDonations = Donation::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $myDonations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving your personal donations history.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}