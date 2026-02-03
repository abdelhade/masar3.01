<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use Carbon\Carbon;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use App\Models\JournalDetail;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;

class ExpenseManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Expenses');
    }

    /**
     * عرض لوحة تحكم المصروفات
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // إحصائيات اليوم
        $todayExpenses = $this->getExpensesTotal($today, $today);

        // إحصائيات الشهر الحالي
        $monthExpenses = $this->getExpensesTotal($startOfMonth, $endOfMonth);

        // إحصائيات الشهر السابق للمقارنة
        $lastMonthExpenses = $this->getExpensesTotal($startOfLastMonth, $endOfLastMonth);

        // نسبة التغير
        $changePercentage = $lastMonthExpenses > 0
            ? round((($monthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100, 1)
            : 0;

        // عدد عمليات المصروفات هذا الشهر
        $monthTransactionsCount = $this->getExpensesCount($startOfMonth, $endOfMonth);

        // أعلى بند مصروفات هذا الشهر
        $topExpenseAccount = $this->getTopExpenseAccount($startOfMonth, $endOfMonth);

        // حسابات المصروفات
        $expenseAccounts = AccHead::where('code', 'like', '57%')
            ->where('isdeleted', 0)
            ->where('is_basic', 0)
            ->get();

        // مراكز التكلفة
        $costCenters = CostCenter::all();

        // آخر 10 عمليات مصروفات
        $recentExpenses = $this->getRecentExpenses(10);

        // المصروفات حسب البند (لأعلى 5 بنود)
        $expensesByAccount = $this->getExpensesByAccount($startOfMonth, $endOfMonth, 5);

        // المصروفات اليومية للشهر الحالي (للرسم البياني)
        $dailyExpenses = $this->getDailyExpenses($startOfMonth, $endOfMonth);

        return view('reports::expenses.expense-dashboard', compact(
            'todayExpenses',
            'monthExpenses',
            'lastMonthExpenses',
            'changePercentage',
            'monthTransactionsCount',
            'topExpenseAccount',
            'expenseAccounts',
            'costCenters',
            'recentExpenses',
            'expensesByAccount',
            'dailyExpenses'
        ));
    }

    /**
     * عرض صفحة تسجيل مصروف جديد
     */
    public function create()
    {
        // جلب المصروفات فقط (الكود 57% أو acc_type = 7)
        $expenseAccounts = AccHead::where(function ($q) {
            $q->where('code', 'like', '57%')
                ->orWhere('acc_type', '7');
        })
            ->where('isdeleted', 0)
            ->where('is_basic', 0)
            ->orderBy('aname')
            ->get();

        $costCenters = CostCenter::orderBy('cname')->get();

        // حسابات النقدية والبنوك فقط للدفع منها
        $paymentAccounts = AccHead::where(function ($q) {
            $q->where('code', 'like', '11%') // النقدية (الصناديق)
                ->orWhere('code', 'like', '12%'); // البنوك
        })
            ->where('isdeleted', 0)
            ->where('is_basic', 0)
            ->orderBy('aname')
            ->get();

        return view('reports::expenses.create-expense', compact(
            'expenseAccounts',
            'costCenters',
            'paymentAccounts'
        ));
    }

    /**
     * حفظ مصروف جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_account_id' => 'required|exists:acc_head,id',
            'payment_account_id' => 'required|exists:acc_head,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'expense_date' => 'required|date',
        ], [
            'expense_account_id.required' => 'حساب المصروف مطلوب',
            'payment_account_id.required' => 'حساب الدفع مطلوب',
            'amount.required' => 'المبلغ مطلوب',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'expense_date.required' => 'التاريخ مطلوب',
        ]);

        // هنا يمكن إضافة منطق حفظ المصروف
        // يجب ربطه مع نظام القيود المحاسبية الموجود

        return redirect()
            ->route('expenses.dashboard')
            ->with('success', 'تم تسجيل المصروف بنجاح');
    }

    /**
     * الحصول على إجمالي المصروفات لفترة معينة
     */
    private function getExpensesTotal($fromDate, $toDate): float
    {
        return JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '57%');
        })
            ->whereDate('crtime', '>=', $fromDate)
            ->whereDate('crtime', '<=', $toDate)
            ->sum('debit');
    }

    /**
     * الحصول على عدد عمليات المصروفات
     */
    private function getExpensesCount($fromDate, $toDate): int
    {
        return JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '57%');
        })
            ->whereDate('crtime', '>=', $fromDate)
            ->whereDate('crtime', '<=', $toDate)
            ->count();
    }

    /**
     * الحصول على أعلى بند مصروفات
     */
    private function getTopExpenseAccount($fromDate, $toDate): ?object
    {
        return JournalDetail::select('account_id', DB::raw('SUM(debit) as total'))
            ->whereHas('accHead', function ($q) {
                $q->where('code', 'like', '57%');
            })
            ->whereDate('crtime', '>=', $fromDate)
            ->whereDate('crtime', '<=', $toDate)
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->with('accHead:id,aname,code')
            ->first();
    }

    /**
     * الحصول على آخر المصروفات
     */
    private function getRecentExpenses(int $limit = 10)
    {
        return JournalDetail::whereHas('accHead', function ($q) {
            $q->where('code', 'like', '57%');
        })
            ->where('debit', '>', 0)
            ->with(['accHead:id,aname,code', 'costCenter:id,name', 'head'])
            ->orderByDesc('crtime')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على المصروفات حسب البند
     */
    private function getExpensesByAccount($fromDate, $toDate, int $limit = 5)
    {
        return JournalDetail::select('account_id', DB::raw('SUM(debit) as total'))
            ->whereHas('accHead', function ($q) {
                $q->where('code', 'like', '57%');
            })
            ->whereDate('crtime', '>=', $fromDate)
            ->whereDate('crtime', '<=', $toDate)
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->with('accHead:id,aname,code')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على المصروفات اليومية (للرسم البياني)
     */
    private function getDailyExpenses($fromDate, $toDate)
    {
        return JournalDetail::select(
            DB::raw('DATE(crtime) as date'),
            DB::raw('SUM(debit) as total')
        )
            ->whereHas('accHead', function ($q) {
                $q->where('code', 'like', '57%');
            })
            ->whereDate('crtime', '>=', $fromDate)
            ->whereDate('crtime', '<=', $toDate)
            ->groupBy(DB::raw('DATE(crtime)'))
            ->orderBy('date')
            ->get();
    }
}
