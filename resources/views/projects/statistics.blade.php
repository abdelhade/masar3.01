@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.projects')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4 text-dark fw-bolder border-bottom pb-2">
                    {{ __('Projects Statistics Dashboard') }}
                    <i class="las la-tachometer-alt"></i>
                </h2>
            </div>
        </div>

        <!-- 1. KPIs Cards -->
        <div class="row g-4 mb-5">
            <!-- Total Projects -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow border-end border-5 border-primary h-100 p-2 transform-hover">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-primary fw-bold mb-1">{{ __('Total Projects in System') }}</p>
                                <h1 class="display-5 fw-bolder mb-0 text-dark">
                                    {{ number_format($overallTotal) }}
                                </h1>
                                <small class="text-muted">{{ __('Total Projects') }}</small>
                            </div>
                            <div class="text-primary bg-primary-subtle rounded-circle p-3 opacity-50">
                                <i class="las la-layer-group" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($sortedStatistics as $status => $stats)
                @if ($stats)
                    @php
                        $colorClass = $stats['color'] ?? 'secondary';
                        $iconClass = $stats['icon'] ?? 'la-question-circle';
                        $title = $stats['title'] ?? __('Unspecified');
                        $count = number_format($stats['count'] ?? 0);
                        $avgDuration = $stats['avg_duration'] ?? '0';
                    @endphp
                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow border-end border-5 border-{{ $colorClass }} h-100 p-2 transform-hover">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-{{ $colorClass }} fw-bold mb-1">{{ $title }}</p>
                                        <h2 class="display-6 fw-bolder mb-0 text-dark">{{ $count }}</h2>
                                        <small class="text-muted">
                                            {{ __('Average Duration') }}: {{ $avgDuration }} {{ __('days') }}
                                        </small>
                                    </div>
                                    <div
                                        class="text-{{ $colorClass }} bg-{{ $colorClass }}-subtle rounded-circle p-3 opacity-50">
                                        <i class="las {{ $iconClass }}" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- 2. Charts Section -->
        <div class="row g-4 mb-5">
            <!-- Pie Chart (Projects Distribution) -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="mb-4 text-center fw-bold text-dark border-bottom pb-2">
                            {{ __('Projects Distribution by Status (%)') }}
                        </h5>
                        <div class="chart-container flex-grow-1" style="max-height: 400px; padding: 20px;">
                            <canvas id="statusPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart (Average Duration) -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="mb-4 text-center fw-bold text-dark border-bottom pb-2">
                            {{ __('Average Completion Duration by Status (Days)') }}
                        </h5>
                        <div class="chart-container flex-grow-1" style="max-height: 400px; padding: 20px;">
                            <canvas id="durationBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Statistics Table -->
        <h3 class="mt-4 mb-3 fw-bold text-dark">{{ __('Projects Status Details') }}</h3>
        <div class="table-responsive mb-5 rounded shadow-sm">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th class="text-center">{{ __('Status') }}</th>
                        <th class="text-center">{{ __('Projects Count') }}</th>
                        <th class="text-center">{{ __('Average Expected Duration (Days)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sortedStatistics as $status => $stats)
                        @if ($stats)
                            <tr>
                                <td class="text-center fw-bold">
                                    <span class="badge bg-{{ $stats['color'] }} p-2">{{ $stats['title'] }}</span>
                                </td>
                                <td class="text-center">{{ number_format($stats['count']) }}</td>
                                <td class="text-center">{{ $stats['avg_duration'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td class="text-center">{{ __('Grand Total') }}:</td>
                        <td class="text-center">{{ number_format($overallTotal) }}</td>
                        <td class="text-center">-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            function getColorConfig() {
                const statusMap = {
                    'success': {
                        rgb: '40, 167, 69',
                        hex: '#28a745'
                    },
                    'danger': {
                        rgb: '220, 53, 69',
                        hex: '#dc3545'
                    },
                    'warning': {
                        rgb: '255, 193, 7',
                        hex: '#ffc107'
                    },
                    'primary': {
                        rgb: '0, 123, 255',
                        hex: '#007bff'
                    },
                    'secondary': {
                        rgb: '108, 117, 125',
                        hex: '#6c757d'
                    }
                };

                const stats = {!! json_encode(array_values($sortedStatistics)) !!}.filter(s => s !== null);
                const labels = stats.map(s => s.title);
                const counts = stats.map(s => s.count);
                const durations = stats.map(s => parseFloat(s.avg_duration));
                const colors = stats.map(s => statusMap[s.color] || statusMap['secondary']);

                return {
                    labels,
                    counts,
                    durations,
                    colors
                };
            }

            window.onload = function() {
                const config = getColorConfig();

                // Pie Chart
                const pieCtx = document.getElementById('statusPieChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: config.labels,
                        datasets: [{
                            data: config.counts,
                            backgroundColor: config.colors.map(c => `rgba(${c.rgb}, 0.7)`),
                            borderColor: config.colors.map(c => c.hex),
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        size: 14,
                                        family: 'Cairo, sans-serif'
                                    }
                                }
                            }
                        }
                    }
                });

                // Bar Chart
                const barCtx = document.getElementById('durationBarChart').getContext('2d');
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: config.labels,
                        datasets: [{
                            label: '{{ __('Average Duration (days)') }}',
                            data: config.durations,
                            backgroundColor: config.colors.map(c => `rgba(${c.rgb}, 0.8)`),
                            borderColor: config.colors.map(c => c.hex),
                            borderWidth: 1.5,
                            borderRadius: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: '{{ __('Days') }}',
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 14
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                titleFont: {
                                    family: 'Cairo'
                                },
                                bodyFont: {
                                    family: 'Cairo'
                                }
                            }
                        }
                    }
                });
            };
        </script>
    @endpush
    @push('styles')
        <style>
            .transform-hover:hover {
                transform: translateY(-5px);
                transition: all 0.3s ease;
            }

            .bg-primary-subtle {
                background-color: #cfe2ff !important;
            }

            .bg-success-subtle {
                background-color: #d1e7dd !important;
            }

            .bg-danger-subtle {
                background-color: #f8d7da !important;
            }

            .bg-warning-subtle {
                background-color: #fff3cd !important;
            }
        </style>
    @endpush
@endsection
