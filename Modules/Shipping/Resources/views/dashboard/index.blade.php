@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Shipping Management Dashboard') }}</h1>
                <p class="text-muted mb-0">{{ __('Comprehensive overview of shipments, orders, drivers and companies') }}</p>
            </div>
        </div>

        <!-- Widget Stats -->
        @include('shipping::components.stats-widget', ['stats' => $widgetStats, 'topDrivers' => $topDrivers, 'recentShipments' => $recentShipments])

        <!-- Quick Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                    <i class="fas fa-shipping-fast text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">{{ __('Total Shipments') }}</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_shipments'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded p-3">
                                    <i class="fas fa-box text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">{{ __('Total Orders') }}</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_orders'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded p-3">
                                    <i class="fas fa-user-tie text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">{{ __('Total Drivers') }}</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_drivers'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded p-3">
                                    <i class="fas fa-building text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">{{ __('Total Companies') }}</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_companies'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipment Status Breakdown -->
        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Shipment Status Distribution') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($stats['shipment_status'] as $status => $data)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $data['label'] }}</span>
                                    <strong>{{ $data['count'] }} ({{ $data['percentage'] }}%)</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $data['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Order Delivery Status Distribution') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($stats['delivery_status'] as $status => $data)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $data['label'] }}</span>
                                    <strong>{{ $data['count'] }} ({{ $data['percentage'] }}%)</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $data['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver & Company Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Driver Statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <span class="badge bg-success">{{ __('Available') }}</span>
                                <span class="ms-2">{{ $stats['drivers']['available'] }}</span>
                            </li>
                            <li>
                                <span class="badge bg-secondary">{{ __('Busy') }}</span>
                                <span class="ms-2">{{ $stats['drivers']['busy'] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Company Statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <span class="badge bg-warning">{{ __('Active') }}</span>
                                <span class="ms-2">{{ $stats['companies']['active'] }}</span>
                            </li>
                            <li>
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                <span class="ms-2">{{ $stats['companies']['inactive'] }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Monthly Shipments Trend') }}</h5>
            </div>
            <div class="card-body">
                nvas id="monthlyTrenrendChart" height="120"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyTrendData = @json($stats['monthly_trend']);
            if (monthlyTrendData.labels.length > 0) {
                const ctx = document.getElementById('monthlyTrendChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthlyTrendData.labels,
                            datasets: [{
                                    label: '{{ __('Total Shipments') }}',
                                    data: monthlyTrendData.data.total,
                                    borderColor: 'rgb(75, 192, 192)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                    tension: 0.4
                                },
                                {
                                    label: '{{ __('Delivered') }}',
                                    data: monthlyTrendData.data.delivered,
                                    borderColor: 'rgb(40, 167, 69)',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
@endpush
