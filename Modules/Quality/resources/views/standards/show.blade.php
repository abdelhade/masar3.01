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
                        <i class="fas fa-ruler-combined me-2"></i>
                        تفاصيل معيار الجودة: {{ $standard->standard_name }}
                    </h4>
                    <div>
                        <a href="{{ route('quality.standards.edit', $standard) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        <a href="{{ route('quality.standards.index') }}" class="btn btn-secondary btn-sm">
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
                            <strong>رمز المعيار:</strong>
                            <p>{{ $standard->standard_code }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>اسم المعيار:</strong>
                            <p>{{ $standard->standard_name }}</p>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>الصنف:</strong>
                            <p>{{ $standard->item?->name ?? 'غير محدد' }}</p>
                        </div>

                        @if($standard->description)
                        <div class="col-12 mb-3">
                            <strong>الوصف:</strong>
                            <p>{{ $standard->description }}</p>
                        </div>
                        @endif

                        @if($standard->test_method)
                        <div class="col-12 mb-3">
                            <strong>طريقة الاختبار:</strong>
                            <p>{{ $standard->test_method }}</p>
                        </div>
                        @endif

                        <!-- معايير الاختبار -->
                        <div class="col-12 mb-4 mt-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-vial me-2"></i>معايير الاختبار
                            </h5>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $standard->sample_size }}</h3>
                                    <p class="mb-0">حجم العينة</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-0">
                                        @switch($standard->test_frequency)
                                            @case('per_batch') لكل دفعة @break
                                            @case('daily') يومي @break
                                            @case('weekly') أسبوعي @break
                                            @case('monthly') شهري @break
                                        @endswitch
                                    </h5>
                                    <p class="mb-0">تكرار الاختبار</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($standard->acceptance_threshold, 1) }}%</h3>
                                    <p class="mb-0">عتبة القبول</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $standard->max_defects_allowed }}</h3>
                                    <p class="mb-0">حد العيوب المسموح</p>
                                </div>
                            </div>
                        </div>

                        <!-- الحالة -->
                        <div class="col-md-6 mb-3">
                            <strong>الحالة:</strong>
                            <p>
                                <span class="badge bg-{{ $standard->is_active ? 'success' : 'secondary' }}">
                                    {{ $standard->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>الفرع:</strong>
                            <p>{{ $standard->branch?->name ?? 'غير محدد' }}</p>
                        </div>

                        @if($standard->notes)
                        <div class="col-12 mb-3">
                            <strong>ملاحظات:</strong>
                            <p class="alert alert-info">{{ $standard->notes }}</p>
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
                            <p>{{ $standard->created_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>آخر تحديث:</strong>
                            <p>{{ $standard->updated_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

