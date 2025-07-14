<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use Illuminate\Support\Facades\DB;
use App\Models\MultiJournal;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use App\Models\CostCenter;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class MultiJournalController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض قيود يوميه حسابات')->only(['index']);
        $this->middleware('can:إضافة قيود يوميه حسابات')->only(['create', 'store']);
        $this->middleware('can:تعديل قيود يوميه حسابات')->only(['update']);
        $this->middleware('can:حذف قيود يوميه حسابات')->only(['destroy']);
    }

    public function index()
    {
        $multis = MultiJournal::where('isdeleted', 0)
            ->where('pro_type',  8)
            ->orderBy('pro_id', 'desc')
            ->get();
        return view('multi-journals.index', compact('multis'));
    }

    public function create()
    {
        $accounts = \App\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->get(); // أو أي جدول الحسابات عندك

        $employees = \App\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
            ->get();

        $cost_centers = \App\Models\CostCenter::get();
        return view('multi-journals.create', compact('accounts', 'employees', 'cost_centers'));
    }
    public function store(Request $request)
    {
        // التحقق من التوازن بين المجاميع المدينة والدائنة
        $totalDebit = collect($request->debit)->sum();
        $totalCredit = collect($request->credit)->sum();

        if (number_format($totalDebit, 2) !== number_format($totalCredit, 2)) {
            return back()->withErrors(['error' => 'يجب أن تتساوى المجاميع المدينة والدائنة.'])->withInput();
        }

        $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'account_id'  => 'required|array',
            'debit'       => 'required|array',
            'credit'      => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // تحديد pro_id جديد
            $lastProId = OperHead::where('pro_type', $request->pro_type)->max('pro_id');
            $newProId = $lastProId ? $lastProId + 1 : 1;

            // إنشاء رأس القيد في oper_heads
            $oper = OperHead::create([
                'pro_id'        => $newProId,
                'is_journal'    => 1,
                'journal_type'  => 1,
                'info'          => $request->info,
                'info2'         => $request->info2,
                'info3'         => $request->info3,
                'details'       => $request->details,
                'pro_date'      => $request->pro_date,
                'pro_num'       => $request->pro_num,
                'emp_id'        => $request->emp_id,
                'pro_value'     => $totalDebit,
                'cost_center'   => $request->cost_center,
                'user'          => Auth::id(),
                'pro_type'      => $request->pro_type,
            ]);

            // journal_id جديد
            $lastJournalId = JournalHead::max('journal_id');
            $newJournalId = $lastJournalId ? $lastJournalId + 1 : 1;

            // إنشاء journal_head
            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total'      => $totalDebit,
                'date'       => $request->pro_date,
                'op_id'      => $oper->id,
                'pro_type'   => $request->pro_type,
                'details'    => $request->details,
                'user'       => Auth::id(),
            ]);

            // إدخال الأسطر (journal_details)
            foreach ($request->account_id as $i => $accId) {
                // تخطي الصفوف الفارغة تمامًا
                if ((!$accId || ($request->debit[$i] == 0 && $request->credit[$i] == 0))) {
                    continue;
                }

                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $accId,
                    'debit'      => $request->debit[$i] ?? 0,
                    'credit'     => $request->credit[$i] ?? 0,
                    'type'       => ($request->debit[$i] > 0) ? 0 : 1,
                    'info'       => $request->note[$i] ?? null,
                    'op_id'      => $oper->id,
                    'isdeleted'  => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('multi-journals.index')->with('success', 'تم حفظ القيد المتعدد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطأ في الحفظ: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $oper = OperHead::findOrFail($id);
        $journal = JournalHead::where('op_id', $id)->firstOrFail();
        $details = JournalDetail::where('journal_id', $journal->journal_id)->get();
        $accounts = AccHead::where('is_basic', '0')
            ->get();
        $costCenters = CostCenter::all();
        $employees = AccHead::where('code', 'like', '213%')
            ->where('is_basic', '0')
            ->get();

        return view('multi-journals.edit', compact('oper', 'journal', 'details', 'accounts', 'costCenters', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $totalDebit = collect($request->debit)->sum();
        $totalCredit = collect($request->credit)->sum();

        if (number_format($totalDebit, 2) !== number_format($totalCredit, 2)) {
            return back()->withErrors(['error' => 'يجب أن تتساوى المجاميع المدينة والدائنة.'])->withInput();
        }

        $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'account_id'  => 'required|array',
            'debit'       => 'required|array',
            'credit'      => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // تحديث رأس القيد في oper_heads
            $oper = OperHead::findOrFail($id);
            $oper->update([
                'info'          => $request->info,
                'info2'         => $request->info2,
                'info3'         => $request->info3,
                'details'       => $request->details,
                'pro_date'      => $request->pro_date,
                'pro_num'       => $request->pro_num,
                'emp_id'        => $request->emp_id,
                'pro_value'     => $totalDebit,
                'cost_center'   => $request->cost_center,
                'user'          => Auth::id(),
                'pro_type'      => $request->pro_type,
            ]);

            // حذف journal_head القديم إن وجد
            $journalHead = JournalHead::where('op_id', $oper->id)->first();
            if ($journalHead) {
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
                $journalHead->delete();
            }

            // إنشاء journal_head الجديد
            $newJournalId = JournalHead::max('journal_id') + 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total'      => $totalDebit,
                'date'       => $request->pro_date,
                'op_id'      => $oper->id,
                'pro_type'   => $request->pro_type,
                'details'    => $request->details,
                'user'       => Auth::id(),
            ]);

            // إنشاء journal_details الجديد
            foreach ($request->account_id as $i => $accId) {
                if ((!$accId || ($request->debit[$i] == 0 && $request->credit[$i] == 0))) {
                    continue;
                }

                JournalDetail::create([
                    'journal_id' => $newJournalId,
                    'account_id' => $accId,
                    'debit'      => $request->debit[$i] ?? 0,
                    'credit'     => $request->credit[$i] ?? 0,
                    'type'       => ($request->debit[$i] > 0) ? 0 : 1,
                    'info'       => $request->note[$i] ?? null,
                    'op_id'      => $oper->id,
                    'isdeleted'  => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('multi-journals.index')->with('success', 'تم تعديل القيد المتعدد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطأ في التعديل: ' . $e->getMessage()])->withInput();
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

            return redirect()->route('multi-journals.index')->with('success', 'تم حذف القيد بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }
}
