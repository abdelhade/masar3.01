@php
    // Inject InvoiceFormStateManager to get field states
    $fieldStates = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getFieldStates();
    $jsConfig = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getJavaScriptConfig();
@endphp
<div id="invoice-fixed-footer" class="invoice-footer-compact p-2 mt-auto" style="z-index: 999; background: #fff;">
    <div class="row border border-secondary rounded p-2 mb-1 g-2">
        @if (setting('invoice_show_item_details'))
            <div class="col-3">
                @if ($currentSelectedItem)
                    <div class="card">
                        <div class="card-header text-white py-1 px-2">
                            <h6 class="mb-0 small"><i class="fas fa-box"></i> {{ __('Item Details') }}</h6>
                        </div>
                        <div class="card-body py-1 px-2">
                            <div class="row g-1">


                                <div class="col-md-6 border-end pe-2">


                                    <div class="row mb-1 g-0">
                                        <div class="col-5 small">{{ __('Name:') }}</div>
                                        <div class="col-7 fw-bold small"><span class="badge bg-light text-dark">{{ $selectedItemData['name'] }}</span></div>
                                    </div>
                                    <div class="row mb-1 g-0"><div class="col-5 small">{{ __('Store:') }}</div><div class="col-7 small"><span class="badge bg-light text-dark">{{ $selectedItemData['selected_store_name'] }}</span></div></div>
                                    <div class="row mb-1 g-0"><div class="col-5 small">{{ __('Available in Store:') }}</div><div class="col-7 small"><span class="badge bg-light text-dark">{{ $selectedItemData['available_quantity_in_store'] }}</span></div></div>
                                    <div class="row mb-1 g-0"><div class="col-6 small">{{ __('Total in Stores:') }}</div><div class="col-6 small"><span class="badge bg-light text-dark">{{ $selectedItemData['total_available_quantity'] }}</span></div></div>
                                </div>
                                <div class="col-md-6 ps-2">
                                    <div class="row mb-1 g-0"><div class="col-6 small">{{ __('Unit:') }}</div><div class="col-6 small"><span class="badge bg-light text-dark">{{ $selectedItemData['unit_name'] }}</span></div></div>
                                    <div class="row mb-1 g-0"><div class="col-6 small">{{ __('Price:') }}</div><div class="col-6 text-primary fw-bold small"><span class="badge bg-light text-dark">{{ number_format($selectedItemData['price'], 2) }}</span></div></div>
                                    <div class="row mb-1 g-0"><div class="col-6 small">{{ __('Last Purchase Price:') }}</div><div class="col-6 text-success small"><span class="badge bg-light text-dark">{{ number_format($selectedItemData['last_purchase_price'] ?? 0, 2) }}</span></div></div>
                                    <div class="row mb-1 g-0"><div class="col-6 small">{{ __('Average Purchase Price:') }}</div><div class="col-6 text-success small"><span class="badge bg-light text-dark main-num">{{ number_format($selectedItemData['average_cost'], 2) }}</span></div></div>
                                </div>


                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body py-1 px-2 text-center text-muted small">
                            <i class="fas fa-search fa-2x mb-1"></i>
                            <p class="mb-0 small">{{ __('Search for an item to display its data here') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif


        @if (setting('invoice_show_recommended_items'))
            @if ($type == 10)
                <div class="col-2">
                    <div class="card">
                        <div class="card-header text-white py-1 px-2"><h6 class="mb-0 small"><i class="fas fa-star"></i> {{ __('Recommendations') }}</h6></div>
                        <div class="card-body py-1 px-2">
                            @if (!empty($recommendedItems) && $type == 10)
                                <ul class="list-group list-group-flush small">
                                    @foreach ($recommendedItems as $item)
                                        <li class="list-group-item py-1 px-2 small d-flex justify-content-between align-items-center">
                                            <span class="small">{{ $item['name'] }} ({{ $item['total_quantity'] }} {{ __('Unit') }})</span>
                                            {{-- <button wire:click="addRecommendedItem({{ $item['id'] }})"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> {{ __('Add') }}
                                    </button> --}}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted text-center small mb-0">{{ __('No recommendations available') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="col-2"></div>
            @endif
        @endif

        @if ($type != 21)
            <div class="col-2">
                <div class="card">
                    <div class="card-body py-1 px-2">
                        <div class="form-group mb-1">
                            <label for="cash_box_id" class="small mb-0">{{ __('Cash Box') }}</label>
                            <select wire:model="cash_box_id" class="form-control form-control-sm form-control-sm-footer">
                                @foreach ($cashAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-1">
                            @php
                                $isSalesInvoice = in_array($type, [10, 12, 14, 16, 19, 22]);
                                $isPurchaseInvoice = in_array($type, [11, 13, 15, 17, 20, 24, 25]);
                            @endphp
                            <label for="received_from_client" class="small mb-0">@if ($isPurchaseInvoice){{ __('Amount Paid to Supplier') }}@else{{ __('Amount Received from Customer') }}@endif</label>
                            <input type="number" step="0.01" wire:model.live="received_from_client"
                                id="received-from-client" class="form-control form-control-sm form-control-sm-footer scnd {{ $isCurrentAccountCash ? 'bg-light' : '' }}" min="0"
                                @disabled($isCurrentAccountCash) @readonly($isCurrentAccountCash)
                                title="{{ $isCurrentAccountCash ? __('This field is automatically set for cash accounts') : '' }}">
                        </div>
                        <div class="form-group mb-0">
                            <label for="notes" class="small mb-0">{{ __('Notes') }}</label>
                            <textarea wire:model="notes" class="form-control form-control-sm form-control-sm-footer" rows="1"
                                placeholder="{{ __('Additional notes...') }}"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-5 ms-auto">
            <div class="card">
                <div class="card-body py-1 px-2">
                    @if ($type != 21)
                        <div class="row mb-1 align-items-center g-1">
                            <div class="col-3 text-right fw-bold small">{{ __('Subtotal:') }}</div>
                            <div class="col-3 text-left text-primary small" id="display-subtotal">{{ number_format($subtotal) }}</div>
                        </div>
                    @endif
                    @if ($type != 18 && $type != 21)
                        <div class="row mb-1 align-items-center g-1">
                            <div class="col-2 text-right font-weight-bold small"><label class="mb-0">{{ __('Discount %') }}</label></div>
                            <div class="col-3"><div class="input-group input-group-sm"><input type="number" step="0.01" wire:model.live="discount_percentage" onclick="this.select()" id="discount-percentage" class="form-control form-control-sm form-control-sm-footer {{ ($fieldStates['discount']['invoice'] ?? true) ? '' : 'bg-light' }}" min="0" max="100" @disabled(!($fieldStates['discount']['invoice'] ?? true))><span class="input-group-text">%</span></div></div>
                            <div class="col-2 text-right font-weight-bold small"><label for="discount_value" class="mb-0">{{ __('Discount Value') }}</label></div>
                            <div class="col-3"><input type="number" step="0.01" wire:model.live="discount_value" onclick="this.select()" class="form-control form-control-sm form-control-sm-footer {{ ($fieldStates['discount']['invoice'] ?? true) ? '' : 'bg-light' }}" id="discount-value" @disabled(!($fieldStates['discount']['invoice'] ?? true))></div>
                        </div>
                        <div class="row mb-1 align-items-center g-1">
                            <div class="col-2 text-right font-weight-bold small"><label class="mb-0">{{ __('Additional %') }}</label></div>
                            <div class="col-3"><div class="input-group input-group-sm"><input type="number" step="0.01" wire:model.live="additional_percentage" onclick="this.select()" id="additional-percentage" class="form-control form-control-sm form-control-sm-footer {{ ($fieldStates['additional']['invoice'] ?? true) ? '' : 'bg-light' }}" min="0" max="100" @disabled(!($fieldStates['additional']['invoice'] ?? true))><span class="input-group-text">%</span></div></div>
                            <div class="col-2 text-right font-weight-bold small"><label for="additional_value" class="mb-0">{{ __('Additional Value') }}</label></div>
                            <div class="col-3"><input type="number" step="0.01" wire:model.live="additional_value" onclick="this.select()" class="form-control form-control-sm form-control-sm-footer" id="additional-value" @disabled(!($fieldStates['additional']['invoice'] ?? true))></div>
                        </div>
                        @if (isVatEnabled())
                            <div class="row mb-1 align-items-center g-1">
                                <div class="col-2 text-right font-weight-bold small"><label class="mb-0">{{ __('VAT %') }}</label></div>
                                <div class="col-3"><div class="input-group input-group-sm"><input type="number" step="0.01" wire:model="vat_percentage" readonly class="form-control form-control-sm form-control-sm-footer bg-light" @disabled(!($fieldStates['vat']['invoice'] ?? true))><span class="input-group-text">%</span></div></div>
                                <div class="col-2 text-right font-weight-bold small"><label for="vat_value" class="mb-0">{{ __('VAT Value') }}</label></div>
                                <div class="col-3"><input type="number" step="0.01" wire:model="vat_value" readonly class="form-control form-control-sm form-control-sm-footer bg-light" id="vat-value" @disabled(!($fieldStates['vat']['invoice'] ?? true))></div>
                            </div>
                            <div class="row mb-1 align-items-center g-1">
                                <div class="col-2 text-right font-weight-bold small"><label class="mb-0">{{ __('Withholding Tax %') }}</label></div>
                                <div class="col-3"><div class="input-group input-group-sm"><input type="number" step="0.01" wire:model="withholding_tax_percentage" readonly class="form-control form-control-sm form-control-sm-footer bg-light" @disabled(!($fieldStates['withholding_tax']['invoice'] ?? true))><span class="input-group-text">%</span></div></div>
                                <div class="col-2 text-right font-weight-bold small"><label for="withholding_tax_value" class="mb-0">{{ __('Withholding Tax Value') }}</label></div>
                                <div class="col-3"><input type="number" step="0.01" wire:model="withholding_tax_value" readonly class="form-control form-control-sm form-control-sm-footer bg-light" id="withholding-tax-value" @disabled(!($fieldStates['withholding_tax']['invoice'] ?? true))></div>
                            </div>
                        @endif
                    @endif
                    @if ((isVatEnabled() || isWithholdingTaxEnabled()) && ($fieldStates['vat']['showAggregated'] ?? false))
                        <div class="row mb-1 align-items-center border-top pt-1 small text-info">
                            <div class="col-5 text-right font-weight-bold"><i class="fas fa-calculator"></i> {{ __('إجمالي الضريبة على الأصناف:') }}</div>
                            <div class="col-3 text-left font-weight-bold">{{ number_format($vat_value ?? 0) }}</div>
                        </div>
                    @endif
                    @if ((isVatEnabled() || isWithholdingTaxEnabled()) && ($fieldStates['withholding_tax']['showAggregated'] ?? false))
                        <div class="row mb-1 align-items-center small text-info">
                            <div class="col-5 text-right font-weight-bold"><i class="fas fa-calculator"></i> {{ __('إجمالي خصم الضريبة على الأصناف:') }}</div>
                            <div class="col-3 text-left font-weight-bold">{{ number_format($withholding_tax_value ?? 0) }}</div>
                        </div>
                    @endif
                    <hr class="my-1">
                    @if ($type != 21)
                        <div class="row mb-1 align-items-center g-1">
                            <div class="col-3 text-right fw-bold small">{{ __('Net') }}</div>
                            <div class="col-3 text-left font-weight-bold small main-num" id="display-total">{{ number_format($total_after_additional) }}</div>
                        </div>
                    @endif
                    <div class="row mb-1 align-items-center g-1">
                        @if ($type != 21)
                            @php $isSalesInvoice = in_array($type, [10, 12, 14, 16, 19, 22]); $isPurchaseInvoice = in_array($type, [11, 13, 15, 17, 20, 24, 25]); @endphp
                            <div class="col-3 text-right font-weight-bold small">@if ($isPurchaseInvoice){{ __('Paid to Supplier:') }}@else{{ __('Paid by Customer:') }}@endif</div>
                            <div class="col-3 text-left font-weight-bold small" id="display-received">{{ number_format($received_from_client) }}</div>
                        @endif
                        <div class="col-3 text-left">
                            @if (View::getSection('formAction') === 'edit')
                                <button type="submit" class="btn btn-sm btn-main" wire:loading.attr="disabled" wire:target="updateForm">
                                    <span wire:loading wire:target="updateForm" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    <span wire:loading.remove wire:target="updateForm"><i class="fas fa-save"></i> {{ __('Update Invoice') }}</span>
                                    <span wire:loading wire:target="updateForm">{{ __('Updating...') }}</span>
                                </button>
                            @else
                                @canany(['create ' . $titles[$type], 'create invoices'])
                                    <button type="submit" class="btn btn-sm btn-main"><i class="fas fa-save"></i> {{ __('Save Invoice') }}</button>
                                @endcanany
                            @endif
                        </div>
                        @if (setting('enable_installment_from_invoice') && $type == 10)
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-sm btn-info" id="btn-installment-invoice"
                                    data-total="{{ $total_after_additional ?? 0 }}"
                                    data-client="{{ $acc1_id ?? '' }}">
                                    <i class="las la-calendar-check"></i> {{ __('Installment') }}
                                </button>
                            </div>
                        @endif

                        @if (View::getSection('formAction') === 'edit')
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-sm btn-secondary" wire:click="cancelUpdate" wire:loading.attr="disabled">
                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                </button>
                            </div>
                        @endif
                        @can('print ' . $titles[$type])
                            @if (!setting('invoice_allow_print'))
                                <div class="col-3 text-left">
                                    <button type="button" class="btn btn-sm btn-warning" wire:click.debounce.500ms="saveAndPrint" wire:loading.attr="disabled">
                                        <span wire:loading wire:target="saveAndPrint">{{ __('Saving...') }}</span>
                                        <span wire:loading.remove wire:target="saveAndPrint"><i class="fas fa-save"></i> {{ __('Save and Print') }}</span>
                                    </button>
                                </div>
                            @endif
                        @endcan
                    </div>
                    @if ($type != 21)
                        @php $remaining = $total_after_additional - $received_from_client; @endphp
                        <div class="row mb-0 g-1">
                            <div class="col-3 text-right font-weight-bold small">{{ __('Remaining:') }}</div>
                            <div class="col-3 text-left font-weight-bold small {{ $remaining > 0.01 ? 'text-danger' : ($remaining < -0.01 ? 'text-success' : '') }}" id="display-remaining">{{ number_format($remaining) }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
