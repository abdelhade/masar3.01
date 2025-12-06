<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">تعديل المعالجة</h4>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> تعديل المعالجة
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="update">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>الموظف:</strong></label>
                                <p class="form-control-plaintext">{{ $processing->employee->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>الفترة:</strong></label>
                                <p class="form-control-plaintext">{{ $processing->period_start->format('Y-m-d') }} - {{ $processing->period_end->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>الراتب الثابت:</strong></label>
                                <p class="form-control-plaintext">{{ number_format($processing->fixed_salary, 2) }} ريال</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>أجر الساعة:</strong></label>
                                <p class="form-control-plaintext">{{ number_format($processing->hourly_wage, 2) }} ريال</p>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">عدد الساعات <span class="text-danger">*</span></label>
                            <input type="number" 
                                wire:model="editingHoursWorked" 
                                step="0.01" 
                                min="0.01" 
                                class="form-control @error('editingHoursWorked') is-invalid @enderror"
                                placeholder="0.00">
                            @error('editingHoursWorked')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea wire:model.defer="editingNotes" class="form-control" rows="3" placeholder="أدخل ملاحظات إضافية..."></textarea>
                        </div>
                        @if($editingHoursWorked > 0)
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>راتب الساعات:</strong> {{ number_format($editingHoursWorked * $processing->hourly_wage, 2) }} ريال
                                </div>
                                <div class="col-md-6">
                                    <strong>إجمالي الراتب:</strong> {{ number_format($processing->fixed_salary + ($editingHoursWorked * $processing->hourly_wage), 2) }} ريال
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-save"></i> حفظ التعديلات
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    جاري الحفظ...
                                </span>
                            </button>
                            <a href="{{ route('flexible-salary.processing.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

