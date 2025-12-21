<div class="row mt-4 p-3 bg-light">
    @if (setting('invoice_show_item_details'))
        <div class="col-3">
            @if ($currentSelectedItem)
                <div class="card border-primary">
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
                                        <span class="badge bg-light text-dark">{{ $selectedItemData['name'] }}</span>
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
                <div class="card border-primary">
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
                <div class="card border-primary">
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
            <div class="card border-primary">



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
                        <input type="number" step="0.01" 
                            x-model.number="receivedFromClient"
                            @input="updateReceived()" 
                            :disabled="isCashAccount"
                            :readonly="isCashAccount"
                            id="received-from-client"
                            class="form-control form-control-sm scnd"
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

    <div class="col-5 ">
        <div class="card border-primary">
            <div class="card-body">
                @if ($type != 21)
                    {{-- إضافة الإجمالي الفرعي لا ينطبق على التحويلات --}}
                    <div class="row mb-2">
                        <div class="col-3 text-right font-weight-bold">{{ __('Subtotal:') }}</div>
                        <div class="col-3 text-left text-primary" id="display-subtotal" x-text="window.formatNumberFixed(subtotal || 0)">
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
                                <input type="number" step="0.01" 
                                    x-model.number="discountPercentage"
                                    @input="if (discountPercentage !== null && discountPercentage !== undefined) { discountPercentage = parseFloat(parseFloat(discountPercentage || 0).toFixed(2)); } updateDiscountFromPercentage()"
                                    id="discount-percentage" 
                                    class="form-control form-control-sm"
                                    style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                    max="100">
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
                            <input type="number" step="0.01" 
                                x-model.number="discountValue"
                                @input="updateDiscountFromValue()"
                                @focus="$event.target.select()"
                                class="form-control form-control-sm"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                id="discount-value">
                        </div>


                    </div>


                    {{-- الإضافي (مثال: ضريبة) --}}
                    <div class="row mb-2 align-items-center">
                        <div class="col-2 text-right font-weight-bold">
                            <label style="font-size: 0.95em;">{{ __('Additional %') }}</label>
                        </div>


                        <div class="col-3">
                            <div class="input-group">
                                <input type="number" step="0.01" 
                                    x-model.number="additionalPercentage"
                                    @input="if (additionalPercentage !== null && additionalPercentage !== undefined) { additionalPercentage = parseFloat(parseFloat(additionalPercentage || 0).toFixed(2)); } updateAdditionalFromPercentage()"
                                    id="additional-percentage" 
                                    class="form-control form-control-sm"
                                    style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                    max="100">
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
                            <input type="number" step="0.01" 
                                x-model.number="additionalValue"
                                @input="updateAdditionalFromValue()"
                                @focus="$event.target.select()"
                                class="form-control form-control-sm"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                id="additional-value">
                        </div>
                    </div>
                @endif
                <hr>
                {{-- الإجمالي النهائي --}}
                @if ($type != 21)
                    {{-- إضافة الإجمالي النهائي لا ينطبق على التحويلات --}}
                    <div class="row mb-2">
                        <div class="col-3 text-right font-weight-bold">{{ __('Final Total:') }}</div>
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
                        <div class="col-3 text-left font-weight-bold fs-5" id="display-received" x-text="window.formatNumberFixed(receivedFromClient || 0)">
                            {{ number_format($received_from_client) }}
                        </div>
                    @endif {{-- إضافة المدفوع من العميل لا ينطبق على التحويلات --}}
                    <div class="col-3 text-left">
                        @if (View::getSection('formAction') === 'edit')
                            <button type="submit" class="btn btn-lg btn-main" wire:loading.attr="disabled">
                                <i class="fas fa-save"></i> {{ __('Update Invoice') }}
                            </button>
                        @else
                            @canany(['create ' . $titles[$type], 'create invoices'])
                                <button type="submit" class="btn btn-lg btn-main" wire:attr="disabled">
                                    <i class="fas fa-save"></i> {{ __('Save Invoice') }}
                                </button>
                            @endcanany
                        @endif
                    </div>


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
                            id="display-remaining" 
                            x-text="window.formatNumberFixed(remaining || 0)">
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
