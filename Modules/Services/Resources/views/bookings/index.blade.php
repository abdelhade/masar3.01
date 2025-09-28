@extends('admin.dashboard')
@section('title', 'إدارة حجوزات الخدمات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt me-2"></i>
                        إدارة حجوزات الخدمات
                    </h3>
                    <a href="{{ route('services.bookings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        حجز جديد
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_from">من تاريخ</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_to">إلى تاريخ</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">الحالة</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="service_id">الخدمة</label>
                                <select class="form-control" id="service_id" name="service_id">
                                    <option value="">جميع الخدمات</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" 
                                                {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary d-block w-100" onclick="applyFilters()">
                                    <i class="fas fa-search me-1"></i>
                                    بحث
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white">#</th>
                                    <th class="text-white">الخدمة</th>
                                    <th class="text-white">العميل</th>
                                    <th class="text-white">التاريخ</th>
                                    <th class="text-white">الوقت</th>
                                    <th class="text-white">الموظف</th>
                                    <th class="text-white">السعر</th>
                                    <th class="text-white">الحالة</th>
                                    <th class="text-white">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->id }}</td>
                                        <td>
                                            <strong>{{ $booking->service->name }}</strong>
                                            <br><small class="text-muted">{{ $booking->service->code }}</small>
                                        </td>
                                        <td>{{ $booking->customer->aname }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        </td>
                                        <td>
                                            @if($booking->employee)
                                                {{ $booking->employee->aname }}
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($booking->price, 2) }} ر.س</strong>
                                        </td>
                                        <td>
                                            @if($booking->is_cancelled)
                                                <span class="badge bg-danger">ملغي</span>
                                            @elseif($booking->is_completed)
                                                <span class="badge bg-success">مكتمل</span>
                                            @else
                                                <span class="badge bg-warning">معلق</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('services.bookings.show', $booking) }}" 
                                                   class="btn btn-sm btn-outline-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(!$booking->is_completed && !$booking->is_cancelled)
                                                    <a href="{{ route('services.bookings.edit', $booking) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('services.bookings.complete', $booking) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                title="إكمال" onclick="return confirm('هل أنت متأكد من إكمال هذا الحجز؟')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            title="إلغاء" onclick="cancelBooking({{ $booking->id }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                @if(!$booking->is_completed)
                                                    <form action="{{ route('services.bookings.destroy', $booking) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الحجز؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                                <p>لا توجد حجوزات متاحة</p>
                                                <a href="{{ route('services.bookings.create') }}" class="btn btn-primary">
                                                    إضافة حجز جديد
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($bookings->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $bookings->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إلغاء الحجز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cancellation_reason">سبب الإلغاء <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                  rows="3" required placeholder="أدخل سبب إلغاء الحجز"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد الإلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    const status = document.getElementById('status').value;
    const serviceId = document.getElementById('service_id').value;
    
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    if (status) params.append('status', status);
    if (serviceId) params.append('service_id', serviceId);
    
    window.location.href = '{{ route("services.bookings.index") }}?' + params.toString();
}

function cancelBooking(bookingId) {
    const form = document.getElementById('cancelForm');
    form.action = '{{ route("services.bookings.cancel", ":id") }}'.replace(':id', bookingId);
    
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}
</script>
@endpush
