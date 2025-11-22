<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;
use App\Models\OperHead;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $fromDate;
    public $toDate;
    public $customerId = null;
    public $customers = [];

    public function mount(): void
    {
        $this->fromDate = today()->format('Y-m-d');
        $this->toDate = today()->format('Y-m-d');
        $this->loadCustomers();
        $this->generateReport();
    }

    public function loadCustomers(): void
    {
        $this->customers = AccHead::where('code', 'like', '1103%')
            ->where('isdeleted', 0)
            ->orderBy('aname')
            ->get();
    }

    public function generateReport(): void
    {
        $this->resetPage();
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerId(): void
    {
        $this->resetPage();
    }

    public function getSalesProperty()
    {
        $query = OperHead::where('pro_type', 10)
            ->with(['acc1Head', 'operationItems'])
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->customerId, function ($q) {
                $q->where('acc1', $this->customerId);
            })
            ->orderBy('pro_date', 'desc');

        $sales = $query->paginate(50);

        // Calculate additional fields for each sale
        $sales->getCollection()->transform(function ($sale) {
            $sale->items_count = $sale->operationItems->count() ?? 0;
            $sale->total_quantity = $sale->operationItems->sum('qty_out') ?? 0;
            $sale->total_sales = $sale->fat_total ?? 0;
            $sale->discount = $sale->fat_disc ?? 0;
            $sale->net_sales = $sale->fat_net ?? 0;
            $sale->status = $sale->isdeleted == 0 ? 'completed' : 'pending';
            return $sale;
        });

        return $sales;
    }

    public function getTotalQuantityProperty(): float
    {
        $query = OperHead::where('pro_type', 10)
            ->with('operationItems')
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->customerId, function ($q) {
                $q->where('acc1', $this->customerId);
            })
            ->get();

        return (float) $query->sum(function ($sale) {
            return (float) ($sale->operationItems->sum('qty_out') ?? 0);
        });
    }

    public function getTotalSalesProperty(): float
    {
        return (float) (OperHead::where('pro_type', 10)
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->customerId, function ($q) {
                $q->where('acc1', $this->customerId);
            })
            ->sum('fat_total') ?? 0);
    }

    public function getTotalDiscountProperty(): float
    {
        return (float) (OperHead::where('pro_type', 10)
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->customerId, function ($q) {
                $q->where('acc1', $this->customerId);
            })
            ->sum('fat_disc') ?? 0);
    }

    public function getTotalNetSalesProperty(): float
    {
        return (float) (OperHead::where('pro_type', 10)
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->customerId, function ($q) {
                $q->where('acc1', $this->customerId);
            })
            ->sum('fat_net') ?? 0);
    }

    public function getTotalInvoicesProperty(): int
    {
        return OperHead::where('pro_type', 10)
            ->when($this->fromDate, function ($q) {
                $q->whereDate('pro_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($q) {
                $q->whereDate('pro_date', '<=', $this->toDate);
            })
            ->when($this->customerId, function ($q) {
                $q->where('acc1', $this->customerId);
            })
            ->count();
    }

    public function getAverageInvoiceValueProperty(): float
    {
        $totalInvoices = $this->totalInvoices;
        $totalNetSales = $this->totalNetSales;
        
        return $totalInvoices > 0 ? (float) ($totalNetSales / $totalInvoices) : 0.0;
    }
}; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('reports.general_sales_report') }}</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="from_date" class="form-label">{{ __('reports.from_date') }}</label>
                            <input type="date" wire:model.live="fromDate" class="form-control" id="from_date">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date" class="form-label">{{ __('reports.to_date') }}</label>
                            <input type="date" wire:model.live="toDate" class="form-control" id="to_date">
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">{{ __('reports.customer') }}</label>
                            <select wire:model.live="customerId" class="form-select" id="customer_id">
                                <option value="">{{ __('reports.all_customers') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button wire:click="generateReport" class="btn btn-primary d-block">{{ __('reports.generate_report') }}</button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_quantity') }}</h6>
                                    <h4 class="card-text">{{ number_format($this->totalQuantity, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_sales') }}</h6>
                                    <h4 class="card-text">{{ number_format($this->totalSales, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_discount') }}</h6>
                                    <h4 class="card-text">{{ number_format($this->totalDiscount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.net_sales') }}</h6>
                                    <h4 class="card-text">{{ number_format($this->totalNetSales, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_invoices') }}</h6>
                                    <h4 class="card-text">{{ $this->totalInvoices }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.average_invoice_value') }}</h6>
                                    <h4 class="card-text">{{ number_format($this->averageInvoiceValue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('reports.date') }}</th>
                                    <th>{{ __('reports.operation_number') }}</th>
                                    <th>{{ __('reports.customer') }}</th>
                                    <th class="text-end">{{ __('reports.quantity') }}</th>
                                    <th class="text-end">{{ __('reports.total_quantity') }}</th>
                                    <th class="text-end">{{ __('reports.total_sales') }}</th>
                                    <th class="text-end">{{ __('reports.total_discount') }}</th>
                                    <th class="text-end">{{ __('reports.net_sales') }}</th>
                                    <th>{{ __('reports.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->sales as $sale)
                                <tr>
                                    <td>{{ $sale->pro_date ? Carbon::parse($sale->pro_date)->format('Y-m-d') : '---' }}</td>
                                    <td>{{ $sale->pro_num ?? '---' }}</td>
                                    <td>{{ $sale->acc1Head->aname ?? '---' }}</td>
                                    <td class="text-end">{{ $sale->items_count ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($sale->total_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->total_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->discount ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($sale->net_sales, 2) }}</td>
                                    <td>
                                        @if($sale->status == 'completed')
                                            <span class="badge bg-success">{{ __('reports.completed') }}</span>
                                        @elseif($sale->status == 'pending')
                                            <span class="badge bg-warning">{{ __('reports.pending') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('reports.unspecified') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('reports.no_data_available') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($this->sales->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $this->sales->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
