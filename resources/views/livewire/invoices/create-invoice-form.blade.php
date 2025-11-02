<div>
    <div class="content-wrapper">
        <section class="content">
            <form wire:submit="saveForm">

                @include('components.invoices.invoice-head')
                {{-- أضف هذا في بداية الـ row الخاص بالبحث في الـ View --}}
                <div class="row">

                    @if (setting('invoice_use_templates'))
                        @if ($availableTemplates->isNotEmpty())
                            <div class="col-lg-1">
                                <label for="selectedTemplate">{{ __('نموذج الفاتورة') }}</label>
                                <select wire:model.live="selectedTemplateId" id="selectedTemplate"
                                    class="form-control @error('selectedTemplateId') is-invalid @enderror">
                                    @foreach ($availableTemplates as $template)
                                        <option value="{{ $template->id }}">
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedTemplateId')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                    @endif
                    <div class="col-lg-3 mb-3" style="position: relative;">
                        <label>ابحث عن صنف</label>
                        <input type="text" wire:model.live="searchTerm" class="form-control frst"
                            placeholder="ابدأ بكتابة اسم الصنف..." autocomplete="off"
                            wire:keydown.arrow-down="handleKeyDown" wire:keydown.arrow-up="handleKeyUp"
                            wire:keydown.enter.prevent="handleEnter" />
                        @if (strlen($searchTerm) > 0 && $searchResults->count())
                            <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                @foreach ($searchResults as $index => $item)
                                    <li class="list-group-item list-group-item-action @if ($selectedResultIndex === $index) active @endif"
                                        wire:click="addItemFromSearch({{ $item->id }})">
                                        {{ $item->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @elseif (strlen($searchTerm) > 0 && $searchResults->isEmpty())
                            <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                <li class="list-group-item list-group-item-action list-group-item-success
                    @if ($isCreateNewItemSelected) active @endif"
                                    style="cursor: pointer;" wire:click.prevent="createNewItem('{{ $searchTerm }}')">
                                    <i class="fas fa-plus"></i>
                                    <strong>إنشاء صنف جديد:</strong> "{{ $searchTerm }}"
                                </li>
                            </ul>
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
                            {{-- @elseif (strlen($barcodeTerm) > 0) --}}
                        @endif
                    </div>
                    @if (setting('invoice_select_price_type'))
                        {{-- اختيار نوع السعر العام للفاتورة --}}
                        @if (in_array($type, [10, 12, 14, 16, 22]))
                            <div class="col-lg-2">
                                <label for="selectedPriceType">{{ __('اختر نوع السعر للفاتورة') }}</label>
                                <select wire:model.live="selectedPriceType"
                                    class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror">
                                    {{-- <option value="">{{ __('اختر نوع السعر') }}</option> --}}
                                    @foreach ($priceTypes as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedPriceType')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                    @endif
                    {{-- <x-branches::branch-select :branches="$branches" model="branch_id" /> --}}

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

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('enlarge-menu');
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (data) => {
                Swal.fire({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                }).then((result) => {
                    location.reload();
                });
            });
        })


        document.addEventListener('livewire:initialized', () => {
            @this.on('prompt-create-item-from-barcode', (event) => {
                Swal.fire({
                    title: 'صنف غير موجود!',
                    text: `الباركود "${event.barcode}" غير مسجل. هل تريد إنشاء صنف جديد؟`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، قم بالإنشاء',
                    cancelButtonText: 'إلغاء',
                    input: 'text',
                    inputLabel: 'الرجاء إدخال اسم الصنف الجديد',
                    inputPlaceholder: 'اكتب اسم الصنف هنا...',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'اسم الصنف مطلوب!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        // استدعاء دالة Livewire لإتمام عملية الإنشاء
                        @this.call('createItemFromPrompt', result.value, event.barcode);
                    }
                });
            });
        });


        // document.addEventListener('livewire:init', () => {
        //     Livewire.on('no-quantity', (data) => {
        //         Swal.fire({
        //             title: data.title,
        //             text: data.text,
        //             icon: data.icon,
        //         })
        //     });
        // });

        document.addEventListener('livewire:init', () => {
            Livewire.on('error', (data) => {
                Swal.fire({
                    title: data.title,
                    text: data.text,
                    icon: data.icon,
                })
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

        // document.addEventListener('item-not-found', function() {
        // Swal.fire({
        // title: 'الصنف غير موجود',
        // text: 'الصنف بالباركود المدخل غير موجود. هل تريد إضافة صنف جديد؟',
        // icon: 'warning',
        // showCancelButton: true,
        // confirmButtonText: 'نعم، إضافة صنف',
        // cancelButtonText: 'لا',
        // }).then((result) => {
        // if (result.isConfirmed) {
        // window.location.href = '{{ route('items.create') }}';
        // }
        // });
        // });

        // طريقة بديلة بدون Alpine
        // document.addEventListener('DOMContentLoaded', function() {
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
        // });

        // دالة للتحقق من إمكانية الوصول للعنصر
        function isElementAccessible(element) {
            if (!element) return false;

            // التحقق من أن العنصر مرئي وغير مخفي
            const style = window.getComputedStyle(element);
            if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                return false;
            }

            // التحقق من أن العنصر غير معطل
            if (element.disabled || element.readOnly) {
                return false;
            }

            // التحقق من أن العنصر داخل viewport
            const rect = element.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
                return false;
            }

            return true;
        }

        // دالة للعثور على العنصر التالي المتاح
        function findNextAccessibleElement(currentElement, selectors) {
            for (let selector of selectors) {
                const element = document.querySelector(selector);
                if (element && isElementAccessible(element) && element !== currentElement) {
                    return element;
                }
            }
            return null;
        }

        // دالة للعثور على العنصر التالي في نفس الصف
        function findNextInRow(currentElement, rowIndex) {
            const fieldOrder = [
                `quantity_${rowIndex}`,
                `price_${rowIndex}`,
                `discount_${rowIndex}`,
                `sub_value_${rowIndex}`
            ];

            const currentId = currentElement.id;
            const currentIndex = fieldOrder.indexOf(currentId);

            // البحث عن العنصر التالي في نفس الصف
            for (let i = currentIndex + 1; i < fieldOrder.length; i++) {
                const nextElement = document.getElementById(fieldOrder[i]);
                if (nextElement && isElementAccessible(nextElement)) {
                    return nextElement;
                }
            }

            return null;
        }

        // دالة للعثور على العنصر التالي في الصف التالي
        function findNextInNextRow(currentRowIndex) {
            const nextRowIndex = currentRowIndex + 1;
            const nextRowFields = [
                `quantity_${nextRowIndex}`,
                `price_${nextRowIndex}`,
                `discount_${nextRowIndex}`,
                `sub_value_${nextRowIndex}`
            ];

            for (let fieldId of nextRowFields) {
                const element = document.getElementById(fieldId);
                if (element && isElementAccessible(element)) {
                    return element;
                }
            }

            return null;
        }

        // دالة للعثور على العنصر التالي في النموذج
        function findNextFormElement(currentElement) {
            const allFormElements = [
                'input[wire\\:model\\.live="searchTerm"]',
                'input[id="barcode-search"]',
                'select[wire\\:model\\.live="selectedPriceType"]',
                'select[wire\\:model="branch_id"]',
                'select[wire\\:model="status"]',
                'input[id="final_price"]',
                'button[type="submit"]'
            ];

            return findNextAccessibleElement(currentElement, allFormElements);
        }

        // دالة للعثور على أول حقل كمية متاح
        function findFirstAvailableQuantityField() {
            const quantityFields = document.querySelectorAll('input[id^="quantity_"]');
            for (let field of quantityFields) {
                if (isElementAccessible(field)) {
                    return field;
                }
            }
            return null;
        }

        // دالة للتحقق من وجود عناصر في الجدول
        function hasTableItems() {
            const quantityFields = document.querySelectorAll('input[id^="quantity_"]');
            return quantityFields.length > 0;
        }

        // دالة للعثور على آخر حقل متاح في الجدول
        function findLastAvailableTableField() {
            const allTableFields = [];

            // جمع جميع حقول الجدول
            ['quantity_', 'price_', 'discount_', 'sub_value_'].forEach(function(prefix) {
                document.querySelectorAll(`input[id^="${prefix}"]`).forEach(function(field) {
                    allTableFields.push(field);
                });
            });

            // العثور على آخر حقل متاح
            for (let i = allTableFields.length - 1; i >= 0; i--) {
                if (isElementAccessible(allTableFields[i])) {
                    return allTableFields[i];
                }
            }

            return null;
        }

        // دالة للتنقل الذكي
        function smartNavigate(currentElement) {
            let nextElement = null;

            // تحديد نوع العنصر الحالي
            const currentId = currentElement.id;

            // إذا كان العنصر من حقول الجدول
            if (currentId.startsWith('quantity_') || currentId.startsWith('price_') || currentId.startsWith('discount_')) {
                const index = currentId.split('_')[1];
                nextElement = findNextInRow(currentElement, index);
                if (!nextElement) {
                    nextElement = findNextInNextRow(parseInt(index));
                }
                if (!nextElement) {
                    nextElement = findNextFormElement(currentElement);
                }
            } else if (currentId.startsWith('sub_value_')) {
                const index = currentId.split('_')[2];
                nextElement = findNextInNextRow(parseInt(index));
                if (!nextElement) {
                    nextElement = findNextFormElement(currentElement);
                }
            } else {
                // للعناصر الأخرى في النموذج
                nextElement = findNextFormElement(currentElement);
            }

            // إذا لم يوجد عنصر تالي، انتقل لأول حقل كمية متاح
            if (!nextElement) {
                nextElement = findFirstAvailableQuantityField();
            }

            // إذا لم يوجد أي عنصر متاح، انتقل لأول عنصر في النموذج
            if (!nextElement) {
                const firstFormElement = document.querySelector('input[wire\\:model\\.live="searchTerm"]');
                if (firstFormElement && isElementAccessible(firstFormElement)) {
                    nextElement = firstFormElement;
                }
            }

            return nextElement;
        }

        function addKeyboardListeners() {
            // إزالة المستمعات القديمة أولاً
            document.querySelectorAll('input[data-listener="true"]').forEach(function(field) {
                field.removeAttribute('data-listener');
            });

            // إضافة مستمعات لجميع حقول الجدول (كمية، سعر، خصم، قيمة فرعية)
            const tableFields = ['quantity_', 'price_', 'discount_', 'sub_value_'];
            tableFields.forEach(function(prefix) {
                document.querySelectorAll(`input[id^="${prefix}"]`).forEach(function(field) {
                    if (!field.hasAttribute('data-listener')) {
                        field.setAttribute('data-listener', 'true');
                        field.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const nextElement = smartNavigate(this);
                                if (nextElement) {
                                    nextElement.focus();
                                    if (nextElement.select) nextElement.select();
                                }
                            }
                        });
                    }
                });
            });

            // دالة للتركيز على حقل الكمية بعد إضافة صنف من البحث
            window.focusLastQuantityField = function() {
                setTimeout(function() {
                    const quantityFields = document.querySelectorAll('input[id^="quantity_"]');
                    if (quantityFields.length > 0) {
                        const lastField = quantityFields[quantityFields.length - 1];
                        if (isElementAccessible(lastField)) {
                            lastField.focus();
                            lastField.select();
                        } else {
                            // إذا كان آخر حقل غير متاح، ابحث عن آخر حقل متاح
                            const lastAvailableField = findLastAvailableTableField();
                            if (lastAvailableField) {
                                lastAvailableField.focus();
                                if (lastAvailableField.select) lastAvailableField.select();
                            }
                        }
                    }
                }, 150);
            };

            // دالة للتركيز على أول حقل متاح
            window.focusFirstAvailableField = function() {
                setTimeout(function() {
                    const firstField = findFirstAvailableQuantityField();
                    if (firstField) {
                        firstField.focus();
                        if (firstField.select) firstField.select();
                    } else {
                        // إذا لم يوجد حقول كمية، انتقل لأول عنصر في النموذج
                        const firstFormElement = document.querySelector(
                            'input[wire\\:model\\.live="searchTerm"]');
                        if (firstFormElement && isElementAccessible(firstFormElement)) {
                            firstFormElement.focus();
                            if (firstFormElement.select) firstFormElement.select();
                        }
                    }
                }, 150);
            };

            document.addEventListener('item-not-found', function(event) {
                const data = event.detail;
                const term = data.term || '';
                const type = data.type || 'barcode';

                let title = 'الصنف غير موجود';
                let text = '';
                let itemCreateUrl = '';

                if (type === 'barcode') {
                    text = `الصنف غير موجود. هل تريد إضافة صنف جديد؟`;
                    // تمرير الباركود كمعامل في الرابط
                    itemCreateUrl = `{{ route('items.create') }}?barcode=${encodeURIComponent(term)}`;
                } else {
                    text = `الصنف "${term}" غير موجود. هل تريد إضافة صنف جديد؟`;
                    // تمرير اسم الصنف كمعامل في الرابط
                    itemCreateUrl = `{{ route('items.create') }}?name=${encodeURIComponent(term)}`;
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، إضافة صنف',
                    cancelButtonText: 'لا',
                    allowEscapeKey: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(itemCreateUrl, '_blank');
                    }

                    // تنظيف وإعادة التركيز حسب نوع البحث
                    // if (type === 'barcode') {
                    // const barcodeInput = document.getElementById('barcode-search');
                    // if (barcodeInput) {
                    // barcodeInput.value = '';
                    // barcodeInput.focus();
                    // @this.set('barcodeTerm', '');
                    // }
                    // } else {
                    // const searchInput = document.querySelector(
                    // 'input[wire\\:model\\.live="searchTerm"]');
                    // if (searchInput) {
                    // searchInput.value = '';
                    // searchInput.focus();
                    // @this.set('searchTerm', '');
                    // }
                    // }
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Swal.close();

                    // تحديد نوع الحقل النشط وتنظيفه
                    const activeElement = document.activeElement;

                    if (activeElement && activeElement.id === 'barcode-search') {
                        activeElement.value = '';
                        activeElement.focus();
                        @this.set('barcodeTerm', '');
                    } else if (activeElement && activeElement.hasAttribute('wire:model.live') &&
                        activeElement.getAttribute('wire:model.live') === 'searchTerm') {
                        activeElement.value = '';
                        activeElement.focus();
                        @this.set('searchTerm', '');
                    }
                }
            });
            const searchInput = document.querySelector('input[wire\\:model\\.live="searchTerm"]');
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        // إذا لم تكن هناك نتائج، تحقق من البحث
                        @this.call('handleEnter');
                    }
                });
            }

            // إضافة مستمعات لجميع عناصر النموذج الأخرى
            const formElements = [
                'input[wire\\:model\\.live="searchTerm"]',
                'input[id="barcode-search"]',
                'select[wire\\:model\\.live="selectedPriceType"]',
                'select[wire\\:model="branch_id"]',
                'select[wire\\:model="status"]',
                'input[id="final_price"]'
            ];

            formElements.forEach(function(selector) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(function(element) {
                    if (!element.hasAttribute('data-listener')) {
                        element.setAttribute('data-listener', 'true');
                        element.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const nextElement = smartNavigate(this);
                                if (nextElement) {
                                    nextElement.focus();
                                    if (nextElement.select) nextElement.select();
                                }
                            }
                        });
                    }
                });
            });
        }

        window.addEventListener('focus-last-quantity-field', function() {
            window.focusLastQuantityField();
        });

        window.addEventListener('focus-first-available-field', function() {
            window.focusFirstAvailableField();
        });

        window.addEventListener('focus-barcode-field', () => {
            setTimeout(() => {
                document.getElementById('barcode-input')?.focus();
            }, 100);
        });

        window.addEventListener('focus-quantity-field', event => {
            const index = event.detail;
            setTimeout(() => {
                const field = document.getElementById(`quantity-${index}`) || document.getElementById(
                    `quantity_${index}`);
                if (field && isElementAccessible(field)) {
                    field.focus();
                    if (field.select) field.select();
                } else {
                    // إذا كان الحقل غير متاح، ابحث عن أول حقل متاح
                    window.focusFirstAvailableField();
                }
            }, 100);
        });

        window.focusBarcodeSearch = function() {
            const barcodeInput = document.getElementById('barcode-search');
            if (barcodeInput && isElementAccessible(barcodeInput)) {
                barcodeInput.focus();
            } else {
                console.error('Barcode search field not found or not accessible');
                // إذا كان حقل الباركود غير متاح، انتقل لأول حقل متاح
                window.focusFirstAvailableField();
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

        // إضافة مستمع عام للتنقل بالـ Enter في جميع أنحاء الصفحة
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.type !== 'submit') {
                // التحقق من أن العنصر الحالي لديه مستمع مخصص
                if (!e.target.hasAttribute('data-listener')) {
                    e.preventDefault();
                    const nextElement = smartNavigate(e.target);
                    if (nextElement) {
                        nextElement.focus();
                        if (nextElement.select) nextElement.select();
                    }
                }
            }
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
        });
    </script>
@endpush
