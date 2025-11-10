@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h2 class="mb-0">
                        <i class="fas fa-award me-2"></i>
                        نظام إدارة الجودة (QMS)
                    </h2>
                    <p class="mb-0 mt-2">لوحة تحكم شاملة لمتابعة جميع عمليات الجودة والفحص</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Inspections -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">إجمالي الفحوصات</h6>
                            <h3 class="mb-0">{{ $totalInspections }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> نسبة النجاح: {{ number_format($passRate, 1) }}%
                            </small>
                        </div>
                        <div class="text-primary" style="font-size: 3rem;">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NCRs -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">تقارير عدم المطابقة</h6>
                            <h3 class="mb-0">{{ $totalNCRs }}</h3>
                            <small class="text-danger">
                                <i class="fas fa-exclamation-circle"></i> مفتوحة: {{ $openNCRs }}
                            </small>
                        </div>
                        <div class="text-danger" style="font-size: 3rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CAPA -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">إجراءات تصحيحية</h6>
                            <h3 class="mb-0">{{ $activeCapas }}</h3>
                            <small class="text-warning">
                                <i class="fas fa-clock"></i> متأخرة: {{ $overdueCapas }}
                            </small>
                        </div>
                        <div class="text-warning" style="font-size: 3rem;">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batches -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">الدفعات النشطة</h6>
                            <h3 class="mb-0">{{ $activeBatches }}</h3>
                            <small class="text-info">
                                <i class="fas fa-hourglass-half"></i> تنتهي قريباً: {{ $expiringSoonBatches }}
                            </small>
                        </div>
                        <div class="text-info" style="font-size: 3rem;">
                            <i class="fas fa-barcode"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="row">
        <!-- Recent Inspections -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر الفحوصات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الفحص</th>
                                    <th>الصنف</th>
                                    <th>النتيجة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInspections as $inspection)
                                <tr>
                                    <td>
                                        <a href="{{ route('quality.inspections.show', $inspection) }}">
                                            {{ $inspection->inspection_number }}
                                        </a>
                                    </td>
                                    <td>{{ $inspection->item->name ?? '---' }}</td>
                                    <td>
                                        @if($inspection->result == 'pass')
                                            <span class="badge bg-success">نجح</span>
                                        @elseif($inspection->result == 'fail')
                                            <span class="badge bg-danger">فشل</span>
                                        @else
                                            <span class="badge bg-warning">مشروط</span>
                                        @endif
                                    </td>
                                    <td>{{ $inspection->inspection_date->format('Y-m-d') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">لا توجد فحوصات</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent NCRs -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        آخر تقارير عدم المطابقة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم NCR</th>
                                    <th>الصنف</th>
                                    <th>الخطورة</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentNCRs as $ncr)
                                <tr>
                                    <td>{{ $ncr->ncr_number }}</td>
                                    <td>{{ $ncr->item->name ?? '---' }}</td>
                                    <td>
                                        @if($ncr->severity == 'critical')
                                            <span class="badge bg-danger">حرج</span>
                                        @elseif($ncr->severity == 'major')
                                            <span class="badge bg-warning">رئيسي</span>
                                        @else
                                            <span class="badge bg-info">ثانوي</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ncr->status == 'open' ? 'danger' : 'success' }}">
                                            {{ $ncr->status == 'open' ? 'مفتوح' : 'مغلق' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">لا توجد تقارير</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('quality.inspections.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle me-2"></i>
                                فحص جديد
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ url('/quality/ncrs/create') }}" class="btn btn-danger w-100">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                تقرير NCR جديد
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ url('/quality/batches') }}" class="btn btn-info w-100">
                                <i class="fas fa-barcode me-2"></i>
                                إدارة الدفعات
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('quality.reports') }}" class="btn btn-success w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                عرض التقارير
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

