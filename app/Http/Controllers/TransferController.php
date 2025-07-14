<?php

namespace App\Http\Controllers;

use App\Models\{AccHead, OperHead, Transfer, JournalHead};
use App\Models\JournalDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class TransferController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:عرض التحويلات النقدية')->only(['index']);
        $this->middleware('can:إضافة التحويلات النقدية')->only(['create', 'store']);
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


        $type = $request->get('type');
        $proTypeMap = [
            'receipt' => 1,
            'payment' => 2,
        ];

        $pro_type = $proTypeMap[$type] ?? null;

        $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // حسابات الصندوق
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '121%')
            ->select('id', 'aname')
            ->get();

        // حسابات البنك
        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '124%')
            ->select('id', 'aname')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
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
        return view('transfers.create', compact(
            'type',
            'pro_type',
            'newProId',
            'cashAccounts',
            'bankAccounts',
            'employeeAccounts',
            'otherAccounts'
        ));
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'pro_type'   => 'required|integer',
            'pro_date'   => 'required|date',
            'pro_num'    => 'nullable|string',
            'emp_id'     => 'required|integer',
            'emp2_id'    => 'nullable|integer',
            'acc1'       => 'required|integer',
            'acc2'       => 'required|integer',
            'pro_value'  => 'required|numeric', // نفس قيمة المدين والدائن
            'details'    => 'nullable|string',
            'info'       => 'nullable|string',
            'info2'      => 'nullable|string',
            'info3'      => 'nullable|string',
            'cost_center' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // الحصول على pro_id جديد حسب نوع العملية
            $lastProId = Operhead::where('pro_type', $validated['pro_type'])->max('pro_id');
            $newProId = $lastProId ? $lastProId + 1 : 1;

            $oper = Operhead::create([
                'pro_id'        => $newProId,
                'pro_date'      => $validated['pro_date'],
                'pro_type'      => $validated['pro_type'],
                'pro_num'       => $validated['pro_num'] ?? null,
                'pro_serial'    => $request['pro_serial'] ?? null,
                'acc1'          => $validated['acc1'],
                'acc2'          => $validated['acc2'],
                'pro_value'     => $validated['pro_value'],
                'details'       => $validated['details'] ?? null,
                'isdeleted'     => 0,
                'tenant'        => 0,
                'branch'        => 1,
                'is_finance'    => 1,
                'is_journal'    => 1,
                'journal_type'  => 2,
                'emp_id'        => $validated['emp_id'],
                'emp2_id'       => $validated['emp2_id'] ?? null,
                'acc1_before'   => 0,
                'acc1_after'    => 0,
                'acc2_before'   => 0,
                'acc2_after'    => 0,
                'cost_center'   => $validated['cost_center'] ?? null,
                'user'          => Auth::id(),
                'info'          => $validated['info'] ?? null,
                'info2'         => $validated['info2'] ?? null,
                'info3'         => $validated['info3'] ?? null,
            ]);

            // إنشاء journal_head
            $lastJournalId = JournalHead::max('journal_id');
            $newJournalId = $lastJournalId ? $lastJournalId + 1 : 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total'      => $validated['pro_value'],
                'date'       => $validated['pro_date'],
                'op_id'      => $oper->id,
                'pro_type'   => $validated['pro_type'],
                'details'    => $validated['details'] ?? null,
                'user'       => Auth::id(),
            ]);

            // تفاصيل اليومية: مدين
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['acc1'],
                'debit'      => $validated['pro_value'],
                'credit'     => 0,
                'type'       => 0,
                'info'       => $validated['info'] ?? null,
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
            ]);

            // تفاصيل اليومية: دائن
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['acc2'],
                'debit'      => 0,
                'credit'     => $validated['pro_value'],
                'type'       => 1,
                'info'       => $validated['info'] ?? null,
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
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
            ->where('code', 'like', '121%')
            ->select('id', 'aname')
            ->get();

        // حسابات البنك
        $bankAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '124%')
            ->select('id', 'aname')
            ->get();

        // حسابات الموظفين
        $employeeAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
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

        return view('transfers.edit', [
            'transfer' => $transfer,
            'type' => $type,
            'cashAccounts' => $cashAccounts,
            'bankAccounts' => $bankAccounts,
            'employeeAccounts' => $employeeAccounts,
            'otherAccounts' => $otherAccounts,
            'pro_id' => $transfer->pro_id,
            'pro_type' => $transfer->pro_type
        ]);
    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'pro_num'     => 'nullable|string',
            'emp_id'      => 'required|integer',
            'emp2_id'     => 'nullable|integer',
            'acc1'        => 'required|integer',
            'acc2'        => 'required|integer',
            'pro_value'   => 'required|numeric',
            'details'     => 'nullable|string',
            'info'        => 'nullable|string',
            'info2'       => 'nullable|string',
            'info3'       => 'nullable|string',
            'cost_center' => 'nullable|integer',
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
}
