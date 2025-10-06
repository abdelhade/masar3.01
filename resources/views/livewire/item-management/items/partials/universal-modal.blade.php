@if($showModal)
<div class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title font-family-cairo fw-bold text-white" id="universalModalLabel">
                    {{ $modalTitle }}
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="closeModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('livewire.item-management.items.partials.alerts')
                <form wire:submit.prevent="saveModalData">
                    <div class="mb-3">
                        <label for="modalName" class="form-label font-family-cairo fw-bold">الاسم</label>
                        <input type="text" wire:model="modalData.name" class="form-control font-family-cairo fw-bold" id="modalName" placeholder="أدخل الاسم" autofocus>
                        @error('modalData.name')
                            <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary font-family-cairo fw-bold" wire:click="closeModal">إلغاء</button>
                <button type="button" class="btn btn-primary font-family-cairo fw-bold" wire:click="saveModalData" wire:loading.attr="disabled" wire:target="saveModalData">
                    <span wire:loading.remove wire:target="saveModalData">حفظ</span>
                    <span wire:loading wire:target="saveModalData">جاري الحفظ...</span>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
@endif


