{{-- Attendance Tab --}}
<div x-show="activeTab === 'attendance'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold">
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
                    <h6 class="card-title mb-0 font-hold fw-bold">
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
                                <button class="btn btn-outline-secondary" type="button" 
                                        @click="togglePassword()"
                                        data-bs-toggle="tooltip" 
                                        :title="showPassword ? '{{ __('إخفاء كلمة المرور') }}' : '{{ __('إظهار كلمة المرور') }}'">
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

