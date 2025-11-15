<?php

namespace App\Http\Controllers;

use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\DB;
use App\Models\MultiVoucher;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use App\Models\CostCenter;
use App\Models\ProType;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MultiVoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view multi receipt vouchers')->only(['index']);
        $this->middleware('can:create multi receipt vouchers')->only(['create', 'store']);
    }

    public function index()
    {
        $multis = MultiVoucher::where('isdeleted', 0)
            ->whereIn('pro_type',  [32, 33, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55])
            ->orderBy('pro_id', 'desc')
            ->get();
        return view('multi-vouchers.index', compact('multis'));
    }

    public function create(Request $request)
    {
        $branches = userBranches();
        $type = $request->type;
        $pro_type = ProType::where('pname', $type)->first()?->id;
        $ptext = ProType::where('pname', $type)->first()?->ptext;
        // dd($type, $pro_type, $ptext);

        $employees = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->get();

        $lastProId = OperHead::where('pro_type', $type)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;


        [$accounts1, $accounts2] = $this->getAccountsByType($pro_type);
        // dd([$accounts1, $accounts2]);
        return view('multi-vouchers.create', compact('accounts1', 'accounts2', 'pro_type', 'ptext', 'employees', 'newProId', 'branches'));
    }

    private function getAccountsByType($type)
    {
        $query = fn() => AccHead::where('isdeleted', 0)->where('is_basic', 0);

        switch ($type) {
            case 32:
                return [
                    $query()->where('is_fund', 1)->get(),
                    $query()->get()
                ];

            case 33:
                return [
                    $query()->get(),
                    $query()->where('is_fund', 1)->get()
                ];

            case 40:
                return [
                    $query()->where('employees_expensses', 1)->get(),
                    $query()->where('code', 'like', '2102%')->get(),
                ];

            case 41:
                return [
                    $query()->where('employees_expensses', 1)->get(),
                    $query()->where('code', 'like', '2102%')->get(),
                ];

            case 42:
            case 43:
            case 44:
                return [
                    $query()->where('code', 'like', '2102%')->get(),
                    $query()->where('employees_expensses', 1)->get(),
                ];

            case 45:
                return [
                    $query()->where('code', 'like', '1103%')->get(),
                    $query()->where('code', 'like', '2101%')->get()
                ];

            case 46:
                return [
                    $query()->where('code', 'like', '57%')->where('code', 'not Like', '5701%')->get(),
                    $query()->where('code', 'like', '1107%')->get()
                ];

            case 47:
                return [
                    $query()->where('code', 'like', '42%')->get(),
                    $query()->get()
                ];

            case 48:
                return [
                    $query()->where('code', 'like', '1101%')->get(),
                    $query()->where('code', 'like', '47%')->get(),
                ];

            case 49:
                return [
                    $query()->where('code', 'like', '1103%')->get(),
                    $query()->where('code', 'like', '2101%')->get()
                ];

            case 50:
                return [
                    $query()->where('acc_type', '13')->get(),
                    $query()->get()
                ];

            case 51:
            case 52:
            case 53:
                return [
                    $query()->where('acc_type', '13')->get(),
                    $query()->get(),
                ];

            case 54:
                return [
                    $query()->get(),
                    $query()->where('acc_type', '13')->get(),
                ];
            case 55:
                return [
                    $query()->where('acc_type', '13')->get(),
                    $query()->where('acc_type', '14')->get(),
                ];

            default:
                abort(404, 'نوع العملية غير مدعوم');
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'pro_date'      => 'required|date',
            'details'       => 'string|max:255',
            'sub_value'     => 'required|array|min:1',
            'sub_value.*'   => 'nullable|numeric|min:0',
            'note.*'        => 'nullable|string|max:255',
            'acc1'          => 'nullable|array',
            'acc1.*'        => 'nullable|exists:acc_head,id',
            'acc2'          => 'nullable|array',
            'acc2.*'        => 'nullable|exists:acc_head,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            $pro_type = $request->pro_type;

            // تحديد رقم العملية الجديد
            $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            // حساب القيمة الكلية
            $pro_value = collect($request->sub_value)
                ->filter(fn($v) => floatval($v) > 0)
                ->sum();

            // إنشاء رأس العملية
            $operHead = OperHead::create([
                'pro_id'      => $newProId,
                'pro_date'    => $request->pro_date,
                'pro_type'    => $pro_type,
                'pro_value'   => $pro_value,
                'details'     => $request->details ?? null,
                'pro_serial'  => $request->pro_serial ?? null,
                'pro_num'     => $request->pro_num ?? null,
                'branch'      => 1,
                'is_finance'  => 1,
                'is_journal'  => 1,
                'emp_id'      => $request->emp_id,
                'cost_center' => $request->cost_center ?? null,
                'user'        => Auth::id(),
                'branch_id' => $request->branch_id
            ]);

            // إنشاء رأس القيد
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total'      => $pro_value,
                'date'       => $request->pro_date,
                'op_id'      => $operHead->id,
                'pro_type'   => $pro_type,
                'details'    => $request->details,
                'user'       => Auth::id(),
                'branch_id' => $request->branch_id
            ]);

            // القوائم الخاصة بأنواع العمليات
            $account1_types = ['32', '40', '41', '46', '47', '50', '53', '55'];
            $account2_types = ['33', '42', '43', '44', '45', '48', '49', '51', '52', '54'];

            // الحساب الرئيسي
            $mainAcc = null;
            $mainIsDebit = null;

            if (in_array($pro_type, $account1_types)) {
                $mainAcc = $request->acc1[0] ?? null;
                $mainIsDebit = true;
            } elseif (in_array($pro_type, $account2_types)) {
                $mainAcc = $request->acc2[0] ?? null;
                $mainIsDebit = false;
            }

            if ($mainAcc) {
                JournalDetail::create([
                    'journal_id' => $journalHead->id,
                    'account_id' => $mainAcc,
                    'debit'      => $mainIsDebit ? $pro_value : 0,
                    'credit'     => $mainIsDebit ? 0 : $pro_value,
                    'op_id'      => $operHead->id,
                    'type'       => $mainIsDebit ? 0 : 1,
                    'info'       => null,
                    'isdeleted'  => 0,
                    'branch_id' => $request->branch_id
                ]);
            }

            // الحسابات الفرعية
            foreach ($request->sub_value as $index => $value) {
                $value = floatval($value);
                if ($value <= 0) continue;

                $acc_id = null;
                $debit = 0;
                $credit = 0;
                $type = null;

                if (in_array($pro_type, $account1_types)) {
                    $acc_id = $request->acc2[$index] ?? null;
                    $debit = 0;
                    $credit = $value;
                    $type = 1;
                } elseif (in_array($pro_type, $account2_types)) {
                    $acc_id = $request->acc1[$index] ?? null;
                    $debit = $value;
                    $credit = 0;
                    $type = 0;
                }

                if (!$acc_id) continue;

                JournalDetail::create([
                    'journal_id' => $journalHead->id,
                    'account_id' => $acc_id,
                    'debit'      => $debit,
                    'credit'     => $credit,
                    'op_id'      => $operHead->id,
                    'type'       => $type,
                    'info'       => $request->note[$index] ?? null,
                    'isdeleted'  => 0,
                    'branch_id' => $request->branch_id
                ]);
            }

            DB::commit();

            return redirect()->route('multi-vouchers.index')
                ->with('success', 'تم حفظ القيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        // تحميل العملية بالعلاقات اللازمة
        $operHead = OperHead::with(['journalHead.dets.accountHead', 'employee'])
            ->findOrFail($id);

        // جلب بيانات نوع العملية
        $pro_type = $operHead->pro_type;
        $ptext = ProType::where('id', $pro_type)->first()?->ptext;

        if (!$ptext) {
            abort(404, 'نوع العملية غير موجود');
        }

        // الموظفين
        $employees = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->get();

        // الحسابات حسب نوع العملية
        [$accounts1, $accounts2] = $this->getAccountsByType($pro_type);

        // تصنيف نوع العملية
        $account1_types = ['32', '40', '41', '46', '47', '50', '53', '55'];
        $account2_types = ['33', '42', '43', '44', '45', '48', '49', '51', '52', '54'];

        // تحميل تفاصيل اليومية
        $journalDetails = $operHead->journalHead?->dets ?? [];

        $mainEntry = null;
        $subEntries = [];

        foreach ($journalDetails as $detail) {
            if (
                ($detail->debit > 0 && in_array($pro_type, $account1_types)) ||
                ($detail->credit > 0 && in_array($pro_type, $account2_types))
            ) {
                $mainEntry = $detail;
            } else {
                $subEntries[] = $detail;
            }
        }

        return view('multi-vouchers.edit', compact(
            'operHead',
            'accounts1',
            'accounts2',
            'pro_type',
            'ptext',
            'employees',
            'mainEntry',
            'subEntries'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pro_date'      => 'required|date',
            'details'       => 'string|max:255',
            'sub_value'     => 'required|array|min:1',
            'sub_value.*'   => 'nullable|numeric|min:0',
            'note.*'        => 'nullable|string|max:255',
            'acc1'          => 'nullable|array',
            'acc1.*'        => 'nullable|exists:acc_head,id',
            'acc2'          => 'nullable|array',
            'acc2.*'        => 'nullable|exists:acc_head,id',
        ]);

        try {
            DB::beginTransaction();

            $operHead = OperHead::findOrFail($id);
            $pro_type = $operHead->pro_type;

            $pro_value = collect($request->sub_value)
                ->filter(fn($v) => floatval($v) > 0)
                ->sum();

            $operHead->update([
                'pro_date'    => $request->pro_date,
                'pro_value'   => $pro_value,
                'details'     => $request->details,
                'pro_serial'  => $request->pro_serial,
                'pro_num'     => $request->pro_num ?? null,
                'emp_id'      => $request->emp_id,
                'cost_center' => $request->cost_center ?? null,
                'user'        => Auth::id(),
            ]);

            // تحديث رأس القيد
            $journalHead = JournalHead::where('op_id', $operHead->id)->first();

            if ($journalHead) {
                $journalHead->update([
                    'total'   => $pro_value,
                    'date'    => $request->pro_date,
                    'details' => $request->details,
                    'user'    => Auth::id(),
                ]);
            }

            // حذف تفاصيل القيد السابقة
            JournalDetail::where('op_id', $operHead->id)->delete();

            $account1_types = ['32', '40', '41', '46', '47', '50', '53', '55'];
            $account2_types = ['33', '42', '43', '44', '45', '48', '49', '51', '52', '54'];

            // الحساب الرئيسي
            $mainAcc = null;
            $mainIsDebit = null;

            if (in_array($pro_type, $account1_types)) {
                $mainAcc = $request->acc1[0] ?? null;
                $mainIsDebit = true;
            } elseif (in_array($pro_type, $account2_types)) {
                $mainAcc = $request->acc2[0] ?? null;
                $mainIsDebit = false;
            }

            if ($mainAcc) {
                JournalDetail::create([
                    'journal_id' => $journalHead->id,
                    'account_id' => $mainAcc,
                    'debit'      => $mainIsDebit ? $pro_value : 0,
                    'credit'     => $mainIsDebit ? 0 : $pro_value,
                    'op_id'      => $operHead->id,
                    'type'       => $mainIsDebit ? 0 : 1,
                    'info'       => null,
                    'isdeleted'  => 0,
                ]);
            }

            // الحسابات الفرعية
            foreach ($request->sub_value as $index => $value) {
                $value = floatval($value);
                if ($value <= 0) continue;

                $acc_id = null;
                $debit = 0;
                $credit = 0;
                $type = null;

                if (in_array($pro_type, $account1_types)) {
                    $acc_id = $request->acc2[$index] ?? null;
                    $debit = 0;
                    $credit = $value;
                    $type = 1;
                } elseif (in_array($pro_type, $account2_types)) {
                    $acc_id = $request->acc1[$index] ?? null;
                    $debit = $value;
                    $credit = 0;
                    $type = 0;
                }

                if (!$acc_id) continue;

                JournalDetail::create([
                    'journal_id' => $journalHead->id,
                    'account_id' => $acc_id,
                    'debit'      => $debit,
                    'credit'     => $credit,
                    'op_id'      => $operHead->id,
                    'type'       => $type,
                    'info'       => $request->note[$index] ?? null,
                    'isdeleted'  => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('multi-vouchers.index')
                ->with('success', 'تم تحديث القيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()]);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // جلب رأس القيد من جدول oper_heads
            $oper = OperHead::findOrFail($id);

            // حذف journal_head المرتبط (إن وُجد)
            $journalHead = JournalHead::where('op_id', $oper->id)->first();

            if ($journalHead) {
                // حذف التفاصيل المرتبطة به
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
                $journalHead->delete();
            }

            // حذف الرأس من جدول oper_heads
            $oper->delete();

            DB::commit();

            return redirect()->route('multi-vouchers.index')->with('success', 'تم حذف القيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    public function statistics()
    {
        // أنواع العمليات الخاصة برواتب الموظفين
        $proTypeMap = [
            32 => ['title' => 'سند قبض متعدد', 'color' => 'success', 'icon' => 'la-hand-holding-usd'],
            33 => ['title' => 'سند صرف متعدد', 'color' => 'danger', 'icon' => 'la-money-bill-wave-alt'],
            40 => ['title' => 'مصروفات موظفين 1', 'color' => 'warning', 'icon' => 'la-file-invoice-dollar'],
            41 => ['title' => 'مصروفات موظفين 2', 'color' => 'warning', 'icon' => 'la-file-invoice-dollar'],
            42 => ['title' => 'تسويات موظفين 1', 'color' => 'primary', 'icon' => 'la-exchange-alt'],
            43 => ['title' => 'تسويات موظفين 2', 'color' => 'primary', 'icon' => 'la-exchange-alt'],
            44 => ['title' => 'تسويات موظفين 3', 'color' => 'primary', 'icon' => 'la-exchange-alt'],
            45 => ['title' => 'سلف موظفين', 'color' => 'info', 'icon' => 'la-hand-holding-usd'],
            46 => ['title' => 'مصروفات إدارية', 'color' => 'secondary', 'icon' => 'la-briefcase'],
            47 => ['title' => 'إيرادات متنوعة', 'color' => 'success', 'icon' => 'la-coins'],
            48 => ['title' => 'تسويات نقدية', 'color' => 'primary', 'icon' => 'la-exchange-alt'],
            49 => ['title' => 'تسويات سلف', 'color' => 'info', 'icon' => 'la-hand-holding-usd'],
            50 => ['title' => 'مصروفات أخرى 1', 'color' => 'warning', 'icon' => 'la-file-invoice-dollar'],
            51 => ['title' => 'مصروفات أخرى 2', 'color' => 'warning', 'icon' => 'la-file-invoice-dollar'],
            52 => ['title' => 'مصروفات أخرى 3', 'color' => 'warning', 'icon' => 'la-file-invoice-dollar'],
            53 => ['title' => 'مصروفات أخرى 4', 'color' => 'warning', 'icon' => 'la-file-invoice-dollar'],
            54 => ['title' => 'إيرادات أخرى', 'color' => 'success', 'icon' => 'la-coins'],
            55 => ['title' => 'تسويات أخرى', 'color' => 'primary', 'icon' => 'la-exchange-alt'],
        ];

        // إحصائيات حسب نوع العملية
        $statistics = OperHead::whereIn('pro_type', array_keys($proTypeMap))
            ->select('pro_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(pro_value) as value'))
            ->groupBy('pro_type')
            ->get()
            ->keyBy('pro_type')
            ->map(function ($item) use ($proTypeMap) {
                return [
                    'title' => $proTypeMap[$item->pro_type]['title'],
                    'count' => $item->count,
                    'value' => $item->value,
                    'color' => $proTypeMap[$item->pro_type]['color'],
                    'icon' => $proTypeMap[$item->pro_type]['icon'],
                ];
            })->toArray();

        // ترتيب الإحصائيات حسب نوع العملية
        $sortedStatistics = array_replace(array_fill_keys(array_keys($proTypeMap), null), $statistics);

        // إجمالي الكلي
        $overallTotal = OperHead::whereIn('pro_type', array_keys($proTypeMap))
            ->select(DB::raw('COUNT(*) as overall_count'), DB::raw('SUM(pro_value) as overall_value'))
            ->first();

        // إحصائيات حسب الموظفين
        $employeeStats = OperHead::whereIn('pro_type', array_keys($proTypeMap))
            ->join('acc_head', 'operhead.emp_id', '=', 'acc_head.id')
            ->select('acc_head.id', 'acc_head.aname as employee_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(pro_value) as value'))
            ->groupBy('acc_head.id', 'acc_head.aname')
            ->get();

        // إحصائيات حسب مراكز التكلفة
        $costCenterStats = OperHead::whereIn('pro_type', array_keys($proTypeMap))
            ->join('cost_centers', 'operhead.cost_center', '=', 'cost_centers.id')
            ->select('cost_centers.id', 'cost_centers.cname as cost_center_name', DB::raw('SUM(pro_value) as value'))
            ->groupBy('cost_centers.id', 'cost_centers.cname')
            ->get();

        return view('multi-vouchers.statistics', compact('sortedStatistics', 'overallTotal', 'employeeStats', 'costCenterStats'));
    }
}
