<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">معالجة الراتب المرن (ثابت + ساعات عمل مرن)</h4>
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

    {{-- Create New Processing Button --}}
    <div class="row mb-3">
        <div class="col-12">
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i> إنشاء معالجة جديدة
            </button>
        </div>
    </div>

    {{-- Create Processing Modal --}}
    @if($this->showCreateModal)
    <div class="modal fade show d-block" 
         id="createProcessingModal"
         tabindex="-1" 
         role="dialog" 
         aria-modal="true" 
         style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0,0,0,0.5) !important; z-index: 1050 !important; margin: 0 !important; padding: 0 !important;">
        <div class="modal-dialog modal-fullscreen" role="document" style="width: 100vw !important; max-width: 100vw !important; height: 100vh !important; margin: 0 !important; padding: 0 !important;">
            <div class="modal-content" style="width: 100vw !important; max-width: 100vw !important; height: 100vh !important; border-radius: 0 !important; border: none !important; margin: 0 !important; padding: 0 !important;">
                <div class="modal-header bg-primary text-white" style="border-radius: 0;">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> معالجة جديدة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeCreateModal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 150px);">
                    <form wire:submit.prevent="processSalary">
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
                                                                    class="form-control form-control-sm"
                                                                    placeholder="0.00">
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
                                                        wire:model="employeeHours.{{ $employee->id }}" 
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
                                    <span wire:loading.remove>معالجة</span>
                                    <span wire:loading>جاري المعالجة...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeCreateModal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Results --}}
    @if($showResults && !empty($processingResults))
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">نتائج المعالجة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم الموظف</th>
                                    <th>الراتب الثابت</th>
                                    <th>عدد الساعات</th>
                                    <th>أجر الساعة</th>
                                    <th>راتب الساعات</th>
                                    <th>إجمالي الراتب</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processingResults as $result)
                                <tr>
                                    <td>{{ $result['employee_name'] }}</td>
                                    <td>{{ number_format($result['fixed_salary'], 2) }}</td>
                                    <td>{{ number_format($result['hours_worked'], 2) }}</td>
                                    <td>{{ number_format($result['hourly_wage'], 2) }}</td>
                                    <td>{{ number_format($result['flexible_salary'], 2) }}</td>
                                    <td><strong>{{ number_format($result['total_salary'], 2) }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Previous Processings --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">المعالجات السابقة</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم الموظف</th>
                                    <th>الفترة</th>
                                    <th>الراتب الثابت</th>
                                    <th>عدد الساعات</th>
                                    <th>أجر الساعة</th>
                                    <th>إجمالي الراتب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processings as $processing)
                                <tr>
                                    <td>{{ $processing->employee->name }}</td>
                                    <td>{{ $processing->period_start->format('Y-m-d') }} - {{ $processing->period_end->format('Y-m-d') }}</td>
                                    <td>{{ number_format($processing->fixed_salary, 2) }}</td>
                                    <td>{{ number_format($processing->hours_worked, 2) }}</td>
                                    <td>{{ number_format($processing->hourly_wage, 2) }}</td>
                                    <td>{{ number_format($processing->total_salary, 2) }}</td>
                                    <td>{!! $processing->status_badge !!}</td>
                                    <td>
                                        @if($processing->status === 'pending')
                                        <div class="btn-group" role="group">
                                            <button wire:click="openEditModal({{ $processing->id }})" 
                                                wire:loading.attr="disabled"
                                                onclick="console.log('Button clicked, processing ID: {{ $processing->id }}')"
                                                class="btn btn-sm btn-primary"
                                                title="تعديل">
                                                <span wire:loading.remove wire:target="openEditModal({{ $processing->id }})">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </span>
                                                <span wire:loading wire:target="openEditModal({{ $processing->id }})">
                                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                                </span>
                                            </button>
                                            <button wire:click="approveProcessing({{ $processing->id }})" 
                                                class="btn btn-sm btn-success"
                                                wire:confirm="هل أنت متأكد من الموافقة على هذه المعالجة؟"
                                                title="موافقة">
                                                <i class="fas fa-check"></i> موافقة
                                            </button>
                                            <button wire:click="rejectProcessing({{ $processing->id }})" 
                                                class="btn btn-sm btn-danger"
                                                wire:confirm="هل أنت متأكد من رفض هذه المعالجة؟"
                                                title="رفض">
                                                <i class="fas fa-times"></i> رفض
                                            </button>
                                        </div>
                                        @endif
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
                    {{ $processings->links() }}
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Edit Modal --}}
    @if($this->showEditModal && $this->editingProcessingId)
    <div class="modal fade show d-block" 
         id="editProcessingModal"
         tabindex="-1" 
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="editProcessingModalLabel"
         wire:key="edit-modal-{{ $this->editingProcessingId }}"
         style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0,0,0,0.5) !important; z-index: 1050 !important; margin: 0 !important; padding: 0 !important;">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="margin: 1.75rem auto;">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> تعديل المعالجة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        $editingProcessing = $this->editingProcessingId ? \App\Models\FlexibleSalaryProcessing::with('employee')->find($this->editingProcessingId) : null;
                    @endphp
                    @if($editingProcessing)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>الموظف:</strong></label>
                            <p class="form-control-plaintext">{{ $editingProcessing->employee->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>الفترة:</strong></label>
                            <p class="form-control-plaintext">{{ $editingProcessing->period_start->format('Y-m-d') }} - {{ $editingProcessing->period_end->format('Y-m-d') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>الراتب الثابت:</strong></label>
                            <p class="form-control-plaintext">{{ number_format($editingProcessing->fixed_salary, 2) }} ريال</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>أجر الساعة:</strong></label>
                            <p class="form-control-plaintext">{{ number_format($editingProcessing->hourly_wage, 2) }} ريال</p>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">عدد الساعات <span class="text-danger">*</span></label>
                        <input type="number" 
                            wire:model.live="editingHoursWorked" 
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
                        <textarea wire:model="editingNotes" class="form-control" rows="3" placeholder="أدخل ملاحظات إضافية..."></textarea>
                    </div>
                    @if($this->editingHoursWorked > 0 && $editingProcessing)
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>راتب الساعات:</strong> {{ number_format($this->editingHoursWorked * $editingProcessing->hourly_wage, 2) }} ريال
                            </div>
                            <div class="col-md-6">
                                <strong>إجمالي الراتب:</strong> {{ number_format($editingProcessing->fixed_salary + ($this->editingHoursWorked * $editingProcessing->hourly_wage), 2) }} ريال
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> لم يتم العثور على بيانات المعالجة
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeEditModal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="updateProcessing" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="updateProcessing">
                            <i class="fas fa-save"></i> حفظ التعديلات
                        </span>
                        <span wire:loading wire:target="updateProcessing">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            جاري الحفظ...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    @script
    <script>
        // إغلاق الـ modals عند الضغط على الخلفية
        document.addEventListener('click', function(event) {
            const editModal = document.getElementById('editProcessingModal');
            const createModal = document.getElementById('createProcessingModal');
            const backdrop = document.querySelector('.modal-backdrop');
            
            if (event.target === editModal || (event.target === backdrop && editModal && editModal.style.display === 'block')) {
                @this.closeEditModal();
            }
            
            if (event.target === createModal || (event.target === backdrop && createModal && createModal.style.display === 'block')) {
                @this.closeCreateModal();
            }
        });
        
        // التأكد من ظهور مودال التعديل عند تحديث Livewire
        document.addEventListener('livewire:updated', () => {
            const editModal = document.getElementById('editProcessingModal');
            if (editModal && editModal.classList.contains('show') && editModal.style.display !== 'block') {
                editModal.style.display = 'block';
            }
        });
    </script>
    @endscript
</div>
