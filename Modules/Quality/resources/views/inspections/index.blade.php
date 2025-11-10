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
                <div>
                    <a href="{{ route('quality.inspections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>فحص جديد
                    </a>
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
                            <th>رقم الفحص</th>
                            <th>الصنف</th>
                            <th>النوع</th>
                            <th>الكمية</th>
                            <th>النتيجة</th>
                            <th>نسبة النجاح</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
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
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('quality.inspections.show', $inspection) }}" 
                                       class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('quality.inspections.edit', $inspection) }}" 
                                       class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
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

