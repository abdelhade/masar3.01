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
                    <h2 class="mb-1">{{ $standard->standard_name }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">الجودة</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quality.standards.index') }}">معايير الجودة</a></li>
                            <li class="breadcrumb-item active">{{ $standard->standard_code }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('quality.standards.edit', $standard) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('quality.standards.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات المعيار</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">رمز المعيار</label>
                            <div class="fw-bold">{{ $standard->standard_code }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">اسم المعيار</label>
                            <div class="fw-bold">{{ $standard->standard_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">الصنف</label>
                            <div class="fw-bold">{{ $standard->item?->name ?? 'غير محدد' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">الفرع</label>
                            <div class="fw-bold">{{ $standard->branch?->name ?? 'غير محدد' }}</div>
                        </div>
                        @if($standard->description)
                        <div class="col-12 mb-3">
                            <label class="text-muted small">الوصف</label>
                            <div class="fw-bold">{{ $standard->description }}</div>
                        </div>
                        @endif
                        @if($standard->test_method)
                        <div class="col-12 mb-3">
                            <label class="text-muted small">طريقة الاختبار</label>
                            <div class="fw-bold">{{ $standard->test_method }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($standard->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>ملاحظات</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">{{ $standard->notes }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- الإحصائيات والحالة -->
        <div class="col-lg-4">
            <!-- الحالة -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($standard->is_active)
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        @else
                            <i class="fas fa-pause-circle text-secondary" style="font-size: 3rem;"></i>
                        @endif
                    </div>
                    <h4 class="mb-2">{{ $standard->is_active ? 'نشط' : 'غير نشط' }}</h4>
                    <div class="text-muted">حالة المعيار</div>
                </div>
            </div>

            <!-- معايير الاختبار -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">معايير الاختبار</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>حجم العينة</span>
                            <strong>{{ $standard->sample_size }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>تكرار الاختبار</span>
                            <strong>
                                {{ match($standard->test_frequency) {
                                    'per_batch' => 'لكل دفعة',
                                    'daily' => 'يومي',
                                    'weekly' => 'أسبوعي',
                                    'monthly' => 'شهري',
                                    default => $standard->test_frequency
                                } }}
                            </strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between text-success">
                            <span>عتبة القبول</span>
                            <strong>{{ number_format($standard->acceptance_threshold, 1) }}%</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between text-danger">
                            <span>حد العيوب المسموح</span>
                            <strong>{{ $standard->max_defects_allowed }}</strong>
                        </div>
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
                    <div class="mb-3">{{ $standard->created_at?->format('Y-m-d H:i') }}</div>
                    
                    @if($standard->updated_at != $standard->created_at)
                    <div class="small text-muted mb-2">آخر تحديث</div>
                    <div>{{ $standard->updated_at?->format('Y-m-d H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection