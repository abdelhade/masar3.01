<table class="table table-striped mb-0" style="min-width: 1200px;">

    <thead class="table-light text-center align-middle">
        <tr>
            @foreach ($this->currentTemplate->getOrderedColumns() as $columnKey)
                @if ($this->shouldShowColumn($columnKey))
                    @php
                        $width = $this->currentTemplate->getColumnWidth($columnKey);
                        $columnNames = [
                            'item_name' => 'الصنف',
                            'unit' => 'الوحدة',
                            'quantity' => 'الكمية',
                            'length' => 'الطول',
                            'width' => 'العرض',
                            'height' => 'الارتفاع',
                            'density' => 'الكثافة',
                            'price' => 'السعر',
                            'discount' => 'الخصم',
                            'sub_value' => 'القيمة',
                        ];
                    @endphp
                    <th class="font-family-cairo fw-bold font-14 text-center" style="width: {{ $width }}%;">
                        {{ $columnNames[$columnKey] ?? $columnKey }}
                    </th>
                @endif
            @endforeach
            <th class="font-family-cairo fw-bold font-14 text-center" style="width: 5%;">إجراء</th>
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
                                                wire:click="selectItemFromTable({{ $row['item_id'] }}, {{ $row['unit_id'] ?? '' }}, {{ $row['price'] ?? 0 }})"
                                                style="cursor: pointer; font-size: 0.85em; height: 2em; padding: 1px 4px; display: block;">
                                                {{ $row['name'] ?? 'غير محدد' }}
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
                                                wire:model.blur="invoiceItems.{{ $index }}.quantity"
                                                id="quantity_{{ $index }}" placeholder="{{ __('الكمية') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) readonly @endif>
                                        </td>
                                    @endif

                                    {{-- الطول --}}
                                    @if ($this->shouldShowColumn('length'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                wire:model.blur="invoiceItems.{{ $index }}.length"
                                                placeholder="{{ __('الطول') }} ({{ $dimensionsUnit }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif

                                    {{-- العرض --}}
                                    @if ($this->shouldShowColumn('width'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                wire:model.blur="invoiceItems.{{ $index }}.width"
                                                placeholder="{{ __('العرض') }} ({{ $dimensionsUnit }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif

                                    {{-- الارتفاع --}}
                                    @if ($this->shouldShowColumn('height'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                wire:model.blur="invoiceItems.{{ $index }}.height"
                                                placeholder="{{ __('الارتفاع') }} ({{ $dimensionsUnit }})"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif

                                    {{-- الكثافة --}}
                                    @if ($this->shouldShowColumn('density'))
                                        <td style="width: 10%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0.01"
                                                wire:model.blur="invoiceItems.{{ $index }}.density"
                                                placeholder="{{ __('الكثافة') }}" value="{{ $row['density'] ?? 1 }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control" @if (!$enableDimensionsCalculation) disabled @endif>
                                        </td>
                                    @endif

                                    {{-- السعر --}}
                                    @if ($this->shouldShowColumn('price'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                wire:model.blur="invoiceItems.{{ $index }}.price"
                                                id="price_{{ $index }}" placeholder="{{ __('السعر') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control">
                                        </td>
                                    @endif

                                    {{-- الخصم --}}
                                    @if ($this->shouldShowColumn('discount'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                wire:model.blur="invoiceItems.{{ $index }}.discount"
                                                id="discount_{{ $index }}" placeholder="{{ __('الخصم') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control">
                                        </td>
                                    @endif

                                    {{-- القيمة الفرعية --}}
                                    @if ($this->shouldShowColumn('sub_value'))
                                        <td style="width: 15%; font-size: 1.2em;">
                                            <input type="number" step="0.01" min="0"
                                                wire:model.blur="invoiceItems.{{ $index }}.sub_value"
                                                id="sub_value_{{ $index }}" placeholder="{{ __('القيمة') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control">
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
                                            لا توجد أصناف مضافة. استخدم البحث أعلاه لإضافة أصناف.
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
