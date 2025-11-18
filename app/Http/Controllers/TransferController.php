<?php

namespace App\Http\Controllers;

use App\Models\{OperHead, Transfer, JournalHead};
use App\Models\JournalDetail;
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view transfer-statistics')->only('statistics');
        // خريطة أنواع التحويل إلى slug بصيغة الشرطات المستخدمة في أسماء الصلاحيات
        $typeSlugs = [
            3 => 'cash-to-cash',
            4 => 'cash-to-bank',
            5 => 'bank-to-cash',
            6 => 'bank-to-bank',
        ];

        // middleware عام يسمح بالتحقق من صلاحيات الوصول حسب الإجراء ونوع التحويل
        $this->middleware(function ($request, $next) use ($typeSlugs) {
            $action = $request->route()->getActionMethod();

            // Helper: تحقق من صلاحية عبر Gate
            $allow = function ($perm) {
                return Gate::allows($perm);
            };

            // INDEX: إن وُجدت query param `type` نتحقق بصيغة `view {type}`، وإلا نفعل fallback
            if ($action === 'index') {
                $type = $request->get('type');
                if ($type) {
                    // Normalize incoming type to hyphen form (accept underscores or hyphens)
                    $normType = str_replace('_', '-', $type);
                    if ($allow("view {$normType}")) return $next($request);
                    abort(403);
                }
                if ($allow('view transfers')) return $next($request);
                abort(403);
            }

            // CREATE / STORE: type يجب أن يأتي كـ query param
            if (in_array($action, ['create', 'store'])) {
                $type = $request->get('type');
                if ($type) {
                    $normType = str_replace('_', '-', $type);
                    if ($allow("create {$normType}")) return $next($request);
                }
                if ($allow('create transfers')) return $next($request);
                abort(403);
            }

            // EDIT / UPDATE / DESTROY: نقرأ السجل لمعرفة pro_type
            if (in_array($action, ['edit', 'update', 'destroy'])) {
                $routeParam = $request->route('id') ?? $request->route('transfer') ?? $request->route('operhead') ?? null;
                $id = null;
                if (is_object($routeParam) && isset($routeParam->id)) $id = $routeParam->id;
                elseif (is_numeric($routeParam)) $id = $routeParam;

                $oper = $id ? OperHead::find($id) : null;
                $typeSlug = $oper && isset($typeSlugs[$oper->pro_type]) ? $typeSlugs[$oper->pro_type] : null;

                if ($action === 'destroy') {
                    if ($typeSlug && $allow("delete {$typeSlug}")) return $next($request);
                    if ($allow('delete transfers')) return $next($request);
                    abort(403);
                }

                // edit/update
                if ($typeSlug && $allow("edit {$typeSlug}")) return $next($request);
                if ($allow('edit transfers')) return $next($request);
                abort(403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        $transfers = Transfer::with('account1')->whereIn('pro_type', [3, 4, 5, 6]) // أنواع التحويل المطلوبة
            ->where('isdeleted', 0) // تجاهل المحذوفة
            ->orderByDesc('pro_date') // الترتيب حسب التاريخ تنازلي
            ->get();

        return view('transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $branches = userBranches();
        // تقبل قيمة النوع مع شرطات أو underscores، وحوّلها إلى المفتاح المُستخدم داخل الخريطة
        $rawType = $request->get('type');
        $type = $rawType ? str_replace('-', '_', $rawType) : null;
        $proTypeMap = [
            'receipt' => 1,
            'payment' => 2,
            'cash_to_cash' => 3,
            'cash_to_bank' => 4,
            'bank_to_cash' => 5,
            'bank_to_bank' => 6,
        ];

        $pro_type = $proTypeMap[$type] ?? null;

        $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // حسابات الصندوق
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1101%')
            ->select('id', 'aname')
            ->get();

        // حسابات البنك
        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1102%')
            ->select('id', 'aname')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->select('id', 'aname')
            ->get();

        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where(function ($query) {
                $query->where('is_fund', 'not', '1');
                $query->where('is_stock', 'not', '1 order by code');
            })
            ->select('id', 'aname', 'code')
            ->get();

        return view('transfers.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_type' => 'required|integer',
            'pro_date' => 'required|date',
            'pro_num' => 'nullable|string',
            'emp_id' => 'required|integer',
            'emp2_id' => 'nullable|integer',
            'acc1' => 'required|integer',
            'acc2' => 'required|integer',
            'pro_value' => 'required|numeric', // نفس قيمة المدين والدائن
            'details' => 'nullable|string',
            'info' => 'nullable|string',
            'info2' => 'nullable|string',
            'info3' => 'nullable|string',
            'cost_center' => 'nullable|integer',
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            // الحصول على pro_id جديد حسب نوع العملية
            $lastProId = Operhead::where('pro_type', $validated['pro_type'])->max('pro_id');
            $newProId = $lastProId ? $lastProId + 1 : 1;

            $oper = Operhead::create([
                'pro_id' => $newProId,
                'pro_date' => $validated['pro_date'],
                'pro_type' => $validated['pro_type'],
                'pro_num' => $validated['pro_num'] ?? null,
                'pro_serial' => $request['pro_serial'] ?? null,
                'acc1' => $validated['acc1'],
                'acc2' => $validated['acc2'],
                'pro_value' => $validated['pro_value'],
                'details' => $validated['details'] ?? null,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'emp_id' => $validated['emp_id'],
                'emp2_id' => $validated['emp2_id'] ?? null,
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'cost_center' => $validated['cost_center'] ?? null,
                'user' => Auth::id(),
                'info' => $validated['info'] ?? null,
                'info2' => $validated['info2'] ?? null,
                'info3' => $validated['info3'] ?? null,
                'branch_id' => $validated['branch_id'],
            ]);

            // إنشاء journal_head
            $lastJournalId = JournalHead::max('journal_id');
            $newJournalId = $lastJournalId ? $lastJournalId + 1 : 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $validated['pro_value'],
                'date' => $validated['pro_date'],
                'op_id' => $oper->id,
                'pro_type' => $validated['pro_type'],
                'details' => $validated['details'] ?? null,
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // تفاصيل اليومية: مدين
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['acc1'],
                'debit' => $validated['pro_value'],
                'credit' => 0,
                'type' => 0,
                'info' => $validated['info'] ?? null,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $validated['branch_id'],
            ]);

            // تفاصيل اليومية: دائن
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['acc2'],
                'debit' => 0,
                'credit' => $validated['pro_value'],
                'type' => 1,
                'info' => $validated['info'] ?? null,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $validated['branch_id'],
            ]);

            DB::commit();

            return redirect()->route('transfers.index')->with('success', 'تم حفظ السند والقيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }


    public function edit($id)
    {
        $transfer = Transfer::findOrFail($id);

        // تحديد نوع التحويل بناءً على pro_type
        $typeMap = [
            3 => 'cash_to_cash',
            4 => 'cash_to_bank',
            5 => 'bank_to_cash',
            6 => 'bank_to_bank',
        ];
        $type = $typeMap[$transfer->pro_type] ?? null;

        // حسابات الصندوق
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1101%')
            ->select('id', 'aname')
            ->get();

        // حسابات البنك
        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1102%')
            ->select('id', 'aname')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->select('id', 'aname')
            ->get();

        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->orderBy('code')
            ->select('id', 'aname', 'code')
            ->get();

            // تأكد من أن الحسابات الحالية موجودة في القوائم حتى لو تغيرت التصنيفات
            if ($transfer->acc1) {
                $acc1 = AccHead::select('id', 'aname')->find($transfer->acc1);
                if ($acc1 && !$cashAccounts->contains('id', $acc1->id) && !$bankAccounts->contains('id', $acc1->id)) {
                    // ضع acc1 في otherAccounts كي يظهر في القوائم
                    $otherAccounts->push($acc1);
                }
            }

            if ($transfer->acc2) {
                $acc2 = AccHead::select('id', 'aname')->find($transfer->acc2);
                if ($acc2 && !$cashAccounts->contains('id', $acc2->id) && !$bankAccounts->contains('id', $acc2->id)) {
                    $otherAccounts->push($acc2);
                }
            }

            // تأكد أن الموظف/المندوب موجودان في قائمة الحسابات الخاصة بالموظفين
            if ($transfer->emp_id) {
                $e1 = AccHead::select('id', 'aname')->find($transfer->emp_id);
                if ($e1 && !$employeeAccounts->contains('id', $e1->id)) {
                    $employeeAccounts->push($e1);
                }
            }
            if ($transfer->emp2_id) {
                $e2 = AccHead::select('id', 'aname')->find($transfer->emp2_id);
                if ($e2 && !$employeeAccounts->contains('id', $e2->id)) {
                    $employeeAccounts->push($e2);
                }
            }

            // مراكز التكلفة (إن وُجِدَت في التطبيق)
            $costCenters = [];
            if (class_exists('\Modules\\CostCenter\\Models\\CostCenter')) {
                $costCenters = \Modules\CostCenter\Models\CostCenter::where('isdeleted', 0)->select('id', 'name')->get();
            }

        return view('transfers.edit', [
            'transfer' => $transfer,
            'type' => $type,
            'cashAccounts' => $cashAccounts,
            'bankAccounts' => $bankAccounts,
            'employeeAccounts' => $employeeAccounts,
            'otherAccounts' => $otherAccounts,
            'pro_id' => $transfer->pro_id,
            'pro_type' => $transfer->pro_type,
            'costCenters' => $costCenters,
        ]);
    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pro_type' => 'required|integer',
            'pro_date' => 'required|date',
            'pro_num' => 'nullable|string',
            'emp_id' => 'required|integer',
            'emp2_id' => 'nullable|integer',
            'acc1' => 'required|integer',
            'acc2' => 'required|integer',
            'pro_value' => 'required|numeric',
            'details' => 'nullable|string',
            'info' => 'nullable|string',
            'info2' => 'nullable|string',
            'info3' => 'nullable|string',
            'cost_center' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // تحديث operhead
            $oper = Operhead::findOrFail($id);
            $oper->update([
                'pro_date' => $validated['pro_date'],
                'pro_num' => $validated['pro_num'] ?? null,
                'pro_serial' => $request['pro_serial'] ?? null,
                'acc1' => $validated['acc1'],
                'acc2' => $validated['acc2'],
                'pro_value' => $validated['pro_value'],
                'details' => $validated['details'] ?? null,
                'emp_id' => $validated['emp_id'],
                'emp2_id' => $validated['emp2_id'] ?? null,
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'cost_center' => $validated['cost_center'] ?? null,
                'user' => Auth::id(),
                'info' => $validated['info'] ?? null,
                'info2' => $validated['info2'] ?? null,
                'info3' => $validated['info3'] ?? null,
            ]);

            // تحديث journal_head
            $journalHead = JournalHead::where('op_id', $oper->id)->first();
            if ($journalHead) {
                $journalHead->update([
                    'total' => $validated['pro_value'],
                    'date' => $validated['pro_date'],
                    'pro_type' => $validated['pro_type'],
                    'details' => $validated['details'] ?? null,
                    'user' => Auth::id(),
                ]);

                $journalId = $journalHead->journal_id;

                // حذف التفاصيل القديمة
                JournalDetail::where('journal_id', $journalId)->delete();

                // إنشاء تفاصيل جديدة (مدين)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $validated['acc1'],
                    'debit' => $validated['pro_value'],
                    'credit' => 0,
                    'type' => 0,
                    'info' => $validated['info'] ?? null,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                ]);

                // إنشاء تفاصيل جديدة (دائن)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $validated['acc2'],
                    'debit' => 0,
                    'credit' => $validated['pro_value'],
                    'type' => 1,
                    'info' => $validated['info'] ?? null,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                ]);
            }

            DB::commit();
            return redirect()->route('transfers.index')->with('success', 'تم تعديل السند بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }


    public function show(string $request) {}


    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $voucher = Operhead::findOrFail($id);

            // حذف journal_head المرتبط
            $journalHead = JournalHead::where('op_id', $voucher->id)->first();

            if ($journalHead) {
                // حذف تفاصيل اليومية
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();

                // حذف رأس اليومية
                $journalHead->delete();
            }

            // حذف السند
            $voucher->delete();

            DB::commit();
            return redirect()->route('transfers.index')->with('success', 'تم حذف السند والقيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    public function statistics()
    {
        // أنواع التحويلات النقدية
        $proTypeMap = [
            3 => ['title' => 'تحويل من صندوق إلى صندوق', 'color' => 'primary', 'icon' => 'la-exchange-alt'],
            4 => ['title' => 'تحويل من صندوق إلى بنك', 'color' => 'success', 'icon' => 'la-university'],
            5 => ['title' => 'تحويل من بنك إلى صندوق', 'color' => 'warning', 'icon' => 'la-hand-holding-usd'],
            6 => ['title' => 'تحويل من بنك إلى بنك', 'color' => 'info', 'icon' => 'la-credit-card'],
        ];

        // جلب الإحصائيات من جدول OperHead
        $statistics = OperHead::whereIn('pro_type', [3, 4, 5, 6])
            ->where('isdeleted', 0)
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
        $sortedStatistics = array_replace(array_fill_keys([3, 4, 5, 6], null), $statistics);

        // إجمالي الكلي
        $overallTotal = OperHead::whereIn('pro_type', [3, 4, 5, 6])
            ->where('isdeleted', 0)
            ->select(DB::raw('COUNT(*) as overall_count'), DB::raw('SUM(pro_value) as overall_value'))
            ->first();

        return view('transfers.statistics', compact('sortedStatistics', 'overallTotal'));
    }
}
