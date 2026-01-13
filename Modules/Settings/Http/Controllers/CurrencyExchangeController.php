<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Controllers;

use App\Models\CostCenter;
use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;
use Modules\Settings\Models\Currency;

class CurrencyExchangeController extends Controller
{
    /**
     * Display a listing of currency exchange operations.
     */
    public function index()
    {
        $exchanges = OperHead::whereIn('pro_type', [80, 81])
            ->where('isdeleted', 0)
            ->with(['acc1Head', 'acc2Head', 'currency', 'user'])
            ->orderByDesc('pro_date')
            ->get();

        return view('settings::currency-exchange.index', compact('exchanges'));
    }

    /**
     * Show the form for creating a new currency exchange.
     */
    public function create()
    {
        // Get all cash accounts (code starts with 1101)
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1101%')
            ->with('currency:id,name,symbol')
            ->select('id', 'aname', 'balance', 'currency_id', 'code')
            ->orderBy('code')
            ->get();

        // Get all active currencies
        $currencies = Currency::active()
            ->with('latestRate')
            ->get();

        // Get branches
        $branches = userBranches();

        // Get cost centers
        $costCenters = CostCenter::where('deleted', 0)->get();

        // Determine next pro_id for buy operation (we'll use 80 as default)
        $lastProIdBuy = OperHead::where('pro_type', 80)->max('pro_id') ?? 0;
        $newProIdBuy = $lastProIdBuy + 1;

        $lastProIdSell = OperHead::where('pro_type', 81)->max('pro_id') ?? 0;
        $newProIdSell = $lastProIdSell + 1;

        return view('settings::currency-exchange.create', compact(
            'cashAccounts',
            'currencies',
            'branches',
            'costCenters',
            'newProIdBuy',
            'newProIdSell'
        ));
    }

    /**
     * Store a newly created currency exchange in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation_type' => 'required|in:80,81', // 80 = buy, 81 = sell
            'pro_date' => 'required|date',
            'pro_num' => 'nullable|string',
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'currency_rate' => 'required|numeric|min:0',
            'pro_value' => 'required|numeric|min:0',
            'details' => 'nullable|string',
            'cost_center' => 'nullable|integer|exists:cost_centers,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // التحقق من تفعيل تعدد العملات
        if (!isMultiCurrencyEnabled()) {
            return back()->withErrors(['error' => 'نظام تعدد العملات غير مفعل.'])->withInput();
        }

        // التحقق من تطابق العملة مع الحساب المستلم (acc1)
        $acc1 = AccHead::find($validated['acc1']);
        $acc2 = AccHead::find($validated['acc2']);

        if (!$acc1 || !$acc2) {
            return back()->withErrors(['error' => 'الحسابات المحددة غير صحيحة.'])->withInput();
        }

        // Validation: العملة المختارة يجب أن تطابق عملة الحساب المستلم (acc1)
        if ($acc1->currency_id != $validated['currency_id']) {
            return back()->withErrors([
                'currency_mismatch' => 'يجب أن تكون العملة المختارة مطابقة لعملة الحساب المستلم (إلى صندوق).'
            ])->withInput();
        }

        // حساب القيمة الأساسية (بعد الضرب في سعر الصرف)
        $baseValue = (float) $validated['pro_value'] * (float) $validated['currency_rate'];

        try {
            DB::beginTransaction();

            // تحديد رقم العملية الجديد بناءً على pro_type
            $pro_type = (int) $validated['operation_type'];
            $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id') ?? 0;
            $newProId = $lastProId + 1;

            // إنشاء سجل جديد في جدول operhead
            $oper = OperHead::create([
                'pro_id' => $newProId,
                'pro_date' => $validated['pro_date'],
                'pro_type' => $pro_type,
                'acc1' => $validated['acc1'],
                'acc2' => $validated['acc2'],
                'pro_value' => $baseValue,
                'currency_id' => $validated['currency_id'],
                'currency_rate' => $validated['currency_rate'],
                'details' => $validated['details'] ?? null,
                'pro_num' => $validated['pro_num'] ?? null,
                'isdeleted' => 0,
                'tenant' => 0,
                'branch' => 1,
                'is_finance' => 1,
                'is_journal' => 1,
                'journal_type' => 2,
                'acc1_before' => 0,
                'acc1_after' => 0,
                'acc2_before' => 0,
                'acc2_after' => 0,
                'cost_center' => $validated['cost_center'] ?? null,
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // إنشاء رأس القيد (JournalHead)
            $lastJournalId = JournalHead::max('journal_id') ?? 0;
            $newJournalId = $lastJournalId + 1;
            $journalHead = JournalHead::create([
                'journal_id' => $newJournalId,
                'total' => $baseValue,
                'date' => $validated['pro_date'],
                'op_id' => $oper->id,
                'pro_type' => $pro_type,
                'details' => $validated['details'] ?? null,
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // إنشاء تفاصيل القيد (مدين)
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

            // إنشاء تفاصيل القيد (دائن)
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

            DB::commit();

            return redirect()->route('settings.currency-exchange.index')
                ->with('success', 'تم حفظ عملية تبادل العملة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified currency exchange.
     */
    public function edit($id)
    {
        $exchange = OperHead::whereIn('pro_type', [80, 81])
            ->findOrFail($id);

        // Get all cash accounts (code starts with 1101)
        $cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '1101%')
            ->with('currency:id,name,symbol')
            ->select('id', 'aname', 'balance', 'currency_id', 'code')
            ->orderBy('code')
            ->get();

        // Get all active currencies
        $currencies = Currency::active()
            ->with('latestRate')
            ->get();

        // Get branches
        $branches = userBranches();

        // Get cost centers
        $costCenters = CostCenter::where('deleted', 0)->get();

        return view('settings::currency-exchange.edit', compact(
            'exchange',
            'cashAccounts',
            'currencies',
            'branches',
            'costCenters'
        ));
    }

    /**
     * Update the specified currency exchange in storage.
     */
    public function update(Request $request, $id)
    {
        $exchange = OperHead::whereIn('pro_type', [80, 81])->findOrFail($id);

        $validated = $request->validate([
            'operation_type' => 'required|in:80,81',
            'pro_date' => 'required|date',
            'pro_num' => 'nullable|string',
            'acc1' => 'required|integer|exists:acc_head,id',
            'acc2' => 'required|integer|exists:acc_head,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'currency_rate' => 'required|numeric|min:0',
            'pro_value' => 'required|numeric|min:0',
            'details' => 'nullable|string',
            'cost_center' => 'nullable|integer|exists:cost_centers,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // التحقق من تفعيل تعدد العملات
        if (!isMultiCurrencyEnabled()) {
            return back()->withErrors(['error' => 'نظام تعدد العملات غير مفعل.'])->withInput();
        }

        // التحقق من تطابق العملة مع الحساب المستلم
        $acc1 = AccHead::find($validated['acc1']);

        if ($acc1->currency_id != $validated['currency_id']) {
            return back()->withErrors([
                'currency_mismatch' => 'يجب أن تكون العملة المختارة مطابقة لعملة الحساب المستلم (إلى صندوق).'
            ])->withInput();
        }

        // حساب القيمة الأساسية
        $baseValue = (float) $validated['pro_value'] * (float) $validated['currency_rate'];

        try {
            DB::beginTransaction();

            // تحديث operhead
            $exchange->update([
                'pro_date' => $validated['pro_date'],
                'pro_type' => (int) $validated['operation_type'],
                'pro_num' => $validated['pro_num'] ?? null,
                'acc1' => $validated['acc1'],
                'acc2' => $validated['acc2'],
                'pro_value' => $baseValue,
                'currency_id' => $validated['currency_id'],
                'currency_rate' => $validated['currency_rate'],
                'details' => $validated['details'] ?? null,
                'cost_center' => $validated['cost_center'] ?? null,
                'user' => Auth::id(),
                'branch_id' => $validated['branch_id'],
            ]);

            // تحديث journal_head
            $journalHead = JournalHead::where('op_id', $exchange->id)->first();
            if ($journalHead) {
                $journalHead->update([
                    'total' => $baseValue,
                    'date' => $validated['pro_date'],
                    'pro_type' => (int) $validated['operation_type'],
                    'details' => $validated['details'] ?? null,
                    'user' => Auth::id(),
                    'branch_id' => $validated['branch_id'],
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
                    'info' => $validated['details'] ?? null,
                    'op_id' => $exchange->id,
                    'isdeleted' => 0,
                    'branch_id' => $validated['branch_id'],
                ]);

                // إنشاء تفاصيل جديدة (دائن)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $validated['acc2'],
                    'debit' => 0,
                    'credit' => $baseValue,
                    'type' => 1,
                    'info' => $validated['details'] ?? null,
                    'op_id' => $exchange->id,
                    'isdeleted' => 0,
                    'branch_id' => $validated['branch_id'],
                ]);
            }

            DB::commit();

            return redirect()->route('settings.currency-exchange.index')
                ->with('success', 'تم تعديل عملية تبادل العملة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified currency exchange from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $exchange = OperHead::whereIn('pro_type', [80, 81])->findOrFail($id);

            // حذف journal_head المرتبط
            $journalHead = JournalHead::where('op_id', $exchange->id)->first();

            if ($journalHead) {
                // حذف تفاصيل اليومية
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();

                // حذف رأس اليومية
                $journalHead->delete();
            }

            // حذف العملية
            $exchange->delete();

            DB::commit();

            return redirect()->route('settings.currency-exchange.index')
                ->with('success', 'تم حذف عملية تبادل العملة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }
}
