<?php

namespace Modules\Depreciation\Livewire;

use App\Models\JournalDetail;
use App\Models\JournalHead;
use App\Models\OperHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;
use Modules\Branches\Models\Branch;
use Modules\Depreciation\Models\AccountAsset;

class DepreciationManager extends Component
{
    use WithPagination;

    public $showModal = false;

    public $selectedAccount = null;

    public $editMode = false;

    public $itemId = null;

    // Asset form fields
    public $asset_name = '';

    public $purchase_date = '';

    public $purchase_cost = '';

    public $salvage_value = 0;

    // Depreciation form fields
    public $annual_depreciation_amount = '';

    public $depreciation_method = 'straight_line';

    public $useful_life_years = '';

    public $depreciation_date = '';

    public $notes = '';

    public $schedulePreview = [];

    // Search and filter
    public $search = '';

    public $filterBranch = '';

    public $filterStatus = '';

    public $selectedAssets = [];

    public $selectAll = false;

    protected $rules = [
        'asset_name' => 'nullable|string|max:255',
        'purchase_date' => 'nullable|date',
        'purchase_cost' => 'nullable|numeric|min:0',
        'salvage_value' => 'nullable|numeric|min:0',
        'annual_depreciation_amount' => 'nullable|numeric',
        'depreciation_method' => 'nullable|in:straight_line,double_declining,sum_of_years',
        'useful_life_years' => 'nullable|integer|min:1|max:100',
        'depreciation_date' => 'nullable|date',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'useful_life_years.min' => 'العمر الإنتاجي يجب أن يكون سنة واحدة على الأقل',
        'useful_life_years.max' => 'العمر الإنتاجي لا يمكن أن يتجاوز 100 سنة',
        'asset_name.string' => 'اسم الأصل يجب أن يكون نصاً',
        'asset_name.max' => 'اسم الأصل لا يجب أن يتجاوز 255 حرفاً',
        'purchase_date.date' => 'تاريخ الشراء غير صالح',
        'purchase_cost.numeric' => 'تكلفة الشراء يجب أن تكون رقماً',
        'purchase_cost.min' => 'تكلفة الشراء لا يمكن أن تكون سالبة',
        'salvage_value.numeric' => 'قيمة الخردة يجب أن تكون رقماً',
        'salvage_value.min' => 'قيمة الخردة لا يمكن أن تكون سالبة',
        'annual_depreciation_amount.numeric' => 'مبلغ الإهلاك السنوي يجب أن يكون رقماً',
        'depreciation_method.in' => 'طريقة الإهلاك المحددة غير صحيحة',
        'useful_life_years.integer' => 'العمر الإنتاجي يجب أن يكون عدداً صحيحاً',
        'depreciation_date.date' => 'تاريخ الإهلاك غير صالح',
        'notes.string' => 'حقل الملاحظات يجب أن يكون نصاً',
        'notes.max' => 'الملاحظات لا يجب أن تتجاوز 500 حرف',
    ];

    public function mount()
    {
        $this->depreciation_date = now()->format('Y-m-d');
        $this->recalculateSchedulePreview();
    }

    public function render()
    {
        $accountAssets = AccountAsset::query()
            ->with(['accHead', 'depreciationAccount', 'expenseAccount', 'accHead.branch'])
            ->when($this->search, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('aname', 'like', '%'.$this->search.'%');
                })->orWhere('asset_name', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterBranch, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('branch_id', $this->filterBranch);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get asset accounts for creating new assets
        $assetAccounts = AccHead::query()
            ->where('acc_type', 13)
            ->where('is_basic', 0)
            ->where('isdeleted', 0)
            ->whereDoesntHave('accountAsset')
            ->orderBy('aname')
            ->get();

        $branches = Branch::orderBy('name')->get();

        return view('depreciation::livewire.depreciation-manager', [
            'accountAssets' => $accountAssets,
            'assetAccounts' => $assetAccounts,
            'branches' => $branches,
        ]);
    }

    public function selectAccountForDepreciation($accountId)
    {
        $this->selectedAccount = AccHead::findOrFail($accountId);
        $this->resetForm();
        // Set purchase cost from account balance
        $this->purchase_cost = (string) ($this->selectedAccount->balance ?? '');
        $this->showModal = true;
        $this->recalculateSchedulePreview();
    }

    public function createAssetRecord($accountId)
    {
        $this->selectedAccount = AccHead::findOrFail($accountId);
        $this->resetForm();
        $this->editMode = false;
        // Set purchase cost from account balance for new asset records
        $this->purchase_cost = (string) ($this->selectedAccount->balance ?? '');
        $this->showModal = true;
        $this->recalculateSchedulePreview();
    }

    public function editAsset($assetId)
    {
        $asset = AccountAsset::with('accHead')->findOrFail($assetId);

        $this->selectedAccount = $asset->accHead;
        $this->asset_name = $asset->asset_name;
        $this->purchase_date = $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '';
        $this->purchase_cost = $asset->purchase_cost;
        $this->salvage_value = $asset->salvage_value;
        $this->useful_life_years = $asset->useful_life_years;
        $this->depreciation_method = $asset->depreciation_method;
        $this->annual_depreciation_amount = $asset->annual_depreciation;
        $this->depreciation_date = $asset->depreciation_start_date ? $asset->depreciation_start_date->format('Y-m-d') : now()->format('Y-m-d');
        $this->notes = $asset->notes;
        $this->itemId = $asset->id;

        $this->editMode = true;
        $this->showModal = true;
        $this->recalculateSchedulePreview();
    }

    public function processDepreciation()
    {
        if (! $this->selectedAccount) {
            $this->addError('selectedAccount', 'يرجى اختيار حساب الأصل أولاً.');

            return;
        }

        $this->validate();

        // Validate dates relationship: depreciation_date >= purchase_date when both provided
        if (! empty($this->purchase_date) && ! empty($this->depreciation_date)) {
            $purchase = Carbon::parse($this->purchase_date);
            $firstDep = Carbon::parse($this->depreciation_date);
            if ($firstDep->lt($purchase)) {
                $this->addError('depreciation_date', 'تاريخ أول إهلاك لا يمكن أن يكون قبل تاريخ الشراء');

                return;
            }
        }

        try {
            DB::beginTransaction();

            \Log::info('Processing asset depreciation', [
                'edit_mode' => $this->editMode,
                'item_id' => $this->itemId,
                'account_id' => $this->selectedAccount->id,
            ]);

            // Create or update AccountAsset record
            $assetData = [
                'acc_head_id' => $this->selectedAccount->id,
                'asset_name' => $this->asset_name ?: $this->selectedAccount->aname,
                'purchase_date' => $this->purchase_date,
                'purchase_cost' => $this->purchase_cost ?: 0,
                'salvage_value' => $this->salvage_value ?: 0,
                'useful_life_years' => $this->useful_life_years,
                'depreciation_method' => $this->depreciation_method ?: 'straight_line',
                'annual_depreciation' => $this->annual_depreciation_amount ?: 0,
                'depreciation_start_date' => $this->depreciation_date,
                'last_depreciation_date' => $this->depreciation_date,
                'is_active' => true,
                'notes' => $this->notes,
            ];

            // Find or create depreciation accounts first
            $depreciationAccounts = $this->getOrCreateDepreciationAccounts($this->selectedAccount);

            // Add depreciation account IDs to asset data
            $assetData['depreciation_account_id'] = $depreciationAccounts['accumulated_depreciation']->id;
            $assetData['expense_account_id'] = $depreciationAccounts['expense_depreciation']->id;

            if ($this->editMode && $this->itemId) {
                // Update existing asset
                $asset = AccountAsset::findOrFail($this->itemId);
                $asset->acc_head_id = $assetData['acc_head_id'];
                $asset->asset_name = $assetData['asset_name'];
                $asset->purchase_date = $assetData['purchase_date'];
                $asset->purchase_cost = $assetData['purchase_cost'];
                $asset->salvage_value = $assetData['salvage_value'];
                $asset->useful_life_years = $assetData['useful_life_years'];
                $asset->depreciation_method = $assetData['depreciation_method'];
                $asset->annual_depreciation = $assetData['annual_depreciation'];
                $asset->depreciation_start_date = $assetData['depreciation_start_date'];
                $asset->last_depreciation_date = $assetData['last_depreciation_date'];
                $asset->is_active = $assetData['is_active'];
                $asset->notes = $assetData['notes'];
                $asset->depreciation_account_id = $assetData['depreciation_account_id'];
                $asset->expense_account_id = $assetData['expense_account_id'];
                $asset->save();

                \Log::info('Asset updated successfully', [
                    'asset_id' => $asset->id,
                    'asset_name' => $asset->asset_name,
                    'purchase_cost' => $asset->purchase_cost,
                ]);

                $message = 'تم تحديث بيانات الأصل بنجاح';
            } else {
                // Create new asset record
                $asset = AccountAsset::create($assetData);
                $message = 'تم إنشاء سجل الأصل بنجاح';
            }

            // لا تنشئ أي قيود محاسبية من هذه الشاشة
            $message .= ' (تم حفظ بيانات الأصل والإعدادات فقط)';

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Surface the error inside the modal as validation-style message
            $this->addError('general', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ]);
        }
    }

    public function generatePreview(): void
    {
        // Same date validation when generating preview
        if (! empty($this->purchase_date) && ! empty($this->depreciation_date)) {
            $purchase = Carbon::parse($this->purchase_date);
            $firstDep = Carbon::parse($this->depreciation_date);
            if ($firstDep->lt($purchase)) {
                $this->addError('depreciation_date', 'تاريخ أول إهلاك لا يمكن أن يكون قبل تاريخ الشراء');

                return;
            }
        }

        $this->recalculateSchedulePreview();
    }

    // Live updates to schedule preview when inputs change
    public function updatedPurchaseCost()
    {
        $this->recalculateSchedulePreview();
    }

    public function updatedUsefulLifeYears()
    {
        $this->recalculateSchedulePreview();
    }

    public function updatedDepreciationMethod()
    {
        $this->recalculateSchedulePreview();
    }

    public function updatedDepreciationDate()
    {
        $this->recalculateSchedulePreview();
    }

    public function updatedSalvageValue()
    {
        $this->recalculateSchedulePreview();
    }

    private function recalculateSchedulePreview(): void
    {
        $asset = (object) [
            'purchase_cost' => (float) ($this->purchase_cost ?: 0),
            'salvage_value' => (float) ($this->salvage_value ?: 0),
            'useful_life_years' => (int) ($this->useful_life_years ?: 0),
            'depreciation_method' => $this->depreciation_method ?: 'straight_line',
            'depreciation_start_date' => $this->depreciation_date ?: now()->format('Y-m-d'),
        ];

        $this->schedulePreview = $this->calculateDepreciationSchedulePreview($asset);

        // Auto-derive the first year's depreciation amount if possible
        if (! empty($this->schedulePreview)) {
            $firstYear = $this->schedulePreview[0];
            // Only override if user hasn't set a custom value or when creating
            if (empty($this->annual_depreciation_amount) || ! $this->editMode) {
                $this->annual_depreciation_amount = (string) round($firstYear['annual_depreciation'], 2);
            }
        }
    }

    private function calculateDepreciationSchedulePreview(object $asset): array
    {
        $schedule = [];

        if (! $asset->useful_life_years || ! $asset->purchase_cost) {
            return $schedule;
        }

        $startDate = $asset->depreciation_start_date ? Carbon::parse($asset->depreciation_start_date) : now();
        $depreciableAmount = $asset->purchase_cost - ($asset->salvage_value ?? 0);
        $currentBookValue = $asset->purchase_cost;
        $accumulatedDepreciation = 0;

        for ($year = 1; $year <= $asset->useful_life_years; $year++) {
            $yearStartDate = $startDate->copy()->addYears($year - 1);
            $yearEndDate = $startDate->copy()->addYears($year)->subDay();

            $annualDepreciation = $this->calculateYearlyDepreciationPreview(
                $asset,
                $currentBookValue,
                $accumulatedDepreciation,
                $depreciableAmount,
                $year
            );

            if ($annualDepreciation <= 0) {
                break;
            }

            $accumulatedDepreciation += $annualDepreciation;
            $currentBookValue -= $annualDepreciation;

            if ($accumulatedDepreciation > $depreciableAmount) {
                $annualDepreciation -= ($accumulatedDepreciation - $depreciableAmount);
                $accumulatedDepreciation = $depreciableAmount;
                $currentBookValue = $asset->salvage_value ?? 0;
            }

            $schedule[] = [
                'year' => $year,
                'start_date' => $yearStartDate->format('Y-m-d'),
                'end_date' => $yearEndDate->format('Y-m-d'),
                'beginning_book_value' => $currentBookValue + $annualDepreciation,
                'annual_depreciation' => $annualDepreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'ending_book_value' => $currentBookValue,
                'percentage' => $depreciableAmount > 0 ? ($annualDepreciation / $depreciableAmount) * 100 : 0,
            ];

            if ($accumulatedDepreciation >= $depreciableAmount) {
                break;
            }
        }

        return $schedule;
    }

    private function calculateYearlyDepreciationPreview(object $asset, float $currentBookValue, float $accumulatedDepreciation, float $depreciableAmount, int $year): float
    {
        switch ($asset->depreciation_method) {
            case 'straight_line':
                return $depreciableAmount / max($asset->useful_life_years, 1);
            case 'declining_balance':
                $rate = 1 / max($asset->useful_life_years, 1);
                $db = $currentBookValue * $rate;
                $remainingDepreciable = $depreciableAmount - $accumulatedDepreciation;

                return min($db, $remainingDepreciable);
            case 'double_declining':
                $rate = 2 / max($asset->useful_life_years, 1);
                $ddb = $currentBookValue * $rate;
                $remainingDepreciable = $depreciableAmount - $accumulatedDepreciation;
                $remainingYears = max($asset->useful_life_years - ($year - 1), 1);
                $slRemaining = $remainingDepreciable / $remainingYears;
                $depreciation = max($ddb, $slRemaining);

                return min($depreciation, $remainingDepreciable);
            case 'sum_of_years':
                $sumOfYears = ($asset->useful_life_years * ($asset->useful_life_years + 1)) / 2;
                $remainingYears = $asset->useful_life_years - ($year - 1);

                return ($depreciableAmount * $remainingYears) / max($sumOfYears, 1);
            default:
                return $depreciableAmount / max($asset->useful_life_years, 1);
        }
    }

    private function getOrCreateDepreciationAccounts(AccHead $assetAccount)
    {
        // Find accumulated depreciation account (acc_type = 15)
        $accumulatedDepreciation = AccHead::where('accountable_id', $assetAccount->id)
            ->where('acc_type', 15)
            ->first();

        if (! $accumulatedDepreciation) {
            $accumulatedDepreciation = AccHead::create([
                'aname' => 'مجمع إهلاك '.$assetAccount->aname,
                'code' => $this->generateAccountCode(15, $assetAccount->branch_id),
                'acc_type' => 15,
                'accountable_id' => $assetAccount->id,
                'accountable_type' => AccHead::class,
                'branch_id' => $assetAccount->branch_id,
                'is_basic' => 0,
                'isdeleted' => 0,
            ]);
        }

        // Find expense depreciation account (acc_type = 16)
        $expenseDepreciation = AccHead::where('accountable_id', $assetAccount->id)
            ->where('acc_type', 16)
            ->first();

        if (! $expenseDepreciation) {
            $expenseDepreciation = AccHead::create([
                'aname' => 'مصروف إهلاك '.$assetAccount->aname,
                'code' => $this->generateAccountCode(16, $assetAccount->branch_id),
                'acc_type' => 16,
                'accountable_id' => $assetAccount->id,
                'accountable_type' => AccHead::class,
                'branch_id' => $assetAccount->branch_id,
                'is_basic' => 0,
                'isdeleted' => 0,
            ]);
        }

        return [
            'accumulated_depreciation' => $accumulatedDepreciation,
            'expense_depreciation' => $expenseDepreciation,
        ];
    }

    private function createDepreciationVoucher(AccHead $assetAccount, AccHead $accumulatedAccount, AccHead $expenseAccount, $amount)
    {
        // Get next operation ID
        $lastProId = OperHead::where('pro_type', 61)->max('pro_id') ?? 0;
        $newProId = $lastProId + 1;

        // Create operation header
        $oper = OperHead::create([
            'pro_id' => $newProId,
            'pro_date' => $this->depreciation_date,
            'pro_type' => 61, // قيد يومية
            'acc1' => $expenseAccount->id,
            'acc2' => $accumulatedAccount->id,
            'pro_value' => $amount,
            'details' => 'قيد إهلاك '.$assetAccount->aname.' - '.$this->notes,
            'info' => 'قيد إهلاك تلقائي',
            'user' => Auth::id(),
            'branch_id' => $assetAccount->branch_id,
            'isdeleted' => 0,
        ]);

        // Get next journal ID
        $lastJournalId = JournalHead::max('journal_id') ?? 0;
        $newJournalId = $lastJournalId + 1;

        // Create journal header
        $journalHead = JournalHead::create([
            'journal_id' => $newJournalId,
            'total' => $amount,
            'op_id' => $oper->id,
            'pro_type' => 61,
            'date' => $this->depreciation_date,
            'details' => 'قيد إهلاك '.$assetAccount->aname,
            'user' => Auth::id(),
            'branch_id' => $assetAccount->branch_id,
        ]);

        // Debit: Expense Account (مصروف الإهلاك)
        JournalDetail::create([
            'journal_id' => $newJournalId,
            'account_id' => $expenseAccount->id,
            'debit' => $amount,
            'credit' => 0,
            'type' => 0, // مدين
            'info' => 'مصروف إهلاك '.$assetAccount->aname,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'branch_id' => $assetAccount->branch_id,
        ]);

        // Credit: Accumulated Depreciation Account (مجمع الإهلاك)
        JournalDetail::create([
            'journal_id' => $newJournalId,
            'account_id' => $accumulatedAccount->id,
            'debit' => 0,
            'credit' => $amount,
            'type' => 1, // دائن
            'info' => 'مجمع إهلاك '.$assetAccount->aname,
            'op_id' => $oper->id,
            'isdeleted' => 0,
            'branch_id' => $assetAccount->branch_id,
        ]);

        return $oper;
    }

    private function generateAccountCode($accType, $branchId)
    {
        // Generate a unique account code based on account type and branch
        $prefix = $accType.str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $lastAccount = AccHead::where('code', 'like', $prefix.'%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = (int) substr($lastAccount->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->itemId = null;
        $this->editMode = false;
        $this->asset_name = '';
        $this->purchase_date = '';
        $this->purchase_cost = '';
        $this->salvage_value = 0;
        $this->annual_depreciation_amount = '';
        $this->depreciation_method = '';
        $this->useful_life_years = '';
        $this->depreciation_date = now()->format('Y-m-d');
        $this->notes = '';
        $this->resetErrorBag();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterBranch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedAssets = $this->getVisibleAssetIds();
        } else {
            $this->selectedAssets = [];
        }
    }

    public function updatedSelectedAssets()
    {
        $this->selectAll = count($this->selectedAssets) === count($this->getVisibleAssetIds());
    }

    private function getVisibleAssetIds()
    {
        return AccountAsset::query()
            ->with(['accHead'])
            ->when($this->search, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('aname', 'like', '%'.$this->search.'%');
                })->orWhere('asset_name', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterBranch, function ($query) {
                $query->whereHas('accHead', function ($q) {
                    $q->where('branch_id', $this->filterBranch);
                });
            })
            ->pluck('id')
            ->toArray();
    }

    public function bulkDepreciation()
    {
        if (empty($this->selectedAssets)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'يرجى اختيار أصل واحد على الأقل',
            ]);

            return;
        }

        $processedCount = 0;
        $totalAmount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($this->selectedAssets as $assetId) {
                $asset = AccountAsset::with('accHead')->find($assetId);

                if (! $asset || ! $asset->annual_depreciation) {
                    continue;
                }

                // Calculate monthly depreciation if not done this month
                $monthlyDepreciation = $asset->annual_depreciation / 12;
                $lastDepreciation = $asset->last_depreciation_date ?
                    Carbon::parse($asset->last_depreciation_date) :
                    Carbon::parse($asset->depreciation_start_date);

                if ($lastDepreciation->format('Y-m') >= now()->format('Y-m')) {
                    continue; // Already processed this month
                }

                // Get or create depreciation accounts
                $depreciationAccounts = $this->getOrCreateDepreciationAccounts($asset->accHead);

                // Create depreciation voucher
                $this->createDepreciationVoucher(
                    $asset->accHead,
                    $depreciationAccounts['accumulated_depreciation'],
                    $depreciationAccounts['expense_depreciation'],
                    $monthlyDepreciation
                );

                // Update asset
                $asset->increment('accumulated_depreciation', $monthlyDepreciation);
                $asset->update([
                    'last_depreciation_date' => now(),
                    'depreciation_account_id' => $depreciationAccounts['accumulated_depreciation']->id,
                    'expense_account_id' => $depreciationAccounts['expense_depreciation']->id,
                ]);

                $processedCount++;
                $totalAmount += $monthlyDepreciation;
            }

            DB::commit();

            $this->selectedAssets = [];
            $this->selectAll = false;

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => "تم معالجة {$processedCount} أصل بإجمالي ".number_format($totalAmount, 2),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء المعالجة المجمعة: '.$e->getMessage(),
            ]);
        }
    }
}
