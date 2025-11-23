@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $inspection->inspection_number }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quality.inspections.index') }}">الفحوصات</a></li>
                            <li class="breadcrumb-item active">{{ $inspection->inspection_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('quality.inspections.edit', $inspection) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('quality.inspections.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- معلومات أساسية -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات الفحص</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">الصنف</label>
                            <div class="fw-bold">{{ $inspection->item?->name ?? 'غير محدد' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">نوع الفحص</label>
                            <div class="fw-bold">
                                {{ match($inspection->inspection_type) {
                                    'receiving' => 'فحص استلام مواد خام',
                                    'in_process' => 'فحص أثناء الإنتاج',
                                    'final' => 'فحص نهائي',
                                    'random' => 'فحص عشوائي',
                                    'customer_complaint' => 'فحص شكوى عميل',
                                    default => $inspection->inspection_type
                                } }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">تاريخ الفحص</label>
                            <div class="fw-bold">{{ $inspection->inspection_date?->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">المفتش</label>
                            <div class="fw-bold">{{ $inspection->inspector?->name ?? 'غير محدد' }}</div>
                        </div>
                        @if($inspection->supplier)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">المورد</label>
                            <div class="fw-bold">{{ $inspection->supplier->aname }}</div>
                        </div>
                        @endif
                        @if($inspection->batch_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">رقم الدفعة</label>
                            <div class="fw-bold">{{ $inspection->batch_number }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- العيوب والملاحظات -->
            @if($inspection->defects_found || $inspection->inspector_notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>العيوب والملاحظات</h5>
                </div>
                <div class="card-body">
                    @if($inspection->defects_found)
                    <div class="mb-3">
                        <label class="text-muted small">العيوب المكتشفة</label>
                        <div class="alert alert-warning mb-0">{{ $inspection->defects_found }}</div>
                    </div>
                    @endif
                    @if($inspection->inspector_notes)
                    <div class="mb-0">
                        <label class="text-muted small">ملاحظات المفتش</label>
                        <div class="alert alert-info mb-0">{{ $inspection->inspector_notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- النتائج والإحصائيات -->
        <div class="col-lg-4">
            <!-- النتيجة النهائية -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($inspection->result == 'pass')
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        @elseif($inspection->result == 'fail')
                            <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                        @else
                            <i class="fas fa-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                        @endif
                    </div>
                    <h4 class="mb-2">
                        {{ match($inspection->result) {
                            'pass' => 'نجح',
                            'fail' => 'فشل',
                            'conditional' => 'مشروط',
                            default => $inspection->result
                        } }}
                    </h4>
                    <div class="text-muted">النتيجة النهائية</div>
                </div>
            </div>

            <!-- إحصائيات الكميات -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">إحصائيات الكميات</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>الكمية المفحوصة</span>
                            <strong>{{ number_format($inspection->quantity_inspected, 2) }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between text-success">
                            <span>كمية النجاح</span>
                            <strong>{{ number_format($inspection->pass_quantity, 2) }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between text-danger">
                            <span>كمية الفشل</span>
                            <strong>{{ number_format($inspection->fail_quantity, 2) }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between bg-light">
                            <span class="fw-bold">نسبة النجاح</span>
                            <strong class="{{ $inspection->pass_percentage >= 95 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($inspection->pass_percentage, 1) }}%
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الإجراء المتخذ -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">الإجراء المتخذ</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <span class="badge bg-{{ match($inspection->action_taken) {
                            'accepted' => 'success',
                            'rejected' => 'danger',
                            'rework' => 'warning',
                            'conditional_accept' => 'info',
                            default => 'secondary'
                        } }} fs-6 px-3 py-2">
                            {{ match($inspection->action_taken) {
                                'accepted' => 'مقبول',
                                'rejected' => 'مرفوض',
                                'rework' => 'إعادة عمل',
                                'conditional_accept' => 'قبول مشروط',
                                'pending_review' => 'انتظار مراجعة',
                                default => $inspection->action_taken
                            } }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">معلومات إضافية</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">تاريخ الإنشاء</div>
                    <div class="mb-3">{{ $inspection->created_at?->format('Y-m-d H:i') }}</div>
                    
                    @if($inspection->updated_at != $inspection->created_at)
                    <div class="small text-muted mb-2">آخر تحديث</div>
                    <div class="mb-3">{{ $inspection->updated_at?->format('Y-m-d H:i') }}</div>
                    @endif

                    <div class="small text-muted mb-2">الحالة</div>
                    <span class="badge bg-{{ $inspection->status == 'completed' ? 'success' : 'warning' }}">
                        {{ match($inspection->status) {
                            'pending' => 'قيد الانتظار',
                            'in_progress' => 'قيد التنفيذ',
                            'completed' => 'مكتمل',
                            default => $inspection->status
                        } }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection