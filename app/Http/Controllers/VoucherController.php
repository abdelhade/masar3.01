<?php

namespace App\Http\Controllers;

use Modules\Accounts\Models\AccHead;
use App\Models\{OperHead, CostCenter, Voucher, JournalDetail, JournalHead, Project};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class VoucherController extends Controller
{

public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $type = $request->get('type', 'all'); // جلب النوع من URL

            // ✅ حالة 1: إذا طلب عرض "جميع السندات"
            if ($type === 'all') {
                // تحقق: هل عنده أي صلاحية من الثلاثة؟
                if (!Auth::user()->can('view recipt') &&
                    !Auth::user()->can('view payment') &&
                    !Auth::user()->can('view exp-payment')) {
                    abort(403, 'غير مصرح لك بعرض أي سندات');
                }
            } else {
                // ✅ حالة 2: إذا طلب نوع محدد (receipt, payment, exp-payment)
                // ربط كل نوع بصلاحيته المطلوبة
                $permissionMap = [
                    'receipt' => 'view recipt',           // سندات القبض
                    'payment' => 'view payment',           // سندات الدفع
                    'exp-payment' => 'view exp-payment',   // سندات المصاريف
                    'multi_payment' => 'view multi-payment',     // سندات دفع متعددة
                    'multi_receipt' => 'view multi-receipt',     // سندات قبض متعددة
                ];

                // تحقق: هل عنده صلاحية النوع المطلوب؟
                if (isset($permissionMap[$type]) && !Auth::user()->can($permissionMap[$type])) {
                    abort(403, 'غير مصرح لك بعرض هذا النوع من السندات');
                }
            }

            return $next($request); // السماح بالمرور
        })->only(['index']); // تطبيق الحماية على index فقط
    $this->middleware(function ($request, $next) {
            $type = $request->get('type'); // نوع السند المطلوب إنشاؤه

            // ربط كل نوع بصلاحيته المطلوبة للإنشاء
            $permissionMap = [
                'receipt' => 'create recipt',
                'payment' => 'create payment',
                'exp-payment' => 'create exp-payment',
                'multi_payment' => 'create multi-payment',
                'multi_receipt' => 'create multi-receipt',
            ];

            // تحقق: هل عنده صلاحية إنشاء هذا النوع؟
            if (isset($permissionMap[$type]) && !Auth::user()->can($permissionMap[$type])) {
                abort(403, 'غير مصرح لك بإنشاء هذا النوع من السندات');
            }

            // إذا لم يتم تحديد نوع، التحقق من وجود أي صلاحية إنشاء
            if (!$type) {
                $hasAnyCreatePermission = Auth::user()->can( 'create recipt') ||
                                        Auth::user()->can('create payment') ||
                                        Auth::user()->can('create exp-payment') ||
                                        Auth::user()->can('create multi-payment') ||
                                        Auth::user()->can('create multi-receipt');

                if (!$hasAnyCreatePermission) {
                    abort(403, 'غير مصرح لك بإنشاء أي نوع من السندات');
                }
            }

            return $next($request);
        })->only(['create', 'store']);

        $this->middleware(function ($request, $next) {
            $voucherId = $request->route('voucher');
            $voucher = Voucher::find($voucherId);

            if ($voucher) {
                // ربط كل pro_type بصلاحية التعديل المطلوبة
                $typePermissionMap = [
                    1 => 'edit recipt',      // سندات قبض
                    2 => 'edit payment',      // سندات دفع
                    3 => 'edit exp-payment',  // سندات مصاريف
                    // بالنسبة للسندات المتعددة، نستخدم نفس الصلاحيات أو نخصصها
                ];

                // تحديد نوع السند من الـ pro_type
                $proType = $voucher->pro_type;

                // للسندات المتعددة، نتحقق من الـ pname
                $pname = \App\Models\ProType::find($proType)?->pname;
                if ($pname === 'multi_payment') {
                    $requiredPermission = 'edit multi-payment';
                } elseif ($pname === 'multi_receipt') {
                    $requiredPermission = 'edit multi-receipt';
                } else {
                    $requiredPermission = $typePermissionMap[$proType] ?? null;
                }

                // تحقق: هل عنده الصلاحية المطلوبة؟
                if ($requiredPermission && !Auth::user()->can($requiredPermission)) {
                    abort(403, 'غير مصرح لك بتعديل هذا السند');
                }
            }

            return $next($request);
        })->only(['edit', 'update']);


        $this->middleware(function ($request, $next) {
            $voucherId = $request->route('voucher');
            $voucher = Voucher::find($voucherId);

            if ($voucher) {
                // ربط كل pro_type بصلاحية الحذف المطلوبة
                $typePermissionMap = [
                    1 => 'delete recipt',      // سندات قبض
                    2 => 'delete payment',      // سندات دفع
                    3 => 'delete exp-payment',  // سندات مصاريف
                    // بالنسبة للسندات المتعددة، نستخدم نفس الصلاحيات أو نخصصها
                ];

                // تحديد نوع السند من الـ pro_type
                $proType = $voucher->pro_type;

                // للسندات المتعددة، نتحقق من الـ pname
                $pname = \App\Models\ProType::find($proType)?->pname;
                if ($pname === 'multi_payment') {
                    $requiredPermission = 'delete multi-payment';
                } elseif ($pname === 'multi_receipt') {
                    $requiredPermission = 'delete multi-receipt';
                } else {
                    $requiredPermission = $typePermissionMap[$proType] ?? null;
                }

                // تحقق: هل عنده الصلاحية المطلوبة؟
                if ($requiredPermission && !Auth::user()->can($requiredPermission)) {
                    abort(403, 'غير مصرح لك بحذف هذا السند');
                }

                // تحقق إضافي: يمكن للمستخدم حذف السندات التي قام بإنشائها فقط
                if (Auth::user()->can('delete own vouchers only') && $voucher->user != Auth::id()) {
                    abort(403, 'يمكنك حذف السندات التي قمت بإنشائها فقط');
                }
            }

            return $next($request);
        })->only(['destroy']);

    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // افتراضي: عرض الكل

        // If the request is for multi vouchers, delegate to MultiVoucherController
        if (in_array($type, ['multi_payment', 'multi_receipt'])) {
            return redirect()->route('multi-vouchers.index', ['type' => $type]);
        }
    // تحديد النوع التلقائي بناءً على صلاحيات المستخدم
        if ($type === 'all') {
            $userPermissions = [];
            if (Auth::user()->can('view recipt')) {
                $userPermissions[] = 'receipt';
            }
            if (Auth::user()->can('view payment')) {
                $userPermissions[] = 'payment';
            }
            if (Auth::user()->can('view exp-payment')) {
                $userPermissions[] = 'exp-payment';
            }

            // إذا كان لديه صلاحية واحدة فقط، اعرض هذا النوع مباشرة
            if (count($userPermissions) === 1) {
                $type = $userPermissions[0];
            }
        }
        $typeMapping = [
            'receipt' => 1,
            'payment' => 2,
            'exp-payment' => 3,
        ];

        $query = Voucher::where('isdeleted', 0)->orderByDesc('pro_date');

        if ($type !== 'all') {
            if (in_array($type, ['multi_payment', 'multi_receipt'])) {
                // find all ProType ids that match this pname (covers multiple pro_type ids)
                $proTypeIds = \App\Models\ProType::where('pname', $type)->pluck('id')->toArray();
                if (!empty($proTypeIds)) {
                    $query->whereIn('pro_type', $proTypeIds);
                } else {
                    // fallback to an impossible id to return empty
                    $query->where('pro_type', 0);
                }
            } else {
                $proType = $typeMapping[$type] ?? null;
                if ($proType) {
                    $query->where('pro_type', $proType);
                }
            }
        }

        $vouchers = $query->get();

        // معلومات النوع لعرضها في الصفحة
        $typeInfo = [
            'receipt' => [
                'title' => 'سندات القبض العام',
                'create_text' => 'إضافة سند قبض عام',
                'icon' => 'fa-plus-circle',
                'color' => 'success'
            ],
            'payment' => [
                'title' => 'سندات الدفع العام',
                'create_text' => 'إضافة سند دفع عام',
                'icon' => 'fa-minus-circle',
                'color' => 'danger'
            ],
            'exp-payment' => [
                'title' => 'سندات دفع المصاريف',
                'create_text' => 'إضافة سند دفع مصاريف',
                'icon' => 'fa-credit-card',
                'color' => 'warning'
            ],
            'multi_payment' => [
                'title' => 'سندات الدفع متعددة',
                'create_text' => 'إضافة سند دفع متعدد',
                'icon' => 'fa-list-alt',
                'color' => 'info'
            ],
            'multi_receipt' => [
                'title' => 'سندات القبض متعددة',
                'create_text' => 'إضافة سند قبض متعدد',
                'icon' => 'fa-list-ul',
                'color' => 'primary'
            ],
            'all' => [
                'title' => 'جميع السندات',
                'create_text' => 'إضافة سند جديد',
                'icon' => 'fa-plus',
                'color' => 'primary',
                'show_dropdown' => true
            ]
        ];

        $currentTypeInfo = $typeInfo[$type] ?? $typeInfo['all'];

        return view('vouchers.index', compact('vouchers', 'type', 'currentTypeInfo'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type');

         $permissionMap = [
            'receipt' => 'create recipt',
            'payment' => 'create payment',
            'exp-payment' => 'create exp-payment',
            'multi_payment' => 'create multi-payment',
            'multi_receipt' => 'create multi-receipt',
        ];

        if (isset($permissionMap[$type]) && !Auth::user()->can($permissionMap[$type])) {
            abort(403, 'غير مصرح لك بإنشاء هذا النوع من السندات');
        }
        // Map request type to pro_type used in operhead
        $proTypeMap = [
            'receipt' => 1,
            'payment' => 2,
            // exp-payment is a separate type (3) in the UI
            'exp-payment' => 3,
        ];

        $pro_type = $proTypeMap[$type] ?? null;
        $branches = userBranches();

        // Determine next pro_id for the given pro_type
        $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // حسابات الصندوق
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1101%')
            ->select('id', 'aname', 'balance')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%') // غيّر الكود حسب النظام عندك
            ->select('id', 'aname', 'balance')
            ->get();

        // حسابات المصاريف
        $expensesAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '57%') // غيّر الكود حسب النظام عندك
            ->select('id', 'aname', 'balance', 'code')
            ->orderBy('code')
            ->get();

        // If no expense-specific accounts found, fallback to otherAccounts later (to avoid empty dropdowns)

        // المشاريع
        $projects = Project::all();

        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->select('id', 'aname', 'code', 'balance')
            ->orderBy('code')
            ->get();

        if ($expensesAccounts->isEmpty()) {
            $expensesAccounts = $otherAccounts;
        }

        $costCenters = CostCenter::where('deleted', 0)
            ->get();

        return view(
            'vouchers.create',
            get_defined_vars()

        );
    }

    public function store(Request $request)
    {
            $type = $request->get('pro_type');
        $permissionMap = [
            1 => 'create recipt',
            2 => 'create payment',
            3 => 'create exp-payment',
        ];

        $requiredPermission = $permissionMap[$type] ?? null;
        if ($requiredPermission && !Auth::user()->can($requiredPermission)) {
            abort(403, 'غير مصرح لك بإنشاء هذا النوع من السندات');
        }

        $validated = $request->validate([
            'pro_id' => 'required|integer',
            'pro_date' => 'required|date',
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'emp_id' => 'nullable|integer|exists:acc_head,id',
            'emp2_id' => 'nullable|integer|exists:acc_head,id',
            'pro_value' => 'required|numeric',
            'project_id' => 'nullable|integer|exists:projects,id',
            'details' => 'nullable|string',
            'pro_serial' => 'nullable|string',
            'pro_num' => 'nullable|string',
            'cost_center' => 'nullable|integer|exists:cost_centers,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            DB::beginTransaction();

            // تحديد رقم العملية الجديد بناءً على pro_type
            $pro_type = $request->get('pro_type');
            if (!$pro_type) {
                throw new \Exception('نوع العملية غير محدد.');
            }
            $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            // إنشاء سجل جديد في جدول operhead
            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_date' => $validated['pro_date'],
                'pro_type' => $pro_type,
                'acc1' => $validated['acc1'],
                'acc2' => $validated['acc2'],
                'pro_value' => $validated['pro_value'],
                'details' => $request['details'] ?? null,
                'pro_serial' => $request['pro_serial'] ?? null,
                'pro_num' => $request['pro_num'] ?? null,
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
                'project_id' => $validated['project_id'] ?? null,
                'branch_id' => $validated['branch_id'],

            ]);

            // إنشاء رأس القيد (JournalHead)
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;
            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total'      => $validated['pro_value'],
                'date'       => $validated['pro_date'],
                'op_id'      => $oper->id,
                'pro_type'   => $pro_type,
                'details'    => $request['details'] ?? null,
                'user'       => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // إنشاء تفاصيل القيد (مدين)
            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => $validated['acc1'],
                'debit'      => $validated['pro_value'],
                'credit'     => 0,
                'type'       => 0,
                'info'       => $request['details'] ?? null,
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
                'branch_id' => $validated['branch_id'],
            ]);
            // إنشاء تفاصيل القيد (دائن)
            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => $validated['acc2'],
                'debit'      => 0,
                'credit'     => $validated['pro_value'],
                'type'       => 1,
                'info'       => $request['details'] ?? null,
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
                'branch_id' => $validated['branch_id'],
            ]);

            DB::commit();
            return redirect()->route('vouchers.index')->with('success', 'تم حفظ السند والقيد المحاسبي بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()])->withInput();
        }
    }
    public function show($id) {}

    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);
         // التحقق من الصلاحية قبل التعديل
        $typePermissionMap = [
            1 => 'edit recipt',
            2 => 'edit payment',
            3 => 'edit exp-payment',
        ];

        $requiredPermission = $typePermissionMap[$voucher->pro_type] ?? null;
        if ($requiredPermission && !Auth::user()->can($requiredPermission)) {
            abort(403, 'غير مصرح لك بتعديل هذا السند');
        }
        // If this operation is a multi-voucher type, redirect to the MultiVoucher edit form
        $pname = \App\Models\ProType::find($voucher->pro_type)?->pname;
        if (in_array($pname, ['multi_payment', 'multi_receipt'])) {
            return redirect()->route('multi-vouchers.edit', $id);
        }

        $type = $voucher->pro_type;



        // حسابات الصندوق
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1101%')
            ->select('id', 'aname', 'code', 'balance')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '2102%')
            ->select('id', 'aname', 'code', 'balance')
            ->get();

        // حسابات المصاريف
        $expensesAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '57%')
            ->select('id', 'aname', 'code', 'balance')
            ->orderBy('code')
            ->get();
        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->select('id', 'aname', 'code', 'balance')
            ->orderBy('code')
            ->get();

        if ($expensesAccounts->isEmpty()) {
            $expensesAccounts = $otherAccounts;
        }

        $costCenters = CostCenter::where('deleted', 0)
            ->get();

        $projects = Project::all();


        return view('vouchers.edit', compact(
            'voucher',
            'type',
            'cashAccounts',
            'employeeAccounts',
            'otherAccounts',
            'expensesAccounts',
            'costCenters'
        ));
    }


    public function update(Request $request, $id)
    {
         $voucher = Voucher::findOrFail($id);

        // التحقق من الصلاحية قبل التحديث
        $typePermissionMap = [
            1 => 'edit recipt',
            2 => 'edit payment',
            3 => 'edit exp-payment',
        ];

        $requiredPermission = $typePermissionMap[$voucher->pro_type] ?? null;
        if ($requiredPermission && !Auth::user()->can($requiredPermission)) {
            abort(403, 'غير مصرح لك بتعديل هذا السند');
        }
        $validated = $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'pro_num'     => 'nullable|string',
            'emp_id'      => 'nullable|integer',
            'emp2_id'     => 'nullable|integer',
            'acc1'        => 'required|integer|exists:acc_head,id',
            'acc2'        => 'required|integer|exists:acc_head,id',
            'pro_value'   => 'required|numeric',
            'details'     => 'nullable|string',
            'info'        => 'nullable|string',
            'info2'       => 'nullable|string',
            'info3'       => 'nullable|string',
            'cost_center' => 'nullable|integer',
            'project_id'  => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // تحديث operhead
            $oper = OperHead::findOrFail($id);
            $oper->update([
                'pro_date'     => $validated['pro_date'],
                'pro_num'      => $validated['pro_num'] ?? null,
                'pro_serial'   => $request['pro_serial'] ?? null,
                'acc1'         => $validated['acc1'],
                'acc2'         => $validated['acc2'],
                'pro_value'    => $validated['pro_value'],
                'details'      => $validated['details'] ?? null,
                'emp_id'       => $validated['emp_id'],
                'emp2_id'      => $validated['emp2_id'] ?? null,
                'acc1_before'  => 0,
                'acc1_after'   => 0,
                'acc2_before'  => 0,
                'acc2_after'   => 0,
                'cost_center'  => $validated['cost_center'] ?? null,
                'user'         => Auth::id(),
                'info'         => $validated['info'] ?? null,
                'info2'        => $validated['info2'] ?? null,
                'info3'        => $validated['info3'] ?? null,
                'project_id'   => $validated['project_id'] ?? null,
            ]);

            // تحديث journal_head
            $journalHead = JournalHead::where('op_id', $oper->id)->first();
            if ($journalHead) {
                $journalHead->update([
                    'total'     => $validated['pro_value'],
                    'date'      => $validated['pro_date'],
                    'pro_type'  => $validated['pro_type'],
                    'details'   => $validated['details'] ?? null,
                    'user'      => Auth::id(),
                ]);

                $journalId = $journalHead->journal_id;

                // حذف التفاصيل القديمة
                JournalDetail::where('journal_id', $journalId)->delete();

                // إنشاء تفاصيل جديدة (مدين)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $validated['acc1'],
                    'debit'      => $validated['pro_value'],
                    'credit'     => 0,
                    'type'       => 0,
                    'info'       => $validated['info'] ?? null,
                    'op_id'      => $oper->id,
                    'isdeleted'  => 0,
                    'branch_id'  => $journalHead->branch_id ?? null,
                ]);

                // إنشاء تفاصيل جديدة (دائن)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $validated['acc2'],
                    'debit'      => 0,
                    'credit'     => $validated['pro_value'],
                    'type'       => 1,
                    'info'       => $validated['info'] ?? null,
                    'op_id'      => $oper->id,
                    'isdeleted'  => 0,
                    'branch_id'  => $journalHead->branch_id ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('vouchers.index')->with('success', 'تم تعديل السند بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])->withInput();
        }
    }
    public function destroy(string $id)
    {
          $voucher = Voucher::findOrFail($id);

        // التحقق من الصلاحية قبل الحذف
        $typePermissionMap = [
            1 => 'delete recipt',
            2 => 'delete payment',
            3 => 'delete exp-payment',
        ];

        $requiredPermission = $typePermissionMap[$voucher->pro_type] ?? null;
        if ($requiredPermission && !Auth::user()->can($requiredPermission)) {
            abort(403, 'غير مصرح لك بحذف هذا السند');
        }
        try {
            DB::beginTransaction();

            $voucher = OperHead::findOrFail($id);

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
            return redirect()->route('vouchers.index')->with('success', 'تم حذف السند والقيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    public function statistics(Request $request)
    {
                if (! Auth::user()->can('view vouchers-statistics')) {
            abort(403, 'غير مصرح لك بعرض إحصائيات السندات المتعددة');
        }
        // تحديد الأنواع
        $proTypeMapping = [
            1 => 'سندات القبض العام',
            2 => 'سندات الدفع العام',
            3 => 'سندات دفع المصاريف',
            4 => 'سندات الدفع متعددة',
            5 => 'سندات القبض متعددة',
        ];

        // جلب الإحصائيات: عدد وإجمالي القيمة لكل نوع سند
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

        // جلب إجمالي جميع السندات
        $overallTotal = OperHead::where('isdeleted', 0)
            ->whereIn('pro_type', array_keys($proTypeMapping))
            ->select(
                DB::raw('COUNT(*) as overall_count'),
                DB::raw('SUM(pro_value) as overall_value')
            )
            ->first();

        // لإضافة الأنواع التي لا تحتوي على سندات (لضمان ظهورها في الإحصائيات بقيمة صفر)
        foreach ($proTypeMapping as $typeId => $title) {
            if (!$statistics->has($typeId)) {
                $statistics->put($typeId, [
                    'title' => $title,
                    'count' => 0,
                    'value' => 0,
                ]);
            }
        }

        // إعادة ترتيب البيانات حسب الـ pro_type
        $sortedStatistics = $statistics->sortKeys();

        return view('vouchers.statistics', compact('sortedStatistics', 'overallTotal'));
    }
}
