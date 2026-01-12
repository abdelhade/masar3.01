<?php

namespace App\Http\Controllers;

use App\Models\OperHead;
use App\Models\Transfer;
use App\Models\JournalHead;
use Illuminate\Http\Request;
use App\Models\JournalDetail;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Accounts\Models\AccHead;
use Modules\Settings\Models\Currency;

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
                    if ($allow("view {$normType}")) {
                        return $next($request);
                    }
                    abort(403);
                }
                if ($allow('view transfers')) {
                    return $next($request);
                }
                abort(403);
            }

            // CREATE / STORE: type يجب أن يأتي كـ query param
            if (in_array($action, ['create', 'store'])) {
                $type = $request->get('type');
                if ($type) {
                    $normType = str_replace('_', '-', $type);
                    if ($allow("create {$normType}")) {
                        return $next($request);
                    }
                }
                if ($allow('create transfers')) {
                    return $next($request);
                }
                abort(403);
            }

            // SHOW / EDIT / UPDATE / DESTROY: نقرأ السجل لمعرفة pro_type
            if (in_array($action, ['show', 'edit', 'update', 'destroy'])) {
                $routeParam = $request->route('id') ?? $request->route('transfer') ?? $request->route('operhead') ?? null;
                $id = null;
                if (is_object($routeParam) && isset($routeParam->id)) {
                    $id = $routeParam->id;
                } elseif (is_numeric($routeParam)) {
                    $id = $routeParam;
                }

                $oper = $id ? OperHead::find($id) : null;
                $typeSlug = $oper && isset($typeSlugs[$oper->pro_type]) ? $typeSlugs[$oper->pro_type] : null;

                if ($action === 'show') {
                    if ($typeSlug && $allow("view {$typeSlug}")) {
                        return $next($request);
                    }
                    if ($allow('view transfers')) {
                        return $next($request);
                    }
                    abort(403);
                }

                if ($action === 'destroy') {
                    if ($typeSlug && $allow("delete {$typeSlug}")) {
                        return $next($request);
                    }
                    if ($allow('delete transfers')) {
                        return $next($request);
                    }
                    abort(403);
                }

                // edit/update
                if ($typeSlug && $allow("edit {$typeSlug}")) {
                    return $next($request);
                }
                if ($allow('edit transfers')) {
                    return $next($request);
                }
                abort(403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        $transfers = Transfer::with(['account1', 'currency']) // Eager load currency to prevent N+1 queries
            ->whereIn('pro_type', [3, 4, 5, 6]) // أنواع التحويل المطلوبة
            ->where('isdeleted', 0) // تجاهل المحذوفة
            ->orderByDesc('pro_date') // الترتيب حسب التاريخ تنازلي
            ->get();

        return view('transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $branches = userBranches();
        $allCurrencies = Currency::active()->with('latestRate')->get();

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

        // تحديد العنوان العربي حسب نوع التحويل
        $typeTitles = [
            'cash_to_cash' => 'تحويل من صندوق إلى صندوق',
            'cash_to_bank' => 'تحويل من صندوق إلى بنك',
            'bank_to_cash' => 'تحويل من بنك إلى صندوق',
            'bank_to_bank' => 'تحويل من بنك إلى بنك',
        ];
        $pageTitle = $typeTitles[$type] ?? 'تحويل نقدي';

        $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // تحديد نوع الحسابات حسب نوع التحويل
        // acc_type = 3: صندوق
        // acc_type = 4: بنك
        $fromAccountType = null;
        $toAccountType = null;

        switch ($type) {
            case 'cash_to_cash':
                $fromAccountType = 3; // صندوق
                $toAccountType = 3; // صندوق
                break;
            case 'cash_to_bank':
                $fromAccountType = 3; // صندوق
                $toAccountType = 4; // بنك
                break;
            case 'bank_to_cash':
                $fromAccountType = 4; // بنك
                $toAccountType = 3; // صندوق
                break;
            case 'bank_to_bank':
                $fromAccountType = 4; // بنك
                $toAccountType = 4; // بنك
                break;
        }

        // حسابات "من حساب" حسب نوع التحويل - مع إضافة currency_id و balance
        $fromAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->when($fromAccountType, function ($query) use ($fromAccountType) {
                return $query->where('acc_type', $fromAccountType);
            })
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات "إلى حساب" حسب نوع التحويل - مع إضافة currency_id و balance
        $toAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->when($toAccountType, function ($query) use ($toAccountType) {
                return $query->where('acc_type', $toAccountType);
            })
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات الصندوق (للاحتفاظ بالتوافق مع الكود القديم إن لزم)
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', 3)
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات البنك (للاحتفاظ بالتوافق مع الكود القديم إن لزم)
        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', 4)
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->select('id', 'aname', 'currency_id')
            ->get();

        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->orderBy('code')
            ->with('currency:id,name')
            ->select('id', 'aname', 'code', 'balance', 'currency_id')
            ->get();

        // مراكز التكلفة (إن وُجِدَت في التطبيق)
        $costCenters = [];
        if (class_exists('\Modules\\CostCenter\\Models\\CostCenter')) {
            $costCenters = \Modules\CostCenter\Models\CostCenter::where('isdeleted', 0)->select('id', 'name')->get();
        }

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
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'pro_value' => 'required|numeric',
            'details' => 'nullable|string',
            'info' => 'nullable|string',
            'info2' => 'nullable|string',
            'info3' => 'nullable|string',
            'cost_center' => 'nullable|integer',
            'branch_id' => 'required|exists:branches,id',
            'currency_id' => 'nullable|integer',
            'currency_rate' => 'nullable|numeric',
        ]);

        // التحقق من تطابق العملات في حالة تفعيل تعدد العملات
        if (isMultiCurrencyEnabled()) {
            $acc1 = AccHead::find($validated['acc1']);
            $acc2 = AccHead::find($validated['acc2']);

            if ($acc1 && $acc2 && $acc1->currency_id != $acc2->currency_id) {
                return back()->withErrors(['currency_mismatch' => 'عذراً، يجب أن يكون للحسابين نفس العملة لإتمام التحويل.'])->withInput();
            }
        }

        // تحديد العملة وسعر الصرف - القيم الافتراضية id=1 و rate=1
        $currencyId = (int) ($request->get('currency_id') ?? 1);
        $currencyRate = (float) ($request->get('currency_rate') ?? 1);

        // التأكد من أن القيم صحيحة
        if ($currencyId <= 0) $currencyId = 1;
        if ($currencyRate <= 0) $currencyRate = 1;

        // ضرب القيمة في سعر الصرف للحفظ والقيود
        // pro_value المدخلة هي القيمة الأصلية (مثلاً 1000 دولار)
        // baseValue هي القيمة الأساسية بعد الضرب (مثلاً 1000 * 47 = 47000)
        $baseValue = (float) $validated['pro_value'] * $currencyRate;

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
                'pro_value' => $baseValue,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,
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
                'total' => $baseValue,
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
                'debit' => $baseValue,
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
                'credit' => $baseValue,
                'type' => 1,
                'info' => $validated['info'] ?? null,
                'op_id' => $oper->id,
                'isdeleted' => 0,
                'branch_id' => $validated['branch_id'],
            ]);

            DB::commit();

            return redirect()->route('transfers.index')->with('success', 'تم حفظ التحويل والقيد المحاسبي بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $transfer = Transfer::findOrFail($id);
        //$branches = userBranches();
        $allCurrencies = Currency::active()->with('latestRate')->get();

        // تحديد نوع التحويل بناءً على pro_type
        $typeMap = [
            3 => 'cash_to_cash',
            4 => 'cash_to_bank',
            5 => 'bank_to_cash',
            6 => 'bank_to_bank',
        ];
        $type = $typeMap[$transfer->pro_type] ?? null;

        // تحديد نوع الحسابات حسب نوع التحويل
        // acc_type = 3: صندوق
        // acc_type = 4: بنك
        $fromAccountType = null;
        $toAccountType = null;

        switch ($type) {
            case 'cash_to_cash':
                $fromAccountType = 3; // صندوق
                $toAccountType = 3; // صندوق
                break;
            case 'cash_to_bank':
                $fromAccountType = 3; // صندوق
                $toAccountType = 4; // بنك
                break;
            case 'bank_to_cash':
                $fromAccountType = 4; // بنك
                $toAccountType = 3; // صندوق
                break;
            case 'bank_to_bank':
                $fromAccountType = 4; // بنك
                $toAccountType = 4; // بنك
                break;
        }

        // حسابات "من حساب" حسب نوع التحويل - مع إضافة currency_id و balance
        $fromAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->when($fromAccountType, function ($query) use ($fromAccountType) {
                return $query->where('acc_type', $fromAccountType);
            })
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات "إلى حساب" حسب نوع التحويل - مع إضافة currency_id و balance
        $toAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->when($toAccountType, function ($query) use ($toAccountType) {
                return $query->where('acc_type', $toAccountType);
            })
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات الصندوق (للاحتفاظ بالتوافق مع الكود القديم)
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', 3)
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات البنك (للاحتفاظ بالتوافق مع الكود القديم)
        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', 4)
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->select('id', 'aname', 'currency_id')
            ->get();

        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->orderBy('code')
            ->with('currency:id,name')
            ->select('id', 'aname', 'code', 'balance', 'currency_id')
            ->get();

        // تأكد من أن الحسابات الحالية موجودة في القوائم حتى لو تغيرت التصنيفات
        if ($transfer->acc1) {
            $acc1 = AccHead::with('currency:id,name')->select('id', 'aname', 'balance', 'currency_id')->find($transfer->acc1);
            if ($acc1 && ! $fromAccounts->contains('id', $acc1->id)) {
                // ضع acc1 في fromAccounts كي يظهر في القوائم
                $fromAccounts->push($acc1);
            }
        }

        if ($transfer->acc2) {
            $acc2 = AccHead::with('currency:id,name')->select('id', 'aname', 'balance', 'currency_id')->find($transfer->acc2);
            if ($acc2 && ! $toAccounts->contains('id', $acc2->id)) {
                // ضع acc2 في toAccounts كي يظهر في القوائم
                $toAccounts->push($acc2);
            }
        }

        // تأكد أن الموظف/المندوب موجودان في قائمة الحسابات الخاصة بالموظفين
        if ($transfer->emp_id) {
            $e1 = AccHead::select('id', 'aname', 'currency_id')->find($transfer->emp_id);
            if ($e1 && ! $employeeAccounts->contains('id', $e1->id)) {
                $employeeAccounts->push($e1);
            }
        }
        if ($transfer->emp2_id) {
            $e2 = AccHead::select('id', 'aname', 'currency_id')->find($transfer->emp2_id);
            if ($e2 && ! $employeeAccounts->contains('id', $e2->id)) {
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
            'fromAccounts' => $fromAccounts,
            'toAccounts' => $toAccounts,
            'cashAccounts' => $cashAccounts,
            'bankAccounts' => $bankAccounts,
            'employeeAccounts' => $employeeAccounts,
            'otherAccounts' => $otherAccounts,
            'pro_id' => $transfer->pro_id,
            'pro_type' => $transfer->pro_type,
            'costCenters' => $costCenters,
           //'branches' => $branches,
            'allCurrencies' => $allCurrencies,
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
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'pro_value' => 'required|numeric',
            'details' => 'nullable|string',
            'info' => 'nullable|string',
            'info2' => 'nullable|string',
            'info3' => 'nullable|string',
            'cost_center' => 'nullable|integer',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'currency_rate' => 'nullable|numeric',
        ]);

        // التحقق من تطابق العملات في حالة تفعيل تعدد العملات
        if (isMultiCurrencyEnabled()) {
            $acc1 = AccHead::find($validated['acc1']);
            $acc2 = AccHead::find($validated['acc2']);

            if ($acc1 && $acc2 && $acc1->currency_id != $acc2->currency_id) {
                return back()->withErrors(['currency_mismatch' => 'عذراً، يجب أن يكون للحسابين نفس العملة لإتمام التحويل.'])->withInput();
            }
        }

        // تحديد العملة وسعر الصرف - القيم الافتراضية id=1 و rate=1
        $currencyId = (int) ($request->get('currency_id') ?? 1);
        $currencyRate = (float) ($request->get('currency_rate') ?? 1);

        // التأكد من أن القيم صحيحة
        if ($currencyId <= 0) $currencyId = 1;
        if ($currencyRate <= 0) $currencyRate = 1;

        // ضرب القيمة في سعر الصرف للحفظ والقيود
        // pro_value المدخلة هي القيمة الأصلية (مثلاً 1000 دولار)
        // baseValue هي القيمة الأساسية بعد الضرب (مثلاً 1000 * 47 = 47000)
        $baseValue = (float) $validated['pro_value'] * $currencyRate;

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
                'pro_value' => $baseValue,
                'currency_id' => $currencyId,
                'currency_rate' => $currencyRate,
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
                    'total' => $baseValue,
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
                    'debit' => $baseValue,
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
                    'credit' => $baseValue,
                    'type' => 1,
                    'info' => $validated['info'] ?? null,
                    'op_id' => $oper->id,
                    'isdeleted' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('transfers.index')->with('success', 'تم تعديل التحويل بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $transfer = OperHead::with([
            'journalHead.dets.accHead',
            'acc1Head',
            'acc2Head',
            'employee',
            'type',
            'user',
        ])
            ->whereIn('pro_type', [3, 4, 5, 6])
            ->findOrFail($id);

        $typeSlugs = [
            3 => 'cash-to-cash',
            4 => 'cash-to-bank',
            5 => 'bank-to-cash',
            6 => 'bank-to-bank',
        ];

        $type = $typeSlugs[$transfer->pro_type] ?? 'transfer';

        return view('transfers.show', compact('transfer', 'type'));
    }

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
