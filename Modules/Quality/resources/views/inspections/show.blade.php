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
                        <i class="fas fa-clipboard-check me-2"></i>
                        تفاصيل الفحص: {{ $inspection->inspection_number }}
                    </h4>
                    <div>
                        <a href="{{ route('quality.inspections.edit', $inspection) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('quality.inspections.index') }}" class="btn btn-secondary btn-sm">
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
                        
                        <div class="col-md-4 mb-3">
                            <strong>رقم الفحص:</strong>
                            <p>{{ $inspection->inspection_number }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>الصنف:</strong>
                            <p>{{ $inspection->item?->name ?? 'غير محدد' }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>نوع الفحص:</strong>
                            <p>
                                @switch($inspection->inspection_type)
                                    @case('receiving') استلام @break
                                    @case('in_process') أثناء الإنتاج @break
                                    @case('final') نهائي @break
                                    @case('random') عشوائي @break
                                    @case('customer_complaint') شكوى عميل @break
                                    @default {{ $inspection->inspection_type }}
                                @endswitch
                            </p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>تاريخ الفحص:</strong>
                            <p>{{ $inspection->inspection_date?->format('Y-m-d') }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>المفتش:</strong>
                            <p>{{ $inspection->inspector?->name ?? 'غير محدد' }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>الحالة:</strong>
                            <p>
                                <span class="badge bg-{{ $inspection->status == 'completed' ? 'success' : ($inspection->status == 'pending' ? 'warning' : 'info') }}">
                                    @switch($inspection->status)
                                        @case('pending') قيد الانتظار @break
                                        @case('in_progress') قيد التنفيذ @break
                                        @case('completed') مكتمل @break
                                        @default {{ $inspection->status }}
                                    @endswitch
                                </span>
                            </p>
                        </div>

                        @if($inspection->supplier)
                        <div class="col-md-4 mb-3">
                            <strong>المورد:</strong>
                            <p>{{ $inspection->supplier->name }}</p>
                        </div>
                        @endif

                        @if($inspection->batch_number)
                        <div class="col-md-4 mb-3">
                            <strong>رقم الدفعة:</strong>
                            <p>{{ $inspection->batch_number }}</p>
                        </div>
                        @endif

                        <!-- نتائج الفحص -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-chart-bar me-2"></i>نتائج الفحص
                            </h5>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($inspection->quantity_inspected, 2) }}</h3>
                                    <p class="mb-0">الكمية المفحوصة</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($inspection->pass_quantity, 2) }}</h3>
                                    <p class="mb-0">كمية النجاح</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($inspection->fail_quantity, 2) }}</h3>
                                    <p class="mb-0">كمية الفشل</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($inspection->pass_percentage, 1) }}%</h3>
                                    <p class="mb-0">نسبة النجاح</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <strong>النتيجة النهائية:</strong>
                            <p>
                                <span class="badge bg-{{ $inspection->result == 'pass' ? 'success' : ($inspection->result == 'fail' ? 'danger' : 'warning') }} fs-5">
                                    @switch($inspection->result)
                                        @case('pass') ناجح ✓ @break
                                        @case('fail') راسب ✗ @break
                                        @case('conditional') مشروط @break
                                        @default {{ $inspection->result }}
                                    @endswitch
                                </span>
                            </p>
                        </div>

                        @if($inspection->defects_found)
                        <div class="col-12 mb-3">
                            <strong>العيوب المكتشفة:</strong>
                            <p class="alert alert-warning">{{ $inspection->defects_found }}</p>
                        </div>
                        @endif

                        @if($inspection->inspector_notes)
                        <div class="col-12 mb-3">
                            <strong>ملاحظات المفتش:</strong>
                            <p class="alert alert-info">{{ $inspection->inspector_notes }}</p>
                        </div>
                        @endif

                        <div class="col-12 mb-3">
                            <strong>الإجراء المتخذ:</strong>
                            <p>{{ $inspection->action_taken }}</p>
                        </div>

                        @if($inspection->approved_by)
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-check-circle me-2"></i>معلومات الموافقة
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>تمت الموافقة بواسطة:</strong>
                            <p>{{ $inspection->approvedBy?->name }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>تاريخ الموافقة:</strong>
                            <p>{{ $inspection->approved_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        @if($inspection->approval_notes)
                        <div class="col-12 mb-3">
                            <strong>ملاحظات الموافقة:</strong>
                            <p>{{ $inspection->approval_notes }}</p>
                        </div>
                        @endif
                        @endif

                        <!-- معلومات إضافية -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info me-2"></i>معلومات إضافية
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>تاريخ الإنشاء:</strong>
                            <p>{{ $inspection->created_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>آخر تحديث:</strong>
                            <p>{{ $inspection->updated_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

