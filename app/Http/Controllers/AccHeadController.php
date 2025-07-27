<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AccHeadController extends Controller
{
public function __construct()
{
    $this->middleware(function ($request, $next) {
        $type = $request->query('type');

        // لو مش موجود في الرابط، نجيبه من الـ ID
        if (!$type) {
            $id = $request->route('account') ?? $request->route('id');

            if ($id) {
                $account = \App\Models\AccHead::find($id);

                if ($account) {
                    $code = substr($account->code, 0, 3);

                    $map = [
                        '122' => 'client',
                        '211' => 'supplier',
                        '121' => 'fund',
                        '124' => 'bank',
                        '213' => 'employee',
                        '123' => 'store',
                        '044' => 'expense',
                        '032' => 'revenue',
                        '212' => 'creditor',
                        '125' => 'debtor',
                        '231' => 'partner',
                        '234' => 'current-partner',
                        '011' => 'asset',
                        '112' => 'rentable',
                    ];

                    $type = $map[$code] ?? null;
                }
            }
        }

        $label = match ($type) {
            'client' => 'العملاء',
            'supplier' => 'الموردين',
            'fund' => 'الصناديق',
            'bank' => 'البنوك',
            'employee' => 'الموظفين',
            'store' => 'المخازن',
            'expense' => 'المصروفات',
            'revenue' => 'الإيرادات',
            'creditor' => 'دائنين متنوعين',
            'debtor' => 'مدينين متنوعين',
            'partner' => 'الشركاء',
            'current-partner' => 'جارى الشركاء',
            'asset' => 'الأصول الثابتة',
            'rentable' => 'الأصول القابلة للتأجير',
            default => null,
        };

        if ($label) {
            $action = $request->route()?->getActionMethod();

            $permissionMap = [
                'index' => "عرض $label",
                'create' => "إضافة $label",
                'store' => "إضافة $label",
                'edit' => "تعديل $label",
                'update' => "تعديل $label",
                'destroy' => "حذف $label",
            ];

            if (isset($permissionMap[$action])) {
                $permission = $permissionMap[$action];

                if (!Auth::check() || !Auth::user()->can($permission)) {
                    abort(403, 'ليس لديك صلاحية لهذا الإجراء.');
                }
            }
        }

        return $next($request);
    });
}


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
