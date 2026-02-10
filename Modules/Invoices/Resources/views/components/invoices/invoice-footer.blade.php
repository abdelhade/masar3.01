@php
    // Inject InvoiceFormStateManager to get field states
    $fieldStates = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getFieldStates();
    $jsConfig = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getJavaScriptConfig();
@endphp
<div id="invoice-fixed-footer" class="p-2 mt-auto" style="z-index: 999; background: #fff;" x-data="{ fieldStates: @js($fieldStates) }">
    <div class="row border border-secondary border-3 rounded p-2 mb-2">
        @if (setting('invoice_show_item_details'))
            <div class="col-3">
                @if ($currentSelectedItem)
                    <div class="card" style="font-size: 0.75rem;">
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
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ $selectedItemData['name'] }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-5">{{ __('Store:') }}</div>
                                        <div class="col-7">
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ $selectedItemData['selected_store_name'] }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-5">{{ __('Available in Store:') }}</div>
                                        <div class="col-7">
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                                {{ $selectedItemData['available_quantity_in_store'] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-6">{{ __('Total in Stores:') }}</div>
                                        <div class="col-6">
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                                {{ $selectedItemData['total_available_quantity'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 ps-2">
                                    <div class="row mb-1">
                                        <div class="col-6">{{ __('Unit:') }}</div>
                                        <div class="col-6">
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ $selectedItemData['unit_name'] }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-6">{{ __('Price:') }}</div>
                                        <div class="col-6 text-primary fw-bold">
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                                {{ number_format($selectedItemData['price'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-6">{{ __('Last Purchase Price:') }}</div>
                                        <div class="col-6 text-success">
                                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                                {{ number_format($selectedItemData['last_purchase_price'] ?? 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-6">{{ __('Average Purchase Price:') }}</div>
                                        <div class="col-6 text-success">
                                            <span class="badge bg-light text-dark main-num" style="font-size: 0.7rem;">
                                                {{ number_format($selectedItemData['average_cost'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card" style="font-size: 0.75rem;">
                        <div class="card-body text-center text-muted p-2">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('Search for an item to display its data here') }}</p>
                        </div>
                    </div>
                @endif
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
                        <div class="card-body p-2">
                            @if (!empty($recommendedItems) && $type == 10)
                                <ul class="list-group list-group-flush">
                                    @foreach ($recommendedItems as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-1" style="font-size: 0.7rem;">
                                            <span>{{ $item['name'] }} ({{ $item['total_quantity'] }} {{ __('Unit') }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted text-center mb-0">{{ __('No recommendations available') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="col-2">
                </div>
            @endif
        @endif

        @if ($type != 21)
            <div class="col-2">
                <div class="card" style="font-size: 0.75rem;">
                    <div class="card-body p-2">
                        <div class="form-group mb-2">
                            <label for="cash_box_id" style="font-size: 0.75rem;">{{ __('Cash Box') }}</label>
                            <select wire:model="cash_box_id" class="form-control form-control-sm"
                                style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;">
                                @foreach ($cashAccounts as $account)
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
                            <input type="number" step="0.01" x-model.number="receivedFromClient"
                                @input="updateReceived()" :disabled="isCashAccount" :readonly="isCashAccount"
                                id="received-from-client" class="form-control form-control-sm scnd"
                                :class="{ 'bg-light': isCashAccount }"
                                style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" min="0"
                                :title="isCashAccount ? '{{ __('This field is automatically set for cash accounts') }}' : ''">
                        </div>

                        <div class="form-group mb-0">
                            <label for="notes" style="font-size: 0.75rem;">{{ __('Notes') }}</label>
                            <textarea wire:model="notes" class="form-control form-control-sm" rows="1"
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
                            <div class="col-3 text-left text-primary" id="display-subtotal"
                                x-text="window.formatNumberFixed(subtotal || 0)" style="font-size: 0.85rem;">
                                {{ number_format($subtotal) }}
                            </div>
                        </div>
                    @endif
                    @if ($type != 18 && $type != 21)
                        <div class="row mb-1 align-items-center">
                            <div class="col-2 text-right font-weight-bold">
                                <label style="font-size: 0.75rem;">{{ __('Discount %') }}</label>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" x-model.number="discountPercentage"
                                        onclick="this.select()" @input="updateDiscountFromPercentage()"
                                        id="discount-percentage" class="form-control"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" min="0"
                                        max="100" :disabled="!fieldStates.discount.invoice"
                                        :class="{ 'bg-light': !fieldStates.discount.invoice }">
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 text-right font-weight-bold">
                                <label for="discount_value" class="form-label" style="font-size: 0.75rem;">قيمة الخصم</label>
                            </div>
                            <div class="col-3">
                                <input type="text" inputmode="decimal" pattern="[0-9]*\.?[0-9]*"
                                    x-model="discountValueText" onclick="this.select()"
                                    @input.debounce.500ms="discountValue = parseFloat(discountValueText) || 0; updateDiscountFromValue()"
                                    @blur="discountValue = parseFloat(discountValueText) || 0; updateDiscountFromValue()"
                                    @focus="$event.target.select()" class="form-control form-control-sm"
                                    style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" id="discount-value"
                                    :disabled="!fieldStates.discount.invoice"
                                    :class="{ 'bg-light': !fieldStates.discount.invoice }">
                            </div>
                        </div>

                        <div class="row mb-1 align-items-center">
                            <div class="col-2 text-right font-weight-bold">
                                <label style="font-size: 0.75rem;">{{ __('Additional %') }}</label>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" x-model.number="additionalPercentage"
                                        onclick="this.select()" @input="updateAdditionalFromPercentage()"
                                        id="additional-percentage" class="form-control"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" min="0"
                                        max="100" :disabled="!fieldStates.additional.invoice"
                                        :class="{ 'bg-light': !fieldStates.additional.invoice }">
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 text-right font-weight-bold">
                                <label for="additional_value" class="form-label" style="font-size: 0.75rem;">{{ __('Additional Value') }}</label>
                            </div>
                            <div class="col-3">
                                <input type="text" inputmode="decimal" pattern="[0-9]*\.?[0-9]*"
                                    x-model="additionalValueText" onclick="this.select()"
                                    @input.debounce.500ms="additionalValue = parseFloat(additionalValueText) || 0; updateAdditionalFromValue()"
                                    @blur="additionalValue = parseFloat(additionalValueText) || 0; updateAdditionalFromValue()"
                                    @focus="$event.target.select()" class="form-control form-control-sm"
                                    style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" id="additional-value"
                                    :disabled="!fieldStates.additional.invoice"
                                    :class="{ 'bg-light': !fieldStates.additional.invoice }">
                            </div>
                        </div>


                        {{-- ضريبة القيمة المضافة (VAT) - يظهر فقط إذا كان مفعل --}}
                        @if (isVatEnabled())
                            <div class="row mb-1 align-items-center">
                                <div class="col-2 text-right font-weight-bold">
                                    <label style="font-size: 0.75rem;">{{ __('VAT %') }}</label>
                                </div>
                                <div class="col-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" x-model.number="vatPercentage" readonly
                                            onclick="this.select()" class="form-control bg-light"
                                            style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;"
                                            title="النسبة من الإعدادات" :disabled="!fieldStates.vat.invoice">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 text-right font-weight-bold">
                                    <label for="vat_value" class="form-label" style="font-size: 0.75rem;">{{ __('VAT Value') }}</label>
                                </div>
                                <div class="col-3">
                                    <input type="number" step="0.01" x-model.number="vatValue" readonly
                                        onclick="this.select()" class="form-control form-control-sm bg-light"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;" id="vat-value"
                                        title="تُحسب تلقائياً" :disabled="!fieldStates.vat.invoice">
                                </div>
                            </div>

                            <div class="row mb-1 align-items-center">
                                <div class="col-2 text-right font-weight-bold">
                                    <label style="font-size: 0.75rem;">{{ __('Withholding Tax %') }}</label>
                                </div>
                                <div class="col-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" x-model.number="withholdingTaxPercentage" 
                                            onclick="this.select()" readonly class="form-control bg-light"
                                            style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;"
                                            title="النسبة من الإعدادات" :disabled="!fieldStates.withholding_tax.invoice">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="font-size: 0.75rem;">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 text-right font-weight-bold">
                                    <label for="withholding_tax_value" class="form-label" style="font-size: 0.75rem;">{{ __('Withholding Tax Value') }}</label>
                                </div>
                                <div class="col-3">
                                    <input type="number" step="0.01" x-model.number="withholdingTaxValue"
                                        readonly onclick="this.select()" class="form-control form-control-sm bg-light"
                                        style="font-size: 0.75rem; height: 1.8em; padding: 2px 4px;"
                                        id="withholding-tax-value" title="تُحسب تلقائياً"
                                        :disabled="!fieldStates.withholding_tax.invoice">
                                </div>
                            </div>
                        @endif
                    @endif

                    @if (isVatEnabled() || isWithholdingTaxEnabled())
                        <div x-show="fieldStates.vat.showAggregated" x-cloak class="row mb-1 align-items-center border-top pt-1">
                            <div class="col-5 text-right font-weight-bold text-info" style="font-size: 0.75rem;">
                                <i class="fas fa-calculator"></i> {{ __('إجمالي الضريبة على الأصناف:') }}
                            </div>
                            <div class="col-3 text-left font-weight-bold text-info" style="font-size: 0.75rem;"
                                x-text="window.formatNumberFixed(calculateAggregatedTax())">
                                0.00
                            </div>
                        </div>
                        <div x-show="fieldStates.withholding_tax.showAggregated" x-cloak class="row mb-1 align-items-center">
                            <div class="col-5 text-right font-weight-bold text-info" style="font-size: 0.75rem;">
                                <i class="fas fa-calculator"></i> {{ __('إجمالي خصم الضريبة على الأصناف:') }}
                            </div>
                            <div class="col-3 text-left font-weight-bold text-info" style="font-size: 0.75rem;"
                                x-text="window.formatNumberFixed(calculateAggregatedTaxDiscount())">
                                0.00
                            </div>
                        </div>
                    @endif

                    <hr class="my-1">
                    @if ($type != 21)
                        <div class="row mb-1">
                            <div class="col-3 text-right fw-bolder" style="font-size: 0.9rem;">{{ __('Net') }}</div>
                            <div class="col-3 text-left font-weight-bold main-num" id="display-total" style="font-size: 0.9rem;"
                                x-text="window.formatNumberFixed(totalAfterAdditional || 0)">
                                {{ number_format($total_after_additional) }}
                            </div>
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
                            <div class="col-3 text-left font-weight-bold" id="display-received" style="font-size: 0.8rem;"
                                x-text="window.formatNumberFixed(receivedFromClient || 0)">
                                {{ number_format($received_from_client) }}
                            </div>
                        @endif
                        <div class="col-3 text-left">
                            @if (View::getSection('formAction') === 'edit')
                                <button type="submit" class="btn btn-md btn-main" wire:loading.attr="disabled"
                                    wire:target="updateForm" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                    <span wire:loading wire:target="updateForm"
                                        class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    <span wire:loading.remove wire:target="updateForm"><i class="fas fa-save"></i>
                                        {{ __('Update Invoice') }}</span>
                                    <span wire:loading wire:target="updateForm">{{ __('Updating...') }}</span>
                                </button>
                            @else
                                @canany(['create ' . $titles[$type], 'create invoices'])
                                    <button type="submit" class="btn btn-md btn-main" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-save"></i> {{ __('Save Invoice') }}
                                    </button>
                                @endcanany
                            @endif
                        </div>

                        @if (setting('enable_installment_from_invoice') && $type == 10)
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-md btn-info" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;"
                                    onclick="
                                    const form = this.closest('form');
                                    const alpineData = form?._x_dataStack?.[0];
                                    const finalTotal = alpineData?.totalAfterAdditional || 0;
                                    const clientId = alpineData?.$wire?.acc1_id;
                                    if (!clientId || clientId === 'null' || clientId === null) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'تحذير',
                                            text: 'يرجى اختيار العميل في الفاتورة أولاً',
                                            confirmButtonText: 'حسناً'
                                        });
                                        return;
                                    }
                                    const modalEl = document.getElementById('installmentModal');
                                    if (modalEl) {
                                        const modal = new bootstrap.Modal(modalEl);
                                        modal.show();
                                        setTimeout(() => {
                                            const totalInput = document.getElementById('totalAmount');
                                            if (totalInput) {
                                                totalInput.value = finalTotal;
                                                totalInput.dispatchEvent(new Event('input', { bubbles: true }));
                                            }
                                            const clientSelect = document.getElementById('accHeadId');
                                            if (clientSelect) {
                                                clientSelect.value = clientId;
                                                clientSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                            }
                                        }, 300);
                                    }
                                ">
                                    <i class="las la-calendar-check"></i> {{ __('Installment') }}
                                </button>
                            </div>
                        @endif

                        @if (View::getSection('formAction') === 'edit')
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-md btn-secondary" wire:click="cancelUpdate"
                                    wire:loading.attr="disabled" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                </button>
                            </div>
                        @endif

                        @can('print ' . $titles[$type])
                            @if (!setting('invoice_allow_print'))
                                <div class="col-3 text-left">
                                    <button type="button" class="btn btn-md btn-warning"
                                        wire:click.debounce.500ms="saveAndPrint" wire:loading.attr="disabled"
                                        style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                        <span wire:loading wire:target="saveAndPrint">{{ __('Saving...') }}</span>
                                        <span wire:loading.remove wire:target="saveAndPrint">
                                            <i class="fas fa-save"></i> {{ __('Save and Print') }}
                                        </span>
                                    </button>
                                </div>
                            @endif
                        @endcan
                    </div>

                    @if ($type != 21)
                        <div class="row">
                            <div class="col-3 text-right font-weight-bold" style="font-size: 0.8rem;">{{ __('Remaining:') }}</div>
                            <div class="col-3 text-left font-weight-bold" style="font-size: 0.8rem;"
                                :class="remaining > 0.01 ? 'text-danger' : (remaining < -0.01 ? 'text-success' : '')"
                                id="display-remaining" x-text="window.formatNumberFixed(remaining || 0)">
                                @php
                                    $remaining = $total_after_additional - $received_from_client;
                                @endphp
                                {{ number_format($remaining) }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
