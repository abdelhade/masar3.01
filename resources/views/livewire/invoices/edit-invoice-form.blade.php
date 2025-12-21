<div>
    @section('formAction', 'edit')
    <div class="invoice-container">
        <div class="content-wrapper">
            <section class="content">
                <form wire:submit.prevent="updateForm"
                    x-data="invoiceCalculations({
                        invoiceItems: @entangle('invoiceItems'),
                        discountPercentage: @entangle('discount_percentage'),
                        additionalPercentage: @entangle('additional_percentage'),
                        receivedFromClient: @entangle('received_from_client'),
                        discountValue: @entangle('discount_value'),
                        additionalValue: @entangle('additional_value'),
                        dimensionsUnit: @js($dimensionsUnit ?? 'cm'),
                        enableDimensionsCalculation: @js($enableDimensionsCalculation ?? false),
                        invoiceType: @js($type ?? 10),
                        isCashAccount: @entangle('isCurrentAccountCash'),
                        items: @entangle('invoiceItems'),
                        acc1Id: @entangle('acc1_id'),
                        editableFieldsOrder: @js($this->getEditableFieldsOrder()),
                        currentBalance: @js($currentBalance ?? 0)
                    })"
                    @submit.prevent="
                        // ✅ 1. مزامنة جميع القيم من Alpine.js إلى Livewire
                                syncToLivewire();
                        // ✅ 2. انتظار قليل للتأكد من اكتمال المزامنة
                        setTimeout(() => {
                            // ✅ 3. إرسال النموذج
                            $wire.updateForm();
                        }, 100);
                    "
                    @keydown.enter.prevent="
                        if ($event.target.tagName === 'BUTTON' && $event.target.type === 'submit') {
                            $event.target.click();
                        }
                    ">

                    @include('components.invoices.invoice-head')

                    {{-- ✅ إعداد الفاتورة للـ Alpine.js --}}
                    <div id="invoice-config" 
                         data-is-cash="{{ $isCurrentAccountCash ? '1' : '0' }}" 
                         wire:key="invoice-config-{{ $isCurrentAccountCash ? '1' : '0' }}"
                         style="display:none;"></div>

                    <div class="row">
                        <div class="col-lg-3 mb-3" style="position: relative;">
                            <label>ابحث عن صنف</label>
                            <input type="text" wire:model.live="searchTerm" class="form-control frst"
                                placeholder="ابدأ بكتابة اسم الصنف..." autocomplete="off"
                                wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                                wire:keydown.enter.prevent="handleEnter" />
                            @if (strlen($searchTerm) > 0 && $searchResults->count())
                                <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                    @foreach ($searchResults as $index => $item)
                                        <li class="list-group-item list-group-item-action
                                             @if ($selectedResultIndex === $index) active @endif"
                                            wire:click="addItemFromSearch({{ $item->id }})">
                                            {{ $item->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(strlen($searchTerm) > 0 && $searchResults->isEmpty())
                                <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                    <li class="list-group-item list-group-item-action list-group-item-success @if ($isCreateNewItemSelected) active @endif"
                                        style="cursor: pointer;"
                                        wire:click.prevent="createNewItem('{{ $searchTerm }}')">
                                        <i class="fas fa-plus"></i>
                                        <strong>إنشاء صنف جديد:</strong> "{{ $searchTerm }}"
                                    </li>
                                </ul>
                            @elseif(strlen($searchTerm) > 0)
                                <div class="mt-2" style="position: absolute; z-index: 1000; width: 100%;">
                                    <div class="list-group-item text-danger">
                                        لا توجد نتائج لـ "{{ $searchTerm }}"
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-3 mb-3">
                            <label>ابحث بالباركود</label>
                            <input type="text" wire:model.live="barcodeTerm" class="form-control" id="barcode-search"
                                placeholder="ادخل الباركود " autocomplete="off" wire:keydown.enter="addItemByBarcode" />
                            @if (strlen($barcodeTerm) > 0 && $barcodeSearchResults->count())
                                <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                    @foreach ($barcodeSearchResults as $index => $item)
                                        <li class="list-group-item list-group-item-action"
                                            wire:click="addItemFromSearch({{ $item->id }})">
                                            {{ $item->name }} ({{ $item->code }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- اختيار نوع السعر العام للفاتورة --}}
                        @if (in_array($type, [10, 12, 14, 16, 22]))
                            <div class="col-lg-2">
                                <label for="selectedPriceType">{{ __('اختر نوع السعر للفاتورة') }}</label>
                                <select wire:model.live="selectedPriceType"
                                    class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror">
                                    <option value="">{{ __('اختر نوع السعر') }}</option>
                                    @foreach ($priceTypes as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedPriceType')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif

                        <x-branches::branch-select :branches="$branches" model="branch_id" />

                        @if ($type == 14)
                            <div class="col-lg-1">
                                <label for="status">{{ __('حالة الفاتوره') }}</label>
                                <select wire:model="status" id="status"
                                    class="form-control form-control-sm @error('status') is-invalid @enderror">
                                    @foreach ($statues as $statusCase)
                                        <option value="{{ $statusCase->value }}">{{ $statusCase->translate() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="row form-control">
                        @include('components.invoices.invoice-item-table')
                    </div>

                    {{-- قسم الإجماليات والمدفوعات --}}
                    @include('components.invoices.invoice-footer')
                </form>
            </section>
        </div>
    </div>

    <style>
        .modal.show {
            z-index: 1055;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            font-size: 0.875em;
        }

        .alert ul {
            padding-left: 1.2rem;
        }

        .modal-dialog-centered {
            min-height: calc(100vh - 1rem);
        }

        @media (min-width: 576px) {
            .modal-dialog-centered {
                min-height: calc(100vh - 3.5rem);
            }
        }
    </style>

    @push('scripts')
        {{-- ✅ Include Shared Invoice Scripts Component --}}
        @include('components.invoices.invoice-scripts')
    @endpush
</div>
