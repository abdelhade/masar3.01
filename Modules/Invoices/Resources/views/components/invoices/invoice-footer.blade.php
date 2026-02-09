@php
    // Inject InvoiceFormStateManager to get field states
    $fieldStates = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getFieldStates();
    $jsConfig = app(\Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class)->getJavaScriptConfig();
@endphp
<div id="invoice-fixed-footer" class="p-3 mt-auto" style="z-index: 999; background: #fff;" x-data="{ fieldStates: @js($fieldStates) }">
    <div class="row border border-secondary border-3 rounded p-3 mb-3">
        @if (setting('invoice_show_item_details'))
            <div class="col-3">
                @if ($currentSelectedItem)
                    <div class="card">
                        <div class="card-header text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-box"></i> {{ __('Item Details') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row gx-4">


                                <div class="col-md-6 border-end pe-3">


                                    <div class="row mb-2">
                                        <div class="col-5 fs-6">{{ __('Name:') }}</div>
                                        <div class="col-7 fw-bold">
                                            <span
                                                class="badge bg-light text-dark">{{ $selectedItemData['name'] }}</span>
                                        </div>
                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-5 fs-6">{{ __('Store:') }}</div>
                                        <div class="col-7">
                                            <span
                                                class="badge bg-light text-dark">{{ $selectedItemData['selected_store_name'] }}</span>
                                        </div>
                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-5 fs-6">{{ __('Available in Store:') }}</div>
                                        <div class="col-7">
                                            <span class="badge bg-light text-dark">
                                                {{ $selectedItemData['available_quantity_in_store'] }}
                                            </span>
                                        </div>
                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-6 fs-6">{{ __('Total in Stores:') }}</div>
                                        <div class="col-6">
                                            <span class="badge bg-light text-dark">
                                                {{ $selectedItemData['total_available_quantity'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-6 ps-3">


                                    <div class="row mb-2">
                                        <div class="col-6 fs-6">{{ __('Unit:') }}</div>
                                        <div class="col-6">
                                            <span
                                                class="badge bg-light text-dark">{{ $selectedItemData['unit_name'] }}</span>
                                        </div>
                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-6 fs-6">{{ __('Price:') }}</div>
                                        <div class="col-6 text-primary fw-bold">
                                            <span class="badge bg-light text-dark">
                                                {{ number_format($selectedItemData['price'], 2) }}
                                            </span>
                                        </div>
                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-6 fs-6">{{ __('Last Purchase Price:') }}</div>
                                        <div class="col-6 text-success">
                                            <span class="badge bg-light text-dark">
                                                {{ number_format($selectedItemData['last_purchase_price'] ?? 0, 2) }}
                                            </span>
                                        </div>
                                    </div>


                                    <div class="row mb-2">
                                        <div class="col-6 fs-6">{{ __('Average Purchase Price:') }}</div>
                                        <div class="col-6 text-success">
                                            <span class="badge bg-light text-dark main-num">
                                                {{ number_format($selectedItemData['average_cost'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>{{ __('Search for an item to display its data here') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif


        @if (setting('invoice_show_recommended_items'))
            @if ($type == 10)
                <div class="col-2">
                    <div class="card">
                        <div class="card-header text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-star"></i> {{ __('Recommendations (Top 5 Purchased Items)') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if (!empty($recommendedItems) && $type == 10)
                                <ul class="list-group">
                                    @foreach ($recommendedItems as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>{{ $item['name'] }} ({{ $item['total_quantity'] }}
                                                {{ __('Unit') }})</span>
                                            {{-- <button wire:click="addRecommendedItem({{ $item['id'] }})"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> {{ __('Add') }}
                                    </button> --}}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted text-center">{{ __('No recommendations available') }}</p>
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
                <div class="card">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="cash_box_id" style="font-size: 1em;">{{ __('Cash Box') }}</label>
                            <select wire:model="cash_box_id" class="form-control form-control-sm"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;">
                                {{-- <option value="">{{ __('Choose Cash Box') }}</option> --}}
                                @foreach ($cashAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                @endforeach
                            </select>
                        </div>



                        <div class="form-group mb-3">
                            @php
                                // تحديد نوع الفاتورة: بيع أو شراء
                                $isSalesInvoice = in_array($type, [10, 12, 14, 16, 19, 22]); // بيع
                                $isPurchaseInvoice = in_array($type, [11, 13, 15, 17, 20, 24, 25]); // شراء
                            @endphp
                            @if ($isPurchaseInvoice)
                                <label for="received_from_client"
                                    style="font-size: 1em;">{{ __('Amount Paid to Supplier') }}</label>
                            @else
                                <label for="received_from_client"
                                    style="font-size: 1em;">{{ __('Amount Received from Customer') }}</label>
                            @endif
                            <input type="number" step="0.01" x-model.number="receivedFromClient"
                                @input="updateReceived()" :disabled="isCashAccount" :readonly="isCashAccount"
                                id="received-from-client" class="form-control form-control-sm scnd"
                                :class="{ 'bg-light': isCashAccount }"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                :title="isCashAccount ? '{{ __('This field is automatically set for cash accounts') }}' : ''">
                        </div>



                        <div class="form-group mb-3">
                            <label for="notes" style="font-size: 1em;">{{ __('Notes') }}</label>
                            <textarea wire:model="notes" class="form-control form-control-sm" rows="1"
                                placeholder="{{ __('Additional notes...') }}" style="font-size: 0.95em; padding: 6px;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-5 ms-auto">
            <div class="card">
                <div class="card-body">
                    @if ($type != 21)
                        {{-- إضافة الإجمالي الفرعي لا ينطبق على التحويلات --}}
                        <div class="row mb-2">
                            <div class="col-3 text-right fw-bolder fs-4">{{ __('Subtotal:') }}</div>
                            <div class="col-3 text-left text-primary" id="display-subtotal"
                                x-text="window.formatNumberFixed(subtotal || 0)">
                                {{ number_format($subtotal) }}
                            </div>
                        </div>
                    @endif {{-- إضافة الإجمالي الفرعي لا ينطبق على التحويلات --}}
                    @if ($type != 18 && $type != 21)
                        {{-- الخصم --}}
                        <div class="row mb-2 align-items-center">
                            <div class="col-2 text-right font-weight-bold">
                                <label style="font-size: 0.95em;">{{ __('Discount %') }}</label>
                            </div>
                            <div class="col-3">
                                <div class="input-group">
                                    <input type="number" step="0.01" x-model.number="discountPercentage"
                                        onclick="this.select()" @input="updateDiscountFromPercentage()"
                                        id="discount-percentage" class="form-control form-control-sm"
                                        style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                        max="100" :disabled="!fieldStates.discount.invoice"
                                        :class="{ 'bg-light': !fieldStates.discount.invoice }">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>


                            <div class="col-2 text-right font-weight-bold">
                                <label for="discount_value" class="form-label" style="font-size: 0.95em;">قيمة
                                    الخصم</label>
                            </div>


                            <div class="col-3">
                                <input type="text" inputmode="decimal" pattern="[0-9]*\.?[0-9]*"
                                    x-model="discountValueText" onclick="this.select()"
                                    @input.debounce.500ms="discountValue = parseFloat(discountValueText) || 0; updateDiscountFromValue()"
                                    @blur="discountValue = parseFloat(discountValueText) || 0; updateDiscountFromValue()"
                                    @focus="$event.target.select()" class="form-control form-control-sm"
                                    style="font-size: 0.95em; height: 2em; padding: 2px 6px;" id="discount-value"
                                    :disabled="!fieldStates.discount.invoice"
                                    :class="{ 'bg-light': !fieldStates.discount.invoice }">
                            </div>


                        </div>


                        {{-- الإضافي (مثال: ضريبة) --}}
                        <div class="row mb-2 align-items-center">
                            <div class="col-2 text-right font-weight-bold">
                                <label style="font-size: 0.95em;">{{ __('Additional %') }}</label>
                            </div>


                            <div class="col-3">
                                <div class="input-group">
                                    <input type="number" step="0.01" x-model.number="additionalPercentage"
                                        onclick="this.select()" @input="updateAdditionalFromPercentage()"
                                        id="additional-percentage" class="form-control form-control-sm"
                                        style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                        max="100" :disabled="!fieldStates.additional.invoice"
                                        :class="{ 'bg-light': !fieldStates.additional.invoice }">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>


                            <div class="col-2 text-right font-weight-bold">
                                <label for="additional_value" class="form-label"
                                    style="font-size: 0.95em;">{{ __('Additional Value') }}</label>
                            </div>


                            <div class="col-3">
                                <input type="text" inputmode="decimal" pattern="[0-9]*\.?[0-9]*"
                                    x-model="additionalValueText" onclick="this.select()"
                                    @input.debounce.500ms="additionalValue = parseFloat(additionalValueText) || 0; updateAdditionalFromValue()"
                                    @blur="additionalValue = parseFloat(additionalValueText) || 0; updateAdditionalFromValue()"
                                    @focus="$event.target.select()" class="form-control form-control-sm"
                                    style="font-size: 0.95em; height: 2em; padding: 2px 6px;" id="additional-value"
                                    :disabled="!fieldStates.additional.invoice"
                                    :class="{ 'bg-light': !fieldStates.additional.invoice }">
                            </div>
                        </div>


                        {{-- ضريبة القيمة المضافة (VAT) - يظهر فقط إذا كان مفعل --}}
                        @if (isVatEnabled())
                            <div class="row mb-2 align-items-center">
                                <div class="col-2 text-right font-weight-bold">
                                    <label style="font-size: 0.95em;">{{ __('VAT %') }}</label>
                                </div>


                                <div class="col-3">
                                    <div class="input-group">
                                        <input type="number" step="0.01" x-model.number="vatPercentage" readonly
                                            onclick="this.select()" class="form-control form-control-sm bg-light"
                                            style="font-size: 0.95em; height: 2em; padding: 2px 6px;"
                                            title="النسبة من الإعدادات" :disabled="!fieldStates.vat.invoice">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-2 text-right font-weight-bold">
                                    <label for="vat_value" class="form-label"
                                        style="font-size: 0.95em;">{{ __('VAT Value') }}</label>
                                </div>


                                <div class="col-3">
                                    <input type="number" step="0.01" x-model.number="vatValue" readonly
                                        onclick="this.select()" class="form-control form-control-sm bg-light"
                                        style="font-size: 0.95em; height: 2em; padding: 2px 6px;" id="vat-value"
                                        title="تُحسب تلقائياً" :disabled="!fieldStates.vat.invoice">
                                </div>
                            </div>

                            {{-- خصم المنبع - يظهر مع الضريبة --}}
                            <div class="row mb-2 align-items-center">
                                <div class="col-2 text-right font-weight-bold">
                                    <label style="font-size: 0.95em;">{{ __('Withholding Tax %') }}</label>
                                </div>


                                <div class="col-3">
                                    <div class="input-group">
                                        <input type="number" step="0.01"
                                            x-model.number="withholdingTaxPercentage" onclick="this.select()" readonly
                                            class="form-control form-control-sm bg-light"
                                            style="font-size: 0.95em; height: 2em; padding: 2px 6px;"
                                            title="النسبة من الإعدادات"
                                            :disabled="!fieldStates.withholding_tax.invoice">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-2 text-right font-weight-bold">
                                    <label for="withholding_tax_value" class="form-label"
                                        style="font-size: 0.95em;">{{ __('Withholding Tax Value') }}</label>
                                </div>


                                <div class="col-3">
                                    <input type="number" step="0.01" x-model.number="withholdingTaxValue"
                                        readonly onclick="this.select()" class="form-control form-control-sm bg-light"
                                        style="font-size: 0.95em; height: 2em; padding: 2px 6px;"
                                        id="withholding-tax-value" title="تُحسب تلقائياً"
                                        :disabled="!fieldStates.withholding_tax.invoice">
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Aggregated Values Display (for item-level taxes) --}}
                    @if (isVatEnabled() || isWithholdingTaxEnabled())
                        <div x-show="fieldStates.vat.showAggregated" x-cloak
                            class="row mb-2 align-items-center border-top pt-2">
                            <div class="col-5 text-right font-weight-bold text-info">
                                <i class="fas fa-calculator"></i> {{ __('إجمالي الضريبة على الأصناف:') }}
                            </div>
                            <div class="col-3 text-left font-weight-bold text-info"
                                x-text="window.formatNumberFixed(calculateAggregatedTax())">
                                0.00
                            </div>
                        </div>

                        <div x-show="fieldStates.withholding_tax.showAggregated" x-cloak
                            class="row mb-2 align-items-center">
                            <div class="col-5 text-right font-weight-bold text-info">
                                <i class="fas fa-calculator"></i> {{ __('إجمالي خصم الضريبة على الأصناف:') }}
                            </div>
                            <div class="col-3 text-left font-weight-bold text-info"
                                x-text="window.formatNumberFixed(calculateAggregatedTaxDiscount())">
                                0.00
                            </div>
                        </div>
                    @endif

                    <hr>
                    {{-- الإجمالي النهائي --}}
                    @if ($type != 21)
                        {{-- إضافة الإجمالي النهائي لا ينطبق على التحويلات --}}
                        <div class="row mb-2">
                            <div class="col-3 text-right  fw-bolder fs-4">{{ __('Net') }}</div>
                            <div class="col-3 text-left font-weight-bold fs-5 main-num" id="display-total"
                                x-text="window.formatNumberFixed(totalAfterAdditional || 0)">
                                {{ number_format($total_after_additional) }}
                            </div>
                        </div>
                    @endif {{-- إضافة الإجمالي النهائي لا ينطبق على التحويلات --}}
                    <div class="row mb-2">
                        @if ($type != 21)
                            {{-- إضافة المدفوع من العميل لا ينطبق على التحويلات --}}
                            @php
                                $isSalesInvoice = in_array($type, [10, 12, 14, 16, 19, 22]); // بيع
                                $isPurchaseInvoice = in_array($type, [11, 13, 15, 17, 20, 24, 25]); // شراء
                            @endphp
                            <div class="col-3 text-right font-weight-bold">
                                @if ($isPurchaseInvoice)
                                    {{ __('Paid to Supplier:') }}
                                @else
                                    {{ __('Paid by Customer:') }}
                                @endif
                            </div>
                            <div class="col-3 text-left font-weight-bold fs-5" id="display-received"
                                x-text="window.formatNumberFixed(receivedFromClient || 0)">
                                {{ number_format($received_from_client) }}
                            </div>
                        @endif {{-- إضافة المدفوع من العميل لا ينطبق على التحويلات --}}
                        <div class="col-3 text-left">
                            @if (View::getSection('formAction') === 'edit')
                                <button type="submit" class="btn btn-lg btn-main" wire:loading.attr="disabled"
                                    wire:target="updateForm">
                                    <span wire:loading wire:target="updateForm"
                                        class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    <span wire:loading.remove wire:target="updateForm"><i class="fas fa-save"></i>
                                        {{ __('Update Invoice') }}</span>
                                    <span wire:loading wire:target="updateForm">{{ __('Updating...') }}</span>
                                </button>
                            @else
                                @canany(['create ' . $titles[$type], 'create invoices'])
                                    <button type="submit" class="btn btn-lg btn-main">
                                        <i class="fas fa-save"></i> {{ __('Save Invoice') }}
                                    </button>
                                @endcanany
                            @endif
                        </div>

                        {{-- زر التقسيط - فواتير المبيعات فقط --}}
                        @if (setting('enable_installment_from_invoice') && $type == 10)
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-lg btn-info"
                                    onclick="

                                    // Get Alpine.js data from the form
                                    const form = this.closest('form');
                                    const alpineData = form?._x_dataStack?.[0];

                                    const finalTotal = alpineData?.totalAfterAdditional || 0;
                                    const clientId = alpineData?.$wire?.acc1_id;

                                    // Check if client is selected
                                    if (!clientId || clientId === 'null' || clientId === null) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'تحذير',
                                            text: 'يرجى اختيار العميل في الفاتورة أولاً',
                                            confirmButtonText: 'حسناً'
                                        });
                                        return;
                                    }

                                    // Open modal
                                    const modalEl = document.getElementById('installmentModal');
                                    if (modalEl) {
                                        const modal = new bootstrap.Modal(modalEl);
                                        modal.show();

                                        // After modal is shown, update the input directly and trigger Livewire
                                        setTimeout(() => {
                                            // Set the total amount input value
                                            const totalInput = document.getElementById('totalAmount');
                                            if (totalInput) {
                                                totalInput.value = finalTotal;
                                                totalInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                console.log('✅ Total amount set to:', finalTotal);
                                            }

                                            // Set the client select value
                                            const clientSelect = document.getElementById('accHeadId');
                                            if (clientSelect) {
                                                clientSelect.value = clientId;
                                                clientSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                                console.log('✅ Client set to:', clientId);
                                            }
                                        }, 300);
                                    } else {
                                        console.error('❌ Modal element not found');
                                    }
                                ">
                                    <i class="las la-calendar-check"></i> {{ __('Installment') }}
                                </button>
                            </div>
                        @endif

                        @if (View::getSection('formAction') === 'edit')
                            <div class="col-3 text-left">
                                <button type="button" class="btn btn-lg btn-secondary" wire:click="cancelUpdate"
                                    wire:loading.attr="disabled">
                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                </button>
                            </div>
                        @endif


                        @can('print ' . $titles[$type])


                            @if (!setting('invoice_allow_print'))
                                <div class="col-3 text-left">
                                    <button type="button" class="btn btn-lg btn-warning"
                                        wire:click.debounce.500ms="saveAndPrint" wire:loading.attr="disabled">
                                        <span wire:loading wire:target="saveAndPrint">{{ __('Saving...') }}</span>
                                        <span wire:loading.remove wire:target="saveAndPrint">
                                            <i class="fas fa-save"></i> {{ __('Save and Print') }}
                                        </span>
                                    </button>
                                </div>
                            @endif
                        @endcan


                    </div>


                    {{-- الباقي على العميل --}}
                    @if ($type != 21)
                        {{-- إضافة الباقي لا ينطبق على التحويلات --}}
                        <div class="row">
                            <div class="col-3 text-right font-weight-bold">{{ __('Remaining:') }}</div>
                            <div class="col-3 text-left font-weight-bold"
                                :class="remaining > 0.01 ? 'text-danger' : (remaining < -0.01 ? 'text-success' : '')"
                                id="display-remaining" x-text="window.formatNumberFixed(remaining || 0)">
                                @php
                                    $remaining = $total_after_additional - $received_from_client;
                                @endphp
                                {{ number_format($remaining) }}
                            </div>
                        </div>
                    @endif {{-- إضافة الباقي لا ينطبق على التحويلات --}}
                </div>
            </div>
        </div>
    </div>
</div>
