
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $balance ? 'تعديل رصيد الإجازة' : 'إضافة رصيد إجازة جديد' }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('leaves.balances.index') }}" class="btn btn-secondary font-family-cairo fw-bold">
                                <i class="fas fa-arrow-left"></i>
                                العودة للقائمة
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit="save">
                            <!-- رسائل الخطأ العامة -->
                            @if($errors->has('general'))
                                <div class="alert alert-danger font-family-cairo fw-bold">
                                    {{ $errors->first('general') }}
                                </div>
                            @endif

                            <div class="row">
                                <!-- الموظف -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                                        <select wire:model="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror font-family-cairo fw-bold font-14">
                                            <option value="">اختر الموظف</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <div class="invalid-feedback font-family-cairo fw-bold">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- نوع الإجازة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="leave_type_id" class="form-label">نوع الإجازة <span class="text-danger">*</span></label>
                                        <select wire:model="leave_type_id" id="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror font-family-cairo fw-bold font-14">
                                            <option value="">اختر نوع الإجازة</option>
                                            @foreach($leaveTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('leave_type_id')
                                            <div class="invalid-feedback font-family-cairo fw-bold">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- السنة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="year" class="form-label">السنة <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               wire:model="year" 
                                               id="year"
                                               min="2020" 
                                               max="2030" 
                                               class="form-control @error('year') is-invalid @enderror">
                                        @error('year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- الرصيد الافتتاحي -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="opening_balance_days" class="form-label">الرصيد الافتتاحي (أيام) <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               wire:model="opening_balance_days" 
                                               id="opening_balance_days"
                                               step="0.5" 
                                               min="0" 
                                               class="form-control @error('opening_balance_days') is-invalid @enderror">
                                        @error('opening_balance_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- الأيام المتراكمة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="accrued_days" class="form-label">الأيام المتراكمة <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               wire:model="accrued_days" 
                                               id="accrued_days"
                                               step="0.5" 
                                               min="0" 
                                               class="form-control @error('accrued_days') is-invalid @enderror">
                                        @error('accrued_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- الأيام المستخدمة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="used_days" class="form-label">الأيام المستخدمة <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               wire:model="used_days" 
                                               id="used_days"
                                               step="0.5" 
                                               min="0" 
                                               class="form-control @error('used_days') is-invalid @enderror">
                                        @error('used_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- الأيام المعلقة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pending_days" class="form-label">الأيام المعلقة <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               wire:model="pending_days" 
                                               id="pending_days"
                                               step="0.5" 
                                               min="0" 
                                               class="form-control @error('pending_days') is-invalid @enderror">
                                        @error('pending_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- الأيام المحولة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="carried_over_days" class="form-label">الأيام المحولة <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               wire:model="carried_over_days" 
                                               id="carried_over_days"
                                               step="0.5" 
                                               min="0" 
                                               class="form-control @error('carried_over_days') is-invalid @enderror">
                                        @error('carried_over_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- ملاحظات -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">ملاحظات</label>
                                        <textarea wire:model="notes" 
                                                  id="notes"
                                                  rows="3" 
                                                  class="form-control @error('notes') is-invalid @enderror"
                                                  placeholder="أضف ملاحظات إضافية..."></textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- ملخص الرصيد -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">ملخص الرصيد</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <div class="border-end">
                                                        <h3 class="text-primary">{{ number_format($this->remaining_days, 1) }}</h3>
                                                        <p class="text-muted mb-0">الأيام المتبقية</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="border-end">
                                                        <h3 class="text-success">{{ number_format($this->opening_balance_days + $this->accrued_days + $this->carried_over_days, 1) }}</h3>
                                                        <p class="text-muted mb-0">إجمالي الرصيد</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <h3 class="text-danger">{{ number_format($this->used_days + $this->pending_days, 1) }}</h3>
                                                    <p class="text-muted mb-0">إجمالي المستخدم</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- أزرار الإجراءات -->
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <a href="{{ route('leaves.balances.index') }}" class="btn btn-secondary">
                                        إلغاء
                                    </a>
                                    <button type="submit" 
                                            class="btn btn-primary"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            {{ $balance ? 'تحديث' : 'حفظ' }}
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