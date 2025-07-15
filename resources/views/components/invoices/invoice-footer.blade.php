<div class="row mt-4">
    <div class="col-3">
        @if ($currentSelectedItem)
            <div class="card border-primary">
                <div class="card-header text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-box"></i> بيانات الصنف
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row gx-4">

                        <div class="col-md-6 border-end pe-3">

                            <div class="row mb-2">
                                <div class="col-5 fs-6">الاسم:</div>
                                <div class="col-7 fw-bold">
                                    <span class="badge bg-light text-dark">{{ $selectedItemData['name'] }}</span>
                                </div>
                            </div>

                            @if ($selectedItemData['code'])
                                <div class="row mb-2">
                                    <div class="col-5 fs-6">الكود:</div>
                                    <div class="col-7">
                                        <span class="badge bg-light text-dark">{{ $selectedItemData['code'] }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-2">
                                <div class="col-5 fs-6">المخزن:</div>
                                <div class="col-7">
                                    <span
                                        class="badge bg-light text-dark">{{ $selectedItemData['selected_store_name'] }}</span>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5 fs-6">المتاح بالمخزن:</div>
                                <div class="col-7">
                                    <span class="badge bg-light text-dark">
                                        {{ $selectedItemData['available_quantity_in_store'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 ps-3">
                            <div class="row mb-2">
                                <div class="col-6 fs-6">الإجمالي في المخازن:</div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark">
                                        {{ $selectedItemData['total_available_quantity'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6 fs-6">الوحدة:</div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark">{{ $selectedItemData['unit_name'] }}</span>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6 fs-6">السعر:</div>
                                <div class="col-6 text-primary fw-bold">
                                    <span class="badge bg-light text-dark">
                                        {{ number_format($selectedItemData['price']) }} ج.م
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6 fs-6">سعر الشراء الأخير:</div>
                                <div class="col-6 text-success">
                                    <span class="badge bg-light text-dark">
                                        {{ number_format($selectedItemData['cost']) }} ج.م
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
                    <p>ابحث عن صنف لعرض بياناته هنا</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-3">
        <div class="card border-primary">

            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="cash_box_id" style="font-size: 1em;">صندوق النقدية</label>
                    <select wire:model="cash_box_id" class="form-control form-control-sm"
                        style="font-size: 0.95em; height: 2em; padding: 2px 6px;">
                        {{-- <option value="">اختر صندوق النقدية</option> --}}
                        @foreach ($cashAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->aname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    @if ($type == 11)
                        <label for="received_from_client" style="font-size: 1em;">المبلغ المدفوع للمورد</label>
                    @else
                        <label for="received_from_client" style="font-size: 1em;">المبلغ المستلم من العميل</label>
                    @endif
                    <input type="number" step="0.01" wire:model="received_from_client" wire:change="calculateTotals"
                        class="form-control form-control-sm" style="font-size: 0.95em; height: 2em; padding: 2px 6px;"
                        min="0">
                </div>

                <div class="form-group mb-3">
                    <label for="notes" style="font-size: 1em;">ملاحظات</label>
                    <textarea wire:model="notes" class="form-control form-control-sm" rows="1" placeholder="ملاحظات إضافية..."
                        style="font-size: 0.95em; padding: 6px;"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card border-primary">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6 text-right font-weight-bold">الإجمالي الفرعي:</div>
                    <div class="col-6 text-left text-primary">
                        {{ number_format($subtotal) }} ج.م
                    </div>
                </div>

                {{-- الخصم --}}
                <div class="row mb-2 align-items-center">
                    <div class="col-3 text-right font-weight-bold">
                        <label style="font-size: 0.95em;">الخصم %</label>
                    </div>
                    <div class="col-3">
                        <div class="input-group">
                            <input type="number" step="0.01" wire:model="discount_percentage"
                                wire:change="calculateTotals" class="form-control form-control-sm"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0" max="100">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-3 text-right font-weight-bold">
                        <label for="discount_value" class="form-label" style="font-size: 0.95em;">قيمة
                            الخصم</label>
                    </div>

                    <div class="col-3">
                        <input type="number" step="0.01" wire:model="discount_value"
                            wire:change="calculateTotals" class="form-control form-control-sm"
                            style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                            id="discount_value">
                    </div>

                </div>

                {{-- الإضافي (مثال: ضريبة) --}}
                <div class="row mb-2 align-items-center">
                    <div class="col-3 text-right font-weight-bold">
                        <label style="font-size: 0.95em;">الاضافي %</label>
                    </div>

                    <div class="col-3">
                        <div class="input-group">
                            <input type="number" step="0.01" wire:model="additional_percentage"
                                wire:change="calculateTotals" class="form-control form-control-sm"
                                style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                                max="100">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-3 text-right font-weight-bold">
                        <label for="additional_value" class="form-label" style="font-size: 0.95em;">قيمة
                            الاضافي</label>
                    </div>

                    <div class="col-3">
                        <input type="number" step="0.01" wire:model="additional_value"
                            wire:change="calculateTotals" class="form-control form-control-sm"
                            style="font-size: 0.95em; height: 2em; padding: 2px 6px;" min="0"
                            id="additional_value">
                    </div>

                </div>

                <hr>

                {{-- الإجمالي النهائي --}}
                <div class="row mb-2">
                    <div class="col-6 text-right font-weight-bold">الإجمالي النهائي:</div>
                    <div class="col-6 text-left font-weight-bold fs-5">
                        {{ number_format($total_after_additional) }} ج.م
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-6 text-right font-weight-bold">المدفوع من العميل:</div>
                    <div class="col-6 text-left font-weight-bold fs-5">
                        {{ number_format($received_from_client) }} ج.م
                    </div>
                </div>

                {{-- الباقي على العميل --}}
                <div class="row">
                    <div class="col-6 text-right font-weight-bold">الباقي:</div>
                    <div class="col-6 text-left font-weight-bold text-danger">
                        @php
                            $remaining = $total_after_additional - $received_from_client;
                        @endphp
                        {{ number_format(max($remaining, 0)) }} ج.م
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
