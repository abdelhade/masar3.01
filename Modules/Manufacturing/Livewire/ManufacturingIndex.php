<?php

namespace Modules\Manufacturing\Livewire;

use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\{OperHead, AccHead};
use Modules\Branches\Models\Branch;
use Modules\Manufacturing\Services\ManufacturingInvoiceService;

class ManufacturingIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $branchFilter = '';
    public $statusFilter = '';
    public $perPage = 15;
    public $sortField = 'pro_date';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'branchFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[On('deleteInvoice')]
    public function deleteInvoice($invoiceId, ManufacturingInvoiceService $service)
    {
        try {
            $success = $service->deleteManufacturingInvoice((int) $invoiceId);

            if ($success) {
                $this->dispatch('success-swal', [
                    'title' => __('Deleted!'),
                    'text' => __('Manufacturing invoice deleted successfully'),
                    'icon' => 'success'
                ]);
            } else {
                throw new \Exception('Failed to delete');
            }
        } catch (\Exception $e) {
            $this->dispatch('error-swal', [
                'title' => __('Error!'),
                'text' => __('An error occurred while deleting'),
                'icon' => 'error'
            ]);
        }
    }

    public function confirmDelete($invoiceId)
    {
        $this->dispatch('confirm-delete', [
            'title' => __('Are you sure?'),
            'text' => __('The manufacturing invoice and all related data will be deleted'),
            'icon' => 'warning',
            'confirmButtonText' => __('Yes, delete it'),
            'cancelButtonText' => __('Cancel'),
            'invoiceId' => $invoiceId
        ]);
    }

    public function render()
    {
        $query = OperHead::where('pro_type', 59)
            ->with(['acc1Head', 'acc2Head', 'employee', 'branch']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('pro_id', 'like', '%' . $this->search . '%')
                    ->orWhere('info', 'like', '%' . $this->search . '%')
                    ->orWhereHas('acc1Head', function ($subQuery) {
                        $subQuery->where('aname', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('acc2Head', function ($subQuery) {
                        $subQuery->where('aname', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Date filter
        if ($this->dateFrom) {
            $query->whereDate('pro_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('pro_date', '<=', $this->dateTo);
        }

        // Branch filter
        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $invoices = $query->paginate($this->perPage);

        // Quick statistics
        $statistics = [
            'total' => OperHead::where('pro_type', 59)->count(),
            'thisMonth' => OperHead::where('pro_type', 59)
                ->whereYear('pro_date', date('Y'))
                ->whereMonth('pro_date', date('m'))
                ->count(),
            'totalValue' => OperHead::where('pro_type', 59)->sum('pro_value'),
            'avgValue' => OperHead::where('pro_type', 59)->avg('pro_value'),
        ];

        $branches = Branch::all();

        return view('manufacturing::livewire.manufacturing-index', [
            'invoices' => $invoices,
            'statistics' => $statistics,
            'branches' => $branches
        ]);
    }
}
