<?php

use Livewire\Volt\Component;
use App\Models\Item;
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
            'topSellingItems' => $this->getTopSellingItems(),
            'periodLabel' => $this->getPeriodLabel(),
        ];
    }
    
    private function getTopSellingItems()
    {
        // Remove SUM(operation_items.qty_out * operation_items.price) as total_revenue
        // since operation_items.price does not exist.
        // Instead, just return quantity and order_count.
        return OperationItems::select(
                'items.name',
                'items.id',
                DB::raw('SUM(operation_items.qty_out) as quantity'),
                DB::raw('COUNT(DISTINCT operhead.id) as order_count')
            )
            ->join('operhead', 'operation_items.pro_id', '=', 'operhead.id')
            ->join('items', 'operation_items.item_id', '=', 'items.id')
            ->where('operhead.pro_type', OperationTypeEnum::SALES_INVOICE->value)
            ->where('operhead.pro_date', '>=', $this->startDate)
            ->where('operhead.pro_date', '<=', $this->endDate)
            ->where('operation_items.isdeleted', 0)
            ->where('operation_items.qty_out', '>', 0)
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('quantity')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                $item->percentage = 0; // سيتم حسابها في JavaScript
                // Set total_revenue to null for compatibility with JS
                $item->total_revenue = null;
                return $item;
            });
    }
    
    private function getPeriodLabel()
    {
        switch ($this->selectedPeriod) {
            case 'week':
                return __('هذا الأسبوع');
            case 'month':
                return __('هذا الشهر');
            case 'quarter':
                return __('هذا الربع');
            case 'year':
                return __('هذا العام');
            default:
                return __('هذا الشهر');
        }
    }
}; ?>

<div class="card" style="direction: rtl; font-family: 'Cairo', sans-serif;">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
        <span>{{ __('الأصناف الأكثر مبيعاً') }}</span>
        <div class="d-flex gap-2 align-items-center">
            <small class="text-muted">{{ $periodLabel }}</small>
            <select wire:model.live="selectedPeriod" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem;">
                <option value="week">{{ __('أسبوع') }}</option>
                <option value="month">{{ __('شهر') }}</option>
                <option value="quarter">{{ __('ربع سنة') }}</option>
                <option value="year">{{ __('سنة') }}</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        @if($topSellingItems->count() > 0)
            <canvas id="topSellingItemsChart" height="300"></canvas>
        @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                <p>{{ __('لا توجد بيانات مبيعات في الفترة المحددة') }}</p>
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
        const ctx = document.getElementById('topSellingItemsChart');
        if (!ctx) return;
        
        // الحصول على البيانات من Livewire
        const topSellingItems = @json($topSellingItems);
        
        if (topSellingItems.length === 0) return;
        
        // حساب النسب المئوية
        const totalQuantity = topSellingItems.reduce((sum, item) => sum + item.quantity, 0);
        topSellingItems.forEach(item => {
            item.percentage = ((item.quantity / totalQuantity) * 100).toFixed(1);
        });
        
        const labels = topSellingItems.map(item => item.name);
        const quantities = topSellingItems.map(item => item.quantity);
        // revenues will be null, so we can skip it or show N/A in tooltip
        // const revenues = topSellingItems.map(item => item.total_revenue);

        // تدمير الرسم البياني السابق إذا كان موجوداً
        if (chart) {
            chart.destroy();
        }
        
        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: "{{ __('الكمية المباعة') }}",
                    data: quantities,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 5,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        rtl: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                const item = topSellingItems[context.dataIndex];
                                let revenueText = "{{ __('غير متوفر') }}";
                                if (item.total_revenue !== null && item.total_revenue !== undefined) {
                                    revenueText = `${item.total_revenue.toLocaleString()} {{ __('ريال') }}`;
                                }
                                return [
                                    `{{ __('الكمية') }}: ${context.parsed.y}`,
                                    `{{ __('الإيرادات') }}: ${revenueText}`,
                                    `{{ __('النسبة') }}: ${item.percentage}%`,
                                    `{{ __('عدد الطلبات') }}: ${item.order_count}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 12
                            },
                            color: '#666',
                            padding: 8,
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        },
                        title: {
                            display: true,
                            text: "{{ __('الكمية المباعة') }}",
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#333'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: "'Cairo', sans-serif",
                                size: 11
                            },
                            color: '#666',
                            maxRotation: 45,
                            minRotation: 0,
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                return label.length > 15 ? label.substring(0, 15) + '...' : label;
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // تهيئة الرسم البياني عند تحميل الصفحة
    initChart();
    
    // تحديث الرسم البياني عند تغيير البيانات
    Livewire.on('top-selling-items-updated', () => {
        setTimeout(initChart, 100);
    });
});
</script>
@endpush
