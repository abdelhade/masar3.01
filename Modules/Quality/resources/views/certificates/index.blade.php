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
                    <h2 class="mb-0"><i class="fas fa-certificate me-2"></i>الشهادات والامتثال</h2>
                </div>
                <div>
                    <a href="{{ route('quality.certificates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>شهادة جديدة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">شهادات نشطة</h6>
                    <h3 class="text-success">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">تنتهي قريباً</h6>
                    <h3 class="text-warning">{{ $stats['expiring_soon'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">منتهية</h6>
                    <h3 class="text-danger">{{ $stats['expired'] }}</h3>
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
                            <th>رقم الشهادة</th>
                            <th>اسم الشهادة</th>
                            <th>النوع</th>
                            <th>جهة الإصدار</th>
                            <th>تاريخ الإصدار</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الأيام المتبقية</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                        <tr class="{{ $certificate->isExpired() ? 'table-danger' : ($certificate->isExpiringSoon() ? 'table-warning' : '') }}">
                            <td><strong>{{ $certificate->certificate_number }}</strong></td>
                            <td>{{ $certificate->certificate_name }}</td>
                            <td>
                                <span class="badge bg-info">
                                    شهادة
                                </span>
                            </td>
                            <td>{{ $certificate->issuing_authority }}</td>
                            <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                            <td>{{ $certificate->expiry_date->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $daysLeft = $certificate->daysUntilExpiry();
                                @endphp
                                <span class="badge bg-{{ $daysLeft < 0 ? 'danger' : ($daysLeft < 30 ? 'warning' : 'success') }}">
                                    {{ abs($daysLeft) }} {{ $daysLeft < 0 ? 'منتهي' : 'يوم' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ match($certificate->status) {
                                    'active' => 'success',
                                    'expired' => 'danger',
                                    'renewal_pending' => 'warning',
                                    default => 'secondary'
                                } }}">
                                    {{ $certificate->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('quality.certificates.show', $certificate) }}" class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('quality.certificates.edit', $certificate) }}" class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $certificate->id }}" 
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $certificate->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف الشهادة "{{ $certificate->certificate_name }}"?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('quality.certificates.destroy', $certificate) }}" method="POST" class="d-inline">
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
                            <td colspan="9" class="text-center py-4">لا توجد شهادات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($certificates->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $certificates->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

