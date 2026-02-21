<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use App\Models\Project;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Settings\Models\Currency;

class VoucherController extends Controller
{
    // Constants for voucher types (من ProTypesSeeder)
    private const TYPE_RECEIPT = 1;              // سند قبض
    private const TYPE_PAYMENT = 2;              // سند دفع
    private const TYPE_EXP_PAYMENT = 101;        // سند دفع مصروفات
    private const TYPE_MULTI_RECEIPT = 32;       // سند قبض متعدد
    private const TYPE_MULTI_PAYMENT = 33;       // سند دفع متعدد
    
    // Permission mapping
    private const PERMISSION_MAP = [
        'receipt' => 'view recipt',
        'payment' => 'view payment',
        'exp-payment' => 'view exp-payment',
        'multi_payment' => 'view multi-payment',
        'multi_receipt' => 'view multi-receipt',
    ];
    
    private const CREATE_PERMISSION_MAP = [
        'receipt' => 'create recipt',
        'payment' => 'create payment',
        'exp-payment' => 'create exp-payment',
        'multi_payment' => 'create multi-payment',
        'multi_receipt' => 'create multi-receipt',
    ];
    
    private const EDIT_PERMISSION_MAP = [
        self::TYPE_RECEIPT => 'edit recipt',
        self::TYPE_PAYMENT => 'edit payment',
        self::TYPE_EXP_PAYMENT => 'edit exp-payment',
    ];
    
    private const DELETE_PERMISSION_MAP = [
        self::TYPE_RECEIPT => 'delete recipt',
        self::TYPE_PAYMENT => 'delete payment',
        self::TYPE_EXP_PAYMENT => 'delete exp-payment',
    ];

    public function __construct()
    {
        // Middleware for index - view permissions
        $this->middleware(function ($request, $next) {
            $this->checkViewPermission($request->get('type', 'all'));
            return $next($request);
        })->only(['index']);

        // Middleware for create/store - create permissions
        $this->middleware(function ($request, $next) {
            $this->checkCreatePermission($request->get('type'));
            return $next($request);
        })->only(['create', 'store']);

        // Middleware for edit/update - edit permissions
        $this->middleware(function ($request, $next) {
            $voucherId = $request->route('voucher');
            $voucher = Voucher::find($voucherId);
            
            if ($voucher) {
                $this->checkEditPermission($voucher);
            }
            
            return $next($request);
        })->only(['edit', 'update']);

        // Middleware for destroy - delete permissions
        $this->middleware(function ($request, $next) {
            $voucherId = $request->route('voucher');
            $voucher = Voucher::find($voucherId);
            
            if ($voucher) {
                $this->checkDeletePermission($voucher);
            }
            
            return $next($request);
        })->only(['destroy']);
    }

    /**
     * Check view permission based on voucher type
     */
    private function checkViewPermission(string $type): void
    {
        if ($type === 'all') {
            // للعرض الكامل، يجب أن يكون لديه صلاحية واحدة على الأقل
            $hasAnyPermission = Auth::user()->can('view recipt') || 
                              Auth::user()->can('view payment');
            
            if (!$hasAnyPermission) {
                abort(403, __('غير مصرح لك بعرض السندات'));
            }
        } else {
            // للأنواع المحددة
            $permission = self::PERMISSION_MAP[$type] ?? null;
            
            if ($permission && !Auth::user()->can($permission)) {
                abort(403, __('غير مصرح لك بعرض هذا النوع من السندات'));
            }
        }
    }

    /**
     * Check create permission based on voucher type
     */
    private function checkCreatePermission(?string $type): void
    {
        if (!$type) {
            $hasAnyPermission = Auth::user()->can('create recipt') ||
                              Auth::user()->can('create payment') ||
                              Auth::user()->can('create exp-payment');
            
            if (!$hasAnyPermission) {
                abort(403, __('غير مصرح لك بإنشاء أي نوع من السندات'));
            }
            return;
        }
        
        $permission = self::CREATE_PERMISSION_MAP[$type] ?? null;
        
        if ($permission && !Auth::user()->can($permission)) {
            abort(403, __('غير مصرح لك بإنشاء هذا النوع من السندات'));
        }
    }

    /**
     * Check edit permission for a voucher
     */
    private function checkEditPermission(Voucher $voucher): void
    {
        $pname = \App\Models\ProType::find($voucher->pro_type)?->pname;
        
        if ($pname === 'multi_payment') {
            $permission = 'edit multi-payment';
        } elseif ($pname === 'multi_receipt') {
            $permission = 'edit multi-receipt';
        } else {
            $permission = self::EDIT_PERMISSION_MAP[$voucher->pro_type] ?? null;
        }
        
        if ($permission && !Auth::user()->can($permission)) {
            abort(403, __('غير مصرح لك بتعديل هذا السند'));
        }
    }

    /**
     * Check delete permission for a voucher
     */
    private function checkDeletePermission(Voucher $voucher): void
    {
        $pname = \App\Models\ProType::find($voucher->pro_type)?->pname;
        
        if ($pname === 'multi_payment') {
            $permission = 'delete multi-payment';
        } elseif ($pname === 'multi_receipt') {
            $permission = 'delete multi-receipt';
        } else {
            $permission = self::DELETE_PERMISSION_MAP[$voucher->pro_type] ?? null;
        }
        
        if ($permission && !Auth::user()->can($permission)) {
            abort(403, __('غير مصرح لك بحذف هذا السند'));
        }
        
        // تحقق إضافي: المستخدم يمكنه حذف سنداته فقط
        if (Auth::user()->can('delete own vouchers only') && $voucher->user != Auth::id()) {
            abort(403, __('يمكنك حذف السندات التي قمت بإنشائها فقط'));
        }
    }

    /**
     * Display a listing of vouchers
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $query = Voucher::query()
            ->where('isdeleted', 0)
            ->with(['currency', 'type', 'account1', 'account2', 'emp1', 'user_id'])
            ->orderByDesc('pro_date');
        
        // تطبيق الفلتر حسب النوع
        $this->applyTypeFilter($query, $type);
        
        $vouchers = $query->paginate(15);
        $currentTypeInfo = $this->getTypeInfo($type);
        
        return view('vouchers.index', compact('vouchers', 'type', 'currentTypeInfo'));
    }

    /**
     * Apply type filter to query
     */
    private function applyTypeFilter($query, string $type): void
    {
        if ($type === 'all') {
            // عرض سندات القبض والدفع فقط (استبعاد المصاريف والسندات المتعددة)
            $query->whereIn('pro_type', [self::TYPE_RECEIPT, self::TYPE_PAYMENT]);
        } elseif (in_array($type, ['multi_payment', 'multi_receipt'])) {
            $proTypeIds = \App\Models\ProType::where('pname', $type)->pluck('id')->toArray();
            
            if (!empty($proTypeIds)) {
                $query->whereIn('pro_type', $proTypeIds);
            } else {
                $query->where('pro_type', 0); // لا توجد نتائج
            }
        } else {
            $typeMapping = [
                'receipt' => self::TYPE_RECEIPT,
                'payment' => self::TYPE_PAYMENT,
                'exp-payment' => self::TYPE_EXP_PAYMENT,
            ];
            
            $proType = $typeMapping[$type] ?? null;
            if ($proType) {
                $query->where('pro_type', $proType);
            }
        }
    }

    /**
     * Get type information for display
     */
    private function getTypeInfo(string $type): array
    {
        $typeInfo = [
            'receipt' => [
                'title' => __('سندات القبض العام'),
                'create_text' => __('إضافة سند قبض عام'),
                'icon' => 'fa-plus-circle',
                'color' => 'success',
            ],
            'payment' => [
                'title' => __('سندات الدفع العام'),
                'create_text' => __('إضافة سند دفع عام'),
                'icon' => 'fa-minus-circle',
                'color' => 'danger',
            ],
            'exp-payment' => [
                'title' => __('سندات دفع المصاريف'),
                'create_text' => __('إضافة سند دفع مصاريف'),
                'icon' => 'fa-credit-card',
                'color' => 'warning',
            ],
            'multi_payment' => [
                'title' => __('سندات الدفع متعددة'),
                'create_text' => __('إضافة سند دفع متعدد'),
                'icon' => 'fa-list-alt',
                'color' => 'info',
            ],
            'multi_receipt' => [
                'title' => __('سندات القبض متعددة'),
                'create_text' => __('إضافة سند قبض متعدد'),
                'icon' => 'fa-list-ul',
                'color' => 'primary',
            ],
            'all' => [
                'title' => __('سندات القبض والدفع'),
                'create_text' => __('إضافة سند جديد'),
                'icon' => 'fa-plus',
                'color' => 'primary',
                'show_dropdown' => true,
            ],
        ];
        
        return $typeInfo[$type] ?? $typeInfo['all'];
    }

    /**
     * Show the form for creating a new voucher
     */
    public function create(Request $request)
    {
        $type = $request->get('type');
        $this->checkCreatePermission($type);
        
        $proTypeMap = [
            'receipt' => self::TYPE_RECEIPT,
            'payment' => self::TYPE_PAYMENT,
            'exp-payment' => self::TYPE_EXP_PAYMENT,
        ];
        
        $pro_type = $proTypeMap[$type] ?? null;
        
        if (!$pro_type) {
            abort(404, __('نوع السند غير صحيح'));
        }
        
        // جلب البيانات المطلوبة
        $branches = userBranches();
        $allCurrencies = Currency::active()->with('latestRate')->get();
        $newProId = $this->getNextProId($pro_type);
        
        // جلب الحسابات
        $cashAccounts = $this->getCashAccounts();
        $bankAccounts = $this->getBankAccounts();
        $employeeAccounts = $this->getEmployeeAccounts();
        $expensesAccounts = $this->getExpensesAccounts();
        $paymentExpensesAccounts = $this->getPaymentExpensesAccounts();
        $otherAccounts = $this->getOtherAccounts();
        
        if ($expensesAccounts->isEmpty()) {
            $expensesAccounts = $otherAccounts;
        }
        
        $projects = Project::all();
        $costCenters = CostCenter::where('deleted', 0)->get();
        
        return view('vouchers.create', get_defined_vars());
    }

    /**
     * Get next pro_id for a given pro_type
     */
    private function getNextProId(int $proType): int
    {
        $lastProId = OperHead::where('pro_type', $proType)->max('pro_id') ?? 0;
        return $lastProId + 1;
    }

    /**
     * Get cash accounts
     */
    private function getCashAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where(function($q) {
                $q->where('acc_type', '3')
                  ->orWhere('acc_type', '4');
            })
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();
    }

    /**
     * Get bank accounts
     */
    private function getBankAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', '4')
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();
    }

    /**
     * Get employee accounts
     */
    private function getEmployeeAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'currency_id')
            ->get();
    }

    /**
     * Get expenses accounts
     */
    private function getExpensesAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '57%')
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'code', 'currency_id')
            ->orderBy('code')
            ->get();
    }

    /**
     * Get payment expenses accounts
     */
    private function getPaymentExpensesAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('acc_type', '7')
            ->with('currency:id,name')
            ->select('id', 'aname', 'balance', 'code', 'currency_id')
            ->orderBy('code')
            ->get();
    }

    /**
     * Get other accounts
     */
    private function getOtherAccounts()
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->with('currency:id,name')
            ->select('id', 'aname', 'code', 'balance', 'currency_id')
            ->orderBy('code')
            ->get();
    }

    /**
     * Store a newly created voucher
     */
    public function store(Request $request)
    {
        $proType = (int) $request->get('pro_type');
        $this->checkStorePermission($proType);
        
        $validated = $this->validateVoucherData($request);
        
        // التحقق من تطابق العملات
        if (isMultiCurrencyEnabled()) {
            $this->validateCurrencyMatch($validated['acc1'], $validated['acc2']);
        }
        
        $currencyData = $this->prepareCurrencyData($request);
        $baseValue = (float) $validated['pro_value'] * $currencyData['rate'];
        
        try {
            DB::beginTransaction();
            
            $newProId = $this->getNextProId($proType);
            
            // إنشاء السند
            $oper = $this->createOperHead($validated, $proType, $newProId, $baseValue, $currencyData);
            
            // إنشاء القيد المحاسبي
            $this->createJournalEntries($oper, $validated, $baseValue);
            
            DB::commit();
            
            return redirect()
                ->route('vouchers.index')
                ->with('success', __('تم حفظ السند والقيد المحاسبي بنجاح'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => __('حدث خطأ أثناء الحفظ') . ': ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Check store permission
     */
    private function checkStorePermission(int $proType): void
    {
        $permissionMap = [
            self::TYPE_RECEIPT => 'create recipt',
            self::TYPE_PAYMENT => 'create payment',
            self::TYPE_EXP_PAYMENT => 'create exp-payment',
        ];
        
        $permission = $permissionMap[$proType] ?? null;
        
        if ($permission && !Auth::user()->can($permission)) {
            abort(403, __('غير مصرح لك بإنشاء هذا النوع من السندات'));
        }
    }

    /**
     * Validate voucher data
     */
    private function validateVoucherData(Request $request): array
    {
        return $request->validate([
            'pro_id' => 'required|integer',
            'pro_date' => 'required|date',
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'emp_id' => 'nullable|integer|exists:acc_head,id',
            'emp2_id' => 'nullable|integer|exists:acc_head,id',
            'pro_value' => 'required|numeric|min:0.01',
            'project_id' => 'nullable|integer|exists:projects,id',
            'details' => 'nullable|string|max:500',
            'pro_serial' => 'nullable|string|max:50',
            'pro_num' => 'nullable|string|max:50',
            'cost_center' => 'nullable|integer|exists:cost_centers,id',
            'branch_id' => 'required|exists:branches,id',
            'currency_id' => 'nullable|integer',
            'currency_rate' => 'nullable|numeric|min:0.01',
        ]);
    }

    /**
     * Validate currency match between accounts
     */
    private function validateCurrencyMatch(int $acc1Id, int $acc2Id): void
    {
        $acc1 = AccHead::find($acc1Id);
        $acc2 = AccHead::find($acc2Id);
        
        if ($acc1 && $acc2 && $acc1->currency_id != $acc2->currency_id) {
            throw new \Exception(__('عذراً، يجب أن يكون للحسابين نفس العملة لإتمام السند'));
        }
    }

    /**
     * Prepare currency data
     */
    private function prepareCurrencyData(Request $request): array
    {
        $currencyId = (int) ($request->get('currency_id') ?? 1);
        $currencyRate = (float) ($request->get('currency_rate') ?? 1);
        
        return [
            'id' => max($currencyId, 1),
            'rate' => max($currencyRate, 1),
        ];
    }

    /**
     * Create OperHead record
     */
    private function createOperHead(array $validated, int $proType, int $newProId, float $baseValue, array $currencyData): OperHead
    {
        return OperHead::create([
            'pro_id' => $newProId,
            'pro_date' => $validated['pro_date'],
            'pro_type' => $proType,
            'acc1' => $validated['acc1'],
            'acc2' => $validated['acc2'],
            'pro_value' => $baseValue,
            'currency_id' => $currencyData['id'],
            'currency_rate' => $currencyData['rate'],
            'details' => $validated['details'] ?? null,
            'pro_serial' => $validated['pro_serial'] ?? null,
            'pro_num' => $validated['pro_num'] ?? null,
            'isdeleted' => 0,
            'tenant' => 0,
            'branch' => 1,
            'is_finance' => 1,
            'is_journal' => 1,
            'journal_type' => 2,
            'emp_id' => $validated['emp_id'] ?? null,
            'emp2_id' => $validated['emp2_id'] ?? null,
            'acc1_before' => 0,
            'acc1_after' => 0,
            'acc2_before' => 0,
            'acc2_after' => 0,
            'cost_center' => $validated['cost_center'] ?? null,
            'user' => Auth::id(),
            'project_id' => $validated['project_id'] ?? null,
            'branch_id' => $validated['branch_id'],
        ]);
    }

    /**
     * Create journal entries
     */
    private function createJournalEntries(OperHead $oper, array $validated, float $baseValue): void
    {
        $lastJournalId = JournalHead::max('journal_id') ?? 0;
        $newJournalId = $lastJournalId + 1;
        
        $journalHead = JournalHead::create([
            'journal_id' => $newJournalId,
            'total' => $baseValue,
            'date' => $validated['pro_date'],
            'op_id' => $oper->id,
            'pro_type' => $oper->pro_type,
            'details' => $validated['details'] ?? null,
            'user' => Auth::id(),
            'branch_id' => $validated['branch_id'],
        ]);
        
        // قيد مدين
        JournalDetail::create([
            'journal_id' => $journalHead->journal_id,
            'account_id' => $validated['acc1'],
            'debit' => $baseValue,
            'credit' => 0,
            'type' => 0,
            'info' => $validated['details'] ?? null,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'branch_id' => $validated['branch_id'],
        ]);
        
        // قيد دائن
        JournalDetail::create([
            'journal_id' => $journalHead->journal_id,
            'account_id' => $validated['acc2'],
            'debit' => 0,
            'credit' => $baseValue,
            'type' => 1,
            'info' => $validated['details'] ?? null,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'branch_id' => $validated['branch_id'],
        ]);
    }

    /**
     * Display the specified voucher
     */
    public function show(int $id)
    {
        $voucher = Voucher::with([
            'type',
            'account1',
            'account2',
            'emp1',
            'user_id',
            'currency',
        ])->findOrFail($id);
        
        $this->checkShowPermission($voucher);
        
        $typeMap = [
            self::TYPE_RECEIPT => 'receipt',
            self::TYPE_PAYMENT => 'payment',
            self::TYPE_EXP_PAYMENT => 'exp-payment',
        ];
        
        $type = $typeMap[$voucher->pro_type] ?? 'receipt';
        
        return view('vouchers.show', compact('voucher', 'type'));
    }

    /**
     * Check show permission
     */
    private function checkShowPermission(Voucher $voucher): void
    {
        $permissionMap = [
            self::TYPE_RECEIPT => 'view recipt',
            self::TYPE_PAYMENT => 'view payment',
            self::TYPE_EXP_PAYMENT => 'view exp-payment',
        ];
        
        $permission = $permissionMap[$voucher->pro_type] ?? null;
        
        if ($permission && !Auth::user()->can($permission)) {
            abort(403, __('غير مصرح لك بعرض هذا السند'));
        }
    }

    /**
     * Show the form for editing the specified voucher
     */
    public function edit(int $id)
    {
        $voucher = Voucher::findOrFail($id);
        $this->checkEditPermission($voucher);
        
        // إذا كان سند متعدد، توجيه للـ controller المناسب
        $pname = \App\Models\ProType::find($voucher->pro_type)?->pname;
        if (in_array($pname, ['multi_payment', 'multi_receipt'])) {
            return redirect()->route('multi-vouchers.edit', $id);
        }
        
        $typeMap = [
            self::TYPE_RECEIPT => 'receipt',
            self::TYPE_PAYMENT => 'payment',
            self::TYPE_EXP_PAYMENT => 'exp-payment',
        ];
        
        $type = $typeMap[$voucher->pro_type] ?? 'receipt';
        
        // جلب البيانات المطلوبة
        $branches = userBranches();
        $allCurrencies = Currency::active()->with('latestRate')->get();
        $cashAccounts = $this->getCashAccounts();
        $bankAccounts = $this->getBankAccounts();
        $employeeAccounts = $this->getEmployeeAccounts();
        $expensesAccounts = $this->getExpensesAccounts();
        $paymentExpensesAccounts = $this->getPaymentExpensesAccounts();
        $otherAccounts = $this->getOtherAccounts();
        
        if ($expensesAccounts->isEmpty()) {
            $expensesAccounts = $otherAccounts;
        }
        
        $projects = Project::all();
        $costCenters = CostCenter::where('deleted', 0)->get();
        
        return view('vouchers.edit', compact(
            'voucher',
            'type',
            'cashAccounts',
            'bankAccounts',
            'employeeAccounts',
            'otherAccounts',
            'expensesAccounts',
            'paymentExpensesAccounts',
            'costCenters',
            'projects',
            'branches',
            'allCurrencies'
        ));
    }

    /**
     * Update the specified voucher
     */
    public function update(Request $request, int $id)
    {
        $voucher = Voucher::findOrFail($id);
        $this->checkEditPermission($voucher);
        
        $validated = $request->validate([
            'pro_type' => 'required|integer',
            'pro_date' => 'required|date',
            'pro_num' => 'nullable|string|max:50',
            'pro_serial' => 'nullable|string|max:50',
            'emp_id' => 'nullable|integer',
            'emp2_id' => 'nullable|integer',
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'pro_value' => 'required|numeric|min:0.01',
            'details' => 'nullable|string|max:500',
            'info' => 'nullable|string|max:500',
            'info2' => 'nullable|string|max:500',
            'info3' => 'nullable|string|max:500',
            'cost_center' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'branch_id' => 'required|exists:branches,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'currency_rate' => 'nullable|numeric|min:0.01',
        ]);
        
        // التحقق من تطابق العملات
        if (isMultiCurrencyEnabled()) {
            $this->validateCurrencyMatch($validated['acc1'], $validated['acc2']);
        }
        
        $currencyData = $this->prepareCurrencyData($request);
        $baseValue = (float) $validated['pro_value'] * $currencyData['rate'];
        
        try {
            DB::beginTransaction();
            
            $this->updateOperHead($voucher, $validated, $baseValue, $currencyData);
            $this->updateJournalEntries($voucher, $validated, $baseValue);
            
            DB::commit();
            
            return redirect()
                ->route('vouchers.index')
                ->with('success', __('تم تعديل السند بنجاح'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => __('حدث خطأ') . ': ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update OperHead record
     */
    private function updateOperHead(Voucher $voucher, array $validated, float $baseValue, array $currencyData): void
    {
        $oper = OperHead::findOrFail($voucher->id);
        
        $oper->update([
            'pro_date' => $validated['pro_date'],
            'pro_num' => $validated['pro_num'] ?? null,
            'pro_serial' => $validated['pro_serial'] ?? null,
            'acc1' => $validated['acc1'],
            'acc2' => $validated['acc2'],
            'pro_value' => $baseValue,
            'currency_id' => $currencyData['id'],
            'currency_rate' => $currencyData['rate'],
            'details' => $validated['details'] ?? null,
            'emp_id' => $validated['emp_id'] ?? null,
            'emp2_id' => $validated['emp2_id'] ?? null,
            'cost_center' => $validated['cost_center'] ?? null,
            'user' => Auth::id(),
            'info' => $validated['info'] ?? null,
            'info2' => $validated['info2'] ?? null,
            'info3' => $validated['info3'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'branch_id' => $validated['branch_id'],
        ]);
    }

    /**
     * Update journal entries
     */
    private function updateJournalEntries(Voucher $voucher, array $validated, float $baseValue): void
    {
        $journalHead = JournalHead::where('op_id', $voucher->id)->first();
        
        if (!$journalHead) {
            return;
        }
        
        $journalHead->update([
            'total' => $baseValue,
            'date' => $validated['pro_date'],
            'pro_type' => $validated['pro_type'],
            'details' => $validated['details'] ?? null,
            'user' => Auth::id(),
            'branch_id' => $validated['branch_id'],
        ]);
        
        // حذف التفاصيل القديمة
        JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
        
        // إنشاء تفاصيل جديدة (مدين)
        JournalDetail::create([
            'journal_id' => $journalHead->journal_id,
            'account_id' => $validated['acc1'],
            'debit' => $baseValue,
            'credit' => 0,
            'type' => 0,
            'info' => $validated['info'] ?? null,
            'op_id' => $voucher->id,
            'isdeleted' => 0,
            'branch_id' => $validated['branch_id'],
        ]);
        
        // إنشاء تفاصيل جديدة (دائن)
        JournalDetail::create([
            'journal_id' => $journalHead->journal_id,
            'account_id' => $validated['acc2'],
            'debit' => 0,
            'credit' => $baseValue,
            'type' => 1,
            'info' => $validated['info'] ?? null,
            'op_id' => $voucher->id,
            'isdeleted' => 0,
            'branch_id' => $validated['branch_id'],
        ]);
    }

    /**
     * Remove the specified voucher
     */
    public function destroy(int $id)
    {
        $voucher = Voucher::findOrFail($id);
        $this->checkDeletePermission($voucher);
        
        try {
            DB::beginTransaction();
            
            $oper = OperHead::findOrFail($id);
            
            // حذف القيود المحاسبية المرتبطة
            $journalHead = JournalHead::where('op_id', $oper->id)->first();
            
            if ($journalHead) {
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
                $journalHead->delete();
            }
            
            // حذف السند
            $oper->delete();
            
            DB::commit();
            
            return redirect()
                ->route('vouchers.index')
                ->with('success', __('تم حذف السند والقيد بنجاح'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => __('حدث خطأ أثناء الحذف') . ': ' . $e->getMessage()]);
        }
    }

    /**
     * Display vouchers statistics
     */
    public function statistics(Request $request)
    {
        if (!Auth::user()->can('view vouchers-statistics')) {
            abort(403, __('غير مصرح لك بعرض إحصائيات السندات'));
        }
        
        $proTypeMapping = [
            self::TYPE_RECEIPT => __('سندات القبض العام'),
            self::TYPE_PAYMENT => __('سندات الدفع العام'),
            self::TYPE_EXP_PAYMENT => __('سندات دفع المصاريف'),
        ];
        
        $statistics = OperHead::where('isdeleted', 0)
            ->whereIn('pro_type', array_keys($proTypeMapping))
            ->groupBy('pro_type')
            ->select(
                'pro_type',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(pro_value) as total_value')
            )
            ->get()
            ->keyBy('pro_type')
            ->map(function ($item) use ($proTypeMapping) {
                return [
                    'title' => $proTypeMapping[$item->pro_type],
                    'count' => $item->total_count,
                    'value' => $item->total_value,
                ];
            });
        
        $overallTotal = OperHead::where('isdeleted', 0)
            ->whereIn('pro_type', array_keys($proTypeMapping))
            ->select(
                DB::raw('COUNT(*) as overall_count'),
                DB::raw('SUM(pro_value) as overall_value')
            )
            ->first();
        
        // إضافة الأنواع التي لا تحتوي على سندات
        foreach ($proTypeMapping as $typeId => $title) {
            if (!$statistics->has($typeId)) {
                $statistics->put($typeId, [
                    'title' => $title,
                    'count' => 0,
                    'value' => 0,
                ]);
            }
        }
        
        $sortedStatistics = $statistics->sortKeys();
        
        return view('vouchers.statistics', compact('sortedStatistics', 'overallTotal'));
    }
}
