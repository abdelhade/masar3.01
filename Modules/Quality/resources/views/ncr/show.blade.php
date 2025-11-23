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
                    <h2 class="mb-1">{{ $ncr->ncr_number }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quality.ncr.index') }}">تقارير NCR</a></li>
                            <li class="breadcrumb-item active">{{ $ncr->ncr_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('quality.ncr.edit', $ncr) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('quality.ncr.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات التقرير</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">الصنف</label>
                            <div class="fw-bold">{{ $ncr->item?->name ?? 'غير محدد' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">المصدر</label>
                            <div class="fw-bold">
                                {{ match($ncr->source) {
                                    'receiving_inspection' => 'فحص استلام',
                                    'in_process' => 'أثناء الإنتاج',
                                    'final_inspection' => 'فحص نهائي',
                                    'customer_complaint' => 'شكوى عميل',
                                    'internal_audit' => 'تدقيق داخلي',
                                    'supplier_notification' => 'إشعار مورد',
                                    default => $ncr->source
                                } }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">تاريخ الاكتشاف</label>
                            <div class="fw-bold">{{ $ncr->detected_date?->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">الكمية المتأثرة</label>
                            <div class="fw-bold">{{ number_format($ncr->affected_quantity, 3) }}</div>
                        </div>
                        @if($ncr->batch_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">رقم الدفعة</label>
                            <div class="fw-bold">{{ $ncr->batch_number }}</div>
                        </div>
                        @endif
                        @if($ncr->inspection)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">الفحص المرتبط</label>
                            <div class="fw-bold">
                                <a href="{{ route('quality.inspections.show', $ncr->inspection) }}" class="text-decoration-none">
                                    {{ $ncr->inspection->inspection_number }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- الوصف والتفاصيل -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>الوصف والتفاصيل</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">وصف المشكلة</label>
                        <div class="alert alert-danger mb-0">{{ $ncr->problem_description }}</div>
                    </div>
                    @if($ncr->immediate_action)
                    <div class="mb-0">
                        <label class="text-muted small">الإجراء الفوري المتخذ</label>
                        <div class="alert alert-info mb-0">{{ $ncr->immediate_action }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- الحالة والإحصائيات -->
        <div class="col-lg-4">
            <!-- الحالة -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($ncr->status == 'closed')
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        @elseif($ncr->status == 'open')
                            <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                        @else
                            <i class="fas fa-clock text-warning" style="font-size: 3rem;"></i>
                        @endif
                    </div>
                    <h4 class="mb-2">
                        {{ match($ncr->status) {
                            'open' => 'مفتوح',
                            'in_progress' => 'قيد المعالجة',
                            'closed' => 'مغلق',
                            'cancelled' => 'ملغى',
                            default => $ncr->status
                        } }}
                    </h4>
                    <div class="text-muted">حالة التقرير</div>
                </div>
            </div>

            <!-- التصنيف -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">التصنيف</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>مستوى الخطورة</span>
                            <span class="badge bg-{{ $ncr->severity == 'critical' ? 'danger' : ($ncr->severity == 'major' ? 'warning' : 'info') }}">
                                {{ match($ncr->severity) {
                                    'critical' => 'حرج',
                                    'major' => 'رئيسي',
                                    'minor' => 'ثانوي',
                                    default => $ncr->severity
                                } }}
                            </span>
                        </div>
                        @if($ncr->disposition)
                        <div class="list-group-item d-flex justify-content-between">
                            <span>التصرف</span>
                            <strong>
                                {{ match($ncr->disposition) {
                                    'rework' => 'إعادة عمل',
                                    'scrap' => 'إتلاف',
                                    'return_to_supplier' => 'إرجاع للمورد',
                                    'use_as_is' => 'استخدام كما هو',
                                    'repair' => 'إصلاح',
                                    'downgrade' => 'تخفيض الدرجة',
                                    default => $ncr->disposition
                                } }}
                            </strong>
                        </div>
                        @endif
                        @if($ncr->estimated_cost)
                        <div class="list-group-item d-flex justify-content-between">
                            <span>التكلفة المقدرة</span>
                            <strong class="text-warning">{{ number_format($ncr->estimated_cost, 2) }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- المسؤولون -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">المسؤولون</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">تم الاكتشاف بواسطة</div>
                    <div class="mb-3">{{ $ncr->detectedBy?->name ?? 'غير محدد' }}</div>
                    
                    @if($ncr->assignedTo)
                    <div class="small text-muted mb-2">تم التعيين إلى</div>
                    <div class="mb-3">{{ $ncr->assignedTo->name }}</div>
                    @endif

                    @if($ncr->target_closure_date)
                    <div class="small text-muted mb-2">تاريخ الإغلاق المستهدف</div>
                    <div>{{ $ncr->target_closure_date->format('Y-m-d') }}</div>
                    @endif
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">معلومات إضافية</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">تاريخ الإنشاء</div>
                    <div class="mb-3">{{ $ncr->created_at?->format('Y-m-d H:i') }}</div>
                    
                    @if($ncr->updated_at != $ncr->created_at)
                    <div class="small text-muted mb-2">آخر تحديث</div>
                    <div>{{ $ncr->updated_at?->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection