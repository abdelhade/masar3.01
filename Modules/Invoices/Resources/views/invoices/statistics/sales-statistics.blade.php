@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.sales-invoices')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">إحصائيات المبيعات</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المبيعات</h5>
                        <p class="card-text">{{ number_format($stats['total_sales'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المرتجعات</h5>
                        <p class="card-text">{{ number_format($stats['total_returns'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الطلبات</h5>
                        <p class="card-text">{{ $stats['total_orders'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي العروض</h5>
                        <p class="card-text">{{ $stats['total_quotations'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الربح</h5>
                        <p class="card-text">{{ number_format($stats['total_profit'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">مبيعات اليوم</h5>
                        <p class="card-text">{{ number_format($stats['today_sales'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">أعلى فاتورة مبيعات</h5>
                        <p class="card-text">{{ number_format($stats['highest_sale'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">العملاء النشطين</h5>
                        <p class="card-text">{{ $stats['active_customers'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">توزيع المبيعات والمرتجعات</h5>
                        <canvas id="salesPieChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">المبيعات خلال الأسبوع</h5>
                        <canvas id="salesBarChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .stats-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #f8f9fa;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #343a40;
        }

        .card-text {
            font-size: 1.3rem;
            font-weight: bold;
            color: #007bff;
        }

        h2 {
            font-weight: 700;
            color: #212529;
        }

        .chart-canvas {
            max-height: 200px !important;
            max-width: 100% !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pie Chart
        const pieCtx = document.getElementById('salesPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['المبيعات', 'المرتجعات'],
                datasets: [{
                    data: [@json($stats['total_sales']), @json($stats['total_returns'])],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderColor: ['#1e7e34', '#c82333'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'توزيع المبيعات والمرتجعات',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('salesBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($stats['sales_by_day']->toArray())),
                datasets: [{
                        label: 'المبيعات',
                        data: @json(array_values($stats['sales_by_day']->toArray())),
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
                        borderWidth: 1
                    },
                    {
                        label: 'المرتجعات',
                        data: @json(array_values($stats['returns_by_day']->toArray())),
                        backgroundColor: '#dc3545',
                        borderColor: '#c82333',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'القيمة (جنيه)',
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'التاريخ',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'المبيعات والمرتجعات خلال الأسبوع',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });
    </script>
@endsection
