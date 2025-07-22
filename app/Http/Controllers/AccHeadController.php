<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AccHeadController extends Controller
{
    public function __construct()
    {
        // الصلاحيات للكيانات المختلفة

        // العملاء
        $this->middleware('can:عرض العملاء')->only(['index', 'show']);
        $this->middleware('can:إضافة العملاء')->only(['create', 'store']);
        $this->middleware('can:تعديل العملاء')->only(['edit', 'update']);
        $this->middleware('can:حذف العملاء')->only(['destroy']);
        $this->middleware('can:طباعة العملاء')->only(['print']);

        // الموردين
        $this->middleware('can:عرض الموردين')->only(['index', 'show']);
        $this->middleware('can:إضافة الموردين')->only(['create', 'store']);
        $this->middleware('can:تعديل الموردين')->only(['edit', 'update']);
        $this->middleware('can:حذف الموردين')->only(['destroy']);
        $this->middleware('can:طباعة الموردين')->only(['print']);

        // الصناديق
        $this->middleware('can:عرض الصناديق')->only(['index', 'show']);
        $this->middleware('can:إضافة الصناديق')->only(['create', 'store']);
        $this->middleware('can:تعديل الصناديق')->only(['edit', 'update']);
        $this->middleware('can:حذف الصناديق')->only(['destroy']);
        $this->middleware('can:طباعة الصناديق')->only(['print']);

        // البنوك
        $this->middleware('can:عرض البنوك')->only(['index', 'show']);
        $this->middleware('can:إضافة البنوك')->only(['create', 'store']);
        $this->middleware('can:تعديل البنوك')->only(['edit', 'update']);
        $this->middleware('can:حذف البنوك')->only(['destroy']);
        $this->middleware('can:طباعة البنوك')->only(['print']);

        // الموظفين
        $this->middleware('can:عرض الموظفين')->only(['index', 'show']);
        $this->middleware('can:إضافة الموظفين')->only(['create', 'store']);
        $this->middleware('can:تعديل الموظفين')->only(['edit', 'update']);
        $this->middleware('can:حذف الموظفين')->only(['destroy']);
        $this->middleware('can:طباعة الموظفين')->only(['print']);

        // المخازن
        $this->middleware('can:عرض المخازن')->only(['index', 'show']);
        $this->middleware('can:إضافة المخازن')->only(['create', 'store']);
        $this->middleware('can:تعديل المخازن')->only(['edit', 'update']);
        $this->middleware('can:حذف المخازن')->only(['destroy']);
        $this->middleware('can:طباعة المخازن')->only(['print']);

        // المصروفات
        $this->middleware('can:عرض المصروفات')->only(['index', 'show']);
        $this->middleware('can:إضافة المصروفات')->only(['create', 'store']);
        $this->middleware('can:تعديل المصروفات')->only(['edit', 'update']);
        $this->middleware('can:حذف المصروفات')->only(['destroy']);
        $this->middleware('can:طباعة المصروفات')->only(['print']);

        // الايرادات
        $this->middleware('can:عرض الايرادات')->only(['index', 'show']);
        $this->middleware('can:إضافة الايرادات')->only(['create', 'store']);
        $this->middleware('can:تعديل الايرادات')->only(['edit', 'update']);
        $this->middleware('can:حذف الايرادات')->only(['destroy']);
        $this->middleware('can:طباعة الايرادات')->only(['print']);

        // دائنين متنوعين
        $this->middleware('can:عرض دائنين متنوعين')->only(['index', 'show']);
        $this->middleware('can:إضافة دائنين متنوعين')->only(['create', 'store']);
        $this->middleware('can:تعديل دائنين متنوعين')->only(['edit', 'update']);
        $this->middleware('can:حذف دائنين متنوعين')->only(['destroy']);
        $this->middleware('can:طباعة دائنين متنوعين')->only(['print']);

        // مدينين متنوعين
        $this->middleware('can:عرض مدينين متنوعين')->only(['index', 'show']);
        $this->middleware('can:إضافة مدينين متنوعين')->only(['create', 'store']);
        $this->middleware('can:تعديل مدينين متنوعين')->only(['edit', 'update']);
        $this->middleware('can:حذف مدينين متنوعين')->only(['destroy']);
        $this->middleware('can:طباعة مدينين متنوعين')->only(['print']);
        // الشركاء
        $this->middleware('can:عرض الشركاء')->only(['index', 'show']);
        $this->middleware('can:إضافة الشركاء')->only(['create', 'store']);
        $this->middleware('can:تعديل الشركاء')->only(['edit', 'update']);
        $this->middleware('can:حذف الشركاء')->only(['destroy']);
        $this->middleware('can:طباعة الشركاء')->only(['print']);

        // جاري الشركاء
        $this->middleware('can:عرض جارى الشركاء')->only(['index', 'show']);
        $this->middleware('can:إضافة جارى الشركاء')->only(['create', 'store']);
        $this->middleware('can:تعديل جارى الشركاء')->only(['edit', 'update']);
        $this->middleware('can:حذف جارى الشركاء')->only(['destroy']);
        $this->middleware('can:طباعة جارى الشركاء')->only(['print']);

        // الأصول الثابتة
        $this->middleware('can:عرض الأصول الثابتة')->only(['index', 'show']);
        $this->middleware('can:إضافة الأصول الثابتة')->only(['create', 'store']);
        $this->middleware('can:تعديل الأصول الثابتة')->only(['edit', 'update']);
        $this->middleware('can:حذف الأصول الثابتة')->only(['destroy']);
        $this->middleware('can:طباعة الأصول الثابتة')->only(['print']);

    }

    // public function __construct()
    // {
    //     $this->middleware('can:عرض العملاء')->only(['index', 'show']);
    //     $this->middleware('can:إضافة إدارة الحسابات')->only(['create', 'store']);
    //     $this->middleware('can:تعديل إدارة الحسابات')->only(['edit', 'update']);
    //     $this->middleware('can:حذف إدارة الحسابات')->only(['destroy']);
    //     $this->middleware('can:طباعة إدارة الحسابات')->only(['print']);

    //     $this->middleware('can:عرض الحسابات')->only(['index', 'show']);
    //     $this->middleware('can:عرض حساب فرعي')->only(['showSubAccount']);
    //     $this->middleware('can:إضافة الحسابات')->only(['create', 'store']);
    //     $this->middleware('can:تعديل الحسابات')->only(['edit', 'update']);
    //     $this->middleware('can:حذف الحسابات')->only(['destroy']);
    //     $this->middleware('can:بحث الحسابات')->only(['search']);
    // }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $accountsQuery = AccHead::query()->where('is_basic', 0);

        if ($type) {
            $patterns = [
                'client' => '122%',
                'supplier' => '211%',
                'fund' => '121%',
                'bank' => '124%',
                'expense' => '44%',
                'revenue' => '32%',
                'creditor' => '212%',
                'debtor' => '125%',
                'partner' => '231%',
                'asset' => '11%',
                'employee' => '213%',
                'rentable' => '112%',
                'store' => '123%',
            ];

            $accountsQuery->where('code', 'like', $patterns[$type] ?? '9999%');
        }

        $accounts = $accountsQuery->get();
        return view('accounts.index', compact('accounts'));
    }

    public function summary(Request $request)
    {
        $accId = $request->input('acc_id');
        return redirect()->route('accounts.show', $accId);
    }

    public function create(Request $request)
    {
        $parent = $request->query('parent', 0);
        $last_id = '';
        $resacs = [];

        if ($parent) {
            $lastAccount = DB::table('acc_head')
                ->where('code', 'like', $parent . '%')
                ->where('is_basic', 0)
                ->orderByDesc('id')
                ->first();

            if ($lastAccount) {
                $suffix = str_replace($parent, '', $lastAccount->code);
                $next = str_pad(((int) $suffix + 1), 3, '0', STR_PAD_LEFT);
                $last_id = $parent . $next;
            } else {
                $last_id = $parent . "001";
            }

            $resacs = DB::table('acc_head')
                ->where('is_basic', '1')
                ->where('code', 'like', $parent . '%')
                ->orderBy('code')
                ->get();
        } else {
            $resacs = DB::table('acc_head')
                ->where('is_basic', '1')
                ->orderBy('code')
                ->get();
        }

        return view('accounts.create', compact('parent', 'last_id', 'resacs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:9|unique:acc_head,code',
            'aname' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'e_mail' => 'nullable|email|max:100',
            'constant' => 'nullable|string|max:50',
            'is_stock' => 'nullable',
            'is_fund' => 'nullable',
            'rentable' => 'nullable',
            'employees_expensses' => 'nullable',
            'parent_id' => 'nullable|integer',
            'nature' => 'nullable|string|max:50',
            'kind' => 'nullable|string|max:50',
            'is_basic' => 'nullable',
            'start_balance' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'secret' => 'nullable',
            'info' => 'nullable|string|max:500',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
            'deletable' => 'nullable',
            'editable' => 'nullable',
            'isdeleted' => 'nullable',
        ], [
            'code.required' => 'مطلوب تدخل رمز الحساب.',
            'code.max' => 'رمز الحساب لازم مايعديش 9 حروف.',
            'aname.required' => 'مطلوب تدخل اسم الحساب.',
            'aname.max' => 'اسم الحساب لازم مايعديش 100 حرف.',
            'phone.max' => 'رقم التليفون لازم مايعديش 15 حرف.',
            'address.max' => 'العنوان لازم مايعديش 250 حرف.',
            'e_mail.email' => 'البريد الإلكتروني لازم يكون صحيح.',
            'e_mail.max' => 'البريد الإلكتروني لازم مايعديش 100 حرف.',
            'constant.max' => 'الثابت لازم مايعديش 50 حرف.',
            'parent_id.integer' => 'رقم الحساب الأب لازم يكون رقم.',
            'nature.max' => 'الطبيعة لازم مايعديش 50 حرف.',
            'kind.max' => 'النوع لازم مايعديش 50 حرف.',
            'start_balance.numeric' => 'الرصيد الابتدائي لازم يكون رقم.',
            'credit.numeric' => 'الائتمان لازم يكون رقم.',
            'debit.numeric' => 'الخصم لازم يكون رقم.',
            'balance.numeric' => 'الرصيد لازم يكون رقم.',
            'info.max' => 'المعلومات لازم مايعديش 500 حرف.',
            'tenant.integer' => 'المستأجر لازم يكون رقم.',
            'branch.integer' => 'الفرع لازم يكون رقم.',
        ]);

        AccHead::create([
            'code' => $request->code,
            'deletable' => $request->deletable ?? 1,
            'editable' => $request->editable ?? 1,
            'aname' => $request->aname,
            'phone' => $request->phone,
            'address' => $request->address,
            'e_mail' => $request->e_mail,
            'constant' => $request->constant,
            'is_stock' => $request->has('is_stock') ? 1 : 0,
            'is_fund' => $request->has('is_fund') ? 1 : 0,
            'rentable' => $request->has('rentable') ? 1 : 0,
            'employees_expensses' => $request->has('employees_expensses') ? 1 : 0,
            'parent_id' => $request->parent_id,
            'nature' => $request->nature,
            'kind' => $request->kind,
            'is_basic' => $request->is_basic ?? 0,
            'start_balance' => $request->start_balance ?? 0,
            'credit' => $request->credit ?? 0,
            'debit' => $request->debit ?? 0,
            'balance' => $request->balance ?? 0,
            'secret' => $request->has('secret') ? 1 : 0,
            'crtime' => now(),
            'mdtime' => now(),
            'info' => $request->info,
            'isdeleted' => $request->isdeleted ?? 0,
            'tenant' => $request->tenant ?? 0,
            'branch' => $request->branch ?? 0,
        ]);

        $parent = null;

        if ($request->parent_id) {
            $parentAcc = AccHead::find($request->parent_id);
            if ($parentAcc) {
                $parentCode = substr($parentAcc->code, 0, 3);

                $map = [
                    '122' => 'client',
                    '211' => 'supplier',
                    '121' => 'fund',
                    '124' => 'bank',
                    '044' => 'expense',
                    '032' => 'revenue',
                    '212' => 'creditor',
                    '125' => 'debtor',
                    '231' => 'partner',
                    '011' => 'asset',
                    '213' => 'employee',
                    '112' => 'rentable',
                    '123' => 'store',
                ];

                $parent = $map[$parentCode] ?? null;
            }
        }
        return redirect()->route('accounts.index', ['type' => $parent])
            ->with('success', 'تمت إضافة الحساب بنجاح');
    }

    public function show($id)
    {
        $account = AccHead::findOrFail($id);
        $parent = substr($account->code, 0, -3);

        $resacs = DB::table('acc_head')
            ->where('is_basic', 1)
            ->where('code', 'like', $parent . '%')
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'resacs', 'parent'));
    }

    public function edit($id)
    {
        $account = AccHead::findOrFail($id);

        // استخراج الكود الأب لعرض الحسابات الأساسية المتعلقة
        $parent = substr($account->code, 0, -3);

        $resacs = DB::table('acc_head')
            ->where('is_basic', 1)
            ->where('code', 'like', $parent . '%')
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'resacs', 'parent'));
    }

    public function update(Request $request, $id)
    {
        $account = AccHead::findOrFail($id);

        $validated = $request->validate([
            'aname' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'e_mail' => 'nullable|email|max:100',
            'constant' => 'nullable|string|max:50',
            'start_balance' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'nature' => 'nullable|string|max:50',
            'kind' => 'nullable|string|max:50',
            'info' => 'nullable|string|max:500',
        ]);

        $account->update([
            'aname' => $request->aname,
            'phone' => $request->phone,
            'address' => $request->address,
            'e_mail' => $request->e_mail,
            'constant' => $request->constant,
            'is_stock' => $request->has('is_stock') ? 1 : 0,
            'is_fund' => $request->has('is_fund') ? 1 : 0,
            'rentable' => $request->has('rentable') ? 1 : 0,
            'employees_expensses' => $request->has('employees_expensses') ? 1 : 0,
            'secret' => $request->has('secret') ? 1 : 0,
            'nature' => $request->nature,
            'kind' => $request->kind,
            'start_balance' => $request->start_balance ?? 0,
            'credit' => $request->credit ?? 0,
            'debit' => $request->debit ?? 0,
            'balance' => $request->balance ?? 0,
            'info' => $request->info,
            'mdtime' => now(), // تاريخ آخر تعديل
        ]);
        $parent = null;

        if ($request->parent_id) {
            $parentAcc = AccHead::find($request->parent_id);
            if ($parentAcc) {
                $parentCode = substr($parentAcc->code, 0, 3);

                $map = [
                    '122' => 'client',
                    '211' => 'supplier',
                    '121' => 'fund',
                    '124' => 'bank',
                    '044' => 'expense',
                    '032' => 'revenue',
                    '212' => 'creditor',
                    '125' => 'debtor',
                    '231' => 'partner',
                    '234' => 'partner',
                    '011' => 'asset',
                    '213' => 'employee',
                    '112' => 'rentable',
                    '123' => 'store',
                ];

                $parent = $map[$parentCode] ?? null;
            }
        }
        return redirect()->route('accounts.index', ['type' => $parent])
            ->with('success', 'تمت إضافة الحساب بنجاح');
    }


    public function destroy($id)
    {
        $acc = AccHead::findOrFail($id);

        if (!$acc->deletable) {
            return redirect()->back()->with('error', 'هذا الحساب غير قابل للحذف.');
        }

        // التحقق من وجود حركات محاسبية مرتبطة بالحساب
        $hasTransactions = DB::table('journal_details')->where('account_id', $id)->exists();

        if ($hasTransactions) {
            return redirect()->back()->with('error', 'لا يمكن حذف الحساب لأنه مرتبط بحركات محاسبية.');
        }

        // حذف الحساب
        $acc->delete();

        return redirect()->route('accounts.index')->with('success', 'تم حذف الحساب بنجاح.');
    }

    public function startBalance()
    {
        return view('accounts.startBalance.manage-start-balance');
    }

    public function accountMovementReport($accountId = null)
    {
        return view('accounts.reports.account-movement', compact('accountId'));
    }

    public function balanceSheet()
    {
        return view('accounts.reports.manage-balance-sheet');
    }
}
