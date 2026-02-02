@php
    $fieldStates = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getFieldStates();
@endphp

<div>
    <style>
        footer.footer { display: none !important; }
    </style>
    @section('formAction', 'create')
    <div class="content-wrapper">
        <section class="content">
            <form id="invoice-main-form"
                class="d-flex flex-column g-0 invoice-form-fullheight"
                style="height: 100%; min-height: 0; overflow: hidden;"
                data-invoice-submit="saveForm">

                <div id="invoice-search-container"
                    class="d-flex flex-column flex-grow-1 overflow-hidden g-0"
                    data-branch-id="{{ $branch_id ?? '' }}"
                    data-invoice-type="{{ $type ?? 10 }}"
                    data-price-type="{{ $selectedPriceType ?? 1 }}"
                    data-store-id="{{ $acc2_id ?? '' }}">
                    @push('invoice_head_barcode')
                    <div class="col-lg-4">
                        <label class="form-label" style="font-size: 1em;">{{ __('Search by Barcode') }}</label>
                        <input type="text" class="form-control form-control-sm font-hold font-14" id="barcode-search"
                            placeholder="{{ __('Enter Barcode ') }}" autocomplete="off"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                            wire:keydown.enter="addItemByBarcode" />
                    </div>
                    @if (setting('allow_edit_price_payments'))
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
                    @endpush
                    @include('invoices::components.invoices.invoice-head')

                    <div id="invoice-config" data-is-cash="{{ $isCurrentAccountCash ? '1' : '0' }}"
                        wire:key="invoice-config-{{ $isCurrentAccountCash ? '1' : '0' }}" style="display:none;"></div>

                    <div class="row flex-grow-1 overflow-hidden g-0 py-0">
                        <div class="col-12 h-100 py-0 d-flex flex-column min-height-0">
                            @push('invoice_table_search_row')
                            <div class="d-inline-block invoice-cell-search-wrap w-100" style="position: relative;">
                                <input type="text" class="form-control form-control-sm invoice-field" id="search-input"
                                    placeholder="{{ __('Search by item name...') }}" autocomplete="off"
                                    style="max-width: 100%;">
                                <div id="invoice-search-results" class="list-group position-absolute shadow-sm invoice-search-results"
                                    style="z-index: 999; max-height: 280px; overflow-y: auto; width: 320px; margin-top: 2px;"></div>
                            </div>
                            @endpush
                            <div class="flex-grow-1 min-height-0 overflow-hidden">
                                @include('invoices::components.invoices.invoice-item-table')
                            </div>
                        </div>
                    </div>
                </div>

                @include('invoices::components.invoices.invoice-footer')
            </form>

            @if (setting('enable_installment_from_invoice'))
                <div wire:ignore>
                    @livewire(
                        'installments::create-installment-from-invoice',
                        [
                            'invoiceTotal' => $total_after_additional ?? 0,
                            'clientAccountId' => $acc1_id ?? null,
                        ],
                        'installment-modal'
                    )
                </div>
            @endif
        </section>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.basic.min.js"></script>
    @include('invoices::components.invoices.invoice-scripts')
@endpush