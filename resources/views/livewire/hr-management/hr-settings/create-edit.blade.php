<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title font-hold fw-bold">{{ __('hr.edit_hr_setting') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('hr.settings.index') }}" class="btn btn-secondary font-hold fw-bold">
                            <i class="fas fa-arrow-left"></i>
                            {{ __('hr.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <!-- رسائل الخطأ العامة -->
                        @if($errors->has('general'))
                            <div class="alert alert-danger font-hold fw-bold">
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        <div class="row">
                            <!-- النسبة المئوية القصوى للشركة -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="company_max_leave_percentage" class="form-label">{{ __('hr.company_max_leave_percentage') }} (%) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           wire:model.blur="company_max_leave_percentage" 
                                           id="company_max_leave_percentage"
                                           step="0.01" 
                                           min="0" 
                                           max="100"
                                           class="form-control @error('company_max_leave_percentage') is-invalid @enderror font-hold fw-bold">
                                    <small class="form-text text-muted font-hold">{{ __('hr.company_max_leave_percentage_help') }}</small>
                                    @error('company_max_leave_percentage')
                                        <div class="invalid-feedback font-hold fw-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- معلومات إضافية -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info font-hold">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>{{ __('hr.note') }}:</strong> {{ __('hr.company_max_leave_percentage_note') }}
                                </div>
                            </div>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('hr.settings.index') }}" class="btn btn-secondary font-hold fw-bold">
                                    إلغاء
                                </a>
                                <button type="submit" 
                                        class="btn btn-main font-hold fw-bold"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        {{ $setting ? 'تحديث' : 'حفظ' }}
                                    </span>
                                    <span wire:loading>
                                        <i class="fas fa-spinner fa-spin"></i>
                                        جاري الحفظ...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
