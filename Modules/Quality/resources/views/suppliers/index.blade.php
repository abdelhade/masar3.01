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
                        @can('create rateSuppliers')
                            <a href="{{ route('quality.suppliers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>تقييم جديد
                            </a>
                        @endcan

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
                                @canany(['edit rateSuppliers', 'delete rateSuppliers', 'view rateSuppliers'])
                                    <th>الإجراءات</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ratings as $rating)
                                <tr>
                                    <td><strong>{{ $rating->supplier->aname ?? '---' }}</strong></td>
                                    <td>
                                        {{ $rating->period_start ? $rating->period_start->format('Y-m-d') : '---' }} <br>
                                        <small class="text-muted">إلى
                                            {{ $rating->period_end ? $rating->period_end->format('Y-m-d') : '---' }}</small>
                                    </td>
                                    <td><span class="badge bg-primary">{{ number_format($rating->quality_score, 1) }}</span>
                                    </td>
                                    <td><span class="badge bg-info">{{ number_format($rating->delivery_score, 1) }}</span>
                                    </td>
                                    <td><strong>{{ number_format($rating->overall_score, 1) }}/100</strong></td>
                                    <td>
                                        <span
                                            class="badge bg-{{ match ($rating->rating) {
                                                'excellent' => 'success',
                                                'good' => 'info',
                                                'acceptable' => 'warning',
                                                'poor' => 'danger',
                                                'unacceptable' => 'dark',
                                                default => 'secondary',
                                            } }}">
                                            {{ match ($rating->rating) {
                                                'excellent' => 'ممتاز',
                                                'good' => 'جيد',
                                                'acceptable' => 'مقبول',
                                                'poor' => 'ضعيف',
                                                'unacceptable' => 'غير مقبول',
                                                default => $rating->rating,
                                            } }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $rating->supplier_status == 'approved' ? 'success' : 'danger' }}">
                                            {{ $rating->supplier_status }}
                                        </span>
                                    </td>
                                    @canany(['edit rateSuppliers', 'delete rateSuppliers', 'view rateSuppliers'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view rateSuppliers')
                                                <a href="{{ route('quality.suppliers.show', $rating) }}"
                                                    class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan
                                                @can('edit rateSuppliers')
                                                <a href="{{ route('quality.suppliers.edit', $rating) }}"
                                                    class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                @can('delete rateSuppliers')
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $rating->id }}" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endcan
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $rating->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف تقييم المورد
                                                            "{{ $rating->supplier->aname ?? '---' }}"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('quality.suppliers.destroy', $rating) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">حذف</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">لا توجد تقييمات للموردين</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ratings->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $ratings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
