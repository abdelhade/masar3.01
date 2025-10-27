<table class="table table-striped mb-0" style="min-width: 1200px;">
    <thead class="table-light text-center align-middle">
        <tr>
            {{-- عرض الأعمدة بناءً على النموذج المختار --}}
            @if ($this->shouldShowColumn('item_name'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الصنف') }}</th>
            @endif

            @if ($this->shouldShowColumn('item_code'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الكود') }}</th>
            @endif

            @if ($this->shouldShowColumn('barcode'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الباركود') }}</th>
            @endif

            @if ($this->shouldShowColumn('unit'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الوحدة') }}</th>
            @endif

            @if ($this->shouldShowColumn('quantity'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الكمية') }}</th>
            @endif

            @if ($this->shouldShowColumn('price'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('السعر') }}</th>
            @endif

            @if ($this->shouldShowColumn('discount'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الخصم') }}</th>
            @endif

            @if ($this->shouldShowColumn('sub_value'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('القيمة') }}</th>
            @endif

            @if ($this->shouldShowColumn('notes'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('ملاحظات') }}</th>
            @endif

            @if ($this->shouldShowColumn('expiry_date'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('تاريخ الانتهاء') }}</th>
            @endif

            @if ($this->shouldShowColumn('batch_number'))
                <th class="font-family-cairo fw-bold font-14 text-center">{{ __('رقم الدفعة') }}</th>
            @endif

            <th class="font-family-cairo fw-bold font-14 text-center">{{ __('إجراء') }}</th>
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
                                                {{ $items->firstWhere('id', $row['item_id'])->code ?? '-' }}
                                            </span>
                                        </td>
                                    @endif

                                    {{-- الباركود --}}
                                    @if ($this->shouldShowColumn('barcode'))
                                        <td style="width: 12%; font-size: 1.2em;">
                                            <span class="form-control"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;">
                                                {{ $items->firstWhere('id', $row['item_id'])->barcodes->first()->barcode ?? '-' }}
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
                                            <input type="number" min="1"
                                                wire:model.blur="invoiceItems.{{ $index }}.quantity"
                                                id="quantity_{{ $index }}" placeholder="{{ __('الكمية') }}"
                                                style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                                class="form-control">
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

                                    {{-- ملاحظات --}}
                                    @if ($this->shouldShowColumn('notes'))
                                        <td style="width: 20%; font-size: 1.2em;">
                                            <input type="text"
                                                wire:model.blur="invoiceItems.{{ $index }}.notes"
                                                placeholder="{{ __('ملاحظات') }}"
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
</table>`
