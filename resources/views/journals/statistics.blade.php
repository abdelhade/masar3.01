@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.journals')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">{{ __('Journal Entries Statistics') }} 📊</h2>
            </div>
        </div>

        <!-- الكروت -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted font-hold fw-bold mb-2">
                                    {{ __('Total Journal Entries') }}
                                </h6>
                                <h2 class="font-hold fw-bold mb-0 text-primary">
                                    {{ number_format($overallTotal->overall_value, 2) }}
                                </h2>
                                <small class="text-muted font-hold">
                                    {{ number_format($overallTotal->overall_count) }} {{ __('Entry') }}
                                </small>
                            </div>
                            <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                                <i class="las la-chart-pie"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($sortedStatistics as $typeId => $stats)
                @if ($stats)
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card shadow-sm h-100 border-start border-{{ $stats['color'] }} border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted font-hold fw-bold mb-2">
                                            {{ $stats['title'] }}
                                        </h6>
                                        <h2 class="font-hold fw-bold mb-0 text-{{ $stats['color'] }}">
                                            {{ number_format($stats['value'], 2) }}
                                        </h2>
                                        <small class="text-muted font-hold">
                                            {{ number_format($stats['count']) }} {{ __('Entry') }}
                                        </small>
                                    </div>
                                    <div class="text-{{ $stats['color'] }}" style="font-size: 3rem; opacity: 0.3;">
                                        <i class="las {{ $stats['icon'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Charts -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <h3 class="mb-3">{{ __('Distribution of Entries by Type') }}</h3>
                <canvas id="typePieChart" height="150"></canvas>
            </div>
            <div class="col-lg-6 mb-4">
                <h3 class="mb-3">{{ __('Distribution of Values by Accounts') }}</h3>
                <canvas id="accountBarChart" height="150"></canvas>
            </div>
        </div>

        <!-- Statistics by Entry Type -->
        <h3 class="mt-5">{{ __('Statistics by Entry Type') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Entry Type') }}</th>
                        <th>{{ __('Entries Count') }}</th>
                        <th>{{ __('Total Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sortedStatistics as $typeId => $stats)
                        @if ($stats)
                            <tr>
                                <td>{{ $typeId }}</td>
                                <td>{{ $stats['title'] }}</td>
                                <td>{{ number_format($stats['count']) }}</td>
                                <td>{{ number_format($stats['value'], 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="2" class="text-right">{{ __('Grand Total') }}:</td>
                        <td>{{ number_format($overallTotal->overall_count) }}</td>
                        <td>{{ number_format($overallTotal->overall_value, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Statistics by Accounts -->
        <h3 class="mt-5">{{ __('Statistics by Accounts') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Account Name') }}</th>
                        <th>{{ __('Total Debit') }}</th>
                        <th>{{ __('Total Credit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accountStats as $stat)
                        <tr>
                            <td>{{ $stat->id }}</td>
                            <td>{{ $stat->account_name }}</td>
                            <td>{{ number_format($stat->debit_total, 2) }}</td>
                            <td>{{ number_format($stat->credit_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Statistics by Employees -->
        <h3 class="mt-5">{{ __('Statistics by Employees') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Employee Name') }}</th>
                        <th>{{ __('Entries Count') }}</th>
                        <th>{{ __('Total Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employeeStats as $stat)
                        <tr>
                            <td>{{ $stat->id }}</td>
                            <td>{{ $stat->employee_name }}</td>
                            <td>{{ number_format($stat->count) }}</td>
                            <td>{{ number_format($stat->value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Statistics by Cost Centers -->
        <h3 class="mt-5">{{ __('Statistics by Cost Centers') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Cost Center Name') }}</th>
                        <th>{{ __('Total Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($costCenterStats as $stat)
                        <tr>
                            <td>{{ $stat->id }}</td>
                            <td>{{ $stat->cost_center_name }}</td>
                            <td>{{ number_format($stat->value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Pie Chart: Distribution of Entries by Type
        const typePieChart = new Chart(document.getElementById('typePieChart'), {
            type: 'pie',
            data: {
                labels: [
                    @foreach ($sortedStatistics as $stats)
                        @if ($stats)
                            '{{ $stats['title'] }}',
                        @endif
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach ($sortedStatistics as $stats)
                            @if ($stats)
                                {{ $stats['value'] }},
                            @endif
                        @endforeach
                    ],
                    backgroundColor: [
                        @foreach ($sortedStatistics as $stats)
                            @if ($stats)
                                'rgba({{ $stats['color'] == 'success' ? '40, 167, 69' : ($stats['color'] == 'danger' ? '220, 53, 69' : '0, 123, 255') }}, 0.5)',
                            @endif
                        @endforeach
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: '{{ __('Distribution of Entries by Type') }}'
                    }
                }
            }
        });

        // Bar Chart: Distribution of Values by Accounts
        const accountBarChart = new Chart(document.getElementById('accountBarChart'), {
            type: 'bar',
            data: {
                labels: [
                    @foreach ($accountStats as $stat)
                        '{{ $loop->index + 1 }}: {{ $stat->account_name }}',
                    @endforeach
                ],
                datasets: [{
                        label: '{{ __('Total Debit') }}',
                        data: [
                            @foreach ($accountStats as $stat)
                                {{ $stat->debit_total }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: '{{ __('Total Credit') }}',
                        data: [
                            @foreach ($accountStats as $stat)
                                {{ $stat->credit_total }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgba(220, 53, 69, 1)',
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
                            text: '{{ __('Value') }}'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: '{{ __('Account') }}'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    },
                    title: {
                        display: true,
                        text: '{{ __('Distribution of Values by Accounts') }}'
                    }
                }
            }
        });
    </script>
@endsection
