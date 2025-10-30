@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ __('Inquiries Dashboard') }}</h1>
                <p class="text-muted mb-0">{{ __('Comprehensive statistics and analysis for all inquiry details') }}</p>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-question-circle text-primary fa-2x"></i>
                        </div>
                        <p class="text-muted mb-1">{{ __('Total Inquiries') }}</p>
                        <h3 class="mb-0">{{ $stats['overview']['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-briefcase text-success fa-2x"></i>
                        </div>
                        <p class="text-muted mb-1">{{ __('In Progress') }}</p>
                        <h3 class="mb-0">{{ $stats['overview']['active'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-file-signature text-warning fa-2x"></i>
                        </div>
                        <p class="text-muted mb-1">{{ __('Tenders') }}</p>
                        <h3 class="mb-0">{{ $stats['overview']['tender'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-comments text-info fa-2x"></i>
                        </div>
                        <p class="text-muted mb-1">{{ __('With Comments') }}</p>
                        <h3 class="mb-0">{{ $stats['overview']['with_comments'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-folder-open text-secondary fa-2x"></i>
                        </div>
                        <p class="text-muted mb-1">{{ __('With Documents') }}</p>
                        <h3 class="mb-0">{{ $stats['overview']['with_documents'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <i class="fas fa-layer-group text-dark fa-2x"></i>
                        </div>
                        <p class="text-muted mb-1">{{ __('With Work Types') }}</p>
                        <h3 class="mb-0">{{ $stats['overview']['with_work_types'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section: 4 charts in one row -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Status Distribution (Pie)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusPieChart" height="180"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Quotation States (Pie)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="quotationPieChart" height="180"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Most Frequent Work Types (Bar)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="workTypeBarChart" height="180"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Inquiry Sources (Bar)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="sourceBarChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Types & Sources & Sizes -->
        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Most Frequent Work Types') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($stats['work_types'] as $type)
                                <li class="mb-2">
                                    <span class="badge bg-info">{{ $type['name'] }}</span>
                                    <span class="ms-2">{{ $type['count'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Inquiry Sources') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($stats['sources'] as $src)
                                <li class="mb-2">
                                    <span class="badge bg-secondary">{{ $src['name'] }}</span>
                                    <span class="ms-2">{{ $src['count'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">{{ __('Project Sizes') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($stats['sizes'] as $sz)
                                <li class="mb-2">
                                    <span class="badge bg-dark">{{ $sz['name'] }}</span>
                                    <span class="ms-2">{{ $sz['count'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">{{ __('Monthly Inquiries Trend') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="120"></canvas>
            </div>
        </div>

        <!-- Recent Inquiries Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Recent Inquiries') }}</h5>
                <a href="{{ route('inquiries.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('View All') }} <i class="fas fa-arrow-left ms-2"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Work Types') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Size') }}</th>
                                <th>{{ __('Created At') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recent_inquiries'] as $inq)
                                <tr>
                                    <td>{{ $inq['id'] }}</td>
                                    <td>{{ $inq['client'] }}</td>
                                    <td>{{ $inq['status'] }}</td>
                                    <td>{{ $inq['work_types'] }}</td>
                                    <td>{{ $inq['source'] }}</td>
                                    <td>{{ $inq['size'] }}</td>
                                    <td>{{ $inq['created_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ __('No recent inquiries') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pie Chart for Status
            const statusData = @json(array_values($stats['status_breakdown']));
            const statusLabels = statusData.map(s => s.label);
            const statusCounts = statusData.map(s => s.count);
            const statusColors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8'];
            new Chart(document.getElementById('statusPieChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: statusColors,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Pie Chart for Quotation States
            const quotationData = @json(array_values($stats['quotation_states']));
            const quotationLabels = quotationData.map(q => q.label);
            const quotationCounts = quotationData.map(q => q.count);
            const quotationColors = quotationData.map(q => {
                switch (q.color) {
                    case 'success':
                        return '#28a745';
                    case 'danger':
                        return '#dc3545';
                    case 'warning':
                        return '#ffc107';
                    case 'secondary':
                        return '#6c757d';
                    case 'info':
                        return '#17a2b8';
                    default:
                        return '#007bff';
                }
            });
            new Chart(document.getElementById('quotationPieChart'), {
                type: 'doughnut',
                data: {
                    labels: quotationLabels,
                    datasets: [{
                        data: quotationCounts,
                        backgroundColor: quotationColors,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Bar Chart for Work Types
            const workTypeData = @json($stats['work_types']);
            const workTypeLabels = workTypeData.map(w => w.name);
            const workTypeCounts = workTypeData.map(w => w.count);
            new Chart(document.getElementById('workTypeBarChart'), {
                type: 'bar',
                data: {
                    labels: workTypeLabels,
                    datasets: [{
                        label: '{{ __('Number of Inquiries') }}',
                        data: workTypeCounts,
                        backgroundColor: '#007bff',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Bar Chart for Sources
            const sourceData = @json($stats['sources']);
            const sourceLabels = sourceData.map(s => s.name);
            const sourceCounts = sourceData.map(s => s.count);
            new Chart(document.getElementById('sourceBarChart'), {
                type: 'bar',
                data: {
                    labels: sourceLabels,
                    datasets: [{
                        label: '{{ __('Number of Inquiries') }}',
                        data: sourceCounts,
                        backgroundColor: '#17a2b8',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Monthly Trend Chart (إذا كان موجودًا)
            // ...
        });
    </script>
@endpush
