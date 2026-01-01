@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">إحصائيات المشتريات</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المشتريات</h5>
                        <p class="card-text">{{ number_format($stats['total_purchases'], 2) }} جنيه</p>
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
                        <h5 class="card-title">مشتريات اليوم</h5>
                        <p class="card-text">{{ number_format($stats['today_purchases'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">المدفوعات المعلقة</h5>
                        <p class="card-text">{{ number_format($stats['pending_payments'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">أعلى فاتورة مشتريات</h5>
                        <p class="card-text">{{ number_format($stats['highest_purchase'], 2) }} جنيه</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">الموردين النشطين</h5>
                        <p class="card-text">{{ $stats['active_suppliers'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">توزيع المشتريات والمرتجعات</h5>
                        <canvas id="purchasesPieChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">المشتريات خلال الأسبوع</h5>
                        <canvas id="purchasesBarChart" class="chart-canvas"></canvas>
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
        const pieCtx = document.getElementById('purchasesPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['المشتريات', 'المرتجعات'],
                datasets: [{
                    data: [@json($stats['total_purchases']), @json($stats['total_returns'])],
                    backgroundColor: ['#007bff', '#dc3545'],
                    borderColor: ['#0056b3', '#c82333'],
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
                        text: 'توزيع المشتريات والمرتجعات',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('purchasesBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($stats['purchases_by_day']->toArray())),
                datasets: [{
                        label: 'المشتريات',
                        data: @json(array_values($stats['purchases_by_day']->toArray())),
                        backgroundColor: '#007bff',
                        borderColor: '#0056b3',
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
                        text: 'المشتريات والمرتجعات خلال الأسبوع',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });
    </script>
@endsection
