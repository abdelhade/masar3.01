@extends('admin.dashboard')

@section('title', 'إحصائيات الشيكات')

{{-- Dynamic Sidebar: نعرض فقط الشيكات والحسابات --}}
@section('sidebar')
    @include('components.sidebar.checks')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-white text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-chart-pie fa-2x"></i>
                            </div>
                            <div>
                                <h1 class="mb-1 fw-bold text-white">إحصائيات الشيكات</h1>
                                <p class="mb-0 text-white-75">نظرة عامة على أوراق القبض والدفع</p>
                            </div>
                        </div>
                        <!-- Date Filter -->
                        <div class="btn-group" role="group">
                            <a href="{{ route('checks.dashboard', ['date_filter' => 'week']) }}" 
                               class="btn btn-{{ $dateFilter === 'week' ? 'light' : 'outline-light' }}">
                                أسبوع
                            </a>
                            <a href="{{ route('checks.dashboard', ['date_filter' => 'month']) }}" 
                               class="btn btn-{{ $dateFilter === 'month' ? 'light' : 'outline-light' }}">
                                شهر
                            </a>
                            <a href="{{ route('checks.dashboard', ['date_filter' => 'year']) }}" 
                               class="btn btn-{{ $dateFilter === 'year' ? 'light' : 'outline-light' }}">
                                سنة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">إجمالي الشيكات</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h2>
                            <small class="text-muted">{{ number_format($stats['total_amount'], 2) }} ر.س</small>
                        </div>
                        <div class="icon-box bg-light rounded-circle p-3">
                            <i class="fas fa-file-invoice fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-2">شيكات معلقة</h6>
                            <h2 class="mb-0 fw-bold text-warning">{{ number_format($stats['pending']) }}</h2>
                            <small class="text-muted">{{ number_format($stats['pending_amount'], 2) }} ر.س</small>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-2">شيكات مصفاة</h6>
                            <h2 class="mb-0 fw-bold text-success">{{ number_format($stats['cleared']) }}</h2>
                            <small class="text-muted">{{ number_format($stats['cleared_amount'], 2) }} ر.س</small>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-2">شيكات مرتدة</h6>
                            <h2 class="mb-0 fw-bold text-danger">{{ number_format($stats['bounced']) }}</h2>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Status Distribution Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>توزيع الحالات</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>الاتجاه الشهري</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-4 mb-4">
        <!-- Overdue Checks -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle text-danger me-2"></i>الشيكات المتأخرة</h5>
                    <span class="badge bg-danger">{{ $overdueChecks->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($overdueChecks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الشيك</th>
                                        <th>البنك</th>
                                        <th>المبلغ</th>
                                        <th>تاريخ الاستحقاق</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueChecks as $check)
                                        <tr>
                                            <td><strong>{{ $check->check_number }}</strong></td>
                                            <td>{{ $check->bank_name }}</td>
                                            <td><strong class="text-primary">{{ number_format($check->amount, 2) }} ر.س</strong></td>
                                            <td>
                                                <span class="text-danger">
                                                    {{ $check->due_date->format('Y-m-d') }}
                                                    <br>
                                                    <small>{{ $check->due_date->diffForHumans() }}</small>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">لا توجد شيكات متأخرة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Checks by Bank -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-university text-primary me-2"></i>الشيكات حسب البنك</h5>
                </div>
                <div class="card-body p-0">
                    @if($checksByBank->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>البنك</th>
                                        <th>العدد</th>
                                        <th>إجمالي المبلغ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($checksByBank as $bank)
                                        <tr>
                                            <td>{{ $bank->bank_name ?: 'غير محدد' }}</td>
                                            <td><span class="badge bg-primary">{{ $bank->count }}</span></td>
                                            <td><strong>{{ number_format($bank->total_amount, 2) }} ر.س</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-database fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد بيانات</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Checks -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>الشيكات الأخيرة</h5>
                    <a href="{{ route('checks.incoming') }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل <i class="fas fa-arrow-left ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentChecks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الشيك</th>
                                        <th>البنك</th>
                                        <th>المبلغ</th>
                                        <th>تاريخ الاستحقاق</th>
                                        <th>الحالة</th>
                                        <th>النوع</th>
                                        <th>أُنشئ بواسطة</th>
                                        <th>تاريخ الإنشاء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentChecks as $check)
                                        <tr>
                                            <td><strong>{{ $check->check_number }}</strong></td>
                                            <td>{{ $check->bank_name }}</td>
                                            <td><strong class="text-primary">{{ number_format($check->amount, 2) }} ر.س</strong></td>
                                            <td>{{ $check->due_date->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $check->status_color }}">
                                                    @if($check->status == 'pending') معلق
                                                    @elseif($check->status == 'cleared') مصفى
                                                    @elseif($check->status == 'bounced') مرتد
                                                    @else ملغى
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $check->type === 'incoming' ? 'success' : 'info' }}">
                                                    {{ $check->type === 'incoming' ? 'قبض' : 'دفع' }}
                                                </span>
                                            </td>
                                            <td>{{ $check->creator->name ?? 'غير محدد' }}</td>
                                            <td>{{ $check->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد شيكات</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('checks.incoming.create') }}" class="btn btn-success btn-lg w-100 py-3">
                                <i class="fas fa-plus-circle me-2"></i>
                                إضافة ورقة قبض
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('checks.outgoing.create') }}" class="btn btn-info btn-lg w-100 py-3">
                                <i class="fas fa-plus-circle me-2"></i>
                                إضافة ورقة دفع
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('checks.incoming') }}" class="btn btn-outline-primary btn-lg w-100 py-3">
                                <i class="fas fa-arrow-circle-down me-2"></i>
                                أوراق القبض
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('checks.outgoing') }}" class="btn btn-outline-primary btn-lg w-100 py-3">
                                <i class="fas fa-arrow-circle-up me-2"></i>
                                أوراق الدفع
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}

.icon-shape {
    width: 60px;
    height: 60px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.icon-box {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    border-radius: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.border-4 {
    border-width: 4px !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Cairo, sans-serif'
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart');
    if (trendCtx) {
        const monthlyData = @json($monthlyTrend);
        const labels = monthlyData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`).reverse();
        const counts = monthlyData.map(item => item.count).reverse();
        const amounts = monthlyData.map(item => item.total_amount).reverse();

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
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'إجمالي المبلغ (ر.س)',
                    data: amounts,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true,
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
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Cairo, sans-serif'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'عدد الشيكات',
                            font: {
                                family: 'Cairo, sans-serif'
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'المبلغ (ر.س)',
                            font: {
                                family: 'Cairo, sans-serif'
                            }
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
@endpush
