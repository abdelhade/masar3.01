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
                    <h2 class="mb-0"><i class="fas fa-tools me-2"></i>تفاصيل الإجراء التصحيحي</h2>
                </div>
                <div>
                    <a href="{{ route('quality.capa.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                    <a href="{{ route('quality.capa.edit', $capa) }}" class="btn btn-warning">
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
                            <label class="form-label fw-bold">رقم CAPA:</label>
                            <p class="mb-0">{{ $capa->capa_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نوع الإجراء:</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $capa->action_type == 'corrective' ? 'warning' : 'info' }}">
                                    {{ $capa->action_type == 'corrective' ? 'تصحيحي' : 'وقائي' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تقرير عدم المطابقة:</label>
                            <p class="mb-0">{{ $capa->nonConformanceReport->ncr_number ?? '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الأولوية:</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ match($capa->priority) {
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    'low' => 'success',
                                    default => 'secondary'
                                } }}">
                                    {{ match($capa->priority) {
                                        'high' => 'عالية',
                                        'medium' => 'متوسطة',
                                        'low' => 'منخفضة',
                                        default => $capa->priority
                                    } }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">وصف المشكلة:</label>
                            <p class="mb-0">{{ $capa->problem_description }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">تحليل السبب الجذري:</label>
                            <p class="mb-0">{{ $capa->root_cause_analysis }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">الإجراء المقترح:</label>
                            <p class="mb-0">{{ $capa->proposed_action }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>التواريخ والتنفيذ</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ البدء المخطط:</label>
                            <p class="mb-0">{{ $capa->planned_start_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الإكمال المخطط:</label>
                            <p class="mb-0">{{ $capa->planned_completion_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ البدء الفعلي:</label>
                            <p class="mb-0">{{ $capa->actual_start_date ? $capa->actual_start_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الإكمال الفعلي:</label>
                            <p class="mb-0">{{ $capa->actual_completion_date ? $capa->actual_completion_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">نسبة الإنجاز:</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $capa->completion_percentage >= 100 ? 'success' : 'primary' }}" 
                                     style="width: {{ $capa->completion_percentage }}%">
                                    {{ $capa->completion_percentage }}%
                                </div>
                            </div>
                        </div>
                        @if($capa->implementation_details)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">تفاصيل التنفيذ:</label>
                            <p class="mb-0">{{ $capa->implementation_details }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($capa->verification_details || $capa->effectiveness_review)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>التحقق والمراجعة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($capa->verification_details)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">تفاصيل التحقق:</label>
                            <p class="mb-0">{{ $capa->verification_details }}</p>
                        </div>
                        @endif
                        @if($capa->effectiveness_review)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">مراجعة الفعالية:</label>
                            <p class="mb-0">{{ $capa->effectiveness_review }}</p>
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
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="badge bg-{{ match($capa->status) {
                            'completed' => 'success',
                            'in_progress' => 'warning',
                            'verified' => 'info',
                            default => 'secondary'
                        } }} fs-6 px-3 py-2">
                            {{ match($capa->status) {
                                'completed' => 'مكتمل',
                                'in_progress' => 'قيد التنفيذ',
                                'verified' => 'تم التحقق',
                                default => $capa->status
                            } }}
                        </span>
                    </div>
                    @if($capa->isOverdue())
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>متأخر
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>المسؤوليات</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">المسؤول عن التنفيذ:</label>
                        <p class="mb-0">{{ $capa->responsiblePerson->name ?? '---' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تم التحقق بواسطة:</label>
                        <p class="mb-0">{{ $capa->verifiedBy->name ?? '---' }}</p>
                    </div>
                    @if($capa->verification_date)
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ التحقق:</label>
                        <p class="mb-0">{{ $capa->verification_date->format('Y-m-d') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>معلومات النظام</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ الإنشاء:</label>
                        <p class="mb-0">{{ $capa->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">آخر تحديث:</label>
                        <p class="mb-0">{{ $capa->updated_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection