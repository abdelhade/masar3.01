<div id="invoice-fixed-footer" class="p-2" style="background: #fff; border-top: 3px solid #dee2e6; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
    <div class="row border border-secondary border-3 rounded p-2 mb-0">
        @if (setting('invoice_show_item_details'))
            <div class="col-3">
                <div class="card" style="font-size: 0.75rem;" id="item-details-card">
                    <div class="card-header text-white py-1">
                        <h6 class="mb-0" style="font-size: 0.8rem;">
                            <i class="fas fa-box"></i> {{ __('Item Details') }}
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row gx-2">
                            <div class="col-md-6 border-end pe-2">
                                <div class="row mb-1">
                                    <div class="col-5">{{ __('Name:') }}</div>
                                    <div class="col-7 fw-bold">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-name">-</span>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5">{{ __('Store:') }}</div>
                                    <div class="col-7">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-store">-</span>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-5">{{ __('Available in Store:') }}</div>
                                    <div class="col-7">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-available">-</span>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-6">{{ __('Total in Stores:') }}</div>
                                    <div class="col-6">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-total">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 ps-2">
                                <div class="row mb-1">
                                    <div class="col-6">{{ __('Unit:') }}</div>
                                    <div class="col-6">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-unit">-</span>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-6">{{ __('Price:') }}</div>
                                    <div class="col-6 text-primary fw-bold">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-price">-</span>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-6">{{ __('Last Purchase Price:') }}</div>
                                    <div class="col-6 text-success">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem;" id="selected-item-last-price">-</span>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-6">{{ __('Average Purchase Price:') }}</div>
                                    <div class="col-6 text-success">
                                        <span class="badge bg-light text-dark main-num" style="font-size: 0.7rem;" id="selected-item-avg-cost">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (setting('invoice_show_recommended_items'))
            @if ($type == 10)
                <div class="col-2">
                    <div class="card" style="font-size: 0.75rem;">
                        <div class="card-header text-white py-1">
                            <h6 class="mb-0" style="font-size: 0.8rem;">
                                <i class="fas fa-star"></i> {{ __('Recommendations (Top 5 Purchased Items)') }}
                            </h6>
                        </div>
                        <div class="card-body p-2" id="recommended-items-list">
                            <p class="text-muted text-center mb-0">{{ __('No recommendations available') }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-2"></div>
            @endif
        @endif

        @if ($type != 21)
            <div class="col-2">
                <div class="card" style="font-size: 0.75rem;">
                    <div class="card-body p-2">
                        <div class="form-group mb-2">
                            <label for="cash_box_id" style="font-size: 0.75rem;">{{ __('Cash Box') }}</label>
                            <select id="cash_box_id" class="form-control form-control-sm"
                                style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;">
                                @foreach ($cashAccounts ?? [] as $account)
                                    <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            @php
                                $isSalesInvoice = in_array($type, [10, 12, 14, 16, 19, 22]);
                                $isPurchaseInvoice = in_array($type, [11, 13, 15, 17, 20, 24, 25]);
                            @endphp
                            @if ($isPurchaseInvoice)
                                <label for="received_from_client" style="font-size: 0.75rem;">{{ __('Amount Paid to Supplier') }}</label>
                            @else
                                <label for="received_from_client" style="font-size: 0.75rem;">{{ __('Amount Received from Customer') }}</label>
                            @endif
                            <input type="number" step="0.01" id="received-from-client" class="form-control form-control-sm scnd"
                                style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" min="0" value="0">
                        </div>

                        <div class="form-group mb-0">
                            <label for="notes" style="font-size: 0.75rem;">{{ __('Notes') }}</label>
                            <textarea id="notes" class="form-control form-control-sm" rows="1"
                                placeholder="{{ __('Additional notes...') }}" style="font-size: 0.75rem; padding: 4px;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-5 ms-auto">
            <div class="card" style="font-size: 0.75rem;">
                <div class="card-body p-2">
                    @if ($type != 21)
                        <div class="row mb-1">
                            <div class="col-3 text-right fw-bolder" style="font-size: 0.85rem;">{{ __('Subtotal:') }}</div>
                            <div class="col-3 text-left text-primary" id="display-subtotal" style="font-size: 0.85rem;">0</div>
                        </div>
                    @endif
                    
                    @if ($type != 18 && $type != 21)
                        <div class="row mb-1 align-items-center">
                            <div class="col-2 text-right font-weight-bold">
                                <label style="font-size: 0.75rem;">{{ __('Discount %') }}</label>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" id="discount-percentage" class="form-control"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" min="0" max="100" value="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 text-right font-weight-bold">
                                <label for="discount_value" class="form-label" style="font-size: 0.75rem;">قيمة الخصم</label>
                            </div>
                            <div class="col-3">
                                <input type="number" step="0.01" id="discount-value" class="form-control form-control-sm"
                                    style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" value="0" min="0">
                            </div>
                        </div>

                        <div class="row mb-1 align-items-center">
                            <div class="col-2 text-right font-weight-bold">
                                <label style="font-size: 0.75rem;">{{ __('Additional %') }}</label>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" id="additional-percentage" class="form-control"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" min="0" max="100" value="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 text-right font-weight-bold">
                                <label for="additional_value" class="form-label" style="font-size: 0.75rem;">{{ __('Additional Value') }}</label>
                            </div>
                            <div class="col-3">
                                <input type="number" step="0.01" id="additional-value" class="form-control form-control-sm"
                                    style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" value="0" min="0">
                            </div>
                        </div>

                        {{-- VAT --}}
                        @if (isVatEnabled())
                            <div class="row mb-1 align-items-center">
                                <div class="col-2 text-right font-weight-bold">
                                    <label style="font-size: 0.75rem;">{{ __('VAT %') }}</label>
                                </div>
                                <div class="col-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" id="vat-percentage" readonly class="form-control bg-light"
                                            style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" value="{{ $vatPercentage ?? 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 text-right font-weight-bold">
                                    <label for="vat_value" class="form-label" style="font-size: 0.75rem;">{{ __('VAT Value') }}</label>
                                </div>
                                <div class="col-3">
                                    <input type="number" step="0.01" id="vat-value-display" readonly class="form-control form-control-sm bg-light"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" value="0">
                                </div>
                            </div>

                            <div class="row mb-1 align-items-center">
                                <div class="col-2 text-right font-weight-bold">
                                    <label style="font-size: 0.75rem;">{{ __('Withholding Tax %') }}</label>
                                </div>
                                <div class="col-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" id="withholding-tax-percentage" readonly class="form-control bg-light"
                                            style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" value="{{ $withholdingTaxPercentage ?? 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 text-right font-weight-bold">
                                    <label for="withholding_tax_value" class="form-label" style="font-size: 0.75rem;">{{ __('Withholding Tax Value') }}</label>
                                </div>
                                <div class="col-3">
                                    <input type="number" step="0.01" id="withholding-tax-value-display" readonly class="form-control form-control-sm bg-light"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" value="0">
                                </div>
                            </div>
                        @endif
                    @endif

                    <hr class="my-1">
                    
                    @if ($type != 21)
                        <div class="row mb-1">
                            <div class="col-3 text-right fw-bolder" style="font-size: 0.9rem;">{{ __('Net') }}</div>
                            <div class="col-3 text-left font-weight-bold main-num" id="display-total" style="font-size: 0.9rem;">0</div>
                        </div>
                    @endif
                    
                    <div class="row mb-1">
                        @if ($type != 21)
                            @php
                                $isSalesInvoice = in_array($type, [10, 12, 14, 16, 19, 22]);
                                $isPurchaseInvoice = in_array($type, [11, 13, 15, 17, 20, 24, 25]);
                            @endphp
                            <div class="col-3 text-right font-weight-bold" style="font-size: 0.8rem;">
                                @if ($isPurchaseInvoice)
                                    {{ __('Paid to Supplier:') }}
                                @else
                                    {{ __('Paid by Customer:') }}
                                @endif
                            </div>
                            <div class="col-3 text-left font-weight-bold" id="display-received" style="font-size: 0.8rem;">0</div>
                        @endif
                        
                        <div class="col-3 text-left">
                            <button type="submit" class="btn btn-md btn-main" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                <i class="fas fa-save"></i> {{ __('Save Invoice') }}
                            </button>
                        </div>

                        @if (setting('enable_installment_from_invoice') && $type == 10)
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-md btn-info" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                    <i class="las la-calendar-check"></i> {{ __('Installment') }}
                                </button>
                            </div>
                        @endif
                    </div>

                    @if ($type != 21)
                        <div class="row">
                            <div class="col-3 text-right font-weight-bold" style="font-size: 0.8rem;">{{ __('Remaining:') }}</div>
                            <div class="col-3 text-left font-weight-bold" style="font-size: 0.8rem;" id="display-remaining">0</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSS for Fixed Footer within content --}}
<style>
    /* Footer fixed within content area - responsive automatically */
