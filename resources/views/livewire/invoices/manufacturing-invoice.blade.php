<div class="container">
    @if ($currentStep === 1)
        <div class="row">
            <div class="col-12  ">
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

                                <button wire:click="openSaveTemplateModal"
                                    class="btn btn-info px-5 py-3 text-lg font-bold">
                                    حفظ كنموذج <i class="fas fa-save"></i>
                                </button>

                                <!-- زر اختيار نموذج -->
                                <button wire:click="openLoadTemplateModal"
                                    class="btn btn-warning px-5 py-3 text-lg font-bold">
                                    اختيار نموذج <i class="fas fa-folder-open"></i>
                                </button>

                                <button wire:click="adjustCostsByPercentage"
                                    class="btn btn-primary px-5 py-3 text-lg font-bold">
                                    توزيع التكاليف <i class="fas fa-balance-scale"></i>
                                </button>
                                <button wire:click="saveInvoice" class="btn btn-success px-5 py-3 text-lg font-bold">
                                    حفظ الفاتورة <i class="fas fa-save"></i>
                                </button>

                                <button wire:click="cancelInvoice" type="button"
                                    class="btn btn-danger px-5 py-3 text-lg font-bold">
                                    إلغاء <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>

                        <!-- مودال حفظ النموذج -->
                        @if ($showSaveTemplateModal)
                            <div class="modal fade show" style="display: block;">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">حفظ كنموذج تصنيع</h5>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>اسم النموذج</label>
                                                <input type="text" wire:model="templateName" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button wire:click="saveAsTemplate" class="btn btn-primary">حفظ</button>
                                            <button wire:click="closeSaveTemplateModal"
                                                class="btn btn-secondary">إلغاء</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- مودال تحميل النموذج -->
                        @if ($showLoadTemplateModal)
                            <div class="modal fade show" style="display: block;">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">اختيار نموذج تصنيع</h5>
                                        </div>
                                        <div class="modal-body">
                                            <select wire:model="selectedTemplate" class="form-control">
                                                <option value="">اختر نموذج</option>
                                                @foreach ($templates as $template)
                                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button wire:click="loadTemplate" class="btn btn-primary">تحميل</button>
                                            <button wire:click="closeLoadTemplateModal"
                                                class="btn btn-secondary">إلغاء</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                                <div class="col-lg-3 mb-0" style="position: relative; z-index: 999;">
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

                            <div class="bg-light rounded-3 p-3 mx-3 mb-3">
                                @if (empty($selectedProducts))
                                    <div class="text-center py-5">
                                        <i class="fas fa-box-open text-muted mb-3" style="font-size: 2rem;"></i>
                                        <h5 class="text-muted">لا توجد منتجات</h5>
                                        <p class="text-muted small">أضف المنتجات المستخدمة في التصنيع</p>
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-light">
                                                <tr class="text-center">
                                                    <th style="width: 20%">المنتج</th>
                                                    <th style="width: 15%">الكمية</th>
                                                    <th style="width: 15%">تكلفة الوحدة</th>
                                                    <th style="width: 15%">نسبة التكلفة %</th>
                                                    <th style="width: 15%">الإجمالي</th>
                                                    <th style="width: 10%">إجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody id="products_table_body">
                                                @foreach ($selectedProducts as $index => $product)
                                                    <tr wire:key="product-{{ $product['id'] ?? 'index-' . $index }}">
                                                        <td>
                                                            <input type="text"
                                                                value="{{ $product['name'] ?? '' }}"
                                                                class="form-control form-control-sm bg-light" readonly
                                                                style="padding:2px;height:30px;font-size: 0.9em;">
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                id="product_quantity_{{ $index }}"
                                                                wire:model.lazy="selectedProducts.{{ $index }}.quantity"
                                                                wire:blur="updateProductTotal({{ $index }}, 'quantity')"
                                                                min="0.01" step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="padding:2px;height:30px;font-size: 0.9em;"
                                                                placeholder="الكمية">
                                                        </td>

                                                        <td>
                                                            <input type="number"
                                                                id="product_unit_cost_{{ $index }}"
                                                                wire:model.lazy="selectedProducts.{{ $index }}.unit_cost"
                                                                min="0" step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="padding:2px;height:30px;font-size: 0.9em;"
                                                                placeholder="تكلفة الوحدة"
                                                                title="سيتم تعديل سعر الشراء المتوسط للصنف">

                                                            @if (isset($product['old_unit_cost']) && $product['unit_cost'] != $product['old_unit_cost'])
                                                                <small class="text-warning d-block">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                    سيتم تحديث المتوسط من
                                                                    {{ number_format($product['old_unit_cost'], 2) }}
                                                                    إلى {{ number_format($product['unit_cost'], 2) }}
                                                                </small>
                                                            @endif
                                                        </td>

                                                        <td>
                                                            <input type="number"
                                                                id="product_cost_percentage_{{ $index }}"
                                                                wire:model.lazy="selectedProducts.{{ $index }}.cost_percentage"
                                                                min="0" max="100" step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="padding:2px;height:30px;font-size: 0.9em;"
                                                                placeholder="نسبة التكلفة">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                value="{{ number_format($product['total_cost'] ?? 0, 2) }} جنيه"
                                                                class="form-control form-control-sm bg-opacity-10 fw-bold text-green-600"
                                                                readonly
                                                                style="padding:2px;height:30px;font-size: 0.9em;">
                                                        </td>
                                                        <td class="text-center">
                                                            <button wire:click="removeProduct({{ $index }})"
                                                                class="btn btn-danger btn-sm"
                                                                style="height:30px;padding:2px 8px;">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr style=" border: none; border-top: 12px solid #1908da; margin: 0.1rem 0;">

                    <div class="container-fluid">
                        <div class="row">
                            <div class="chat-box-left col-12"
                                style="width: 100% !important; max-width: 100% !important; height: 300px;">

                                <!-- إضافة متغير لحفظ التاب النشط -->
                                <input type="hidden" wire:model="activeTab"
                                    value="{{ $activeTab ?? 'general_chat' }}">

                                <ul class="nav nav-pills mb-3 d-flex justify-content-center gap-2" id="pills-tab"
                                    role="tablist" style="font-size: 0.8rem;">
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-2 {{ ($activeTab ?? 'general_chat') == 'general_chat' ? 'active' : '' }}"
                                            id="general_chat_tab" data-bs-toggle="pill" href="#general_chat"
                                            onclick="setActiveTab('general_chat')"
                                            wire:click="$set('activeTab', 'general_chat')">
                                            المواد الخام
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-2 {{ ($activeTab ?? 'general_chat') == 'group_chat' ? 'active' : '' }}"
                                            id="group_chat_tab" data-bs-toggle="pill" href="#group_chat"
                                            onclick="setActiveTab('group_chat')"
                                            wire:click="$set('activeTab', 'group_chat')">
                                            المصروفات
                                        </a>
                                    </li>
                                </ul>

                                <div class="chat-list" data-simplebar="init">
                                    <div class="simplebar-wrapper" style="margin: 0px;">
                                        <div class="simplebar-mask">
                                            <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                                <div class="simplebar-content-wrapper"
                                                    style="height: 100%; overflow: hidden;">
                                                    <div class="simplebar-content" style="padding: 0px;">
                                                        <div class="tab-content" id="pills-tabContent">

                                                            <!-- تاب المواد الخام -->
                                                            <div class="tab-pane fade {{ ($activeTab ?? 'general_chat') == 'general_chat' ? 'active show' : '' }}"
                                                                id="general_chat">
                                                                <div class="mb-8 card"
                                                                    style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                                                                    <!-- حقل البحث للمواد الخام -->
                                                                    <div class="row p-3">
                                                                        <div class="col-lg-3 mb-2"
                                                                            style="position: relative;">
                                                                            <input type="text"
                                                                                wire:model.live="rawMaterialSearchTerm"
                                                                                id="raw_material_search"
                                                                                class="form-control form-control-sm frst"
                                                                                placeholder="ابحث عن مادة خام..."
                                                                                autocomplete="off"
                                                                                style="font-size: 1em;"
                                                                                wire:keydown.arrow-down="handleKeyDownRawMaterial"
                                                                                wire:keydown.arrow-up="handleKeyUpRawMaterial"
                                                                                wire:keydown.enter.prevent="handleEnterRawMaterial" />

                                                                            @if (strlen($rawMaterialSearchTerm) > 0 && !is_null($rawMaterialSearchResults) && $rawMaterialSearchResults->count())
                                                                                <ul class="list-group position-absolute w-100"
                                                                                    style="z-index: 999;">
                                                                                    @foreach ($rawMaterialSearchResults as $index => $item)
                                                                                        <li class="list-group-item list-group-item-action
                                                                        @if ($rawMaterialSelectedResultIndex === $index) active @endif"
                                                                                            wire:click="addRawMaterialFromSearch({{ $item->id }})">
                                                                                            {{ $item->name }}
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            @elseif (strlen($rawMaterialSearchTerm) > 0)
                                                                                <div class="mt-2"
                                                                                    style="position: absolute; z-index: 1000; width: 100%;">
                                                                                    <div
                                                                                        class="list-group-item text-danger">
                                                                                        لا توجد نتائج لـ
                                                                                        "{{ $rawMaterialSearchTerm }}"
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>

                                                                        <div class="col-lg-2 mb-2">
                                                                            <select wire:model="rawAccount"
                                                                                class="form-control form-control-sm"
                                                                                style="font-size: 1em;">
                                                                                @foreach ($Stors as $keyStore1 => $valueStore1)
                                                                                    <option
                                                                                        value="{{ $keyStore1 }}">
                                                                                        {{ $valueStore1 }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('rawAccount')
                                                                                <span
                                                                                    class="text-danger small">{{ $message }}</span>
                                                                            @enderror
                                                                        </div>
                                                                    </div>

                                                                    <div class="bg-light rounded-3 p-3 mx-3 mb-3">
                                                                        @if (empty($selectedRawMaterials))
                                                                            <div class="text-center py-5">
                                                                                <i class="fas fa-box-open text-muted mb-3"
                                                                                    style="font-size: 2rem;"></i>
                                                                                <h5 class="text-muted">لا توجد مواد خام
                                                                                </h5>
                                                                                <p class="text-muted small">أضف المواد
                                                                                    الخام المستخدمة في التصنيع</p>
                                                                            </div>
                                                                        @else
                                                                            <div class="space-y-3">
                                                                                <!-- جدول المواد الخام -->
                                                                                <table
                                                                                    class="table table-bordered table-sm">
                                                                                    <thead class="table-light">
                                                                                        <tr class="text-center">
                                                                                            <th style="width: 20%">
                                                                                                المادة الخام
                                                                                            </th>
                                                                                            <th style="width: 15%">
                                                                                                الوحدة</th>
                                                                                            <th style="width: 15%">
                                                                                                الكمية</th>
                                                                                            <th style="width: 15%">
                                                                                                سعر الوحدة
                                                                                            </th>
                                                                                            <th style="width: 15%">
                                                                                                الإجمالي
                                                                                            </th>
                                                                                            <th style="width: 10%">
                                                                                                إجراءات</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody
                                                                                        id="raw_materials_table_body">
                                                                                        @foreach ($selectedRawMaterials as $index => $material)
                                                                                            <tr
                                                                                                wire:key="raw-material-{{ $material['id'] ?? 'index-' . $index }}">
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        value="{{ $material['name'] ?? '' }}"
                                                                                                        class="form-control form-control-sm bg-light"
                                                                                                        readonly
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;">
                                                                                                </td>
                                                                                                <td>
                                                                                                    <select
                                                                                                        wire:model="selectedRawMaterials.{{ $index }}.unit_id"
                                                                                                        class="form-control form-control-sm unit-select"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        data-item-id="{{ $material['id'] ?? '' }}">
                                                                                                        {{-- <option
                                                                                                            value="">
                                                                                                            اختر
                                                                                                            وحدة
                                                                                                        </option> --}}
                                                                                                        @foreach ($material['unitsList'] ?? [] as $unit)
                                                                                                            <option
                                                                                                                value="{{ $unit['id'] }}">
                                                                                                                {{ $unit['name'] }}
                                                                                                                ({{ number_format($unit['available_qty'], 0, '.', '') }}
                                                                                                                قطعة)
                                                                                                            </option>
                                                                                                        @endforeach
                                                                                                    </select>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="number"
                                                                                                        id="raw_quantity_{{ $index }}"
                                                                                                        wire:model.lazy="selectedRawMaterials.{{ $index }}.quantity"
                                                                                                        wire:blur="updateRawMaterialTotal('selectedRawMaterials.{{ $index }}.quantity')"
                                                                                                        min="0.01"
                                                                                                        step="0.01"
                                                                                                        class="form-control form-control-sm"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        placeholder="الكمية">
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="number"
                                                                                                        id="raw_unit_cost_{{ $index }}"
                                                                                                        wire:model.lazy="selectedRawMaterials.{{ $index }}.unit_cost"
                                                                                                        wire:blur="updateRawMaterialTotal('selectedRawMaterials.{{ $index }}.unit_cost')"
                                                                                                        min="0"
                                                                                                        step="0.01"
                                                                                                        class="form-control form-control-sm cost-input"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        placeholder="سعر الوحدة">
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        value="{{ number_format($material['total_cost'] ?? 0, 2) }} "
                                                                                                        class="form-control form-control-sm  bg-opacity-10  fw-bold"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        readonly>
                                                                                                </td>
                                                                                                <td
                                                                                                    class="text-center">
                                                                                                    <button
                                                                                                        wire:click="removeRawMaterial({{ $index }})"
                                                                                                        class="btn btn-danger btn-sm"
                                                                                                        style="height:30px;padding:2px 8px;">
                                                                                                        <i
                                                                                                            class="fa fa-trash"></i>
                                                                                                    </button>
                                                                                                </td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                                {{-- </div>
                                                                                    </div> --}}
                                                                                {{-- @endforeach --}}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div><!--end general chat-->

                                                            <!-- تاب المصروفات -->
                                                            <div class="tab-pane fade {{ ($activeTab ?? 'general_chat') == 'group_chat' ? 'active show' : '' }}"
                                                                id="group_chat">
                                                                <div class="col-12">
                                                                    <div class="card h-100">
                                                                        <div
                                                                            class="card-header  bg-opacity-10 border-0">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center">

                                                                                <button wire:click="addExpense"
                                                                                    class="btn btn-primary btn-sm">
                                                                                    إضافة مصروف
                                                                                </button>
                                                                            </div>
                                                                        </div>

                                                                        <div class="bg-light rounded-3 p-3 mx-3 mb-3"
                                                                            style="max-height: 190px; overflow-y: auto; overflow-x: hidden;">
                                                                            @if (count($additionalExpenses) === 0)
                                                                                <div class="text-center py-5">
                                                                                    <i class="fas fa-money-bill-wave text-muted mb-3"
                                                                                        style="font-size: 2rem;"></i>
                                                                                    <h5 class="text-muted">لا توجد
                                                                                        مصاريف إضافية</h5>
                                                                                    <p class="text-muted small">أضف
                                                                                        المصاريف الإضافية للتصنيع</p>
                                                                                </div>
                                                                            @else
                                                                                <div class="row">
                                                                                    {{-- الجزء الخاص بإدخال المصروفات --}}
                                                                                    <div class="col-md-8">
                                                                                        <table
                                                                                            class="table table-bordered table-sm">
                                                                                            <thead>
                                                                                                <tr>
                                                                                                    <th>المبلغ</th>
                                                                                                    <th>الحساب</th>
                                                                                                    <th>الوصف</th>
                                                                                                    <th>حذف</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody
                                                                                                id="additional_expenses_table_body">
                                                                                                @foreach ($additionalExpenses as $index => $expense)
                                                                                                    <tr>
                                                                                                        <td>
                                                                                                            <input
                                                                                                                type="number"
                                                                                                                wire:model.live.debounce.300="additionalExpenses.{{ $index }}.amount"
                                                                                                                min="0"
                                                                                                                step="0.01"
                                                                                                                placeholder="0.00"
                                                                                                                class="form-control form-control-sm @error('additionalExpenses.' . $index . '.amount') is-invalid @enderror"
                                                                                                                style="padding:2px;height:30px;">
                                                                                                            @error('additionalExpenses.'
                                                                                                                . $index .
                                                                                                                '.amount')
                                                                                                                <div class="invalid-feedback"
                                                                                                                    style="font-size: 0.8em;">
                                                                                                                    {{ $message }}
                                                                                                                </div>
                                                                                                            @enderror
                                                                                                        </td>
                                                                                                        <td>
                                                                                                            <select
                                                                                                                wire:model="additionalExpenses.{{ $index }}.account_id"
                                                                                                                class="form-control form-control-sm"
                                                                                                                style="padding:2px;height:30px;">
                                                                                                                @foreach ($expenseAccountList as $keyExpense => $valueExpense)
                                                                                                                    <option
                                                                                                                        value="{{ $keyExpense }}">
                                                                                                                        {{ $valueExpense }}
                                                                                                                    </option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        </td>
                                                                                                        <td>
                                                                                                            <input
                                                                                                                type="text"
                                                                                                                wire:model="additionalExpenses.{{ $index }}.description"
                                                                                                                placeholder="وصف المصروف"
                                                                                                                class="form-control form-control-sm @error('additionalExpenses.' . $index . '.description') is-invalid @enderror"
                                                                                                                style="padding:2px;height:30px;">
                                                                                                            @error('additionalExpenses.'
                                                                                                                . $index .
                                                                                                                '.description')
                                                                                                                <div class="invalid-feedback"
                                                                                                                    style="font-size: 0.8em;">
                                                                                                                    {{ $message }}
                                                                                                                </div>
                                                                                                            @enderror
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="text-center">
                                                                                                            <button
                                                                                                                wire:click="removeExpense({{ $index }})"
                                                                                                                class="btn btn-danger btn-sm"
                                                                                                                style="height:30px;padding:2px 8px;">
                                                                                                                <i
                                                                                                                    class="fa fa-trash"></i>
                                                                                                            </button>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>

                                                                                    {{-- الجزء الخاص بإجمالي المصروفات --}}
                                                                                    <div class="col-md-4">
                                                                                        <div class="card p-3 bg-light">
                                                                                            <h6 class="mb-3 fw-bold">
                                                                                                إجمالي المصروفات
                                                                                                الإضافية</h6>
                                                                                            <p
                                                                                                class="fs-5 text-success">
                                                                                                {{ number_format(collect($additionalExpenses)->sum('amount')) }}
                                                                                                جنيه
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div><!--end group chat-->

                                                        </div><!--end tab-content-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-5">
                            <div class="row gx-2 align-items-end">
                                <div class="col-4">
                                    <label class="form-label small text-gray-600">المواد الخام</label>
                                    <input type="text"
                                        class="form-control form-control-sm text-blue-600 fw-bold py-1 px-2"
                                        style="font-size: 0.75rem;"
                                        value="{{ number_format($totalRawMaterialsCost) }} ج" readonly>
                                </div>

                                <div class="col-4">
                                    <label class="form-label small text-gray-600">المصاريف</label>
                                    <input type="text"
                                        class="form-control form-control-sm text-purple-600 fw-bold py-1 px-2"
                                        style="font-size: 0.75rem;"
                                        value=" {{ number_format(collect($additionalExpenses)->sum('amount')) }} ج"
                                        readonly>
                                </div>

                                <div class="col-4">
                                    <label class="form-label small text-gray-600">الإجمالي</label>
                                    <input type="text"
                                        class="form-control form-control-sm text-success fw-bold py-1 px-2"
                                        style="font-size: 0.75rem;"
                                        value="{{ number_format($totalManufacturingCost) }} ج" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-2">
                        </div>

                        <div class="col-5">
                            <div class="row gx-2 align-items-end">
                                <div class="col-4">
                                    <label class="form-label small text-gray-600">الانتاج التام</label>
                                    <input type="text"
                                        class="form-control form-control-sm text-blue-600 fw-bold py-1 px-2"
                                        style="font-size: 0.75rem;" value="{{ number_format($totalProductsCost) }} ج"
                                        readonly>
                                </div>

                                <div class="col-4">
                                    <label class="form-label small text-gray-600">التكلفه المعياريه</label>
                                    <input type="text"
                                        class="form-control form-control-sm text-purple-600 fw-bold py-1 px-2"
                                        style="font-size: 0.75rem;" {{-- value="{{ number_format($totalAdditionalExpenses) }} ج" readonly --}}>
                                </div>

                                <div class="col-4">
                                    <label class="form-label small text-gray-600">فرق التكلفه</label>
                                    <input type="text" class="form-control form-control-sm  fw-bold py-1 px-2"
                                        style="font-size: 0.75rem;" {{-- value="{{ number_format($totalManufacturingCost) }} ج" readonly --}}>
                                </div>
                            </div>
                        </div>
                    </div>
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

            //alerts
            document.addEventListener('livewire:init', () => {
                Livewire.on('success-swal', (data) => {
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                    }).then((result) => {
                        location.reload();
                    });
                });
            })

            document.addEventListener('livewire:init', () => {
                console.log('Livewire initialized');
                Livewire.on('error-swal', (data) => {
                    console.log('Received error-swal event:', data);
                    // استخراج الكائن الأول من المصفوفة
                    const swalData = Array.isArray(data) ? data[0] : data;
                    if (swalData && swalData.title && swalData.text) {
                        Swal.fire({
                            title: swalData.title,
                            text: swalData.text,
                            icon: swalData.icon || 'error',
                        });
                    } else {
                        console.error('Invalid data received for error-swal:', data);
                        Swal.fire({
                            title: 'خطأ غير معروف',
                            text: 'حدث خطأ أثناء معالجة البيانات',
                            icon: 'error',
                        });
                    }
                });
            });


            // Livewire.on('form-validation-error', errors => {
            //     let messages = errors.join('\n');
            //     Swal.fire({
            //         icon: 'error',
            //         title: 'خطأ في البيانات',
            //         text: messages,
            //     });
            // });
        });
    </script>
@endpush
