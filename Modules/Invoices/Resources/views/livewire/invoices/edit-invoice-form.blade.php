@php
    // Inject InvoiceFormStateManager to get field states
    $fieldStates = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getFieldStates();
    $jsConfig = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getJavaScriptConfig();
@endphp

<div>
    {{-- Hide Global Footer on this page only (layout already hides via hide_footer section) --}}
    <style>
        footer.footer { display: none !important; }
    </style>
    @section('formAction', 'edit')
    <div class="invoice-container">
        <div class="content-wrapper">
            <section class="content">
                <form id="invoice-edit-form"
                    class="d-flex flex-column g-0 invoice-form-fullheight"
                    style="height: 100%; min-height: 0; overflow: hidden;"
                    data-invoice-submit="updateForm">

                    @push('invoice_head_barcode')
                    <div class="col-lg-4" style="position: relative;">
                        <label class="form-label" style="font-size: 1em;">{{ __('Search by Barcode') }}</label>
                        <input type="text" wire:model.live="barcodeTerm" class="form-control form-control-sm font-hold font-14" id="barcode-search"
                            placeholder="{{ __('Enter Barcode ') }}" autocomplete="off"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                            wire:keydown.enter="addItemByBarcode" />
                        @if (strlen($barcodeTerm) > 0 && $barcodeSearchResults->count())
                            <ul class="list-group position-absolute shadow-sm w-100" style="z-index: 999; max-height: 200px; overflow-y: auto; margin-top: 2px;">
                                @foreach ($barcodeSearchResults as $index => $item)
                                    <li class="list-group-item list-group-item-action" style="cursor: pointer;"
                                        wire:click="addItemFromSearch({{ $item->id }})">
                                        {{ $item->name }} ({{ $item->code }})
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @endpush
                    @include('invoices::components.invoices.invoice-head')

                    <div id="invoice-config"
                         data-is-cash="{{ $isCurrentAccountCash ? '1' : '0' }}"
                         wire:key="invoice-config-{{ $isCurrentAccountCash ? '1' : '0' }}"
                         style="display:none;"></div>

                    <div class="row flex-grow-1 overflow-hidden g-0 py-0">
                        <div class="col-12 h-100 py-0 d-flex flex-column min-height-0">
                            @push('invoice_table_search_row')
                            <div class="d-inline-block invoice-cell-search-wrap w-100" style="position: relative;">
                                <input type="text" wire:model.live="searchTerm" class="form-control form-control-sm invoice-field" id="search-input"
                                    placeholder="{{ __('Search by item name...') }}" autocomplete="off"
                                    style="max-width: 100%;"
                                    wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                                    wire:keydown.enter.prevent="handleEnter" />
                                @if (strlen($searchTerm) > 0 && $searchResults->count())
                                    <ul class="list-group position-absolute shadow-sm" style="z-index: 999; max-height: 280px; overflow-y: auto; width: 320px; margin-top: 2px;">
                                        @foreach ($searchResults as $index => $item)
                                            <li class="list-group-item list-group-item-action @if ($selectedResultIndex === $index) active @endif"
                                                wire:click="addItemFromSearch({{ $item->id }})" style="cursor: pointer;">
                                                {{ $item->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif(strlen($searchTerm) > 0 && $searchResults->isEmpty())
                                    <ul class="list-group position-absolute shadow-sm" style="z-index: 999; width: 320px; margin-top: 2px;">
                                        <li class="list-group-item list-group-item-action list-group-item-success @if ($isCreateNewItemSelected) active @endif"
                                            style="cursor: pointer;" wire:click.prevent="createNewItem('{{ $searchTerm }}')">
                                            <i class="fas fa-plus"></i>
                                            <strong>{{ __('Create new item') }}</strong>: {{ $searchTerm }}
                                        </li>
                                    </ul>
                                @elseif(strlen($searchTerm) > 0)
                                    <div class="list-group position-absolute shadow-sm" style="z-index: 999; width: 320px; margin-top: 2px;">
                                        <div class="list-group-item text-danger small">
                                            {{ __('No results for') }} "{{ $searchTerm }}"
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endpush
                            <div class="flex-grow-1 min-height-0 overflow-hidden">
                                @include('invoices::components.invoices.invoice-item-table')
                            </div>
                        </div>
                    </div>

                    {{-- قسم الإجماليات والمدفوعات --}}
                    @include('invoices::components.invoices.invoice-footer')
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
        @include('invoices::components.invoices.invoice-scripts')
    @endpush
</div>
