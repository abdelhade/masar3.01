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
                    <h2 class="mb-0"><i class="fas fa-search me-2"></i>التدقيق الداخلي</h2>
                </div>
                <div>
                    <a href="{{ route('quality.audits.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>تدقيق جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">تدقيقات مخططة</h6>
                    <h3 class="text-info">{{ $stats['planned'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">قيد التنفيذ</h6>
                    <h3 class="text-warning">{{ $stats['in_progress'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">مكتملة</h6>
                    <h3 class="text-success">{{ $stats['completed'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
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
                            <th>رقم التدقيق</th>
                            <th>العنوان</th>
                            <th>النوع</th>
                            <th>المدقق الرئيسي</th>
                            <th>التاريخ المخطط</th>
                            <th>النتيجة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                        <tr>
                            <td><a href="{{ route('quality.audits.show', $audit) }}">{{ $audit->audit_number }}</a></td>
                            <td>{{ $audit->audit_title }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ match($audit->audit_type) {
                                        'internal' => 'داخلي',
                                        'external' => 'خارجي',
                                        'supplier' => 'مورد',
                                        'certification' => 'شهادة',
                                        'customer' => 'عميل',
                                        default => $audit->audit_type
                                    } }}
                                </span>
                            </td>
                            <td>{{ $audit->leadAuditor->name ?? '---' }}</td>
                            <td>{{ $audit->planned_date->format('Y-m-d') }}</td>
                            <td>
                                @if($audit->overall_result)
                                    <span class="badge bg-{{ match($audit->overall_result) {
                                        'pass' => 'success',
                                        'fail' => 'danger',
                                        default => 'warning'
                                    } }}">
                                        {{ $audit->overall_result }}
                                    </span>
                                @else
                                    <span class="text-muted">---</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ match($audit->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'warning',
                                    'planned' => 'info',
                                    default => 'secondary'
                                } }}">
                                    {{ $audit->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('quality.audits.show', $audit) }}" class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('quality.audits.edit', $audit) }}" class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $audit->id }}" 
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $audit->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف التدقيق "{{ $audit->audit_title }}"؟
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('quality.audits.destroy', $audit) }}" method="POST" class="d-inline">
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
                            <td colspan="8" class="text-center py-4">لا توجد تدقيقات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($audits->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $audits->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

