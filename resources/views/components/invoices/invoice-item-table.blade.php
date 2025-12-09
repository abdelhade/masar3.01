<table class="table table-striped mb-0" style="min-width: 1200px;">
    <thead class="table-light text-center align-middle">
        <tr>
            @foreach ($this->currentTemplate->getOrderedColumns() as $columnKey)
                @if ($this->shouldShowColumn($columnKey))
                    @php
                        $width = $this->currentTemplate->getColumnWidth($columnKey);
                        $columnNames = [
                            'item_name' => __('Item Name'),
                            'unit' => __('Unit'),
                            'quantity' => __('Quantity'),
                            'batch_number' => __('Batch Number'),
                            'expiry_date' => __('Expiry Date'),
                            'length' => __('Length'),
                            'width' => __('Width'),
                            'height' => __('Height'),
                            'density' => __('Density'),
                            'price' => __('Price'),
                            'discount' => __('Discount'),
                            'sub_value' => __('Value'),
                        ];
                    @endphp
                    <th class="font-hold fw-bold font-14 text-center" style="width: {{ $width }}%;">
                        {{ __($columnNames[$columnKey] ?? $columnKey) }}
                    </th>
                @endif
            @endforeach
            <th class="font-hold fw-bold font-14 text-center" style="width: 5%;">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="20" style="padding:0; border:none;">
                <div style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
                    <table class="table mb-0" style="background: transparent;">
                        <tbody>
                            @forelse ($invoiceItems as $index => $row)
                                @php
                                    $allowEditValue = ($this->settings['allow_edit_invoice_value'] ?? '0') == '1';
                                @endphp
                                <tr wire:key="invoice-row-{{ $index }}"
                                    x-data="{
                                        itemIndex: {{ $index }},
                                        quantity: @js($row['quantity'] ?? 0),
                                        price: @js($row['price'] ?? 0),
                                        discount: @js($row['discount'] ?? 0),
                                        length: @js($row['length'] ?? null),
                                        width: @js($row['width'] ?? null),
                                        height: @js($row['height'] ?? null),
                                        density: @js($row['density'] ?? 1),
                                        allowEditValue: @js($allowEditValue),
                                        lastSubValue: @js($row['sub_value'] ?? 0),
                                        get subValue() {
                                            const qty = parseFloat(this.quantity) || 0;
                                            const prc = parseFloat(this.price) || 0;
                                            const disc = parseFloat(this.discount) || 0;
                                            
                                            // التحقق من NaN قبل الحساب
                                            if (isNaN(qty) || isNaN(prc) || isNaN(disc)) {
                                                const safeValue = 0;
                                                if (Math.abs(safeValue - this.lastSubValue) > 0.01) {
                                                    this.lastSubValue = safeValue;
                                                    $wire.set(`invoiceItems.${this.itemIndex}.sub_value`, safeValue);
                                                }
                                                return safeValue;
                                            }
                                            
                                            const calculated = Math.round((qty * prc - disc) * 100) / 100;
                                            const safeCalculated = isNaN(calculated) ? 0 : calculated;
                                            
                                            // تحديث القيمة في Livewire فقط إذا تغيرت القيمة بشكل ملحوظ
                                            // (الحساب التلقائي دائماً مسموح، حتى لو لم يكن التعديل اليدوي مسموحاً)
                                            if (Math.abs(safeCalculated - this.lastSubValue) > 0.01) {
                                                this.lastSubValue = safeCalculated;
                                                // تحديث مباشر - Livewire سيتحقق تلقائياً أن هذه قيمة محسوبة وليست تعديل يدوي
                                                $wire.set(`invoiceItems.${this.itemIndex}.sub_value`, safeCalculated);
                                            }
                                            
                                            return safeCalculated;
                                        }
                                    }"
                                    x-init="
                                        // تحديث القيمة الفرعية عند تغيير الكمية/السعر/الخصم
                                        $watch('quantity', () => { 
                                            subValue; 
                                            if (typeof $root !== 'undefined' && $root.syncToLivewire) {
                                                $root.syncToLivewire();
                                            }
                                        });
                                        $watch('price', () => { 
                                            subValue; 
                                            if (typeof $root !== 'undefined' && $root.syncToLivewire) {
                                                $root.syncToLivewire();
                                            }
                                        });
                                        $watch('discount', () => { 
                                            subValue; 
                                            if (typeof $root !== 'undefined' && $root.syncToLivewire) {
                                                $root.syncToLivewire();
                                            }
                                        });
                                    ">
                                    {{-- اسم الصنف --}}
                                    @if ($this->shouldShowColumn('item_name'))
                                        <td style="width: 18%; font-size: 1.2em;">
                                            <span class="form-control"
                                                wire:click="selectItemFromTable({{ $row['item_id'] }}, {{ $row['unit_id'] ?? '' }}, {{ $row['price'] ?? 0 }})"
                                                style="cursor: pointer; font-size: 0.85em; height: 2em; padding: 1px 4px; display: block;">
                                                {{ $row['name'] ?? __('Not Specified') }}
                                            </span>
                                        </td>
                                    @endif


                                    {{-- كود الصنف --}}
                                    @if ($this->shouldShowColumn('item_code'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <span class="form-control"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;">
                                                {{ optional($items->firstWhere('id', $row['item_id']))->code ?? '-' }}
                                            </span>
                                        </td>
                                    @endif


                                    {{-- الوحدة --}}
                                    @if ($this->shouldShowColumn('unit'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <select wire:model.live="invoiceItems.{{ $index }}.unit_id"
                                                wire:key="unit-select-{{ $index }}-{{ $row['item_id'] ?? 'default' }}"
                                                wire:change="updatePriceForUnit({{ $index }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control @error('invoiceItems.' . $index . '.unit_id') is-invalid @enderror">
                                                @if (isset($row['available_units']) && $row['available_units']->count() > 0)
                                                    @foreach ($row['available_units'] as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                    @endif


                                    {{-- الكمية --}}
                                    @if ($this->shouldShowColumn('quantity'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.001" min="0"
                                                x-model.number="quantity"
                                                x-on:keyup="
                                                    quantity = parseFloat($event.target.value) || 0;
                                                    quantity = isNaN(quantity) ? 0 : quantity;
                                                    // تحديث sub_value تلقائياً
                                                    const prc = parseFloat(price) || 0;
                                                    const disc = parseFloat(discount) || 0;
                                                    const subVal = Math.round((quantity * prc - disc) * 100) / 100;
                                                    const safeSubVal = isNaN(subVal) ? 0 : subVal;
                                                    // تحديث Livewire
                                                    $wire.set('invoiceItems.{{ $index }}.quantity', quantity);
                                                    $wire.set('invoiceItems.{{ $index }}.sub_value', safeSubVal);
                                                    // تحديث جميع الحسابات في footer
                                                    $root.syncToLivewire();
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.quantity"
                                                id="quantity_{{ $index }}" placeholder="{{ __('Quantity') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control">
                                        </td>
                                    @endif


                                    {{-- ✅ رقم الدفعة (محدّث) --}}
                                    @if ($this->shouldShowColumn('batch_number'))
                                        <td style="width: 12%; font-size: 1.2em;">
                                            @php
                                                // تحديد نوع الفاتورة
                                                $isIncomingInvoice = in_array($this->type, [11, 13, 20]);
                                                // 11 = مشتريات, 13 = مردود مشتريات, 20 = أمر إضافة

                                                $isOutgoingInvoice = in_array($this->type, [10, 12, 14, 16, 19, 22]);
                                                // 10 = مبيعات, 12 = مردود مبيعات, 14 = أمر بيع، إلخ
                                            @endphp


                                            @if ($isIncomingInvoice)
                                                {{-- 🟢 في فواتير الشراء: الحقل مفتوح للكتابة --}}
                                                <input type="text"
                                                    wire:model.blur="invoiceItems.{{ $index }}.batch_number"
                                                    class="form-control text-center"
                                                    placeholder="{{ __('Batch Number') }}"
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px;" />
                                            @elseif (
                                                $isOutgoingInvoice &&
                                                    $this->expiryDateMode === 'show_all' &&
                                                    isset($row['show_batch_selector']) &&
                                                    $row['show_batch_selector']
                                            )
                                                {{-- 🔵 في فواتير البيع + وضع "عرض الكل": قائمة منسدلة --}}
                                                <select
                                                    wire:change="selectBatch({{ $index }}, $event.target.value)"
                                                    class="form-control"
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px;">
                                                    <option value="">{{ __('Select Batch...') }}</option>
                                                    @foreach ($this->availableBatches[$row['item_id']] ?? [] as $batch)
                                                        <option value="{{ $batch['batch_number'] }}"
                                                            @if (($row['batch_number'] ?? '') == $batch['batch_number']) selected @endif>
                                                            {{ $batch['display_text'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{-- 🔴 في فواتير البيع (الأقرب أولاً / معطل): readonly --}}
                                                <input type="text" value="{{ $row['batch_number'] ?? '' }}"
                                                    class="form-control text-center"
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px; background-color: #f8f9fa; cursor: not-allowed;"
                                                    placeholder="{{ __('Not available') }}" />
                                            @endif
                                        </td>
                                    @endif


                                    {{-- ✅ تاريخ الصلاحية (محدّث ومُصلح) --}}
                                    @if ($this->shouldShowColumn('expiry_date'))
                                        <td style="width: 12%; font-size: 1.2em;">
                                            @php
                                                $isIncomingInvoice = in_array($this->type, [11, 13, 20]);
                                                $isOutgoingInvoice = in_array($this->type, [10, 12, 14, 16, 19, 22]);
                                            @endphp


                                            @if ($isIncomingInvoice)
                                                {{-- 🟢 في فواتير الشراء: حقل date مفتوح --}}
                                                <input type="date"
                                                    wire:model.live="invoiceItems.{{ $index }}.expiry_date"
                                                    class="form-control text-center"
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                    value="{{ $row['expiry_date'] ?? '' }}" />
                                            @else
                                                {{-- 🔴 في فواتير البيع: readonly --}}
                                                <input type="text"
                                                    value="{{ isset($row['expiry_date']) ? \Carbon\Carbon::parse($row['expiry_date'])->format('Y-m-d') : '' }}"
                                                    class="form-control text-center" readonly
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px; background-color: #f8f9fa; cursor: not-allowed;"
                                                    placeholder="{{ __('Not available') }}" />
                                            @endif


                                            {{-- تنبيه إذا كانت الصلاحية قريبة --}}
                                            @if (isset($row['expiry_date']))
                                                @php
                                                    try {
                                                        $expiryDate = \Carbon\Carbon::parse($row['expiry_date']);
                                                        $daysUntilExpiry = now()->diffInDays($expiryDate, false);
                                                    } catch (\Exception $e) {
                                                        $daysUntilExpiry = null;
                                                    }
                                                @endphp


                                                @if ($daysUntilExpiry !== null)
                                                    @if ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 30)
                                                        <small class="text-warning d-block" style="font-size: 0.75em;">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            {{ __('Remaining') }} {{ $daysUntilExpiry }}
                                                            {{ __('day(s)') }}
                                                        </small>
                                                    @elseif($daysUntilExpiry < 0)
                                                        <small class="text-danger d-block" style="font-size: 0.75em;">
                                                            <i class="fas fa-times-circle"></i>
                                                            {{ __('Expired') }}
                                                        </small>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    @endif




                                    {{-- الطول --}}
                                    @if ($this->shouldShowColumn('length'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                x-model.number="length"
                                                x-on:keyup="
                                                    length = parseFloat($event.target.value) || 0;
                                                    length = isNaN(length) ? 0 : length;
                                                    if (length > 0 && width > 0 && height > 0 && !isNaN(width) && !isNaN(height) && !isNaN(density)) {
                                                        let qty = length * width * height * density;
                                                        @if ($dimensionsUnit === 'cm')
                                                            qty = qty / 1000000;
                                                        @endif
                                                        qty = Math.round(qty * 1000) / 1000;
                                                        qty = isNaN(qty) ? 0 : qty;
                                                        quantity = qty;
                                                        $wire.set('invoiceItems.{{ $index }}.length', length);
                                                        $wire.set('invoiceItems.{{ $index }}.quantity', qty);
                                                        $root.syncToLivewire();
                                                    }
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.length"
                                                placeholder="{{ __('Length') }} ({{ $dimensionsUnit }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif


                                    {{-- العرض --}}
                                    @if ($this->shouldShowColumn('width'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                x-model.number="width"
                                                x-on:keyup="
                                                    width = parseFloat($event.target.value) || 0;
                                                    width = isNaN(width) ? 0 : width;
                                                    if (length > 0 && width > 0 && height > 0 && !isNaN(length) && !isNaN(height) && !isNaN(density)) {
                                                        let qty = length * width * height * density;
                                                        @if ($dimensionsUnit === 'cm')
                                                            qty = qty / 1000000;
                                                        @endif
                                                        qty = Math.round(qty * 1000) / 1000;
                                                        qty = isNaN(qty) ? 0 : qty;
                                                        quantity = qty;
                                                        $wire.set('invoiceItems.{{ $index }}.width', width);
                                                        $wire.set('invoiceItems.{{ $index }}.quantity', qty);
                                                        $root.syncToLivewire();
                                                    }
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.width"
                                                placeholder="{{ __('Width') }} ({{ $dimensionsUnit }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif


                                    {{-- الارتفاع --}}
                                    @if ($this->shouldShowColumn('height'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                x-model.number="height"
                                                x-on:keyup="
                                                    height = parseFloat($event.target.value) || 0;
                                                    height = isNaN(height) ? 0 : height;
                                                    if (length > 0 && width > 0 && height > 0 && !isNaN(length) && !isNaN(width) && !isNaN(density)) {
                                                        let qty = length * width * height * density;
                                                        @if ($dimensionsUnit === 'cm')
                                                            qty = qty / 1000000;
                                                        @endif
                                                        qty = Math.round(qty * 1000) / 1000;
                                                        qty = isNaN(qty) ? 0 : qty;
                                                        quantity = qty;
                                                        $wire.set('invoiceItems.{{ $index }}.height', height);
                                                        $wire.set('invoiceItems.{{ $index }}.quantity', qty);
                                                        $root.syncToLivewire();
                                                    }
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.height"
                                                placeholder="{{ __('Height') }} ({{ $dimensionsUnit }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif


                                    {{-- الكثافة --}}
                                    @if ($this->shouldShowColumn('density'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0.01"
                                                x-model.number="density"
                                                x-on:keyup="
                                                    density = parseFloat($event.target.value) || 1;
                                                    density = isNaN(density) ? 1 : density;
                                                    if (length > 0 && width > 0 && height > 0 && !isNaN(length) && !isNaN(width) && !isNaN(height)) {
                                                        let qty = length * width * height * density;
                                                        @if ($dimensionsUnit === 'cm')
                                                            qty = qty / 1000000;
                                                        @endif
                                                        qty = Math.round(qty * 1000) / 1000;
                                                        qty = isNaN(qty) ? 0 : qty;
                                                        quantity = qty;
                                                        $wire.set('invoiceItems.{{ $index }}.density', density);
                                                        $wire.set('invoiceItems.{{ $index }}.quantity', qty);
                                                        $root.syncToLivewire();
                                                    }
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.density"
                                                placeholder="{{ __('Density') }}" value="{{ $row['density'] ?? 1 }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control"
                                                @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif


                                    {{-- السعر --}}
                                    @if ($this->shouldShowColumn('price'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number"
                                                x-model.number="price"
                                                x-on:keyup="
                                                    price = parseFloat($event.target.value) || 0;
                                                    price = isNaN(price) ? 0 : price;
                                                    // تحديث sub_value تلقائياً
                                                    const qty = parseFloat(quantity) || 0;
                                                    const disc = parseFloat(discount) || 0;
                                                    const subVal = Math.round((qty * price - disc) * 100) / 100;
                                                    const safeSubVal = isNaN(subVal) ? 0 : subVal;
                                                    // تحديث Livewire
                                                    $wire.set('invoiceItems.{{ $index }}.price', price);
                                                    $wire.set('invoiceItems.{{ $index }}.sub_value', safeSubVal);
                                                    // تحديث جميع الحسابات في footer
                                                    $root.syncToLivewire();
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.price"
                                                class="form-control text-center" step="1"
                                                @if (!auth()->user()->can('allow_price_change')) readonly @endif />

                                        </td>
                                    @endif


                                    {{-- الخصم --}}
                                    @if ($this->shouldShowColumn('discount'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number"
                                                x-model.number="discount"
                                                x-on:keyup="
                                                    discount = parseFloat($event.target.value) || 0;
                                                    discount = isNaN(discount) ? 0 : discount;
                                                    // تحديث sub_value تلقائياً
                                                    const qty = parseFloat(quantity) || 0;
                                                    const prc = parseFloat(price) || 0;
                                                    const subVal = Math.round((qty * prc - discount) * 100) / 100;
                                                    const safeSubVal = isNaN(subVal) ? 0 : subVal;
                                                    // تحديث Livewire
                                                    $wire.set('invoiceItems.{{ $index }}.discount', discount);
                                                    $wire.set('invoiceItems.{{ $index }}.sub_value', safeSubVal);
                                                    // تحديث جميع الحسابات في footer
                                                    $root.syncToLivewire();
                                                "
                                                wire:model.blur="invoiceItems.{{ $index }}.discount"
                                                class="form-control text-center" step="0.01"
                                                @if (!auth()->user()->can('allow_discount_change')) readonly @endif />
                                        </td>
                                    @endif


                                    {{-- القيمة الفرعية --}}
                                    @if ($this->shouldShowColumn('sub_value'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            @php
                                                $allowEditValue = ($this->settings['allow_edit_invoice_value'] ?? '0') == '1';
                                            @endphp
                                            @if ($allowEditValue)
                                                {{-- ✅ مسموح بالتعديل: حقل قابل للتعديل --}}
                                                <input type="number" step="0.01" min="0"
                                                    :value="subValue"
                                                    x-on:keyup="
                                                        const value = parseFloat($event.target.value) || 0;
                                                        const safeValue = isNaN(value) ? 0 : value;
                                                        $wire.set('invoiceItems.{{ $index }}.sub_value', safeValue);
                                                        $root.syncToLivewire();
                                                    "
                                                    wire:model.blur="invoiceItems.{{ $index }}.sub_value"
                                                    id="sub_value_{{ $index }}" placeholder="{{ __('Value') }}"
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                    class="form-control">
                                            @else
                                                {{-- ❌ غير مسموح بالتعديل: حقل للقراءة فقط --}}
                                                <input type="number" step="0.01" min="0"
                                                    :value="subValue"
                                                    readonly
                                                    id="sub_value_{{ $index }}" 
                                                    placeholder="{{ __('Value') }}"
                                                    style="font-size: 0.85em; height: 2em; padding: 1px 4px; background-color: #f8f9fa; cursor: not-allowed;"
                                                    class="form-control"
                                                    title="{{ __('غير مسموح بتعديل قيمة الفاتورة مباشرة.') }}">
                                            @endif
                                        </td>
                                    @endif


                                    {{-- زر الحذف --}}
                                    <td class="text-center" style="width: 10%; font-size: 1.2em;">
                                        <button type="button" wire:click="removeRow({{ $index }})"
                                            class="btn btn-danger btn-icon-square-sm"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20">
                                        <div class="alert alert-info text-center mb-0">
                                            {{ __('No items have been added. Use the search above to add items.') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>
