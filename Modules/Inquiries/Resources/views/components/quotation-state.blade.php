<!-- Quotation State Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    حالة التسعير
                </h6>
                <small class="d-block mt-1">اختر حالة التسعير</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">حالة التسعير</label>
                        <select wire:model.live="quotationState" class="form-select">
                            <option value="">اختر الحالة...</option>
                            @foreach ($quotationStateOptions as $state)
                                <option value="{{ $state->value }}">
                                    {{ $state->label() }}</option>
                            @endforeach
                        </select>
                        @error('quotationState')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (in_array($this->quotationState, [
                            \Modules\Inquiries\Enums\QuotationStateEnum::REJECTED->value,
                            \Modules\Inquiries\Enums\QuotationStateEnum::RE_ESTIMATION->value,
                        ]))
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">سبب الحالة</label>
                            <input type="text" wire:model.live="quotationStateReason" class="form-control"
                                placeholder="أدخل السبب...">
                            @error('quotationStateReason')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">حجم المشروع</label>
                        <select wire:model="projectSize" class="form-select">
                            <option value="">اختر حجم المشروع...</option>
                            @foreach ($projectSizeOptions as $size)
                                <option value="{{ $size }}">{{ $size }}
                                </option>
                            @endforeach
                        </select>
                        @error('projectSize')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">أولوية KON</label>
                        <select wire:model="konPriority" class="form-select">
                            <option value="">اختر أولوية KON...</option>
                            @foreach ($konPriorityOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                        @error('konPriority')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">أولوية العميل</label>
                        <select wire:model="clientPriority" class="form-select">
                            <option value="">اختر أولوية ...</option>
                            @foreach ($clientPriorityOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                        @error('clientPriority')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
