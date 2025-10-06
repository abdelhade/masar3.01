<!-- Barcode Modal (Bootstrap) -->
<div wire:ignore.self class="modal fade" id="barcodeModal" tabindex="-1" role="dialog" aria-labelledby="barcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-family-cairo fw-bold" id="barcodeModalLabel">إدارة الباركودات الإضافية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-family-cairo fw-bold mb-0">الباركودات الإضافية</h6>
                            <button type="button" class="btn btn-success btn-sm font-family-cairo fw-bold"
                                wire:click.prevent="addModalBarcode()">
                                <i class="las la-plus me-1"></i> إضافة باركود
                            </button>
                        </div>
                        
                        @if(count($modalBarcodeData) > 0)
                            @foreach($modalBarcodeData as $index => $barcode)
                                <div class="input-group mb-2" wire:key="additional-barcode-{{ $index }}">
                                    <span class="input-group-text bg-light">
                                        <i class="las la-barcode text-primary"></i>
                                    </span>
                                    <input type="text" 
                                           wire:model="modalBarcodeData.{{ $index }}"
                                           id="modalBarcodeInput.{{ $index }}"
                                           class="form-control font-family-cairo fw-bold" 
                                           placeholder="أدخل الباركود"
                                           maxlength="25">
                                    <button type="button" 
                                            class="btn btn-outline-danger"
                                            wire:click="removeModalBarcode({{ $index }})"
                                            title="حذف الباركود">
                                        <i class="las la-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="las la-barcode text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">لا توجد باركودات إضافية</p>
                            </div>
                        @endif
                        
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary font-family-cairo fw-bold" data-bs-dismiss="modal">
                    إلغاء
                </button>
                <button type="button" class="btn btn-primary font-family-cairo fw-bold" 
                        wire:click="saveAdditionalBarcodes">
                    <i class="las la-save me-1"></i> حفظ الباركودات
                </button>
            </div>
        </div>
    </div>
</div>


