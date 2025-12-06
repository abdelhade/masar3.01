<div class="container-fluid" dir="rtl">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">إدارة المكافآت والخصومات</h4>
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

    {{-- Add Button --}}
    <div class="row mb-3">
        <div class="col-12">
            <button wire:click="openForm" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{__('hr.add_deduction_reward')}}
            </button>
        </div>
    </div>

    {{-- Form Modal --}}
    @if($showForm)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $editingId ? 'تعديل' : 'إضافة' }} {{ $type === 'deduction' ? 'خصم' : 'مكافأة' }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الموظف <span class="text-danger">*</span></label>
                                <select wire:model="selectedEmployee" class="form-select @error('selectedEmployee') is-invalid @enderror">
                                    <option value="">اختر الموظف</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedEmployee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">النوع <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="deduction">خصم</option>
                                    <option value="reward">مكافأة</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                                <input type="number" wire:model="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">السبب <span class="text-danger">*</span></label>
                                <input type="text" wire:model="reason" class="form-control @error('reason') is-invalid @enderror">
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">ملاحظات</label>
                                <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <button type="button" wire:click="closeForm" class="btn btn-secondary">إلغاء</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">قائمة المكافآت والخصومات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم الموظف</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>السبب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->employee->name }}</td>
                                    <td>
                                        @if($item->type === 'deduction')
                                            <span class="badge bg-danger">خصم</span>
                                        @else
                                            <span class="badge bg-success">مكافأة</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->amount, 2) }}</td>
                                    <td>{{ $item->date->format('Y-m-d') }}</td>
                                    <td>{{ $item->reason }}</td>
                                    <td>
                                        @if($item->hasJournal())
                                            <span class="badge bg-success">معتمد</span>
                                        @else
                                            <span class="badge bg-warning">قيد المراجعة</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$item->hasJournal())
                                            <button wire:click="approve({{ $item->id }})" 
                                                class="btn btn-sm btn-success"
                                                wire:confirm="هل أنت متأكد من الموافقة؟">
                                                موافقة
                                            </button>
                                        @endif
                                        @if(!$item->hasJournal())
                                            <button wire:click="openForm({{ $item->id }})" class="btn btn-sm btn-primary">تعديل</button>
                                            <button wire:click="delete({{ $item->id }})" 
                                                class="btn btn-sm btn-danger"
                                                wire:confirm="هل أنت متأكد من الحذف؟">
                                                حذف
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد بيانات</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

