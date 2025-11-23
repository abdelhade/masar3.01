{{-- Employee Form with Alpine.js Tabs --}}
<div class="container-fluid" style="direction: rtl;">
    <!-- Navigation Tabs - Alpine.js -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'personal' }"
                    @click="switchTab('personal')"
                    type="button">
                <i class="fas fa-user me-2"></i>{{ __('البيانات الشخصية') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'location' }"
                    @click="switchTab('location')"
                    type="button">
                <i class="fas fa-map-marker-alt me-2"></i>{{ __('الموقع') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'job' }"
                    @click="switchTab('job')"
                    type="button">
                <i class="fas fa-briefcase me-2"></i>{{ __('الوظيفة') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'salary' }"
                    @click="switchTab('salary')"
                    type="button">
                <i class="fas fa-money-bill-wave me-2"></i>{{ __('المرتبات') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'attendance' }"
                    @click="switchTab('attendance')"
                    type="button">
                <i class="fas fa-clock me-2"></i>{{ __('الحضور') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'kpi' }"
                    @click="switchTab('kpi')"
                    type="button">
                <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'Accounting' }"
                    @click="switchTab('Accounting')"
                    type="button">
                <i class="fas fa-chart-line me-2"></i>{{ __('الحسابات') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-family-cairo fw-bold"
                    :class="{ 'active': activeTab === 'leaveBalances' }"
                    @click="switchTab('leaveBalances')"
                    type="button">
                <i class="fas fa-calendar-check me-2"></i>{{ __('رصيد الإجازات') }}
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Personal Information Tab -->
        <div x-show="activeTab === 'personal'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-user me-2"></i>{{ __('البيانات الأساسية') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('الاسم الكامل') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.defer="name"
                                        placeholder="{{ __('أدخل الاسم الكامل') }}">
                                    @error('name')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('البريد الإلكتروني') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" wire:model.defer="email"
                                        placeholder="{{ __('أدخل البريد الإلكتروني') }}">
                                    @error('email')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('رقم الهاتف') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.defer="phone"
                                        placeholder="{{ __('أدخل رقم الهاتف') }}">
                                    @error('phone')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('رقم الهوية') }}</label>
                                    <input type="text" class="form-control" wire:model.defer="nationalId"
                                        placeholder="{{ __('أدخل رقم الهوية') }}">
                                    @error('nationalId')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('النوع') }}</label>
                                    <select class="form-select" wire:model.defer="gender">
                                        <option value="">{{ __('اختر النوع') }}</option>
                                        <option value="male">{{ __('ذكر') }}</option>
                                        <option value="female">{{ __('أنثى') }}</option>
                                    </select>
                                    @error('gender')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('تاريخ الميلاد') }}</label>
                                    <input type="date" class="form-control" wire:model.defer="date_of_birth">
                                    @error('date_of_birth')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('hr.marital_status') }}</label>
                                    <select class="form-select" wire:model.defer="marital_status">
                                        <option value="">{{ __('hr.select_marital_status') }}</option>
                                        <option value="single">{{ __('hr.single') }}</option>
                                        <option value="married">{{ __('hr.married') }}</option>
                                        <option value="divorced">{{ __('hr.divorced') }}</option>
                                        <option value="widowed">{{ __('hr.widowed') }}</option>
                                    </select>
                                    @error('marital_status')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('hr.education_level') }}</label>
                                    <select class="form-select" wire:model.defer="education">
                                        <option value="">{{ __('hr.select_education_level') }}</option>
                                        <option value="diploma">{{ __('hr.diploma') }}</option>
                                        <option value="bachelor">{{ __('hr.bachelor') }}</option>
                                        <option value="master">{{ __('hr.master') }}</option>
                                        <option value="doctorate">{{ __('hr.doctorate') }}</option>
                                    </select>
                                    @error('education')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('معلومات إضافية') }}</label>
                                    <textarea class="form-control" rows="3" wire:model.defer="information"
                                        placeholder="{{ __('أدخل أي معلومات إضافية...') }}"></textarea>
                                    @error('information')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-success text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-image me-2"></i>{{ __('الصورة الشخصية') }}
                            </h6>
                        </div>
                        <div class="card-body text-center py-3">
                            <div class="mb-3">
                                <div class="position-relative d-inline-block">
                                    <template x-if="imagePreview">
                                        <img :src="imagePreview"
                                            alt="{{ __('صورة الموظف') }}"
                                            class="rounded-circle border border-3 border-light shadow"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    </template>
                                    <template x-if="!imagePreview && isEdit && currentImageUrl">
                                        <div style="position: relative;">
                                            <img :src="currentImageUrl"
                                                alt="{{ __('صورة الموظف') }}"
                                                class="rounded-circle border border-3 border-light shadow"
                                                style="width: 120px; height: 120px; object-fit: cover; display: none;"
                                                x-on:load="
                                                    $el.style.display = 'block';
                                                    $el.nextElementSibling.style.display = 'none';
                                                    $el.nextElementSibling.nextElementSibling.style.display = 'none';
                                                "
                                                x-on:error="
                                                    $el.style.display = 'none';
                                                    $el.nextElementSibling.style.display = 'none';
                                                    $el.nextElementSibling.nextElementSibling.style.display = 'block';
                                                ">
                                            
                                            <!-- Loading indicator -->
                                            <div style="display: block; text-align: center; padding: 20px;">
                                                <div class="spinner-border text-primary rounded-circle" role="status" style="width: 120px; height: 120px;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Placeholder (shown on error) -->
                                            <div style="display: none; text-align: center;">
                                                <img src="{{ asset('assets/images/avatar-placeholder.svg') }}"
                                                    alt="{{ __('صورة الموظف') }}"
                                                    class="rounded-circle border border-3 border-light shadow"
                                                    style="width: 120px; height: 120px; object-fit: cover;">
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!imagePreview && (!isEdit || !currentImageUrl)">
                                        <img src="{{ asset('assets/images/avatar-placeholder.svg') }}"
                                            alt="{{ __('صورة الموظف') }}"
                                            class="rounded-circle border border-3 border-light shadow"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    </template>
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="file" class="form-control" wire:model="image" 
                                       @change="handleImageChange($event)" accept="image/jpeg,image/png,image/jpg,image/gif">
                                
                                <!-- File Info -->
                                <div x-show="selectedFileName" x-transition class="alert alert-info py-2 mt-2" style="font-size: 0.85rem;">
                                    <i class="fas fa-file-image me-1"></i>
                                    <span x-text="selectedFileName"></span>
                                </div>

                                <!-- Upload Progress -->
                                <div wire:loading wire:target="image" class="progress mt-2" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                         role="progressbar" style="width: 100%">
                                        <span class="fw-bold">{{ __('جاري الرفع...') }}</span>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('الحد الأقصى: 2 ميجابايت') }} | {{ __('الأنواع: JPG, PNG, GIF') }}
                                </small>

                                @error('image')
                                    <div class="alert alert-danger py-2 mt-2" role="alert">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold text-dark">{{ __('حالة الموظف') }}
                                    <span class="text-danger">*</span></label>
                                <select class="form-select font-family-cairo fw-bold" wire:model.defer="status">
                                    <option value="مفعل">{{ __('مفعل') }}</option>
                                    <option value="معطل">{{ __('معطل') }}</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Location Tab -->
        <div x-show="activeTab === 'location'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-info text-white py-2">
                    <h6 class="card-title mb-0 font-family-cairo fw-bold">
                        <i class="fas fa-map-marker-alt me-2"></i>{{ __('الموقع الجغرافي') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">{{ __('البلد') }}</label>
                            <select class="form-select" wire:model.defer="country_id">
                                <option value="">{{ __('اختر البلد') }}</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->title }}</option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">{{ __('المحافظة') }}</label>
                            <select class="form-select" wire:model.defer="state_id">
                                <option value="">{{ __('اختر المحافظة') }}</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->title }}</option>
                                @endforeach
                            </select>
                            @error('state_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">{{ __('المدينة') }}</label>
                            <select class="form-select" wire:model.defer="city_id">
                                <option value="">{{ __('اختر المدينة') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->title }}</option>
                                @endforeach
                            </select>
                            @error('city_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">{{ __('المنطقة') }}</label>
                            <select class="form-select" wire:model.defer="town_id">
                                <option value="">{{ __('اختر المنطقة') }}</option>
                                @foreach ($towns as $town)
                                    <option value="{{ $town->id }}">{{ $town->title }}</option>
                                @endforeach
                            </select>
                            @error('town_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Job Tab -->
        <div x-show="activeTab === 'job'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-warning text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-briefcase me-2"></i>{{ __('الوظيفة والقسم') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('الوظيفة') }}</label>
                                    <select class="form-select" wire:model.defer="job_id">
                                        <option value="">{{ __('اختر الوظيفة') }}</option>
                                        @foreach ($jobs as $job)
                                            <option value="{{ $job->id }}">{{ $job->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('job_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('القسم') }}</label>
                                    <select class="form-select" wire:model.defer="department_id">
                                        <option value="">{{ __('اختر القسم') }}</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('المستوى الوظيفي') }}</label>
                                    <select class="form-select" wire:model.defer="job_level">
                                        <option value="">{{ __('اختر المستوى') }}</option>
                                        <option value="مبتدئ">{{ __('مبتدئ') }}</option>
                                        <option value="متوسط">{{ __('متوسط') }}</option>
                                        <option value="محترف">{{ __('محترف') }}</option>
                                    </select>
                                    @error('job_level')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-secondary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-calendar-alt me-2"></i>{{ __('تواريخ التوظيف') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('تاريخ التوظيف') }}</label>
                                    <input type="date" class="form-control" wire:model.defer="date_of_hire">
                                    @error('date_of_hire')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('تاريخ الانتهاء') }}</label>
                                    <input type="date" class="form-control" wire:model.defer="date_of_fire">
                                    @error('date_of_fire')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Salary Tab -->
        <div x-show="activeTab === 'salary'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-success text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-money-bill-wave me-2"></i>{{ __('المرتب الأساسي') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('المرتب') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">ر.س</span>
                                        <input type="number" class="form-control" wire:model.defer="salary"
                                            placeholder="0.00" step="0.01">
                                    </div>
                                    @error('salary')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('نوع الاستحقاق') }}</label>
                                    <select class="form-select" wire:model.defer="salary_type">
                                        <option value="">{{ __('اختر نوع الاستحقاق') }}</option>
                                        <option value="ساعات عمل فقط">{{ __('ساعات عمل فقط') }}</option>
                                        <option value="ساعات عمل و إضافي يومى">{{ __('ساعات عمل و إضافي يومى') }}</option>
                                        <option value="ساعات عمل و إضافي للمده">{{ __('ساعات عمل و إضافي للمده') }}</option>
                                        <option value="حضور فقط">{{ __('حضور فقط') }}</option>
                                        <option value="إنتاج فقط">{{ __('إنتاج فقط') }}</option>
                                    </select>
                                    @error('salary_type')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-info text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-calculator me-2"></i>{{ __('حسابات إضافية') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('الساعة الإضافي تحسب ك') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            wire:model.defer="additional_hour_calculation" placeholder="0.00" step="0.01">
                                        <span class="input-group-text">ساعة</span>
                                    </div>
                                    @error('additional_hour_calculation')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('اليوم الإضافي يحسب ك') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            wire:model.defer="additional_day_calculation" placeholder="0.00" step="0.01">
                                        <span class="input-group-text">يوم</span>
                                    </div>
                                    @error('additional_day_calculation')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('الساعة المتأخرة تحسب ك') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            wire:model.defer="late_hour_calculation" placeholder="0.00" step="1">
                                        <span class="input-group-text">ساعة</span>
                                    </div>
                                    @error('late_hour_calculation')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('اليوم الغياب يحسب ك') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            wire:model.defer="late_day_calculation" placeholder="0.00" step="1">
                                        <span class="input-group-text">يوم</span>
                                    </div>
                                    @error('late_day_calculation')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Attendance Tab -->
        <div x-show="activeTab === 'attendance'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-clock me-2"></i>{{ __('نظام الحضور') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('الشيفت') }}</label>
                                    <select class="form-select fw-bold" wire:model.defer="shift_id">
                                        <option value="">{{ __('اختر الشيفت') }}</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}">
                                              {{ $shift->shift_type  }} | {{ $shift->start_time }} - {{ $shift->end_time }} | {{ $shift->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('رقم البصمة') }}</label>
                                    <input type="number" class="form-control" wire:model.defer="finger_print_id"
                                        placeholder="{{ __('أدخل رقم البصمة') }}" min="0">
                                    @error('finger_print_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('الاسم في البصمة') }}</label>
                                    <input type="text" class="form-control" wire:model.defer="finger_print_name"
                                        placeholder="{{ __('أدخل الاسم في البصمة') }}">
                                    @error('finger_print_name')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-warning text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-mobile-alt me-2"></i>{{ __('نظام الهاتف المحمول') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('باسورد الهاتف') }}</label>
                                    <div class="input-group">
                                        <input :type="showPassword ? 'text' : 'password'" class="form-control"
                                            wire:model.defer="password" 
                                            :placeholder="$wire.isEdit ? '{{ __('اتركه فارغاً للحفاظ على الباسورد الحالي') }}' : '{{ __('أدخل باسورد الهاتف') }}'">
                                        <button class="btn btn-outline-secondary" type="button" @click="togglePassword()">
                                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- KPI Tab -->
        <div x-show="activeTab === 'kpi'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-family-cairo fw-bold">
                        <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء للموظف') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <!-- إضافة معدل أداء جديد -->
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold text-primary">
                                <i class="fas fa-plus me-2"></i>{{ __('إضافة معدل أداء جديد') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-dark">{{ __('اختر معدل الأداء') }}
                                        <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <input type="text" class="form-control"
                                                :value="selectedKpiId ? getKpiName(selectedKpiId) : kpiSearch"
                                                @input="kpiSearch = $event.target.value; selectedKpiId = ''; kpiSearchOpen = true"
                                                @click="kpiSearchOpen = true"
                                                @keydown.escape="kpiSearchOpen = false"
                                                @keydown.arrow-down.prevent="navigateKpiDown()"
                                                @keydown.arrow-up.prevent="navigateKpiUp()"
                                                @keydown.enter.prevent="selectCurrentKpi()"
                                                :placeholder="selectedKpiId ? '' : '{{ __('ابحث عن معدل الأداء...') }}'"
                                                autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button"
                                                @click="kpiSearchOpen = !kpiSearchOpen">
                                                <i class="fas" :class="kpiSearchOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" type="button"
                                                x-show="selectedKpiId"
                                                @click="clearKpiSelection()"
                                                title="{{ __('مسح الاختيار') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <!-- Dropdown Results -->
                                        <div x-show="kpiSearchOpen && filteredKpis.length > 0"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1"
                                            style="z-index: 1000; max-height: 250px; overflow-y: auto;"
                                            @click.away="kpiSearchOpen = false">
                                            <template x-for="(kpi, index) in filteredKpis" :key="kpi.id">
                                                <div class="p-2 border-bottom cursor-pointer"
                                                    @click="selectKpi(kpi)"
                                                    :class="kpiSearchIndex === index ? 'bg-primary text-white' : 'hover-bg-light'"
                                                    style="cursor: pointer;">
                                                    <div class="fw-bold" x-text="kpi.name"></div>
                                                    <small x-text="kpi.description" x-show="kpi.description"></small>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- No Results -->
                                        <div x-show="kpiSearchOpen && kpiSearch && filteredKpis.length === 0"
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 p-3 text-center text-muted"
                                            style="z-index: 1000;">
                                            <i class="fas fa-search me-2"></i>{{ __('لا توجد نتائج') }}
                                        </div>
                                    </div>
                                    @error('selected_kpi_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary btn-lg w-100"
                                        @click="$wire.addKpi()" wire:loading.attr="disabled" 
                                        :disabled="!selectedKpiId">
                                        <span wire:loading.remove wire:target="addKpi">
                                            <i class="fas fa-plus me-2"></i>{{ __('إضافة') }}
                                        </span>
                                        <span wire:loading wire:target="addKpi">
                                            <i class="fas fa-spinner fa-spin me-2"></i>{{ __('جاري الإضافة...') }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معدلات الأداء المضافة -->
                    <template x-if="kpiIds.length > 0">
                        <div>
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fas fa-list me-2"></i>{{ __('معدلات الأداء المضافة') }}
                            </h6>
                            <div class="row g-3 mb-3">
                                <template x-for="kpiId in kpiIds" :key="kpiId">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card border-success h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title fw-bold text-success mb-1"
                                                            x-text="getKpiName(kpiId)"></h6>
                                                        <small class="text-muted" x-text="getKpiDescription(kpiId)"></small>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                        @click="$wire.removeKpi(kpiId)" title="{{ __('حذف') }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="form-label fw-bold text-dark small">{{ __('الوزن النسبي') }}</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" :value="kpiWeights[kpiId] || 0"
                                                            @input="kpiWeights[kpiId] = parseInt($event.target.value) || 0"
                                                            min="0" max="100" step="1">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- مؤشر المجموع -->
                            <div class="alert py-2"
                                :class="{
                                    'alert-success': totalKpiWeight === 100,
                                    'alert-danger': totalKpiWeight > 100,
                                    'alert-warning': totalKpiWeight < 100
                                }">
                                <i class="fas me-2"
                                    :class="{
                                        'fa-check-circle': totalKpiWeight === 100,
                                        'fa-times-circle': totalKpiWeight > 100,
                                        'fa-exclamation-triangle': totalKpiWeight < 100
                                    }"></i>
                                <span x-text="weightMessage"></span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="card shadow-sm"
                                :class="{
                                    'border-success': totalKpiWeight === 100,
                                    'border-danger': totalKpiWeight > 100,
                                    'border-warning': totalKpiWeight < 100
                                }">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="card-title fw-bold mb-0"
                                            :class="{
                                                'text-success': totalKpiWeight === 100,
                                                'text-danger': totalKpiWeight > 100,
                                                'text-warning': totalKpiWeight < 100
                                            }">
                                            <i class="fas fa-calculator me-2"></i>{{ __('المجموع الحالي للأوزان') }}
                                        </h6>
                                        <span class="badge text-white"
                                            :class="{
                                                'bg-success': totalKpiWeight === 100,
                                                'bg-danger': totalKpiWeight > 100,
                                                'bg-warning': totalKpiWeight < 100
                                            }"
                                            x-text="totalKpiWeight + '%'"></span>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar"
                                            :style="'width: ' + Math.min(totalKpiWeight, 100) + '%'"
                                            :class="{
                                                'bg-success': totalKpiWeight === 100,
                                                'bg-danger': totalKpiWeight > 100,
                                                'bg-warning': totalKpiWeight < 100
                                            }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- رسالة عند عدم وجود KPIs -->
                    <template x-if="kpiIds.length === 0">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('لم يتم إضافة أي معدلات أداء بعد. استخدم النموذج أعلاه لإضافة معدلات الأداء.') }}
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <!-- Accounting Tab -->
        <div x-show="activeTab === 'Accounting'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            {{--  Accounting --}}
            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-money-bill-wave me-2"></i>{{ __('الحسابات') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('يتبع للحساب') }}</label>
                                    <select class="form-select" wire:model.live="salary_basic_account_id">
                                        <option value="">{{ __('اختر الحساب الرئيسي للمرتب') }}</option>
                                        @foreach ($salary_basic_accounts as $key => $account)
                                            <option value="{{ $account['id'] }}">{{ $account['code'] }} - {{ $account['aname'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('salary_basic_account_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- accounting balance --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold">
                                <i class="fas fa-money-bill-wave me-2"></i>{{ __('الأرصده') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark">{{ __('الرصيد الأفتتاحى') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">ر.س</span>
                                        <input type="number" class="form-control" wire:model.defer="opening_balance" onclick="this.select()"
                                            placeholder="0.00" step="0.5">
                                    </div>
                                    @error('opening_balance')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Leave Balances Tab -->
        <div x-show="activeTab === 'leaveBalances'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-family-cairo fw-bold">
                        <i class="fas fa-calendar-check me-2"></i>{{ __('رصيد الإجازات للموظف') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <!-- إضافة رصيد إجازة جديد -->
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 font-family-cairo fw-bold text-primary">
                                <i class="fas fa-plus me-2"></i>{{ __('إضافة رصيد إجازة جديد') }}
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-dark">{{ __('اختر نوع الإجازة') }}
                                        <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <input type="text" class="form-control"
                                                :value="selectedLeaveTypeId ? getLeaveTypeName(selectedLeaveTypeId) : leaveTypeSearch"
                                                @input="leaveTypeSearch = $event.target.value; selectedLeaveTypeId = ''; leaveTypeSearchOpen = true"
                                                @click="leaveTypeSearchOpen = true"
                                                @keydown.escape="leaveTypeSearchOpen = false"
                                                @keydown.arrow-down.prevent="navigateLeaveTypeDown()"
                                                @keydown.arrow-up.prevent="navigateLeaveTypeUp()"
                                                @keydown.enter.prevent="selectCurrentLeaveType()"
                                                :placeholder="selectedLeaveTypeId ? '' : '{{ __('ابحث عن نوع الإجازة...') }}'"
                                                autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button"
                                                @click="leaveTypeSearchOpen = !leaveTypeSearchOpen">
                                                <i class="fas" :class="leaveTypeSearchOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" type="button"
                                                x-show="selectedLeaveTypeId"
                                                @click="clearLeaveTypeSelection()"
                                                title="{{ __('مسح الاختيار') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <!-- Dropdown Results -->
                                        <div x-show="leaveTypeSearchOpen && filteredLeaveTypes.length > 0"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1"
                                            style="z-index: 1000; max-height: 250px; overflow-y: auto;"
                                            @click.away="leaveTypeSearchOpen = false">
                                            <template x-for="(leaveType, index) in filteredLeaveTypes" :key="leaveType.id">
                                                <div class="p-2 border-bottom cursor-pointer"
                                                    @click="selectLeaveType(leaveType)"
                                                    :class="leaveTypeSearchIndex === index ? 'bg-primary text-white' : 'hover-bg-light'"
                                                    style="cursor: pointer;">
                                                    <div class="fw-bold" x-text="leaveType.name"></div>
                                                    <small class="text-muted" x-show="leaveType.code" x-text="'الكود: ' + leaveType.code"></small>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- No Results -->
                                        <div x-show="leaveTypeSearchOpen && leaveTypeSearch && filteredLeaveTypes.length === 0"
                                            class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 p-3 text-center text-muted"
                                            style="z-index: 1000;">
                                            <i class="fas fa-search me-2"></i>{{ __('لا توجد نتائج') }}
                                        </div>
                                    </div>
                                    @error('selected_leave_type_id')
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary btn-lg w-100"
                                        @click="$wire.addLeaveBalance()" wire:loading.attr="disabled" 
                                        :disabled="!selectedLeaveTypeId">
                                        <span wire:loading.remove wire:target="addLeaveBalance">
                                            <i class="fas fa-plus me-2"></i>{{ __('إضافة') }}
                                        </span>
                                        <span wire:loading wire:target="addLeaveBalance">
                                            <i class="fas fa-spinner fa-spin me-2"></i>{{ __('جاري الإضافة...') }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- أرصدة الإجازات المضافة -->
                    <template x-if="leaveBalanceIds.length > 0">
                        <div>
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fas fa-list me-2"></i>{{ __('أرصدة الإجازات المضافة') }}
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center fw-bold">{{ __('نوع الإجازة') }}</th>
                                            <th class="text-center fw-bold">{{ __('السنة') }} <span class="text-danger">*</span></th>
                                            <th class="text-center fw-bold">{{ __('الرصيد الافتتاحي') }}</th>
                                            <th class="text-center fw-bold">{{ __('المتراكمة') }}</th>
                                            <th class="text-center fw-bold">{{ __('المستخدمة') }}</th>
                                            <th class="text-center fw-bold">{{ __('المعلقة') }}</th>
                                            <th class="text-center fw-bold">{{ __('المحولة') }}</th>
                                            <th class="text-center fw-bold">{{ __('المتبقي') }}</th>
                                            <th class="text-center fw-bold">{{ __('ملاحظات') }}</th>
                                            <th class="text-center fw-bold">{{ __('إجراءات') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(balanceKey, index) in Object.keys(leaveBalances)" :key="balanceKey">
                                            <tr>
                                                <td class="align-middle">
                                                    <span class="fw-bold text-success" x-text="getLeaveTypeName(leaveBalances[balanceKey].leave_type_id)"></span>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        :value="leaveBalances[balanceKey].year || ''"
                                                        @input="leaveBalances[balanceKey].year = parseInt($event.target.value) || ''"
                                                        min="2020" max="2030" placeholder="2024">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        :value="leaveBalances[balanceKey].opening_balance_days || ''"
                                                        @input="leaveBalances[balanceKey].opening_balance_days = parseFloat($event.target.value) || 0"
                                                        step="1" min="0" placeholder="0">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        :value="leaveBalances[balanceKey].accrued_days || ''"
                                                        @input="leaveBalances[balanceKey].accrued_days = parseFloat($event.target.value) || 0"
                                                        step="1" min="0" placeholder="0">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        :value="leaveBalances[balanceKey].used_days || ''"
                                                        @input="leaveBalances[balanceKey].used_days = parseFloat($event.target.value) || 0"
                                                        step="1" min="0" placeholder="0">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        :value="leaveBalances[balanceKey].pending_days || ''"
                                                        @input="leaveBalances[balanceKey].pending_days = parseFloat($event.target.value) || 0"
                                                        step="1" min="0" placeholder="0">
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        :value="leaveBalances[balanceKey].carried_over_days || ''"
                                                        @input="leaveBalances[balanceKey].carried_over_days = parseFloat($event.target.value) || 0"
                                                        step="1" min="0" placeholder="0">
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="badge fw-bold"
                                                        :class="calculateRemainingDays(leaveBalances[balanceKey]) >= 0 ? 'bg-success' : 'bg-danger'"
                                                        x-text="calculateRemainingDays(leaveBalances[balanceKey])"></span>
                                                </td>
                                                <td class="align-middle">
                                                    <textarea class="form-control form-control-sm" rows="1"
                                                        :value="leaveBalances[balanceKey].notes || ''"
                                                        @input="leaveBalances[balanceKey].notes = $event.target.value"
                                                        placeholder="{{ __('أضف ملاحظات..') }}"></textarea>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                        @click="$wire.removeLeaveBalance(balanceKey)" title="{{ __('حذف') }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- رسالة عند عدم وجود أرصدة -->
                    <template x-if="leaveBalanceIds.length === 0">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('لم يتم إضافة أي رصيد إجازة بعد. استخدم النموذج أعلاه لإضافة رصيد إجازة.') }}
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
