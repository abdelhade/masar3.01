<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">معالجة دفاتر الحضور</h4>
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
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Validation Errors Summary --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6><i class="fas fa-exclamation-circle"></i> يرجى تصحيح الأخطاء التالية:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Processing Form --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">معالجة جديدة</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="processAttendance" wire:loading.attr="disabled" wire:loading.disable>
                        <div class="row">
                            {{-- Processing Type --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">نوع المعالجة <span class="text-danger">*</span></label>
                                <select wire:model.live.debounce.300ms="processingType" class="form-select @error('processingType') is-invalid @enderror">
                                    <option value="single">موظف واحد</option>
                                    <option value="multiple">عدة موظفين</option>
                                    <option value="department">قسم كامل</option>
                                </select>
                                @error('processingType') 
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                    </div> 
                                @enderror
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" wire:model="startDate" class="form-control @error('startDate') is-invalid @enderror">
                                @error('startDate') 
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                    </div> 
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                                <input type="date" wire:model="endDate" class="form-control @error('endDate') is-invalid @enderror">
                                @error('endDate') 
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                    </div> 
                                @enderror
                            </div>
                        </div>

                        <div class="row" wire:key="selection-row-{{ $processingType }}">
                            {{-- Single Employee Selection --}}
                            <div class="col-md-6 mb-3" wire:key="single-employee-{{ $processingType }}">
                                @if($processingType === 'single')
                                    <div wire:loading.remove>
                                        <label class="form-label">اختيار الموظف <span class="text-danger">*</span></label>
                                        <select wire:model="selectedEmployee" class="form-select @error('selectedEmployee') is-invalid @enderror">
                                            <option value="">اختر موظف...</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->name }} - {{ $employee->department?->title ?? 'بدون قسم' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selectedEmployee') 
                                            <div class="invalid-feedback d-block">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                            </div> 
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Multiple Employees Selection --}}
                            <div class="col-md-6 mb-3" wire:key="multiple-employees-{{ $processingType }}">
                                @if($processingType === 'multiple')
                                    <div wire:loading.remove>
                                        <label class="form-label">اختيار الموظفين <span class="text-danger">*</span></label>
                                        <select wire:model="selectedEmployees" class="form-select @error('selectedEmployees') is-invalid @enderror" multiple size="6">
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->name }} - {{ $employee->department?->title ?? 'بدون قسم' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">اضغط Ctrl + Click لاختيار أكثر من موظف</small>
                                        @error('selectedEmployees') 
                                            <div class="invalid-feedback d-block">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                            </div> 
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Department Selection --}}
                            <div class="col-md-6 mb-3" wire:key="department-{{ $processingType }}">
                                @if($processingType === 'department')
                                    <div wire:loading.remove>
                                        <label class="form-label">اختيار القسم <span class="text-danger">*</span></label>
                                        <select wire:model="selectedDepartment" class="form-select @error('selectedDepartment') is-invalid @enderror">
                                            <option value="">اختر قسم...</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedDepartment') 
                                            <div class="invalid-feedback d-block">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                            </div> 
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Loading Indicator --}}
                            <div wire:loading class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">جاري التحميل...</span>
                                    </div>
                                    <span>جاري تحديث النموذج...</span>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea wire:model="notes" class="form-control" rows="3" placeholder="ملاحظات اختيارية..."></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:loading.disable wire:target="processAttendance"
                            wire:click.prevent="processAttendance">
                                <span wire:loading.remove wire:target="processAttendance">
                                    <i class="fas fa-play"></i> بدء المعالجة
                                </span>
                                <span wire:loading wire:target="processAttendance">
                                    <i class="fas fa-spinner fa-spin"></i> جاري المعالجة...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    {{-- Processing History --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">سجل المعالجات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>رقم المعالجة</th>
                                    <th>النوع</th>
                                    <th>الموظف/القسم</th>
                                    <th>الفترة</th>
                                    <th>إجمالي الراتب</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processings as $processing)
                                    <tr>
                                        <td>#{{ $processing->id }}</td>
                                        <td>{{ $processing->type_label }}</td>
                                        <td>
                                            @if($processing->employee)
                                                {{ $processing->employee->name }}
                                            @elseif($processing->department)
                                                {{ $processing->department->title }}
                                            @else
                                                متعدد
                                            @endif
                                        </td>
                                        <td>
                                            {{ $processing->period_start->format('Y-m-d') }} - 
                                            {{ $processing->period_end->format('Y-m-d') }}
                                        </td>
                                        <td>{{ number_format($processing->total_salary, 2) }}</td>
                                        <td>{!! $processing->status_badge !!}</td>
                                        <td>{{ $processing->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        wire:click="viewProcessingDetails({{ $processing->id }})">
                                                    <i class="fas fa-eye"></i> التفاصيل
                                                </button>
                                                
                                                @if($processing->status === 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            wire:click="approveProcessing({{ $processing->id }})"
                                                            onclick="return confirm('هل أنت متأكد من اعتماد هذه المعالجة؟')">
                                                        <i class="fas fa-check"></i> اعتماد
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            wire:click="rejectProcessing({{ $processing->id }})"
                                                            onclick="return confirm('هل أنت متأكد من رفض هذه المعالجة؟')">
                                                        <i class="fas fa-times"></i> رفض
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">لا توجد معالجات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Processing Details Modal --}}
    @if($showDetails && $selectedProcessing)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تفاصيل المعالجة #{{ $selectedProcessing->id }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDetails"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Processing Summary --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong>الموظف:</strong><br>
                                {{ $selectedProcessing->employee?->name ?? 'متعدد' }}
                            </div>
                            <div class="col-md-3">
                                <strong>القسم:</strong><br>
                                {{ $selectedProcessing->department?->title ?? 'متعدد' }}
                            </div>
                            <div class="col-md-3">
                                <strong>الفترة:</strong><br>
                                {{ $selectedProcessing->period_start->format('Y-m-d') }} - {{ $selectedProcessing->period_end->format('Y-m-d') }}
                            </div>
                            <div class="col-md-3">
                                <strong>الحالة:</strong><br>
                                {!! $selectedProcessing->status_badge !!}
                            </div>
                        </div>

                        {{-- Daily Details --}}
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                        <th>نوع اليوم</th>
                                        <th>وقت الدخول</th>
                                        <th>وقت الخروج</th>
                                        <th>ساعات أساسية</th>
                                        <th>ساعات فعلية</th>
                                        <th>ساعات إضافية</th>
                                        <th>ساعات تأخير</th>
                                        <th>الراتب اليومي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($processingDetails as $detail)
                                        <tr>
                                            <td>{{ $detail->attendance_date->format('Y-m-d') }}</td>
                                            <td>{!! $detail->status_badge !!}</td>
                                            <td>{!! $detail->working_day_badge !!}</td>
                                            <td>{{ $detail->formatted_check_in_time }}</td>
                                            <td>{{ $detail->formatted_check_out_time }}</td>
                                            <td>{{ number_format($detail->attendance_basic_hours_count, 2) }}</td>
                                            <td>{{ number_format($detail->attendance_actual_hours_count, 2) }}</td>
                                            <td>{{ number_format($detail->attendance_overtime_hours_count, 2) }}</td>
                                            <td>{{ number_format($detail->attendance_late_hours_count, 2) }}</td>
                                            <td>{{ number_format($detail->total_due_hourly_salary, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetails">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>