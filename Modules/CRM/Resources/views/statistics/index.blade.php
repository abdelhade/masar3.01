@extends('admin.dashboard')
@section('content')
    @push('styles')
        <style>
            .body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .stats-card {
                transition: transform 0.2s, box-shadow 0.2s;
                border: none;
                border-radius: 12px;
            }

            .stats-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .stats-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: white;
            }

            .chart-container {
                background: white;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .status-badge {
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: bold;
                text-align: center;
                margin: 2px;
                display: inline-block;
            }

            .bg-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .bg-gradient-success {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            }

            .bg-gradient-info {
                background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            }

            .bg-gradient-warning {
                background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            }

            .bg-gradient-danger {
                background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            }

            .progress-custom {
                height: 8px;
                border-radius: 10px;
            }

            .table-responsive {
                border-radius: 12px;
                overflow: hidden;
            }
        </style>
    @endpush
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h2 mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات نظام إدارة العلاقات
                    </h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> تحديث
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-gradient-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1">إجمالي العملاء</h6>
                                <h3 class="mb-0">{{ $statistics['clients']['total'] }}</h3>
                                <small class="text-success">
                                    +{{ $statistics['clients']['new_this_month'] }} هذا الشهر
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-gradient-success">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1">الفرص التجارية</h6>
                                <h3 class="mb-0">{{ $statistics['leads']['total'] }}</h3>
                                <small class="text-info">
                                    معدل النجاح: {{ $statistics['leads']['success_rate'] }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-gradient-info">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1">المهام</h6>
                                <h3 class="mb-0">{{ $statistics['tasks']['total'] }}</h3>
                                <small class="text-danger">
                                    {{ $statistics['tasks']['overdue'] }} متأخرة
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-gradient-warning">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="text-muted mb-1">قيمة الفرص</h6>
                                <h3 class="mb-0">{{ number_format($statistics['leads']['total_value']) }}</h3>
                                <small class="text-muted">
                                    متوسط: {{ number_format($statistics['leads']['average_value']) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Lead Status Breakdown -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            الفرص حسب الحالة
                        </h6>
                    </div>

                    <div class="card-body">
                        @foreach ($statistics['leads']['by_status'] as $status)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>{{ $status->name }}</span>
                                    <span class="badge bg-primary">{{ $status->leads_count }}</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar"
                                        style="width: {{ $statistics['leads']['total'] > 0 ? ($status->leads_count / $statistics['leads']['total']) * 100 : 0 }}%; background-color: {{ $status->color }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Task Priority Breakdown -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            المهام حسب الأولوية
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach ($statistics['tasks']['by_priority'] as $priority)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $priority['label'] }}</span>
                                    <span class="status-badge bg-{{ $priority['color'] }} text-white">
                                        {{ $priority['count'] }}
                                    </span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar bg-{{ $priority['color'] }}"
                                        style="width: {{ $priority['percentage'] }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $priority['percentage'] }}%</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Source Statistics -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-seedling me-2"></i>
                            مصادر الفرص
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach ($statistics['sources']['sources'] as $source)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>{{ $source['title'] }}</span>
                                    <span class="badge bg-success">{{ $source['count'] }}</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar bg-success" style="width: {{ $source['percentage'] }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $source['percentage'] }}%</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            إجراءات سريعة
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>{{ $statistics['tasks']['due_today'] }}</strong>
                                    مهام مستحقة اليوم
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>{{ $statistics['tasks']['overdue'] }}</strong>
                                    مهام متأخرة
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-user-check me-2"></i>
                                    <strong>{{ $statistics['clients']['active'] }}</strong>
                                    عملاء نشطون
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-address-book me-2"></i>
                                    <strong>{{ $statistics['contacts']['total'] }}</strong>
                                    جهات اتصال
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<!-- Scripts -->
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.3.0/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/4.3.0/chart.min.js"></script>

    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        // Refresh data function
        function refreshData() {
            const btn = document.querySelector('[onclick="refreshData()"]');
            const icon = btn.querySelector('i');

            btn.disabled = true;
            icon.classList.add('fa-spin');

            fetch(window.location.href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    alert('حدث خطأ أثناء تحديث البيانات');
                })
                .finally(() => {
                    btn.disabled = false;
                    icon.classList.remove('fa-spin');
                });
        }

        // Auto-refresh every 5 minutes
        setInterval(refreshData, 300000);

        // Add some interactive features
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('click', function() {
                const cardBody = this.querySelector('.card-body');
                cardBody.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    cardBody.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Tooltip for progress bars
        document.querySelectorAll('.progress-bar').forEach(bar => {
            bar.setAttribute('data-bs-toggle', 'tooltip');
            bar.setAttribute('data-bs-placement', 'top');
            const percentage = bar.style.width;
            bar.setAttribute('title', `النسبة: ${percentage}`);
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Print functionality
        function printReport() {
            window.print();
        }

        // Add print styles
        const printStyles = `
            @media print {
                .btn, .dropdown, [onclick] {
                    display: none !important;
                }
                .card {
                    break-inside: avoid;
                    margin-bottom: 20px;
                }
                .chart-container {
                    page-break-inside: avoid;
                }
            }
        `;

        const styleSheet = document.createElement("style");
        styleSheet.textContent = printStyles;
        document.head.appendChild(styleSheet);
    </script>
@endpush
