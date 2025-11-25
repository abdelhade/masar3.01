@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        تقارير عدم المطابقة (NCR)
                    </h2>
                    <p class="text-muted mb-0">إدارة ومتابعة تقارير عدم المطابقة</p>
                </div>
                @can('create ncr')
                <div>
                    <a href="{{ route('quality.ncr.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus-circle me-2"></i>
                        تقرير NCR جديد
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">إجمالي التقارير</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2.5rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">تقارير مفتوحة</h6>
                            <h3 class="mb-0 text-danger">{{ $stats['open'] }}</h3>
                        </div>
                        <div class="text-danger" style="font-size: 2.5rem;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">تقارير حرجة</h6>
                            <h3 class="mb-0 text-warning">{{ $stats['critical'] }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">تقارير متأخرة</h6>
                            <h3 class="mb-0 text-info">{{ $stats['overdue'] }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('quality.ncr.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="رقم NCR، الوصف، رقم الدفعة...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوح</option>
                        <option value="investigating" {{ request('status') == 'investigating' ? 'selected' : '' }}>قيد التحقيق</option>
                        <option value="implementing" {{ request('status') == 'implementing' ? 'selected' : '' }}>قيد التنفيذ</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلق</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الخطورة</label>
                    <select name="severity" class="form-select">
                        <option value="">الكل</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>حرج</option>
                        <option value="major" {{ request('severity') == 'major' ? 'selected' : '' }}>رئيسي</option>
                        <option value="minor" {{ request('severity') == 'minor' ? 'selected' : '' }}>ثانوي</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">المصدر</label>
                    <select name="source" class="form-select">
                        <option value="">الكل</option>
                        <option value="receiving_inspection">فحص استلام</option>
                        <option value="in_process">أثناء الإنتاج</option>
                        <option value="final_inspection">فحص نهائي</option>
                        <option value="customer_complaint">شكوى عميل</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- NCRs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>رقم NCR</th>
                            <th>الصنف</th>
                            <th>رقم الدفعة</th>
                            <th>الخطورة</th>
                            <th>الحالة</th>
                            <th>المكتشف</th>
                            <th>التاريخ</th>
                            @canany(['edit ncr', 'delete ncr', 'view ncr'])
                            <th>الإجراءات</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ncrs as $ncr)
                        <tr>
                            <td>
                                <a href="{{ route('quality.ncr.show', $ncr) }}" class="fw-bold">
                                    {{ $ncr->ncr_number }}
                                </a>
                            </td>
                            <td>{{ $ncr->item->name ?? '---' }}</td>
                            <td>
                                @if($ncr->batch_number)
                                    <span class="badge bg-secondary">{{ $ncr->batch_number }}</span>
                                @else
                                    ---
                                @endif
                            </td>
                            <td>
                                @if($ncr->severity == 'critical')
                                    <span class="badge bg-danger">حرج</span>
                                @elseif($ncr->severity == 'major')
                                    <span class="badge bg-warning">رئيسي</span>
                                @else
                                    <span class="badge bg-info">ثانوي</span>
                                @endif
                            </td>
                            <td>
                                @if($ncr->status == 'open')
                                    <span class="badge bg-danger">مفتوح</span>
                                @elseif($ncr->status == 'closed')
                                    <span class="badge bg-success">مغلق</span>
                                @else
                                    <span class="badge bg-warning">{{ $ncr->status }}</span>
                                @endif
                            </td>
                            <td>{{ $ncr->detectedBy->name ?? '---' }}</td>
                            <td>{{ $ncr->detected_date->format('Y-m-d') }}</td>
                            @canany(['edit ncr', 'delete ncr', 'view ncr'])
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view ncr')
                                    <a href="{{ route('quality.ncr.show', $ncr) }}"
                                       class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit ncr')
                                    <a href="{{ route('quality.ncr.edit', $ncr) }}"
                                       class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete ncr')
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $ncr->id }}"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $ncr->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تأكيد الحذف</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                هل أنت متأكد من حذف تقرير NCR "{{ $ncr->ncr_number }}"؟
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('quality.ncr.destroy', $ncr) }}" method="POST" class="d-inline">
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
                                <p class="text-muted mb-0">لا توجد تقارير عدم مطابقة</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($ncrs->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $ncrs->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

