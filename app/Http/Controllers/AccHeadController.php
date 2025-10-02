<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use App\Models\AccountsType;
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
    // خريطة ربط الأكواد بأنواع الحسابات
    private const ACCOUNT_TYPE_MAP = [
        '1103' => 'clients',
        '2101' => 'suppliers',
        '1101' => 'funds',
        '1102' => 'banks',
        '57' => 'expenses',
        '42' => 'revenues',
        '2104' => 'creditors',
        '1106' => 'debtors',
        '31' => 'partners',
        '32' => 'current-partners',
        '12' => 'assets',
        '2102' => 'employees',
        '1104' => 'warhouses',
        '1202' => 'rentables',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $type = $request->query('type');

            if (!$type) {
                $id = $request->route('account') ?? $request->route('id');

                if ($id) {
                    $account = AccHead::find($id);

                    if ($account) {
                        $type = $this->determineAccountType($account->code);
                    }
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $accountsQuery = AccHead::query()
            ->where('is_basic', 0);
          

        if ($type) {
            // Get account type ID from accounts_types table
            $accountType = AccountsType::where('name', $type)->first();
            
            if ($accountType) {
                $accountsQuery->where('acc_type', $accountType->id);
            }
        }

        $accounts = $accountsQuery->with('accountType')
            ->get([
                'id', 'code','acc_type', 'balance', 'address', 'phone', 'aname', 
                'is_basic', 'is_stock', 'is_fund', 'employees_expensses', 
                'deletable', 'editable', 'rentable', 'acc_type'
            ]);

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
        $accountTypes = AccountsType::all();

        if ($parent) {
            $lastAccount = DB::table('acc_head')
                ->where('code', 'like', $parent . '%')
                ->orderByDesc('id')
                ->first();

            if ($lastAccount) {
                $suffix = str_replace($parent, '', $lastAccount->code);
                $next = str_pad((int) $suffix + 1, 3, '0', STR_PAD_LEFT);
                $last_id = $parent . $next;
            } else {
                $last_id = $parent . '001';
            }

            $resacs = DB::table('acc_head')
                ->where('is_basic', '1')
                ->where('code', 'like', $parent . '%')
                ->orderBy('code')
                ->get();
        } else {
            $resacs = DB::table('acc_head')->where('is_basic', '1')->orderBy('code')->get();
        }

        return view('accounts.create', compact('parent', 'last_id', 'resacs', 'branches', 'accountTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:9|unique:acc_head,code',
            'aname' => 'required|string|max:100|unique:acc_head,aname',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'e_mail' => 'nullable|email|max:100',
            'constant' => 'nullable|string|max:50',
            'is_stock' => 'nullable|boolean',
            'is_fund' => 'nullable|boolean',
            'rentable' => 'nullable|boolean',
            'employees_expensses' => 'nullable|boolean',
            'parent_id' => 'required|integer|exists:acc_head,id',
            'nature' => 'nullable|string|max:50',
            'kind' => 'nullable|string|max:50',
            'acc_type' => 'nullable|integer|exists:accounts_types,id',
            'is_basic' => 'nullable|boolean',
            'start_balance' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'secret' => 'nullable|boolean',
            'info' => 'nullable|string|max:500',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
            'deletable' => 'nullable|boolean',
            'editable' => 'nullable|boolean',
            'isdeleted' => 'nullable|boolean',
            'reserve' => 'nullable|boolean',
            'zatca_name' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'zatca_address' => 'nullable|string|max:250',
            'company_type' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'country_id' => 'nullable|integer|exists:countries,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'town_id' => 'nullable|integer|exists:towns,id',
            'branch_id' => 'required|exists:branches,id',
            'acc_type' => 'nullable|integer|exists:accounts_types,id',
        ]);

        if (isset($validated['acc_type']) && !empty($validated['acc_type'])) {
            $account_type = $validated['acc_type'];
        } else {
            // جلب parent account
            $parentAccount = \App\Models\AccHead::find($validated['parent_id']);
            $account_type = $parentAccount ? $parentAccount->acc_type : null;
        }

        try {
            DB::beginTransaction();

            $asset = AccHead::create([
                'code' => $validated['code'],
                'deletable' => $validated['deletable'] ?? 1,
                'editable' => $validated['editable'] ?? 1,
                'aname' => $validated['aname'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'e_mail' => $validated['e_mail'] ?? null,
                'constant' => $validated['constant'] ?? null,
                'is_stock' => $validated['is_stock'] ?? 0,
                'is_fund' => $validated['is_fund'] ?? 0,
                'rentable' => $validated['rentable'] ?? 0,
                'employees_expensses' => $validated['employees_expensses'] ?? 0,
                'parent_id' => $validated['parent_id'],
                'nature' => $validated['nature'] ?? null,
                'kind' => $validated['kind'] ?? null,
                'acc_type' => $validated['acc_type'] ?? null,
                'is_basic' => $validated['is_basic'] ?? 0,
                'start_balance' => $validated['start_balance'] ?? 0,
                'credit' => $validated['credit'] ?? 0,
                'debit' => $validated['debit'] ?? 0,
                'balance' => $validated['balance'] ?? 0,
                'secret' => $validated['secret'] ?? 0,
                'crtime' => now(),
                'mdtime' => now(),
                'info' => $validated['info'] ?? null,
                'isdeleted' => $validated['isdeleted'] ?? 0,
                'tenant' => $validated['tenant'] ?? 0,
                'branch' => $validated['branch'] ?? 0,
                'zatca_name' => $validated['zatca_name'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'zatca_address' => $validated['zatca_address'] ?? null,
                'company_type' => $validated['company_type'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'state_id' => $validated['state_id'] ?? null,
                'town_id' => $validated['town_id'] ?? null,
                'branch_id' => $validated['branch_id'],
                'acc_type' => $validated['acc_type'],
            ]);

            if (($validated['reserve'] ?? 0) == 1) {
                $this->createDepreciationAccounts($asset, $validated['branch_id']);
            }

            $parentType = $this->determineParentType($validated['parent_id']);

            DB::commit();

            return redirect()
                ->route('accounts.index', ['type' => $parentType])
                ->with('success', 'تمت إضافة الحساب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة الحساب: ' . $e->getMessage());
        }
    }

    private function createDepreciationAccounts(AccHead $asset, int $branchId): void
    {
        $lastDep = AccHead::where('parent_id', 40)
            ->orderByDesc('id')
            ->first();
        $depCode = $lastDep ? (int) $lastDep->code + 1 : 12401;

        AccHead::create([
            'code' => (string) $depCode,
            'aname' => 'مجمع إهلاك ' . $asset->aname,
            'parent_id' => 40,
            'is_basic' => 0,
            'deletable' => 0,
            'editable' => 1,
            'crtime' => now(),
            'mdtime' => now(),
            'branch_id' => $branchId,
            'account_id' => $asset->id,
            'acc_type' => 15,
        ]);

        $lastExp = AccHead::where('parent_id', 77)
            ->orderByDesc('id')
            ->first();
        $expCode = $lastExp ? (int) $lastExp->code + 1 : 530201;

        AccHead::create([
            'code' => (string) $expCode,
            'aname' => 'مصروف إهلاك ' . $asset->aname,
            'parent_id' => 77,
            'is_basic' => 0,
            'deletable' => 0,
            'editable' => 1,
            'crtime' => now(),
            'mdtime' => now(),
            'branch_id' => $branchId,
            'account_id' => $asset->id,
            'acc_type' => 16,
        ]);
    }

    /**
     * Update all depreciation accounts linked to a specific asset account
     * تحديث جميع حسابات الإهلاك المرتبطة بحساب أصل معين
     */
    private function updateDepreciationAccounts(AccHead $asset, int $branchId): void
    {
        // Update all depreciation accounts (type 15) linked to this asset
        AccHead::where('account_id', $asset->id)
            ->where('acc_type', 15)
            ->update([
                'aname' => 'مجمع إهلاك ' . $asset->aname,
                'branch_id' => $branchId,
                'mdtime' => now(),
            ]);

        // Update all expense accounts (type 16) linked to this asset
        AccHead::where('account_id', $asset->id)
            ->where('acc_type', 16)
            ->update([
                'aname' => 'مصروف إهلاك ' . $asset->aname,
                'branch_id' => $branchId,
                'mdtime' => now(),
            ]);
    }

    /**
     * Public method to update depreciation accounts for a specific asset
     * طريقة عامة لتحديث حسابات الإهلاك لأصل معين
     */
    public function updateAssetDepreciationAccounts(Request $request, $assetId)
    {
        try {
            $asset = AccHead::findOrFail($assetId);
            
            // Validate that this is actually an asset account
            if (!str_starts_with($asset->code, '12')) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا ليس حساب أصل صحيح'
                ], 400);
            }
            
            DB::beginTransaction();
            
            $this->updateDepreciationAccounts($asset, $asset->branch_id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حسابات الإهلاك بنجاح'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حسابات الإهلاك: ' . $e->getMessage()
            ], 500);
        }
    }

    private function determineParentType(?int $parentId): ?string
    {
        if (!$parentId) {
            return null;
        }

        $parentAcc = AccHead::find($parentId);
        if (!$parentAcc) {
            return null;
        }

        return $this->determineAccountType($parentAcc->code);
    }

    /**
     * تحديد نوع الحساب من الكود
     */
    private function determineAccountType(string $code): ?string
    {
        foreach (self::ACCOUNT_TYPE_MAP as $prefix => $type) {
            if (str_starts_with($code, $prefix)) {
                return $type;
            }
        }

        return null;
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
        $accountTypes = AccountsType::all();

        return view('accounts.edit', compact('account', 'resacs', 'parent', 'countries', 'cities', 'states', 'towns', 'accountTypes'));
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
        $accountTypes = AccountsType::all();
        $branches = userBranches();
        return view('accounts.edit', compact('account', 'resacs', 'parent', 'countries', 'cities', 'states', 'towns', 'accountTypes', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $account = AccHead::findOrFail($id);
        
        $validated = $request->validate([
            'aname' => 'required|string|max:100|unique:acc_head,aname,' . $id,
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:250',
            'e_mail' => 'nullable|email|max:100',
            'constant' => 'nullable|string|max:50',
            'is_stock' => 'nullable|boolean',
            'is_fund' => 'nullable|boolean',
            'rentable' => 'nullable|boolean',
            'employees_expensses' => 'nullable|boolean',
            'parent_id' => 'required|integer|exists:acc_head,id',
            'nature' => 'nullable|string|max:50',
            'kind' => 'nullable|string|max:50',
            'acc_type' => 'nullable|integer',
            'is_basic' => 'nullable|boolean',
            'secret' => 'nullable|boolean',
            'info' => 'nullable|string|max:500',
            'zatca_name' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'zatca_address' => 'nullable|string|max:250',
            'company_type' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'country_id' => 'nullable|integer|exists:countries,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'town_id' => 'nullable|integer|exists:towns,id',
            'branch_id' => 'required|exists:branches,id',
            'reserve' => 'nullable|boolean', // Added for depreciation accounts handling
        ]);

        try {
            DB::beginTransaction();

            // تحديث البيانات الأساسية
            $account->update([
                'aname' => $validated['aname'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'e_mail' => $validated['e_mail'] ?? null,
                'constant' => $validated['constant'] ?? null,
                'is_stock' => $validated['is_stock'] ?? 0,
                'is_fund' => $validated['is_fund'] ?? 0,
                'rentable' => $validated['rentable'] ?? 0,
                'employees_expensses' => $validated['employees_expensses'] ?? 0,
                'parent_id' => $validated['parent_id'],
                'nature' => $validated['nature'] ?? null,
                'kind' => $validated['kind'] ?? null,
                'acc_type' => $validated['acc_type'] ?? null,
                'is_basic' => $validated['is_basic'] ?? 0,
                'secret' => $validated['secret'] ?? 0,
                'mdtime' => now(),
                'info' => $validated['info'] ?? null,
                'zatca_name' => $validated['zatca_name'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'zatca_address' => $validated['zatca_address'] ?? null,
                'company_type' => $validated['company_type'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'state_id' => $validated['state_id'] ?? null,
                'town_id' => $validated['town_id'] ?? null,
                'branch_id' => $validated['branch_id'],
            ]);

            // Check if this is an asset account (starts with '12') and update related depreciation accounts
            if (str_starts_with($account->code, '12')) {
                // Always update existing depreciation accounts when asset name changes
                $this->updateDepreciationAccounts($account, $validated['branch_id']);
            }

            // Handle depreciation accounts for assets (create new ones if reserve = 1)
            if (($validated['reserve'] ?? 0) == 1) {
                // Check if depreciation accounts already exist for this asset
                $existingDepreciationAccount = AccHead::where('account_id', $account->id)
                    ->where('acc_type', 15) // Depreciation account type
                    ->first();
                
                $existingExpenseAccount = AccHead::where('account_id', $account->id)
                    ->where('acc_type', 16) // Expense account type
                    ->first();
                
                // Only create new depreciation accounts if they don't exist
                if (!$existingDepreciationAccount || !$existingExpenseAccount) {
                    $this->createDepreciationAccounts($account, $validated['branch_id']);
                }
            }

            // تحديد نوع الحساب الأب للإعادة التوجيه
            $parentType = $this->determineParentType($validated['parent_id']);

            DB::commit();

            return redirect()
                ->route('accounts.index', ['type' => $parentType])
                ->with('success', __('messages.updated_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الحساب: ' . $e->getMessage());
        }
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

        $parentType = null;
        if ($acc->parent_id) {
            $parentAcc = AccHead::find($acc->parent_id);
            if ($parentAcc) {
                $parentType = $this->determineAccountType($parentAcc->code);
            }
        }

        $acc->delete();
        return redirect()
            ->route('accounts.index', ['type' => $parentType])
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