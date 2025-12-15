<?php

use Livewire\Volt\Component;
use Modules\Checks\Models\Check;
use Modules\Checks\Services\CheckService;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $dateFilter = 'month';

    private function checkService(): CheckService
    {
        return app(CheckService::class);
    }

    private function getDateRange(): array
    {
        return match($this->dateFilter) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function with(): array
    {
        $dateRange = $this->getDateRange();
        
        $stats = $this->checkService()->getStatistics($dateRange);

        $overdueChecks = Check::where('status', Check::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $recentChecks = Check::with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $checksByBank = Check::whereBetween('created_at', $dateRange)
            ->select('bank_name', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('bank_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $monthlyTrend = Check::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('count(*) as count'),
                DB::raw('sum(amount) as total_amount')
            )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return [
            'stats' => $stats,
            'overdueChecks' => $overdueChecks,
            'recentChecks' => $recentChecks,
            'checksByBank' => $checksByBank,
            'monthlyTrend' => $monthlyTrend,
        ];
    }
};

?>

<div>
    <flux:heading>لوحة تحكم الشيكات</flux:heading>

    <!-- Date Filter -->
    <div class="mb-4 flex justify-end gap-2">
        <flux:button wire:click="$set('dateFilter', 'week')" variant="{{ $dateFilter === 'week' ? 'primary' : 'ghost' }}" size="sm">
            أسبوع
        </flux:button>
        <flux:button wire:click="$set('dateFilter', 'month')" variant="{{ $dateFilter === 'month' ? 'primary' : 'ghost' }}" size="sm">
            شهر
        </flux:button>
        <flux:button wire:click="$set('dateFilter', 'year')" variant="{{ $dateFilter === 'year' ? 'primary' : 'ghost' }}" size="sm">
            سنة
        </flux:button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <flux:card>
            <div class="flex justify-between items-center">
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">إجمالي الشيكات</flux:text>
                    <flux:heading size="lg">{{ number_format($stats['total']) }}</flux:heading>
                </div>
                <flux:icon name="check-square" class="w-12 h-12 text-gray-300" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-center">
                <div>
                    <flux:text class="text-sm text-yellow-600 dark:text-yellow-400">شيكات معلقة</flux:text>
                    <flux:heading size="lg">{{ number_format($stats['pending']) }}</flux:heading>
                    <flux:text class="text-xs text-gray-500">{{ number_format($stats['pending_amount'], 2) }} ر.س</flux:text>
                </div>
                <flux:icon name="clock" class="w-12 h-12 text-gray-300" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-center">
                <div>
                    <flux:text class="text-sm text-green-600 dark:text-green-400">شيكات مصفاة</flux:text>
                    <flux:heading size="lg">{{ number_format($stats['cleared']) }}</flux:heading>
                    <flux:text class="text-xs text-gray-500">{{ number_format($stats['cleared_amount'], 2) }} ر.س</flux:text>
                </div>
                <flux:icon name="check" class="w-12 h-12 text-gray-300" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-between items-center">
                <div>
                    <flux:text class="text-sm text-red-600 dark:text-red-400">شيكات مرتدة</flux:text>
                    <flux:heading size="lg">{{ number_format($stats['bounced']) }}</flux:heading>
                </div>
                <flux:icon name="exclamation-triangle" class="w-12 h-12 text-gray-300" />
            </div>
        </flux:card>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <!-- Overdue Checks -->
        <flux:card>
            <flux:heading size="md">الشيكات المتأخرة</flux:heading>
            
            @if($overdueChecks->count() > 0)
                <flux:table :headers="['رقم الشيك', 'البنك', 'المبلغ', 'تاريخ الاستحقاق']">
                    @foreach($overdueChecks as $check)
                        <flux:row wire:key="overdue-{{ $check->id }}">
                            <flux:cell>{{ $check->check_number }}</flux:cell>
                            <flux:cell>{{ $check->bank_name }}</flux:cell>
                            <flux:cell>{{ number_format($check->amount, 2) }} ر.س</flux:cell>
                            <flux:cell>
                                <div class="text-red-600">
                                    {{ $check->due_date->format('Y-m-d') }}
                                    <br><small>{{ $check->due_date->diffForHumans() }}</small>
                                </div>
                            </flux:cell>
                        </flux:row>
                    @endforeach
                </flux:table>
            @else
                <flux:empty-state>
                    لا توجد شيكات متأخرة
                </flux:empty-state>
            @endif
        </flux:card>

        <!-- Checks by Bank -->
        <flux:card>
            <flux:heading size="md">الشيكات حسب البنك</flux:heading>
            
            @if($checksByBank->count() > 0)
                <flux:table :headers="['البنك', 'العدد', 'إجمالي المبلغ']">
                    @foreach($checksByBank as $bank)
                        <flux:row wire:key="bank-{{ $bank->bank_name }}">
                            <flux:cell>{{ $bank->bank_name }}</flux:cell>
                            <flux:cell>
                                <flux:badge>{{ $bank->count }}</flux:badge>
                            </flux:cell>
                            <flux:cell>{{ number_format($bank->total_amount, 2) }} ر.س</flux:cell>
                        </flux:row>
                    @endforeach
                </flux:table>
            @else
                <flux:empty-state>
                    لا توجد بيانات
                </flux:empty-state>
            @endif
        </flux:card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <!-- Status Distribution Chart -->
        <flux:card>
            <flux:heading size="md">توزيع الحالات</flux:heading>
            <div class="h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </flux:card>

        <!-- Monthly Trend Chart -->
        <flux:card>
            <flux:heading size="md">الاتجاه الشهري</flux:heading>
            <div class="h-64">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </flux:card>
    </div>

    <!-- Recent Checks -->
    <flux:card>
        <flux:heading size="md">الشيكات الأخيرة</flux:heading>
        
        @if($recentChecks->count() > 0)
            <flux:table :headers="['رقم الشيك', 'البنك', 'المبلغ', 'تاريخ الاستحقاق', 'الحالة', 'النوع', 'أُنشئ بواسطة', 'تاريخ الإنشاء']">
                @foreach($recentChecks as $check)
                    <flux:row wire:key="recent-{{ $check->id }}">
                        <flux:cell><strong>{{ $check->check_number }}</strong></flux:cell>
                        <flux:cell>{{ $check->bank_name }}</flux:cell>
                        <flux:cell>{{ number_format($check->amount, 2) }} ر.س</flux:cell>
                        <flux:cell>{{ $check->due_date->format('Y-m-d') }}</flux:cell>
                        <flux:cell>
                            <flux:badge color="{{ $check->status_color }}">
                                {{ Check::getStatuses()[$check->status] }}
                            </flux:badge>
                        </flux:cell>
                        <flux:cell>
                            <flux:badge color="{{ $check->type === 'incoming' ? 'success' : 'info' }}">
                                {{ Check::getTypes()[$check->type] }}
                            </flux:badge>
                        </flux:cell>
                        <flux:cell>{{ $check->creator->name ?? 'غير محدد' }}</flux:cell>
                        <flux:cell>{{ $check->created_at->format('Y-m-d H:i') }}</flux:cell>
                    </flux:row>
                @endforeach
            </flux:table>
        @else
            <flux:empty-state>
                لا توجد شيكات
            </flux:empty-state>
        @endif
    </flux:card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@script
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const statusData = {
                labels: ['معلق', 'مصفى', 'مرتد', 'ملغى'],
                datasets: [{
                    data: [
                        {{ $stats['pending'] }},
                        {{ $stats['cleared'] }},
                        {{ $stats['bounced'] }},
                        {{ $stats['total'] - $stats['pending'] - $stats['cleared'] - $stats['bounced'] }}
                    ],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderColor: [
                        'rgb(255, 193, 7)',
                        'rgb(40, 167, 69)',
                        'rgb(220, 53, 69)',
                        'rgb(108, 117, 125)'
                    ],
                    borderWidth: 2
                }]
            };

            new Chart(statusCtx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Monthly Trend Chart
        const trendCtx = document.getElementById('monthlyTrendChart');
        if (trendCtx) {
            const monthlyData = @json($monthlyTrend);
            const labels = monthlyData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);
            const counts = monthlyData.map(item => item.count);
            const amounts = monthlyData.map(item => item.total_amount);

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'عدد الشيكات',
                        data: counts,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'إجمالي المبلغ (ر.س)',
                        data: amounts,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4,
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
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'عدد الشيكات'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'المبلغ (ر.س)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endscript
