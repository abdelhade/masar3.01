<?php

namespace Modules\Depreciation\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccHead;
use Modules\Branches\Models\Branch;
use Modules\Depreciation\Models\DepreciationItem;
use Illuminate\Support\Facades\DB;

class DepreciationManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $itemId = null;

    // Form fields
    public $name = '';
    public $purchase_date = '';
    public $cost = '';
    public $useful_life = '';
    public $salvage_value = 0;
    public $depreciation_method = 'straight_line';
    public $asset_account_id = null;
    public $branch_id = null;
    public $notes = '';
    public $is_active = true;

    // Search and filter
    public $search = '';
    public $filterBranch = '';
    public $filterMethod = '';
    public $filterStatus = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'purchase_date' => 'required|date',
        'cost' => 'required|numeric|min:0',
        'useful_life' => 'required|integer|min:1|max:100',
        'salvage_value' => 'nullable|numeric|min:0',
        'depreciation_method' => 'required|in:straight_line,double_declining,sum_of_years',
        'asset_account_id' => 'nullable|exists:acc_head,id',
        'branch_id' => 'required|exists:branches,id',
        'notes' => 'nullable|string|max:1000',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'اسم الأصل مطلوب',
        'purchase_date.required' => 'تاريخ الشراء مطلوب',
        'cost.required' => 'تكلفة الأصل مطلوبة',
        'useful_life.required' => 'العمر الإنتاجي مطلوب',
        'branch_id.required' => 'الفرع مطلوب',
    ];

    public function mount()
    {
        $this->branch_id = auth()->user()->branch_id ?? null;
    }

    public function render()
    {
        $depreciationItems = DepreciationItem::query()
            ->with(['assetAccount', 'depreciationAccount', 'expenseAccount', 'branch'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterBranch, function ($query) {
                $query->where('branch_id', $this->filterBranch);
            })
            ->when($this->filterMethod, function ($query) {
                $query->where('depreciation_method', $this->filterMethod);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_active', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get asset accounts (starting with code '12')
        $assetAccounts = AccHead::where('code', 'like', '12%')
            ->where('isdeleted', 0)
            ->orderBy('aname')
            ->get();

        $branches = Branch::orderBy('name')->get();

        return view('depreciation::livewire.depreciation-manager', [
            'depreciationItems' => $depreciationItems,
            'assetAccounts' => $assetAccounts,
            'branches' => $branches,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $item = DepreciationItem::findOrFail($id);
        
        $this->itemId = $item->id;
        $this->name = $item->name;
        $this->purchase_date = $item->purchase_date->format('Y-m-d');
        $this->cost = $item->cost;
        $this->useful_life = $item->useful_life;
        $this->salvage_value = $item->salvage_value;
        $this->depreciation_method = $item->depreciation_method;
        $this->asset_account_id = $item->asset_account_id;
        $this->branch_id = $item->branch_id;
        $this->notes = $item->notes;
        $this->is_active = $item->is_active;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'purchase_date' => $this->purchase_date,
                'cost' => $this->cost,
                'useful_life' => $this->useful_life,
                'salvage_value' => $this->salvage_value ?: 0,
                'depreciation_method' => $this->depreciation_method,
                'asset_account_id' => $this->asset_account_id,
                'branch_id' => $this->branch_id,
                'notes' => $this->notes,
                'is_active' => $this->is_active,
            ];

            // Calculate annual depreciation
            $data['annual_depreciation'] = ($this->cost - ($this->salvage_value ?: 0)) / $this->useful_life;

            if ($this->editMode) {
                $item = DepreciationItem::findOrFail($this->itemId);
                $item->update($data);
                $message = 'تم تحديث بيانات الإهلاك بنجاح';
            } else {
                $item = DepreciationItem::create($data);
                $message = 'تم إضافة الأصل للإهلاك بنجاح';
                
                // Auto-link with depreciation accounts if asset account is selected
                if ($this->asset_account_id) {
                    $this->linkDepreciationAccounts($item);
                }
            }

            DB::commit();
            
            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ]);
        }
    }

    private function linkDepreciationAccounts(DepreciationItem $item)
    {
        // Find related depreciation accounts
        $depreciationAccount = AccHead::where('account_id', $this->asset_account_id)
            ->where('acc_type', 15)
            ->first();
            
        $expenseAccount = AccHead::where('account_id', $this->asset_account_id)
            ->where('acc_type', 16)
            ->first();

        if ($depreciationAccount || $expenseAccount) {
            $item->update([
                'depreciation_account_id' => $depreciationAccount?->id,
                'expense_account_id' => $expenseAccount?->id,
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $item = DepreciationItem::findOrFail($id);
            $item->delete();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'تم حذف الأصل بنجاح'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ]);
        }
    }

    public function calculateDepreciation($id)
    {
        try {
            $item = DepreciationItem::findOrFail($id);
            
            // Calculate current year depreciation
            $yearsUsed = now()->diffInYears($item->purchase_date);
            $totalDepreciation = min($yearsUsed * $item->annual_depreciation, $item->cost - $item->salvage_value);
            
            $item->update([
                'accumulated_depreciation' => $totalDepreciation
            ]);
            
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'تم حساب الإهلاك المتراكم بنجاح'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء حساب الإهلاك: ' . $e->getMessage()
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->itemId = null;
        $this->name = '';
        $this->purchase_date = '';
        $this->cost = '';
        $this->useful_life = '';
        $this->salvage_value = 0;
        $this->depreciation_method = 'straight_line';
        $this->asset_account_id = null;
        $this->notes = '';
        $this->is_active = true;
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

    public function updatingFilterMethod()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
}