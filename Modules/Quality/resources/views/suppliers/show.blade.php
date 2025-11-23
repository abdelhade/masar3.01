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
                    <h2 class="mb-0"><i class="fas fa-star me-2"></i>تفاصيل تقييم المورد</h2>
                </div>
                <div>
                    <a href="{{ route('quality.suppliers.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات التقييم</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المورد:</label>
                            <p class="mb-0">{{ $rating->supplier->aname ?? '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نوع الفترة:</label>
                            <p class="mb-0">
                                {{ match($rating->period_type) {
                                    'monthly' => 'شهري',
                                    'quarterly' => 'ربع سنوي',
                                    'annual' => 'سنوي',
                                    default => $rating->period_type
                                } }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">بداية الفترة:</label>
                            <p class="mb-0">{{ $rating->period_start ? $rating->period_start->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">نهاية الفترة:</label>
                            <p class="mb-0">{{ $rating->period_end ? $rating->period_end->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ التقييم:</label>
                            <p class="mb-0">{{ $rating->rating_date ? $rating->rating_date->format('Y-m-d') : '---' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تم التقييم بواسطة:</label>
                            <p class="mb-0">{{ $rating->ratedBy->name ?? '---' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>نقاط التقييم</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نقاط الجودة:</label>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-primary" style="width: {{ $rating->quality_score }}%">
                                    {{ number_format($rating->quality_score, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نقاط التسليم:</label>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-info" style="width: {{ $rating->delivery_score }}%">
                                    {{ number_format($rating->delivery_score, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">نقاط التوثيق:</label>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: {{ $rating->documentation_score }}%">
                                    {{ number_format($rating->documentation_score, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">النقاط الإجمالية:</label>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-{{ $rating->overall_score >= 80 ? 'success' : ($rating->overall_score >= 60 ? 'warning' : 'danger') }}" 
                                     style="width: {{ $rating->overall_score }}%">
                                    {{ number_format($rating->overall_score, 1) }}/100
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>المقاييس التشغيلية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $rating->total_inspections }}</h4>
                                <small class="text-muted">إجمالي الفحوصات</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ $rating->passed_inspections }}</h4>
                                <small class="text-muted">فحوصات ناجحة</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-danger">{{ $rating->failed_inspections }}</h4>
                                <small class="text-muted">فحوصات فاشلة</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-warning">{{ $rating->ncrs_raised }}</h4>
                                <small class="text-muted">تقارير عدم المطابقة</small>
                            </div>
                        </div>
                    </div>
                    @if($rating->ncrs_raised > 0)
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <h5 class="text-danger">{{ $rating->critical_ncrs }}</h5>
                                <small class="text-muted">حرجة</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <h5 class="text-warning">{{ $rating->major_ncrs }}</h5>
                                <small class="text-muted">رئيسية</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <h5 class="text-info">{{ $rating->minor_ncrs }}</h5>
                                <small class="text-muted">ثانوية</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-award me-2"></i>التقييم النهائي</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-{{ match($rating->rating) {
                            'excellent' => 'success',
                            'good' => 'info',
                            'acceptable' => 'warning',
                            'poor' => 'danger',
                            'unacceptable' => 'dark',
                            default => 'secondary'
                        } }} fs-4 px-4 py-3">
                            {{ match($rating->rating) {
                                'excellent' => 'ممتاز',
                                'good' => 'جيد',
                                'acceptable' => 'مقبول',
                                'poor' => 'ضعيف',
                                'unacceptable' => 'غير مقبول',
                                default => $rating->rating
                            } }}
                        </span>
                    </div>
                    <h3 class="mb-0">{{ number_format($rating->overall_score, 1) }}/100</h3>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>حالة المورد</h5>
                </div>
                <div class="card-body text-center">
                    <span class="badge bg-{{ $rating->supplier_status == 'approved' ? 'success' : 'danger' }} fs-6 px-3 py-2">
                        {{ $rating->supplier_status == 'approved' ? 'معتمد' : 'غير معتمد' }}
                    </span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>معلومات النظام</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ الإنشاء:</label>
                        <p class="mb-0">{{ $rating->created_at ? $rating->created_at->format('Y-m-d H:i') : '---' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">آخر تحديث:</label>
                        <p class="mb-0">{{ $rating->updated_at ? $rating->updated_at->format('Y-m-d H:i') : '---' }}</p>
                    </div>
                    @if($rating->approvedBy)
                    <div class="mb-3">
                        <label class="form-label fw-bold">تمت الموافقة بواسطة:</label>
                        <p class="mb-0">{{ $rating->approvedBy->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection