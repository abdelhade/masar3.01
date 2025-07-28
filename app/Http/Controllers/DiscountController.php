<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\{DB, Auth};
use App\Http\Requests\CreatDiscountRequest;
use App\Models\{AccHead, OperHead, JournalHead, JournalDetail};

class DiscountController extends Controller
{
    //     public function __construct()
    // {

    //     $this->middleware('can:إضافة قائمة الخصومات المسموح بها')->only(['create', 'store']);
    //     $this->middleware('can:إضافة قائمة الخصومات المكتسبة')->only(['create', 'store']);
    //     $this->middleware('can:إضافة خصم مسموح به')->only(['create', 'store']);

    //     $this->middleware('can:تعديل قائمة الخصومات المكتسبة')->only(['edit', 'update']);
    //     $this->middleware('can:تعديل قائمة الخصومات المسموح بها')->only(['edit', 'update']);
    //     $this->middleware('can:تعديل خصم مسموح به')->only(['edit', 'update']);

    //     $this->middleware('can:حذف قائمة الخصومات المكتسبة')->only(['destroy']);
    //     $this->middleware('can:حذف قائمة الخصومات المسموح بها')->only(['destroy']);
    //     $this->middleware('can:حذف خصم مسموح به')->only(['destroy']);
    // }

    public function index(Request $request)
    {
        $type = (int) $request->input('type');

        if (!$type) {
            // لو معاه صلاحية واحدة بس، يحوله عليها
            if (auth()->user()->can('عرض قائمة الخصومات المسموح بها')) {
                return redirect()->route('discounts.index', ['type' => 30]);
            } elseif (auth()->user()->can('عرض قائمة الخصومات المكتسبة')) {
                return redirect()->route('discounts.index', ['type' => 31]);
            } else {
                abort(403, 'غير مصرح لك بعرض هذه الصفحة');
            }
        }

        // تحقق من صلاحية العرض حسب النوع
        if ($type == 30 && !auth()->user()->can('عرض قائمة الخصومات المسموح بها')) {
            abort(403, 'غير مصرح لك بعرض قائمة الخصومات المسموح بها');
        }

        if ($type == 31 && !auth()->user()->can('عرض قائمة الخصومات المكتسبة')) {
            abort(403, 'غير مصرح لك بعرض قائمة الخصومات المكتسبة');
        }

        $discounts = OperHead::with(['acc1Head', 'acc2Head']);

        if ($type == 30) {
            $discounts = $discounts->where(function ($query) {
                $query->where('acc1', 91)->orWhere('acc2', 91);
            });
        } elseif ($type == 31) {
            $discounts = $discounts->where(function ($query) {
                $query->where('acc1', 97)->orWhere('acc2', 97);
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
            abort(403, __('نوع الرمز غير صالح'));
        }

        // تحقق من الصلاحية قبل الدخول
        if ($type == 30 && !auth()->user()->can('إضافة قائمة الخصومات المسموح بها')) {
            abort(403, 'غير مصرح لك بإضافة هذه القائمة');
        }

        if ($type == 31 && !auth()->user()->can('إضافة قائمة الخصومات المكتسبة')) {
            abort(403, 'غير مصرح لك بإضافة هذه القائمة');
        }
        $type = (int) $request->get('type');
        $hash = $request->get('q');

        if ($hash !== md5($type)) {
            abort(403, __('نوع الرمز غير صالح'));
        }

        $lastProId = OperHead::max('pro_id');
        $nextProId = $lastProId ? $lastProId + 1 : 1;

        if ($type == 30) {
            // خصم مسموح به: acc1 العملاء - acc2 ثابت (id 91)
            $acc2Fixed = AccHead::findOrFail(91);
            $clientsAccounts = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '122%')
                ->select('id', 'aname', 'balance')->get()->map(function ($account) {
                    $account->balance = $this->getAccountBalance($account->id);
                    return $account;
                });
            return view('discounts.create', [
                'type' => $type,
                'nextProId' => $nextProId,
                'acc2Fixed' => $acc2Fixed,
                'clientsAccounts' => $clientsAccounts
            ]);
        } elseif ($type == 31) {
            // خصم مكتسب: acc1 ثابت (id 97) - acc2 الموردين
            $acc1Fixed = AccHead::findOrFail(97);
            $suppliers = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '211%')
                ->select('id', 'aname', 'balance')
                ->get()->map(function ($account) {
                    $account->balance = $this->getAccountBalance($account->id);
                    return $account;
                });

            return view('discounts.create', [
                'type' => $type,
                'nextProId' => $nextProId,
                'acc1Fixed' => $acc1Fixed,
                'suppliers' => $suppliers
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

    public function store(CreatDiscountRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $oper = new OperHead();
            $oper->pro_type = $request->type;
            $oper->pro_id = $request->pro_id;
            $oper->pro_date = $request->pro_date;
            $oper->info = $request->info ?? null;
            $oper->pro_value = $request->pro_value;

            if ($validated['type'] == 30) {
                // خصم مسموح به: acc1 = العملاء، acc2 ثابت (91)
                $oper->acc1 = $validated['acc1'];
                $oper->acc2 = 91;
            } elseif ($validated['type'] == 31) {
                // خصم مكتسب: acc1 ثابت (97), acc2 = المورد
                $oper->acc1 = 97;
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
                'details' => $oper->info ?? ($oper->pro_type == 30 ? 'خصم مسموح به' : 'خصم مكتسب'),
                'user' => Auth::id(),
            ]);

            if ($oper->pro_type == 30) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $oper->acc1,
                    'debit' => 0,
                    'credit' => $oper->pro_value,
                    'type' => 1,
                    'info' => $oper->info ?? 'خصم مسموح به',
                    'op_id' => $oper->id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 91,
                    'debit' => $oper->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $oper->info ?? 'خصم مسموح به',
                    'op_id' => $oper->id,
                ]);
            } elseif ($oper->pro_type == 31) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 97,
                    'debit' => $oper->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $oper->info ?? 'خصم مكتسب',
                    'op_id' => $oper->id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $oper->acc2,
                    'debit' => 0,
                    'credit' => $oper->pro_value,
                    'type' => 1,
                    'info' => $oper->info ?? 'خصم مكتسب',
                    'op_id' => $oper->id,
                ]);
            }
            DB::commit();
            Alert::toast('تم حفظ البيانات بنجاح', 'success');
            return redirect()->route('discounts.index', ['type' => $oper->pro_type]);
        } catch (\Exception $e) {
            logger()->error('خطأ أثناء حفظ الخصم: ');
            Alert::toast('حدث خطأ أثناء حفظ الخصم', 'error');
            return back()->withInput();
        }
    }

    public function edit(Request $request, OperHead $discount)
    {
        $type = (int) $request->get('type');


        if (!in_array($type, [30, 31])) {
            abort(403, 'نوع الخصم غير صحيح');
        }

        // تحقق من صلاحية التعديل حسب النوع
        if ($type == 30 && !Auth::user()->can('تعديل قائمة الخصومات المسموح بها')) {
            abort(403, 'غير مصرح لك بتعديل هذه القائمة');
        }

        if ($type == 31 && !Auth::user()->can('تعديل قائمة الخصومات المكتسبة')) {
            abort(403, 'غير مصرح لك بتعديل هذه القائمة');
        }
        $titles = [
            30 => 'خصم مسموح به',
            31 => 'خصم مكتسب',
        ];
        if (!in_array($type, [30, 31])) {
            abort(403, 'نوع الخصم غير صحيح');
        }

        if ($type == 30) {
            $acc2Fixed = AccHead::findOrFail(91);
            $clientsAccounts = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '122%')
                ->select('id', 'aname')
                ->get();

            return view('discounts.edit', compact('discount', 'type', 'acc2Fixed', 'clientsAccounts', 'titles'));
        } elseif ($type == 31) {
            $acc1Fixed = AccHead::findOrFail(97);
            $suppliers = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '211%')
                ->select('id', 'aname')
                ->get();

            return view('discounts.edit', compact('discount', 'type', 'acc1Fixed', 'suppliers', 'titles'));
        }
    }

    public function update(CreatDiscountRequest $request, OperHead $discount)
    {
        try {
            $discount->pro_date = $request->pro_date;
            $discount->info = $request->info ?? null;
            $discount->pro_value = $request->pro_value;

            if ($request->type == 30) {
                $discount->acc1 = $request->acc1;
                $discount->acc2 = 91;
            } elseif ($request->type == 31) {
                $discount->acc1 = 97;
                $discount->acc2 = $request->acc2;
            }
            $discount->save();

            JournalDetail::where('op_id', $discount->id)->delete();
            JournalHead::where('op_id', $discount->id)->delete();

            $journalId = JournalHead::max('journal_id') + 1;
            JournalHead::create([
                'journal_id' => $journalId,
                'total' => $discount->pro_value,
                'op_id' => $discount->id,
                'op2' => 0,
                'pro_type' => $discount->pro_type,
                'date' => $discount->pro_date,
                'details' => $discount->info ?? ($discount->pro_type == 30 ? 'خصم مسموح به' : 'خصم مكتسب'),
                'user' => Auth::id(),
            ]);
            if ($discount->pro_type == 30) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $discount->acc1,
                    'debit' => $discount->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $discount->info ?? 'خصم مسموح به',
                    'op_id' => $discount->id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 91,
                    'debit' => 0,
                    'credit' => $discount->pro_value,
                    'type' => 1,
                    'info' => $discount->info ?? 'خصم مسموح به',
                    'op_id' => $discount->id,
                ]);
            } elseif ($discount->pro_type == 31) {
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => 97,
                    'debit' => $discount->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $discount->info ?? 'خصم مكتسب',
                    'op_id' => $discount->id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $discount->acc2,
                    'debit' => 0,
                    'credit' => $discount->pro_value,
                    'type' => 1,
                    'info' => $discount->info ?? 'خصم مكتسب',
                    'op_id' => $discount->id,
                ]);
            }
            Alert::toast('تم تحديث الخصم بنجاح', 'success');
            return redirect()->route('discounts.index', ['type' => $discount->pro_type]);
        } catch (\Exception $e) {
            logger()->error('خطأ أثناء تحديث الخصم: ');
            Alert::toast('حدث خطأ أثناء تحديث الخصم', 'error');
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $discount = OperHead::findOrFail($id);

        // تحقق من الصلاحية حسب نوع العملية
        if ($discount->pro_type == 30 && !Auth::user()->can('حذف قائمة الخصومات المسموح بها')) {
            abort(403, 'غير مصرح لك بحذف هذه القائمة');
        }

        if ($discount->pro_type == 31 && !Auth::user()->can('حذف قائمة الخصومات المكتسبة')) {
            abort(403, 'غير مصرح لك بحذف هذه القائمة');
        }

        try {
            $discount = OperHead::findOrFail($id);
            JournalDetail::where('op_id', $discount->id)->delete();
            JournalHead::where('op_id', $discount->id)->delete();

            $discount->delete();
            Alert::toast('تم حذف الخصم بنجاح', 'success');
            return redirect()->route('discounts.index');
        } catch (\Exception $e) {
            logger()->error('خطأ أثناء حذف الخصم: ' . $e->getMessage());
            Alert::toast('حدث خطأ أثناء حذف الخصم', 'error');
            return back();
        }
    }
}
