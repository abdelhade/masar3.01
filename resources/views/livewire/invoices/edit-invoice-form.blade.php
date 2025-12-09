<div>
    @section('formAction', 'edit')
    <div class="invoice-container">
        <div class="content-wrapper">
            <section class="content">
                <form wire:submit="updateForm"
                    x-data="invoiceCalculations({
                        invoiceItems: @js($invoiceItems),
                        discountPercentage: @js($discount_percentage ?? 0),
                        additionalPercentage: @js($additional_percentage ?? 0),
                        receivedFromClient: @js($received_from_client ?? 0),
                        dimensionsUnit: @js($dimensionsUnit ?? 'cm'),
                        enableDimensionsCalculation: @js($enableDimensionsCalculation ?? false),
                        invoiceType: @js($type ?? 10)
                    })"
                    x-init="
                        // تحديث invoiceItems من Livewire عند التغيير
                        $watch('invoiceItems', () => {
                            if (typeof $wire !== 'undefined') {
                                invoiceItems = $wire.invoiceItems;
                                syncToLivewire();
                            }
                        });
                        $watch('discountPercentage', () => syncToLivewire());
                        $watch('additionalPercentage', () => syncToLivewire());
                        $watch('receivedFromClient', () => syncToLivewire());
                        
                        // الاستماع لتحديثات Livewire
                        if (typeof Livewire !== 'undefined') {
                            Livewire.hook('morph.updated', ({ component }) => {
                                if (component?.__instance?.__livewire) {
                                    const wire = component.__instance.__livewire;
                                    invoiceItems = wire.invoiceItems || invoiceItems;
                                    discountPercentage = parseFloat(wire.discount_percentage) || 0;
                                    additionalPercentage = parseFloat(wire.additional_percentage) || 0;
                                    receivedFromClient = parseFloat(wire.received_from_client) || 0;
                                }
                            });
                        }
                    ">

                    @include('components.invoices.invoice-head')

                    <div class="row">
                        <div class="col-lg-3 mb-3" style="position: relative;">
                            <label>ابحث عن صنف</label>
                            <input type="text" wire:model.live="searchTerm" class="form-control frst"
                                placeholder="ابدأ بكتابة اسم الصنف..." autocomplete="off"
                                wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                                wire:keydown.enter.prevent="handleEnter" />
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
                            @elseif(strlen($searchTerm) > 0 && $searchResults->isEmpty())
                                <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                    <li class="list-group-item list-group-item-action list-group-item-success @if ($isCreateNewItemSelected) active @endif"
                                        style="cursor: pointer;"
                                        wire:click.prevent="createNewItem('{{ $searchTerm }}')">
                                        <i class="fas fa-plus"></i>
                                        <strong>إنشاء صنف جديد:</strong> "{{ $searchTerm }}"
                                    </li>
                                </ul>
                            @elseif(strlen($searchTerm) > 0)
                                <div class="mt-2" style="position: absolute; z-index: 1000; width: 100%;">
                                    <div class="list-group-item text-danger">
                                        لا توجد نتائج لـ "{{ $searchTerm }}"
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-3 mb-3">
                            <label>ابحث بالباركود</label>
                            <input type="text" wire:model.live="barcodeTerm" class="form-control" id="barcode-search"
                                placeholder="ادخل الباركود " autocomplete="off" wire:keydown.enter="addItemByBarcode" />
                            @if (strlen($barcodeTerm) > 0 && $barcodeSearchResults->count())
                                <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                    @foreach ($barcodeSearchResults as $index => $item)
                                        <li class="list-group-item list-group-item-action"
                                            wire:click="addItemFromSearch({{ $item->id }})">
                                            {{ $item->name }} ({{ $item->code }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- اختيار نوع السعر العام للفاتورة --}}
                        @if (in_array($type, [10, 12, 14, 16, 22]))
                            <div class="col-lg-2">
                                <label for="selectedPriceType">{{ __('اختر نوع السعر للفاتورة') }}</label>
                                <select wire:model.live="selectedPriceType"
                                    class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror">
                                    <option value="">{{ __('اختر نوع السعر') }}</option>
                                    @foreach ($priceTypes as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedPriceType')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif

                        <x-branches::branch-select :branches="$branches" model="branch_id" />

                        @if ($type == 14)
                            <div class="col-lg-1">
                                <label for="status">{{ __('حالة الفاتوره') }}</label>
                                <select wire:model="status" id="status"
                                    class="form-control form-control-sm @error('status') is-invalid @enderror">
                                    @foreach ($statues as $statusCase)
                                        <option value="{{ $statusCase->value }}">{{ $statusCase->translate() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="row form-control">
                        @include('components.invoices.invoice-item-table')
                    </div>

                    {{-- قسم الإجماليات والمدفوعات --}}
                    @include('components.invoices.invoice-footer')
                </form>
            </section>
        </div>
    </div>

    <style>
        .modal.show {
            z-index: 1055;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            font-size: 0.875em;
        }

        .alert ul {
            padding-left: 1.2rem;
        }

        .modal-dialog-centered {
            min-height: calc(100vh - 1rem);
        }

        @media (min-width: 576px) {
            .modal-dialog-centered {
                min-height: calc(100vh - 3.5rem);
            }
        }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Alpine.js Component للحسابات (نفس المستخدم في create)
            document.addEventListener('alpine:init', () => {
                Alpine.data('invoiceCalculations', (initialData) => ({
                    invoiceItems: initialData.invoiceItems || [],
                    discountPercentage: parseFloat(initialData.discountPercentage) || 0,
                    additionalPercentage: parseFloat(initialData.additionalPercentage) || 0,
                    receivedFromClient: parseFloat(initialData.receivedFromClient) || 0,
                    dimensionsUnit: initialData.dimensionsUnit || 'cm',
                    enableDimensionsCalculation: initialData.enableDimensionsCalculation || false,
                    invoiceType: initialData.invoiceType || 10,

                    calculateSubValue(item) {
                        const qty = parseFloat(item.quantity) || 0;
                        const price = parseFloat(item.price) || 0;
                        const discount = parseFloat(item.discount) || 0;
                        const result = (qty * price) - discount;
                        return Math.round(result * 100) / 100; // دقة أفضل للتقريب
                    },

                    calculateQuantityFromDimensions(item) {
                        if (!this.enableDimensionsCalculation) return item.quantity;
                        
                        const length = parseFloat(item.length) || 0;
                        const width = parseFloat(item.width) || 0;
                        const height = parseFloat(item.height) || 0;
                        const density = parseFloat(item.density) || 1;
                        
                        if (length > 0 && width > 0 && height > 0) {
                            let quantity = length * width * height * density;
                            
                            if (this.dimensionsUnit === 'cm') {
                                quantity = quantity / 1000000;
                            }
                            
                            return Math.round(quantity * 1000) / 1000;
                        }
                        return item.quantity || 0;
                    },

                    calculateQuantityFromSubValue(item) {
                        const subValue = parseFloat(item.sub_value) || 0;
                        const discount = parseFloat(item.discount) || 0;
                        const price = parseFloat(item.price) || 0;
                        
                        if (price <= 0) return 0;
                        
                        return Math.round(((subValue + discount) / price) * 1000) / 1000;
                    },

                    get subtotal() {
                        const sum = this.invoiceItems.reduce((total, item) => {
                            return total + this.calculateSubValue(item);
                        }, 0);
                        return Math.round(sum * 100) / 100;
                    },

                    get discountValue() {
                        const percentage = parseFloat(this.discountPercentage) || 0;
                        const result = (this.subtotal * percentage) / 100;
                        return Math.round(result * 100) / 100;
                    },

                    get additionalValue() {
                        const percentage = parseFloat(this.additionalPercentage) || 0;
                        const result = (this.subtotal * percentage) / 100;
                        return Math.round(result * 100) / 100;
                    },

                    get totalAfterAdditional() {
                        const result = this.subtotal - this.discountValue + this.additionalValue;
                        return Math.round(result * 100) / 100;
                    },

                    get remaining() {
                        const total = this.totalAfterAdditional;
                        const received = parseFloat(this.receivedFromClient) || 0;
                        return Math.max(total - received, 0);
                    },

                    syncToLivewire() {
                        if (typeof $wire === 'undefined') return;
                        
                        // تحديث القيم المحسوبة في Livewire (استخدام defer لتقليل الطلبات)
                        $wire.set('subtotal', this.subtotal).defer;
                        $wire.set('discount_value', this.discountValue).defer;
                        $wire.set('additional_value', this.additionalValue).defer;
                        $wire.set('total_after_additional', this.totalAfterAdditional).defer;
                        
                        // تحديث القيم الفرعية لكل صنف
                        this.invoiceItems.forEach((item, index) => {
                            const subValue = this.calculateSubValue(item);
                            if (item.sub_value !== subValue) {
                                $wire.set(`invoiceItems.${index}.sub_value`, subValue).defer;
                            }
                        });
                    },

                    updateFromLivewire(livewireData) {
                        if (livewireData.invoiceItems) {
                            this.invoiceItems = livewireData.invoiceItems;
                        }
                        if (livewireData.discount_percentage !== undefined) {
                            this.discountPercentage = parseFloat(livewireData.discount_percentage) || 0;
                        }
                        if (livewireData.additional_percentage !== undefined) {
                            this.additionalPercentage = parseFloat(livewireData.additional_percentage) || 0;
                        }
                        if (livewireData.received_from_client !== undefined) {
                            this.receivedFromClient = parseFloat(livewireData.received_from_client) || 0;
                        }
                    },

                }));
            });

            // إضافة Alpine.js directive للتحكم في التركيز
            $(document).ready(function() {
                $(document).on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            });

            document.addEventListener('item-not-found', function() {
                Swal.fire({
                    title: 'الصنف غير موجود',
                    text: 'الصنف بالباركود المدخل غير موجود. هل تريد إضافة صنف جديد؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، إضافة صنف',
                    cancelButtonText: 'لا',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('items.create') }}';
                    }
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

                document.addEventListener('item-not-found', function() {
                    Swal.fire({
                        title: 'الصنف غير موجود',
                        text: 'الصنف بالباركود المدخل غير موجود. هل تريد إضافة صنف جديد؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'نعم، إضافة صنف',
                        cancelButtonText: 'لا',
                        allowEscapeKey: true // تأكد إن زر Esc مسموح
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('{{ route('items.create') }}', '_blank');
                        }
                        const barcodeInput = document.getElementById('barcode-search');
                        if (barcodeInput) {
                            barcodeInput.value = ''; // تنظيف الحقل
                            barcodeInput.focus(); // إرجاع التركيز للحقل
                            @this.set('barcodeTerm', ''); // تحديث Livewire
                        }
                    });
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        Swal.close(); // إغلاق الـ SweetAlert يدوياً
                        const barcodeInput = document.getElementById('barcode-search');
                        if (barcodeInput) {
                            barcodeInput.value = ''; // تنظيف الحقل
                            barcodeInput.focus(); // إرجاع التركيز
                            @this.set('barcodeTerm', ''); // تحديث Livewire
                        }
                    }
                });

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

            window.addEventListener('focus-last-quantity-field', function() {
                setTimeout(function() {
                    const quantityFields = document.querySelectorAll('input[id^="quantity_"]');
                    if (quantityFields.length > 0) {
                        const lastField = quantityFields[quantityFields.length - 1];
                        lastField.focus();
                        lastField.select();
                    }
                }, 150);
            });

            window.addEventListener('focus-barcode-field', () => {
                setTimeout(() => {
                    document.getElementById('barcode-input')?.focus();
                }, 100);
            });

            window.addEventListener('focus-quantity-field', event => {
                const index = event.detail;
                setTimeout(() => {
                    document.getElementById(`quantity-${index}`)?.focus();
                }, 100);
            });

            window.focusBarcodeSearch = function() {
                const barcodeInput = document.getElementById('barcode-search'); // تأكد من الـ ID
                if (barcodeInput) {
                    barcodeInput.focus();
                } else {
                    console.error('Barcode search field not found');
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('livewire:updated', function() {
                    setTimeout(function() {
                        addKeyboardListeners();
                    }, 100);
                });

                addKeyboardListeners();
            });

            document.addEventListener('livewire:init', () => {
                Livewire.on('open-print-window', (event) => {
                    const url = event.url;
                    console.log('JavaScript received print URL: ' + url);
                    const printWindow = window.open(url, '_blank');
                    if (printWindow) {
                        printWindow.onload = function() {
                            printWindow.print();
                        };
                    } else {
                        alert('يرجى السماح بفتح النوافذ المنبثقة في المتصفح للطباعة.');
                    }
                });

                Livewire.on('swal', (data) => {
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                    });
                });
            });
        </script>
    @endpush
</div>
