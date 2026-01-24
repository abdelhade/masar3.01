<?php

namespace Modules\Accounts\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounts\Models\AccHead;
use Modules\Accounts\Models\AccountsType;
use Modules\HR\Models\City;
use Modules\HR\Models\Country;
use Modules\HR\Models\State;
use Modules\HR\Models\Town;

class AccHeadController extends Controller
{
    private const ACCOUNT_TYPE_MAP = [
        '1103' => 'clients',
        '2101' => 'suppliers',
        '1101' => 'funds',
        '1102' => 'banks',
        '5' => 'expenses',
        '42' => 'revenues',
        '2104' => 'creditors',
        '1106' => 'debtors',
        '31' => 'partners',
        '32' => 'current-partners',
        '12' => 'assets',
        '2102' => 'employees',
        '1104' => 'warhouses',
        '1202' => 'rentables',
        '1105' => 'check-portfolios-incoming', // حافظات أوراق القبض
        '2103' => 'check-portfolios-outgoing', // حافظات أوراق الدفع
    ];

    public function __construct()
    {
        // حماية صفحة الإحصائيات
        $this->middleware('can:view basicData-statistics')->only(['basicDataStatistics']);
        $this->middleware('can:view opening-balance-accounts')->only(['startBalance']);
        $this->middleware('can:view accounts-balance-sheet')->only(['balanceSheet']);
        // ملاحظة: صلاحيات index يتم فحصها في IndexAccountRequest::authorize()

        // حماية صفحات التعديل والحذف حسب نوع الحساب
        $this->middleware(function ($request, $next) {
            $id = $request->route('account') ?? $request->route('id');

            if ($id) {
                $account = AccHead::find($id);
                if ($account) {
                    $type = $this->determineAccountType($account->code);
                    if ($type) {
                        $action = $this->getActionName($request);

                        $this->checkPermissionByType($type, $action);
                    }
                }
            }

            return $next($request);
        })->only(['edit', 'update', 'destroy']);

        // حماية صفحة الإضافة حسب الـ parent
        $this->middleware(function ($request, $next) {
            $parentId = null;

            // في create: parent يأتي من query string (كود الـ parent)
            if ($request->routeIs('accounts.create')) {
                $parentCode = $request->query('parent');
                if ($parentCode) {
                    $type = $this->determineAccountType($parentCode);
                    if ($type) {
                        $this->checkPermissionByType($type, 'create');
                    }
                }
            }

            // في store: parent_id يأتي من form body (id الـ parent)
            if ($request->routeIs('accounts.store')) {
                $parentId = $request->input('parent_id');
                if ($parentId) {
                    $parentAccount = AccHead::find($parentId);
                    if ($parentAccount) {
                        $type = $this->determineAccountType($parentAccount->code);
                        if ($type) {
                            $this->checkPermissionByType($type, 'create');
                        }
                    }
                }
            }

            return $next($request);
        })->only(['create', 'store']);
    }

    private function checkIndexPermission($type): void
    {
        $permissionName = $this->getPermissionNameByType($type);
        $permission = "view $permissionName";

        $user = Auth::user();
        if (! $user || ! $user->can($permission)) {
            abort(403, 'غير مصرح لك بعرض هذه الصفحة');
        }
    }

    private function checkPermissionByType($type, $action): void
    {
        $permissionName = $this->getPermissionNameByType($type);
        $permission = "$action $permissionName";

        $user = Auth::user();
        if (! $user || ! $user->can($permission)) {
            abort(403, 'غير مصرح لك بهذا الإجراء');
        }
    }

    private function getActionName($request)
    {
        $route = $request->route();
        if ($route) {
            $action = $request->route()->getActionMethod();

            if (in_array($action, ['edit', 'update'])) {
                return 'edit';
            } elseif (in_array($action, ['create', 'store'])) {
                return 'create';
            } elseif ($action === 'destroy') {
                return 'delete';
            } elseif ($action === 'show') {
                return 'view';
            }
        }

        return 'view';
    }

    private function getPermissionNameByType($type): string
    {
        $permissionMap = [
            'clients' => 'Clients',
            'suppliers' => 'Suppliers',
            'funds' => 'Funds',
            'banks' => 'Banks',
            'employees' => 'Employees',
            'warhouses' => 'warhouses',
            'expenses' => 'Expenses',
            'revenues' => 'Revenues',
            'creditors' => 'various_creditors',
            'debtors' => 'various_debtors',
            'partners' => 'partners',
            'current-partners' => 'current_partners',
            'assets' => 'assets',
            'rentables' => 'rentables',
            'check-portfolios-incoming' => 'check-portfolios-incoming',
            'check-portfolios-outgoing' => 'check-portfolios-outgoing',
        ];

        return $permissionMap[$type] ?? 'accounts';
    }

    public function index(\Modules\Accounts\Http\Requests\IndexAccountRequest $request)
    {

        $type = $request->getType();

        // إذا لم يكن هناك type، لا نعرض أي حسابات
        if (! $type) {
            $accounts = AccHead::whereRaw('1 = 0')->paginate($request->getPerPage());

            return view('accounts::index', compact('accounts'));
        }

        // Build query using scopes
        $accounts = AccHead::nonBasic()
            ->byType($type)
            ->search($request->getSearch())
            ->withBasicRelations()
            ->orderBy('code')
            ->paginate($request->getPerPage())
            ->withQueryString();

        return view('accounts::index', compact('accounts'));
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
                ->where('code', 'like', $parent.'%')
                ->orderByDesc('id')
                ->first();

            if ($lastAccount) {
                $suffix = str_replace($parent, '', $lastAccount->code);
                $next = str_pad((int) $suffix + 1, 3, '0', STR_PAD_LEFT);
                $last_id = $parent.$next;
            } else {
                $last_id = $parent.'001';
            }

            $resacs = DB::table('acc_head')
                ->where('is_basic', '1')
                ->where('code', 'like', $parent.'%')
                ->orderBy('code')
                ->get();
        } else {
            $resacs = DB::table('acc_head')->where('is_basic', '1')->orderBy('code')->get();
        }

        $currencies = collect();
        if (isMultiCurrencyEnabled()) {
            $currencies = \Modules\Settings\Models\Currency::get();
        }

        return view('accounts::create', compact('parent', 'last_id', 'resacs', 'branches', 'accountTypes', 'currencies'));
    }

    public function store(Request $request)
    {
        // Debug log for client/supplier submissions to inspect missing inputs
        $parentCode = $request->query('parent') ?? $request->input('parent_id');
        if (in_array($parentCode, ['1103', '2101'])) {
            Log::info('AccHeadController.store - incoming_request', $request->all());
        }

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
            'parent_id' => 'required|integer|exists:acc_head,id',
            'nature' => 'nullable|string|max:50',
            'kind' => 'nullable|string|max:50',
            'acc_type' => 'nullable|integer|exists:accounts_types,id',
            'is_basic' => 'nullable',
            'start_balance' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'debit' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'debit_limit' => 'nullable|numeric|min:0',
            'secret' => 'nullable',
            'info' => 'nullable|string|max:500',
            'tenant' => 'nullable|integer',
            'branch' => 'nullable|integer',
            'deletable' => 'nullable',
            'editable' => 'nullable',
            'isdeleted' => 'nullable',
            'reserve' => 'nullable',
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
            'currency_id' => 'nullable|integer|exists:currencies,id',
        ]);

        if (! isMultiCurrencyEnabled()) {
            $validated['currency_id'] = null;
        }

        if (isset($validated['acc_type']) && ! empty($validated['acc_type'])) {
            $account_type = $validated['acc_type'];
        } else {
            // جلب parent account
            $parentAccount = AccHead::find(id: $validated['parent_id']);
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
                'acc_type' => $account_type,
                'is_basic' => $validated['is_basic'] ?? 0,
                'start_balance' => $validated['start_balance'] ?? 0,
                'credit' => $validated['credit'] ?? 0,
                'debit' => $validated['debit'] ?? 0,
                'balance' => $validated['balance'] ?? 0,
                'debit_limit' => $validated['debit_limit'] ?? null,
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
                'currency_id' => $validated['currency_id'] ?? null,
            ]);

            if (($validated['reserve'] ?? 0) == 1) {
                $this->createDepreciationAccounts($asset, $validated['branch_id']);
            }

            // إذا كان الحساب شريك (acc_type = 11)، ننشئ جاري شريك تلقائياً
            if ($account_type == 11) {
                $this->createCurrentPartnerAccount($asset);
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
                ->with('error', 'حدث خطأ أثناء إضافة الحساب: '.$e->getMessage());
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
            'aname' => 'مجمع إهلاك '.$asset->aname,
            'parent_id' => 40,
            'is_basic' => 0,
            'deletable' => 0,
            'editable' => 1,
            'crtime' => now(),
            'mdtime' => now(),
            'branch_id' => $branchId,
            'accountable_id' => $asset->id,
            'accountable_type' => AccHead::class,
            'acc_type' => 15,
        ]);

        $lastExp = AccHead::where('parent_id', 77)
            ->orderByDesc('id')
            ->first();
        $expCode = $lastExp ? (int) $lastExp->code + 1 : 530201;

        AccHead::create([
            'code' => (string) $expCode,
            'aname' => 'مصروف إهلاك '.$asset->aname,
            'parent_id' => 77,
            'is_basic' => 0,
            'deletable' => 0,
            'editable' => 1,
            'crtime' => now(),
            'mdtime' => now(),
            'branch_id' => $branchId,
            'accountable_id' => $asset->id,
            'accountable_type' => AccHead::class,
            'acc_type' => 16,
        ]);
    }

    /**
     * Update all depreciation accounts linked to a specific asset account
     * تحديث جميع حسابات الإهلاك المرتبطة بحساب أصل معين
     */
    private function updateDepreciationAccounts(AccHead $asset, int $branchId): void
    {
        try {
            // Update all depreciation accounts (type 15) linked to this asset
            $depreciationUpdated = AccHead::where('accountable_id', $asset->id)
                ->where('acc_type', 15)
                ->update([
                    'aname' => 'مجمع إهلاك '.$asset->aname,
                    'branch_id' => $branchId,
                    'mdtime' => now(),
                ]);

            Log::info('Depreciation accounts updated: '.$depreciationUpdated);

            // Update all expense accounts (type 16) linked to this asset
            $expenseUpdated = AccHead::where('accountable_id', $asset->id)
                ->where('acc_type', 16)
                ->update([
                    'aname' => 'مصروف إهلاك '.$asset->aname,
                    'branch_id' => $branchId,
                    'mdtime' => now(),
                ]);

            Log::info('Expense accounts updated: '.$expenseUpdated);
        } catch (\Exception $e) {
            Log::error('Error updating depreciation accounts: '.$e->getMessage());
            // Don't throw the exception, just log it so the main update can continue
        }
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
            if (! str_starts_with($asset->code, '12')) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا ليس حساب أصل صحيح',
                ], 400);
            }

            DB::beginTransaction();

            $this->updateDepreciationAccounts($asset, $asset->branch_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حسابات الإهلاك بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حسابات الإهلاك: '.$e->getMessage(),
            ], 500);
        }
    }

    private function determineParentType(?int $parentId): ?string
    {
        if (! $parentId) {
            return null;
        }

        $parentAcc = AccHead::find($parentId);
        if (! $parentAcc) {
            return null;
        }

        return $this->determineAccountType($parentAcc->code);
    }

    /**
     * تحديد نوع الحساب من الكود
     */
    private function determineAccountType(string $code): ?string
    {
        // ترتيب الفحص من الأطول للأقصر لتجنب التداخل
        $sortedMap = self::ACCOUNT_TYPE_MAP;
        uksort($sortedMap, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($sortedMap as $prefix => $type) {
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
            ->where('code', 'like', $parent.'%')
            ->orderBy('code')
            ->get();
        $countries = Country::all()->pluck('title', 'id');
        $cities = City::all()->pluck('title', 'id');
        $states = State::all()->pluck('title', 'id');
        $towns = Town::all()->pluck('title', 'id');
        $accountTypes = AccountsType::all();

        return view('accounts::edit', compact('account', 'resacs', 'parent', 'countries', 'cities', 'states', 'towns', 'accountTypes'));
    }

    public function edit($id)
    {
        try {
            $account = AccHead::findOrFail($id);

            // جلب جميع الحسابات الأساسية
            $resacs = DB::table('acc_head')
                ->where('is_basic', 1)
                ->orderBy('code')
                ->get();

            $parent = substr($account->code, 0, -3);
            $countries = Country::all()->pluck('title', 'id');
            $cities = City::all()->pluck('title', 'id');
            $states = State::all()->pluck('title', 'id');
            $towns = Town::all()->pluck('title', 'id');
            $accountTypes = AccountsType::all();
            $branches = userBranches();

            $currencies = collect();
            if (isMultiCurrencyEnabled()) {
                $currencies = \Modules\Settings\Models\Currency::active()->get();
            }

            return view('accounts::edit', compact('account', 'resacs', 'parent', 'countries', 'cities', 'states', 'towns', 'accountTypes', 'branches', 'currencies'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ في تحميل صفحة التعديل: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $account = AccHead::findOrFail($id);

        // Debug log for client/supplier updates to inspect missing inputs
        $parentCode = $this->determineAccountType($account->code);

        if (in_array($parentCode, ['clients', 'suppliers'])) {
            Log::info('AccHeadController.update - incoming_request', $request->all());
        }

        try {
            $validated = $request->validate([
                'aname' => 'required|string|max:100|unique:acc_head,aname,'.$id,
                'phone' => 'nullable|string|max:15',
                'address' => 'nullable|string|max:250',
                'e_mail' => 'nullable|email|max:100',
                'constant' => 'nullable|string|max:50',
                'is_stock' => 'nullable',
                'is_fund' => 'nullable',
                'rentable' => 'nullable',
                'employees_expensses' => 'nullable',
                'parent_id' => 'required|integer|exists:acc_head,id',
                'nature' => 'nullable|string|max:50',
                'kind' => 'nullable|string|max:50',
                'acc_type' => 'nullable|integer',
                'is_basic' => 'nullable',
                'debit_limit' => 'nullable|numeric|min:0',
                'secret' => 'nullable',
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
                'currency_id' => 'nullable|integer|exists:currencies,id',
                'reserve' => 'nullable', // Added for depreciation accounts handling
            ]);

            if (! isMultiCurrencyEnabled()) {
                $validated['currency_id'] = null;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            DB::beginTransaction();

            Log::info('Updating account', ['id' => $id, 'validated' => $validated]);

            // تحديث البيانات الأساسية
            $account->aname = $validated['aname'];
            $account->phone = $validated['phone'] ?? null;
            $account->address = $validated['address'] ?? null;
            $account->e_mail = $validated['e_mail'] ?? null;
            $account->constant = $validated['constant'] ?? null;
            $account->is_stock = $validated['is_stock'] ?? 0;
            $account->is_fund = $validated['is_fund'] ?? 0;
            $account->rentable = $validated['rentable'] ?? 0;
            $account->employees_expensses = $validated['employees_expensses'] ?? 0;
            $account->parent_id = $validated['parent_id'];
            $account->nature = $validated['nature'] ?? null;
            $account->kind = $validated['kind'] ?? null;
            $account->acc_type = $validated['acc_type'] ?? $account->acc_type;
            $account->is_basic = $validated['is_basic'] ?? 0;
            $account->debit_limit = $validated['debit_limit'] ?? null;
            $account->secret = $validated['secret'] ?? 0;
            $account->mdtime = now();
            $account->info = $validated['info'] ?? null;
            $account->zatca_name = $validated['zatca_name'] ?? null;
            $account->vat_number = $validated['vat_number'] ?? null;
            $account->national_id = $validated['national_id'] ?? null;
            $account->zatca_address = $validated['zatca_address'] ?? null;
            $account->company_type = $validated['company_type'] ?? null;
            $account->nationality = $validated['nationality'] ?? null;
            $account->country_id = $validated['country_id'] ?? null;
            $account->city_id = $validated['city_id'] ?? null;
            $account->state_id = $validated['state_id'] ?? null;
            $account->town_id = $validated['town_id'] ?? null;
            $account->branch_id = $validated['branch_id'];
            $account->save();

            // Handle depreciation accounts for assets (create if reserve = 1 and not exists)
            if (($validated['reserve'] ?? 0) == 1) {
                try {
                    // Check using parent_id relationship instead of account_id
                    $hasDepreciationAccounts = AccHead::where('parent_id', $account->id)
                        ->whereIn('acc_type', [15, 16])
                        ->exists();

                    if (! $hasDepreciationAccounts) {
                        $this->createDepreciationAccounts($account, $validated['branch_id']);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not create depreciation accounts', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();

            Log::info('Account updated successfully', ['id' => $id, 'aname' => $account->aname]);

            // Determine account type for redirect
            $parentType = $this->determineAccountType($account->code);
            $redirectType = $parentType;
            if ($parentType === 'assets' && str_starts_with($account->code, '1202')) {
                $redirectType = 'rentables';
            }

            return redirect()
                ->route('accounts.index', ['type' => $redirectType])
                ->with('success', 'تم تحديث الحساب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الحساب: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $acc = AccHead::findOrFail($id);

        if (! $acc->deletable) {
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
        return view('accounts::startBalance.manage-start-balance');
    }

    public function accountMovementReport($accountId = null)
    {
        return view('accounts::reports.account-movement', compact('accountId'));
    }

    public function balanceSheet()
    {
        return view('accounts::reports.manage-balance-sheet');
    }

    public function basicDataStatistics()
    {
        $stats = [];
        $account_types = AccountsType::all()->pluck('id', 'name')->toArray();

        foreach (self::ACCOUNT_TYPE_MAP as $prefix => $type) {
            $account_type_id = $account_types[$type] ?? null;
            if ($account_type_id) {
                $stats[$type] = [
                    'count' => AccHead::where('acc_type', $account_type_id)
                        ->where('is_basic', 0)
                        ->where('isdeleted', 0)
                        ->count(),
                    'total_balance' => AccHead::where('acc_type', $account_type_id)
                        ->where('is_basic', 0)
                        ->where('isdeleted', 0)
                        ->sum('balance'),
                    'highest_balance' => AccHead::where('acc_type', $account_type_id)
                        ->where('is_basic', 0)
                        ->where('isdeleted', 0)
                        ->orderByDesc('balance')
                        ->first(['aname', 'balance']),
                    'active_accounts' => AccHead::where('acc_type', $account_type_id)
                        ->where('is_basic', 0)
                        ->where('isdeleted', 0)
                        ->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('journal_details')
                                ->whereColumn('journal_details.account_id', 'acc_head.id')
                                ->where('journal_details.crtime', '>=', now()->subDays(30));
                        })
                        ->count(),
                ];
                $stats[$type]['highest_balance_name'] = $stats[$type]['highest_balance'] ? $stats[$type]['highest_balance']->aname : 'لا يوجد';
                $stats[$type]['highest_balance_amount'] = $stats[$type]['highest_balance'] ? $stats[$type]['highest_balance']->balance : 0;
            } else {
                // Initialize missing types with default values
                $stats[$type] = [
                    'count' => 0,
                    'total_balance' => 0,
                    'highest_balance_name' => 'لا يوجد',
                    'highest_balance_amount' => 0,
                    'active_accounts' => 0,
                ];
            }
        }

        // بيانات للشارتس
        $stats['chart_data'] = [
            'counts' => array_map(function ($type) use ($stats) {
                return $stats[$type]['count'] ?? 0;
            }, array_values(self::ACCOUNT_TYPE_MAP)),
            'balances' => array_map(function ($type) use ($stats) {
                return $stats[$type]['total_balance'] ?? 0;
            }, array_values(self::ACCOUNT_TYPE_MAP)),
            'labels' => array_map(function ($type) {
                return __("accounts.types.$type");
            }, array_values(self::ACCOUNT_TYPE_MAP)),
        ];

        return view('accounts::statistics.basic-data-statistics', compact('stats'));
    }

    /**
     * إنشاء حساب جاري الشريك تلقائياً عند إنشاء حساب شريك
     */
    private function createCurrentPartnerAccount(AccHead $partnerAccount): void
    {
        // جلب الحساب الأساسي (كود 21081 - جاري الشركاء)
        $parentAccount = DB::table('acc_head')
            ->where('code', '21081')
            ->where('is_basic', 1)
            ->first();

        if (! $parentAccount) {
            return;
        }

        // توليد كود جديد لجاري الشريك (21081xx)
        $lastAccount = DB::table('acc_head')
            ->where('code', 'like', '21081%')
            ->where('code', '!=', '21081')
            ->orderByDesc('id')
            ->first();

        if ($lastAccount) {
            $suffix = str_replace('21081', '', $lastAccount->code);
            $next = str_pad(((int) $suffix + 1), 2, '0', STR_PAD_LEFT);
            $newCode = '21081'.$next;
        } else {
            $newCode = '2108101';
        }

        // إنشاء حساب جاري الشريك
        AccHead::create([
            'code' => $newCode,
            'aname' => 'جاري الشريك - '.$partnerAccount->aname,
            'phone' => $partnerAccount->phone,
            'address' => $partnerAccount->address,
            'parent_id' => $parentAccount->id,
            'branch_id' => $partnerAccount->branch_id,
            'acc_type' => '12',
            'zatca_name' => $partnerAccount->zatca_name,
            'vat_number' => $partnerAccount->vat_number,
            'national_id' => $partnerAccount->national_id,
            'zatca_address' => $partnerAccount->zatca_address,
            'company_type' => $partnerAccount->company_type,
            'nationality' => $partnerAccount->nationality,
            'is_basic' => 0,
            'deletable' => 1,
            'editable' => 1,
            'start_balance' => 0,
            'credit' => 0,
            'debit' => 0,
            'balance' => 0,
            'isdeleted' => 0,
            'crtime' => now(),
            'mdtime' => now(),
        ]);
    }
}
