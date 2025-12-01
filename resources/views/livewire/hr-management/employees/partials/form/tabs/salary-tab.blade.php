{{-- Salary Tab --}}
<div x-show="activeTab === 'salary'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-success text-white py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold">
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
                    <h6 class="card-title mb-0 font-hold fw-bold">
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

