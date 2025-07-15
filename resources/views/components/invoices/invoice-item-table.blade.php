<table class="table table-bordered">
    <thead>
        <tr>
            <th>الصنف</th>
            <th>الوحدة</th>
            <th>الكمية</th>
            <th>السعر</th>
            <th>الخصم</th>
            <th>القيمة</th>
            <th class="text-center">إجراء</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="7" style="padding:0; border:none;">
                <div style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
                    <table class="table mb-0" style="background: transparent;">
                        <tbody>
                            @forelse ($invoiceItems as $index => $row)
                                <tr wire:key="invoice-row-{{ $index }}">
                                    {{-- اختيار الصنف --}}
                                    <td style="width: 18%; font-size: 1.2em;">
                                        <span class="form-control"
                                            wire:click="selectItemFromTable({{ $row['item_id'] }}, {{ $row['unit_id'] ?? '' }}, {{ $row['price'] ?? 0 }})"
                                            style="cursor: pointer; font-size: 0.85em; height: 2em; padding: 1px 4px; display: block;">
                                            {{ $items->firstWhere('id', $row['item_id'])->name ?? 'غير محدد' }}
                                        </span>
                                    </td>

                                    {{-- اختيار الوحدة --}}
                                    <td style="width: 15%; font-size: 1.2em;">
                                        <select wire:model.live="invoiceItems.{{ $index }}.unit_id"
                                            wire:key="unit-select-{{ $index }}-{{ $row['item_id'] ?? 'default' }}"
                                            wire:change="updatePriceForUnit({{ $index }})"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                            class="form-control @error('invoiceItems.' . $index . '.unit_id') is-invalid @enderror">
                                            @if (isset($row['available_units']) && $row['available_units']->count() > 0)
                                                @foreach ($row['available_units'] as $unit)
                                                    <option value="{{ $unit->id }}">
                                                        {{ $unit->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('invoiceItems.' . $index . '.unit_id')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </td>

                                    {{-- حقل الكمية مع التنقل التلقائي --}}
                                    <td style="width: 10%; font-size: 1.2em;">
                                        <input type="number" step="0.01" min="0"
                                            wire:model.blur="invoiceItems.{{ $index }}.quantity"
                                            id="quantity_{{ $index }}" placeholder="الكمية"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                            onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('price_{{ $index }}')?.focus();document.getElementById('price_{{ $index }}')?.select();}"
                                            class="form-control @error('invoiceItems.' . $index . '.quantity') is-invalid @enderror">
                                        @error('invoiceItems.' . $index . '.quantity')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </td>

                                    {{-- حقل السعر مع التنقل التلقائي --}}
                                    <td style="width: 15%; font-size: 1.2em;">
                                        <input type="number" step="0.01" min="0"
                                            wire:model.live="invoiceItems.{{ $index }}.price"
                                            id="price_{{ $index }}" placeholder="السعر"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                            onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('discount_{{ $index }}')?.focus();document.getElementById('discount_{{ $index }}')?.select();}"
                                            class="form-control @error('invoiceItems.' . $index . '.price') is-invalid @enderror">
                                        @error('invoiceItems.' . $index . '.price')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </td>

                                    {{-- حقل الخصم مع التنقل للصف التالي أو البحث --}}
                                    <td style="width: 15%; font-size: 1.2em;">
                                        <input type="number" step="0.01" min="0"
                                            wire:model.blur="invoiceItems.{{ $index }}.discount"
                                            id="discount_{{ $index }}" placeholder="الخصم"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                            onkeydown="if(event.key==='Enter'){
                                                                event.preventDefault();
                                                                const subValueField = document.getElementById('sub_value_{{ $index }}');
                                                                if(subValueField) {
                                                                    subValueField.focus();
                                                                    subValueField.select();
                                                                }
                                                            }"
                                            class="form-control">
                                    </td>

                                    {{-- حقل القيمة الفرعية --}}
                                    <td style="width: 15%; font-size: 1.2em;">
                                        <input type="number" step="0.01" min="0"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                            wire:model.blur="invoiceItems.{{ $index }}.sub_value"
                                            id="sub_value_{{ $index }}" placeholder="القيمة"
                                            onkeydown="if(event.key==='Enter'){
                                                                event.preventDefault();
                                                                const nextQuantity = document.getElementById('quantity_{{ $index + 1 }}');
                                                                if(nextQuantity) {
                                                                    nextQuantity.focus();
                                                                    nextQuantity.select();
                                                                } else {
                                                                    const searchField = document.querySelector('input[wire\\:model\\.live=&quot;searchTerm&quot;]');
                                                                    if(searchField) searchField.focus();
                                                                }
                                                            }"
                                            class="form-control">
                                    </td>

                                    {{-- زرّ الحذف --}}
                                    <td class="text-center" style="width: 10%; font-size: 1.2em;">
                                        <button type="button" wire:click="removeRow({{ $index }})"
                                            style="font-size: 0.85em; height: 2em; padding: 1px 4px;"
                                            class="btn btn btn-danger"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا الصف؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13">
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
