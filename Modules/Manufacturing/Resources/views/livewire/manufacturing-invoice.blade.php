<div class="container-fluid" x-data="manufacturingCalculator()" x-init="initFromLivewire()">
    @if ($currentStep === 1)
        <div>
        <!-- Fixed Header -->
        <div class="card border-0 shadow-sm mb-3" style="position: sticky; top: 0; z-index: 1000; background: white;">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-3 flex-wrap">
                            <span>{{ __('Manufacturing Invoice') }}</span>
                            <span class="text-muted small fw-normal">|</span>
                            <span class="text-muted small">
                                <span class="fw-semibold">{{ __('Invoice Number') }}:</span>
                                <span class="text-primary fw-bold">{{ $pro_id }}</span>
                            </span>
                            <span class="text-muted small fw-normal">|</span>
                            <span class="text-muted small">
                                <span class="fw-semibold">{{ __('Date') }}:</span>
                                <span>{{ $invoiceDate }}</span>
                            </span>
                        </h1>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="min-w-200">
                            <x-branches::branch-select :branches="$branches" model="branch_id" />
                        </div>

                        {{-- @if (setting('manufacture_enable_template_saving')) --}}
                            <button wire:click="openSaveTemplateModal" 
                                class="btn btn-primary btn-sm"
                                title="{{ __('Save Template') }}">
                                <i class="fas fa-save"></i>
                            </button>
                            <button type="button" wire:click="openLoadTemplateModal" 
                                class="btn btn-primary btn-sm"
                                wire:loading.attr="disabled"
                                title="{{ __('Load Template') }}">
                                <span wire:loading.remove wire:target="openLoadTemplateModal">
                                    <i class="fas fa-folder-open"></i>
                                </span>
                                <span wire:loading wire:target="openLoadTemplateModal">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </button>
                        {{-- @endif --}}

                        <button wire:click="distributeCostsByPercentage"
                            class="btn btn-primary btn-sm"
                            @if(count($selectedProducts) === 0) disabled @endif
                            title="{{ __('Distribute Costs by Percentage') }}">
                            <i class="fas fa-percentage"></i>
                        </button>
                        <button 
                            x-on:click="if (!$wire.isSaving) { syncForSave(); $wire.saveInvoice(); }"
                            class="btn btn-primary btn-sm"
                            x-bind:disabled="$wire.isSaving"
                            wire:loading.attr="disabled"
                            wire:target="saveInvoice"
                            title="{{ __('Save Invoice') }}">
                            <span wire:loading.remove wire:target="saveInvoice">
                                <i class="fas fa-save"></i>
                            </span>
                            <span wire:loading wire:target="saveInvoice">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <div x-show="products.length > 0" class="alert alert-info mt-2 mb-0 d-flex align-items-center gap-2 py-1 small">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        {{ __('Total raw materials and expenses will be distributed') }}
                        (<span x-text="formatCurrency(totalExpenses)" class="fw-bold"></span>)
                        {{ __('on products based on specified percentages') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Modals -->
        @if ($showSaveTemplateModal)
            <div class="modal fade show" style="display: block; z-index: 2000; background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Save as Manufacturing Template') }}</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>{{ __('Template Name') }}</label>
                                <input type="text" wire:model="templateName" class="form-control">
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>{{ __('Expected Production Time') }}</label>
                                <input type="text" wire:model="templateExpectedTime"
                                    class="form-control" id="timepicker">
                            </div>
                        </div>

                        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                        <script>
                            flatpickr("#timepicker", {
                                enableTime: true,
                                noCalendar: true,
                                dateFormat: "H:i",
                                time_24hr: true
                            });
                        </script>

                        <div class="modal-footer">
                            <button wire:click="saveAsTemplate"
                                class="btn btn-primary">{{ __('Save') }}</button>
                            <button wire:click="closeSaveTemplateModal"
                                class="btn btn-secondary">{{ __('Cancel') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($showLoadTemplateModal)
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5); z-index: 2000;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-folder-open me-2"></i>
                                {{ __('Select Manufacturing Template') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white"
                                wire:click="closeLoadTemplateModal"></button>
                        </div>
                        <div class="modal-body">
                            @if (count($templates) > 0)
                                <div class="mb-3">
                                    <label
                                        class="form-label fw-bold">{{ __('Choose Template') }}:</label>
                                    <select wire:model.live="selectedTemplate"
                                        class="form-select form-select-lg">
                                        <option value="">{{ __('-- Select Template --') }}
                                        </option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template['id'] }}">
                                                {{ $template['display_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if ($selectedTemplate)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>{{ __('Note') }}:</strong>
                                        {{ __('All products and materials saved in this template will be loaded') }}.
                                    </div>
                                @endif

                                @if ($selectedTemplate)
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ __('Template Preview') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $currentTemplate = collect($templates)->firstWhere(
                                                    'id',
                                                    $selectedTemplate,
                                                );
                                            @endphp
                                            @if ($currentTemplate)
                                                <p class="mb-1">
                                                    <strong>{{ __('Date') }}:</strong>
                                                    {{ $currentTemplate['pro_date'] }}
                                                </p>
                                                <p class="mb-0">
                                                    <strong>{{ __('Value') }}:</strong>
                                                    {{ number_format($currentTemplate['pro_value'], 2) }}
                                                    {{ __('EGP') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('No Saved Templates') }}</h5>
                                    <p class="text-muted">
                                        {{ __('Save a template first to load it later') }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            @if (count($templates) > 0)
                                <button wire:click="loadTemplate" class="btn btn-primary px-4">
                                    <i class="fas fa-download me-2"></i>
                                    {{ __('Load Template') }}
                                </button>
                            @endif
                            <button wire:click="closeLoadTemplateModal"
                                class="btn btn-secondary px-4">
                                <i class="fas fa-times me-2"></i>
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row" style="margin-bottom: 140px;">
            <div class="col-12">
                        <!-- قسم المنتجات المصنعة -->
                        <div class="mb-9 card" style="max-height: 200px; overflow-y: auto; overflow-x: hidden; position: relative;">
                            <!-- حقل البحث للمنتجات المصنعة -->
                            <div class="row" style="position: sticky; top: 0; z-index: 10; background: white; padding: 10px 0; margin: 0;">
                                <div class="col-lg-3 mb-0" style="position: relative; z-index: 999;" x-data="productSearch()">
                                    <div class="input-group input-group-sm">
                                        <input type="text" x-model="searchTerm" id="product_search"
                                            class="form-control form-control-sm frst"
                                            placeholder="{{ __('Search for product...') }}" autocomplete="off"
                                            style="font-size: 1em;"
                                            @keydown.arrow-down.prevent="handleKeyDown()"
                                            @keydown.arrow-up.prevent="handleKeyUp()"
                                            @keydown.enter.prevent="handleEnter()" />
                                        <button class="btn btn-outline-secondary" type="button" 
                                            @click="$store.manufacturingItems.loadItems(true)"
                                            title="{{ __('Refresh Items Data') }}">
                                            <i class="fas fa-sync-alt" :class="{'fa-spin': $store.manufacturingItems && $store.manufacturingItems.loading}"></i>
                                        </button>
                                    </div>

                                    <div x-show="results.length > 0" class="position-absolute w-100" style="z-index: 999;">
                                        <ul class="list-group">
                                            <template x-for="(item, index) in results" :key="item.id">
                                                <li class="list-group-item list-group-item-action"
                                                    :class="{ 'active': selectedIndex === index }"
                                                    @click="selectItem(item)"
                                                    x-text="item.name">
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <div x-show="showNoResults"
                                         class="mt-2 position-absolute w-100" style="z-index: 1000;">
                                        <div class="list-group-item text-danger">
                                            {{ __('No results for') }} "<span x-text="searchTerm"></span>"
                                        </div>
                                    </div>
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
                                        <h5 class="text-muted">{{ __('No Products') }}</h5>
                                        <p class="text-muted small">{{ __('Add products used in manufacturing') }}</p>
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-light">
                                                <tr class="text-center">
                                                    <th style="width: 20%">{{ __('Product') }}</th>
                                                    <th style="width: 15%">{{ __('Quantity') }}</th>
                                                    <th style="width: 15%">{{ __('Unit Cost') }}</th>
                                                    <th style="width: 15%">{{ __('Cost Percentage') }} %</th>
                                                    <th style="width: 15%">{{ __('Total') }}</th>
                                                    <th style="width: 10%">{{ __('Actions') }}</th>
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
                                                                x-model.number="products[{{ $index }}].quantity"
                                                                @input="updateProductTotal({{ $index }})"
                                                                min="0.01" step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="padding:2px;height:30px;font-size: 0.9em;"
                                                                placeholder="{{ __('Quantity') }}">
                                                        </td>

                                                        <td>
                                                            <input type="number"
                                                                id="product_unit_cost_{{ $index }}"
                                                                x-model.number="products[{{ $index }}].average_cost"
                                                                @input="updateProductTotal({{ $index }})"
                                                                min="0" step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="padding:2px;height:30px;font-size: 0.9em;"
                                                                placeholder="{{ __('Unit Cost') }}"
                                                                title="{{ __('Average purchase price will be updated') }}">
                                                        </td>

                                                        <td>
                                                            <input type="number"
                                                                id="product_cost_percentage_{{ $index }}"
                                                                x-model.number="products[{{ $index }}].cost_percentage"
                                                                min="0" max="100" step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="padding:2px;height:30px;font-size: 0.9em;"
                                                                placeholder="{{ __('Cost Percentage') }}">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                x-bind:value="formatCurrency(products[{{ $index }}].total_cost || 0)"
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
                                            {{ __('Raw Materials') }}
                                        </a>
                                    </li>

                                   
                                        <li class="nav-item">
                                            <a class="nav-link py-1 px-2 {{ ($activeTab ?? 'general_chat') == 'group_chat' ? 'active' : '' }}"
                                                id="group_chat_tab" data-bs-toggle="pill" href="#group_chat"
                                                onclick="setActiveTab('group_chat')"
                                                wire:click="$set('activeTab', 'group_chat')">
                                                {{ __('Expenses') }}
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
                                                                    style="max-height: 250px; overflow-y: auto; overflow-x: hidden; position: relative;">
                                                                    <!-- حقل البحث للمواد الخام -->
                                                                    <div class="row p-3" style="position: sticky; top: 0; z-index: 10; background: white; margin: 0;">
                                                                        <div class="col-lg-3 mb-2" style="position: relative;" x-data="rawMaterialSearch()">
                                            <div class="input-group input-group-sm">
                                                <input type="text"
                                                    x-model="searchTerm"
                                                    id="raw_material_search"
                                                    class="form-control form-control-sm frst"
                                                    placeholder="{{ __('Search for raw material...') }}"
                                                    autocomplete="off"
                                                    style="font-size: 1em;"
                                                    @keydown.arrow-down.prevent="handleKeyDown()"
                                                    @keydown.arrow-up.prevent="handleKeyUp()"
                                                    @keydown.enter.prevent="handleEnter()" />
                                                <button class="btn btn-outline-secondary" type="button" 
                                                    @click="$store.manufacturingItems.loadItems(true)"
                                                    title="{{ __('Refresh Items Data') }}">
                                                    <i class="fas fa-sync-alt" :class="{'fa-spin': $store.manufacturingItems && $store.manufacturingItems.loading}"></i>
                                                </button>
                                            </div>

                                                                            <div x-show="results.length > 0" class="position-absolute w-100" style="z-index: 999;">
                                                                                <ul class="list-group">
                                                                                    <template x-for="(item, index) in results" :key="item.id">
                                                                                        <li class="list-group-item list-group-item-action"
                                                                                            :class="{ 'active': selectedIndex === index }"
                                                                                            @click="selectItem(item)"
                                                                                            x-text="item.name">
                                                                                        </li>
                                                                                    </template>
                                                                                </ul>
                                                                            </div>

                                                                            <div x-show="showNoResults"
                                                                                 class="mt-2 position-absolute w-100" style="z-index: 1000;">
                                                                                <div class="list-group-item text-danger">
                                                                                    {{ __('No results for') }} "<span x-text="searchTerm"></span>"
                                                                                </div>
                                                                            </div>
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
                                                                                <h5 class="text-muted">
                                                                                    {{ __('No Raw Materials') }}</h5>
                                                                                <p class="text-muted small">
                                                                                    {{ __('Add raw materials used in manufacturing') }}
                                                                                </p>
                                                                            </div>
                                                                        @else
                                                                            <div class="space-y-3">
                                                                                <!-- جدول المواد الخام -->
                                                                                <table
                                                                                    class="table table-bordered table-sm">
                                                                                    <thead class="table-light">
                                                                                        <tr class="text-center">
                                                                                            <th style="width: 20%">
                                                                                                {{ __('Raw Material') }}
                                                                                            </th>
                                                                                            <th style="width: 15%">
                                                                                                {{ __('Unit') }}
                                                                                            </th>
                                                                                            <th style="width: 15%">
                                                                                                {{ __('Quantity') }}
                                                                                            </th>
                                                                                            <th style="width: 15%">
                                                                                                {{ __('Cost Price') }}
                                                                                            </th>
                                                                                            <th style="width: 15%">
                                                                                                {{ __('Total') }}
                                                                                            </th>
                                                                                            <th style="width: 10%">
                                                                                                {{ __('Actions') }}
                                                                                            </th>
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
                                                                                                        x-model="rawMaterials[{{ $index }}].unit_id"
                                                                                                        @change="updateRawMaterialUnit({{ $index }}); updateRawMaterialTotal({{ $index }})"
                                                                                                        class="form-control form-control-sm unit-select"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        data-item-id="{{ $material['id'] ?? '' }}">
                                                                                                        @foreach ($material['unitsList'] ?? [] as $unit)
                                                                                                            <option
                                                                                                                value="{{ $unit['id'] }}">
                                                                                                                {{ $unit['name'] }}
                                                                                                                ({{ number_format($unit['available_qty'], 0, '.', '') }}
                                                                                                                {{ __('pieces') }})
                                                                                                            </option>
                                                                                                        @endforeach
                                                                                                    </select>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="number"
                                                                                                        id="raw_quantity_{{ $index }}"
                                                                                                        x-model.number="rawMaterials[{{ $index }}].quantity"
                                                                                                        @input="updateRawMaterialTotal({{ $index }})"
                                                                                                        min="0.01"
                                                                                                        step="0.01"
                                                                                                        class="form-control form-control-sm"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        placeholder="{{ __('Quantity') }}">
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="number"
                                                                                                        id="raw_unit_cost_{{ $index }}"
                                                                                                        x-bind:value="rawMaterials[{{ $index }}].average_cost || 0"
                                                                                                        readonly
                                                                                                        disabled
                                                                                                        class="form-control form-control-sm cost-input bg-light"
                                                                                                        style="padding:2px;height:30px;font-size: 0.9em;"
                                                                                                        placeholder="{{ __('Average Cost') }}"
                                                                                                        title="{{ __('Average cost cannot be modified in manufacturing invoices') }}">
                                                                                                </td>
                                                                                                <td>
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        x-bind:value="formatCurrency(rawMaterials[{{ $index }}].total_cost || 0)"
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
                                                                                        {{ __('Add Expense') }}
                                                                                    </button>
                                                                                </div>
                                                                            </div>

                                                                            <div class="bg-light rounded-3 p-3 mx-3 mb-3"
                                                                                style="max-height: 190px; overflow-y: auto; overflow-x: hidden;">
                                                                                @if (count($additionalExpenses) === 0)
                                                                                    <div class="text-center py-5">
                                                                                        <i class="fas fa-money-bill-wave text-muted mb-3"
                                                                                            style="font-size: 2rem;"></i>
                                                                                        <h5 class="text-muted">
                                                                                            {{ __('No Additional Expenses') }}
                                                                                        </h5>
                                                                                        <p class="text-muted small">
                                                                                            {{ __('Add additional manufacturing expenses') }}
                                                                                        </p>
                                                                                    </div>
                                                                                @else
                                                                                    <div class="row">
                                                                                        {{-- الجزء الخاص بإدخال المصروفات --}}
                                                                                        <div class="col-md-8">
                                                                                            <table
                                                                                                class="table table-bordered table-sm">
                                                                                                <thead>
                                                                                                    <tr>
                                                                                                        <th>{{ __('Amount') }}
                                                                                                        </th>
                                                                                                        <th>{{ __('Account') }}
                                                                                                        </th>
                                                                                                        <th>{{ __('Description') }}
                                                                                                        </th>
                                                                                                        <th>{{ __('Delete') }}
                                                                                                        </th>
                                                                                                    </tr>
                                                                                                </thead>
                                                                                                <tbody
                                                                                                    id="additional_expenses_table_body">
                                                                                                    @foreach ($additionalExpenses as $index => $expense)
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                                <input
                                                                                                                    type="number"
                                                                                                                    x-model.number="expenses[{{ $index }}].amount"
                                                                                                                    @input="updateTotals()"
                                                                                                                    min="0"
                                                                                                                    step="0.01"
                                                                                                                    placeholder="0.00"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="padding:2px;height:30px;">
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                <select
                                                                                                                    x-model="expenses[{{ $index }}].account_id"
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
                                                                                                                    x-model="expenses[{{ $index }}].description"
                                                                                                                    placeholder="{{ __('Expense Description') }}"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="padding:2px;height:30px;">
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
                                                                                            <div
                                                                                                class="card p-3 bg-light">
                                                                                                <h6
                                                                                                    class="mb-3 fw-bold">
                                                                                                    {{ __('Total Additional Expenses') }}
                                                                                                </h6>
                                                                                                <p
                                                                                                    class="fs-5 text-success"
                                                                                                    x-text="formatCurrency(totalExpenses)">
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
                </div>
            </div>

        <!-- Fixed Footer -->
        <div class="card border-0 shadow-lg" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000; background: white; margin: 0;">
            <div class="card-body py-2">
                <div class="container-fluid">
                    <div class="row g-2 align-items-center">
                        <!-- Input Costs -->
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-0">
                                        <i class="fas fa-cubes me-1 text-primary"></i>{{ __('Raw Materials') }}
                                    </label>
                                    <div class="fw-bold text-primary">
                                        {{ number_format($totalRawMaterialsCost, 2) }} ج.م
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-0">
                                        <i class="fas fa-money-bill-wave me-1 text-warning"></i>{{ __('Expenses') }}
                                    </label>
                                    <div class="fw-bold text-warning">
                                        {{ number_format($totalAdditionalExpenses, 2) }} ج.م
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-0">
                                        <i class="fas fa-coins me-1 text-success"></i>{{ __('Total Cost') }}
                                    </label>
                                    <div class="fw-bold text-success fs-6">
                                        {{ number_format($totalManufacturingCost, 2) }} ج.م
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Output Costs -->
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-0">
                                        <i class="fas fa-box me-1 text-success"></i>{{ __('Finished Products') }}
                                    </label>
                                    <div class="fw-bold text-success">
                                        {{ number_format($totalProductsCost, 2) }} ج.م
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-0">
                                        <i class="fas fa-ruler me-1 text-info"></i>{{ __('Standard Cost') }}
                                    </label>
                                    <div class="fw-bold text-info">
                                        --
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-0">
                                        <i class="fas fa-balance-scale me-1 text-danger"></i>{{ __('Difference') }}
                                    </label>
                                    <div class="fw-bold text-danger">
                                        --
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spacer for fixed footer -->
        <div style="height: 120px;"></div>
        </div>
    @endif
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('modules/manufacturing/js/manufacturing-calculator.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupKeyboardNavigation() {
                // Product quantity field - navigate to search
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

                // Raw material quantity field - navigate to price field
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

                // Price field - navigate to search field
                document.querySelectorAll('input[id^="raw_quantity_"]').forEach(function(field) {
                    field.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const searchField = document.getElementById('raw_material_search');
                            if (searchField) {
                                searchField.focus();
                                searchField.value = '';
                            }
                        }
                    });
                });
            }

            document.addEventListener('livewire:init', () => {
                Livewire.on('show-alert', (data) => {
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                    })
                });
            })

            // Initialize on page load
            setupKeyboardNavigation();
            document.addEventListener('livewire:init', () => {
                // Focus on product quantity field
                Livewire.on('focusProductQuantity', (index) => {
                    setTimeout(() => {
                        const field = document.getElementById(`product_quantity_${index}`);
                        if (field) {
                            field.focus();
                            field.select();
                        }
                    }, 300);
                });

                // Focus on raw material quantity field
                Livewire.on('focusRawMaterialQuantity', (index) => {
                    setTimeout(() => {
                        const field = document.getElementById(`raw_quantity_${index}`);
                        if (field) {
                            field.focus();
                            field.select();
                        }
                    }, 300);
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

            // Success Alert
            Livewire.on('success-swal', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: d.title || '{{ __('Done!') }}',
                    text: d.text || '{{ __('Operation completed successfully') }}',
                    icon: d.icon || 'success',
                    confirmButtonText: '{{ __('OK') }}',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    if (d.reload || d.reload === true) {
                        window.location.reload();
                    }
                });
            });

            // Error Alert
            Livewire.on('error-swal', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: d.title || '{{ __('Error!') }}',
                    text: d.text || '{{ __('An unexpected error occurred') }}',
                    icon: d.icon || 'error',
                    confirmButtonText: '{{ __('OK') }}'
                });
            });

            // General Alert (optional)
            Livewire.on('alert-swal', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire(d);
            });

            document.addEventListener('DOMContentLoaded', function() {
                // Track value changes
                Livewire.on('template-selected', (templateId) => {
                    console.log('Template selected:', templateId);
                });

                Livewire.on('templates-loaded', (count) => {
                    console.log('Templates loaded:', count);
                });
            });
        });
    </script>
@endpush
