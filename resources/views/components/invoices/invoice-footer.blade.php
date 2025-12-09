<div class="row mt-4 ">
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
                                            {{ number_format($selectedItemData['price']) }}
                                        </span>
                                    </div>
                                </div>


                                <div class="row mb-2">
                                    <div class="col-6 fs-6">{{ __('Last Purchase Price:') }}</div>
                                    <div class="col-6 text-success">
                                        <span class="badge bg-light text-dark">
                                            {{ number_format($selectedItemData['last_purchase_price'] ?? 0) }}
                                        </span>
                                    </div>
                                </div>


                                <div class="row mb-2">
                                    <div class="col-6 fs-6">{{ __('Average Purchase Price:') }}</div>
                                    <div class="col-6 text-success">
                                        <span class="badge bg-light text-dark main-num">
                                            {{ number_format($selectedItemData['average_cost']) }}
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
                        @if ($type == 11)
                            <label for="received_from_client"
                                style="font-size: 1em;">{{ __('Amount Paid to Supplier') }}</label>
                        @else
                            <label for="received_from_client"
                                style="font-size: 1em;">{{ __('Amount Received from Customer') }}</label>
                        @endif
                        <input type="number" step="0.01" 
                            x-model.number="$root.receivedFromClient"
                            x-on:keyup="
                                $root.receivedFromClient = parseFloat($event.target.value) || 0;
                                $root.receivedFromClient = isNaN($root.receivedFromClient) ? 0 : $root.receivedFromClient;
                                $wire.set('received_from_client', $root.receivedFromClient);
                                $root.syncToLivewire();
                            "
                            wire:model.blur="received_from_client"
                            class="form-control form-control-sm scnd"
                            style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0">
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

    <div class="col-5">
        <div class="card border-primary">
            <div class="card-body">
                @if ($type != 21)
                    {{-- إضافة الإجمالي الفرعي لا ينطبق على التحويلات --}}
                    <div class="row mb-2">
                        <div class="col-3 text-right font-weight-bold">{{ __('Subtotal:') }}</div>
                        <div class="col-3 text-left text-primary font-weight-bold" 
                            x-text="$root.subtotal !== undefined && !isNaN($root.subtotal) ? new Intl.NumberFormat().format(parseFloat($root.subtotal) || 0) : '0'">
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
                                    x-model.number="$root.discountPercentage"
                                    x-on:keyup="
                                        $root.discountPercentage = parseFloat($event.target.value) || 0;
                                        $root.discountPercentage = isNaN($root.discountPercentage) ? 0 : $root.discountPercentage;
                                        // إعادة حساب discount value تلقائياً
                                        const subtotal = parseFloat($root.subtotal) || 0;
                                        $root.discountValue = Math.round((subtotal * $root.discountPercentage) / 100 * 100) / 100;
                                        $root.discountValue = isNaN($root.discountValue) ? 0 : $root.discountValue;
                                        // تحديث Livewire
                                        $wire.set('discount_percentage', $root.discountPercentage);
                                        $wire.set('discount_value', $root.discountValue);
                                        $root.syncToLivewire();
                                    "
                                    wire:model.blur="discount_percentage"
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
                                :value="$root.discountValue"
                                x-on:keyup="
                                    const value = parseFloat($event.target.value) || 0;
                                    const safeValue = isNaN(value) ? 0 : value;
                                    $root.discountValue = safeValue;
                                    const subtotal = parseFloat($root.subtotal) || 0;
                                    if (!isNaN(subtotal) && subtotal > 0) {
                                        // إعادة حساب discount percentage تلقائياً
                                        $root.discountPercentage = (safeValue * 100) / subtotal;
                                        $root.discountPercentage = isNaN($root.discountPercentage) ? 0 : $root.discountPercentage;
                                        // تحديث Livewire
                                        $wire.set('discount_value', safeValue);
                                        $wire.set('discount_percentage', $root.discountPercentage);
                                    }
                                    $root.syncToLivewire();
                                "
                                wire:model.blur="discount_value"
                                class="form-control form-control-sm"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                id="discount_value">
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
                                            x-model.number="$root.additionalPercentage"
                                            x-on:keyup="
                                                $root.additionalPercentage = parseFloat($event.target.value) || 0;
                                                $root.additionalPercentage = isNaN($root.additionalPercentage) ? 0 : $root.additionalPercentage;
                                                // إعادة حساب additional value تلقائياً
                                                const subtotal = parseFloat($root.subtotal) || 0;
                                                $root.additionalValue = Math.round((subtotal * $root.additionalPercentage) / 100 * 100) / 100;
                                                $root.additionalValue = isNaN($root.additionalValue) ? 0 : $root.additionalValue;
                                                // تحديث Livewire
                                                $wire.set('additional_percentage', $root.additionalPercentage);
                                                $wire.set('additional_value', $root.additionalValue);
                                                $root.syncToLivewire();
                                            "
                                            wire:model.blur="additional_percentage"
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
                                            :value="$root.additionalValue"
                                        x-on:keyup="
                                            const value = parseFloat($event.target.value) || 0;
                                            const safeValue = isNaN(value) ? 0 : value;
                                            $root.additionalValue = safeValue;
                                            const subtotal = parseFloat($root.subtotal) || 0;
                                            const discountValue = parseFloat($root.discountValue) || 0;
                                            const afterDiscount = subtotal - discountValue;
                                            if (!isNaN(afterDiscount) && afterDiscount > 0) {
                                                // إعادة حساب additional percentage تلقائياً
                                                $root.additionalPercentage = (safeValue * 100) / afterDiscount;
                                                $root.additionalPercentage = isNaN($root.additionalPercentage) ? 0 : $root.additionalPercentage;
                                                // تحديث Livewire
                                                $wire.set('additional_value', safeValue);
                                                $wire.set('additional_percentage', $root.additionalPercentage);
                                            }
                                            $root.syncToLivewire();
                                        "
                                            wire:model.blur="additional_value"
                                            class="form-control form-control-sm"
                                            style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                            id="additional_value">
                                </div>
                            </div>
                @endif
                <hr>
                {{-- الإجمالي النهائي --}}
                @if ($type != 21)
                    {{-- إضافة الإجمالي النهائي لا ينطبق على التحويلات --}}
                    <div class="row mb-2">
                        <div class="col-3 text-right font-weight-bold">{{ __('Final Total:') }}</div>
                        <div class="col-3 text-left">
                            <input type="number" step="0.01" 
                                :value="$root.totalAfterAdditional"
                                x-on:keyup="
                                    const value = parseFloat($event.target.value) || 0;
                                    const safeValue = isNaN(value) ? 0 : value;
                                    $root.totalAfterAdditional = safeValue;
                                    // إعادة حساب remaining تلقائياً
                                    const received = parseFloat($root.receivedFromClient) || 0;
                                    $root.remaining = Math.max(safeValue - received, 0);
                                    $root.remaining = isNaN($root.remaining) ? 0 : $root.remaining;
                                    // تحديث Livewire
                                    $wire.set('total_after_additional', safeValue);
                                    $root.syncToLivewire();
                                "
                                wire:model.blur="total_after_additional"
                                class="form-control form-control-sm font-weight-bold fs-5 main-num"
                                style="font-size: 1.1em; height: 2.5em; padding: 2px 6px; font-weight: bold;" min="0"
                                id="total_after_additional_input">
                        </div>
                    </div>
                @endif {{-- إضافة الإجمالي النهائي لا ينطبق على التحويلات --}}
                <div class="row mb-2">
                    @if ($type != 21)
                        {{-- إضافة المدفوع من العميل لا ينطبق على التحويلات --}}
                        <div class="col-3 text-right font-weight-bold">{{ __('Paid by Customer:') }}</div>
                        <div class="col-3 text-left">
                            <input type="number" step="0.01" 
                                :value="$root.receivedFromClient"
                                x-on:keyup="
                                    $root.receivedFromClient = parseFloat($event.target.value) || 0;
                                    $root.receivedFromClient = isNaN($root.receivedFromClient) ? 0 : $root.receivedFromClient;
                                    // إعادة حساب remaining تلقائياً
                                    const total = parseFloat($root.totalAfterAdditional) || 0;
                                    $root.remaining = Math.max(total - $root.receivedFromClient, 0);
                                    $root.remaining = isNaN($root.remaining) ? 0 : $root.remaining;
                                    // تحديث Livewire
                                    $wire.set('received_from_client', $root.receivedFromClient);
                                    $root.syncToLivewire();
                                "
                                wire:model.blur="received_from_client"
                                class="form-control form-control-sm font-weight-bold fs-5"
                                style="font-size: 1.1em; height: 2.5em; padding: 2px 6px; font-weight: bold;" min="0"
                                id="received_from_client_input">
                        </div>
                    @endif {{-- إضافة المدفوع من العميل لا ينطبق على التحويلات --}}
                    <div class="col-3 text-left">
                        @if (View::getSection('formAction') === 'edit')
                            <button type="submit" class="btn btn-lg btn-main" wire:loading.attr="disabled">
                                <i class="fas fa-save"></i> {{ __('Update Invoice') }}
                            </button>
                        @else
                            @canany(['create ' . $titles[$type], 'create invoices'])
                                <button type="submit" class="btn btn-lg btn-main" wire:loading.attr="disabled">
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
                        <div class="col-3 text-left">
                            <input type="number" step="0.01" 
                                :value="$root.remaining"
                                x-on:keyup="
                                    const value = parseFloat($event.target.value) || 0;
                                    const safeValue = isNaN(value) ? 0 : value;
                                    $root.remaining = safeValue;
                                    // إعادة حساب receivedFromClient تلقائياً
                                    const total = parseFloat($root.totalAfterAdditional) || 0;
                                    $root.receivedFromClient = Math.max(0, total - safeValue);
                                    $root.receivedFromClient = isNaN($root.receivedFromClient) ? 0 : $root.receivedFromClient;
                                    // تحديث Livewire
                                    $wire.set('received_from_client', $root.receivedFromClient);
                                    $root.syncToLivewire();
                                "
                                wire:model.blur="received_from_client"
                                class="form-control form-control-sm font-weight-bold text-danger"
                                style="font-size: 1.1em; height: 2.5em; padding: 2px 6px; font-weight: bold; color: #dc3545;" min="0"
                                id="remaining_input">
                        </div>
                    </div>
                @endif {{-- إضافة الباقي لا ينطبق على التحويلات --}}
            </div>
        </div>
    </div>
</div>
