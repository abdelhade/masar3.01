{{-- Job Tab --}}
<div x-show="activeTab === 'job'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-warning text-white py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold">
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
                    <h6 class="card-title mb-0 font-hold fw-bold">
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

