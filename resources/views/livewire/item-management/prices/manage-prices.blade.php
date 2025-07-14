<?php

use Livewire\Volt\Component;
use App\Models\Price;
use Illuminate\Support\Facades\Validator;

new class extends Component {
    public $prices;
    public $name;
    public $priceId;
    public $showModal = false;
    public $isEdit = false;

    public function rules()
    {
        return [
            'name' => 'required|string|max:60|unique:prices,name,' . $this->priceId,
        ];
    }

    public function mount()
    {
        $this->prices = Price::all();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'priceId']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function edit(Price $price)
    {
        $this->resetValidation();
        $this->priceId = $price->id;
        $this->name = $price->name;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('showModal');
    }

    public function save()
    {
        $validated = $this->validate();
        if ($this->isEdit) {
            Price::find($this->priceId)->update($validated);
            session()->flash('success', 'تم تحديث السعر بنجاح');
        } else {
            Price::create([
                'name' => $this->name,
            ]);
            session()->flash('success', 'تم إضافة الوحدة بنجاح');
        }

        $this->showModal = false;
        $this->dispatch('closeModal');
        $this->prices = Price::latest()->get();
    }

    public function delete(Price $price)
    {
        try {
            if ($price->items->count() > 0) {
                session()->flash('error', 'لا يمكن حذف السعر لأنه مرتبط بأصناف.');
                return;
            }
            $price->delete();
            session()->flash('success', 'تم حذف السعر بنجاح');
            $this->prices = Price::latest()->get();
        } catch (\Exception $e) {
            session()->flash('error', 'لا يمكن حذف السعر لأنه مرتبط بأصناف.');
        }
    }
}; ?>

<div>
    <div class="row">
        @if (session()->has('success'))
            <div class="alert alert-success" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 3000)">
                {{ session('error') }}
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    @can('إنشاء - الأسعار')
                        <button wire:click="create" type="button" class="btn btn-primary font-family-cairo fw-bold">
                            {{ __('Add New') }}
                            <i class="fas fa-plus me-2"></i>
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="font-family-cairo fw-bold">#</th>
                                    <th class="font-family-cairo fw-bold">الاسم</th>
                                    @can('عرض - تفاصيل سعر')
                                        <th class="font-family-cairo fw-bold">العمليات</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prices as $price)
                                    <tr>
                                        <td class="font-family-cairo fw-bold">{{ $loop->iteration }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $price->name }}</td>
                                                                          @can('عرض - تفاصيل سعر')
                                        <td>
                                            @can('تعديل - الأسعار')
                                            <a wire:click="edit({{ $price->id }})"><i
                                                    class="las la-pen text-success font-20"></i></a>
                                            @endcan      
                                            @can('حذف - الأسعار')
                                            <a wire:click="delete({{ $price->id }})"
                                                onclick="confirm('هل أنت متأكد من حذف هذا السعر؟') || event.stopImmediatePropagation()">
                                                <i class="las la-trash-alt text-danger font-20"></i>
                                            </a> 
                                           @endcan  
                                        </td>
                                    @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" wire:ignore.self id="priceModal" tabindex="-1" aria-labelledby="priceModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="priceModalLabel">
                        {{ $isEdit ? 'تعديل سعر' : 'إضافة سعر جديد' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">
                        <div class="mb-3">
                            <label for="name" class="form-label font-family-cairo fw-bold">الاسم</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror font-family-cairo fw-bold"
                                id="name" wire:model="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let modalInstance = null;
            const modalElement = document.getElementById('priceModal');

            Livewire.on('showModal', () => {
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalElement);
                }
                modalInstance.show();
            });

            Livewire.on('closeModal', () => {
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            // Optional: Reset modalInstance when modal is fully hidden
            modalElement.addEventListener('hidden.bs.modal', function () {
                modalInstance = null;
            });
        });
    </script>
</div>