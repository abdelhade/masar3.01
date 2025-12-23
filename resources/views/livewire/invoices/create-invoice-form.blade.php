<div>
    @section('formAction', 'create')
    <div class="content-wrapper">
        <section class="content">
            <form
                x-data="invoiceCalculations({
                    invoiceItems: @entangle('invoiceItems'),
                    discountPercentage: @entangle('discount_percentage'),
                    additionalPercentage: @entangle('additional_percentage'),
                    receivedFromClient: @entangle('received_from_client'),
                    discountValue: @entangle('discount_value'),
                    additionalValue: @entangle('additional_value'),
                    vatPercentage: @entangle('vat_percentage'),
                    vatValue: @entangle('vat_value'),
                    withholdingTaxPercentage: @entangle('withholding_tax_percentage'),
                    withholdingTaxValue: @entangle('withholding_tax_value'),
                    defaultVatPercentage: @js(setting('default_vat_percentage', 0)),
                    defaultWithholdingTaxPercentage: @js(setting('default_withholding_tax_percentage', 0)),
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
                        $wire.saveForm();
                    }, 100);
                "
                @keydown.enter.prevent="
                    if ($event.target.tagName === 'BUTTON' && $event.target.type === 'submit') {
                        $event.target.click();
                    }
                ">

                @include('components.invoices.invoice-head')

                <div id="invoice-config" 
             data-is-cash="{{ $isCurrentAccountCash ? '1' : '0' }}" 
             wire:key="invoice-config-{{ $isCurrentAccountCash ? '1' : '0' }}"
             style="display:none;"></div>

        <div class="row">
                    @if (setting('invoice_use_templates'))
                        @if ($availableTemplates->isNotEmpty())
                            <div class="col-lg-1">
                                <label for="selectedTemplate">{{ __('Invoice Template') }}</label>
                                <select wire:model.live="selectedTemplateId" id="selectedTemplate"
                                    class="form-control @error('selectedTemplateId') is-invalid @enderror">
                                    @foreach ($availableTemplates as $template)
                                        <option value="{{ $template->id }}">
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedTemplateId')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                    @endif

                    <div class="col-lg-3 mb-3" style="position: relative;">

                        <label>{{ __('Search Item') }}</label>

                        <div x-data="invoiceSearch({
                            invoiceType: {{ $type }},
                            branchId: '{{ $branch_id ?? '' }}',
                            priceType: {{ $selectedPriceType ?? 1 }},
                            storeId: '{{ $acc2_id ?? '' }}',
                            currentItems: @js($invoiceItems ?? [])
                        })" 
                        style="position: relative;"
                        wire:ignore.self>

                            {{-- ✅ تم إزالة @keydown.enter.prevent و @keydown.arrow-down/up - keydownHandler في init() يتعامل مع جميع مفاتيح التنقل --}}
                            <input type="text" 
                                x-model="searchTerm" 
                                @input.debounce.50ms="if (searchTerm && searchTerm.length >= 1) { showResults = true; } search();"
                                @keydown.escape="clearSearch(true)"
                                x-on:focus="handleSearchFocus()"
                                class="form-control frst" 
                                id="search-input"
                                placeholder="{{ __('Search by item name...') }}" 
                                autocomplete="off">

                            {{-- Loading spinner --}}
                            <div x-show="loading" x-cloak
                                style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);">
                                <i class="fas fa-spinner fa-spin text-primary"></i>
                            </div>

                            {{-- نتائج البحث --}}
                            <div x-show="showResults && searchResults.length > 0 && !loading" x-cloak
                                class="list-group position-absolute w-100"
                                style="z-index: 999; max-height: 300px; overflow-y: auto; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #ddd;"
                                @click.away="showResults = false">

                                <template x-for="(item, index) in searchResults" :key="item.id">
                                    <li :class="'list-group-item list-group-item-action search-item-' + index + (selectedIndex ===
                                        index ? ' active' : '')"
                                        @click="addItemFast(item)" @mouseenter="selectedIndex = index"
                                        style="cursor: pointer; transition: all 0.2s;">
                                        <strong x-text="item.name"></strong>
                                        <small class="text-muted" x-show="item.code"> - <span
                                                x-text="item.code"></span></small>
                                    </li>
                                </template>
                            </div>

                            {{-- زر إضافة صنف جديد --}}
                            <div x-show="showResults && searchResults.length === 0 && searchTerm.length > 0 && !loading" x-cloak
                                class="list-group position-absolute w-100"
                                style="z-index: 999; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #ddd;">
                                <li :class="'list-group-item list-group-item-action list-group-item-success search-item-0' + (
                                    (selectedIndex === 0 && isCreateNewItemSelected) || (selectedIndex === 0 && searchResults.length === 0) ? ' active' : '')"
                                    @click="createNewItem()" @mouseenter="selectedIndex = 0; isCreateNewItemSelected = true"
                                    style="cursor: pointer; transition: all 0.2s;">
                                    <i class="fas fa-plus"></i>
                                    <strong>{{ __('Create new item') }}</strong>: <span x-text="searchTerm"></span>
                                </li>
                            </div>
                        </div>
                    </div>








                    <div class="col-lg-3 mb-3">
                        <label>{{ __('Search by Barcode') }}</label>
                        <input type="text" wire:model.live="barcodeTerm" class="form-control" id="barcode-search"
                            placeholder="{{ __('Enter Barcode ') }}" autocomplete="off"
                            wire:keydown.enter.prevent="addItemByBarcode" />
                        @if (strlen($barcodeTerm) > 0 && !empty($barcodeSearchResults) && count($barcodeSearchResults) > 0)
                            <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                @foreach ($barcodeSearchResults as $index => $item)
                                    <li class="list-group-item list-group-item-action"
                                        wire:click="addItemFromSearchFast({{ $item->id }})">
                                        {{ $item->name }} ({{ $item->code }})
                                    </li>
                                @endforeach
                            </ul>
                            {{-- @elseif (strlen($barcodeTerm) > 0) --}}
                        @endif
                    </div>
                    @if (setting('invoice_select_price_type'))
                        {{-- اختيار نوع السعر العام للفاتورة --}}
                        @if (in_array($type, [10, 12, 14, 16, 22]))
                            <div class="col-lg-2">
                                <label for="selectedPriceType">{{ __('Select Price Type for Invoice') }}</label>
                                <select wire:model.live="selectedPriceType"
                                    class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror">
                                    {{-- <option value="">{{ __('اختر نوع السعر') }}</option> --}}
                                    @foreach ($priceTypes as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedPriceType')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                    @endif

                    {{-- <x-branches::branch-select :branches="$branches" model="branch_id" /> --}}

                    @if ($type == 14)
                        <div class="col-lg-1">
                            <label for="status">{{ __('Invoice Status') }}</label>
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

                @include('components.invoices.invoice-footer')

            </form>
        </section>
    </div>
</div>
@push('scripts')
    {{-- ✅ Include Shared Invoice Scripts Component --}}
    @include('components.invoices.invoice-scripts')
@endpush
