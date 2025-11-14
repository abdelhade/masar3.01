@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3 class="mb-3">إحصائيات البيانات الأساسية</h3>
            </div>
        </div>

        <!-- كارد الملخص -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">ملخص شامل للحسابات</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <h5 class="text-primary mb-1">
                                    {{ array_sum(array_column(array_filter($stats, fn($s) => isset($s['count'])), 'count')) }}
                                </h5>
                                <small class="text-muted">إجمالي الحسابات</small>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-success mb-1">
                                    {{ number_format(array_sum(array_column(array_filter($stats, fn($s) => isset($s['total_balance'])), 'total_balance')), 0) }}
                                    ج</h5>
                                <small class="text-muted">إجمالي الأرصدة</small>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-info mb-1">
                                    {{ array_sum(array_column(array_filter($stats, fn($s) => isset($s['active_accounts'])), 'active_accounts')) }}
                                </h5>
                                <small class="text-muted">الحسابات النشطة</small>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-warning mb-1">
                                    {{ count(array_filter($stats, fn($s) => ($s['total_balance'] ?? 0) > 0)) }}</h5>
                                <small class="text-muted">ذات الرصيد</small>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-danger mb-1">
                                    {{ count(array_filter($stats, fn($s) => ($s['balance'] ?? 0) < 0)) }}</h5>
                                <small class="text-muted">الدائنين</small>
                            </div>
                            <div class="col-md-2">
                                <h5 class="text-secondary mb-1">{{ count($stats) }}</h5>
                                <small class="text-muted">أنواع الحسابات</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الشارتس -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-2">
                        <h6 class="mb-2">توزيع الحسابات</h6>
                        <canvas id="accountsPieChart" class="chart-canvas-small"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-2">
                        <h6 class="mb-2">الأرصدة</h6>
                        <canvas id="accountsBarChart" class="chart-canvas-small"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- تفاصيل مختصرة -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">أهم الإحصائيات</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            @foreach (['clients', 'suppliers', 'funds', 'banks'] as $type)
                                @if (isset($stats[$type]))
                                    <div class="col-md-3 mb-2">
                                        <small class="text-muted">{{ __('accounts.types.' . $type) }}</small><br>
                                        <span class="fw-bold">{{ $stats[$type]['count'] ?? 0 }}</span> حساب |
                                        <span
                                            class="text-success">{{ number_format($stats[$type]['total_balance'] ?? 0, 0) }}</span>
                                        ج
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 0.75rem;
        }

        .chart-canvas-small {
            max-height: 180px !important;
            width: 100% !important;
        }

        h3 {
            font-size: 1.2rem;
        }

        h5 {
            font-size: 1.1rem;
            margin: 0;
        }

        h6 {
            font-size: 0.9rem;
        }

        small {
            font-size: 0.7rem;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Charts with smaller size
        const pieCtx = document.getElementById('accountsPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut', // استخدم doughnut بدل pie لشكل أصغر
            data: {
                labels: @json($stats['chart_data']['labels']),
                datasets: [{
                    data: @json($stats['chart_data']['counts']),
                    backgroundColor: ['#28a745', '#007bff', '#dc3545', '#ffc107', '#17a2b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const barCtx = document.getElementById('accountsBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: @json(array_slice($stats['chart_data']['labels'], 0, 5)), // أول 5 أنواع بس
                datasets: [{
                    data: @json(array_slice($stats['chart_data']['balances'], 0, 5)),
                    backgroundColor: ['#28a745', '#007bff', '#dc3545', '#ffc107', '#17a2b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        display: false
                    }, // إخفاء المحور Y لتوفير المساحة
                    x: {
                        ticks: {
                            font: {
                                size: 9
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endsection
