<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransparencyDashboardController extends Controller
{
    /**
     * جلب الملخص المالي العام للمستخدمين المسجلين (لوحة الشفافية)
     */
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
                        'id' => $donation->id,
                        'amount' => $donation->amount,
                        'gateway_type' => $donation->gateway_type,
                        'created_at' => $donation->created_at,
                        // للمستخدمين العاديين: يبقى الاسم مخفياً تماماً إذا كان التبرع سرياً
                        'donor_name' => $donation->is_anonymous ? 'متبرع مجهول الهوية' : ($donation->user ? $donation->user->full_name : 'متبرع كريم'),
                    ];
                });

            $latestExpenses = Expense::orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
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
                'message' => 'حدث خطأ أثناء تحميل بيانات لوحة الشفافية المالية.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب التقارير المالية التفصيلية الشاملة بلوحة التحكم (SuperAdmin حصراً)
     * مخرجات JSON كاملة ليتولى الفرونت-إند تصديرها وتنسيقها وملفات الـ PDF بناءً عليها
     */
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
                        'id' => $donation->id,
                        'amount' => $donation->amount,
                        'gateway_type' => $donation->gateway_type,
                        'transaction_number' => $donation->transaction_number,
                        // التعديل: تظهر هوية المتبرع الحقيقية للأدمن مع وسم (متبرع سري) لتدقيق الحسابات بوضوح
                        'donor_name' => $donation->is_anonymous 
                            ? ($donation->user ? $donation->user->full_name . ' (متبرع سري)' : 'متبرع سري') 
                            : ($donation->user ? $donation->user->full_name : 'متبرع كريم'),
                        'created_at' => $donation->created_at->format('Y-m-d H:i'),
                    ];
                });

            $latestExpenses = Expense::orderBy('created_at', 'desc')
                ->get()
                ->map(function ($expense) {
                    return [
                        'id' => $expense->id,
                        'title' => $expense->title,
                        'category' => $expense->category,
                        'amount' => $expense->amount,
                        'invoice_image' => $expense->invoice_image_path,
                        'created_at' => $expense->created_at->format('Y-m-d H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'report_metadata' => [
                    'organization' => 'منصة Paws & Hope لإنقاذ ورعاية الحيوانات',
                    'generated_at' => now()->format('Y-m-d H:i'),
                    'currency'     => 'ل.س'
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
                'message' => 'حدث خطأ أثناء إعداد بيانات التقرير المالي.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب سجل المصروفات العامة للمستخدمين المسجلين
     */
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
                'message' => 'حدث خطأ أثناء جلب سجل المصروفات العامة.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب السجل الشخصي لتبرعات المستخدم المسجل الحالي
     */
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
                'message' => 'حدث خطأ أثناء جلب سجل تبرعاتك الشخصي.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}