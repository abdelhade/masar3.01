<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('hr.attendance_processing') }}</h4>
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
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                <div class="flex-grow-1">
                    @if (session('error_type') == 'overlap')
                        <pre class="mb-0"
                            dir="rtl"
                            style="white-space: pre-wrap; font-family: 'Cairo', sans-serif; font-size: 0.95rem; line-height: 1.6; text-align: right; direction: rtl; unicode-bidi: embed;">{{ session('error') }}</pre>
                    @else
                        {{ session('error') }}
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- Validation Errors Summary --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6><i class="fas fa-exclamation-circle"></i> {{ __('hr.please_correct_errors') }}</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Modern Processing Form --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                {{-- Enhanced Header --}}
                <div class="card-header bg-gradient-primary text-white border-0 py-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-white bg-opacity-20 rounded-circle p-2 me-3">
                            <i class="fas fa-cogs text-black fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ __('hr.new_processing') }}</h5>
                            <p class="card-subtitle mb-0 opacity-75 small">{{ __('hr.process_attendance') }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4" wire:loading.class="is-loading">
                    <form wire:submit.prevent="processAttendance" wire:loading.attr="disabled">
                        {{-- Main Form Section --}}
                        <div class="form-section" wire:loading.class="is-loading">
                            <div class="row g-4">
                                {{-- Processing Type with Enhanced Design --}}
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-filter text-primary me-2"></i>
                                            {{ __('hr.processing_type') }}
                                            <span class="text-danger ms-1">*</span>
                                        </label>
                                        <div class="form-control-wrapper">
                                            <select wire:model.live.debounce.300ms="processingType"
                                                class="form-select form-select-modern @error('processingType') is-invalid @enderror">
                                                <option value="single">
                                                    <i class="fas fa-user"></i> {{ __('hr.single_employee') }}
                                                </option>
                                                <option value="multiple">
                                                    <i class="fas fa-users"></i> {{ __('hr.multiple_employees') }}
                                                </option>
                                                <option value="department">
                                                    <i class="fas fa-building"></i> {{ __('hr.full_department') }}
                                                </option>
                                            </select>
                                            <div class="form-control-icon">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                        @error('processingType')
                                            <div class="invalid-feedback-modern">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Start Date Picker --}}
                                <div class="col-12 col-lg-3">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern mb-2">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            تاريخ البداية
                                            <span class="text-danger ms-1">*</span>
                                        </label>
                                        <div class="form-control-wrapper">
                                            <input type="date" wire:model="startDate"
                                                class="form-control form-control-modern @error('startDate') is-invalid @enderror"
                                                style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                            <div class="form-control-icon">
                                                <i class="fas fa-calendar"></i>
                                            </div>
                                        </div>
                                        @error('startDate')
                                            <div class="invalid-feedback-modern">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- End Date Picker --}}
                                <div class="col-12 col-lg-3">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern mb-2">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            تاريخ النهاية
                                            <span class="text-danger ms-1">*</span>
                                        </label>
                                        <div class="form-control-wrapper">
                                            <input type="date" wire:model="endDate"
                                                class="form-control form-control-modern @error('endDate') is-invalid @enderror"
                                                style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                            <div class="form-control-icon">
                                                <i class="fas fa-calendar"></i>
                                            </div>
                                        </div>
                                        @error('endDate')
                                            <div class="invalid-feedback-modern">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>


                            </div>

                            {{-- Selection Section with Smooth Transitions --}}
                            <div class="selection-section mt-4 pt-4 border-top border-light">
                                <div class="selection-wrapper" wire:key="selection-column-{{ $processingType }}">
                                    {{-- Content Container - Always present with consistent height --}}
                                    <div class="selection-content-container">
                                        {{-- Single Employee Selection --}}
                                        @if ($processingType === 'single')
                                            <div class="selection-content" wire:loading.remove
                                                wire:target="processingType">
                                                <div class="form-group-modern">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-user-check text-success me-2"></i>
                                                        اختيار الموظف
                                                        <span class="text-danger ms-1">*</span>
                                                    </label>
                                                    <div class="select-wrapper" wire:key="tom-select-single-employee">
                                                        <x-tom-select wireModel="selectedEmployee" :name="'selectedEmployee'"
                                                            :id="'selectedEmployee'" :required="true" :options="collect($employees)
                                                                ->map(function ($employee) {
                                                                    return [
                                                                        'id' => $employee->id,
                                                                        'text' =>
                                                                            $employee->name .
                                                                            ' - ' .
                                                                            ($employee->department?->title ??
                                                                                __('hr.no_department')),
                                                                    ];
                                                                })
                                                                ->toArray()"
                                                            :placeholder="__('hr.search_employee_placeholder')" :search="true" :create="false"
                                                            :multiple="false" :max-items="1" :max-options="1000"
                                                            :allow-empty-option="true" />
                                                    </div>
                                                    @error('selectedEmployee')
                                                        <div class="invalid-feedback-modern">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Multiple Employees Selection --}}
                                        @if ($processingType === 'multiple')
                                            <div class="selection-content" wire:loading.remove
                                                wire:target="processingType">
                                                <div class="form-group-modern">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-users text-info me-2"></i>
                                                        {{ __('hr.select_employees') }}
                                                        <span class="text-danger ms-1">*</span>
                                                        <span
                                                            class="badge bg-info bg-opacity-10 text-white ms-2 px-2 py-1 rounded-pill small">
                                                            متعدد
                                                            <i class="fas fa-users"></i>
                                                        </span>
                                                    </label>
                                                    <div class="select-wrapper"
                                                        wire:key="tom-select-multiple-employees">
                                                        <x-tom-select wireModel="selectedEmployees" :name="'selectedEmployees'"
                                                            :id="'selectedEmployees'" :required="true" :options="collect($employees)
                                                                ->map(function ($employee) {
                                                                    return [
                                                                        'id' => $employee->id,
                                                                        'text' =>
                                                                            $employee->name .
                                                                            ' - ' .
                                                                            ($employee->department?->title ??
                                                                                __('hr.no_department')),
                                                                    ];
                                                                })
                                                                ->toArray()"
                                                            :placeholder="'🔍 ابحث واختر موظفين متعددين...'" :search="true" :multiple="true"
                                                            :max-items="1000" :max-options="1000" />
                                                    </div>
                                                    @error('selectedEmployees')
                                                        <div class="invalid-feedback-modern">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Department Selection --}}
                                        @if ($processingType === 'department')
                                            <div class="selection-content" wire:loading.remove
                                                wire:target="processingType">
                                                <div class="form-group-modern">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-building text-warning me-2"></i>
                                                        اختيار القسم
                                                        <span class="text-danger ms-1">*</span>
                                                        <span
                                                            class="badge bg-warning bg-opacity-10 text-white ms-2 px-2 py-1 rounded-pill small">
                                                            قسم كامل
                                                            <i class="fas fa-building"></i>
                                                        </span>
                                                    </label>
                                                    <div class="select-wrapper">
                                                        <x-tom-select wireModel="selectedDepartment" :name="'selectedDepartment'"
                                                            :id="'selectedDepartment'" :required="true" :options="collect($departments)
                                                                ->map(function ($department) {
                                                                    return [
                                                                        'id' => $department->id,
                                                                        'text' => $department->title,
                                                                    ];
                                                                })
                                                                ->toArray()"
                                                            :placeholder="'🏢 اختر قسم...'" :search="true" :max-items="1"
                                                            :max-options="1000" />
                                                    </div>
                                                    @error('selectedDepartment')
                                                        <div class="invalid-feedback-modern">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Enhanced Loading Indicator --}}
                                        <div class="loading-overlay" wire:loading wire:target="processingType">
                                            <div class="loading-content">
                                                <div class="loading-spinner">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">جاري التحميل...</span>
                                                    </div>
                                                </div>
                                                <div class="loading-text mt-3">
                                                    <h6 class="mb-1">جاري تحديث النموذج...</h6>
                                                    <p class="text-muted small mb-0">يرجى الانتظار لحظة</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes Section --}}
                            <div class="notes-section mt-4 pt-4 border-top border-light">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="fas fa-sticky-note text-secondary me-2"></i>
                                        ملاحظات
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-white ms-2 px-2 py-1 rounded-pill small">
                                            اختياري
                                        </span>
                                    </label>
                                    <div class="form-control-wrapper">
                                        <textarea wire:model="notes" class="form-control form-control-modern @error('notes') is-invalid @enderror"
                                            rows="4" placeholder="{{ __('hr.add_notes_placeholder') }}"></textarea>
                                        <div class="form-control-icon textarea-icon">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                    </div>
                                    @error('notes')
                                        <div class="invalid-feedback-modern">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Action Section --}}
                            <div class="action-section mt-5 pt-4 border-top border-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    {{-- <div class="action-info">
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            سيتم معالجة البيانات وفقاً للإعدادات المحددة
                                        </p>
                                    </div> --}}
                                    <div class="action-buttons">
                                        <button type="submit"
                                            class="btn btn-main btn-lg px-4 py-3 rounded-pill shadow-sm"
                                            wire:loading.attr="disabled" wire:target="processAttendance">
                                            <span wire:loading.remove wire:target="processAttendance">
                                                <i class="fas fa-rocket me-2"></i>
                                                بدء المعالجة
                                            </span>
                                            <span wire:loading wire:target="processAttendance">
                                                <span class="spinner-border spinner-border-sm me-2" role="status">
                                                    <span class="visually-hidden">جاري المعالجة...</span>
                                                </span>
                                                جاري المعالجة...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                                    <th>أيام عمل فعليه أساسية</th>
                                    <th>أيام عمل فعليه إضافية</th>
                                    <th>أيام غياب</th>
                                    <th>ساعات تأخير</th>
                                    <th>ساعات إضافية</th>
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
                                            @if ($processing->employee)
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
                                        <td>{{ number_format($processing->actual_work_days, 2) }}</td>
                                        <td>{{ number_format($processing->overtime_work_days, 2) }}</td>
                                        <td>{{ number_format($processing->absent_days, 2) }}</td>
                                        <td>{{ formatHoursMinutes($processing->total_late_minutes / 60) }}</td>
                                        <td>{{ formatHoursMinutes($processing->overtime_work_minutes / 60) }}</td>
                                        <td>{{ number_format($processing->total_salary, 2) }}</td>
                                        <td>{!! $processing->status_badge !!}</td>
                                        <td>{{ $processing->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view Attendance Processing')
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        wire:click="viewProcessingDetails({{ $processing->id }})">
                                                        <i class="fas fa-eye"></i> {{ __('hr.details') }}
                                                    </button>
                                                @endcan
                                                @if ($processing->status === 'pending')
                                                    @can('create Attendance Approvals')
                                                        @if ($processing->total_salary > 0)
                                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                                wire:click="approveProcessing({{ $processing->id }})"
                                                                wire:confirm="{{ __('hr.confirm_approve_processing') }}">
                                                                <i class="fas fa-check"></i> {{ __('hr.approve') }}
                                                            </button>
                                                        @endif
                                                    @endcan
                                                    @can('create Attendance Rejections')
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            wire:click="rejectProcessing({{ $processing->id }})"
                                                            wire:confirm="{{ __('hr.confirm_reject_processing') }}">
                                                            <i class="fas fa-times"></i> {{ __('hr.reject') }}
                                                        </button>
                                                    @endcan
                                                @endif

                                                @if ($processing->status === 'pending' || $processing->status === 'rejected')
                                                    @can('delete Attendance Processing')
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            wire:click="deleteProcessing({{ $processing->id }})"
                                                            wire:confirm="{{ __('hr.confirm_delete_processing') }}"
                                                            title="{{ __('hr.delete') }}">
                                                            <i class="fas fa-trash"></i> {{ __('hr.delete') }}
                                                        </button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">لا توجد معالجات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $this->processings->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Processing Details Modal --}}
    @if ($showDetails && $selectedProcessing)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content d-flex flex-column" style="height: 100vh;">
                    {{-- Compact Header --}}
                    <div class="modal-header py-2 px-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div class="d-flex align-items-center gap-3">
                                <!-- <h6 class="mb-0 fw-bold text-white">تفاصيل المعالجة #{{ $selectedProcessing->id }}</h6> -->
                                <div class="d-flex gap-3">
                                    <span class="text-muted">
                                        <i class="fas fa-user"></i> {{ $selectedProcessing->employee?->name ?? __('hr.multiple') }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="fas fa-calendar"></i> {{ $selectedProcessing->period_start->format('Y-m-d') }} - {{ $selectedProcessing->period_end->format('Y-m-d') }}
                                    </span>
                                    <span>{!! $selectedProcessing->status_badge !!}</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" wire:click="closeDetails"></button>
                        </div>
                    </div>

                    {{-- Main Content Area - Maximized for Days Table --}}
                    <div class="modal-body p-2 d-flex flex-column" style="flex: 1; overflow: hidden;">
                        {{-- Daily Details - Takes Maximum Space --}}
                        <div class="flex-grow-1 d-flex flex-column" style="min-height: 0;">
                            <div class="table-responsive flex-grow-1" style="overflow-y: auto;">
                                <table class="table table-sm table-hover table-bordered mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light sticky-top" style="z-index: 10;">
                                        <tr>
                                            <th class="py-1 px-2" style="white-space: nowrap;">التاريخ</th>
                                            <th class="py-1 px-2" style="white-space: nowrap;">الحالة</th>
                                            <th class="py-1 px-2" style="white-space: nowrap;">نوع اليوم</th>
                                            <th class="py-1 px-2" style="white-space: nowrap;">المشروع</th>
                                            <th class="py-1 px-2" style="white-space: nowrap;">وقت الدخول</th>
                                            <th class="py-1 px-2" style="white-space: nowrap;">وقت الخروج</th>
                                            <th class="py-1 px-2 text-end" style="white-space: nowrap;">ساعات أساسية</th>
                                            <th class="py-1 px-2 text-end" style="white-space: nowrap;">ساعات فعلية</th>
                                            <th class="py-1 px-2 text-end" style="white-space: nowrap;">ساعات إضافية</th>
                                            <th class="py-1 px-2 text-end" style="white-space: nowrap;">ساعات تأخير</th>
                                            <th class="py-1 px-2 text-end" style="white-space: nowrap;">الراتب اليومي</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($processingDetails as $detail)
                                            <tr>
                                                <td class="py-1 px-2">{{ $detail->attendance_date->format('Y-m-d') }}</td>
                                                <td class="py-1 px-2">{!! $detail->status_badge !!}</td>
                                                <td class="py-1 px-2">{!! $detail->working_day_badge !!}</td>
                                                <td class="py-1 px-2">{{ $detail->project_code }}</td>
                                                <td class="py-1 px-2">{{ $detail->formatted_check_in_time }}</td>
                                                <td class="py-1 px-2">{{ $detail->formatted_check_out_time }}</td>
                                                <td class="py-1 px-2 text-end">{{ number_format($detail->attendance_basic_hours_count, 2) }}</td>
                                                <td class="py-1 px-2 text-end">{{ number_format($detail->attendance_actual_hours_count, 2) }}</td>
                                                <td class="py-1 px-2 text-end">{{ formatHoursMinutes($detail->attendance_overtime_minutes_count / 60) }}</td>
                                                <td class="py-1 px-2 text-end">{{ formatHoursMinutes($detail->attendance_late_minutes_count / 60) }}</td>
                                                <td class="py-1 px-2 text-end fw-bold">{{ number_format($detail->total_due_hourly_salary, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Compact Summary Section - Collapsible --}}
                        <div class="border-top mt-2 pt-2" style="flex-shrink: 0;">
                            <div class="accordion accordion-flush" id="summaryAccordion">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="summaryHeading">
                                        <button class="accordion-button collapsed py-1 px-2 d-flex align-items-center gap-3 w-100" type="button" data-bs-toggle="collapse" data-bs-target="#summaryCollapse" aria-expanded="false" aria-controls="summaryCollapse" style="font-size: 0.85rem;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calculator me-2 text-primary"></i>
                                                <span class="fw-bold">ملخص الخصومات والمكافآت والسلف</span>
                                            </div>
                                            @if(!empty($finalBalance))
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted d-none d-md-inline" style="font-size: 0.75rem;">
                                                        (راتب المدة: <span class="fw-bold">{{ number_format($finalBalance['net_period_salary'], 2) }}</span>)
                                                        @if($finalBalance['rewards_settled'] > 0 || $finalBalance['deductions_settled'] > 0 || $finalBalance['advances_settled'] > 0)
                                                            <span class="text-success ms-1">تسويات: {{ number_format($finalBalance['rewards_settled'] - $finalBalance['deductions_settled'] - $finalBalance['advances_settled'], 2) }}</span>
                                                        @endif
                                                    </span>
                                                    <span class="badge bg-primary px-3 py-2" style="font-size: 0.85rem; border-radius: 50px;">
                                                        صافي نهائي: {{ number_format($finalBalance['final_net'] ?? 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="summaryCollapse" class="accordion-collapse collapse" aria-labelledby="summaryHeading" data-bs-parent="#summaryAccordion">
                                        <div class="accordion-body p-2" style="max-height: 300px; overflow-y: auto;">
                                            <div class="row g-2">
                                                {{-- Deductions --}}
                                                @if(!empty($deductionsRewardsSummary) && isset($deductionsRewardsSummary['deductions']) && $deductionsRewardsSummary['deductions'] && $deductionsRewardsSummary['deductions']->count() > 0)
                                                <div class="col-md-4">
                                                    <div class="card border-danger mb-2">
                                                        <div class="card-header bg-danger bg-opacity-10 py-1 px-2">
                                                            <small class="text-danger fw-bold" style="font-size: 0.75rem;"><i class="fas fa-minus-circle"></i> الخصومات</small>
                                                        </div>
                                                        <div class="card-body p-1">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.75rem;">
                                                                    <tbody>
                                                                        @foreach($deductionsRewardsSummary['deductions'] as $deduction)
                                                                        <tr>
                                                                            <td class="py-0 px-1">{{ $deduction->date->format('Y-m-d') }}</td>
                                                                            <td class="py-0 px-1 text-truncate" style="max-width: 80px;" title="{{ $deduction->reason }}">{{ $deduction->reason }}</td>
                                                                            <td class="py-0 px-1 text-end">{{ number_format($deduction->amount, 2) }}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                    <tfoot class="table-light">
                                                                        <tr>
                                                                            <td colspan="2" class="py-1 px-1 fw-bold">الإجمالي</td>
                                                                            <td class="py-1 px-1 fw-bold text-end">{{ number_format($deductionsRewardsSummary['total_deductions'], 2) }}</td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                {{-- Rewards --}}
                                                @if(!empty($deductionsRewardsSummary) && isset($deductionsRewardsSummary['rewards']) && $deductionsRewardsSummary['rewards'] && $deductionsRewardsSummary['rewards']->count() > 0)
                                                <div class="col-md-4">
                                                    <div class="card border-success mb-2">
                                                        <div class="card-header bg-success bg-opacity-10 py-1 px-2">
                                                            <small class="text-success fw-bold" style="font-size: 0.75rem;"><i class="fas fa-plus-circle"></i> المكافآت</small>
                                                        </div>
                                                        <div class="card-body p-1">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.75rem;">
                                                                    <tbody>
                                                                        @foreach($deductionsRewardsSummary['rewards'] as $reward)
                                                                        <tr>
                                                                            <td class="py-0 px-1">{{ $reward->date->format('Y-m-d') }}</td>
                                                                            <td class="py-0 px-1 text-truncate" style="max-width: 80px;" title="{{ $reward->reason }}">{{ $reward->reason }}</td>
                                                                            <td class="py-0 px-1 text-end">{{ number_format($reward->amount, 2) }}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                    <tfoot class="table-light">
                                                                        <tr>
                                                                            <td colspan="2" class="py-1 px-1 fw-bold">الإجمالي</td>
                                                                            <td class="py-1 px-1 fw-bold text-end">{{ number_format($deductionsRewardsSummary['total_rewards'], 2) }}</td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                {{-- Advances --}}
                                                @if($advancesSummary && $advancesSummary->count() > 0)
                                                <div class="col-md-4">
                                                    <div class="card border-warning mb-2">
                                                        <div class="card-header bg-warning bg-opacity-10 py-1 px-2">
                                                            <small class="text-warning fw-bold" style="font-size: 0.75rem;"><i class="fas fa-hand-holding-usd"></i> السلف</small>
                                                        </div>
                                                        <div class="card-body p-1">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.75rem;">
                                                                    <tbody>
                                                                        @foreach($advancesSummary as $advance)
                                                                        <tr>
                                                                            <td class="py-0 px-1">{{ $advance->date->format('Y-m-d') }}</td>
                                                                            <td class="py-0 px-1 text-truncate" style="max-width: 80px;" title="{{ $advance->reason }}">{{ $advance->reason }}</td>
                                                                            <td class="py-0 px-1 text-end">{{ number_format($advance->amount, 2) }}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                    <tfoot class="table-light">
                                                                        <tr>
                                                                            <td colspan="2" class="py-1 px-1 fw-bold">الإجمالي</td>
                                                                            <td class="py-1 px-1 fw-bold text-end">{{ number_format($advancesSummary->sum('amount'), 2) }}</td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>

                                            {{-- Final Balance Summary --}}
                                            @if(!empty($finalBalance))
                                            <div class="card border-primary mt-2">
                                                <div class="card-header bg-primary bg-opacity-10 py-2 px-3">
                                                    <h6 class="mb-0 text-primary fw-bold" style="font-size: 0.9rem;"><i class="fas fa-balance-scale"></i> تفاصيل الراتب والمستحقات</h6>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="row g-4">
                                                        {{-- Salary Components (Period) --}}
                                                        <div class="col-md-6 border-end">
                                                            <h6 class="text-muted mb-3 border-bottom pb-2 fw-bold">إجمالي راتب المدة</h6>
                                                            
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-secondary small">الراتب الأساسي الشهري:</span>
                                                                <span class="text-muted small">{{ number_format($finalBalance['basic_salary'] ?? 0, 2) }}</span>
                                                            </div>

                                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-1 bg-light px-2 rounded">
                                                                <span class="text-primary fw-bold">راتب الحضور المستحق (أيام العمل):</span>
                                                                <strong class="text-primary">{{ number_format($finalBalance['salary_due'] ?? 0, 2) }}</strong>
                                                            </div>

                                                            <div class="d-flex justify-content-between mb-2 px-1">
                                                                <span class="text-success small">إضافي حضور:</span>
                                                                <strong class="text-success small">+{{ number_format($finalBalance['overtime_salary'] ?? 0, 2) }}</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2 px-1">
                                                                <span class="text-danger small">خصم حضور/غياب:</span>
                                                                <strong class="text-danger small">-{{ number_format($finalBalance['attendance_deductions'] ?? 0, 2) }}</strong>
                                                            </div>
                                                            
                                                            <div class="d-flex justify-content-between mt-3 pt-2 bg-primary bg-opacity-10 p-2 rounded border border-primary shadow-sm">
                                                                <span class="fw-bold fs-6">إجمالي مستحق راتب المدة:</span>
                                                                <strong class="text-white fs-5">{{ number_format($finalBalance['net_period_salary'] ?? 0, 2) }}</strong>
                                                            </div>
                                                            <small class="text-muted x-small d-block mt-1 text-center">(راتب الحضور + الإضافي - خصم الغياب)</small>
                                                        </div>

                                                        {{-- External Balances & Settlement --}}
                                                        <div class="col-md-6">
                                                            <h6 class="text-muted mb-3 border-bottom pb-2 fw-bold">تسوية المستحقات والسلف</h6>
                                                            
                                                            {{-- Rewards --}}
                                                            <div class="mb-4 bg-light bg-opacity-50 p-2 rounded border" x-data="{ amount: 0 }">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="fw-bold text-success"><i class="las la-gift"></i> المكافآت المسددة:</span>
                                                                    <strong class="text-success fs-6">{{ number_format($finalBalance['rewards_settled'] ?? 0, 2) }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                     <small class="text-muted">المكافآت المتبقية (غير مسدد): <span class="fw-bold text-dark">{{ number_format($finalBalance['rewards_remaining'] ?? 0, 2) }}</span></small>
                                                                </div>
                                                                @if(($finalBalance['rewards_remaining'] ?? 0) > 0)
                                                                    <div class="input-group input-group-sm mt-1 shadow-sm">
                                                                        <input type="number" step="0.01" class="form-control border-success text-center fw-bold" x-model="amount" placeholder="المبلغ">
                                                                        <button class="btn btn-success px-3" type="button" 
                                                                                @click="$wire.payRewards(amount).then(() => { amount = 0 })"
                                                                                wire:loading.attr="disabled">
                                                                            <i class="las la-money-bill-wave"></i> صرف
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Deductions --}}
                                                            <div class="mb-4 bg-light bg-opacity-50 p-2 rounded border" x-data="{ amount: 0 }">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="fw-bold text-danger"><i class="las la-exclamation-circle"></i> الخصومات المسددة:</span>
                                                                    <strong class="text-danger fs-6">{{ number_format($finalBalance['deductions_settled'] ?? 0, 2) }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                     <small class="text-muted">الخصومات المتبقية (غير مسدد): <span class="fw-bold text-dark">{{ number_format($finalBalance['deductions_remaining'] ?? 0, 2) }}</span></small>
                                                                </div>
                                                                @if(($finalBalance['deductions_remaining'] ?? 0) > 0)
                                                                    <div class="input-group input-group-sm mt-1 shadow-sm">
                                                                        <input type="number" step="0.01" class="form-control border-danger text-center fw-bold" x-model="amount" placeholder="المبلغ">
                                                                        <button class="btn btn-danger px-3" type="button" 
                                                                                @click="$wire.applyDeductions(amount).then(() => { amount = 0 })"
                                                                                wire:loading.attr="disabled">
                                                                            <i class="las la-minus-circle"></i> خصم
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Advances --}}
                                                            <div class="mb-3 bg-light bg-opacity-50 p-2 rounded border" x-data="{ amount: 0 }">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="fw-bold text-warning"><i class="las la-hand-holding-usd text-dark"></i> السلف المسددة:</span>
                                                                    <strong class="text-danger fs-6">{{ number_format($finalBalance['advances_settled'] ?? 0, 2) }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                     <small class="text-muted">السلف المتبقية (غير مسدد): <span class="fw-bold text-dark">{{ number_format($finalBalance['advances_remaining'] ?? 0, 2) }}</span></small>
                                                                </div>
                                                                @if(($finalBalance['advances_remaining'] ?? 0) > 0)
                                                                    <div class="input-group input-group-sm mt-1 shadow-sm">
                                                                        <input type="number" step="0.01" class="form-control border-warning text-center fw-bold" x-model="amount" placeholder="المبلغ">
                                                                        <button class="btn btn-warning px-3 text-dark fw-bold" type="button" 
                                                                                @click="$wire.settleAdvance(amount).then(() => { amount = 0 })"
                                                                                wire:loading.attr="disabled">
                                                                            <i class="las la-file-invoice-dollar"></i> سداد
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Compact Footer in the center --}}
                    <div class="modal-footer py-1 px-3 border-top justify-content-center">
                        <button type="button" class="btn btn-sm btn-secondary" wire:click="closeDetails">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Custom Styles for Modern UI --}}
    <style>
        /* Modern Form Styling */
        .card {
            border-radius: 16px;
            overflow: hidden;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .icon-circle {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-wrapper .progress {
            border-radius: 10px;
            background-color: #f8f9fa;
        }

        .progress-bar {
            border-radius: 10px;
        }

        .form-group-modern {
            position: relative;
        }

        .form-label-modern {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .form-label-small {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }

        .form-control-wrapper {
            position: relative;
        }

        .form-control-modern,
        .form-select-modern {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 50px 14px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .form-control-modern:focus,
        .form-select-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background-color: #fff;
        }

        .form-control-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            pointer-events: none;
        }

        .textarea-icon {
            top: 20px;
            transform: none;
        }

        .invalid-feedback-modern {
            display: block;
            width: 100%;
            margin-top: 8px;
            font-size: 0.85rem;
            color: #e53e3e;
            background-color: #fed7d7;
            padding: 8px 12px;
            border-radius: 8px;
            border-left: 4px solid #e53e3e;
        }

        .date-range-wrapper {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .selection-section {
            background: #f8fafc;
            padding: 24px;
            border-radius: 12px;
            margin: 0 -1rem;
        }

        .selection-content-container {
            position: relative;
            min-height: 120px;
        }

        .selection-content {
            animation: fadeInUp 0.4s ease-out;
            min-height: 120px;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(248, 250, 252, 0.9);
            border-radius: 12px;
            backdrop-filter: blur(5px);
            z-index: 10;
            min-height: 120px;
        }

        /* Ensure form structure consistency during submission */
        .form-section {
            position: relative;
        }

        .form-section.is-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .form-section.is-loading * {
            pointer-events: none;
        }

        /* Prevent layout shifts during form submission */
        .card-body {
            position: relative;
        }

        .card-body.is-loading {
            overflow: hidden;
        }

        .loading-content {
            text-align: center;
        }

        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .notes-section textarea {
            resize: vertical;
            min-height: 100px;
        }

        .action-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 0 -1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tom Select Styling */
        .ts-control {
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 8px 12px !important;
            background-color: #f8fafc !important;
            min-height: 52px !important;
        }

        .ts-control.focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
            background-color: #fff !important;
        }

        .ts-dropdown {
            border-radius: 12px !important;
            border: 2px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .date-range-wrapper,
            .selection-section,
            .action-section {
                margin: 0;
                border-radius: 8px;
            }

            .form-control-modern,
            .form-select-modern {
                padding: 12px 40px 12px 12px;
            }

            .btn-primary {
                width: 100%;
                margin-top: 1rem;
            }

            .action-section .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .action-info {
                margin-bottom: 1rem;
            }
        }

        /* Modal Optimization Styles */
        .modal-fullscreen .modal-content {
            border-radius: 0;
        }

        .modal-fullscreen .modal-body {
            padding: 0.5rem;
        }

        .modal-fullscreen .table {
            margin-bottom: 0;
        }

        .modal-fullscreen .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .modal-fullscreen .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .modal-fullscreen .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .modal-fullscreen .table tbody td {
            vertical-align: middle;
        }

        /* Compact accordion */
        .accordion-button {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
        }

        /* Scrollbar styling */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</div>
