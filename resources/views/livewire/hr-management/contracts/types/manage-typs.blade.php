<?php

use Livewire\Volt\Component;
use App\Models\ContractType;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $contractTypeId = null;
    public $name = '';
    public $description = '';
    public $showModal = false;
    public $isEdit = false;
    public string $search = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function with(): array
    {
        return [
            'contractTypes' => ContractType::where('name', 'like', '%' . $this->search . '%')
                ->orderByDesc('id')
                ->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'contractTypeId']);
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $contractType = ContractType::findOrFail($id);
        $this->contractTypeId = $contractType->id;
        $this->name = $contractType->name;
        $this->description = $contractType->description;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            ContractType::find($this->contractTypeId)->update($validated);
            session()->flash('success', __('Contract type updated successfully.'));
        } else {
            ContractType::create($validated);
            session()->flash('success', __('Contract type created successfully.'));
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'contractTypeId']);
    }

    public function delete($id)
    {
        ContractType::findOrFail($id)->delete();
        session()->flash('success', __('Contract type deleted successfully.'));
    }
}; ?>


<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-1">
        @can('إضافة انواع العقود')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createContractTypeModal">
                <i class="las la-plus"></i> إضافة نوع عقد جديد
            </button>
        @endcan
        <div class="mb-3">
            <input type="text" wire:model="search" class="form-control" placeholder="بحث بالاسم...">
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped text-center mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            @canany(['حذف انواع العقود', 'تعديل انواع العقود'])
                                <th>الإجراءات</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contractTypes as $index => $type)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->description }}</td>

                                @canany(['حذف انواع العقود', 'تعديل انواع العقود'])
                                    <td>
                                        @can('تعديل انواع العقود')
                                            <button class="btn btn-success btn-sm me-1" wire:click="edit({{ $type->id }})">
                                                <i class="las la-edit"></i>
                                            </button>
                                        @endcan
                                        @can('حذف انواع العقود')
                                            <button class="btn btn-danger btn-sm" wire:click="delete({{ $type->id }})"
                                                onclick="return confirm('هل أنت متأكد أنك تريد الحذف؟')">
                                                <i class="las la-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                @endcanany

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">لا توجد أنواع عقود.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Create -->
    <div wire:ignore.self class="modal fade" id="createContractTypeModal" tabindex="-1"
        aria-labelledby="createContractTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form wire:submit.prevent="store">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة نوع عقد جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">الاسم</label>
                            <input wire:model.defer="name" type="text" class="form-control" id="name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">الوصف</label>
                            <textarea wire:model.defer="description" class="form-control" id="description"></textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div wire:ignore.self class="modal fade" id="editContractTypeModal" tabindex="-1"
        aria-labelledby="editContractTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form wire:submit.prevent="update">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تعديل نوع العقد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">الاسم</label>
                            <input wire:model.defer="edit_name" type="text" class="form-control" id="edit_name">
                            @error('edit_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">الوصف</label>
                            <textarea wire:model.defer="edit_description" class="form-control" id="edit_description"></textarea>
                            @error('edit_description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تحديث</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
