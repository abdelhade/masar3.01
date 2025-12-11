{{-- Job Tab --}}
<div>
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
                         <!-- Department -->
                         <div class="row gx-4 g-1">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('القسم') }}</label>
                                <select class="form-select" wire:model.blur="department_id">
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
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark">{{ __('مدير القسم') }}</label>
                                <input type="text" class="form-control" value="{{ $departments?->find($department_id)?->director?->name }}" disabled readonly>
                            </div>
                        </div>
                        {{-- Line Manager --}}
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">{{ __('المدير المباشر') }}</label>
                            <select class="form-select" wire:model.defer="line_manager_id">
                                <option value="">{{ __('اختر المدير المباشر') }}</option>
                                    @foreach ($this->lineManagers as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('line_manager_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <!-- Job -->
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
                        <!-- Job Level -->
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

