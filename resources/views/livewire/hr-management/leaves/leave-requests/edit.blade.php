<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تعديل طلب الإجازة</h3>
                    <div class="card-tools">
                        <a href="{{ route('leaves.requests.show', $request->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            العودة للتفاصيل
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <!-- رسائل الخطأ العامة -->
                        @if($errors->has('general'))
                            <div class="alert alert-danger">
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        <!-- معلومات الطلب الحالي -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> معلومات الطلب الحالي</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>الموظف:</strong> {{ $request->employee->name }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>نوع الإجازة:</strong> {{ $request->leaveType->name }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>الحالة:</strong> 
                                            <span class="badge {{ $request->status === 'draft' ? 'bg-secondary' : ($request->status === 'submitted' ? 'bg-warning' : 'bg-success') }}">
                                                {{ $request->status === 'draft' ? 'مسودة' : ($request->status === 'submitted' ? 'مقدم' : 'معتمد') }}
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>المدة الحالية:</strong> {{ number_format($request->duration_days, 1) }} يوم
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- الموظف -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                                    <select wire:model.live="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror">
                                        <option value="">اختر الموظف</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ $employee_id == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- نوع الإجازة -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="leave_type_id" class="form-label">نوع الإجازة <span class="text-danger">*</span></label>
                                    <select wire:model.live="leave_type_id" id="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror">
                                        <option value="">اختر نوع الإجازة</option>
                                        @foreach($leaveTypes as $type)
                                            <option value="{{ $type->id }}" {{ $leave_type_id == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('leave_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- تاريخ البداية -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date" class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           wire:model.live="start_date" 
                                           id="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- تاريخ النهاية -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date" class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           wire:model.live="end_date" 
                                           id="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- السبب -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="reason" class="form-label">سبب الإجازة</label>
                                    <textarea wire:model="reason" 
                                              id="reason"
                                              rows="3" 
                                              class="form-control @error('reason') is-invalid @enderror"
                                              placeholder="أدخل سبب الإجازة...">{{ $reason }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- معلومات إضافية -->
                        @if($selectedEmployeeBalance)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">معلومات الرصيد</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-primary">{{ number_format($available_balance, 1) }}</h4>
                                                        <p class="text-muted mb-0">الرصيد المتاح</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-info">{{ number_format($calculated_days, 1) }}</h4>
                                                        <p class="text-muted mb-0">المدة المطلوبة</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h4 class="text-{{ $available_balance >= $calculated_days ? 'success' : 'danger' }}">
                                                            {{ number_format($available_balance - $calculated_days, 1) }}
                                                        </h4>
                                                        <p class="text-muted mb-0">الرصيد المتبقي</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        @if($overlaps_attendance)
                                                            <div class="alert alert-warning mb-0">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                يوجد تداخل مع الحضور
                                                            </div>
                                                        @else
                                                            <div class="alert alert-success mb-0">
                                                                <i class="fas fa-check"></i>
                                                                لا يوجد تداخل
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- أزرار الإجراءات -->
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('leaves.requests.show', $request->id) }}" class="btn btn-secondary">
                                    إلغاء
                                </a>
                                <button type="submit" 
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="fas fa-save"></i>
                                        حفظ التعديلات
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
