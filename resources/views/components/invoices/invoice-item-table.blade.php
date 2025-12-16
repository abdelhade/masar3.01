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
                                <tr wire:key="invoice-row-{{ $index }}">
                                    {{-- اسم الصنف --}}
                                    @if ($this->shouldShowColumn('item_name'))
                                        <td style="width: 18%; font-size: 1.2em;">
                                            <span class="form-control"
                                                wire:click="selectItemFromTable({{ $row['item_id'] ?? 0 }}, {{ $row['unit_id'] ?? 'null' }}, {{ $row['price'] ?? 0 }})"
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
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <select wire:model.live="invoiceItems.{{ $index }}.unit_id"
                                                wire:key="unit-select-{{ $index }}-{{ $row['item_id'] ?? 'default' }}"
                                                wire:change="updatePriceForUnit({{ $index }})"
                                                @keydown.enter.prevent="moveToNextFieldInRow($event)"
                                                id="unit-{{ $index }}"
                                                data-field="unit" data-row="{{ $index }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control invoice-field @error('invoiceItems.' . $index . '.unit_id') is-invalid @enderror">
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
                                                id="quantity-{{ $index }}" value="{{ $row['quantity'] ?? 0 }}"
                                                data-field="quantity" data-row="{{ $index }}"
                                                @input="
                $wire.set('invoiceItems.{{ $index }}.quantity', $event.target.value, false);
                calculateRowTotal({{ $index }});
            "
                                                @keyup="calculateRowTotal({{ $index }})"
                                                @keydown.enter.prevent="moveToNextFieldInRow($event)"
                                                placeholder="{{ __('Quantity') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control invoice-quantity invoice-field">
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
                                            <input type="number" id="price-{{ $index }}"
                                                value="{{ $row['price'] ?? 0 }}" data-field="price"
                                                data-row="{{ $index }}"
                                                @input="
                $wire.set('invoiceItems.{{ $index }}.price', $event.target.value, false);
                calculateRowTotal({{ $index }});
            "
                                                @keyup="calculateRowTotal({{ $index }})"
                                                @keydown.enter.prevent="moveToNextFieldInRow($event)"
                                                class="form-control text-center invoice-price invoice-field"
                                                step="1" @if ($this->type == 10 && !auth()->user()->can('allow_price_change')) readonly @endif />
                                        </td>
                                    @endif


                                    {{-- الخصم --}}
                                    @if ($this->shouldShowColumn('discount'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number" id="discount-{{ $index }}"
                                                value="{{ $row['discount'] ?? 0 }}" data-field="discount"
                                                data-row="{{ $index }}"
                                                @input="
                $wire.set('invoiceItems.{{ $index }}.discount', $event.target.value, false);
                calculateRowTotal({{ $index }});
            "
                                                @keyup="calculateRowTotal({{ $index }})"
                                                @keydown.enter.prevent="moveToNextFieldInRow($event)"
                                                class="form-control text-center invoice-discount invoice-field"
                                                step="0.01" @if (!auth()->user()->can('allow_discount_change')) readonly @endif />
                                        </td>
                                    @endif


                                    {{-- القيمة الفرعية --}}
                                    @if ($this->shouldShowColumn('sub_value'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                id="sub_value-{{ $index }}"
                                                value="{{ $row['sub_value'] ?? 0 }}" data-field="sub_value"
                                                data-row="{{ $index }}"
                                                @input.debounce.150ms="
                $wire.set('invoiceItems.{{ $index }}.sub_value', $event.target.value, false);
                $wire.call('calculateQuantityFromSubValue', {{ $index }});
            "
                                                @keydown.enter.prevent="moveToNextFieldInRow($event)"
                                                placeholder="{{ __('Value') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control invoice-field">
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
