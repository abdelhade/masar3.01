<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounts\Models\AccHead;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\{DB, Auth};
use App\Http\Requests\CreateDiscountRequest;
use App\Models\{OperHead, JournalHead, JournalDetail};

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $type = (int) $request->input('type');

        if (!$type) {
            // لو معاه صلاحية واحدة بس، يحوله عليها
            if (auth()->user()->can('view Allowed Discounts')) {
                return redirect()->route('discounts.index', ['type' => 30]);
            } elseif (auth()->user()->can('view Earned Discounts')) {
                return redirect()->route('discounts.index', ['type' => 31]);
            } else {
                abort(403, __('You are not authorized to view this page'));
            }
        }

        if ($type == 30 && !auth()->user()->can('view Allowed Discounts')) {
            abort(403, __('You are not authorized to view the allowed discounts list'));
        }

        if ($type == 31 && !auth()->user()->can('view Earned Discounts')) {
            abort(403, __('You are not authorized to view the earned discounts list'));
        }

        $discounts = OperHead::with(['acc1Head', 'acc2Head']);

        if ($type == 30) {
            $discounts = $discounts->where(function ($query) {
                $query->where('acc1', 49)->orWhere('acc2', 49);
            });
        } elseif ($type == 31) {
            $discounts = $discounts->where(function ($query) {
                $query->where('acc1', 54)->orWhere('acc2', 54);
            });
        }

        $discounts = $discounts->get();

        return view('discounts.index', compact('discounts', 'type'));
    }

    public function show() {}

    public function create(Request $request)
    {
        $type = (int) $request->get('type');
        $hash = $request->get('q');

        if ($hash !== md5($type)) {
            abort(403, __('Invalid code type'));
        }

        // تحقق من الصلاحية قبل الدخول
        if ($type == 30 && !auth()->user()->can('create Allowed Discounts')) {
            abort(403, __('You are not authorized to add this list'));
        }

        if ($type == 31 && !auth()->user()->can('create Earned Discounts')) {
            abort(403, __('You are not authorized to add this list'));
        }

        $branches = userBranches();

        $lastProId = OperHead::max('pro_id');
        $nextProId = $lastProId ? $lastProId + 1 : 1;

        if ($type == 30) {
            // خصم مسموح به: acc1 العملاء - acc2 ثابت (id 49)
            $acc2Fixed = AccHead::findOrFail(49);
            $clientsAccounts = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '1103%')
                ->select('id', 'aname', 'balance')->get()->map(function ($account) {
                    $account->balance = $this->getAccountBalance($account->id);
                    return $account;
                });
            return view('discounts.create', [
                'type' => $type,
                'nextProId' => $nextProId,
                'acc2Fixed' => $acc2Fixed,
                'clientsAccounts' => $clientsAccounts,
                'branches' => $branches
            ]);
        } elseif ($type == 31) {
            // خصم مكتسب: acc1 ثابت (id 54) - acc2 الموردين
            $acc1Fixed = AccHead::findOrFail(54);
            $suppliers = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '2101%')
                ->select('id', 'aname', 'balance')
                ->get()->map(function ($account) {
                    $account->balance = $this->getAccountBalance($account->id);
                    return $account;
                });

            return view('discounts.create', [
                'type' => $type,
                'nextProId' => $nextProId,
                'acc1Fixed' => $acc1Fixed,
                'suppliers' => $suppliers,
                'branches' => $branches
            ]);
        } else {
            abort(404);
        }
    }

    protected function getAccountBalance($accountId)
    {
        $totalDebit = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->sum('debit');
        $totalCredit = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->sum('credit');
        return $totalDebit - $totalCredit;
    }

    public function store(CreateDiscountRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            if ($validated['type'] == 30 && !auth()->user()->can('create Allowed Discounts')) {
                abort(403, __('You are not authorized to add this list'));
            }

            if ($validated['type'] == 31 && !auth()->user()->can('create Earned Discounts')) {
                abort(403, __('You are not authorized to add this list'));
            }

            $oper = new OperHead();
            $oper->pro_type = $request->type;
            $oper->pro_id = $request->pro_id;
            $oper->pro_date = $request->pro_date;
            $oper->info = $request->info ?? null;
            $oper->pro_value = $request->pro_value;
            $oper->branch_id = $request->branch_id;

            if ($validated['type'] == 30) {
                // خصم مسموح به: acc1 = العملاء، acc2 ثابت (49)
                $oper->acc1 = $validated['acc1'];
                $oper->acc2 = 49;
            } elseif ($validated['type'] == 31) {
                // خصم مكتسب: acc1 ثابت (54), acc2 = المورد
                $oper->acc1 = 54;
                $oper->acc2 = $validated['acc2'];
            }
            $oper->save();

            $journalId = JournalHead::max('journal_id') + 1;
            JournalHead::create([
                'journal_id' => $journalId,
                'total' => $oper->pro_value,
                'op_id' => $oper->id,
                'op2' => 0,
                'pro_type' => $oper->pro_type,
                'date' => $oper->pro_date,
                'details' => $oper->info ?? ($oper->pro_type == 30 ? __('Allowed Discount') : __('Earned Discount')),
                'user' => Auth::id(),
                'branch_id' => $request->branch_id
            ]);

            if ($oper->pro_type == 30) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $oper->acc1,
                    'debit' => 0,
                    'credit' => $oper->pro_value,
                    'type' => 1,
                    'info' => $oper->info ?? __('Allowed Discount'),
                    'op_id' => $oper->id,
                    'branch_id' => $request->branch_id
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49,
                    'debit' => $oper->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $oper->info ?? __('Allowed Discount'),
                    'op_id' => $oper->id,
                    'branch_id' => $request->branch_id
                ]);
            } elseif ($oper->pro_type == 31) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 54,
                    'debit' => $oper->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $oper->info ?? __('Earned Discount'),
                    'op_id' => $oper->id,
                    'branch_id' => $request->branch_id
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $oper->acc2,
                    'debit' => 0,
                    'credit' => $oper->pro_value,
                    'type' => 1,
                    'info' => $oper->info ?? __('Earned Discount'),
                    'op_id' => $oper->id,
                    'branch_id' => $request->branch_id
                ]);
            }
            DB::commit();
            Alert::toast(__('Data saved successfully'), 'success');
            return redirect()->route('discounts.index', ['type' => $oper->pro_type]);
        } catch (\Exception) {
            DB::rollBack();
            Alert::toast(__('An error occurred while saving the discount'), 'error');
            return back()->withInput();
        }
    }

    public function edit(Request $request, OperHead $discount)
    {
        $type = $discount->pro_type;

        if (!in_array($type, [30, 31])) {
            abort(403, __('Incorrect discount type'));
        }

        if ($type == 30 && !Auth::user()->can('edit Allowed Discounts')) {
            abort(403, __('You are not authorized to edit this list'));
        }

        if ($type == 31 && !Auth::user()->can('edit Earned Discounts')) {
            abort(403, __('You are not authorized to edit this list'));
        }

        $titles = [
            30 => __('Allowed Discount'),
            31 => __('Earned Discount'),
        ];

        if ($type == 30) {
            $acc2Fixed = AccHead::findOrFail(49);
            $clientsAccounts = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '1103%')
                ->select('id', 'aname')
                ->get();

            return view('discounts.edit', compact('discount', 'type', 'acc2Fixed', 'clientsAccounts', 'titles'));
        } elseif ($type == 31) {
            $acc1Fixed = AccHead::findOrFail(54);
            $suppliers = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '2101%')
                ->select('id', 'aname')
                ->get();

            return view('discounts.edit', compact('discount', 'type', 'acc1Fixed', 'suppliers', 'titles'));
        }
    }

    public function update(CreateDiscountRequest $request, OperHead $discount)
    {
        if ($request->type == 30 && !auth()->user()->can('edit Allowed Discounts')) {
            abort(403, __('You are not authorized to edit this list'));
        }

        if ($request->type == 31 && !auth()->user()->can('edit Earned Discounts')) {
            abort(403, __('You are not authorized to edit this list'));
        }

        try {
            DB::beginTransaction();

            $discount->pro_type = $request->type;
            $discount->pro_date = $request->pro_date;
            $discount->info = $request->info ?? null;
            $discount->pro_value = $request->pro_value;

            if ($request->filled('branch_id')) {
                $discount->branch_id = $request->branch_id;
            }

            if ($request->type == 30) {
                $discount->acc1 = $request->acc1;
                $discount->acc2 = 49;
            } elseif ($request->type == 31) {
                $discount->acc1 = 54;
                $discount->acc2 = $request->acc2;
            }
            $discount->save();

            JournalDetail::withoutGlobalScopes()->where('op_id', $discount->id)->delete();
            JournalHead::withoutGlobalScopes()->where('op_id', $discount->id)->delete();

            $journalId = JournalHead::max('journal_id') + 1;
            JournalHead::create([
                'journal_id' => $journalId,
                'total' => $discount->pro_value,
                'op_id' => $discount->id,
                'op2' => 0,
                'pro_type' => $discount->pro_type,
                'date' => $discount->pro_date,
                'details' => $discount->info ?? ($discount->pro_type == 30 ? __('Allowed Discount') : __('Earned Discount')),
                'user' => Auth::id(),
                'branch_id' => $discount->branch_id,
            ]);

            // القيود المحاسبية
            if ($discount->pro_type == 30) {
                // خصم مسموح به
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $discount->acc1,
                    'debit' => 0,
                    'credit' => $discount->pro_value,
                    'type' => 1,
                    'info' => $discount->info ?? __('Allowed Discount'),
                    'op_id' => $discount->id,
                    'branch_id' => $discount->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 49,
                    'debit' => $discount->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $discount->info ?? __('Allowed Discount'),
                    'op_id' => $discount->id,
                    'branch_id' => $discount->branch_id,
                ]);
            } elseif ($discount->pro_type == 31) {
                // خصم مكتسب
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 54,
                    'debit' => $discount->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $discount->info ?? __('Earned Discount'),
                    'op_id' => $discount->id,
                    'branch_id' => $discount->branch_id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $discount->acc2,
                    'debit' => 0,
                    'credit' => $discount->pro_value,
                    'type' => 1,
                    'info' => $discount->info ?? __('Earned Discount'),
                    'op_id' => $discount->id,
                    'branch_id' => $discount->branch_id,
                ]);
            }

            DB::commit();
            Alert::toast(__('Discount updated successfully'), 'success');
            return redirect()->route('discounts.index', ['type' => $discount->pro_type]);
        } catch (\Exception) {
            DB::rollBack();
            Alert::toast(__('An error occurred while updating the discount'), 'error');
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $discount = OperHead::findOrFail($id);

        // تحقق من الصلاحية حسب نوع العملية
        if ($discount->pro_type == 30 && !Auth::user()->can('delete Allowed Discounts')) {
            abort(403, __('You are not authorized to delete this list'));
        }

        if ($discount->pro_type == 31 && !Auth::user()->can('delete Earned Discounts')) {
            abort(403, __('You are not authorized to delete this list'));
        }

        try {
            JournalDetail::where('op_id', $discount->id)->delete();
            JournalHead::where('op_id', $discount->id)->delete();

            $discount->delete();
            Alert::toast(__('Discount deleted successfully'), 'success');
            return redirect()->route('discounts.index', ['type' => $discount->pro_type]);
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the discount'), 'error');
            return back();
        }
    }

    public function generalStatistics(Request $request)
    {
        // إجمالي عدد الخصومات
        $query = OperHead::query();
        $totalDiscounts = $query->count();

        // إجمالي قيمة الخصومات
        $totalValue = $query->sum('pro_value');

        // متوسط قيمة الخصم
        $avgValue = $totalDiscounts > 0 ? round($totalValue / $totalDiscounts, 2) : 0;

        // أعلى وأقل قيمة خصم
        $maxDiscount = $query->max('pro_value') ?? 0;
        $minDiscount = $query->min('pro_value') ?? 0;

        // الخصومات خلال الشهر الحالي
        $currentMonthDiscounts = OperHead::whereYear('pro_date', date('Y'))
            ->whereMonth('pro_date', date('m'))
            ->count();

        $currentMonthValue = OperHead::whereYear('pro_date', date('Y'))
            ->whereMonth('pro_date', date('m'))
            ->sum('pro_value');

        // الخصومات خلال السنة الحالية
        $currentYearDiscounts = OperHead::whereYear('pro_date', date('Y'))
            ->count();

        $currentYearValue = OperHead::whereYear('pro_date', date('Y'))
            ->sum('pro_value');

        // أكثر 5 حسابات حصلت على خصومات
        $topAccounts = OperHead::selectRaw('COALESCE(acc1, acc2) as account_id, COUNT(*) as count, SUM(pro_value) as total')
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with(['acc1Head:id,aname', 'acc2Head:id,aname'])
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->acc1Head->aname ?? $item->acc2Head->aname ?? __('Unknown'),
                    'count' => $item->count,
                    'total' => $item->total
                ];
            });

        // الخصومات حسب الأشهر (آخر 6 أشهر)
        $monthlyDiscounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));

            $count = OperHead::whereYear('pro_date', $year)
                ->whereMonth('pro_date', $month)
                ->count();

            $value = OperHead::whereYear('pro_date', $year)
                ->whereMonth('pro_date', $month)
                ->sum('pro_value');

            $monthName = [
                '01' => __('January'),
                '02' => __('February'),
                '03' => __('March'),
                '04' => __('April'),
                '05' => __('May'),
                '06' => __('June'),
                '07' => __('July'),
                '08' => __('August'),
                '09' => __('September'),
                '10' => __('October'),
                '11' => __('November'),
                '12' => __('December')
            ][$month] ?? '';

            $monthlyDiscounts[] = [
                'month' => date('M Y', strtotime($date)),
                'month_ar' => $monthName . ' ' . $year,
                'count' => $count,
                'value' => $value
            ];
        }

        // نطاقات القيم
        $valueRanges = DB::table('operhead')
            ->select(
                DB::raw('CASE
                    WHEN pro_value < 100 THEN "' . __('Less than 100') . '"
                    WHEN pro_value >= 100 AND pro_value < 500 THEN "' . __('100 - 500') . '"
                    WHEN pro_value >= 500 AND pro_value < 1000 THEN "' . __('500 - 1000') . '"
                    WHEN pro_value >= 1000 AND pro_value < 5000 THEN "' . __('1000 - 5000') . '"
                    ELSE "' . __('More than 5000') . '"
                    END as value_range'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(pro_value) as total')
            )
            ->groupBy('value_range')
            ->get()
            ->map(function ($item) {
                return [
                    'range' => $item->value_range,
                    'count' => $item->count,
                    'total' => $item->total
                ];
            });

        // أحدث الخصومات
        $recentDiscounts = OperHead::with(['acc1Head:id,aname', 'acc2Head:id,aname'])
            ->orderByDesc('pro_date')
            ->limit(10)
            ->get()
            ->map(function ($discount) {
                return [
                    'id' => $discount->id,
                    'pro_id' => $discount->pro_id,
                    'account_name' => $discount->acc1Head->aname ?? $discount->acc2Head->aname ?? '-',
                    'value' => $discount->pro_value,
                    'date' => $discount->pro_date,
                    'info' => $discount->info ?? '-'
                ];
            });

        // إحصائيات حسب الفرع
        $branchStats = OperHead::select('branch_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(pro_value) as total'))
            ->groupBy('branch_id')
            ->with('branch:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'branch_name' => $item->branch->name ?? __('Not specified'),
                    'count' => $item->count,
                    'total' => $item->total
                ];
            });

        // مقارنة بين الشهر الحالي والشهر السابق
        $lastMonthDiscounts = OperHead::whereYear('pro_date', date('Y', strtotime('-1 month')))
            ->whereMonth('pro_date', date('m', strtotime('-1 month')))
            ->count();

        $lastMonthValue = OperHead::whereYear('pro_date', date('Y', strtotime('-1 month')))
            ->whereMonth('pro_date', date('m', strtotime('-1 month')))
            ->sum('pro_value');

        $countChange = $lastMonthDiscounts > 0
            ? round((($currentMonthDiscounts - $lastMonthDiscounts) / $lastMonthDiscounts) * 100, 2)
            : 0;

        $valueChange = $lastMonthValue > 0
            ? round((($currentMonthValue - $lastMonthValue) / $lastMonthValue) * 100, 2)
            : 0;

        $statistics = compact(
            'totalDiscounts',
            'totalValue',
            'avgValue',
            'maxDiscount',
            'minDiscount',
            'currentMonthDiscounts',
            'currentMonthValue',
            'currentYearDiscounts',
            'currentYearValue',
            'topAccounts',
            'monthlyDiscounts',
            'valueRanges',
            'recentDiscounts',
            'branchStats',
            'lastMonthDiscounts',
            'lastMonthValue',
            'countChange',
            'valueChange'
        );

        return view('discounts.statistics', compact('statistics'));
    }
}
