<!-- Additional Barcode Modal -->
<div wire:ignore.self class="modal fade"
    id="add-barcode-modal.{{ $index }}" tabindex="-1"
    aria-labelledby="addBarcodeModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="font-family-cairo fw-bold text-white" id="addBarcodeModalLabel">
                    إضافة وتعديل الباركود
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    wire:click="cancelBarcodeUpdate({{ $index }})" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-2">
                    @if ($creating)
                        <button type="button" class="btn btn-primary btn-sm font-family-cairo fw-bold"
                            wire:click="addBarcodeField({{ $index }})">
                            <i class="las la-plus"></i> إضافة باركود
                        </button>
                    @endif
                </div>

                @foreach ($unitRow['barcodes'] as $barcodeIndex => $barcode)
                    <div class="d-flex align-items-center mb-2" wire:key="{{ $index }}-barcode-{{ $barcodeIndex }}">
                        <input type="text" @if (!$creating) disabled readonly @endif
                            class="form-control font-family-cairo fw-bold"
                            wire:model.live="unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}"
                            id="unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}"
                            placeholder="أدخل الباركود">
                        @if ($creating)
                            <button type="button" class="btn btn-danger btn-sm ms-2"
                                wire:click="removeBarcodeField({{ $index }}, {{ $barcodeIndex }})">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        @endif
                    </div>
                    @error("unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}")
                        <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                    @enderror
                @endforeach
            </div>
            <div class="modal-footer">
                @if ($creating)
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold" data-bs-dismiss="modal"
                        wire:click="cancelBarcodeUpdate({{ $index }})">إلغاء</button>
                    <button type="button" class="btn btn-primary font-family-cairo fw-bold"
                        wire:click="saveBarcodes({{ $index }})">حفظ</button>
                @endif
            </div>
        </div>
    </div>
</div>


