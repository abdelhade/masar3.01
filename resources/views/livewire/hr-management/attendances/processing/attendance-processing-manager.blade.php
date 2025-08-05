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
                            <h5 class="card-title mb-1 fw-bold">معالجة جديدة</h5>
                            <p class="card-subtitle mb-0 opacity-75 small">قم بمعالجة الحضور والانصراف للموظفين</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form wire:submit.prevent="processAttendance" wire:loading.attr="disabled">
                        {{-- Step Indicator --}}
                        <div class="progress-wrapper mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill text-white">
                                    <i class="fas fa-info-circle me-1"></i>
                                    خطوة 1 من 2: إعداد المعالجة
                                </span>
                            </div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: 50%"></div>
                            </div>
                        </div>

                        {{-- Main Form Section --}}
                        <div class="form-section">
                            <div class="row g-4">
                                {{-- Processing Type with Enhanced Design --}}
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">
                                            <i class="fas fa-filter text-primary me-2"></i>
                                            نوع المعالجة 
                                            <span class="text-danger ms-1">*</span>
                                        </label>
                                        <div class="form-control-wrapper">
                                            <select wire:model.live.debounce.300ms="processingType" 
                                                    class="form-select form-select-modern @error('processingType') is-invalid @enderror">
                                                <option value="single">
                                                    <i class="fas fa-user"></i> موظف واحد
                                                </option>
                                                <option value="multiple">
                                                    <i class="fas fa-users"></i> عدة موظفين
                                                </option>
                                                <option value="department">
                                                    <i class="fas fa-building"></i> قسم كامل
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
                                            <input type="date" 
                                                wire:model="startDate" 
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
                                            <input type="date" 
                                                wire:model="endDate" 
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
                                        @if($processingType === 'single')
                                            <div class="selection-content" wire:loading.remove wire:target="processingType">
                                                <div class="form-group-modern">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-user-check text-success me-2"></i>
                                                        اختيار الموظف 
                                                        <span class="text-danger ms-1">*</span>
                                                    </label>
                                                    <div class="select-wrapper" wire:key="tom-select-single-employee">
                                                        <x-tom-select
                                                            wireModel="selectedEmployee" 
                                                            :name="'selectedEmployee'"
                                                            :id="'selectedEmployee'"
                                                            :required="true"
                                                            :options="collect($employees)->map(function($employee) {
                                                                return [
                                                                    'id' => $employee->id,
                                                                    'text' => $employee->name . ' - ' . ($employee->department?->title ?? 'بدون قسم')
                                                                ];
                                                            })->toArray()" 
                                                            :placeholder="'🔍 ابحث واختر موظف...'" 
                                                            :search="true"
                                                            :create="false"
                                                            :multiple="false"
                                                            :max-items="1"
                                                            :max-options="1000"
                                                            :allow-empty-option="true"
                                                        />
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
                                        @if($processingType === 'multiple')
                                            <div class="selection-content" wire:loading.remove wire:target="processingType">
                                                <div class="form-group-modern">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-users text-info me-2"></i>
                                                        اختيار الموظفين 
                                                        <span class="text-danger ms-1">*</span>
                                                        <span class="badge bg-info bg-opacity-10 text-white ms-2 px-2 py-1 rounded-pill small text-white">
                                                            متعدد
                                                            <i class="fas fa-users"></i>
                                                        </span>
                                                    </label>
                                                    <div class="select-wrapper" wire:key="tom-select-multiple-employees">
                                                        <x-tom-select 
                                                            wireModel="selectedEmployees" 
                                                            :name="'selectedEmployees'"
                                                            :id="'selectedEmployees'"
                                                            :required="true"
                                                            :options="collect($employees)->map(function($employee) {
                                                                return [
                                                                    'id' => $employee->id,
                                                                    'text' => $employee->name . ' - ' . ($employee->department?->title ?? 'بدون قسم')
                                                                ];
                                                            })->toArray()" 
                                                            :placeholder="'🔍 ابحث واختر موظفين متعددين...'" 
                                                            :search="true"
                                                            :multiple="true"
                                                            :max-items="1000"
                                                            :max-options="1000"
                                                        />
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
                                        @if($processingType === 'department')
                                            <div class="selection-content" wire:loading.remove wire:target="processingType">
                                                <div class="form-group-modern">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-building text-warning me-2"></i>
                                                        اختيار القسم 
                                                        <span class="text-danger ms-1">*</span>
                                                        <span class="badge bg-warning bg-opacity-10 text-white ms-2 px-2 py-1 rounded-pill small">
                                                            قسم كامل
                                                            <i class="fas fa-building"></i>
                                                        </span>
                                                    </label>
                                                    <div class="select-wrapper">
                                                        <x-tom-select 
                                                            wireModel="selectedDepartment" 
                                                            :name="'selectedDepartment'"
                                                            :id="'selectedDepartment'"
                                                            :required="true"
                                                            :options="collect($departments)->map(function($department) {
                                                                return [
                                                                    'id' => $department->id,
                                                                    'text' => $department->title
                                                                ];
                                                            })->toArray()" 
                                                            :placeholder="'🏢 اختر قسم...'" 
                                                            :search="true"
                                                            :max-items="1"
                                                            :max-options="1000"
                                                        />
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
                                        <span class="badge bg-secondary bg-opacity-10 text-white ms-2 px-2 py-1 rounded-pill small">
                                            اختياري
                                        </span>
                                    </label>
                                    <div class="form-control-wrapper">
                                        <textarea wire:model="notes" 
                                                class="form-control form-control-modern @error('notes') is-invalid @enderror" 
                                                rows="4" 
                                                placeholder="أضف أي ملاحظات أو تعليقات تخص هذه المعالجة..."></textarea>
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
                                    <div class="action-info">
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            سيتم معالجة البيانات وفقاً للإعدادات المحددة
                                        </p>
                                    </div>
                                    <div class="action-buttons">
                                        <button type="submit" 
                                                class="btn btn-primary btn-lg px-4 py-3 rounded-pill shadow-sm" 
                                                wire:loading.attr="disabled" 
                                                wire:target="processAttendance">
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
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header justify-content-between d-flex">
                        <h5 class="modal-title">تفاصيل المعالجة #{{ $selectedProcessing->id }}</h5>
                        <button type="button" class="btn-close m-2" wire:click="closeDetails"></button>
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
                    <div class="modal-footer justify-content-center d-flex">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetails">إغلاق</button>
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
        
        .form-control-modern, .form-select-modern {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 50px 14px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }
        
        .form-control-modern:focus, .form-select-modern:focus {
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
        
        .form-section:has([wire\\:loading]) {
            pointer-events: none;
        }
        
        .form-section:has([wire\\:loading]) * {
            pointer-events: none;
        }
        
        /* Prevent layout shifts during form submission */
        .card-body {
            position: relative;
        }
        
        .card-body:has([wire\\:loading]) {
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
    </style>
</div>