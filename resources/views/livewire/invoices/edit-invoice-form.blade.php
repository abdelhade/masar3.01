<div>
    <div class="invoice-container" style="position: relative;">
        {{-- invoice badge --}}
        <div class="payment-badge {{ $this->getPaymentBadgeClass() }}">
            <div class="badge-text">{{ $this->getPaymentBadgeText() }}</div>
        </div>
        {{--   --}}
        <div class="content-wrapper">
            <section class="content">
                <form wire:submit="updateForm">

                    @include('components.invoices.invoice-head')

                    <div class="row">
                        <div class="col-lg-4 mb-3" style="position: relative;">
                            <label>ابحث عن صنف</label>
                            <input type="text" wire:model.live="searchTerm" class="form-control frst"
                                placeholder="ابدأ بكتابة اسم الصنف..." autocomplete="off"
                                wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                                wire:keydown.enter.prevent="handleEnter"
                                @if ($is_disabled) disabled @endif />
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

                        <div class="col-lg-4 mb-3">
                            <label>ابحث بالباركود</label>
                            <input type="text" wire:model.live="barcodeTerm" class="form-control" id="barcode-search"
                                placeholder="ادخل الباركود " autocomplete="off" wire:keydown.enter="addItemByBarcode"
                                @if ($is_disabled) disabled @endif />
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
                            <div class="col-lg-3">
                                <label for="selectedPriceType">{{ __('اختر نوع السعر للفاتورة') }}</label>
                                <select wire:model.live="selectedPriceType"
                                    class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror"
                                    @if ($is_disabled) disabled @endif>
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

                    {{-- أزرار التحكم --}}
                    <div class="row mt-4">
                        <div class="col-12 text-left">
                            <!-- زر تعديل الفاتورة -->
                            @if ($is_disabled)
                                <button type="button" wire:click="enableEditing" class="btn btn-lg btn-success">
                                    <i class="fas fa-edit"></i> تعديل الفاتورة
                                </button>
                            @endif

                            <!-- زر تحويل الفاتورة -->
                            {{-- @if ($this->canConvertInvoice())
                            <button type="button" wire:click="openConvertModal" class="btn btn-lg btn-warning"
                                title="تحويل الفاتورة إلى نوع آخر">
                                <i class="fas fa-exchange-alt"></i> تحويل الفاتورة
                            </button>
                        @endif --}}
                        </div>
                    </div>

                    <!-- نافذة تحويل الفاتورة المنبثقة -->
                    {{-- @if ($showConvertModal)
                    <!-- خلفية النافذة -->
                    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);"
                        wire:click="closeConvertModal">
                        <!-- النافذة الرئيسية -->
                        <div class="modal-dialog modal-dialog-centered" wire:click.stop>
                            <div class="modal-content">
                                <!-- رأس النافذة -->
                                <div class="modal-header bg-warning text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exchange-alt me-2"></i>
                                        تحويل الفاتورة
                                    </h5>
                                    <button type="button" wire:click="closeConvertModal"
                                        class="btn-close btn-close-white" aria-label="إغلاق"></button>
                                </div>

                                <!-- محتوى النافذة -->
                                <div class="modal-body">
                                    <!-- معلومات الفاتورة الحالية -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-info-circle me-1"></i>
                                                معلومات الفاتورة الحالية
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>النوع الحالي:</strong><br>
                                                    <span
                                                        class="badge bg-primary">{{ $titles[$type] ?? 'غير محدد' }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <strong>رقم الفاتورة:</strong><br>
                                                    <span class="text-muted">{{ $pro_id }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- اختيار النوع الجديد -->
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-arrow-right me-1"></i>
                                            التحويل إلى:
                                        </label>
                                        <select wire:model="selectedConvertType" class="form-select">
                                            <option value="">اختر نوع الفاتورة الجديد</option>
                                            @foreach ($convertFromTypes as $typeId => $typeName)
                                                <option value="{{ $typeId }}">{{ $typeName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- رسالة التأكيد -->
                                    @if ($selectedConvertType)
                                        <div class="alert alert-warning">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                                <div>
                                                    <strong>تأكيد التحويل:</strong><br>
                                                    {{ $this->getConversionConfirmationMessage() }}
                                                    <hr class="my-2">
                                                    <small class="text-muted">
                                                        <strong>ملاحظات مهمة:</strong>
                                                        <ul class="mb-0 mt-1">
                                                            <li>سيتم تحديث الأسعار والحسابات تلقائياً حسب النوع الجديد
                                                            </li>
                                                            <li>ستحتفظ الفاتورة بجميع الأصناف والكميات</li>
                                                            <li>يمكنك حفظ التغييرات أو إلغاؤها بعد التحويل</li>
                                                        </ul>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- أزرار النافذة -->
                                <div class="modal-footer">
                                    <button type="button" wire:click="closeConvertModal" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        إلغاء
                                    </button>
                                    <button type="button" wire:click="convertInvoice" class="btn btn-warning"
                                        @if (!$selectedConvertType) disabled @endif>
                                        <i class="fas fa-exchange-alt me-1"></i>
                                        تحويل الفاتورة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif --}}
                </form>
            </section>
        </div>
    </div>

    <style>
        [disabled] {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.9;
        }

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

        .payment-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 120px;
            height: 82px;
            overflow: hidden;
            z-index: 10;
        }

        .payment-badge::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            border-left: 120px solid transparent;
            border-top: 120px solid;
        }

        .payment-badge .badge-text {
            position: absolute;
            top: 20px;
            right: 10px;
            color: white;
            font-weight: bold;
            font-size: 14px;
            transform: rotate(45deg);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .payment-badge.unpaid::before {
            border-top-color: #dc3545;
        }

        .payment-badge.partial::before {
            border-top-color: #ffc107;
            color: #000;
        }

        .payment-badge.full::before {
            border-top-color: #28a745;
        }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
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
