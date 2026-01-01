@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inventory-invoices')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">{{ __('Inventory Statistics') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Total Waste') }}</h5>
                        <p class="card-text">{{ number_format($stats['total_waste'], 2) }} {{ __('EGP') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Total Issues') }}</h5>
                        <p class="card-text">{{ number_format($stats['total_issues'], 2) }} {{ __('EGP') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Total Additions') }}</h5>
                        <p class="card-text">{{ number_format($stats['total_additions'], 2) }} {{ __('EGP') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Total Transfers') }}</h5>
                        <p class="card-text">{{ $stats['total_transfers'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Total Items') }}</h5>
                        <p class="card-text">{{ $stats['total_items'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Low Stock Items') }}</h5>
                        <p class="card-text">{{ $stats['low_stock_items'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Total Inventory Value') }}</h5>
                        <p class="card-text">{{ number_format($stats['total_inventory_value'], 2) }} {{ __('EGP') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Top Selling Item') }}</h5>
                        <p class="card-text">{{ $stats['top_selling_item_name'] }} ({{ $stats['top_selling_item_qty'] }})
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Inventory Operations Distribution') }}</h5>
                        <canvas id="inventoryPieChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Inventory Operations') }}</h5>
                        <canvas id="inventoryBarChart" class="chart-canvas"></canvas>
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
        const pieCtx = document.getElementById('inventoryPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ["{{ __('Waste') }}", "{{ __('Issues') }}", "{{ __('Additions') }}",
                    "{{ __('Transfers') }}"
                ],
                datasets: [{
                    data: [
                        @json($stats['inventory_by_type']['waste']),
                        @json($stats['inventory_by_type']['issues']),
                        @json($stats['inventory_by_type']['additions']),
                        @json($stats['inventory_by_type']['transfers']),
                    ],
                    backgroundColor: ['#dc3545', '#007bff', '#28a745', '#ffc107'],
                    borderColor: ['#c82333', '#0056b3', '#1e7e34', '#e0a800'],
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
                        text: "{{ __('Inventory Operations Distribution') }}",
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });


        // Bar Chart
        const barCtx = document.getElementById('inventoryBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ["{{ __('Waste') }}", "{{ __('Issues') }}", "{{ __('Additions') }}",
                    "{{ __('Transfers') }}"
                ],
                datasets: [{
                    label: "{{ __('Value (EGP)') }}",
                    data: [
                        @json($stats['inventory_by_type']['waste']),
                        @json($stats['inventory_by_type']['issues']),
                        @json($stats['inventory_by_type']['additions']),
                        @json($stats['inventory_by_type']['transfers']),
                    ],
                    backgroundColor: ['#dc3545', '#007bff', '#28a745', '#ffc107'],
                    borderColor: ['#c82333', '#0056b3', '#1e7e34', '#e0a800'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "{{ __('Value (EGP)') }}",
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: "{{ __('Operation Type') }}",
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
                        text: "{{ __('Inventory Operations') }}",
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });
    </script>
@endsection
