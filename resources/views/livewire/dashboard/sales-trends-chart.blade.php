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
            'salesTrends' => $this->getSalesTrends(),
            'periodLabel' => $this->getPeriodLabel(),
        ];
    }
    
    private function getSalesTrends()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        
        $periods = [];
        $sales = [];
        $quantities = [];
        
        if ($this->selectedPeriod === 'week' || $this->selectedPeriod === 'month') {
            // يومياً
            $current = $start->copy();
            while ($current <= $end) {
                $periods[] = $current->format('Y-m-d');
                
                $daySales = OperationItems::join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
                    ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
                    ->whereDate('operhead.date', $current)
                    ->where('operation_items.isdeleted', 0)
                    ->where('operation_items.qty_out', '>', 0)
                    ->sum(DB::raw('operation_items.qty_out * operation_items.price'));
                
                $dayQuantity = OperationItems::join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
                    ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
                    ->whereDate('operhead.date', $current)
                    ->where('operation_items.isdeleted', 0)
                    ->where('operation_items.qty_out', '>', 0)
                    ->sum('operation_items.qty_out');
                
                $sales[] = $daySales;
                $quantities[] = $dayQuantity;
                
                $current->addDay();
            }
        } else {
            // شهرياً
            $current = $start->copy();
            while ($current <= $end) {
                $periods[] = $current->format('Y-m');
                
                $monthSales = OperationItems::join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
                    ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
                    ->whereYear('operhead.date', $current->year)
                    ->whereMonth('operhead.date', $current->month)
                    ->where('operation_items.isdeleted', 0)
                    ->where('operation_items.qty_out', '>', 0)
                    ->sum(DB::raw('operation_items.qty_out * operation_items.price'));
                
                $monthQuantity = OperationItems::join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
                    ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
                    ->whereYear('operhead.date', $current->year)
                    ->whereMonth('operhead.date', $current->month)
                    ->where('operation_items.isdeleted', 0)
                    ->where('operation_items.qty_out', '>', 0)
                    ->sum('operation_items.qty_out');
                
                $sales[] = $monthSales;
                $quantities[] = $monthQuantity;
                
                $current->addMonth();
            }
        }
        
        return [
            'periods' => $periods,
            'sales' => $sales,
            'quantities' => $quantities
        ];
    }
    
    private function getPeriodLabel()
    {
        switch ($this->selectedPeriod) {
            case 'week':
                return 'تطور المبيعات الأسبوعي';
            case 'month':
                return 'تطور المبيعات الشهري';
            case 'quarter':
                return 'تطور المبيعات الفصلي';
            case 'year':
                return 'تطور المبيعات السنوي';
            default:
                return 'تطور المبيعات';
        }
    }
}; ?>

<div class="card" style="direction: rtl; font-family: 'Cairo', sans-serif;">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
        <span>{{ $periodLabel }}</span>
        <div class="d-flex gap-2 align-items-center">
            <select wire:model.live="selectedPeriod" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem;">
                <option value="week">أسبوع</option>
                <option value="month">شهر</option>
                <option value="quarter">ربع سنة</option>
                <option value="year">سنة</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        @if(count($salesTrends['periods']) > 0)
            <canvas id="salesTrendsChart" height="300"></canvas>
        @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-chart-line fa-3x mb-3"></i>
                <p>لا توجد بيانات مبيعات في الفترة المحددة</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('livewire:init', () => {
    let chart = null;
    
    function initChart() {
        const ctx = document.getElementById('salesTrendsChart');
        if (!ctx) return;
        
        // الحصول على البيانات من Livewire
        const salesTrends = @json($salesTrends);
        
        if (salesTrends.periods.length === 0) return;
        
        // تنسيق التسميات
        const labels = salesTrends.periods.map(period => {
            if (period.includes('-')) {
                // تاريخ يومي
                const date = new Date(period);
                return date.toLocaleDateString('ar-SA', { month: 'short', day: 'numeric' });
            } else {
                // تاريخ شهري
                const [year, month] = period.split('-');
                const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                                   'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                return `${monthNames[parseInt(month) - 1]} ${year}`;
            }
        });
        
        // تدمير الرسم البياني السابق إذا كان موجوداً
        if (chart) {
            chart.destroy();
        }
        
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'المبيعات (ريال)',
                    data: salesTrends.sales,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'الكميات المباعة',
                    data: salesTrends.quantities,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                family: "'Cairo', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        rtl: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return `المبيعات: ${context.parsed.y.toLocaleString()} ريال`;
                                } else {
                                    return `الكميات: ${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'الفترة الزمنية',
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#333'
                        },
                        ticks: {
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 11
                            },
                            color: '#666'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'المبيعات (ريال)',
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#333'
                        },
                        ticks: {
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 12
                            },
                            color: '#666',
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'الكميات',
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#333'
                        },
                        ticks: {
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 12
                            },
                            color: '#666',
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    // تهيئة الرسم البياني عند تحميل الصفحة
    initChart();
    
    // تحديث الرسم البياني عند تغيير البيانات
    Livewire.on('sales-trends-updated', () => {
        setTimeout(initChart, 100);
    });
});
</script>
@endpush
