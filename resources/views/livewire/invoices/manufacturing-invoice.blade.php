<div class="container">
    @if ($currentStep === 1)
        <div class="row">
            <div class="col-10">
                <div class="bg-white shadow-lg rounded-lg p-8">
                    <div class="flex items-center">
                        <h1 class="text-3xl font-bold text-gray-800">فاتورة التصنيع</h1>

                        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-lg border border-gray-200 text-sm text-gray-600"
                            dir="rtl">
                            <!-- بيانات الفاتورة -->
                            <div>
                                <p class="fw-bold mb-1">
                                    رقم الفاتورة: <span class="text-primary">{{ $pro_id }}</span>
                                </p>
                                <p class="mb-0">التاريخ: {{ $invoiceDate }}</p>
                            </div>

                            <div class="flex items-center">
                                <button wire:click="distributeCosts"
                                    class="btn btn-primary px-5 py-3 text-lg font-bold">
                                    توزيع التكاليف
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-lg-2">
                                        <label class="form-label" style="font-size: 1em;"> الحساب </label>
                                        <select wire:model="OperatingAccount" class="form-control form-control-sm"
                                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                                            @foreach ($OperatingCenter as $keyOperation => $valueOperation)
                                                <option value="{{ $keyOperation }}">{{ $valueOperation }}</option>
                                            @endforeach
                                        </select>
                                        @error('OperatingAccount')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-lg-2">
                                        <label class="form-label" style="font-size: 1em;"> الموظف </label>
                                        <select wire:model="employee" class="form-control form-control-sm"
                                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                                            @foreach ($employeeList as $keyEmployee => $valueEmployee)
                                                <option value="{{ $keyEmployee }}">{{ $valueEmployee }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="pro_date" class="form-label"
                                            style="font-size: 1em;">{{ __('التاريخ') }}</label>
                                        <input type="date" wire:model="invoiceDate"
                                            class="form-control form-control-sm @error('pro_date') is-invalid @enderror"
                                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                                        @error('pro_date')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>


                                    <div class="col-lg-2">
                                        <label for="pro_date" class="form-label"
                                            style="font-size: 1em;">{{ __(' رقم الفاتورة ') }}</label>
                                        <input type="text" wire:model="pro_id"
                                            class="form-control form-control-sm @error('pro_id') is-invalid @enderror"
                                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;" readonly>
                                        @error('pro_id')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="patchNumber" class="form-label"
                                            style="font-size: 1em;">{{ __('رقم الباتش ') }}</label>
                                        <input type="text" wire:model="patchNumber"
                                            class="form-control form-control-sm "
                                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="description" class="form-label"
                                            style="font-size: 1em;">{{ __('وصف العملية') }}</label>
                                        <input type="text" wire:model="description"
                                            class="form-control form-control-sm"
                                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                                    </div>

                                </div>
                            </div>
                        </div>
                        <hr style=" border: none; border-top: 12px solid #1908da; margin: 0.1rem 0;">

                        <!-- قسم المنتجات المصنعة -->
                        <div class="mb-9 card" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
                            <!-- حقل البحث للمنتجات المصنعة -->
                            <div class="row">
                                <div class="col-lg-3 mb-0" style="position: relative;">
                                    <input type="text" wire:model.live="productSearchTerm" id="product_search"
                                        class="form-control form-control-sm frst" placeholder="ابحث عن منتج..."
                                        autocomplete="off" style="font-size: 1em;"
                                        wire:keydown.arrow-down="handleKeyDownProduct"
                                        wire:keydown.arrow-up="handleKeyUpProduct"
                                        wire:keydown.enter.prevent="handleEnterProduct" />
                                    @if (strlen($productSearchTerm) > 0 && $productSearchResults->count())
                                        <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                            @foreach ($productSearchResults as $index => $item)
                                                <li class="list-group-item list-group-item-action
                                             @if ($productSelectedResultIndex === $index) active @endif"
                                                    wire:click="addProductFromSearch({{ $item->id }})">
                                                    {{ $item->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif(strlen($productSearchTerm) > 0)
                                        <div class="mt-2" style="position: absolute; z-index: 1000; width: 100%;">
                                            <div class="list-group-item text-danger">
                                                لا توجد نتائج لـ "{{ $productSearchTerm }}"
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-lg-2">
                                    <select wire:model="productAccount" class="form-control form-control-sm"
                                        style="font-size: 1em;">
                                        @foreach ($Stors as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @error('productAccount')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 pt-0">
                                @if (empty($selectedProducts))
                                    <div class="text-center py-12">
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد منتجات</h3>
                                        <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة منتج للتصنيع</p>
                                    </div>
                                @else
                                    <div class="space-y-1">
                                        @foreach ($selectedProducts as $index => $product)
                                            <div class="row g-3 align-items-end"
                                                wire:key="product-{{ $product['id'] }}">
                                                <div class="row g-3 align-items-end">

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1 ">المنتج</label>
                                                        <input type="text" value="{{ $product['name'] ?? '' }}"
                                                            style="font-size: 1em;"
                                                            class="form-control form-control-sm" readonly>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1">الكمية</label>
                                                        <input style="font-size: 1em;" type="number"
                                                            id="product_quantity_{{ $index }}"
                                                            wire:model.lazy="selectedProducts.{{ $index }}.quantity"
                                                            wire:blur="updateProductTotal({{ $index }}, 'quantity')"
                                                            min="0.01" step="0.01"
                                                            class="form-control form-control-sm">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1" style="font-size: 1em;">تكلفة
                                                            الوحدة</label>
                                                        <input type="number" style="font-size: 1em;"
                                                            wire:model.lazy="selectedProducts.{{ $index }}.unit_cost"
                                                            wire:blur="updateProductTotal('selectedProducts.{{ $index }}.unit_cost')"
                                                            min="0" step="0.01"
                                                            class="form-control form-control-sm">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1">نسبة التكلفة %</label>
                                                        <input type="number" style="font-size: 1em;"
                                                            wire:model.lazy="selectedProducts.{{ $index }}.cost_percentage"
                                                            min="0" max="100" step="0.01"
                                                            class="form-control form-control-sm">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1"
                                                            style="font-size: 1em;">الإجمالي</label>
                                                        <input type="text" style="font-size: 1em;"
                                                            value="{{ number_format($product['total_cost'], 2) }} جنيه"
                                                            class="form-control form-control-sm bg-gray-100 font-semibold text-green-600"
                                                            readonly>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <button wire:click="removeProduct({{ $index }})"
                                                            style="font-size: 1em;"  class="btn btn-danger btn-icon-square-sm">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr style=" border: none; border-top: 12px solid #1908da; margin: 0.1rem 0;">

                    <div class="mb-8 card" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                        <!-- حقل البحث للمواد الخام -->
                        <div class="row">
                            <div class="col-lg-3 mb-0" style="position: relative;">
                                <input type="text" wire:model.live="rawMaterialSearchTerm"
                                    id="raw_material_search" class="form-control form-control-sm frst"
                                    placeholder="ابحث عن مادة خام..." autocomplete="off" style="font-size: 1em;"
                                    wire:keydown.arrow-down="handleKeyDownRawMaterial"
                                    wire:keydown.arrow-up="handleKeyUpRawMaterial"
                                    wire:keydown.enter.prevent="handleEnterRawMaterial" />
                                @if (strlen($rawMaterialSearchTerm) > 0 && $rawMaterialSearchResults->count())
                                    <ul class="list-group position-absolute w-100" style="z-index: 999;">
                                        @foreach ($rawMaterialSearchResults as $index => $item)
                                            <li class="list-group-item list-group-item-action
                                             @if ($rawMaterialSelectedResultIndex === $index) active @endif"
                                                wire:click="addRawMaterialFromSearch({{ $item->id }})">
                                                {{ $item->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif(strlen($rawMaterialSearchTerm) > 0)
                                    <div class="mt-2" style="position: absolute; z-index: 1000; width: 100%;">
                                        <div class="list-group-item text-danger">
                                            لا توجد نتائج لـ "{{ $rawMaterialSearchTerm }}"
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-2">
                                <select wire:model="rawAccount" class="form-control form-control-sm"
                                    style="font-size: 1em;">
                                    @foreach ($Stors as $keyStore1 => $valueStore1)
                                        <option value="{{ $keyStore1 }}">{{ $valueStore1 }}</option>
                                    @endforeach
                                </select>
                                @error('rawAccount')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 pt-0">
                            @if (empty($selectedRawMaterials))
                                <div class="text-center py-12">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد مواد خام</h3>
                                    <p class="mt-1 text-sm text-gray-500">أضف المواد الخام المستخمة في التصنيع</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($selectedRawMaterials as $index => $material)
                                        <div class="row g-3 align-items-end"
                                            wire:key="raw-material-{{ $material['id'] }}">

                                            <div class="col-md-2">
                                                <label class="form-label mb-1">المادة الخام</label>
                                                <input type="text" value="{{ $material['name'] ?? '' }}"
                                                    style="font-size: 1em;" class="form-control form-control-sm"
                                                    readonly>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label mb-1">الوحدة</label>
                                                <select wire:model="selectedRawMaterials.{{ $index }}.unit_id"
                                                    style="font-size: 1em;" class="form-control form-control-sm">
                                                    <option value="">اختر وحدة</option>
                                                    @foreach ($material['unitsList'] ?? [] as $unit)
                                                        <option value="{{ $unit['id'] }}">
                                                            {{ $unit['name'] }}
                                                            ({{ number_format($unit['available_qty'], 0, '.', '') }}
                                                            قطعة)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label mb-1">الكمية</label>
                                                <input type="number" style="font-size: 1em;"
                                                    id="raw_quantity_{{ $index }}"
                                                    wire:model.lazy="selectedRawMaterials.{{ $index }}.quantity"
                                                    wire:blur="updateRawMaterialTotal('selectedRawMaterials.{{ $index }}.quantity')"
                                                    min="0.01" step="0.01"
                                                    class="form-control form-control-sm">
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label mb-1">سعر الوحدة</label>
                                                <input type="number" style="font-size: 1em;"
                                                    id="raw_unit_cost_{{ $index }}"
                                                    wire:model.lazy="selectedRawMaterials.{{ $index }}.unit_cost"
                                                    wire:blur="updateRawMaterialTotal('selectedRawMaterials.{{ $index }}.unit_cost')"
                                                    min="0" step="0.01"
                                                    class="form-control form-control-sm">
                                            </div>

                                            {{-- <div class="col-md-2">
                                                <label class="form-label mb-1">المتاح</label>
                                                <input type="text" style="font-size: 1em;"
                                                    value="{{ $material['available_quantity'] ?? 0 }}"
                                                    class="form-control form-control-sm bg-blue-50 text-blue-600"
                                                    readonly>
                                            </div> --}}

                                            <div class="col-md-1">
                                                <label class="form-label mb-1">الإجمالي</label>
                                                <input type="text" style="font-size: 1em;"
                                                    value="{{ number_format($material['total_cost'], 2) }} جنيه"
                                                    class="form-control form-control-sm bg-gray-100 text-green-600"
                                                    readonly>
                                            </div>

                                            <div class="col-md-1">
                                                <button wire:click="removeRawMaterial({{ $index }})"
                                                    class="btn btn-danger btn-icon-square-sm">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-2">
                <div class="mb-4 card">
                    <div class="card-body p-3">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="h2"> المصاريف الإضافية</h3>
                            <button wire:click="addExpense" class="btn btn-primary btn-sm py-1 px-2 text-sm">
                                + إضافة
                            </button>
                        </div>

                        <div class="bg-gray-50 rounded p-2">
                            @if (count($additionalExpenses) === 0)
                                <div class="text-center py-4 text-xs">
                                    <p class="text-gray-500">لا توجد مصاريف إضافية</p>
                                </div>
                            @else
                                <div class="space-y-2" style="max-height: 200px; overflow-y: auto;">
                                    @foreach ($additionalExpenses as $index => $expense)
                                        <div class="bg-white p-2 rounded shadow-xs border border-gray-200 text-xs">
                                            <div class="flex items-end gap-1">
                                                <div class="flex-1">
                                                    <label class="block text-gray-600 mb-1">الوصف</label>
                                                    <input type="text"
                                                        wire:model="additionalExpenses.{{ $index }}.description"
                                                        placeholder="وصف المصروف"
                                                        class="form-control form-control-xs w-full p-1 text-xs @error('additionalExpenses.' . $index . '.description') border-red-500 @enderror">
                                                    @error('additionalExpenses.' . $index . '.description')
                                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="w-20">
                                                    <label class="block text-gray-600 mb-1">المبلغ</label>
                                                    <input type="number"
                                                        wire:model.live="additionalExpenses.{{ $index }}.amount"
                                                        min="0" step="0.01" placeholder="0.00"
                                                        class="form-control form-control-xs w-full p-1 text-xs @error('additionalExpenses.' . $index . '.amount') border-red-500 @enderror">
                                                    @error('additionalExpenses.' . $index . '.amount')
                                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="flex items-end pb-1">
                                                    <button wire:click="removeExpense({{ $index }})"
                                                         class="btn btn-danger btn-icon-square-sm p-1 text-xs">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ملخص التكاليف -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded p-3 mb-4 overflow-hidden">
                    <h3 class="h2"> التكاليف</h3>
                    <div class="space-y-2">

                        <div class="bg-white p-2 rounded shadow-xs">
                            <div class="text-xs text-gray-600">المواد الخام</div>
                            <div class="text-sm font-bold text-blue-600">
                                {{ number_format($totalRawMaterialsCost) }} ج
                            </div>
                        </div>
                        <div class="bg-white p-2 rounded shadow-xs">
                            <div class="text-xs text-gray-600">المصاريف</div>
                            <div class="text-sm font-bold text-purple-600">
                                {{ number_format($totalAdditionalExpenses) }} ج
                            </div>
                        </div>
                        <div class="bg-white p-2 rounded shadow-xs">
                            <div class="text-xs text-gray-600">الإجمالي</div>
                            <div class="text-sm font-bold text-green-600">
                                {{ number_format($totalManufacturingCost) }} ج
                            </div>
                        </div>

                    </div>
                </div>

                <div class="text-center my-5 d-flex flex-column align-items-center gap-3">
                    <button wire:click="saveInvoice"
                        class="btn btn-primary btn-lg px-2 py2 mb-2 flex items-center gap-2"
                        style="font-size: 1.5em;">
                        حفظ الفاتورة
                    </button>

                    <button wire:click="cancelInvoice" type="button"
                        class="btn btn-secondary btn-lg px-2 py-2 flex items-center gap-2" style="font-size: 1.5em;">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupKeyboardNavigation() {
                // حقل الكمية - الانتقال لحقل السعر
                document.querySelectorAll('input[id^="product_quantity_"]').forEach(function(field) {
                    field.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const searchField = document.getElementById('product_search');
                            if (searchField) {
                                searchField.focus();
                                searchField.value = '';
                            }
                        }
                    });
                });
                // حقل الكمية - الانتقال لحقل السعر
                document.querySelectorAll('input[id^="raw_quantity_"]').forEach(function(field) {
                    field.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const index = this.id.split('_')[2];
                            const nextField = document.getElementById('raw_unit_cost_' + index);
                            if (nextField) {
                                nextField.focus();
                                nextField.select();
                            }
                        }
                    });
                });
                // حقل السعر - الانتقال لحقل البحث
                document.querySelectorAll('input[id^="raw_unit_cost_"]').forEach(function(field) {
                    field.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            // العودة لحقل البحث لإضافة مادة خام جديدة
                            const searchField = document.getElementById('raw_material_search');
                            if (searchField) {
                                searchField.focus();
                                searchField.value = '';
                            }
                        }
                    });
                });
            }
            // Initialize on page load
            setupKeyboardNavigation();
            document.addEventListener('livewire:init', () => {
                // التركيز على حقل كمية المنتج
                Livewire.on('focusProductQuantity', (index) => {
                    setTimeout(() => {
                        const field = document.getElementById(`product_quantity_${index}`);
                        if (field) {
                            field.focus();
                            field.select();
                        }
                    }, 100);
                });
                // التركيز على حقل كمية المادة الخام
                Livewire.on('focusRawMaterialQuantity', (index) => {
                    setTimeout(() => {
                        const field = document.getElementById(`raw_quantity_${index}`);
                        if (field) {
                            field.focus();
                            field.select();
                        }
                    }, 100);
                });
                // Re-initialize navigation after Livewire updates
                Livewire.hook('morph.updated', () => {
                    setTimeout(setupKeyboardNavigation, 50);
                });
            });
            // Global focus functions (can be called from anywhere)
            window.focusProductSearch = function() {
                const field = document.getElementById('product_search');
                if (field) {
                    field.focus();
                    field.value = '';
                }
            };
            window.focusRawMaterialSearch = function() {
                const field = document.getElementById('raw_material_search');
                if (field) {
                    field.focus();
                    field.value = '';
                }
            };
            window.focusQuantityField = function(section, index) {
                setTimeout(() => {
                    const field = document.getElementById(`${section}_quantity_${index}`);
                    if (field) {
                        field.focus();
                        field.select();
                    }
                }, 200);
            };
        });
    </script>
@endpush
