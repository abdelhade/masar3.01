<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">إنشاء معالجة جديدة</h4>
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

    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>{{ session('warning') }}</strong>
            @if (session()->has('error_details'))
                <div class="mt-3">
                    <ul class="mb-0 ps-4" style="list-style-type: disc;">
                        @foreach (session('error_details') as $error)
                            <li class="mb-2">{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>{{ session('error') }}</strong>
            @if (session()->has('error_details'))
                <div class="mt-3">
                    <ul class="mb-0 ps-4" style="list-style-type: disc;">
                        @foreach (session('error_details') as $error)
                            <li class="mb-2">{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus"></i> معالجة جديدة
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="store">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">نوع المعالجة <span class="text-danger">*</span></label>
                                <select wire:model.live="processingType" class="form-select @error('processingType') is-invalid @enderror">
                                    <option value="single">موظف واحد</option>
                                    <option value="department">قسم كامل</option>
                                </select>
                                @error('processingType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($this->processingType === 'single')
                            <div class="col-md-6">
                                <label class="form-label">الموظف <span class="text-danger">*</span></label>
                                <select wire:model.live="selectedEmployee" class="form-select @error('selectedEmployee') is-invalid @enderror">
                                    <option value="">اختر الموظف</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedEmployee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @else
                            <div class="col-md-6">
                                <label class="form-label">القسم <span class="text-danger">*</span></label>
                                <select wire:model.live="selectedDepartment" class="form-select @error('selectedDepartment') is-invalid @enderror">
                                    <option value="">اختر القسم</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->title }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDepartment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" wire:model="startDate" class="form-control @error('startDate') is-invalid @enderror">
                                @error('startDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                                <input type="date" wire:model="endDate" class="form-control @error('endDate') is-invalid @enderror">
                                @error('endDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">ملاحظات</label>
                                <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                            </div>

                            @if($this->processingType === 'department' && $this->selectedDepartment)
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">إدخال عدد الساعات لكل موظف</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>اسم الموظف</th>
                                                        <th>الراتب الثابت</th>
                                                        <th>أجر الساعة</th>
                                                        <th>عدد الساعات <span class="text-danger">*</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($employees as $employee)
                                                        <tr>
                                                            <td>{{ $employee->name }}</td>
                                                            <td>{{ number_format($employee->salary, 2) }}</td>
                                                            <td>{{ number_format($employee->flexible_hourly_wage ?? 0, 2) }}</td>
                                                            <td>
                                                                <input type="number" 
                                                                    wire:model="employeeHours.{{ $employee->id }}" 
                                                                    step="0.01" 
                                                                    min="0" 
                                                                    class="form-control form-control-sm @error('employeeHours.'.$employee->id) is-invalid @enderror"
                                                                    placeholder="0.00">
                                                                @error('employeeHours.'.$employee->id)
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($this->processingType === 'single' && $this->selectedEmployee)
                            <div class="col-12">
                                @php
                                    $employee = $employees->firstWhere('id', $this->selectedEmployee);
                                @endphp
                                @if($employee)
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">إدخال عدد الساعات للموظف</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>اسم الموظف:</strong> {{ $employee->name }}</p>
                                                <p><strong>الراتب الثابت:</strong> {{ number_format($employee->salary, 2) }} ريال</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>أجر الساعة:</strong> {{ number_format($employee->flexible_hourly_wage ?? 0, 2) }} ريال</p>
                                                <div class="mb-3">
                                                    <label class="form-label">عدد الساعات <span class="text-danger">*</span></label>
                                                    <input type="number" 
                                                        wire:model.defer="employeeHours.{{ $employee->id }}" 
                                                        step="0.01" 
                                                        min="0" 
                                                        class="form-control @error('employeeHours.'.$employee->id) is-invalid @enderror"
                                                        placeholder="0.00">
                                                    @error('employeeHours.'.$employee->id)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="fas fa-save"></i> حفظ
                                    </span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        جاري المعالجة...
                                    </span>
                                </button>
                                <a href="{{ route('flexible-salary.processing.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

