{{-- Personal Information Tab --}}
<div x-show="activeViewTab === 'personal'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row g-4">
        {{-- Personal Information Card --}}
        <div class="col-lg-8 col-md-12">
            <div class="card border-0 shadow-sm h-100 animate-on-scroll">
                <div class="card-header bg-primary text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-user me-2"></i>{{ __('بيانات شخصية') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-id-card me-1 text-primary"></i>{{ __('الاسم') }}
                                </label>
                                <p class="form-control-plaintext fw-bold mb-0">{{ e($viewEmployee->name) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-envelope me-1 text-primary"></i>{{ __('البريد الإلكتروني') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    <a href="mailto:{{ e($viewEmployee->email) }}" class="text-decoration-none">
                                        {{ e($viewEmployee->email) }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-phone me-1 text-primary"></i>{{ __('رقم الهاتف') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    <a href="tel:{{ e($viewEmployee->phone) }}" class="text-decoration-none">
                                        {{ e($viewEmployee->phone) }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-venus-mars me-1 text-primary"></i>{{ __('النوع') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    @if($viewEmployee->gender == 'male')
                                        <span class="badge bg-info"><i class="fas fa-mars me-1"></i>{{ __('ذكر') }}</span>
                                    @elseif($viewEmployee->gender == 'female')
                                        <span class="badge bg-pink"><i class="fas fa-venus me-1"></i>{{ __('أنثى') }}</span>
                                    @else
                                        <span class="text-muted">{{ __('غير محدد') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-birthday-cake me-1 text-primary"></i>{{ __('تاريخ الميلاد') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    {{ $viewEmployee->date_of_birth ? $viewEmployee->date_of_birth->format('Y-m-d') : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-toggle-on me-1 text-primary"></i>{{ __('الحالة') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    <span class="badge {{ $viewEmployee->status == 'مفعل' ? 'bg-success' : 'bg-danger' }} fs-6">
                                        {{ $viewEmployee->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-id-badge me-1 text-primary"></i>{{ __('رقم الهوية') }}
                                </label>
                                <p class="form-control-plaintext mb-0">{{ e($viewEmployee->nationalId ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-heart me-1 text-primary"></i>{{ __('الحالة الاجتماعية') }}
                                </label>
                                <p class="form-control-plaintext mb-0">{{ e($viewEmployee->marital_status ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-graduation-cap me-1 text-primary"></i>{{ __('مستوى التعليم') }}
                                </label>
                                <p class="form-control-plaintext mb-0">{{ e($viewEmployee->education ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-layer-group me-1 text-primary"></i>{{ __('المستوى الوظيفي') }}
                                </label>
                                <p class="form-control-plaintext mb-0">{{ e($viewEmployee->job_level ?? __('غير محدد')) }}</p>
                            </div>
                        </div>
                        @if ($viewEmployee->information)
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="form-label fw-bold text-muted small mb-1">
                                        <i class="fas fa-info-circle me-1 text-primary"></i>{{ __('معلومات إضافية') }}
                                    </label>
                                    <p class="form-control-plaintext mb-0">{!! nl2br(e($viewEmployee->information)) !!}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Employee Image Card --}}
        <div class="col-lg-4 col-md-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-gradient-secondary text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-image me-2"></i>{{ __('صورة الموظف') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $employeeImage = $viewEmployee->image_url;
                        $hasImage = $viewEmployee->hasMedia('employee_images') && $employeeImage;
                    @endphp
                    @if ($hasImage)
                        <div class="employee-image-wrapper position-relative d-inline-block">
                            <div id="image-loading-{{ $viewEmployee->id }}" 
                                 class="position-absolute top-50 start-50 translate-middle" 
                                 style="display: none; z-index: 10;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <img src="{{ $employeeImage }}"
                                 alt="{{ e($viewEmployee->name) }}"
                                 class="rounded-circle border-3 border-primary shadow-lg employee-image"
                                 style="width: 200px; height: 200px; object-fit: cover;"
                                 loading="lazy"
                                 onload="document.getElementById('image-loading-{{ $viewEmployee->id }}').style.display = 'none';"
                                 onerror="this.src='{{ asset('assets/images/avatar-placeholder.svg') }}'; document.getElementById('image-loading-{{ $viewEmployee->id }}').style.display = 'none';">
                            <div class="mt-3">
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>{{ __('صورة محفوظة') }}
                                </small>
                            </div>
                        </div>
                    @else
                        <img src="{{ asset('assets/images/avatar-placeholder.svg') }}"
                             alt="{{ e($viewEmployee->name) }}"
                             class="rounded-circle border-3 border-light shadow"
                             style="width: 200px; height: 200px; object-fit: cover;">
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-image me-1"></i>{{ __('لا توجد صورة محفوظة') }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

