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
                    <h2 class="mb-0"><i class="fas fa-barcode me-2"></i>تتبع الدفعات</h2>
                </div>
                <div>
                    <a href="{{ route('quality.batches.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>دفعة جديدة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted">دفعات نشطة</h6>
                    <h3 class="text-success">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted">تنتهي قريباً</h6>
                    <h3 class="text-warning">{{ $stats['expiring_soon'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4">
                <div class="card-body">
                    <h6 class="text-muted">منتهية</h6>
                    <h3 class="text-danger">{{ $stats['expired'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted">إجمالي</h6>
                    <h3>{{ $stats['total'] }}</h3>
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
                            <th>رقم الدفعة</th>
                            <th>الصنف</th>
                            <th>تاريخ الإنتاج</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الكمية</th>
                            <th>المتبقي</th>
                            <th>حالة الجودة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr class="{{ $batch->isExpired() ? 'table-danger' : ($batch->isExpiringSoon() ? 'table-warning' : '') }}">
                            <td>
                                <a href="{{ route('quality.batches.show', $batch) }}" class="fw-bold">
                                    {{ $batch->batch_number }}
                                </a>
                            </td>
                            <td>{{ $batch->item->name ?? '---' }}</td>
                            <td>{{ $batch->production_date ? $batch->production_date->format('Y-m-d') : '---' }}</td>
                            <td>
                                {{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '---' }}
                                @if($batch->isExpiringSoon())
                                    <i class="fas fa-exclamation-triangle text-warning" title="ينتهي قريباً"></i>
                                @endif
                            </td>
                            <td>{{ number_format($batch->quantity, 2) }}</td>
                            <td>{{ number_format($batch->remaining_quantity, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ match($batch->quality_status) {
                                    'passed' => 'success',
                                    'failed' => 'danger',
                                    'conditional' => 'warning',
                                    'quarantine' => 'dark',
                                    default => 'secondary'
                                } }}">
                                    {{ $batch->quality_status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ match($batch->status) {
                                    'active' => 'success',
                                    'expired' => 'danger',
                                    'quarantine' => 'warning',
                                    default => 'secondary'
                                } }}">
                                    {{ $batch->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('quality.batches.show', $batch) }}" class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('quality.batches.edit', $batch) }}" class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $batch->id }}" 
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $batch->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف الدفعة "{{ $batch->batch_number }}"?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('quality.batches.destroy', $batch) }}" method="POST" class="d-inline">
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
                            <td colspan="9" class="text-center py-4">لا توجد دفعات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $batches->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

