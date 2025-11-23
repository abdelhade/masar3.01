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
                    <h2 class="mb-0"><i class="fas fa-ruler-combined me-2"></i>معايير الجودة</h2>
                </div>
                <div>
                    <a href="{{ route('quality.standards.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>معيار جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">إجمالي المعايير</h6>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">معايير نشطة</h6>
                    <h3 class="text-success">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">معايير موقوفة</h6>
                    <h3 class="text-danger">{{ $stats['inactive'] }}</h3>
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
                            <th>الكود</th>
                            <th>اسم المعيار</th>
                            <th>الصنف</th>
                            <th>تكرار الفحص</th>
                            <th>حد القبول</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($standards as $standard)
                        <tr>
                            <td><strong>{{ $standard->standard_code }}</strong></td>
                            <td>{{ $standard->standard_name }}</td>
                            <td>{{ $standard->item->name ?? '---' }}</td>
                            <td>
                                {{ match($standard->test_frequency) {
                                    'per_batch' => 'لكل دفعة',
                                    'daily' => 'يومي',
                                    'weekly' => 'أسبوعي',
                                    'monthly' => 'شهري',
                                    default => $standard->test_frequency
                                } }}
                            </td>
                            <td><strong>{{ $standard->acceptance_threshold }}%</strong></td>
                            <td>
                                @if($standard->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">موقوف</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('quality.standards.show', $standard) }}" class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('quality.standards.edit', $standard) }}" class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $standard->id }}" 
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $standard->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف معيار الجودة "{{ $standard->standard_name }}"؟
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('quality.standards.destroy', $standard) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">لا توجد معايير جودة</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($standards->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $standards->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

