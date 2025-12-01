{{-- Attendance & Salary Information Tab --}}
<div x-show="activeViewTab === 'attendance'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-clock me-2"></i>{{ __('بيانات المرتبات والحضور') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-clock me-1 text-success"></i>{{ __('الشيفت') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    @if($viewEmployee->shift)
                                        <span class="badge bg-info fs-6">
                                            {{ $viewEmployee->shift->start_time }} - {{ $viewEmployee->shift->end_time }}
                                        </span>
                                    @else
                                        {{ __('غير محدد') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-fingerprint me-1 text-success"></i>{{ __('رقم البصمة') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->finger_print_id ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-user-tag me-1 text-success"></i>{{ __('الاسم في البصمة') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->finger_print_name ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-lock me-1 text-success"></i>{{ __('باسورد الهاتف') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    {!! $viewEmployee->password ? '<span class="badge bg-secondary fs-6">********</span>' : __('غير محدد') !!}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-plus-circle me-1 text-success"></i>{{ __('الساعة الإضافي تحسب ك') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">
                                    {{ $viewEmployee->additional_hour_calculation ? $viewEmployee->additional_hour_calculation . ' ساعة' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-calendar-plus me-1 text-success"></i>{{ __('اليوم الإضافي يحسب ك') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">
                                    {{ $viewEmployee->additional_day_calculation ? $viewEmployee->additional_day_calculation . ' يوم' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-minus-circle me-1 text-danger"></i>{{ __('الساعة المتأخرة تحسب ك') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">
                                    {{ $viewEmployee->late_hour_calculation ? $viewEmployee->late_hour_calculation . ' ساعة' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-calendar-minus me-1 text-danger"></i>{{ __('اليوم المتأخر يحسب ك') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">
                                    {{ $viewEmployee->late_day_calculation ? $viewEmployee->late_day_calculation . ' يوم' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

