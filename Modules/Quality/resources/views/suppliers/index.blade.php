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
                    <h2 class="mb-0"><i class="fas fa-star me-2"></i>تقييم الموردين</h2>
                </div>
                <div>
                    <a href="{{ route('quality.suppliers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>تقييم جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">موردين ممتازين</h6>
                    <h3 class="text-success">{{ $stats['excellent'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">موردين جيدين</h6>
                    <h3 class="text-info">{{ $stats['good'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">موردين ضعفاء</h6>
                    <h3 class="text-danger">{{ $stats['poor'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">إجمالي التقييمات</h6>
                    <h3>{{ $stats['total_suppliers'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>المورد</th>
                            <th>الفترة</th>
                            <th>نقاط الجودة</th>
                            <th>نقاط التسليم</th>
                            <th>النقاط الكلية</th>
                            <th>التقييم</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ratings as $rating)
                        <tr>
                            <td><strong>{{ $rating->supplier->aname ?? '---' }}</strong></td>
                            <td>
                                {{ $rating->period_start->format('Y-m-d') }} <br>
                                <small class="text-muted">إلى {{ $rating->period_end->format('Y-m-d') }}</small>
                            </td>
                            <td><span class="badge bg-primary">{{ number_format($rating->quality_score, 1) }}</span></td>
                            <td><span class="badge bg-info">{{ number_format($rating->delivery_score, 1) }}</span></td>
                            <td><strong>{{ number_format($rating->overall_score, 1) }}/100</strong></td>
                            <td>
                                <span class="badge bg-{{ match($rating->rating) {
                                    'excellent' => 'success',
                                    'good' => 'info',
                                    'acceptable' => 'warning',
                                    'poor' => 'danger',
                                    'unacceptable' => 'dark',
                                    default => 'secondary'
                                } }}">
                                    {{ match($rating->rating) {
                                        'excellent' => 'ممتاز',
                                        'good' => 'جيد',
                                        'acceptable' => 'مقبول',
                                        'poor' => 'ضعيف',
                                        'unacceptable' => 'غير مقبول',
                                        default => $rating->rating
                                    } }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $rating->supplier_status == 'approved' ? 'success' : 'danger' }}">
                                    {{ $rating->supplier_status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('quality.suppliers.show', $rating) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">لا توجد تقييمات للموردين</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ratings->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $ratings->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

