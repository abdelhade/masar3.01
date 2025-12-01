{{-- Job Information Tab --}}
<div x-show="activeViewTab === 'job'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-warning text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-briefcase me-2"></i>{{ __('بيانات الوظيفة') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-briefcase me-1 text-warning"></i>{{ __('الوظيفة') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->job?->title ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-building me-1 text-warning"></i>{{ __('القسم') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->department?->title ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-money-bill-wave me-1 text-warning"></i>{{ __('الراتب') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fw-bold text-success fs-5">
                                    {{ $viewEmployee->salary ? number_format($viewEmployee->salary, 2) . ' ر.س' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-tag me-1 text-warning"></i>{{ __('نوع الاستحقاق') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">{{ e($viewEmployee->salary_type ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-calendar-check me-1 text-warning"></i>{{ __('تاريخ التوظيف') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">
                                    {{ $viewEmployee->date_of_hire ? $viewEmployee->date_of_hire->format('Y-m-d') : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-calendar-times me-1 text-warning"></i>{{ __('تاريخ الانتهاء') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fs-5">
                                    {{ $viewEmployee->date_of_fire ? $viewEmployee->date_of_fire->format('Y-m-d') : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

