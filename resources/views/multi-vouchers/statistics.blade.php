@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">{{ __('Employee Salaries Statistics') }} 📊</h2>
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
                                    {{ __('Total Employee Salaries') }}
                                </h6>
                                <h2 class="font-hold fw-bold mb-0 text-primary">
                                    {{ number_format($overallTotal->overall_value, 2) }}
                                </h2>
                                <small class="text-muted font-hold">
                                    {{ number_format($overallTotal->overall_count) }} {{ __('Voucher') }}
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
                                            {{ number_format($stats['count']) }} {{ __('Voucher') }}
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

        <!-- الشارتس -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <h3 class="mb-3">{{ __('Salary Distribution by Voucher Type') }}</h3>
                <canvas id="typePieChart" height="150"></canvas>
            </div>
            <div class="col-lg-6 mb-4">
                <h3 class="mb-3">{{ __('Salary Distribution by Employees') }}</h3>
                <canvas id="employeeBarChart" height="150"></canvas>
            </div>
        </div>

        <!-- تفاصيل الإحصائيات حسب نوع السند -->
        <h3 class="mt-5">{{ __('Statistics by Voucher Type') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Voucher Type') }}</th>
                        <th>{{ __('Number of Vouchers') }}</th>
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

        <!-- إحصائيات حسب الموظفين -->
        <h3 class="mt-5">{{ __('Statistics by Employees') }}</h3>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Employee Name') }}</th>
                        <th>{{ __('Number of Vouchers') }}</th>
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

        <!-- إحصائيات حسب مراكز التكلفة -->
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

    <!-- تضمين Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // مخطط دائري: توزيع الرواتب حسب نوع السند
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
                                'rgba({{ $stats['color'] == 'success'
                                    ? '40, 167, 69'
                                    : ($stats['color'] == 'danger'
                                        ? '220, 53, 69'
                                        : ($stats['color'] == 'warning'
                                            ? '255, 193, 7'
                                            : ($stats['color'] == 'info'
                                                ? '23, 162, 184'
                                                : ($stats['color'] == 'primary'
                                                    ? '0, 123, 255'
                                                    : '108, 117, 125')))) }}, 0.5)',
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
                        text: '{{ __('Salary Distribution by Voucher Type') }}'
                    }
                }
            }
        });

        // مخطط أعمدة: توزيع الرواتب حسب الموظفين
        const employeeBarChart = new Chart(document.getElementById('employeeBarChart'), {
            type: 'bar',
            data: {
                labels: [
                    @foreach ($employeeStats as $stat)
                        '{{ $loop->index + 1 }}: {{ $stat->employee_name }}',
                    @endforeach
                ],
                datasets: [{
                    label: '{{ __('Total Value') }}',
                    data: [
                        @foreach ($employeeStats as $stat)
                            {{ $stat->value }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    borderColor: 'rgba(0, 123, 255, 1)',
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
                            text: '{{ __('Value') }}'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: '{{ __('Employee') }}'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: '{{ __('Salary Distribution by Employees') }}'
                    }
                }
            }
        });
    </script>
@endsection
