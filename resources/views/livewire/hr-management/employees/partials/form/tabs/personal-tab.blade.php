{{-- Personal Information Tab --}}
<div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold">
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
                    <h6 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas fa-image me-2"></i>{{ __('الصورة الشخصية') }}
                    </h6>
                </div>
                <div class="card-body text-center py-3">
                    <div class="mb-3">
                        <div class="position-relative d-inline-block">
                            <img id="employee-image-preview" 
                                 src="{{ asset('assets/images/avatar-placeholder.svg') }}"
                                 alt="{{ __('صورة الموظف') }}"
                                 class="rounded-circle border border-3 border-light shadow"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="file" id="employee-image-input" class="form-control" wire:model="image" 
                               onchange="handleEmployeeImageChange(this)" accept="image/jpeg,image/png,image/jpg,image/gif">
                        
                        <!-- File Info -->
                        <div id="file-info" class="alert alert-info py-2 mt-2" style="font-size: 0.85rem; display: none;">
                            <i class="fas fa-file-image me-1"></i>
                            <span id="file-name"></span>
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
                        <select class="form-select font-hold fw-bold" wire:model.defer="status">
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

