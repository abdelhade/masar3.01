{{-- Employee View Partial --}}
<div class="container-fluid" style="direction: rtl;">
    @if ($viewEmployee)
        <div class="row">
            <!-- بيانات شخصية -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-user me-2"></i>{{ __('بيانات شخصية') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الاسم') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->name }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('البريد الإلكتروني') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->email }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('رقم الهاتف') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->phone }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('النوع') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->gender == 'male' ? __('ذكر') : ($viewEmployee->gender == 'female' ? __('أنثى') : __('غير محدد')) }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('تاريخ الميلاد') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->date_of_birth ? $viewEmployee->date_of_birth->format('Y-m-d') : __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الحالة') }}:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge {{ $viewEmployee->status == 'مفعل' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $viewEmployee->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('رقم الهوية') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->nationalId ?? __('غير محدد') }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الحالة الاجتماعية') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->marital_status ?? __('غير محدد') }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('مستوى التعليم') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->education ?? __('غير محدد') }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('المستوى الوظيفي') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->job_level ?? __('غير محدد') }}</p>
                            </div>
                        </div>
                        @if ($viewEmployee->information)
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">{{ __('معلومات إضافية') }}:</label>
                                <p class="form-control-plaintext">{{ $viewEmployee->information }}</p>
                            </div>
                        @endif
                        
                    </div>
                </div>
            </div>

            <!-- بيانات تفصيلية (الموقع) -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-map-marker-alt me-2"></i>{{ __('بيانات الموقع') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('البلد') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->country?->title ?? __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('المحافظة') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->state?->title ?? __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('المدينة') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->city?->title ?? __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('المنطقة') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->town?->title ?? __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Employee Image --}}
                <div class="card border-0 shadow-sm mb-3 mt-3 text-center">
                    <label class="form-label fw-bold text-dark">{{ __('صورة الموظف') }}:</label>
                    <div class="mt-2">
                        @php
                            $employeeImage = $viewEmployee->getFirstMediaUrlFixed('employee_images');
                            $hasImage = $viewEmployee->hasMedia('employee_images') && $employeeImage;
                        @endphp
                        @if ($hasImage)
                            <div id="employee-image-container" class="d-inline-block" style="position: relative;">
                                
                                <!-- Loading indicator -->
                                <div id="image-loading" style="display: block; text-align: center; padding: 20px;">
                                    <div class="spinner-border text-primary rounded-circle" role="status" style="width: 200px; height: 200px;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">جاري تحميل الصورة...</p>
                                </div>
                                <img id="employee-image" 
                                     src="{{ $employeeImage }}" 
                                     alt="{{ $viewEmployee->name }}" 
                                     class="rounded-circle border border-3 border-light shadow" 
                                     style="width: 200px; height: 200px; object-fit: cover; display: none;"
                                     onload="
                                        this.style.display = 'block';
                                        document.getElementById('image-loading').style.display = 'none';
                                        document.getElementById('image-placeholder').style.display = 'none';
                                        clearTimeout(window.imageLoadTimeout);
                                     "
                                     onerror="
                                        this.style.display = 'none';
                                        document.getElementById('image-loading').style.display = 'none';
                                        document.getElementById('image-placeholder').style.display = 'block';
                                        clearTimeout(window.imageLoadTimeout);
                                     ">
                                
                                <!-- Placeholder (shown on error) -->
                                <div id="image-placeholder" style="display: none; text-align: center;">
                                    <img src="{{ asset('assets/images/avatar-placeholder.svg') }}" 
                                         alt="{{ $viewEmployee->name }}" 
                                         class="rounded-circle border border-3 border-light shadow" 
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    {{ __('صورة محفوظة') }}
                                </small>
                            </div>
                            
                            <script>
                                // Set timeout for image loading (5 seconds)
                                window.imageLoadTimeout = setTimeout(function() {
                                    const loading = document.getElementById('image-loading');
                                    const placeholder = document.getElementById('image-placeholder');
                                    const image = document.getElementById('employee-image');
                                    
                                    if (loading && placeholder && image) {
                                        loading.style.display = 'none';
                                        placeholder.style.display = 'block';
                                        image.style.display = 'none';
                                    }
                                }, 5000);
                            </script>
                        @else
                            <img src="{{ asset('assets/images/avatar-placeholder.svg') }}" 
                                 alt="{{ $viewEmployee->name }}" 
                                 class="img-thumbnail rounded-circle border border-3 border-light shadow" 
                                 style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-image me-1"></i>
                                    {{ __('لا توجد صورة محفوظة') }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- بيانات وظيفة -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-briefcase me-2"></i>{{ __('بيانات الوظيفة') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الوظيفة') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->job?->title ?? __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('القسم') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->department?->title ?? __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الراتب') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->salary ? number_format($viewEmployee->salary, 2) . ' ر.س' : __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('نوع الاستحقاق') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->salary_type ?? __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('تاريخ التوظيف') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->date_of_hire ? $viewEmployee->date_of_hire->format('Y-m-d') : __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('تاريخ الانتهاء') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->date_of_fire ? $viewEmployee->date_of_fire->format('Y-m-d') : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بيانات المرتبات والحضور -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-money-bill-wave me-2"></i>{{ __('بيانات المرتبات والحضور') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الشيفت') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->shift ? $viewEmployee->shift->start_time . ' - ' . $viewEmployee->shift->end_time : __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('رقم البصمة') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->finger_print_id ?? __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الاسم في البصمة') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->finger_print_name ?? __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('باسورد الهاتف') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->password ? '********' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('الساعة الإضافي تحسب ك') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->additional_hour_calculation ? $viewEmployee->additional_hour_calculation . ' ساعة' : __('غير محدد') }}
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('اليوم الإضافي يحسب ك') }}:</label>
                                <p class="form-control-plaintext">
                                    {{ $viewEmployee->additional_day_calculation ? $viewEmployee->additional_day_calculation . ' يوم' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- معدلات الأداء -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-2">
                        <h6 class="card-title mb-0 fw-bold font-family-cairo">
                            <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($viewEmployee && $viewEmployee->kpis->count() > 0)
                            <div class="row g-3">
                                @foreach ($viewEmployee->kpis as $kpi)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-success h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title fw-bold text-success mb-0">
                                                        {{ $kpi->name }}
                                                    </h6>
                                                    <span class="badge bg-primary fs-6">
                                                        {{ $kpi->pivot->weight_percentage }}%
                                                    </span>
                                                </div>
                                                @if ($kpi->description)
                                                    <p class="card-text text-muted small mb-0">
                                                        {{ $kpi->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- مجموع الأوزان -->
                            <div class="mt-3">
                                @php
                                    $totalWeight = $viewEmployee->kpis->sum('pivot.weight_percentage');
                                @endphp
                                <div class="alert {{ $totalWeight == 100 ? 'alert-success' : 'alert-warning' }} mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">
                                            <i class="fas fa-calculator me-2"></i>{{ __('المجموع الكلي:') }}
                                        </span>
                                        <span class="badge {{ $totalWeight == 100 ? 'bg-success' : 'bg-warning' }} fs-5">
                                            {{ $totalWeight }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info text-center mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('لا توجد معدلات أداء محددة لهذا الموظف.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ __('لا توجد بيانات لعرضها.') }}
        </div>
    @endif
</div>
