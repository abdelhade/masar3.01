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
                    <h2 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        فحوصات الجودة
                    </h2>
                </div>
                @can('create inspections')
                <div>
                    <a href="{{ route('quality.inspections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>فحص جديد
                    </a>
                </div>
                @endcan

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رقم الفحص</th>
                            <th>الصنف</th>
                            <th>النوع</th>
                            <th>الكمية</th>
                            <th>النتيجة</th>
                            <th>نسبة النجاح</th>
                            <th>التاريخ</th>
                            @canany(['edit inspections', 'delete inspections' , 'view inspections'])
                            <th>الإجراءات</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inspections as $inspection)
                        <tr>
                            <td>
                                <a href="{{ route('quality.inspections.show', $inspection) }}">
                                    {{ $inspection->inspection_number }}
                                </a>
                            </td>
                            <td>{{ $inspection->item->name ?? '---' }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ match($inspection->inspection_type) {
                                        'receiving' => 'استلام',
                                        'in_process' => 'أثناء الإنتاج',
                                        'final' => 'نهائي',
                                        'random' => 'عشوائي',
                                        'customer_complaint' => 'شكوى',
                                        default => $inspection->inspection_type
                                    } }}
                                </span>
                            </td>
                            <td>{{ number_format($inspection->quantity_inspected, 2) }}</td>
                            <td>
                                @if($inspection->result == 'pass')
                                    <span class="badge bg-success">نجح</span>
                                @elseif($inspection->result == 'fail')
                                    <span class="badge bg-danger">فشل</span>
                                @else
                                    <span class="badge bg-warning">مشروط</span>
                                @endif
                            </td>
                            <td>
                                <strong class="{{ $inspection->pass_percentage >= 95 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($inspection->pass_percentage, 1) }}%
                                </strong>
                            </td>
                            <td>{{ $inspection->inspection_date->format('Y-m-d') }}</td>
                            @canany(['edit inspections', 'delete inspections' , 'view inspections'])
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view inspections')
                                    <a href="{{ route('quality.inspections.show', $inspection) }}"
                                       class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit inspections')
                                    <a href="{{ route('quality.inspections.edit', $inspection) }}"
                                       class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete inspections')
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $inspection->id }}"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $inspection->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف الفحص رقم {{ $inspection->inspection_number }}؟
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('quality.inspections.destroy', $inspection) }}" method="POST" class="d-inline">
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
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">لا توجد فحوصات</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inspections->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $inspections->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

