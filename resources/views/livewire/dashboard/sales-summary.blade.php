<?php

use Livewire\Volt\Component;
use App\Models\OperationItems;
use App\Models\OperHead;
use App\Enums\OperationTypeEnum;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component {
    public string $selectedPeriod = 'month';
    public string $startDate;
    public string $endDate;
    
    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }
    
    public function updatedSelectedPeriod()
    {
        $this->updateDateRange();
    }
    
    public function updateDateRange()
    {
        $now = Carbon::now();
        
        switch ($this->selectedPeriod) {
            case 'week':
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = $now->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;
        }
    }
    
    public function with(): array
    {
        return [
            'salesSummary' => $this->getSalesSummary(),
            'periodLabel' => $this->getPeriodLabel(),
        ];
    }
    
    private function getSalesSummary()
    {
        $summary = OperationItems::select(
                DB::raw('SUM(operation_items.qty_out * operation_items.price) as total_sales'),
                DB::raw('SUM(operation_items.qty_out) as total_quantity'),
                DB::raw('COUNT(DISTINCT operhead.id) as total_orders'),
                DB::raw('COUNT(DISTINCT operation_items.item_id) as unique_items'),
                DB::raw('AVG(operation_items.qty_out * operation_items.price) as avg_order_value')
            )
            ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
            ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
            ->where('operhead.date', '>=', $this->startDate)
            ->where('operhead.date', '<=', $this->endDate)
            ->where('operation_items.isdeleted', 0)
            ->where('operation_items.qty_out', '>', 0)
            ->first();
        
        // حساب النمو مقارنة بالفترة السابقة
        $previousPeriod = $this->getPreviousPeriodData();
        $growth = 0;
        if ($previousPeriod['total_sales'] > 0) {
            $growth = (($summary->total_sales - $previousPeriod['total_sales']) / $previousPeriod['total_sales']) * 100;
        }
        
        return [
            'total_sales' => $summary->total_sales ?? 0,
            'total_quantity' => $summary->total_quantity ?? 0,
            'total_orders' => $summary->total_orders ?? 0,
            'unique_items' => $summary->unique_items ?? 0,
            'avg_order_value' => $summary->avg_order_value ?? 0,
            'growth' => round($growth, 1)
        ];
    }
    
    private function getPreviousPeriodData()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $duration = $start->diffInDays($end);
        
        $previousStart = $start->copy()->subDays($duration);
        $previousEnd = $start->copy()->subDay();
        
        $summary = OperationItems::select(
                DB::raw('SUM(operation_items.qty_out * operation_items.price) as total_sales')
            )
            ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
            ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
            ->where('operhead.date', '>=', $previousStart->format('Y-m-d'))
            ->where('operhead.date', '<=', $previousEnd->format('Y-m-d'))
            ->where('operation_items.isdeleted', 0)
            ->where('operation_items.qty_out', '>', 0)
            ->first();
        
        return [
            'total_sales' => $summary->total_sales ?? 0
        ];
    }
    
    private function getPeriodLabel()
    {
        switch ($this->selectedPeriod) {
            case 'week':
                return 'هذا الأسبوع';
            case 'month':
                return 'هذا الشهر';
            case 'quarter':
                return 'هذا الربع';
            case 'year':
                return 'هذا العام';
            default:
                return 'هذا الشهر';
        }
    }
}; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">{{ __('ملخص المبيعات') }}</h5>
            <select wire:model.live="selectedPeriod" class="form-select form-select-sm" style="width: auto;">
                <option value="week">أسبوع</option>
                <option value="month">شهر</option>
                <option value="quarter">ربع سنة</option>
                <option value="year">سنة</option>
            </select>
        </div>
        <small class="text-muted">{{ $periodLabel }}</small>
    </div>
</div>

<div class="row">
    <!-- إجمالي المبيعات -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-right-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            {{ __('إجمالي المبيعات') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($salesSummary['total_sales'], 2) }} ريال
                        </div>
                        @if($salesSummary['growth'] != 0)
                            <div class="text-xs {{ $salesSummary['growth'] > 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-{{ $salesSummary['growth'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                {{ abs($salesSummary['growth']) }}% {{ $salesSummary['growth'] > 0 ? 'زيادة' : 'انخفاض' }}
                            </div>
                        @endif
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- إجمالي الكميات -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-right-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            {{ __('إجمالي الكميات') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($salesSummary['total_quantity']) }}
                        </div>
                        <div class="text-xs text-muted">
                            {{ __('منتج') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- عدد الطلبات -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-right-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            {{ __('عدد الطلبات') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($salesSummary['total_orders']) }}
                        </div>
                        <div class="text-xs text-muted">
                            {{ __('طلب') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- متوسط قيمة الطلب -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-right-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            {{ __('متوسط قيمة الطلب') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($salesSummary['avg_order_value'], 2) }} ريال
                        </div>
                        <div class="text-xs text-muted">
                            {{ __('لكل طلب') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
