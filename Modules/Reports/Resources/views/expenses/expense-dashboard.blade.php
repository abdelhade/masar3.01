@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.expenses')
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-wallet text-primary me-2"></i>
                    {{ __('Expenses Management') }}
                </h4>
                <p class="text-muted mb-0">{{ __('Comprehensive dashboard for expenses management and tracking') }}</p>
            </div>
            <div>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('New Expense Record') }}
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <!-- Today Expenses -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">{{ __('Today Expenses') }}</p>
                                <h3 class="mb-0 fw-bold text-primary">{{ number_format($todayExpenses, 2) }}</h3>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-calendar-day text-primary fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Expenses -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">{{ __('Monthly Expenses') }}</p>
                                <h3 class="mb-0 fw-bold text-success">{{ number_format($monthExpenses, 2) }}</h3>
                                <small class="{{ $changePercentage >= 0 ? 'text-danger' : 'text-success' }}">
                                    <i class="fas fa-{{ $changePercentage >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ abs($changePercentage) }}% {{ __('vs previous month') }}
                                </small>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-chart-line text-success fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Transactions -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">{{ __('Monthly Transactions Count') }}</p>
                                <h3 class="mb-0 fw-bold text-info">{{ number_format($monthTransactionsCount) }}</h3>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-receipt text-info fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Expense Account -->
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">{{ __('Top Expense Item') }}</p>
                                <h5 class="mb-0 fw-bold text-warning">
                                    {{ $topExpenseAccount?->accHead?->aname ?? '---' }}
                                </h5>
                                @if ($topExpenseAccount)
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
            <!-- Daily Expenses Chart -->
            <div class="col-xl-8 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-area text-primary me-2"></i>
                            {{ __('Daily Expenses') }} - {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                        </h5>
                    </div>
                    <div class="card-body" style="height: 300px; position: relative;">
                        <canvas id="dailyExpensesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Expenses Distribution Chart -->
            <div class="col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-pie-chart text-success me-2"></i>
                            {{ __('Expenses Distribution') }}
                        </h5>
                    </div>
                    <div class="card-body" style="height: 300px; position: relative;">
                        <canvas id="expensesByAccountChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Quick Actions -->
            <div class="col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            {{ __('Quick Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('expenses.create') }}" class="btn btn-outline-primary text-start">
                                <i class="fas fa-plus-circle me-2"></i>
                                {{ __('New Expense Record') }}
                            </a>
                            <a href="{{ route('reports.general-expenses-report') }}"
                                class="btn btn-outline-success text-start">
                                <i class="fas fa-file-alt me-2"></i>
                                {{ __('General Expenses Report') }}
                            </a>
                            <a href="{{ route('reports.general-expenses-daily-report') }}"
                                class="btn btn-outline-info text-start">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ __('Expense Account Statement') }}
                            </a>
                            <a href="{{ route('reports.expenses-balance-report') }}"
                                class="btn btn-outline-secondary text-start">
                                <i class="fas fa-balance-scale me-2"></i>
                                {{ __('Expenses Balance Sheet') }}
                            </a>
                            <a href="{{ route('reports.general-cost-centers-report') }}"
                                class="btn btn-outline-warning text-start">
                                <i class="fas fa-sitemap me-2"></i>
                                {{ __('Cost Centers Report') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="col-xl-8 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history text-info me-2"></i>
                            {{ __('Recent Expenses') }}
                        </h5>
                        <a href="{{ route('reports.general-expenses-report') }}" class="btn btn-sm btn-outline-primary">
                            {{ __('View All') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Expense Item') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Cost Center') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
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
                                                <span
                                                    class="fw-bold text-danger">{{ number_format($expense->debit, 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                {{ __('No expenses recorded') }}
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

        <!-- Top Expense Items -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sort-amount-down text-danger me-2"></i>
                            {{ __('Top Expense Items This Month') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($expensesByAccount as $index => $item)
                                <div class="col-md-4 col-lg mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <span
                                                class="badge bg-{{ ['primary', 'success', 'warning', 'info', 'danger'][$index % 5] }} me-2">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="fw-medium small">{{ $item->accHead?->aname ?? '---' }}</span>
                                        </div>
                                        <h5 class="mb-0 text-danger">{{ number_format($item->total, 2) }}</h5>
                                        @php
                                            $percentage =
                                                $monthExpenses > 0 ? ($item->total / $monthExpenses) * 100 : 0;
                                        @endphp
                                        <div class="progress mt-2" style="height: 5px;">
                                            <div class="progress-bar bg-{{ ['primary', 'success', 'warning', 'info', 'danger'][$index % 5] }}"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($percentage, 1) }}%
                                            {{ __('of total') }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-4">
                                    {{ __('No data available') }}
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
                // Daily expenses chart data
                const dailyData = @json($dailyExpenses);
                const dailyLabels = dailyData.map(item => item.date);
                const dailyValues = dailyData.map(item => parseFloat(item.total));

                // Daily expenses chart
                const dailyCtx = document.getElementById('dailyExpensesChart');
                if (dailyCtx) {
                    new Chart(dailyCtx, {
                        type: 'line',
                        data: {
                            labels: dailyLabels,
                            datasets: [{
                                label: '{{ __('Expenses') }}',
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

                // Distribution chart data
                const accountsData = @json($expensesByAccount);
                const accountLabels = accountsData.map(item => item.acc_head?.aname || '---');
                const accountValues = accountsData.map(item => parseFloat(item.total));

                // Doughnut chart
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
