@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.expenses')
@endsection

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-wallet text-primary me-2"></i>
                إدارة المصروفات
            </h4>
            <p class="text-muted mb-0">لوحة تحكم شاملة لإدارة ومتابعة المصروفات</p>
        </div>
        <div>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                تسجيل مصروف جديد
            </a>
        </div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row mb-4">
        <!-- مصروفات اليوم -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">مصروفات اليوم</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ number_format($todayExpenses, 2) }}</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-calendar-day text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- مصروفات الشهر -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">مصروفات الشهر</p>
                            <h3 class="mb-0 fw-bold text-success">{{ number_format($monthExpenses, 2) }}</h3>
                            <small class="{{ $changePercentage >= 0 ? 'text-danger' : 'text-success' }}">
                                <i class="fas fa-{{ $changePercentage >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($changePercentage) }}% مقارنة بالشهر السابق
                            </small>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-chart-line text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- عدد العمليات -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">عدد العمليات هذا الشهر</p>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format($monthTransactionsCount) }}</h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-receipt text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- أعلى بند مصروفات -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">أعلى بند مصروفات</p>
                            <h5 class="mb-0 fw-bold text-warning">
                                {{ $topExpenseAccount?->accHead?->aname ?? '---' }}
                            </h5>
                            @if($topExpenseAccount)
                                <small class="text-muted">{{ number_format($topExpenseAccount->total, 2) }}</small>
                            @endif
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-crown text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- الرسم البياني للمصروفات اليومية -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area text-primary me-2"></i>
                        المصروفات اليومية - {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </h5>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="dailyExpensesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- المصروفات حسب البند -->
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-pie-chart text-success me-2"></i>
                        توزيع المصروفات
                    </h5>
                </div>
                <div class="card-body" style="height: 300px; position: relative;">
                    <canvas id="expensesByAccountChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- الإجراءات السريعة -->
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        إجراءات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('expenses.create') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus-circle me-2"></i>
                            تسجيل مصروف جديد
                        </a>
                        <a href="{{ route('reports.general-expenses-report') }}" class="btn btn-outline-success text-start">
                            <i class="fas fa-file-alt me-2"></i>
                            تقرير المصروفات العام
                        </a>
                        <a href="{{ route('reports.general-expenses-daily-report') }}" class="btn btn-outline-info text-start">
                            <i class="fas fa-calendar-alt me-2"></i>
                            كشف حساب مصروف
                        </a>
                        <a href="{{ route('reports.expenses-balance-report') }}" class="btn btn-outline-secondary text-start">
                            <i class="fas fa-balance-scale me-2"></i>
                            ميزان المصروفات
                        </a>
                        <a href="{{ route('reports.general-cost-centers-report') }}" class="btn btn-outline-warning text-start">
                            <i class="fas fa-sitemap me-2"></i>
                            تقرير مراكز التكلفة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- آخر المصروفات -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-info me-2"></i>
                        آخر المصروفات المسجلة
                    </h5>
                    <a href="{{ route('reports.general-expenses-report') }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>البند</th>
                                    <th>الوصف</th>
                                    <th>مركز التكلفة</th>
                                    <th class="text-end">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExpenses as $expense)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $expense->crtime ? \Carbon\Carbon::parse($expense->crtime)->format('Y-m-d') : '---' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $expense->accHead?->aname ?? '---' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $expense->accHead?->code ?? '' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($expense->info ?? '---', 30) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $expense->costCenter?->cname ?? '---' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-danger">{{ number_format($expense->debit, 2) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        لا توجد مصروفات مسجلة
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أعلى بنود المصروفات -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sort-amount-down text-danger me-2"></i>
                        أعلى بنود المصروفات هذا الشهر
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($expensesByAccount as $index => $item)
                        <div class="col-md-4 col-lg mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-{{ ['primary', 'success', 'warning', 'info', 'danger'][$index % 5] }} me-2">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="fw-medium small">{{ $item->accHead?->aname ?? '---' }}</span>
                                </div>
                                <h5 class="mb-0 text-danger">{{ number_format($item->total, 2) }}</h5>
                                @php
                                    $percentage = $monthExpenses > 0 ? ($item->total / $monthExpenses) * 100 : 0;
                                @endphp
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-{{ ['primary', 'success', 'warning', 'info', 'danger'][$index % 5] }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($percentage, 1) }}% من الإجمالي</small>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-4">
                            لا توجد بيانات
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // بيانات الرسم البياني للمصروفات اليومية
    const dailyData = @json($dailyExpenses);
    const dailyLabels = dailyData.map(item => item.date);
    const dailyValues = dailyData.map(item => parseFloat(item.total));

    // الرسم البياني للمصروفات اليومية
    const dailyCtx = document.getElementById('dailyExpensesChart');
    if (dailyCtx) {
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'المصروفات',
                    data: dailyValues,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // بيانات الرسم البياني للتوزيع
    const accountsData = @json($expensesByAccount);
    const accountLabels = accountsData.map(item => item.acc_head?.aname || '---');
    const accountValues = accountsData.map(item => parseFloat(item.total));

    // الرسم البياني الدائري
    const pieCtx = document.getElementById('expensesByAccountChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: accountLabels,
                datasets: [{
                    data: accountValues,
                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#0dcaf0',
                        '#dc3545'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection

