@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تقرير عدم المطابقة: {{ $ncr->ncr_number }}
                    </h4>
                    <div>
                        <a href="{{ route('quality.ncr.edit', $ncr) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('quality.ncr.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> رجوع
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- معلومات أساسية -->
                        <div class="col-12 mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>معلومات أساسية
                            </h5>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <strong>رقم التقرير:</strong>
                            <p>{{ $ncr->ncr_number }}</p>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <strong>الحالة:</strong>
                            <p>
                                <span class="badge bg-{{ $ncr->status == 'closed' ? 'success' : ($ncr->status == 'in_progress' ? 'warning' : 'danger') }} fs-6">
                                    @switch($ncr->status)
                                        @case('open') مفتوح @break
                                        @case('in_progress') قيد المعالجة @break
                                        @case('closed') مغلق @break
                                        @case('cancelled') ملغى @break
                                        @default {{ $ncr->status }}
                                    @endswitch
                                </span>
                            </p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>الخطورة:</strong>
                            <p>
                                <span class="badge bg-{{ $ncr->severity == 'critical' ? 'danger' : ($ncr->severity == 'major' ? 'warning' : 'info') }} fs-6">
                                    @switch($ncr->severity)
                                        @case('critical') حرجة @break
                                        @case('major') رئيسية @break
                                        @case('minor') ثانوية @break
                                        @default {{ $ncr->severity }}
                                    @endswitch
                                </span>
                            </p>
                        </div>

                        <div class="col-md-3 mb-3">
                            <strong>المصدر:</strong>
                            <p>
                                @switch($ncr->source)
                                    @case('incoming') استلام @break
                                    @case('in_process') أثناء الإنتاج @break
                                    @case('final') نهائي @break
                                    @case('customer') شكوى عميل @break
                                    @case('internal_audit') تدقيق داخلي @break
                                    @default {{ $ncr->source }}
                                @endswitch
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>الصنف:</strong>
                            <p>{{ $ncr->item?->name ?? 'غير محدد' }}</p>
                        </div>

                        @if($ncr->batch_number)
                        <div class="col-md-4 mb-3">
                            <strong>رقم الدفعة:</strong>
                            <p>{{ $ncr->batch_number }}</p>
                        </div>
                        @endif

                        <div class="col-md-4 mb-3">
                            <strong>الكمية المتأثرة:</strong>
                            <p>{{ number_format($ncr->affected_quantity, 3) }}</p>
                        </div>

                        @if($ncr->inspection)
                        <div class="col-md-6 mb-3">
                            <strong>رقم الفحص:</strong>
                            <p>
                                <a href="{{ route('quality.inspections.show', $ncr->inspection) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $ncr->inspection->inspection_number }}
                                </a>
                            </p>
                        </div>
                        @endif

                        <!-- تواريخ -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar me-2"></i>التواريخ
                            </h5>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>تاريخ الاكتشاف:</strong>
                            <p>{{ $ncr->detected_date?->format('Y-m-d') }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>تاريخ الإغلاق المستهدف:</strong>
                            <p>{{ $ncr->target_closure_date?->format('Y-m-d') }}</p>
                        </div>

                        @if($ncr->actual_closure_date)
                        <div class="col-md-4 mb-3">
                            <strong>تاريخ الإغلاق الفعلي:</strong>
                            <p>{{ $ncr->actual_closure_date?->format('Y-m-d') }}</p>
                        </div>
                        @endif

                        <!-- المسؤولون -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-users me-2"></i>المسؤولون
                            </h5>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>تم الاكتشاف بواسطة:</strong>
                            <p>{{ $ncr->detectedBy?->name ?? 'غير محدد' }}</p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <strong>تم التعيين إلى:</strong>
                            <p>{{ $ncr->assignedTo?->name ?? 'غير محدد' }}</p>
                        </div>

                        @if($ncr->closed_by)
                        <div class="col-md-4 mb-3">
                            <strong>تم الإغلاق بواسطة:</strong>
                            <p>{{ $ncr->closedBy?->name }}</p>
                        </div>
                        @endif

                        <!-- الوصف والتفاصيل -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-file-alt me-2"></i>الوصف والتفاصيل
                            </h5>
                        </div>

                        <div class="col-12 mb-3">
                            <strong>وصف المشكلة:</strong>
                            <p class="alert alert-danger">{{ $ncr->problem_description }}</p>
                        </div>

                        @if($ncr->root_cause)
                        <div class="col-12 mb-3">
                            <strong>السبب الجذري:</strong>
                            <p class="alert alert-warning">{{ $ncr->root_cause }}</p>
                        </div>
                        @endif

                        <div class="col-12 mb-3">
                            <strong>الإجراء الفوري:</strong>
                            <p class="alert alert-info">{{ $ncr->immediate_action }}</p>
                        </div>

                        <!-- التصرف -->
                        <div class="col-md-6 mb-3">
                            <strong>التصرف:</strong>
                            <p>
                                @switch($ncr->disposition)
                                    @case('rework') إعادة العمل @break
                                    @case('accept') قبول @break
                                    @case('scrap') استبعاد @break
                                    @case('return') إرجاع @break
                                    @case('use_as_is') استخدام كما هو @break
                                    @default {{ $ncr->disposition }}
                                @endswitch
                            </p>
                        </div>

                        <!-- التكاليف -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-dollar-sign me-2"></i>التكاليف
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($ncr->estimated_cost, 2) }}</h3>
                                    <p class="mb-0">التكلفة المقدرة</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($ncr->actual_cost, 2) }}</h3>
                                    <p class="mb-0">التكلفة الفعلية</p>
                                </div>
                            </div>
                        </div>

                        @if($ncr->closure_notes)
                        <div class="col-12 mb-3">
                            <strong>ملاحظات الإغلاق:</strong>
                            <p class="alert alert-success">{{ $ncr->closure_notes }}</p>
                        </div>
                        @endif

                        <!-- الإجراءات التصحيحية -->
                        @if($ncr->correctiveActions && $ncr->correctiveActions->count() > 0)
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-tools me-2"></i>الإجراءات التصحيحية المرتبطة
                            </h5>
                        </div>

                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>رقم الإجراء</th>
                                            <th>النوع</th>
                                            <th>الحالة</th>
                                            <th>المسؤول</th>
                                            <th>تاريخ الإنجاز</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ncr->correctiveActions as $capa)
                                        <tr>
                                            <td>{{ $capa->capa_number }}</td>
                                            <td>
                                                @if($capa->action_type == 'corrective')
                                                    <span class="badge bg-warning">تصحيحي</span>
                                                @else
                                                    <span class="badge bg-info">وقائي</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $capa->status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ $capa->status }}
                                                </span>
                                            </td>
                                            <td>{{ $capa->responsiblePerson?->name }}</td>
                                            <td>{{ $capa->planned_completion_date?->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('quality.capa.show', $capa) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- معلومات إضافية -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info me-2"></i>معلومات إضافية
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>تاريخ الإنشاء:</strong>
                            <p>{{ $ncr->created_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>آخر تحديث:</strong>
                            <p>{{ $ncr->updated_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    @if($ncr->status != 'closed')
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <form action="{{ route('quality.ncr.close', $ncr) }}" method="POST" 
                              onsubmit="return confirm('هل أنت متأكد من إغلاق هذا التقرير؟')">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i>إغلاق التقرير
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

