<div>
    @section('formAction', 'create')
    <div class="content-wrapper">
        <section class="content">
            <form wire:submit="saveForm">

                @include('components.invoices.invoice-head')

                <div class="row">
                    @if (setting('invoice_use_templates'))
                        @if ($availableTemplates->isNotEmpty())
                            <div class="col-lg-1">
                                <label for="selectedTemplate">{{ __('Invoice Template') }}</label>
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

                        <label>{{ __('Search Item') }}</label>

                        <div x-data="{
                            searchTerm: '',
                            searchResults: [],
                            loading: false,
                            showResults: false,
                            selectedIndex: -1,

                            async search() {
                                if (this.searchTerm.length === 0) {
                                    this.searchResults = [];
                                    this.showResults = false;
                                    this.selectedIndex = -1;
                                    return;
                                }

                                this.loading = true;

                                try {
                                    const url = '{{ url('/api/items/search') }}?term=' + encodeURIComponent(this.searchTerm) +
                                        '&type={{ $type }}&branch_id={{ $branch_id ?? '' }}&price_type={{ $selectedPriceType ?? 1 }}';

                                    const response = await fetch(url, {
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        credentials: 'same-origin'
                                    });

                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }

                                    const data = await response.json();
                                    this.searchResults = data;
                                    this.showResults = true;
                                    this.selectedIndex = data.length > 0 ? 0 : -1;
                                } catch (error) {
                                    console.error('Search error:', error);
                                } finally {
                                    this.loading = false;
                                }
                            },

                            selectNext() {
                                const totalItems = this.searchResults.length;

                                if (totalItems === 0 && this.searchTerm.length > 0) {
                                    this.selectedIndex = 0;
                                    return;
                                }

                                if (totalItems > 0) {
                                    if (this.selectedIndex === -1) {
                                        this.selectedIndex = 0;
                                    } else if (this.selectedIndex < totalItems - 1) {
                                        this.selectedIndex++;
                                    }
                                }

                                this.scrollToSelected();
                            },

                            selectPrevious() {
                                if (this.selectedIndex <= 0) {
                                    this.selectedIndex = 0;
                                } else {
                                    this.selectedIndex--;
                                }

                                this.scrollToSelected();
                            },

                            scrollToSelected() {
                                this.$nextTick(() => {
                                    const selected = this.$el.querySelector('.search-item-' + this.selectedIndex);
                                    if (selected) {
                                        selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                                    }
                                });
                            },

                            addSelectedItem() {
                                if (this.searchResults.length === 0 && this.searchTerm.length > 0 && this.selectedIndex === 0) {
                                    this.createNewItem();
                                    return;
                                }

                                if (this.selectedIndex >= 0 && this.searchResults[this.selectedIndex]) {
                                    this.addItemFast(this.searchResults[this.selectedIndex]);
                                }
                            },

                            // Helper to safely focus and calculate
                            waitForRowAndCalculate(index) {
                                let attempts = 0;
                                const check = () => {
                                    const quantityField = document.getElementById('quantity-' + index);
                                    if (quantityField) {
                                        quantityField.focus();
                                        quantityField.select();
                                        
                                        // Force row calculation to ensure sub_value is set
                                        if (window.calculateRowTotal) {
                                            window.calculateRowTotal(index);
                                        }

                                        // Ensure standard IDs are used for calculation
                                        if (window.calculateInvoiceTotals) {
                                            window.calculateInvoiceTotals();
                                        }
                                    } else if (attempts < 20) { // Try for ~1 second
                                        attempts++;
                                        setTimeout(check, 50);
                                    }
                                };
                                check();
                            },

                            // ✅ إضافة سريعة
                            addItemFast(item) {
                                // إخفاء البحث فوراً
                                this.clearSearch();

                                // ✅ Call Livewire method
                                @this.call('addItemFromSearchFast', item.id).then((result) => {
                                    if (result && result.success) {
                                        this.waitForRowAndCalculate(result.index);
                                    }
                                }).catch(error => {
                                    console.error('Error adding item:', error);
                                });
                            },

                            createNewItem() {
                                @this.call('createNewItem', this.searchTerm).then((result) => {
                                    if (result && result.success) {
                                        this.clearSearch();
                                        this.waitForRowAndCalculate(result.index);
                                    }
                                });
                            },

                            clearSearch() {
                                this.searchTerm = '';
                                this.searchResults = [];
                                this.showResults = false;
                                this.selectedIndex = -1;
                            }
                        }" style="position: relative;">

                            <input type="text" x-model="searchTerm" @input.debounce.50ms="search()"
                                @keydown.arrow-down.prevent="selectNext()" @keydown.arrow-up.prevent="selectPrevious()"
                                @keydown.enter.prevent="addSelectedItem()" @keydown.escape="clearSearch()"
                                class="form-control frst" id="search-input"
                                placeholder="{{ __('Search by name, code, or barcode...') }}" autocomplete="off">

                            {{-- Loading spinner --}}
                            <div x-show="loading" x-cloak
                                style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);">
                                <i class="fas fa-spinner fa-spin text-primary"></i>
                            </div>

                            {{-- نتائج البحث --}}
                            <div x-show="showResults && searchResults.length > 0" x-cloak
                                class="list-group position-absolute w-100"
                                style="z-index: 999; max-height: 300px; overflow-y: auto; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #ddd;"
                                @click.away="showResults = false">

                                <template x-for="(item, index) in searchResults" :key="item.id">
                                    <li :class="'list-group-item list-group-item-action search-item-' + index + (selectedIndex ===
                                        index ? ' active' : '')"
                                        @click="addItemFast(item)" @mouseenter="selectedIndex = index"
                                        style="cursor: pointer; transition: all 0.2s;">
                                        <strong x-text="item.name"></strong>
                                        <small class="text-muted" x-show="item.code"> - <span
                                                x-text="item.code"></span></small>
                                    </li>
                                </template>
                            </div>

                            {{-- زر إضافة صنف جديد --}}
                            <div x-show="showResults && searchResults.length === 0 && searchTerm.length > 0" x-cloak
                                class="list-group position-absolute w-100"
                                style="z-index: 999; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #ddd;">
                                <li :class="'list-group-item list-group-item-action list-group-item-success search-item-0' + (
                                    selectedIndex === 0 ? ' active' : '')"
                                    @click="createNewItem()" @mouseenter="selectedIndex = 0"
                                    style="cursor: pointer; transition: all 0.2s;">
                                    <i class="fas fa-plus"></i>
                                    <strong>{{ __('Create new item') }}</strong>: <span x-text="searchTerm"></span>
                                </li>
                            </div>
                        </div>
                    </div>








                    <div class="col-lg-3 mb-3">
                        <label>{{ __('Search by Barcode') }}</label>
                        <input type="text" wire:model.live="barcodeTerm" class="form-control" id="barcode-search"
                            placeholder="{{ __('Enter Barcode ') }}" autocomplete="off"
                            wire:keydown.enter="addItemByBarcode" />
                        @if (strlen($barcodeTerm) > 0 && $barcodeSearchResults->count())
                            <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                @foreach ($barcodeSearchResults as $index => $item)
                                    <li class="list-group-item list-group-item-action"
                                        wire:click="addItemFromSearchFast({{ $item->id }})">
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
                                <label for="selectedPriceType">{{ __('Select Price Type for Invoice') }}</label>
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
                            <label for="status">{{ __('Invoice Status') }}</label>
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

                @include('components.invoices.invoice-footer')

            </form>
        </section>
    </div>
</div>
@push('scripts')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

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
            Livewire.on('prompt-create-item-from-barcode', (event) => {
                Swal.fire({
                    title: "{{ __('Item not found!') }}",
                    text: `{{ __('Barcode ') }}"${event.barcode}"{{ __(' is not registered. Do you want to create a new item?') }}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('Yes, create it') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                    input: 'text',
                    inputLabel: "{{ __('Please enter the new item name') }}",
                    inputPlaceholder: "{{ __('Type the item name here...') }}",
                    inputValidator: (value) => {
                        if (!value) {
                            return "{{ __('Item name is required!') }}"
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        // استدعاء دالة Livewire لإتمام عملية الإنشاء
                        console.log('Calling createItemFromPrompt with:', result.value, event
                            .barcode);
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

            Livewire.on('success', (data) => {
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




        // document.addEventListener('alpine:init', () => {
        //     Alpine.data('itemSearch', (config) => ({
        //         searchTerm: '',
        //         searchResults: [],
        //         loading: false,
        //         showResults: false,
        //         selectedIndex: -1,

        //         search() {
        //             if (this.searchTerm.length === 0) {
        //                 this.searchResults = [];
        //                 this.showResults = false;
        //                 return;
        //             }

        //             this.loading = true;

        //             fetch(`${config.apiUrl}?term=${encodeURIComponent(this.searchTerm)}&type=${config.type}&branch_id=${config.branchId}`, {
        //                     headers: {
        //                         'Accept': 'application/json',
        //                         'X-CSRF-TOKEN': config.csrfToken,
        //                         'X-Requested-With': 'XMLHttpRequest'
        //                     },
        //                     credentials: 'same-origin'
        //                 })
        //                 .then(response => response.json())
        //                 .then(data => {
        //                     this.searchResults = data;
        //                     this.showResults = true;
        //                     this.selectedIndex = -1;
        //                     this.loading = false;
        //                 })
        //                 .catch(error => {
        //                     console.error('Search error:', error);
        //                     this.loading = false;
        //                 });
        //         },

        //         selectNext() {
        //             if (this.searchResults.length === 0) return;
        //             this.selectedIndex = (this.selectedIndex + 1) % this.searchResults.length;
        //         },

        //         selectPrevious() {
        //             if (this.searchResults.length === 0) return;
        //             this.selectedIndex = this.selectedIndex <= 0 ?
        //                 this.searchResults.length - 1 :
        //                 this.selectedIndex - 1;
        //         },

        //         addSelectedItem() {
        //             if (this.selectedIndex >= 0 && this.searchResults[this.selectedIndex]) {
        //                 this.addItem(this.searchResults[this.selectedIndex].id);
        //             } else if (this.searchResults.length === 0 && this.searchTerm.length > 0) {
        //                 this.createNewItem();
        //             }
        //         },

        //         addItem(itemId) {
        //             // Call Livewire method
        //             @this.call('getItemForInvoice', itemId).then(result => {
        //                 if (result && result.success) {
        //                     // Item added successfully
        //                     this.clearSearch();

        //                     // Recalculate totals client-side
        //                     setTimeout(() => {
        //                         window.calculateInvoiceTotals();
        //                         // Focus on quantity field
        //                         const quantityField = document.getElementById(
        //                             `quantity-${result.index}`);
        //                         if (quantityField) {
        //                             quantityField.focus();
        //                             quantityField.select();
        //                         }
        //                     }, 100);
        //                 } else if (result && result.exists) {
        //                     // Item already exists, focus on it
        //                     this.clearSearch();
        //                     setTimeout(() => {
        //                         const quantityField = document.getElementById(
        //                             `quantity-${result.index}`);
        //                         if (quantityField) {
        //                             quantityField.focus();
        //                             quantityField.select();
        //                         }
        //                     }, 100);
        //                 }
        //             });
        //         },

        //         createNewItem() {
        //             // Call Livewire method for creating new item
        //             @this.call('createNewItem', this.searchTerm);
        //             this.clearSearch();
        //         },

        //         clearSearch() {
        //             this.searchTerm = '';
        //             this.searchResults = [];
        //             this.showResults = false;
        //             this.selectedIndex = -1;
        //         }
        //     }));
        // });
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
                const field = document.getElementById('quantity-' + e.detail.rowIndex);
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
            if (element.disabled) {
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
                `quantity-${rowIndex}`,
                `price-${rowIndex}`,
                `discount-${rowIndex}`,
                `sub_value-${rowIndex}`
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
                `quantity-${nextRowIndex}`,
                `price-${nextRowIndex}`,
                `discount-${nextRowIndex}`,
                `sub_value-${nextRowIndex}`
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
            const quantityFields = document.querySelectorAll('input[id^="quantity-"]');
            for (let field of quantityFields) {
                if (isElementAccessible(field)) {
                    return field;
                }
            }
            return null;
        }


        // دالة للتحقق من وجود عناصر في الجدول
        function hasTableItems() {
            const quantityFields = document.querySelectorAll('input[id^="quantity-"]');
            return quantityFields.length > 0;
        }


        // دالة للعثور على آخر حقل متاح في الجدول
        function findLastAvailableTableField() {
            const allTableFields = [];


            // جمع جميع حقول الجدول
            ['quantity-', 'price-', 'discount-', 'sub_value-'].forEach(function(prefix) {
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
            if (currentId.startsWith('quantity-') || currentId.startsWith('price-') || currentId.startsWith('discount-')) {
                const index = currentId.split('-')[1];
                nextElement = findNextInRow(currentElement, index);
                if (!nextElement) {
                    nextElement = findNextInNextRow(parseInt(index));
                }
                if (!nextElement) {
                    nextElement = findNextFormElement(currentElement);
                }
            } else if (currentId.startsWith('sub_value-')) {
                const index = currentId.split('-')[1];
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
            const tableFields = ['quantity-', 'price-', 'discount-', 'sub_value-'];
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
                    const quantityFields = document.querySelectorAll('input[id^="quantity-"]');
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


                let title = "{{ __('Item not found') }}";
                let text = '';
                let itemCreateUrl = '';


                if (type === 'barcode') {
                    text =
                        `{{ __('The item with the entered barcode was not found. Do you want to add a new item?') }}`;
                    // تمرير الباركود كمعامل في الرابط
                    itemCreateUrl = `{{ route('items.create') }}?barcode=${encodeURIComponent(term)}`;
                } else {
                    text = `{{ __('Item ') }}"${term}"{{ __(' not found. Do you want to add a new item?') }}`;
                    // تمرير اسم الصنف كمعامل في الرابط
                    itemCreateUrl = `{{ route('items.create') }}?name=${encodeURIComponent(term)}`;
                }


                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Yes, add item') }}",
                    cancelButtonText: "{{ __('No') }}",
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
                        $wire.set('barcodeTerm', '');
                    } else if (activeElement && activeElement.hasAttribute('wire:model.live') &&
                        activeElement.getAttribute('wire:model.live') === 'searchTerm') {
                        activeElement.value = '';
                        activeElement.focus();
                        $wire.set('searchTerm', '');
                    }
                }
            });
            // تنقل بالأسهم في نتائج البحث - JavaScript فقط
            let currentSearchIndex = -1;
            const searchInput = document.getElementById('search-input');

            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    const resultsList = document.getElementById('search-results-list');
                    if (!resultsList) {
                        return;
                    }

                    const items = resultsList.querySelectorAll('li');
                    const totalItems = items.length;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        items.forEach(item => item.classList.remove('active'));
                        currentSearchIndex = (currentSearchIndex + 1) % totalItems;
                        items[currentSearchIndex].classList.add('active');
                        items[currentSearchIndex].scrollIntoView({
                            block: 'nearest'
                        });
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        items.forEach(item => item.classList.remove('active'));
                        currentSearchIndex = currentSearchIndex <= 0 ? totalItems - 1 : currentSearchIndex - 1;
                        items[currentSearchIndex].classList.add('active');
                        items[currentSearchIndex].scrollIntoView({
                            block: 'nearest'
                        });
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (currentSearchIndex >= 0 && items[currentSearchIndex]) {
                            const selectedItem = items[currentSearchIndex];
                            const isCreateNew = selectedItem.getAttribute('data-create-new');

                            if (isCreateNew) {
                                @this.call('createNewItem', searchInput.value);
                            } else {
                                const itemId = selectedItem.getAttribute('data-item-id');
                                @this.call('addItemFromSearchFast', parseInt(itemId));
                            }
                            currentSearchIndex = -1;
                        }
                    }
                });

                // إعادة تعيين الفهرس عند الكتابة
                searchInput.addEventListener('input', function() {
                    currentSearchIndex = -1;
                });
            }

            // النقر على النتائج
            document.addEventListener('click', function(e) {
                if (e.target.matches('#search-results-list li') || e.target.closest('#search-results-list li')) {
                    const listItem = e.target.matches('li') ? e.target : e.target.closest('li');
                    const isCreateNew = listItem.getAttribute('data-create-new');

                    if (isCreateNew) {
                        @this.call('createNewItem', searchInput.value);
                    } else {
                        const itemId = listItem.getAttribute('data-item-id');
                        @this.call('addItemFromSearchFast', parseInt(itemId));
                    }
                    currentSearchIndex = -1;
                }
            });


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


        // حسابات الفاتورة - JavaScript Live Client Side
        
        // ✅ تحسين عمل الـ calculateRowTotal
        window.calculateRowTotal = function(index) {
            let quantity = parseFloat(document.getElementById(`quantity-${index}`)?.value);
            if (isNaN(quantity)) quantity = 0;
            
            let price = parseFloat(document.getElementById(`price-${index}`)?.value);
            if (isNaN(price)) price = 0;
            
            let discount = parseFloat(document.getElementById(`discount-${index}`)?.value);
            if (isNaN(discount)) discount = 0;

            let subValue = (quantity * price) - discount;
            subValue = Math.max(0, subValue);

            // Try to find the field with both possible ID formats to be safe
            const subValueField = document.getElementById(`sub_value-${index}`);
            
            if (subValueField) {
                subValueField.value = subValue.toFixed(2);
            }

            // Update Livewire في الخلفية
            @this.set(`invoiceItems.${index}.sub_value`, subValue, false);

            // Recalculate totals
            if (window.calculateInvoiceTotals) {
                window.calculateInvoiceTotals();
            }
        };

        // 2. تحديث الخصم بناءً على النسبة المئوية
        window.updateDiscountFromPercentage = function() {
            const subtotal = getSubtotal();
            const discountPercentage = parseFloat(document.getElementById('discount-percentage')?.value || 0);
            
            let discountValue = 0;
            if (subtotal > 0) {
                discountValue = (subtotal * discountPercentage) / 100;
            }

            // Update Value Input
            const discountValueField = document.getElementById('discount-value');
            if (discountValueField) discountValueField.value = discountValue.toFixed(2);

            // Sync Livewire
            @this.set('discount_percentage', discountPercentage, false);
            @this.set('discount_value', discountValue.toFixed(2), false);

            calculateFinalTotal(false); // don't recalc discount from value again
        };

        // 3. تحديث الخصم بناءً على القيمة
        window.updateDiscountFromValue = function() {
            const subtotal = getSubtotal();
            const discountValue = parseFloat(document.getElementById('discount-value')?.value || 0);

            let discountPercentage = 0;
            if (subtotal > 0 && discountValue > 0) {
                discountPercentage = (discountValue * 100) / subtotal;
            }

            // Update Percentage Input
            // Note: we might want to round it for display, but keep precision for logic?
            // Let's toggle only if needed to avoid jumping cursor.
            // Usually valid to update the percentage field here.
            const discountPercentageField = document.getElementById('discount-percentage');
            if (discountPercentageField) {
                 // Avoid overwriting if user is typing, but here we are in "FromValue"
                 // so user is typing in Value field. Update Percentage field.
                 discountPercentageField.value = discountPercentage.toFixed(2);
            }

            // Sync Livewire
            @this.set('discount_percentage', discountPercentage.toFixed(2), false);
            @this.set('discount_value', discountValue, false);

            calculateFinalTotal(false);
        };

        // 4. تحديث الإضافي بناءً على النسبة
        window.updateAdditionalFromPercentage = function() {
            const subtotal = getSubtotal();
            const additionalPercentage = parseFloat(document.getElementById('additional-percentage')?.value || 0);

            let additionalValue = 0;
            if (subtotal > 0) {
                additionalValue = (subtotal * additionalPercentage) / 100;
            }

            const additionalValueField = document.getElementById('additional-value');
            if (additionalValueField) additionalValueField.value = additionalValue.toFixed(2);

            @this.set('additional_percentage', additionalPercentage, false);
            @this.set('additional_value', additionalValue.toFixed(2), false);

            calculateFinalTotal(false);
        };

        // 5. تحديث الإضافي بناءً على القيمة
        window.updateAdditionalFromValue = function() {
            const subtotal = getSubtotal();
            const additionalValue = parseFloat(document.getElementById('additional-value')?.value || 0);

            let additionalPercentage = 0;
            if (subtotal > 0 && additionalValue > 0) {
                additionalPercentage = (additionalValue * 100) / subtotal;
            }

            const additionalPercentageField = document.getElementById('additional-percentage');
            if (additionalPercentageField) additionalPercentageField.value = additionalPercentage.toFixed(2);

            @this.set('additional_percentage', additionalPercentage.toFixed(2), false);
            @this.set('additional_value', additionalValue, false);

            calculateFinalTotal(false);
        };

        // Helper: Get Subtotal from DOM
        function getSubtotal() {
            let subtotal = 0;
            document.querySelectorAll('input[id^="sub_value-"]').forEach(field => {
                subtotal += parseFloat(field.value || 0);
            });
            return subtotal;
        }

        // 6. الحساب النهائي الموحد (يجمع كل شيء)
        function calculateFinalTotal(shouldRecalculateDerivedValues = true) {
            const subtotal = getSubtotal();
            
            // Update Subtotal Display
            const displaySubtotal = document.getElementById('display-subtotal');
            if (displaySubtotal) displaySubtotal.textContent = Math.round(subtotal).toLocaleString();
            @this.set('subtotal', subtotal.toFixed(2), false);

            // Get current values (derived or input)
            let discountValue = parseFloat(document.getElementById('discount-value')?.value || 0);
            let additionalValue = parseFloat(document.getElementById('additional-value')?.value || 0);

            // If we just changed rows, we usually want to KEEP PERCENTAGE constant and update value.
            if (shouldRecalculateDerivedValues) {
                 const discountPercentage = parseFloat(document.getElementById('discount-percentage')?.value || 0);
                 const additionalPercentage = parseFloat(document.getElementById('additional-percentage')?.value || 0);
                 
                 discountValue = (subtotal * discountPercentage) / 100;
                 additionalValue = (subtotal * additionalPercentage) / 100;

                 // Update DOM values
                 const discValInput = document.getElementById('discount-value');
                 const addValInput = document.getElementById('additional-value');
                 if(discValInput) discValInput.value = discountValue.toFixed(2);
                 if(addValInput) addValInput.value = additionalValue.toFixed(2);

                 // Sync derived values
                 @this.set('discount_value', discountValue.toFixed(2), false);
                 @this.set('additional_value', additionalValue.toFixed(2), false);
            }

            const total = subtotal - discountValue + additionalValue;
            
            // Display Total
            const displayTotal = document.getElementById('display-total');
            if (displayTotal) displayTotal.textContent = Math.round(total).toLocaleString();
            @this.set('total_after_additional', total.toFixed(2), false);

            // Calculate Remaining
            const received = parseFloat(document.getElementById('received-from-client')?.value || 0);
            const remaining = Math.max(total - received, 0);

            const displayReceived = document.getElementById('display-received');
            if (displayReceived) displayReceived.textContent = Math.round(received).toLocaleString();
            
            const displayRemaining = document.getElementById('display-remaining');
            if (displayRemaining) displayRemaining.textContent = Math.round(remaining).toLocaleString();
        }

        // 7. عند تحديث المدفوع
        window.updateReceived = function() {
            calculateFinalTotal(false); 
            // Also sync received value
            const received = document.getElementById('received-from-client')?.value || 0;
            @this.set('received_from_client', received, false);
        }

        // جعل الدوال متاحة عالمياً
        window.calculateRowTotal = calculateRowTotal;
        window.calculateFinalTotal = calculateFinalTotal;
        window.calculateInvoiceTotals = calculateFinalTotal; // Alias for compatibility



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
                    alert("{{ __('Please allow pop-ups in your browser for printing.') }}");
                }
            });

            Livewire.on('focus-field', (event) => {
                setTimeout(() => {
                    const field = document.getElementById(event.field + '-' + event.rowIndex);
                    if (field && isElementAccessible(field)) {
                        field.focus();
                        if (field.select) field.select();
                    }
                }, 100);
            });

            Livewire.on('focus-search-field', () => {
                setTimeout(() => {
                    const searchField = document.querySelector(
                        'input[wire\\:model\\.live="searchTerm"]');
                    if (searchField && isElementAccessible(searchField)) {
                        searchField.focus();
                        if (searchField.select) searchField.select();
                    }
                }, 100);
            });
        });

        // ✅ التنقل الذكي بين حقول الجدول
        // ✅ التنقل الذكي مع تخطي الحقول الـ disabled
        window.moveToNextFieldInRow = function(event) {
            const currentField = event.target;
            const currentRow = parseInt(currentField.dataset.row);
            const currentFieldName = currentField.dataset.field;

            // ترتيب الحقول في الصف
            const fieldOrder = ['quantity', 'unit', 'price', 'discount', 'sub_value'];
            const currentIndex = fieldOrder.indexOf(currentFieldName);

            // ✅ البحث عن الحقل التالي المتاح (غير disabled/readonly)
            let nextField = null;

            // محاولة الانتقال للحقول التالية في نفس الصف
            for (let i = currentIndex + 1; i < fieldOrder.length; i++) {
                const fieldName = fieldOrder[i];
                const field = document.getElementById(`${fieldName}-${currentRow}`);

                if (field && !field.disabled && !field.readOnly && field.offsetParent !== null) {
                    nextField = field;
                    break;
                }
            }

            // إذا وجدنا حقل متاح في نفس الصف
            if (nextField) {
                nextField.focus();
                nextField.select();
                return;
            }

            // ✅ إذا لم يوجد حقل متاح في نفس الصف، نعود لحقل البحث فوراً (حسب طلب العميل)
            const searchField = document.getElementById('search-input');
            if (searchField) {
                searchField.focus();
                searchField.select();
            }
        };



        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const target = e.target;

                    // إذا كان الحقل في الجدول
                    if (target.classList.contains('invoice-field')) {
                        e.preventDefault();
                    }
                }
            });
        });

        // ✅ منع التصرف الافتراضي للـ Enter في جميع الحقول
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const target = e.target;

                    // إذا كان الحقل في الجدول
                    if (target.classList.contains('invoice-field')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
@endpush
