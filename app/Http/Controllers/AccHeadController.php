<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use App\Models\Country;
use App\Models\City;
use App\Models\State;
use App\Models\Town;
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
                    $account = AccHead::find($id);

                    if ($account) {
                        $code = substr($account->code, 0, 3);

                        $map = [
                            '1103' => 'client',
                            '2101' => 'supplier',
                            '1101' => 'fund',
                            '1102' => 'bank',
                            '57' => 'expense',
                            '42' => 'revenue',
                            '2104' => 'creditor',
                            '1106' => 'debtor',
                            '31' => 'partner',
                            '1202' => 'asset',
                            '2102' => 'employee',
                            '1104' => 'store',
                            '32' => 'current-partner',
                        ];
                        $type = $map[$code] ?? null;
                    }
                }
            }

            // $label = match ($type) {
            //     'client' => 'العملاء',
            //     'supplier' => 'الموردين',
            //     'fund' => 'الصناديق',
            //     'bank' => 'البنوك',
            //     'employee' => 'الموظفين',
            //     'store' => 'المخازن',
            //     'expense' => 'المصروفات',
            //     'revenue' => 'الإيرادات',
            //     'creditor' => 'دائنين متنوعين',
            //     'debtor' => 'مدينين متنوعين',
            //     'partner' => 'الشركاء',
            //     'current-partner' => 'جارى الشركاء',
            //     'asset' => 'الأصول الثابتة',
            //     'rentable' => 'الأصول القابلة للتأجير',
            //     default => null,
            // };

            // if ($label) {
            //     $action = $request->route()?->getActionMethod();

            //     $permissionMap = [
            //         'index' => "عرض $label",
            //         'create' => "إضافة $label",
            //         'store' => "إضافة $label",
            //         'edit' => "تعديل $label",
            //         'update' => "تعديل $label",
            //         'destroy' => "حذف $label",
            //     ];

            //     if (isset($permissionMap[$action])) {
            //         $permission = $permissionMap[$action];

            //         if (!Auth::check() || !Auth::user()->can($permission)) {
            //             abort(403, 'ليس لديك صلاحية لهذا الإجراء.');
            //         }
            //     }
            // }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $accountsQuery = AccHead::query()->where('is_basic', 0);

        if ($type) {
            $patterns = [
                'client' => '1103%',   // العملاء
                'supplier' => '2101%',   // الموردين
                'fund' => '1101%',   // الصناديق
                'bank' => '1102%',   // البنوك
                'expense' => '57%',      // المصروفات
                'revenue' => '42%',      // الإيرادات
                'creditor' => '2104%',   // دائنين اخرين
                'debtor' => '1106%',   // مدينين آخرين
                'partner' => '31%',   // الشريك الرئيسي
                'current-partner' => '32%',   // الشريك الرئيسي
                'asset' => '12%',      // الأصول
                'employee' => '2102%',   // الموظفين
                'rentable' => '1202%',   // مباني
                'store' => '1104%',   // المخازن
            ];

            $accountsQuery->where('code', 'like', $patterns[$type] ?? '9999%');
        }

        $accounts = $accountsQuery->get(['id', 'code', 'balance', 'address', 'phone', 'aname', 'is_basic', 'is_stock', 'is_fund', 'employees_expensses', 'deletable', 'editable', 'rentable', 'phone', 'address']);
        return view('accounts.index', compact('accounts'));
    }

    public function summary(Request $request)
    {
        $accId = $request->input('acc_id');
        return redirect()->route('accounts.show', $accId);
    }

    public function create(Request $request)
    {
        $branches = userBranches();
        $parent = $request->query('parent', 0);
        $last_id = '';
        $resacs = [];

        if ($parent) {
            $lastAccount = DB::table('acc_head')
                ->where('code', 'like', $parent . '%')
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
        // $countries = Country::all()->pluck('title', 'id');
        // $cities = City::all()->pluck('title', 'id');
        // $states = State::all()->pluck('title', 'id');
        // $towns = Town::all()->pluck('title', 'id');
        // dd($branches);
        return view('accounts.create', compact('parent', 'last_id', 'resacs', 'branches'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'code' => 'required|string|max:9|unique:acc_head,code',
            'aname' => 'required|string|max:100|unique:acc_head,aname',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'e_mail' => 'nullable|email|max:100',
            'constant' => 'nullable|string|max:50',
            'is_stock' => 'nullable',
            'is_fund' => 'nullable',
            'rentable' => 'nullable',
            'employees_expensses' => 'nullable',
            'parent_id' => 'required|integer',
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
            // الحقول الجديدة
            'zatca_name' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'zatca_address' => 'nullable|string|max:250',
            'company_type' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
        ], [
            'code.required' => __('validation.custom.code.required'),
            'code.max' => __('validation.custom.code.max'),
            'aname.required' => __('validation.custom.aname.required'),
            'aname.max' => __('validation.custom.aname.max'),
            'phone.max' => __('validation.custom.phone.max'),
            'address.max' => __('validation.custom.address.max'),
            'e_mail.email' => __('validation.custom.e_mail.email'),
            'e_mail.max' => __('validation.custom.e_mail.max'),
            'constant.max' => __('validation.custom.constant.max'),
            'parent_id.integer' => __('validation.custom.parent_id.integer'),
            'nature.max' => __('validation.custom.nature.max'),
            'kind.max' => __('validation.custom.kind.max'),
            'start_balance.numeric' => __('validation.custom.start_balance.numeric'),
            'credit.numeric' => __('validation.custom.credit.numeric'),
            'debit.numeric' => __('validation.custom.debit.numeric'),
            'balance.numeric' => __('validation.custom.balance.numeric'),
            'info.max' => __('validation.custom.info.max'),
            'tenant.integer' => __('validation.custom.tenant.integer'),
            'branch.integer' => __('validation.custom.branch.integer'),
            // رسائل التحقق للحقول الجديدة
            'zatca_name.max' => __('validation.custom.zatca_name.max'),
            'vat_number.max' => __('validation.custom.vat_number.max'),
            'national_id.max' => __('validation.custom.national_id.max'),
            'zatca_address.max' => __('validation.custom.zatca_address.max'),
            'company_type.max' => __('validation.custom.company_type.max'),
            'nationality.max' => __('validation.custom.nationality.max'),
            'branch_id.required' => 'الفرع مطلوب.',
            'branch_id.exists' => 'الفرع المختار غير صحيح.',
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
            // الحقول الجديدة
            'zatca_name' => $request->zatca_name,
            'vat_number' => $request->vat_number,
            'national_id' => $request->national_id,
            'zatca_address' => $request->zatca_address,
            'company_type' => $request->company_type,
            'nationality' => $request->nationality,
            // حقول العنوان
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'state_id' => $request->state_id,
            'town_id' => $request->town_id,
            'branch_id' => $request->branch_id
        ]);

        $parent = null;

        if ($request->parent_id) {
            $parentAcc = AccHead::find($request->parent_id);
            if ($parentAcc) {
                $parentCode = substr($parentAcc->code, 0, 4);

                $map = [
                    '1103' => 'client',
                    '2101' => 'supplier',
                    '1101' => 'fund',
                    '1102' => 'bank',
                    '57' => 'expense',
                    '42' => 'revenue',
                    '2104' => 'creditor',
                    '1106' => 'debtor',
                    '31' => 'partner',
                    '1202' => 'asset',
                    '2102' => 'employee',
                    '1104' => 'store',
                    '32' => 'current-partner',
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
        $countries = Country::all()->pluck('title', 'id');
        $cities = City::all()->pluck('title', 'id');
        $states = State::all()->pluck('title', 'id');
        $towns = Town::all()->pluck('title', 'id');

        return view('accounts.edit', compact('account', 'resacs', 'parent', 'countries', 'cities', 'states', 'towns'));
    }

    public function edit($id)
    {
        $account = AccHead::findOrFail($id);

        $parent = substr($account->code, 0, -3);

        $resacs = DB::table('acc_head')
            ->where('is_basic', 1)
            ->where('code', 'like', $parent . '%')
            ->orderBy('code')
            ->get();
        $countries = Country::all()->pluck('title', 'id');
        $cities = City::all()->pluck('title', 'id');
        $states = State::all()->pluck('title', 'id');
        $towns = Town::all()->pluck('title', 'id');

        return view('accounts.edit', compact('account', 'resacs', 'parent', 'countries', 'cities', 'states', 'towns'));
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
            // الحقول الجديدة
            'zatca_name' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'zatca_address' => 'nullable|string|max:250',
            'company_type' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
        ], [
            // رسائل التحقق للحقول الجديدة
            'zatca_name.max' => __('validation.custom.zatca_name.max'),
            'vat_number.max' => __('validation.custom.vat_number.max'),
            'national_id.max' => __('validation.custom.national_id.max'),
            'zatca_address.max' => __('validation.custom.zatca_address.max'),
            'company_type.max' => __('validation.custom.company_type.max'),
            'nationality.max' => __('validation.custom.nationality.max'),
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
            'mdtime' => now(),
            // الحقول الجديدة
            'zatca_name' => $request->zatca_name,
            'vat_number' => $request->vat_number,
            'national_id' => $request->national_id,
            'zatca_address' => $request->zatca_address,
            'company_type' => $request->company_type,
            'nationality' => $request->nationality,
            // حقول
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'state_id' => $request->state_id,
            'town_id' => $request->town_id,
        ]);

        $parent = null;

        if ($request->parent_id) {
            $parentAcc = AccHead::find($request->parent_id);
            if ($parentAcc) {
                $parentCode = substr($parentAcc->code, 0, 4);

                $map = [
                    '1103' => 'client',
                    '2101' => 'supplier',
                    '1101' => 'fund',
                    '1102' => 'bank',
                    '57' => 'expense',
                    '42' => 'revenue',
                    '2104' => 'creditor',
                    '1106' => 'debtor',
                    '31' => 'partner',
                    '3201' => 'current-partner',
                    '12' => 'asset',
                    '2102' => 'employee',
                    '1202' => 'rentable',
                    '1104' => 'store',
                ];

                $parent = $map[$parentCode] ?? null;
            }
        }
        return redirect()->route('accounts.index', ['type' => $parent])
            ->with('success', 'تم تعديل الحساب بنجاح');
    }

    public function destroy($id)
    {
        $acc = AccHead::findOrFail($id);
        if (!$acc->deletable) {
            return redirect()->back()->with('error', 'هذا الحساب غير قابل للحذف.');
        }
        $hasTransactions = DB::table('journal_details')->where('account_id', $id)->exists();
        if ($hasTransactions) {
            return redirect()->back()->with('error', 'لا يمكن حذف الحساب لأنه مرتبط بحركات محاسبية.');
        }

        $parent = null;
        if ($acc->parent_id) {
            $parentAcc = AccHead::find($acc->parent_id);
            if ($parentAcc) {
                $parentCode = substr($parentAcc->code, 0, 4);

                $map = [
                    '1103' => 'client',
                    '2101' => 'supplier',
                    '1101' => 'fund',
                    '1102' => 'bank',
                    '57' => 'expense',
                    '42' => 'revenue',
                    '2104' => 'creditor',
                    '1106' => 'debtor',
                    '31' => 'partner',
                    '3201' => 'current-partner',
                    '12' => 'asset',
                    '2102' => 'employee',
                    '1202' => 'rentable',
                    '1104' => 'store',
                ];

                $parent = $map[$parentCode] ?? null;
            }
        }
        $acc->delete();
        return redirect()->route('accounts.index', ['type' => $parent])
            ->with('success', 'تم حذف الحساب بنجاح.');
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
