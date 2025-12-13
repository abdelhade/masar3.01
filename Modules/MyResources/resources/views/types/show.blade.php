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
                    <h4 class="page-title">عرض النوع: {{ $type->name_ar ?? $type->name }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('myresources.types.edit', $type) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> طباعة
                        </button>
                        <a href="{{ route('myresources.types.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-list"></i> معلومات النوع</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الاسم:</label>
                            <div class="form-control-static">{{ $type->name }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الاسم العربي:</label>
                            <div class="form-control-static">{{ $type->name_ar ?? 'غير محدد' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">الوصف:</label>
                            <div class="form-control-static">{{ $type->description ?? 'لا يوجد وصف' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">التصنيف:</label>
                            <div class="form-control-static">
                                @if($type->category)
                                    {{ $type->category->name_ar ?? $type->category->name }}
                                @else
                                    غير محدد
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الحالة:</label>
                            <div class="form-control-static">
                                @if($type->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-danger">غير نشط</span>
                                @endif
                            </div>
                        </div>
                    </div>
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

