<div>
    <div class="content-wrapper">
        <section class="content">
            <form wire:submit="updateForm">

                {{-- رأس الفاتورة --}}
                <div class="row">
                    <div class="card-header">
                        <h3 class="card-title fw-bold fs-2">
                            {{ $titles[$type] }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <input type="hidden" wire:model="type">

                            <div class="col-lg-2">
                                <label class="form-label" style="font-size: 1em;">الحساب المدين</label>
                                <select wire:model="acc1_id"
                                    class="form-control form-control-sm @error('acc1_id') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
                                    <option value="">{{ __('اختر الحساب') }}</option>
                                    @foreach ($acc1List as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label" style="font-size: 1em;">الحساب الدائن</label>
                                <select wire:model="acc2_id"
                                    class="form-control form-control-sm @error('acc2_id') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
                                    <option value="">{{ __('اختر الحساب') }}</option>
                                    @foreach ($acc2List as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label" style="font-size: 1em;">الموظف</label>
                                <select wire:model="emp_id"
                                    class="form-control form-control-sm @error('emp_id') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
                                    <option value="">{{ __('اختر الموظف') }}</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label" style="font-size: 1em;">التاريخ</label>
                                <input type="date" wire:model="pro_date"
                                    class="form-control form-control-sm @error('pro_date') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label" style="font-size: 1em;">تاريخ الاستحقاق</label>
                                <input type="date" wire:model="accural_date"
                                    class="form-control form-control-sm @error('accural_date') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
                            </div>

                            <div class="col-lg-1">
                                <label class="form-label" style="font-size: 1em;">رقم الفاتورة</label>
                                <input type="number" wire:model="pro_id"
                                    class="form-control form-control-sm @error('pro_id') is-invalid @enderror" readonly
                                    @if ($is_disabled) disabled @endif>
                            </div>

                            <div class="col-lg-1">
                                <label class="form-label" style="font-size: 1em;">S.N</label>
                                <input type="text" wire:model="serial_number"
                                    class="form-control form-control-sm @error('serial_number') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- البحث عن الأصناف --}}
                <div class="row">

                    {{-- تحسين حقل البحث --}}
                    <div class="col-lg-4 mb-3" style="position: relative;">
                        <label>ابحث عن صنف</label>
                        <input type="text" wire:model.live="searchTerm" class="form-control frst"
                            placeholder="ابدأ بكتابة اسم الصنف..." autocomplete="off"
                            wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                            wire:keydown.enter.prevent="handleEnter" @if ($is_disabled) disabled @endif>
                        @if (strlen($searchTerm) > 0 && $searchResults->count())
                            <ul class="list-group position-absolute w-100"
                                style="z-index: 999; max-height: 200px; overflow-y: auto;">
                                @foreach ($searchResults as $index => $item)
                                    <li class="list-group-item list-group-item-action
                         @if ($selectedResultIndex === $index) active @endif"
                                        wire:click="addItemFromSearch({{ $item->id }})" style="cursor: pointer;">
                                        <strong>{{ $item->name }}</strong>
                                        @if ($item->code)
                                            <br><small class="text-muted">كود: {{ $item->code }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @elseif(strlen($searchTerm) > 0)
                            <div class="mt-2" style="position: absolute; z-index: 1000; width: 100%;">
                                <div class="list-group-item text-danger">
                                    لا توجد نتائج لـ "{{ $searchTerm }}"
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-3">
                        <label>اختر نوع السعر للفاتورة</label>
                        <select wire:model="selectedPriceType"
                            class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror"
                            @if ($is_disabled) disabled @endif>
                            <option value="">{{ __('اختر نوع السعر') }}</option>
                            @foreach ($priceTypes as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- جدول الأصناف --}}
                <div class="row">


                    <table class="table table-striped mb-0" style="min-width: 1200px;">
                        <thead class="table-light text-center align-middle">

                            <tr>
                                <th class="font-family-cairo fw-bold font-14 text-center">الصنف</th>
                                <th class="font-family-cairo fw-bold font-14 text-center">الوحدة</th>
                                <th class="font-family-cairo fw-bold font-14 text-center">الكمية</th>
                                <th class="font-family-cairo fw-bold font-14 text-center">السعر</th>
                                <th class="font-family-cairo fw-bold font-14 text-center">الخصم</th>
                                <th class="font-family-cairo fw-bold font-14 text-center">القيمة</th>
                                <th class="font-family-cairo fw-bold font-14 text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" style="padding:0; border:none;">
                                    <div style="max-height: 320px; overflow-y: auto; overflow-x: hidden;">
                                        <table class="table mb-0" style="background: transparent;">
                                            <tbody>
                                                @forelse ($invoiceItems as $index => $row)
                                                    <tr wire:key="invoice-row-{{ $index }}">
                                                        <td class="text-center" style="width: 18%">
                                                            <select
                                                                wire:model.live="invoiceItems.{{ $index }}.item_id"
                                                                wire:change="updateUnits({{ $index }})"
                                                                class="form-control"
                                                                @if ($is_disabled) disabled @endif>
                                                                <option value="">{{ __('اختر الصنف') }}</option>
                                                                @foreach ($items as $item)
                                                                    <option value="{{ $item->id }}">
                                                                        {{ $item->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td class="text-center" style="width: 15%">
                                                            <select
                                                                wire:model.live="invoiceItems.{{ $index }}.unit_id"
                                                                wire:change="updatePriceForUnit({{ $index }})"
                                                                class="form-control"
                                                                @if ($is_disabled) disabled @endif>
                                                                <option value="">{{ __('اختر الوحدة') }}
                                                                </option>
                                                                @if (isset($row['available_units']))
                                                                    @foreach ($row['available_units'] as $unit)
                                                                        <option value="{{ $unit->id }}">
                                                                            {{ $unit->name }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </td>

                                                        <td class="text-center" style="width: 10%">
                                                            <input type="number" step="0.01" min="0"
                                                                wire:model.blur="invoiceItems.{{ $index }}.quantity"
                                                                id="quantity_{{ $index }}"
                                                                onkeydown="if(event.key==='Enter'){event.preventDefault()
                                            ;document.getElementById('price_{{ $index }}'
                                            )?.focus();document.getElementById('price_{{ $index }}')?.select();}"
                                                                placeholder="الكمية" class="form-control text-center"
                                                                @if ($is_disabled) disabled @endif>
                                                        </td>

                                                        <td style="width: 15%">
                                                            <input type="number" step="0.01" min="0"
                                                                wire:model.blur="invoiceItems.{{ $index }}.price"
                                                                id="price_{{ $index }}" placeholder="السعر"
                                                                onkeydown="if(event.key==='Enter'){event.preventDefault();document
                                            .getElementById('discount_{{ $index }}')?.focus();
                                            document.getElementById('discount_{{ $index }}')?.select();}"
                                                                class="form-control text-center"
                                                                @if ($is_disabled) disabled @endif>
                                                        </td>

                                                        <td class="text-center" style="width: 15%">
                                                            <input type="number" step="0.01" min="0"
                                                                wire:model.blur="invoiceItems.{{ $index }}.discount"
                                                                id="discount_{{ $index }}" placeholder="الخصم"
                                                                onkeydown="if(event.key==='Enter'){
                                                                event.preventDefault();
                                                                const subValueField = document.getElementById('sub_value_{{ $index }}');
                                                                if(subValueField) {
                                                                    subValueField.focus();
                                                                    subValueField.select();
                                                                }
                                                            }"
                                                                class="form-control text-center"
                                                                @if ($is_disabled) disabled @endif>
                                                        </td>

                                                        <td class="text-center" style="width: 15%">
                                                            <input type="number" step="0.01" min="0"
                                                                wire:model.blur="invoiceItems.{{ $index }}.sub_value"
                                                                id="sub_value_{{ $index }}"
                                                                placeholder="القيمة"
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
                                                                class="form-control text-center"
                                                                style="background-color: #f8f9fa;" readonly>
                                                        </td>

                                                        <td class="text-center" style="width: 10%">
                                                            <button type="button"
                                                                wire:click="removeRow({{ $index }})"
                                                                class="btn btn-danger btn-icon-square-sm"
                                                                @if ($is_disabled) disabled @endif
                                                                onclick="return confirm('هل أنت متأكد من حذف هذا الصف؟')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">
                                                            <div class="alert alert-info py-3 mb-0"
                                                                style="font-size: 1.2rem; font-weight: 500;">
                                                                <i class="las la-info-circle me-2"></i>
                                                                لا توجد بيانات
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
                </div>

                {{-- الإجماليات والمدفوعات --}}
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label style="font-size: 1em;">صندوق النقدية</label>
                            <select wire:model="cash_box_id" class="form-control form-control-sm"
                                @if ($is_disabled) disabled @endif>
                                <option value="">اختر صندوق النقدية</option>
                                @foreach ($cashAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label style="font-size: 1em;">المبلغ المستلم من العميل</label>
                            <input type="number" step="0.01" wire:model="received_from_client"
                                wire:change="calculateTotals" class="form-control form-control-sm" min="0"
                                @if ($is_disabled) disabled @endif>
                        </div>

                        <div class="form-group mb-3">
                            <label style="font-size: 1em;">ملاحظات</label>
                            <textarea wire:model="notes" class="form-control form-control-sm" rows="3" placeholder="ملاحظات إضافية..."
                                @if ($is_disabled) disabled @endif></textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-6 text-right font-weight-bold">الإجمالي الفرعي:</div>
                                    <div class="col-6 text-left text-primary">
                                        {{ number_format($subtotal) }} ج.م
                                    </div>
                                </div>

                                <div class="row mb-2 align-items-center">
                                    <div class="col-3 text-right font-weight-bold">
                                        <label style="font-size: 0.95em;">الخصم %</label>
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group">
                                            <input type="number" step="0.01" wire:model="discount_percentage"
                                                wire:change="calculateTotals" class="form-control form-control-sm"
                                                min="0" max="100"
                                                @if ($is_disabled) disabled @endif>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3 text-right font-weight-bold">
                                        <label class="form-label" style="font-size: 0.95em;">قيمة الخصم</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" step="0.01" wire:model="discount_value"
                                            wire:change="calculateTotals" class="form-control form-control-sm"
                                            min="0" id="discount_value"
                                            @if ($is_disabled) disabled @endif>
                                    </div>
                                </div>

                                <div class="row mb-2 align-items-center">
                                    <div class="col-3 text-right font-weight-bold">
                                        <label style="font-size: 0.95em;">الاضافي %</label>
                                    </div>
                                    <div class="col-3">
                                        <div class="input-group">
                                            <input type="number" step="0.01" wire:model="additional_percentage"
                                                wire:change="calculateTotals" class="form-control form-control-sm"
                                                min="0" max="100"
                                                @if ($is_disabled) disabled @endif>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3 text-right font-weight-bold">
                                        <label class="form-label" style="font-size: 0.95em;">قيمة الاضافي</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="number" step="0.01" wire:model="additional_value"
                                            wire:change="calculateTotals" class="form-control form-control-sm"
                                            min="0" id="additional_value"
                                            @if ($is_disabled) disabled @endif>
                                    </div>
                                </div>

                                <hr>

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

                {{-- زر التعديل/الحفظ --}}
                <div class="row mt-4">
                    <div class="col-12 text-left">
                        @if ($is_disabled)
                            <button type="button" wire:click="enableEditing" class="btn btn-lg btn-success">
                                <i class="fas fa-edit"></i> تعديل الفاتورة
                            </button>
                        @else
                            <button type="submit" class="btn btn-lg btn-primary">
                                <i class="fas fa-save"></i> حفظ الفاتورة
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </section>
    </div>
    @push('scripts')
        <script>
            // إضافة Alpine.js directive للتحكم في التركيز
            $(document).ready(function() {
                $(document).on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            });

            document.addEventListener('alpine:init', () => {
                Alpine.directive('focus-next', (el, {
                    expression
                }) => {
                    el.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const nextField = document.getElementById(expression);
                            if (nextField) {
                                nextField.focus();
                                nextField.select();
                            }
                        }
                    });
                });
            });

            // طريقة بديلة بدون Alpine
            document.addEventListener('DOMContentLoaded', function() {
                // استمع لحدث Livewire
                document.addEventListener('livewire:updated', function() {
                    setTimeout(function() {
                        addKeyboardListeners();
                    }, 100);
                });

                addKeyboardListeners();

                // استمع لحدث التركيز على حقل الكمية الجديد
                window.addEventListener('focusQuantityField', function(e) {
                    setTimeout(function() {
                        const field = document.getElementById('quantity_' + e.detail.rowIndex);
                        if (field) {
                            field.focus();
                            field.select();
                        }
                    }, 200);
                });
            });

            function addKeyboardListeners() {
                // إزالة المستمعات القديمة أولاً
                document.querySelectorAll('input[data-listener="true"]').forEach(function(field) {
                    field.removeAttribute('data-listener');
                });

                // إضافة مستمعات جديدة لحقول الكمية
                document.querySelectorAll('input[id^="quantity_"]').forEach(function(field) {
                    if (!field.hasAttribute('data-listener')) {
                        field.setAttribute('data-listener', 'true');
                        field.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const index = this.id.split('_')[1];
                                const nextField = document.getElementById('price_' + index);
                                if (nextField) {
                                    nextField.focus();
                                    nextField.select();
                                }
                            }
                        });
                    }
                });

                // إضافة مستمعات لحقول السعر
                document.querySelectorAll('input[id^="price_"]').forEach(function(field) {
                    if (!field.hasAttribute('data-listener')) {
                        field.setAttribute('data-listener', 'true');
                        field.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const index = this.id.split('_')[1];
                                const nextField = document.getElementById('discount_' + index);
                                if (nextField) {
                                    nextField.focus();
                                    nextField.select();
                                }
                            }
                        });
                    }
                });

                // إضافة مستمعات لحقول الخصم
                document.querySelectorAll('input[id^="discount_"]').forEach(function(field) {
                    if (!field.hasAttribute('data-listener')) {
                        field.setAttribute('data-listener', 'true');
                        field.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const index = this.id.split('_')[1];
                                const nextField = document.getElementById('sub_value_' + index);
                                if (nextField) {
                                    nextField.focus();
                                    nextField.select();
                                }
                            }
                        });
                    }
                });

                // إضافة مستمعات لحقول القيمة الفرعية
                document.querySelectorAll('input[id^="sub_value_"]').forEach(function(field) {
                    if (!field.hasAttribute('data-listener')) {
                        field.setAttribute('data-listener', 'true');
                        field.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const index = this.id.split('_')[2]; // sub_value_0 -> index = 0
                                const nextIndex = parseInt(index) + 1;
                                const nextQuantity = document.getElementById('quantity_' + nextIndex);
                                if (nextQuantity) {
                                    nextQuantity.focus();
                                    nextQuantity.select();
                                } else {
                                    // إذا لم يكن هناك صف تالي، انتقل لحقل البحث
                                    const searchField = document.querySelector(
                                        'input[wire\\:model\\.live="searchTerm"]');
                                    if (searchField) searchField.focus();
                                }
                            }
                        });
                    }
                });

                // دالة للتركيز على حقل الكمية بعد إضافة صنف من البحث
                window.focusLastQuantityField = function() {
                    setTimeout(function() {
                        const quantityFields = document.querySelectorAll('input[id^="quantity_"]');
                        if (quantityFields.length > 0) {
                            const lastField = quantityFields[quantityFields.length - 1];
                            lastField.focus();
                            lastField.select();
                        }
                    }, 150);
                };

                // إضافة مستمع لحقل final_price إذا وُجد
                const finalPriceField = document.getElementById('final_price');
                if (finalPriceField && !finalPriceField.hasAttribute('data-listener')) {
                    finalPriceField.setAttribute('data-listener', 'true');
                    finalPriceField.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            // مثلا تركيز على زر التأكيد
                            const submitBtn = document.querySelector('button[type="submit"]');
                            if (submitBtn) submitBtn.focus();
                        }
                    });
                }
            }

            // تشغيل المستمعات عند تحديث الصفحة
            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('livewire:updated', function() {
                    setTimeout(function() {
                        addKeyboardListeners();
                    }, 100);
                });

                addKeyboardListeners();
            });
        </script>
    @endpush

    <style>
        [disabled] {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.9;
        }
    </style>
</div>
