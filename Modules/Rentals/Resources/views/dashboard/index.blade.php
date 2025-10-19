@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Quick Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                    <i class="fas fa-building text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">إجمالي المباني</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_buildings'] }}</h3>
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
                                    <i class="fas fa-door-open text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">إجمالي الوحدات</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_units'] }}</h3>
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
                                    <i class="fas fa-file-contract text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">العقود النشطة</p>
                                <h3 class="mb-0">{{ $stats['overview']['active_leases'] }}</h3>
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
                                    <i class="fas fa-users text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">إجمالي العملاء</p>
                                <h3 class="mb-0">{{ $stats['overview']['total_clients'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Overview -->
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">الإيرادات المالية</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="border-end">
                                    <p class="text-muted mb-2">الإيراد الشهري</p>
                                    <h4 class="text-success mb-0">
                                        {{ number_format($stats['financial']['total_monthly_revenue'], 2) }} ج.م</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border-end">
                                    <p class="text-muted mb-2">الإيراد السنوي المتوقع</p>
                                    <h4 class="text-primary mb-0">
                                        {{ number_format($stats['financial']['total_yearly_revenue'], 2) }} ج.م</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-2">متوسط الإيجار</p>
                                <h4 class="text-info mb-0">{{ number_format($stats['financial']['average_rent'], 2) }} ج.م
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buildings Stats -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">إحصائيات المباني</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>اسم المبنى</th>
                                        <th>العنوان</th>
                                        <th class="text-center">الطوابق</th>
                                        <th class="text-center">إجمالي الوحدات</th>
                                        <th class="text-center">مؤجرة</th>
                                        <th class="text-center">متاحة</th>
                                        <th class="text-center">معدل الإشغال</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['buildings'] as $building)
                                        <tr>
                                            <td><strong>{{ $building->name }}</strong></td>
                                            <td>{{ $building->address }}</td>
                                            <td class="text-center">{{ $building->floors }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $building->total_units }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ $building->rented_units }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $building->available_units }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 20px; min-width: 100px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $building->occupancy_rate }}%">
                                                        {{ $building->occupancy_rate }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                لا توجد مباني مسجلة
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leases & Lease Stats -->
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">أحدث العقود</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>العميل</th>
                                        <th>المبنى</th>
                                        <th>الوحدة</th>
                                        <th>قيمة الإيجار</th>
                                        <th>تاريخ البدء</th>
                                        <th>تاريخ الانتهاء</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['leases']['recent_leases'] as $lease)
                                        <tr>
                                            <td>{{ $lease['client_name'] }}</td>
                                            <td>{{ $lease['building_name'] }}</td>
                                            <td>{{ $lease['unit_name'] }}</td>
                                            <td><strong>{{ number_format($lease['rent_amount'], 2) }} ج.م</strong></td>
                                            <td>{{ $lease['start_date'] }}</td>
                                            <td>{{ $lease['end_date'] }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $lease['status'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                لا توجد عقود مسجلة
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">ملخص العقود</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">عقود نشطة</span>
                                <h4 class="mb-0 text-success">{{ $stats['leases']['active'] }}</h4>
                            </div>
                            <hr>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">عقود منتهية</span>
                                <h4 class="mb-0 text-danger">{{ $stats['leases']['expired'] }}</h4>
                            </div>
                            <hr>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">عقود قادمة</span>
                                <h4 class="mb-0 text-info">{{ $stats['leases']['upcoming'] }}</h4>
                            </div>
                            <hr>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ $stats['leases']['expiring_soon'] }}</strong> عقد تنتهي خلال 30 يوم
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // يمكن إضافة JavaScript للرسوم البيانية هنا باستخدام Chart.js
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard loaded successfully');
        });
    </script>
@endpush
