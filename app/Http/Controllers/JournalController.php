<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Journal;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use App\Models\OperHead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JournalController extends Controller

{
    public function __construct()
    {
        $this->middleware('can:عرض قيد يومية')->only(['index', 'show']);
        $this->middleware('can:إضافة قيد يومية')->only(['create', 'store']);
    }
    // __________________________________________________________________________________________index
    public function index()
    {
        $journals = Journal::where('isdeleted', 0)
            ->where('pro_type', [7, 8])
            ->orderBy('pro_id', 'desc')
            ->get();
        return view('journals.index', compact('journals'));
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
        return view('journals.create', compact('accounts', 'employees', 'cost_centers'));
    }


    public function store(Request $request)
    {

        // التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'pro_num'     => 'nullable|string',
            'emp_id'      => 'nullable|integer',
            'acc1'        => 'required|integer',
            'acc2'        => 'required|integer',
            'debit'       => 'required|numeric',
            'credit'      => 'required|numeric',
            'info'        => 'nullable|string',
            'info2'        => 'nullable|string',
            'info3'        => 'nullable',
            'details'     => 'nullable|string',
        ]);

        try {
            // نستخدم المعاملة (transaction) لضمان تنفيذ كل العمليات أو الرجوع في حالة خطأ
            DB::beginTransaction();

            // الحصول على آخر pro_id لنوع العملية pro_type
            $lastProId = OperHead::where('pro_type', $validated['pro_type'])->max('pro_id');
            $newProId = $lastProId ? $lastProId + 1 : 1;

            // إنشاء سجل operhead
            $oper = OperHead::create([
                'pro_id'        => $newProId,
                'branch_id'     => 1, // أو من الطلب أو ثابت حسب النظام
                'is_stock'      => 0, // مثال، ضع القيم المناسبة
                'is_finance'    => 0,
                'is_manager'    => 0,
                'is_journal'    => 1,
                'journal_type'  => 1,
                'info'          => $validated['info'],
                'info2'          => $validated['info2'],
                'info3'          => $request['info3'],
                'details'       => $validated['details'],
                'pro_date'      => $validated['pro_date'],
                'pro_num'       => $validated['pro_num'],
                'emp_id'        => $validated['emp_id'],
                'acc1'          => $validated['acc1'],
                'acc2'          => $validated['acc2'],
                'pro_value'     => $validated['debit'],
                'cost_center'   => $request['cost_center'],
                'user'          => Auth::id(),
                'pro_type'      => $validated['pro_type'],
                // أضف باقي الأعمدة حسب الحاجة مع التأكد من nullable أو default values
            ]);

            // الحصول على آخر journal_id في جدول journal_heads
            $lastJournalId = JournalHead::max('journal_id');
            $newJournalId = $lastJournalId ? $lastJournalId + 1 : 1;

            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total'      => $validated['debit'],
                'date'       => $oper->pro_date,
                'op_id'      => $oper->id,  // الربط مع operhead
                'pro_type'   => $validated['pro_type'],
                'details'    => $validated['details'] ?? null,
                'user'       => Auth::id(),
                // أضف باقي الأعمدة المطلوبة أو nullable
            ]);


            // إنشاء تفاصيل اليومية (journal_details) Debit
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['acc1'],
                'debit'      => $validated['debit'],
                'credit'     => 0,
                'type'       => 0, // نوع القيد: مدين
                'info'       => $validated['info'] ?? null,
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
            ]);

            // إنشاء تفاصيل اليومية (journal_details) Credit
            JournalDetail::create([
                'journal_id' => $newJournalId,
                'account_id' => $validated['acc2'],
                'debit'      => 0,
                'credit'     => $validated['credit'],
                'type'       => 1, // نوع القيد: دائن
                'info'       => $validated['info'] ?? null,
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
                // أضف باقي الأعمدة حسب الجدول
            ]);

            // إذا نجح كل شيء نؤكد التغييرات
            DB::commit();

            return redirect()->route('journals.index')->with('success', 'تمت إضافة القيد بنجاح');
        } catch (\Exception $e) {
            // في حالة وجود خطأ نرجع التغييرات
            DB::rollBack();

            // يمكنك تسجيل الخطأ في اللوج أو إظهار رسالة مخصصة
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $journal = \App\Models\Journal::findOrFail($id);

        $accounts = \App\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->get();

        $employees = \App\Models\AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
            ->get();

        $cost_centers = \App\Models\CostCenter::get();

        return view('journals.edit', compact('journal', 'accounts', 'employees', 'cost_centers'));
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pro_type'    => 'required|integer',
            'pro_date'    => 'required|date',
            'pro_num'     => 'nullable|string',
            'emp_id'      => 'nullable|integer',
            'acc1'        => 'required|integer',
            'acc2'        => 'required|integer',
            'debit'       => 'required|numeric',
            'credit'      => 'required|numeric',
            'info'        => 'nullable|string',
            'info2'        => 'nullable|string',
            'info3'        => 'nullable|string',
            'details'     => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oper = OperHead::findOrFail($id);
            $oper->update([
                'pro_date'      => $validated['pro_date'],
                'pro_num'       => $validated['pro_num'],
                'emp_id'        => $validated['emp_id'],
                'info'          => $validated['info'],
                'info2'         => $validated['info2'],
                'info3'         => $validated['info3'],
                'details'       => $validated['details'],
                'acc1'          => $validated['acc1'],
                'acc2'          => $validated['acc2'],
                'pro_value'     => $validated['debit'],
                'cost_center'   => $request['cost_center'],
                'user'          => Auth::id(),
                'pro_type'      => $validated['pro_type'],
            ]);

            $journalHead = JournalHead::where('op_id', $oper->id)->first();
            if ($journalHead) {
                $journalHead->update([
                    'total'    => $validated['debit'],
                    'date'     => $validated['pro_date'],
                    'details'  => $validated['details'],
                    'user'     => Auth::id(),
                ]);
            }

            // حذف التفاصيل القديمة
            JournalDetail::where('op_id', $oper->id)->delete();

            // إضافة التفاصيل الجديدة
            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => $validated['acc1'],
                'debit'      => $validated['debit'],
                'credit'     => 0,
                'type'       => 0,
                'info'       => $validated['info'],
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
            ]);

            JournalDetail::create([
                'journal_id' => $journalHead->journal_id,
                'account_id' => $validated['acc2'],
                'debit'      => 0,
                'credit'     => $validated['credit'],
                'type'       => 1,
                'info'       => $validated['info'],
                'op_id'      => $oper->id,
                'isdeleted'  => 0,
            ]);

            DB::commit();
            return redirect()->route('journals.index')->with('success', 'تم تعديل القيد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء التعديل: ' . $e->getMessage()])->withInput();
        }
    }


    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            // حذف التفاصيل
            JournalDetail::where('op_id', $id)->delete();

            // حذف رأس القيد
            JournalHead::where('op_id', $id)->delete();

            // حذف العملية
            Operhead::findOrFail($id)->delete();
        });

        return redirect()->route('journals.index')->with('success', 'تم حذف القيد بكل تفاصيله بنجاح');
    }
}
