@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">عرض التعيين</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('myresources.assignments.edit', $assignment) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> طباعة
                        </button>
                        <a href="{{ route('myresources.assignments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> رجوع
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card printable-content">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-link"></i> معلومات التعيين</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المورد:</label>
                            <div class="form-control-static">
                                @if($assignment->resource)
                                    {{ $assignment->resource->name }} ({{ $assignment->resource->code ?? 'N/A' }})
                                @else
                                    غير محدد
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المشروع:</label>
                            <div class="form-control-static">
                                @if($assignment->project)
                                    {{ $assignment->project->name }}
                                @else
                                    غير محدد
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">تاريخ البدء:</label>
                            <div class="form-control-static">
                                {{ $assignment->start_date ? $assignment->start_date->format('Y-m-d') : 'غير محدد' }}
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">تاريخ الانتهاء:</label>
                            <div class="form-control-static">
                                {{ $assignment->end_date ? $assignment->end_date->format('Y-m-d') : 'غير محدد' }}
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">التكلفة اليومية:</label>
                            <div class="form-control-static">
                                {{ $assignment->daily_cost ? number_format($assignment->daily_cost, 2) . ' ريال' : 'غير محدد' }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الحالة:</label>
                            <div class="form-control-static">
                                @php
                                    $statusValue = $assignment->status->value ?? $assignment->status;
                                    $statusLabels = [
                                        'scheduled' => 'مجدول',
                                        'active' => 'نشط',
                                        'completed' => 'مكتمل',
                                        'cancelled' => 'ملغي'
                                    ];
                                    $statusColors = [
                                        'scheduled' => 'info',
                                        'active' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger'
                                    ];
                                    $label = $statusLabels[$statusValue] ?? $statusValue;
                                    $color = $statusColors[$statusValue] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نوع التعيين:</label>
                            <div class="form-control-static">
                                @php
                                    $typeValue = $assignment->assignment_type->value ?? $assignment->assignment_type;
                                    $typeLabels = [
                                        'current' => 'حالي',
                                        'upcoming' => 'قادم',
                                        'past' => 'سابق'
                                    ];
                                    $label = $typeLabels[$typeValue] ?? $typeValue;
                                @endphp
                                {{ $label }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">ملاحظات:</label>
                            <div class="form-control-static">{{ $assignment->notes ?? 'لا توجد ملاحظات' }}</div>
                        </div>
                    </div>

                    @if($assignment->assignedBy)
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">تم التعيين بواسطة:</label>
                            <div class="form-control-static">
                                {{ $assignment->assignedBy->name ?? 'غير محدد' }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control-static {
        padding: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        min-height: 2.5rem;
        display: flex;
        align-items: center;
    }

    .printable-content {
        page-break-inside: avoid;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .card-header {
            background: #f1f1f1 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-size: 12px;
        }

        .form-control-static {
            background: #fff !important;
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush
@endsection

