<?php

namespace App\Http\Controllers;

use App\Models\{AccHead, OperHead, CostCenter, Voucher, JournalDetail, JournalHead, Project};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class VoucherController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('can:عرض سند قبض')->only(['index', 'create', 'store']);
    //     $this->middleware('can:عرض سند دفع')->only(['index', 'create', 'store']);
    // }

    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // افتراضي: عرض الكل

        $typeMapping = [
            'receipt' => 1,
            'payment' => 2,
            'exp-payment' => 3,
            'multi_payment' => 4,
            'multi_receipt' => 5,
            'all' => [1, 2, 3, 4, 5] // عرض الكل
        ];

        $query = Voucher::where('isdeleted', 0)->orderByDesc('pro_date');

        if ($type !== 'all') {
            $proType = $typeMapping[$type] ?? null;
            if ($proType) {
                $query->where('pro_type', $proType);
            }
        } else {
            // عرض الكل - يمكنك تحديد الأنواع اللي عايز تعرضها
            $query->whereIn('pro_type', $typeMapping['all']);
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

        $proTypeMap = [
            'receipt'      => 1,
            'payment'      => 2,
            'exp-payment'  => 2,

        ];

        $pro_type = $proTypeMap[$type] ?? null;
        $branches = userBranches();
        $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        $type = $request->get('type');
        $proTypeMap = [
            'receipt' => 1,
            'payment' => 2,
            'exp-payment' => 2,
        ];

        $pro_type = $proTypeMap[$type] ?? null;

        $lastProId = Operhead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
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
            ->select('id', 'aname')
            ->get();

        // حسابات المصاريف
        $expensesAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '57%') // غيّر الكود حسب النظام عندك
            ->select('id', 'aname')
            ->get();

        // المشاريع
        $projects = Project::all();

        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where(function ($query) {
                $query->where('is_fund', 'not', '1');
                $query->where('is_stock', 'not', '1 order by code');
            })
            ->select('id', 'aname', 'code')
            ->get();

        $costCenters = CostCenter::where('deleted', 0)
            ->get();

        return view(
            'vouchers.create',
            get_defined_vars()

        );
    }

    public function store(Request $request)
    {
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
            ->select('id', 'aname', 'code')
            ->get();

        // حسابات المصاريف
        $expensesAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '57%')
            ->select('id', 'aname', 'code')
            ->get();
        // باقي الحسابات
        $otherAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', '!=', 1)
            ->where('is_stock', '!=', 1)
            ->select('id', 'aname', 'code')
            ->orderBy('code')
            ->get();

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
        $validated = $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'pro_num'     => 'nullable|string',
            'emp_id'      => 'nullable|integer',
            'emp2_id'     => 'nullable|integer',
            'acc1'        => 'required|integer',
            'acc2'        => 'required|integer',
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
            $oper = Operhead::findOrFail($id);
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
            return redirect()->route('vouchers.index')->with('success', 'تم حذف السند والقيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    public function statistics(Request $request)
    {
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
