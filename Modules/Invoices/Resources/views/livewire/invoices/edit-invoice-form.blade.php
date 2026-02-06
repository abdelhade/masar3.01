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
    <div class="content-wrapper">
        <section class="content">
            <form id="invoice-edit-form" class="d-flex flex-column g-0 invoice-form-fullheight"
                style="height: 100%; min-height: 0; overflow: hidden;" data-invoice-submit="updateForm">

                <div id="invoice-search-container" class="d-flex flex-column flex-grow-1 overflow-hidden g-0"
                    data-branch-id="{{ $branch_id ?? '' }}" data-invoice-type="{{ $type ?? 10 }}"
                    data-price-type="{{ $selectedPriceType ?? 1 }}" data-store-id="{{ $acc2_id ?? '' }}">

                    @include('invoices::components.invoices.invoice-head')

                    <div id="invoice-config" data-is-cash="{{ $isCurrentAccountCash ? '1' : '0' }}"
                        wire:key="invoice-config-{{ $isCurrentAccountCash ? '1' : '0' }}" style="display:none;"></div>

                    <div class="row flex-grow-1 overflow-hidden g-0 py-0">
                        <div class="col-12 h-100 py-0 d-flex flex-column min-height-0">
                            @push('invoice_table_search_row')
                                <div class="d-inline-block invoice-cell-search-wrap w-100" style="position: relative;" wire:ignore>
                                    <input type="text" class="form-control form-control-sm invoice-field"
                                        id="search-input" placeholder="{{ __('Search by item name...') }}"
                                        autocomplete="off" style="max-width: 100%;">
                                    <div id="invoice-search-results"
                                        class="list-group position-absolute shadow-sm invoice-search-results"
                                        style="z-index: 999; max-height: 280px; overflow-y: auto; width: 320px; margin-top: 2px;">
                                    </div>
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
