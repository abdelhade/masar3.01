<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\AccHead;

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $fromDate;
    public $toDate;
    public $groupBy = 'day';

    public function mount(): void
    {
        $this->groupBy = request('group_by', 'day');
        $this->fromDate = request('from_date') ?? today()->format('Y-m-d');
        $this->toDate = request('to_date') ?? today()->format('Y-m-d');
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

    public function updatedGroupBy(): void
    {
        $this->resetPage();
    }

    public function getSalesTotalsProperty()
    {
        $query = DB::table('operhead')
            ->join('operation_items', 'operhead.id', '=', 'operation_items.pro_id')
            ->where('operhead.pro_type', 10);

        if ($this->fromDate) {
            $query->whereDate('operhead.pro_date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('operhead.pro_date', '<=', $this->toDate);
        }

        if ($this->groupBy === 'day') {
            $salesTotals = $query->selectRaw('
                DATE(operhead.pro_date) as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
            ')
                ->groupBy(DB::raw('DATE(operhead.pro_date)'))
                ->orderBy(DB::raw('DATE(operhead.pro_date)'), 'desc')
                ->paginate(50);
        } elseif ($this->groupBy === 'week') {
            $salesTotals = $query->selectRaw('
                YEAR(operhead.pro_date) as year,
                WEEK(operhead.pro_date, 1) as week,
                CONCAT(YEAR(operhead.pro_date), "-W", LPAD(WEEK(operhead.pro_date, 1), 2, "0")) as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
            ')
                ->groupBy(
                    DB::raw('YEAR(operhead.pro_date)'),
                    DB::raw('WEEK(operhead.pro_date, 1)'),
                    DB::raw('CONCAT(YEAR(operhead.pro_date), "-W", LPAD(WEEK(operhead.pro_date, 1), 2, "0"))')
                )
                ->orderBy(DB::raw('YEAR(operhead.pro_date)'), 'desc')
                ->orderBy(DB::raw('WEEK(operhead.pro_date, 1)'), 'desc')
                ->paginate(50);
        } elseif ($this->groupBy === 'month') {
            $salesTotals = $query->selectRaw('
                YEAR(operhead.pro_date) as year,
                MONTH(operhead.pro_date) as month,
                CONCAT(YEAR(operhead.pro_date), "-", LPAD(MONTH(operhead.pro_date), 2, "0")) as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
            ')
                ->groupBy(
                    DB::raw('YEAR(operhead.pro_date)'),
                    DB::raw('MONTH(operhead.pro_date)'),
                    DB::raw('CONCAT(YEAR(operhead.pro_date), "-", LPAD(MONTH(operhead.pro_date), 2, "0"))')
                )
                ->orderBy(DB::raw('YEAR(operhead.pro_date)'), 'desc')
                ->orderBy(DB::raw('MONTH(operhead.pro_date)'), 'desc')
                ->paginate(50);
        } elseif ($this->groupBy === 'customer') {
            $salesTotals = $query->join('acc_head', 'operhead.acc1', '=', 'acc_head.id')
                ->selectRaw('
                    operhead.acc1 as customer_id,
                    acc_head.aname as customer_name,
                    acc_head.aname as period_name,
                    COUNT(DISTINCT operhead.id) as invoices_count,
                    SUM(operation_items.qty_out) as total_quantity,
                    SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                    SUM(operhead.fat_disc) as total_discount,
                    SUM(operhead.fat_net) as net_sales
                ')
                ->groupBy('operhead.acc1', 'acc_head.aname')
                ->orderBy('net_sales', 'desc')
                ->paginate(50);
        } else {
            $salesTotals = $query->selectRaw('
                "الإجمالي" as period_name,
                COUNT(DISTINCT operhead.id) as invoices_count,
                SUM(operation_items.qty_out) as total_quantity,
                SUM(operation_items.qty_out * operation_items.item_price) as total_sales,
                SUM(operhead.fat_disc) as total_discount,
                SUM(operhead.fat_net) as net_sales
            ')
                ->paginate(50);
        }

        // أضف متوسط الفاتورة لكل صف
        foreach ($salesTotals->getCollection() as $row) {
            $row->average_invoice = $row->invoices_count > 0
                ? $row->net_sales / $row->invoices_count
                : 0;
        }

        return $salesTotals;
    }

    public function getGrandTotalInvoicesProperty(): int
    {
        return $this->salesTotals->sum('invoices_count');
    }

    public function getGrandTotalQuantityProperty(): float
    {
        return (float) $this->salesTotals->sum('total_quantity');
    }

    public function getGrandTotalSalesProperty(): float
    {
        return (float) $this->salesTotals->sum('total_sales');
    }

    public function getGrandTotalDiscountProperty(): float
    {
        return (float) $this->salesTotals->sum('total_discount');
    }

    public function getGrandTotalNetSalesProperty(): float
    {
        return (float) $this->salesTotals->sum('net_sales');
    }

    public function getGrandAverageInvoiceProperty(): float
    {
        $grandTotalInvoices = $this->grandTotalInvoices;
        $grandTotalNetSales = $this->grandTotalNetSales;
        
        return $grandTotalInvoices > 0 ? $grandTotalNetSales / $grandTotalInvoices : 0;
    }

    public function getTotalPeriodsProperty(): int
    {
        return $this->salesTotals->count();
    }

    public function getHighestSalesProperty(): float
    {
        return (float) ($this->salesTotals->max('net_sales') ?? 0);
    }

    public function getLowestSalesProperty(): float
    {
        return (float) ($this->salesTotals->min('net_sales') ?? 0);
    }

    public function getAverageSalesProperty(): float
    {
        $totalPeriods = $this->totalPeriods;
        $grandTotalNetSales = $this->grandTotalNetSales;
        
        return $totalPeriods > 0 ? $grandTotalNetSales / $totalPeriods : 0;
    }
}; ?>
<div>
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>تقرير المبيعات إجماليات</h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="from_date">من تاريخ:</label>
                        <input type="date" id="from_date" class="form-control" wire:model.live="fromDate">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date">إلى تاريخ:</label>
                        <input type="date" id="to_date" class="form-control" wire:model.live="toDate">
                    </div>
                    <div class="col-md-3">
                        <label for="group_by">تجميع حسب:</label>
                        <select id="group_by" class="form-control" wire:model.live="groupBy">
                            <option value="day">اليوم</option>
                            <option value="week">الأسبوع</option>
                            <option value="month">الشهر</option>
                            <option value="customer">العميل</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary d-block" wire:click="generateReport">توليد التقرير</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ $this->groupBy == 'customer' ? 'العميل' : 'الفترة' }}</th>
                                <th class="text-end">عدد الفواتير</th>
                                <th class="text-end">إجمالي الكمية</th>
                                <th class="text-end">إجمالي المبيعات</th>
                                <th class="text-end">إجمالي الخصم</th>
                                <th class="text-end">صافي المبيعات</th>
                                <th class="text-end">متوسط الفاتورة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->salesTotals as $total)
                                <tr>
                                    <td>
                                        @if ($this->groupBy == 'customer')
                                            {{ $total->customer_name ?? '---' }}
                                        @else
                                            {{ $total->period_name ?? '---' }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $total->invoices_count }}</td>
                                    <td class="text-end">{{ number_format($total->total_quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->total_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->total_discount, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->net_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->average_invoice, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th>الإجمالي</th>
                                <th class="text-end">{{ $this->grandTotalInvoices }}</th>
                                <th class="text-end">{{ number_format($this->grandTotalQuantity, 2) }}</th>
                                <th class="text-end">{{ number_format($this->grandTotalSales, 2) }}</th>
                                <th class="text-end">{{ number_format($this->grandTotalDiscount, 2) }}</th>
                                <th class="text-end">{{ number_format($this->grandTotalNetSales, 2) }}</th>
                                <th class="text-end">{{ number_format($this->grandAverageInvoice, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($this->salesTotals->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $this->salesTotals->links() }}
                    </div>
                @endif

                <!-- ملخص -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>إجمالي الفترات:</strong> {{ $this->totalPeriods }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>أعلى مبيعات:</strong> {{ number_format($this->highestSales, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>أدنى مبيعات:</strong> {{ number_format($this->lowestSales, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>متوسط المبيعات:</strong> {{ number_format($this->averageSales, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
