@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل الرصيد الافتتاحي للأصناف'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('تعديل الرصيد الافتتاحي للأصناف')],
        ],
    ])
    <div class="content-wrapper">
        <section class="content">
            <form action="{{ route('inventory-balance.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-2 text-center">
                        <label class="form-label" style="font-size: 1em;">المخزن</label>
                        <select id="store_select" name="store_id"
                            class="form-control form-control-sm @error('store_id') is-invalid @enderror"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                            @foreach ($stors as $store)
                                <option value="{{ $store->id }}">{{ $store->aname }}</option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-lg-2 text-center">
                        <label class="form-label" style="font-size: 1em;">الشريك</label>
                        <select id="partner_select" name="partner_id"
                            class="form-control form-control-sm @error('partner_id') is-invalid @enderror"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                            @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->aname }}</option>
                            @endforeach
                        </select>
                        @error('partner_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-lg-2 text-center">
                        <label class="form-label" style="font-size: 1em;">أجمالي الكميات المضافه</label>
                        <input id="total_quantities" class="form-control form-control-sm" type="text" value="0"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px; background-color: #f8f9fa;" readonly>
                    </div>

                    <div class="col-lg-2 text-center">
                        <label class="form-label" style="font-size: 1em;">قيمة الكميات المضافه</label>
                        <input id="total_value" class="form-control form-control-sm" type="text" value="0.00"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px; background-color: #f8f9fa;" readonly>
                    </div>

                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th style="width: 10%">الكود</th>
                                    <th style="width: 20%">الاسم</th>
                                    <th style="width: 15%">الوحدة</th>
                                    <th style="width: 15%">التكلفة</th>
                                    <th style="width: 15%">رصيد اول المده الحالي</th>
                                    <th style="width: 15%">رصيد اول المده الجديد</th>
                                    <th style="width: 15%">كميه التسويه</th>
                                </tr>
                            </thead>
                            <tbody id="items_table_body">
                                @foreach ($itemList as $item)
                                    <tr data-item-id="{{ $item->id }}">
                                        <td>
                                            <input type="text" value="{{ $item->code }}"
                                                class="form-control form-control-sm" readonly
                                                style="padding:2px;height:30px;">
                                        </td>
                                        <td>
                                            <input type="text" value="{{ $item->name }}"
                                                class="form-control form-control-sm" readonly
                                                style="padding:2px;height:30px;">
                                        </td>
                                        <td>
                                            <select name="unit_ids[{{ $item->id }}]"
                                                class="form-control form-control-sm unit-select"
                                                style="padding:2px;height:30px;" data-item-id="{{ $item->id }}">
                                                @foreach ($item->units as $unit)
                                                    <option value="{{ $unit->id }}"
                                                        data-cost="{{ $unit->pivot->cost ?? 0 }}">
                                                        {{ $unit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" value="{{ $item->units->first()?->pivot->cost ?? 0 }}"
                                                class="form-control form-control-sm cost-input"
                                                style="padding:2px;height:30px;" data-item-id="{{ $item->id }}"
                                                readonly>
                                        </td>

                                        <td>
                                            <input type="text" value="{{ $item->opening_balance ?? 0 }}"
                                                class="form-control form-control-sm current-balance"
                                                style="padding:2px;height:30px;" readonly>
                                        </td>

                                        <td>
                                            <input type="number" name="new_opening_balance[{{ $item->id }}]"
                                                class="form-control form-control-sm new-balance-input"
                                                placeholder="الرصيد الجديد" style="padding:2px;height:30px;"
                                                data-item-id="{{ $item->id }}" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" name="adjustment_qty[{{ $item->id }}]"
                                                class="form-control form-control-sm adjustment-qty"
                                                placeholder="كمية التسوية" style="padding:2px;height:30px;" readonly
                                                step="0.01">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $itemList->links() }}
                <div class="row mt-3">
                    <div class="col-12 text-left">
                        <button type="submit" class="btn btn-primary" id="save-btn">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                    </div>
                </div>

            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = Array.from(document.querySelectorAll('.new-balance-input'));
            if (!inputs.length) return;
            inputs[0].focus();

            // التنقل بين الحقول بالـ Enter
            inputs.forEach((input, idx) => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const next = inputs[idx + 1];
                        if (next) {
                            next.focus();
                        } else {
                            document.getElementById('save-btn').focus();
                        }
                    }
                });
            });

            // حساب كمية التسوية عند تغيير الرصيد الجديد
            document.querySelectorAll('.new-balance-input').forEach(input => {
                input.addEventListener('input', function() {
                    calculateAdjustmentQty(this);
                    updateTotals(); // إضافة حساب الإجماليات
                });
            });

            // تحديث التكلفة عند تغيير الوحدة
            document.querySelectorAll('.unit-select').forEach(select => {
                select.addEventListener('change', function() {
                    updateCost(this);
                    updateTotals(); // إعادة حساب الإجماليات عند تغيير الوحدة
                });
            });

            // حساب الإجماليات عند تحميل الصفحة
            updateTotals();
        });

        document.getElementById('store_select').addEventListener('change', function() {
            refreshItemsData();
        });

        function calculateAdjustmentQty(input) {
            const row = input.closest('tr');
            const currentBalance = parseFloat(row.querySelector('.current-balance').value) || 0;
            const newBalance = parseFloat(input.value) || 0;
            const adjustmentQty = newBalance - currentBalance;

            row.querySelector('.adjustment-qty').value = adjustmentQty.toFixed(2);
        }

        function updateCost(select) {
            const selectedOption = select.options[select.selectedIndex];
            const cost = selectedOption.getAttribute('data-cost') || 0;
            const row = select.closest('tr');

            row.querySelector('.cost-input').value = cost;
        }

        function updateTotals() {
            let totalQuantity = 0;
            let totalValue = 0;

            // حساب الإجماليات من جميع الصفوف
            document.querySelectorAll('#items_table_body tr').forEach(row => {
                const newBalanceInput = row.querySelector('.new-balance-input');
                const costInput = row.querySelector('.cost-input');

                const newBalance = parseFloat(newBalanceInput.value) || 0;
                const cost = parseFloat(costInput.value) || 0;

                // فقط الأصناف التي لها رصيد جديد أكبر من صفر
                if (newBalance > 0) {
                    totalQuantity += newBalance;
                    totalValue += (newBalance * cost);
                }
            });

            // تحديث حقول الإجماليات
            document.getElementById('total_quantities').value = totalQuantity.toFixed(2);
            document.getElementById('total_value').value = totalValue.toFixed(2);
        }

        function refreshItemsData() {
            const storeId = $('#store_select').val();
            $.ajax({
                url: "{{ route('inventory-start-balance.update-opening-balance') }}",
                method: 'POST',
                data: {
                    store_id: storeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        updateItemsTable(response.itemList);
                        updateTotals(); // إعادة حساب الإجماليات بعد تحديث البيانات
                    }
                }
            });
        }

        function updateItemsTable(itemList) {
            const tableBody = document.getElementById('items_table_body');
            // تحديث الرصيد الحالي لكل صنف
            itemList.forEach(item => {
                const row = tableBody.querySelector(`tr[data-item-id="${item.id}"]`);
                if (row) {
                    const currentBalanceInput = row.querySelector('.current-balance');
                    currentBalanceInput.value = item.opening_balance || 0;

                    // إعادة حساب كمية التسوية إذا كان هناك رصيد جديد
                    const newBalanceInput = row.querySelector('.new-balance-input');
                    if (newBalanceInput.value) {
                        calculateAdjustmentQty(newBalanceInput);
                    }
                }
            });
        }
    </script>
@endpush
