@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0"><i class="fas fa-certificate me-2"></i>تفاصيل الشهادة</h2>
                </div>
                <div>
                    <a href="{{ route('quality.certificates.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                    <a href="{{ route('quality.certificates.edit', $certificate) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>المعلومات الأساسية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">رقم الشهادة:</label>
                            <p class="mb-0">{{ $certificate->certificate_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">اسم الشهادة:</label>
                            <p class="mb-0">{{ $certificate->certificate_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نوع الشهادة:</label>
                            <p class="mb-0">
                                <span class="badge bg-info">شهادة</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">جهة الإصدار:</label>
                            <p class="mb-0">{{ $certificate->issuing_authority }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الإصدار:</label>
                            <p class="mb-0">{{ $certificate->issue_date ? $certificate->issue_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الانتهاء:</label>
                            <p class="mb-0">{{ $certificate->expiry_date ? $certificate->expiry_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        @if($certificate->scope)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">نطاق الشهادة:</label>
                            <p class="mb-0">{{ $certificate->scope }}</p>
                        </div>
                        @endif
                        @if($certificate->notes)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">ملاحظات:</label>
                            <p class="mb-0">{{ $certificate->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($certificate->certificate_cost || $certificate->renewal_cost)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>التكاليف</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($certificate->certificate_cost)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تكلفة الشهادة:</label>
                            <p class="mb-0">{{ number_format($certificate->certificate_cost, 2) }}</p>
                        </div>
                        @endif
                        @if($certificate->renewal_cost)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تكلفة التجديد:</label>
                            <p class="mb-0">{{ number_format($certificate->renewal_cost, 2) }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>الحالة</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-{{ match($certificate->status) {
                            'active' => 'success',
                            'expired' => 'danger',
                            'renewal_pending' => 'warning',
                            'suspended' => 'dark',
                            default => 'secondary'
                        } }} fs-6 px-3 py-2">
                            {{ match($certificate->status) {
                                'active' => 'نشط',
                                'expired' => 'منتهي',
                                'renewal_pending' => 'في انتظار التجديد',
                                'suspended' => 'معلق',
                                default => $certificate->status
                            } }}
                        </span>
                    </div>
                    @if($certificate->expiry_date)
                        @php
                            $daysLeft = $certificate->daysUntilExpiry();
                        @endphp
                        <div class="mb-3">
                            <h4 class="text-{{ $daysLeft < 0 ? 'danger' : ($daysLeft < 30 ? 'warning' : 'success') }}">
                                {{ abs($daysLeft) }}
                            </h4>
                            <small class="text-muted">{{ $daysLeft < 0 ? 'يوم منذ الانتهاء' : 'يوم متبقي' }}</small>
                        </div>
                        @if($daysLeft < 0)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>منتهية الصلاحية
                        </div>
                        @elseif($daysLeft < 30)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>تنتهي قريباً
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>التنبيهات</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">التنبيه قبل:</label>
                        <p class="mb-0">{{ $certificate->notification_days }} يوم</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تفعيل التنبيهات:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $certificate->notify_before_expiry ? 'success' : 'secondary' }}">
                                {{ $certificate->notify_before_expiry ? 'مفعل' : 'معطل' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>معلومات النظام</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ الإنشاء:</label>
                        <p class="mb-0">{{ $certificate->created_at ? $certificate->created_at->format('Y-m-d H:i') : '---' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">آخر تحديث:</label>
                        <p class="mb-0">{{ $certificate->updated_at ? $certificate->updated_at->format('Y-m-d H:i') : '---' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection