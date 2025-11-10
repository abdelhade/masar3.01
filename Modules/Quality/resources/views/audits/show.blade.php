@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        تفاصيل التدقيق: {{ $audit->audit_title }}
                    </h4>
                    <div>
                        <a href="{{ route('quality.audits.edit', $audit) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('quality.audits.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>رقم التدقيق:</strong>
                            <p>{{ $audit->audit_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>نوع التدقيق:</strong>
                            <p>
                                @switch($audit->audit_type)
                                    @case('internal') داخلي @break
                                    @case('external') خارجي @break
                                    @case('supplier') تدقيق موردين @break
                                    @case('certification') شهادات @break
                                    @case('customer') عملاء @break
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>الحالة:</strong>
                            <p>
                                <span class="badge bg-{{ $audit->status == 'completed' ? 'success' : ($audit->status == 'in_progress' ? 'warning' : 'info') }}">
                                    @switch($audit->status)
                                        @case('planned') مخطط @break
                                        @case('in_progress') قيد التنفيذ @break
                                        @case('completed') مكتمل @break
                                        @case('cancelled') ملغى @break
                                    @endswitch
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>التاريخ المخطط:</strong>
                            <p>{{ $audit->planned_date?->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>المدقق الرئيسي:</strong>
                            <p>{{ $audit->leadAuditor?->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>التاريخ الفعلي:</strong>
                            <p>{{ $audit->actual_date?->format('Y-m-d') ?? 'لم يتم بعد' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>نطاق التدقيق:</strong>
                            <p>{{ $audit->audit_scope }}</p>
                        </div>
                        @if($audit->audit_objectives)
                        <div class="col-md-12 mb-3">
                            <strong>أهداف التدقيق:</strong>
                            <p>{{ $audit->audit_objectives }}</p>
                        </div>
                        @endif
                        @if($audit->findings)
                        <div class="col-md-12 mb-3">
                            <strong>النتائج:</strong>
                            <p>{{ $audit->findings }}</p>
                        </div>
                        @endif
                        @if($audit->summary)
                        <div class="col-md-12 mb-3">
                            <strong>الملخص:</strong>
                            <p>{{ $audit->summary }}</p>
                        </div>
                        @endif
                        
                        <!-- إحصائيات النتائج -->
                        @if($audit->status == 'completed')
                        <div class="col-12">
                            <h5 class="mb-3">إحصائيات النتائج</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $audit->total_findings ?? 0 }}</h3>
                                            <p class="mb-0">إجمالي النتائج</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $audit->critical_findings ?? 0 }}</h3>
                                            <p class="mb-0">نتائج حرجة</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $audit->major_findings ?? 0 }}</h3>
                                            <p class="mb-0">نتائج رئيسية</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $audit->minor_findings ?? 0 }}</h3>
                                            <p class="mb-0">نتائج ثانوية</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

