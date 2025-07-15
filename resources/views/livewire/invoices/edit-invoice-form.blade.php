<div>
    <div class="content-wrapper">
        <section class="content">
            <form wire:submit="updateForm">

                @include('components.invoices.invoice-head')

                {{-- قسم البحث عن الأصناف وإضافة نوع السعر --}}

                <div class="row">

                    <div class="col-lg-4 mb-3" style="position: relative;">
                        <label>ابحث عن صنف</label>
                        <input type="text" wire:model.live="searchTerm" class="form-control frst"
                            placeholder="ابدأ بكتابة اسم الصنف..." autocomplete="off"
                            wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                            wire:keydown.enter.prevent="handleEnter" @if ($is_disabled) disabled @endif />
                        @if (strlen($searchTerm) > 0 && $searchResults->count())
                            <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                @foreach ($searchResults as $index => $item)
                                    <li class="list-group-item list-group-item-action
                         @if ($selectedResultIndex === $index) active @endif"
                                        wire:click="addItemFromSearch({{ $item->id }})">
                                        {{ $item->name }}
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

                    {{-- اختيار نوع السعر العام للفاتورة --}}
                    <div class="col-lg-3">
                        <label for="selectedPriceType">{{ __('اختر نوع السعر للفاتورة') }}</label>
                        <select wire:model.live="selectedPriceType"
                            class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror" @if ($is_disabled) disabled @endif>
                            <option value="">{{ __('اختر نوع السعر') }}</option>
                            @foreach ($priceTypes as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('selectedPriceType')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="row form-control">
                    @include('components.invoices.invoice-item-table')
                </div>

                {{-- قسم الإجماليات والمدفوعات --}}
                @include('components.invoices.invoice-footer')

                <div class="row mt-4">
                    <div class="col-12 text-left">
                        <button type="submit" class="btn btn-lg btn-primary" @if ($is_disabled) disabled @endif>
                            <i class="fas fa-save"></i> حفظ الفاتورة
                        </button>
                        @if ($is_disabled)
                            <button type="button" wire:click="enableEditing" class="btn btn-lg btn-success">
                                <i class="fas fa-edit"></i> تعديل الفاتورة
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
