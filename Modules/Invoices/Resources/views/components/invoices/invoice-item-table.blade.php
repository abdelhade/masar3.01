<div class="table-responsive invoice-scroll-container card border border-top-0 border-secondary border-3 h-100"
    style="overflow-y: auto; overflow-x: auto; border: 3px solid #dee2e6; position: relative; z-index: 1;">

    <style>
        /* Grid: sticky header + search row inside table (Excel-like) */
        .invoice-data-grid {
            border-collapse: separate !important;
            border-spacing: 0;
            width: 100%;
            border: none;
        }

        .invoice-data-grid th {
            padding: 3px 5px !important;
            margin: 0 !important;
            background-color: #d4dce6;
            border: 1px solid #b0b8c4;
            vertical-align: middle;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 1px 0 #b0b8c4;
        }

        /* صف البحث داخل الجدول — خلية اسم الصنف فقط (مثل Excel) */
        .invoice-data-grid .invoice-grid-search-row td {
            position: sticky;
            top: 30px;
            z-index: 9;
            background: #f5f6f8 !important;
            border: 1px solid #c8ccd4;
            padding: 0 !important;
            margin: 0 !important;
            height: 30px;
            min-height: 30px;
            vertical-align: middle;
            box-shadow: 0 1px 0 #c8ccd4;
        }
        .invoice-data-grid .invoice-grid-search-row td .invoice-cell-search-wrap {
            height: 100%;
            padding: 0;
            margin: 0;
            display: block;
        }
        .invoice-data-grid .invoice-grid-search-row .form-control,
        .invoice-data-grid .invoice-grid-search-row #search-input,
        .invoice-data-grid .invoice-grid-search-row input[type="text"] {
            width: 100% !important;
            height: 100% !important;
            min-height: 30px !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 2px 5px !important;
            margin: 0 !important;
            background: #fff !important;
            font-size: 0.875rem;
            box-shadow: inset 0 0 0 1px #c0c0c0;
        }
        .invoice-data-grid .invoice-grid-search-row .form-control:focus,
        .invoice-data-grid .invoice-grid-search-row #search-input:focus {
            box-shadow: inset 0 0 0 2px #2563eb !important;
            outline: none;
        }


        .invoice-data-grid td {
            padding: 0 !important;
            margin: 0 !important;
            border: 1px solid #c0c0c0;
            vertical-align: middle;
            height: 30px;
        }

        .invoice-data-grid tbody tr:nth-of-type(odd) { background-color: #fff; }
        .invoice-data-grid tbody tr:nth-of-type(even) { background-color: #f5f6f8; }

        /* خلايا شبيهة بـ Excel: حدود رفيعة، padding و margin أقل */
        .invoice-data-grid .form-control,
        .invoice-data-grid .form-select,
        .invoice-data-grid input,
        .invoice-data-grid select {
            width: 100% !important;
            height: 100% !important;
            min-height: 30px !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            padding: 2px 4px !important;
            margin: 0 !important;
            background: transparent !important;
            font-size: 0.875rem;
            box-shadow: inset 0 0 0 1px transparent;
        }
        .invoice-data-grid .form-control:focus,
        .invoice-data-grid input:focus,
        .invoice-data-grid select:focus {
            background: #e8f4ff !important;
            outline: none !important;
            box-shadow: inset 0 0 0 1px #2563eb !important;
            position: relative;
            z-index: 1;
        }

        .invoice-data-grid input[type=number]::-webkit-inner-spin-button,
        .invoice-data-grid input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .invoice-data-grid input[readonly],
        .invoice-data-grid input:disabled {
            background: #f0f1f3 !important;
            cursor: not-allowed;
            color: #6b7280;
        }

        .invoice-data-grid input.text-center { text-align: center; }

        .invoice-data-grid td.action-cell {
            padding: 1px !important;
            margin: 0 !important;
            text-align: center;
        }

        .invoice-data-grid .static-text {
            display: flex;
            align-items: center;
            padding: 0 4px;
            margin: 0;
            height: 100%;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <table class="table invoice-data-grid mb-0" style="min-width: 1200px;">
        <thead class="table-light text-center align-middle">
        <tr>
                        <th class="font-bold fw-bold font-14 text-center" style="width: 30px;">#</th>
                @foreach ($this->currentTemplate->getOrderedColumns() as $columnKey)
                    @if ($this->shouldShowColumn($columnKey))
                        @php
                            $width = $this->currentTemplate->getColumnWidth($columnKey);
                            $columnNames = [
                                'item_name' => __('Item Name'),
                                'code' => __('Code'),
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
                        <th class="font-bold fw-bold font-14 text-center" style="width: {{ $width }}%">
                            {{ __($columnNames[$columnKey] ?? $columnKey) }}
                        </th>
                    @endif
                @endforeach
                <th class="font-bold fw-bold font-14 text-center">{{ __('Action') }}</th>
            </tr>
            {{-- صف البحث: تحت صف العناوين --}}
            <tr class="invoice-grid-search-row">
                <td class="form-control text-center" style="width: 30px; background: #f5f6f8; font-weight: bold; color: #2563eb; vertical-align: middle;">+</td>
                @foreach ($this->currentTemplate->getOrderedColumns() as $columnKey)
                    @if ($this->shouldShowColumn($columnKey))
                        @php $width = $this->currentTemplate->getColumnWidth($columnKey); @endphp
                        @if ($columnKey === 'item_name')
                            <td style="width: {{ $width }}%;">
                                @stack('invoice_table_search_row')
                            </td>
                        @else
                            <td style="width: {{ $width }}%;"></td>
                        @endif
                    @endif
                @endforeach
                <td class="action-cell"></td>
            </tr>
        </thead>

        <tbody>

            @forelse ($invoiceItems as $index => $row)
                <tr wire:key="invoice-row-{{ $row['item_id'] }}" data-row-index="{{ $index }}"
                    wire:click="selectItemFromTable({{ $row['item_id'] ?? 0 }}, {{ $row['unit_id'] ?? 'null' }}, {{ $row['price'] ?? 0 }})"
                    style="cursor: pointer;" class="align-middle">

                    <td class="text-center" style="width: 30px; font-weight: bold; background: #f8f9fa;">
                        {{ $index + 1 }}
                    </td>

                    {{-- اسم الصنف --}}
                    @if ($this->shouldShowColumn('item_name'))
                        <td style="width: 18%;">
                            <div class="static-text" style="font-weight: 900; font-size: 1.1rem; color: #000;"
                                title="{{ $row['name'] ?? __('Not Specified') }}">
                                {{ $row['name'] ?? __('Not Specified') }}
                            </div>
                        </td>
                    @endif

                    {{-- كود الصنف --}}
                    @if ($this->shouldShowColumn('code'))
                        <td style="width: 10%;">
                            <div class="static-text" title="{{ $row['code'] ?? '-' }}">
                                {{ $row['code'] ?? '-' }}
                            </div>
                        </td>
                    @endif
                    
                    {{-- الوحدة --}}
                    @if ($this->shouldShowColumn('unit'))
                        <td style="width: 10%;">
                            @php
                                $availableUnits = $row['available_units'] ?? [];
                                if ($availableUnits instanceof \Illuminate\Support\Collection) {
                                    $availableUnits = $availableUnits->toArray();
                                }
                                $currentUnitId = $row['unit_id'] ?? null;
                                $lastUVal = 1;
                                foreach ($availableUnits as $u) {
                                    $uId = is_array($u) ? $u['id'] ?? null : $u->id ?? null;
                                    if ($uId == $currentUnitId) {
                                        $lastUVal = is_array($u) ? $u['u_val'] ?? 1 : $u->u_val ?? 1;
                                        break;
                                    }
                                }
                            @endphp
                            <select wire:model="invoiceItems.{{ $index }}.unit_id"
                                wire:key="unit-select-{{ $index }}-{{ $row['item_id'] ?? 'default' }}"
                                @change="window.updatePriceClientSide && window.updatePriceClientSide({{ $index }}, $el)"
                                @keydown.enter.prevent="window.handleEnterNavigation && window.handleEnterNavigation($event)"
                                @click.stop id="unit-{{ $index }}" data-field="unit"
                                data-row="{{ $index }}" data-last-u-val="{{ $lastUVal }}"
                                class="form-control invoice-field @error('invoiceItems.' . $index . '.unit_id') is-invalid @enderror">
                                @foreach ($availableUnits as $unit)
                                    @php
                                        $unitId = is_array($unit) ? $unit['id'] ?? '' : $unit->id ?? '';
                                        $unitUVal = is_array($unit) ? $unit['u_val'] ?? 1 : $unit->u_val ?? 1;
                                        $unitName = is_array($unit) ? $unit['name'] ?? '' : $unit->name ?? '';
                                    @endphp
                                    <option value="{{ $unitId }}" data-u-val="{{ $unitUVal }}">
                                        {{ $unitName }}</option>
                                @endforeach
                            </select>
                        </td>
                    @endif


                    {{-- الكمية --}}
                    @if ($this->shouldShowColumn('quantity'))
                        <td style="width: 10%;">
                            <input type="number" step="0.001" min="0" id="quantity-{{ $index }}"
                                wire:model.live="invoiceItems.{{ $index }}.quantity" data-field="quantity"
                                data-row="{{ $index }}" onclick="this.select()"
                                @keydown.enter.prevent="window.handleEnterNavigation && window.handleEnterNavigation($event)"
                                @click.stop placeholder="{{ __('Quantity') }}"
                                class="form-control invoice-quantity invoice-field text-center">
                        </td>
                    @endif


                    {{-- رقم الدفعة --}}
                    @if ($this->shouldShowColumn('batch_number'))
                        <td style="width: 12%;">
                            @php
                                $isIncomingInvoice = in_array($this->type, [11, 13, 20]);
                                $isOutgoingInvoice = in_array($this->type, [10, 12, 14, 16, 19, 22]);
                            @endphp

                            @if ($isIncomingInvoice)
                                <input type="text" id="batch_number-{{ $index }}"
                                    wire:model.blur="invoiceItems.{{ $index }}.batch_number" @click.stop
                                    class="form-control text-center invoice-field"
                                    placeholder="{{ __('Batch Number') }}" />
                            @elseif (
                                $isOutgoingInvoice &&
                                    $this->expiryDateMode === 'show_all' &&
                                    isset($row['show_batch_selector']) &&
                                    $row['show_batch_selector']
                            )
                                <select id="batch_number-{{ $index }}"
                                    wire:change="selectBatch({{ $index }}, $event.target.value)" @click.stop
                                    class="form-control invoice-field font-12">
                                    <option value="">{{ __('Select Batch...') }}</option>
                                    @foreach ($this->availableBatches[$row['item_id']] ?? [] as $batch)
                                        <option value="{{ $batch['batch_number'] }}"
                                            @if (($row['batch_number'] ?? '') == $batch['batch_number']) selected @endif>
                                            {{ $batch['display_text'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" value="{{ $row['batch_number'] ?? '' }}"
                                    class="form-control text-center" readonly
                                    placeholder="{{ __('Not available') }}" />
                            @endif
                        </td>
                    @endif


                    {{-- تاريخ الصلاحية --}}
                    @if ($this->shouldShowColumn('expiry_date'))
                        <td style="width: 12%; position: relative;">
                            @php
                                $isIncomingInvoice = in_array($this->type, [11, 13, 20]);
                            @endphp

                            @if ($isIncomingInvoice)
                                <input type="date" id="expiry_date-{{ $index }}"
                                    wire:model.live="invoiceItems.{{ $index }}.expiry_date" @click.stop
                                    class="form-control text-center invoice-field"
                                    value="{{ $row['expiry_date'] ?? '' }}" />
                            @else
                                <input type="text"
                                    value="{{ isset($row['expiry_date']) ? \Carbon\Carbon::parse($row['expiry_date'])->format('Y-m-d') : '' }}"
                                    class="form-control text-center" readonly
                                    placeholder="{{ __('Not available') }}" />
                            @endif

                            {{-- تنبيه الصلاحية --}}
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
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark"
                                            style="font-size: 0.6em; z-index: 2;"
                                            title="{{ __('Remaining') }} {{ $daysUntilExpiry }} {{ __('day(s)') }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                    @elseif($daysUntilExpiry < 0)
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            style="font-size: 0.6em; z-index: 2;" title="{{ __('Expired') }}">
                                            <i class="fas fa-times-circle"></i>
                                        </span>
                                    @endif
                                @endif
                            @endif
                        </td>
                    @endif


                    {{-- الطول --}}
                    @if ($this->shouldShowColumn('length'))
                        <td style="width: 10%;">
                            <input type="number" step="0.01" min="0" id="length-{{ $index }}"
                                wire:model.blur="invoiceItems.{{ $index }}.length" @click.stop
                                placeholder="{{ __('L') }}" class="form-control invoice-field text-center"
                                @if (!$enableDimensionsCalculation) disabled @endif>
                        </td>
                    @endif


                    {{-- العرض --}}
                    @if ($this->shouldShowColumn('width'))
                        <td style="width: 10%;">
                            <input type="number" step="0.01" min="0" id="width-{{ $index }}"
                                wire:model.blur="invoiceItems.{{ $index }}.width" @click.stop
                                placeholder="{{ __('W') }}" class="form-control invoice-field text-center"
                                @if (!$enableDimensionsCalculation) disabled @endif>
                        </td>
                    @endif


                    {{-- الارتفاع --}}
                    @if ($this->shouldShowColumn('height'))
                        <td style="width: 10%;">
                            <input type="number" step="0.01" min="0" id="height-{{ $index }}"
                                wire:model.blur="invoiceItems.{{ $index }}.height" @click.stop
                                placeholder="{{ __('H') }}" class="form-control invoice-field text-center"
                                @if (!$enableDimensionsCalculation) disabled @endif>
                        </td>
                    @endif


                    {{-- الكثافة --}}
                    @if ($this->shouldShowColumn('density'))
                        <td style="width: 10%;">
                            <input type="number" step="0.01" min="0.01" id="density-{{ $index }}"
                                wire:model.blur="invoiceItems.{{ $index }}.density" @click.stop
                                placeholder="{{ __('D') }}" value="{{ $row['density'] ?? 1 }}"
                                class="form-control invoice-field text-center"
                                @if (!$enableDimensionsCalculation) disabled @endif>
                        </td>
                    @endif


                    {{-- السعر --}}
                    @if ($this->shouldShowColumn('price'))
                        <td style="width: 15%;">
                            <input type="number" id="price-{{ $index }}"
                                wire:model.live="invoiceItems.{{ $index }}.price" data-field="price"
                                data-row="{{ $index }}" onclick="this.select()"
                                @keydown.enter.prevent="window.handleEnterNavigation && window.handleEnterNavigation($event)"
                                @click.stop class="form-control text-center invoice-price invoice-field"
                                step="0.01" @if ($this->type == 10 && !auth()->user()->can('allow_price_change')) readonly @endif />
                        </td>
                    @endif


                    {{-- الخصم --}}
                    @if ($this->shouldShowColumn('discount'))
                        @php
                            $fieldStates = app(
                                \Modules\Invoices\Services\Invoice\InvoiceFormStateManager::class,
                            )->getFieldStates();
                            $isDiscountItemEnabled = $fieldStates['discount']['item'] ?? false;
                            $hasDiscountPermission = auth()->user()->can('allow_discount_change');
                        @endphp
                        <td style="width: 15%;">
                            <input type="number" id="discount-{{ $index }}"
                                wire:model.live="invoiceItems.{{ $index }}.discount" data-field="discount"
                                data-row="{{ $index }}" onclick="this.select()"
                                @keydown.enter.prevent="window.handleEnterNavigation && window.handleEnterNavigation($event)"
                                @click.stop class="form-control text-center invoice-discount invoice-field"
                                step="0.01" @if (!$hasDiscountPermission || !$isDiscountItemEnabled) readonly @endif />
                        </td>
                    @endif


                    {{-- القيمة الفرعية --}}
                    @if ($this->shouldShowColumn('sub_value'))
                        <td style="width: 15%;">
                            <input type="number" step="0.01" min="0" id="sub_value-{{ $index }}"
                                wire:model.live="invoiceItems.{{ $index }}.sub_value" data-field="sub_value"
                                data-row="{{ $index }}" onclick="this.select()"
                                @keydown.enter.prevent="window.handleEnterNavigation && window.handleEnterNavigation($event)"
                                @click.stop placeholder="{{ __('Value') }}"
                                class="form-control invoice-field text-center" readonly>
                        </td>
                    @endif


                    {{-- زر الحذف --}}
                    <td class="action-cell" style="width: 50px;">
                        <button type="button" wire:click="removeRow({{ $index }})" @click.stop
                            class="btn btn-link text-danger p-0">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="20" class="p-3 text-center">
                        <div class="alert alert-info mb-0">
                            {{ __('No items have been added. Use the search above to add items.') }}
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
