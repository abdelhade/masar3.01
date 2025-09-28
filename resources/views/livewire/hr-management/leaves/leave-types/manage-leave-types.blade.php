<?php

use Livewire\Volt\Component;
use App\Models\LeaveType;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $leaveTypes;
    public $name = '';
    public $code = '';
    public $is_paid = false;
    public $requires_approval = false;
    public $max_per_request_days = 0;
    public $accrual_rate_per_month = 0;
    public $carry_over_limit_days = 0;
    public $showModal = false;
    public $isEdit = false;
    public $search = '';
    public $leaveTypeId = null;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:leave_types,name,' . $this->leaveTypeId,
            'code' => 'required|string|max:255|unique:leave_types,code,' . $this->leaveTypeId,
            'is_paid' => 'required|boolean',
            'requires_approval' => 'required|boolean',
            'max_per_request_days' => 'required|integer|min:0',
            'accrual_rate_per_month' => 'required|numeric|min:0',
            'carry_over_limit_days' => 'required|integer|min:0',
        ];
    }

    public function mount()
    {
        $this->loadLeaveTypes();
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
        $this->loadLeaveTypes();
    }
    
    public function loadLeaveTypes()
    {
        $this->leaveTypes = LeaveType::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))->orderByDesc('id')->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->is_paid = false;
        $this->requires_approval = false;
        $this->max_per_request_days = 0;
        $this->accrual_rate_per_month = 0;
        $this->carry_over_limit_days = 0;
        $this->leaveTypeId = null;
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $leaveType = LeaveType::findOrFail($id);
        $this->leaveTypeId = $leaveType->id;
        $this->name = $leaveType->name;
        $this->code = $leaveType->code;
        $this->is_paid = $leaveType->is_paid;
        $this->requires_approval = $leaveType->requires_approval;
        $this->max_per_request_days = $leaveType->max_per_request_days;
        $this->accrual_rate_per_month = $leaveType->accrual_rate_per_month;
        $this->carry_over_limit_days = $leaveType->carry_over_limit_days;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'is_paid' => $this->is_paid,
            'requires_approval' => $this->requires_approval,
            'max_per_request_days' => $this->max_per_request_days,
            'accrual_rate_per_month' => $this->accrual_rate_per_month,
            'carry_over_limit_days' => $this->carry_over_limit_days,
        ];

        if ($this->isEdit) {
            LeaveType::where('id', $this->leaveTypeId)->update($data);
            session()->flash('message', 'تم تحديث نوع الإجازة بنجاح');
        } else {
            LeaveType::create($data);
            session()->flash('message', 'تم إنشاء نوع الإجازة بنجاح');
        }

        $this->closeModal();
        $this->loadLeaveTypes();
    }

    public function delete($id)
    {
        LeaveType::findOrFail($id)->delete();
        session()->flash('message', 'تم حذف نوع الإجازة بنجاح');
        $this->loadLeaveTypes();
    }
    
}; ?>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
<div>
                    <h2 class="mb-0">إدارة أنواع الإجازات</h2>
                    <p class="text-muted mb-0">إدارة أنواع الإجازات المختلفة في النظام</p>
                </div>
                <button type="button" class="btn btn-primary" wire:click="openModal">
                    <i class="fas fa-plus me-2"></i>إضافة نوع إجازة جديد
                </button>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="البحث في أنواع الإجازات..." 
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Leave Types Table -->
    <div class="card">
        <div class="card-body">
            @if($leaveTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>الاسم</th>
                                <th>الكود</th>
                                <th>مدفوعة</th>
                                <th>تتطلب موافقة</th>
                                <th>الحد الأقصى للطلب</th>
                                <th>معدل التراكم/شهر</th>
                                <th>حد التحويل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveTypes as $leaveType)
                                <tr>
                                    <td>
                                        <strong>{{ $leaveType->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $leaveType->code }}</span>
                                    </td>
                                    <td>
                                        @if($leaveType->is_paid)
                                            <span class="badge bg-success">مدفوعة</span>
                                        @else
                                            <span class="badge bg-warning">غير مدفوعة</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leaveType->requires_approval)
                                            <span class="badge bg-info">نعم</span>
                                        @else
                                            <span class="badge bg-secondary">لا</span>
                                        @endif
                                    </td>
                                    <td>{{ $leaveType->max_per_request_days }} يوم</td>
                                    <td>{{ $leaveType->accrual_rate_per_month }} يوم</td>
                                    <td>{{ $leaveType->carry_over_limit_days }} يوم</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    wire:click="edit({{ $leaveType->id }})" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    wire:click="delete({{ $leaveType->id }})"
                                                    onclick="return confirm('هل أنت متأكد من حذف هذا النوع؟')" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد أنواع إجازات</h5>
                    <p class="text-muted">ابدأ بإضافة نوع إجازة جديد</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            @if($isEdit)
                                تعديل نوع الإجازة
                            @else
                                إضافة نوع إجازة جديد
                            @endif
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">اسم نوع الإجازة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" wire:model="name" placeholder="مثال: إجازة سنوية">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">كود نوع الإجازة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" wire:model="code" placeholder="مثال: AL">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_per_request_days" class="form-label">الحد الأقصى للطلب (أيام) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_per_request_days') is-invalid @enderror" 
                                           id="max_per_request_days" wire:model="max_per_request_days" min="0">
                                    @error('max_per_request_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="accrual_rate_per_month" class="form-label">معدل التراكم/شهر (أيام) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('accrual_rate_per_month') is-invalid @enderror" 
                                           id="accrual_rate_per_month" wire:model="accrual_rate_per_month" min="0">
                                    @error('accrual_rate_per_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="carry_over_limit_days" class="form-label">حد التحويل (أيام) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('carry_over_limit_days') is-invalid @enderror" 
                                           id="carry_over_limit_days" wire:model="carry_over_limit_days" min="0">
                                    @error('carry_over_limit_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_paid" wire:model="is_paid">
                                        <label class="form-check-label" for="is_paid">
                                            إجازة مدفوعة
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="requires_approval" wire:model="requires_approval">
                                        <label class="form-check-label" for="requires_approval">
                                            تتطلب موافقة
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">إلغاء</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    @if($isEdit)
                                        تحديث
                                    @else
                                        حفظ
                                    @endif
                                </span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin"></i> جاري الحفظ...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
