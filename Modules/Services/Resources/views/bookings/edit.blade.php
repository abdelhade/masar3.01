@extends('admin.dashboard')

@section('title', 'تعديل حجز الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-edit me-2"></i>
                        تعديل حجز الخدمة
                    </h3>
                    <a href="{{ route('services.bookings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة إلى قائمة الحجوزات
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('services.bookings.update', $booking) }}" method="POST" id="bookingForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Service Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="service_id" class="form-label">الخدمة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('service_id') is-invalid @enderror" 
                                            id="service_id" name="service_id" required>
                                        <option value="">اختر الخدمة</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" 
                                                    data-price="{{ $service->price }}"
                                                    data-duration="60"
                                                    {{ old('service_id', $booking->service_id) == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }} - {{ number_format($service->price, 2) }} ر.س
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Customer Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="customer_id" class="form-label">العميل <span class="text-danger">*</span></label>
                                    <select class="form-control @error('customer_id') is-invalid @enderror" 
                                            id="customer_id" name="customer_id" required>
                                        <option value="">اختر العميل</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                    {{ old('customer_id', $booking->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Employee Selection -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="employee_id" class="form-label">الموظف المسؤول</label>
                                    <select class="form-control @error('employee_id') is-invalid @enderror" 
                                            id="employee_id" name="employee_id">
                                        <option value="">اختر الموظف (اختياري)</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" 
                                                    {{ old('employee_id', $booking->employee_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Booking Date -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="booking_date" class="form-label">تاريخ الحجز <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('booking_date') is-invalid @enderror" 
                                           id="booking_date" name="booking_date" 
                                           value="{{ old('booking_date', $booking->booking_date->format('Y-m-d')) }}" 
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('booking_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Time -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_time" class="form-label">وقت البداية <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" 
                                           value="{{ old('start_time', $booking->start_time->format('H:i')) }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- End Time -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_time" class="form-label">وقت النهاية</label>
                                    <input type="time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" 
                                           value="{{ old('end_time', $booking->end_time ? $booking->end_time->format('H:i') : '') }}" readonly>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">سيتم حساب وقت النهاية تلقائياً حسب مدة الخدمة</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Price -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label">السعر <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" 
                                               value="{{ old('price', $booking->price) }}" 
                                               step="0.01" min="0" required>
                                        <span class="input-group-text">ر.س</span>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Service Duration Display -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">مدة الخدمة</label>
                                    <div class="form-control-plaintext" id="service_duration">
                                        اختر خدمة لعرض المدة
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Notes -->
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="أي ملاحظات إضافية...">{{ old('notes', $booking->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Customer Notes -->
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="customer_notes" class="form-label">ملاحظات العميل</label>
                                    <textarea class="form-control @error('customer_notes') is-invalid @enderror" 
                                              id="customer_notes" name="customer_notes" rows="3" 
                                              placeholder="ملاحظات خاصة بالعميل...">{{ old('customer_notes', $booking->customer_notes) }}</textarea>
                                    @error('customer_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('services.bookings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        حفظ التعديلات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const priceInput = document.getElementById('price');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const durationDisplay = document.getElementById('service_duration');

    // Update price and duration when service changes
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const price = selectedOption.dataset.price;
            const duration = selectedOption.dataset.duration;
            
            priceInput.value = price;
            durationDisplay.textContent = formatDuration(duration);
            
            // Calculate end time if start time is set
            if (startTimeInput.value) {
                calculateEndTime();
            }
        } else {
            priceInput.value = '';
            durationDisplay.textContent = 'اختر خدمة لعرض المدة';
            endTimeInput.value = '';
        }
    });

    // Calculate end time when start time changes
    startTimeInput.addEventListener('change', function() {
        if (serviceSelect.value && this.value) {
            calculateEndTime();
        }
    });

    // Initialize on page load
    if (serviceSelect.value) {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const duration = selectedOption.dataset.duration;
        durationDisplay.textContent = formatDuration(duration);
    }

    function calculateEndTime() {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const duration = parseInt(selectedOption.dataset.duration);
        const startTime = startTimeInput.value;
        
        if (startTime && duration) {
            const [hours, minutes] = startTime.split(':').map(Number);
            const startDate = new Date();
            startDate.setHours(hours, minutes, 0, 0);
            
            const endDate = new Date(startDate.getTime() + (duration * 60000));
            const endTime = endDate.toTimeString().slice(0, 5);
            
            endTimeInput.value = endTime;
        }
    }

    function formatDuration(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        
        if (hours > 0 && mins > 0) {
            return `${hours} ساعة و ${mins} دقيقة`;
        } else if (hours > 0) {
            return `${hours} ساعة`;
        } else {
            return `${mins} دقيقة`;
        }
    }
});
</script>
@endpush
