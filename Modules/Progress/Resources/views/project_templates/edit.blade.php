@extends('progress::layouts.app')

@section('title', __('general.edit_template'))

@section('content')
    <div class="card modern-card">
        <div class="card-header gradient-header text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>{{ __('general.edit_template') }}</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('progress.project-templates.update', $project_template) }}" method="POST" id="templateForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="prevent_enter_submission" value="1">

                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-layer-group me-1"></i>{{ __('general.template_name') }}
                        </label>
                        <input type="text" name="name" class="form-control rounded-pill shadow-sm"
                            value="{{ old('name', $project_template->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-diagram-project me-1"></i>{{ __('general.project__type') }}
                        </label>
                        <select name="project_type_id" class="form-select rounded-pill shadow-sm">
                            <option value="">{{ __('general.select_project_type') }}</option>
                            @foreach ($projectTypes as $type)
                                <option value="{{ $type->id }}"
                                    @selected(old('project_type_id', $project_template->project_type_id) == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-align-left me-1"></i>{{ __('general.description') }}
                    </label>
                    <textarea name="description" class="form-control shadow-sm rounded-3" rows="3">{{ old('description', $project_template->description) }}</textarea>
                </div>

                

                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-list-check me-1"></i>{{ __('general.select_items_for_template') }}
                    </label>

                    <div class="mb-3" style="position: relative;">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                id="work-item-search"
                                class="form-control"
                                placeholder="{{ __('general.search_work_items') }}"
                                autocomplete="off">
                            <span class="input-group-text" id="search-loading" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                            </span>
                        </div>

                        <div id="search-results"
                            class="list-group position-absolute w-100 shadow-lg"
                            style="display: none; z-index: 1000; max-height: 400px; overflow-y: auto;">
                            <div class="list-group-item bg-light d-flex justify-content-between align-items-center">
                                <span class="fw-bold" id="results-count">0 {{ __('general.results') }}</span>
                                <button type="button" class="btn-close btn-sm" id="close-search"></button>
                            </div>
                            <div id="search-results-list"></div>
                            <div class="list-group-item bg-light text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('general.click_to_add_item') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <small class="form-text text-muted d-block mb-3">
                        <i class="fas fa-keyboard me-1"></i>
                        {{ __('general.type_to_search_items') }}
                    </small>

                    
                    <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                                type="button" role="tab">
                                {{ __('general.all_categories') }}
                            </button>
                        </li>
                        @foreach ($categories as $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="cat-{{ $category->id }}-tab" data-bs-toggle="tab"
                                    data-bs-target="#cat-{{ $category->id }}" type="button" role="tab">
                                    {{ $category->name }} ({{ $category->workItems->count() }})
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    
                    <div class="tab-content border rounded-3 p-3 bg-light shadow-sm work-items-container"
                        style="max-height: 300px; overflow-y: auto;">

                        
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            @foreach ($workItems as $item)
                                @php
                                    $isSelected = $project_template->items->contains('work_item_id', $item->id);
                                @endphp
                                <div class="form-check py-2 work-item-option" data-item-id="{{ $item->id }}"
                                    data-category="{{ $item->category_id }}">
                                    <input class="form-check-input work-item-checkbox" type="checkbox"
                                        id="work_item_{{ $item->id }}" value="{{ $item->id }}"
                                        data-unit="{{ $item->unit }}" data-name="{{ $item->name }}"
                                        data-expected-daily="{{ $item->expected_quantity_per_day ?? 0 }}"
                                        data-duration="{{ $item->duration ?? 0 }}"
                                        {{ $isSelected ? 'checked' : '' }}>
                                    <label class="form-check-label" for="work_item_{{ $item->id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $item->name }} ({{ $item->unit }})</span>
                                            <small
                                                class="text-muted">{{ $item->category->name ?? __('general.uncategorized') }}</small>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        
                        @foreach ($categories as $category)
                            <div class="tab-pane fade" id="cat-{{ $category->id }}" role="tabpanel">
                                @foreach ($category->workItems as $item)
                                    @php
                                        $isSelected = $project_template->items->contains('work_item_id', $item->id);
                                    @endphp
                                    <div class="form-check py-2 work-item-option" data-item-id="{{ $item->id }}"
                                        data-category="{{ $item->category_id }}">
                                        <input class="form-check-input work-item-checkbox" type="checkbox"
                                            id="work_item_cat_{{ $item->id }}" value="{{ $item->id }}"
                                            data-unit="{{ $item->unit }}" data-name="{{ $item->name }}"
                                            data-expected-daily="{{ $item->expected_quantity_per_day ?? 0 }}"
                                            data-duration="{{ $item->duration ?? 0 }}"
                                            {{ $isSelected ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_item_cat_{{ $item->id }}">
                                            <div class="d-flex justify-content-between">
                                                <span>{{ $item->name }} ({{ $item->unit }})</span>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">{{ __('general.select_multiple_items') }}</small>
                </div>

                
                <div class="mt-3">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-filter"></i>
                                </span>
                                <input type="text"
                                    id="items-filter"
                                    class="form-control"
                                    placeholder="{{ __('general.filter_items') }}">
                                <button type="button"
                                    id="reset-filter"
                                    class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ __('general.selected_items') }}</h6>
                        <small class="text-muted">{{ __('general.drag_to_reorder') }}</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="selected-items-table">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllItems" class="form-check-input">
                                    </th>
                                    <th style="width: 40px;" class="text-center">
                                        <i class="fas fa-grip-vertical"></i>
                                    </th>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="min-width: 300px;">{{ __('general.item_name') }}</th>
                                    <th style="min-width: 200px;">المشروع الفرعي</th>
                                    <th style="min-width: 350px;">{{ __('general.notes') }}</th>
                                    <th style="width: 100px;" class="text-center">قابل للقياس</th>
                                    <th style="width: 120px;">{{ __('general.default_quantity') }}</th>
                                    <th style="width: 120px;">{{ __('general.estimated_daily_qty') }}</th>
                                    <th style="width: 100px;">{{ __('general.duration') }}</th>
                                    <th style="width: 150px;">{{ __('general.predecessor') }}</th>
                                    <th style="width: 150px;">{{ __('general.dependency_type') }}</th>
                                    <th style="width: 100px;">{{ __('general.lag') }}</th>
                                    <th style="width: 100px;" class="text-center">{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="selected-items-container" class="sortable-table">
                                
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="empty-state" class="text-center py-5 d-none">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">{{ __('general.no_items_selected') }}</p>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-gradient me-2 px-4">
                        <i class="fas fa-save me-1"></i> {{ __('general.update_template') }}
                    </button>

                    <a href="{{ route('progress.project-templates.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> {{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="{{ asset('js/template-predecessor-debug.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const workItems = @json($workItems);
            const templateItems = @json($project_template->items);
            
            console.log('=== Template Edit Page Loaded ===');
            console.log('Work Items:', workItems.length);
            console.log('Template Items:', templateItems.length);
            console.log('Template Items Data:', templateItems.map(item => ({
                id: item.id,
                work_item_id: item.work_item_id,
                predecessor: item.predecessor,
                dependency_type: item.dependency_type,
                lag: item.lag
            })));
            
            // Make templateItems available globally for debugging
            window.templateItems = templateItems;
            window.workItems = workItems;
            const container = document.getElementById('selected-items-container');
            const weeklyHolidaysInput = document.getElementById('weekly_holidays_input');
            const weeklyHolidayCheckboxes = document.querySelectorAll('.weekly-holiday-checkbox');
            const workItemCheckboxes = document.querySelectorAll('.work-item-checkbox');

            // Initialize Sortable for drag & drop
            const sortable = new Sortable(container, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function(evt) {
                    updateItemOrders();
                }
            });

            // Update item orders after drag & drop
            function updateItemOrders() {
                const rows = container.querySelectorAll('tr[data-item-id]');
                rows.forEach((row, index) => {
                    const orderInput = row.querySelector('.item-order');
                    if (orderInput) {
                        orderInput.value = index + 1;
                    }
                    // Update the visual order number
                    const dragHandle = row.querySelector('.drag-handle');
                    if (dragHandle) {
                        const icon = dragHandle.querySelector('i');
                        if (icon) {
                            dragHandle.innerHTML = `<i class="fas fa-grip-vertical text-muted"></i>${orderInput.outerHTML}`;
                        }
                    }
                });
            }

            // Update weekly holidays from checkboxes
            function updateWeeklyHolidays() {
                const selectedHolidays = Array.from(weeklyHolidayCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);
                weeklyHolidaysInput.value = selectedHolidays.join(',');
            }

            // Initialize weekly holidays
            weeklyHolidayCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateWeeklyHolidays);
            });

            // إضافة بند للجدول
            function addItemToContainer(itemId, templateItem = null, uniqueRowId = null) {
                if (!itemId) return;
                
                // Generate unique row ID if not provided
                const rowId = uniqueRowId || `item_${Date.now()}_${itemId}`;
                
                // Check if item already exists
                if (document.querySelector(`tr[data-item-id="${rowId}"]`)) return;

                const item = workItems.find(i => i.id == itemId);
                if (!item) return;

                const name = item?.name || `Item #${itemId}`;
                const unit = item?.unit || '';
                const expectedDaily = item?.expected_quantity_per_day || 0;
                const baseDuration = item?.duration || 0;

                // استخدام البيانات من templateItem إذا وجد
                const defaultQuantity = templateItem ? templateItem.default_quantity : 1;
                const estimatedDailyQty = templateItem ? templateItem.estimated_daily_qty : expectedDaily;
                const duration = templateItem ? templateItem.duration : baseDuration;
                const predecessor = templateItem ? templateItem.predecessor : '';
                const dependencyType = templateItem ? templateItem.dependency_type : 'end_to_start';
                const lag = templateItem ? templateItem.lag : 0;
                const notes = templateItem ? templateItem.notes : '';
                const itemOrder = templateItem ? templateItem.item_order : container.querySelectorAll('tr[data-item-id]').length;
                const itemIdValue = templateItem ? templateItem.id : '';

                // استخدام البيانات من templateItem إذا وجد
                const subprojectName = templateItem ? (templateItem.subproject_name || '') : '';
                const isMeasurable = templateItem ? (templateItem.is_measurable || false) : true;
                
                const row = document.createElement('tr');
                row.dataset.itemId = rowId;
                row.dataset.workItemId = itemId; // Store work_item_id for reference
                
                const rowNumber = container.querySelectorAll('tr[data-item-id]').length + 1;

                row.innerHTML = `
                    <td>
                        <input type="checkbox" class="form-check-input item-checkbox" value="${rowId}">
                    </td>
                    <td class="drag-handle text-center" style="cursor: move;">
                        <i class="fas fa-grip-vertical text-muted"></i>
                        <input type="hidden" class="item-order" name="items[${rowId}][item_order]" value="${itemOrder}">
                        ${itemIdValue ? `<input type="hidden" name="items[${rowId}][id]" value="${itemIdValue}">` : ''}
                    </td>
                    <td class="text-center">${rowNumber}</td>
                    <td>
                        <input type="hidden" name="items[${rowId}][work_item_id]" value="${itemId}">
                        <div class="fw-semibold">${name}</div>
                        <small class="text-muted">${unit}</small>
                    </td>
                    <td>
                        <input type="text"
                               name="items[${rowId}][subproject_name]"
                               class="form-control form-control-sm subproject-name"
                               value="${subprojectName}"
                               placeholder="اسم المشروع الفرعي">
                    </td>
                    <td>
                        <textarea name="items[${rowId}][notes]"
                                  class="form-control form-control-sm notes-textarea"
                                  rows="2"
                                  placeholder="${__('general.notes_placeholder')}">${notes ?? ''}</textarea>
                    </td>
                    <td class="text-center">
                        <input type="checkbox"
                               name="items[${rowId}][is_measurable]"
                               class="form-check-input"
                               ${isMeasurable ? 'checked' : ''}
                               value="1">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${rowId}][default_quantity]"
                               class="form-control form-control-sm total-quantity"
                               value="${defaultQuantity}" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${rowId}][estimated_daily_qty]"
                               class="form-control form-control-sm estimated-daily-qty"
                               value="${estimatedDailyQty}">
                    </td>
                    <td>
                        <input type="number" step="1" min="0"
                               name="items[${rowId}][duration]"
                               class="form-control form-control-sm duration-input"
                               value="${duration}">
                    </td>
                    <td>
                        <select name="items[${rowId}][predecessor]" class="form-select form-select-sm predecessor-select" data-predecessor="${predecessor}">
                            <option value="">{{ __('general.none') }}</option>
                        </select>
                    </td>
                    <td>
                        <select name="items[${rowId}][dependency_type]" class="form-select form-select-sm dependency-type-select">
                            <option value="end_to_start" ${dependencyType === 'end_to_start' ? 'selected' : ''}>${__('general.after_end')}</option>
                            <option value="start_to_start" ${dependencyType === 'start_to_start' ? 'selected' : ''}>${__('general.after_start')}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1"
                               name="items[${rowId}][lag]"
                               class="form-control form-control-sm lag-input"
                               value="${lag}"
                               placeholder="${__('general.positive_or_negative')}">
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-success duplicate-item" title="${__('general.duplicate')}">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger remove-item" title="${__('general.remove')}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                `;

                container.appendChild(row);

                // Update predecessors dropdown for all items
                updatePredecessorsDropdowns();

                // Update item orders
                updateItemOrders();

                // Add event listeners
                setupRowEventListeners(row, itemId);

                return row;
            }

            function setupRowEventListeners(row, itemId) {
                const totalQtyInput = row.querySelector('.total-quantity');
                const estimatedQtyInput = row.querySelector('.estimated-daily-qty');
                const durationInput = row.querySelector('.duration-input');
                const predecessorSelect = row.querySelector('.predecessor-select');

                function updateDuration() {
                    const totalQty = parseFloat(totalQtyInput.value) || 0;
                    const estimatedQty = parseFloat(estimatedQtyInput.value) || 0;
                    let duration = 0;

                    if (totalQty > 0 && estimatedQty > 0) {
                        duration = Math.ceil(totalQty / estimatedQty);
                    } else {
                        const originalItem = workItems.find(i => i.id == itemId);
                        duration = originalItem?.duration || 0;
                    }

                    durationInput.value = duration;
                }

                totalQtyInput.addEventListener('input', updateDuration);
                estimatedQtyInput.addEventListener('input', updateDuration);

                // Duplicate item functionality
                row.querySelector('.duplicate-item').addEventListener('click', function() {
                    duplicateItem(itemId);
                });

                // Remove item functionality
                row.querySelector('.remove-item').addEventListener('click', function() {
                    const checkbox = document.querySelector(`input[value="${itemId}"].work-item-checkbox`);
                    if (checkbox) {
                        checkbox.checked = false;
                        updateWorkItemStyle(checkbox);
                    }
                    row.remove();
                    updatePredecessorsDropdowns();
                    updateItemOrders();
                });

                updateDuration();
            }

            // Duplicate item function
            function duplicateItem(itemId) {
                const originalRow = document.querySelector(`tr[data-item-id="${itemId}"]`);
                if (!originalRow) return;

                const originalItem = workItems.find(i => i.id == itemId);
                if (!originalItem) return;

                // Create new item ID (temporary for form)
                const newItemId = 'dup_' + Date.now() + '_' + itemId;

                const name = originalItem.name + ' - ' + __('general.copy');
                const unit = originalItem.unit;
                const expectedDaily = originalItem.expected_quantity_per_day || 0;
                const baseDuration = originalItem.duration || 0;

                const row = document.createElement('tr');
                row.dataset.itemId = newItemId;

                const rowCount = container.querySelectorAll('tr[data-item-id]').length;

                // Get values from original row
                const originalTotalQty = originalRow.querySelector('.total-quantity').value;
                const originalEstimatedQty = originalRow.querySelector('.estimated-daily-qty').value;
                const originalDuration = originalRow.querySelector('.duration-input').value;
                const originalNotes = originalRow.querySelector('.notes-textarea').value;
                const originalPredecessor = originalRow.querySelector('.predecessor-select').value;
                const originalDependencyType = originalRow.querySelector('.dependency-type-select').value;
                const originalLag = originalRow.querySelector('.lag-input').value;

                row.innerHTML = `
                    <td class="drag-handle text-center" style="cursor: move;">
                        <i class="fas fa-grip-vertical text-muted"></i>
                        <input type="hidden" class="item-order" name="items[${newItemId}][item_order]" value="${rowCount}">
                    </td>
                    <td>
                        <input type="hidden" name="items[${newItemId}][work_item_id]" value="${itemId}">
                        <div class="fw-semibold">${name}</div>
                        <small class="text-muted">${__('general.base_duration')}: ${baseDuration} ${__('general.days')}</small>
                    </td>
                    <td><span class="badge bg-light text-dark">${unit}</span></td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${newItemId}][default_quantity]"
                               class="form-control form-control-sm total-quantity"
                               value="${originalTotalQty}" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${newItemId}][estimated_daily_qty]"
                               class="form-control form-control-sm estimated-daily-qty"
                               value="${originalEstimatedQty}">
                    </td>
                    <td>
                        <input type="number" step="1" min="0"
                               name="items[${newItemId}][duration]"
                               class="form-control form-control-sm duration-input"
                               value="${originalDuration}">
                    </td>
                    <td>
                        <select name="items[${newItemId}][predecessor]" class="form-select form-select-sm predecessor-select">
                            <option value="">{{ __('general.none') }}</option>
                        </select>
                    </td>
                    <td>
                        <select name="items[${newItemId}][dependency_type]" class="form-select form-select-sm dependency-type-select">
                            <option value="end_to_start" ${originalDependencyType === 'end_to_start' ? 'selected' : ''}>${__('general.after_end')}</option>
                            <option value="start_to_start" ${originalDependencyType === 'start_to_start' ? 'selected' : ''}>${__('general.after_start')}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1"
                               name="items[${newItemId}][lag]"
                               class="form-control form-control-sm lag-input"
                               value="${originalLag}"
                               placeholder="${__('general.positive_or_negative')}">
                    </td>
                    <td>
                        <textarea name="items[${newItemId}][notes]"
                                  class="form-control form-control-sm notes-textarea"
                                  rows="2"
                                  placeholder="${__('general.notes_placeholder')}">${originalNotes}</textarea>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-success duplicate-item" title="${__('general.duplicate')}">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger remove-item" title="${__('general.remove')}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                `;

                container.appendChild(row);
                updatePredecessorsDropdowns();
                setupRowEventListeners(row, newItemId);

                // Set predecessor after dropdown is populated
                setTimeout(() => {
                    const predecessorSelect = row.querySelector('.predecessor-select');
                    if (predecessorSelect && originalPredecessor) {
                        predecessorSelect.value = originalPredecessor;
                    }
                }, 100);
            }

            // Helper function for translations in JavaScript
            function __(key) {
                const translations = {
                    'general.base_duration': 'Base Duration',
                    'general.days': 'days',
                    'general.duplicate': 'Duplicate',
                    'general.remove': 'Remove',
                    'general.after_end': 'After End',
                    'general.after_start': 'After Start',
                    'general.positive_or_negative': 'Positive or negative',
                    'general.notes_placeholder': 'Add notes...',
                    'general.copy': 'Copy',
                    'general.none': 'None'
                };
                return translations[key] || key;
            }

            function updatePredecessorsDropdowns() {
                console.log('🔄 Updating all predecessor dropdowns');
                const allRows = container.querySelectorAll('tr[data-item-id]');
                
                allRows.forEach(currentRow => {
                    const currentItemId = currentRow.dataset.itemId;
                    const currentWorkItemId = currentRow.dataset.workItemId;
                    const predecessorSelect = currentRow.querySelector('.predecessor-select');
                    // Check data-predecessor attribute first, then current value
                    const currentValue = predecessorSelect.dataset.predecessor || predecessorSelect.value;
                    
                    console.log('Updating dropdown for item:', currentItemId, 'Work Item:', currentWorkItemId, 'Current value:', currentValue);
                    
                    predecessorSelect.innerHTML = '<option value="">{{ __('general.none') }}</option>';

                    allRows.forEach(otherRow => {
                        const otherItemId = otherRow.dataset.itemId;
                        const otherWorkItemId = otherRow.dataset.workItemId;
                        
                        if (otherItemId !== currentItemId) {
                            const itemNameElement = otherRow.querySelector('td:nth-child(2) .fw-semibold');
                            const itemName = itemNameElement ? itemNameElement.textContent : `Item ${otherWorkItemId}`;
                            const option = document.createElement('option');
                            
                            // Use work_item_id as value
                            option.value = otherWorkItemId;
                            option.textContent = itemName;
                            
                            // Use loose comparison (==) instead of strict (===)
                            if (currentValue == otherWorkItemId) {
                                option.selected = true;
                                console.log('🟫 Re-selecting option:', otherWorkItemId, itemName);
                            }
                            
                            predecessorSelect.appendChild(option);
                        }
                    });
                    
                    // If we found and selected the predecessor, clear the data-predecessor attribute
                    if (currentValue && predecessorSelect.value) {
                        delete predecessorSelect.dataset.predecessor;
                    }
                });
                
                console.log('✅ Finished updating all dropdowns');
            }

            function updateWorkItemStyle(checkbox) {
                const workItemOption = checkbox.closest('.work-item-option');
                if (checkbox.checked) {
                    workItemOption.classList.add('selected-item');
                } else {
                    workItemOption.classList.remove('selected-item');
                }
            }

            // Event listeners
            workItemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateWorkItemStyle(this);
                    if (this.checked) {
                        addItemToContainer(this.value);
                    } else {
                        const row = document.querySelector(`tr[data-item-id="${this.value}"]`);
                        if (row) {
                            row.remove();
                            updatePredecessorsDropdowns();
                        }
                    }
                });
            });

            // Load existing template items
            console.log('Loading existing template items:', templateItems);
            
            // Create a map to store work_item_id to generated row_id
            const workItemToRowIdMap = new Map();
            let rowCounter = 0;
            
            templateItems.forEach(templateItem => {
                // Generate a unique row ID for this template item
                const uniqueRowId = `template_item_${templateItem.id}_${templateItem.work_item_id}`;
                workItemToRowIdMap.set(templateItem.work_item_id, uniqueRowId);
                
                addItemToContainer(templateItem.work_item_id, templateItem, uniqueRowId);
                const checkbox = document.querySelector(`input[value="${templateItem.work_item_id}"].work-item-checkbox`);
                if (checkbox) {
                    checkbox.checked = true;
                    updateWorkItemStyle(checkbox);
                }
                rowCounter++;
            });
            
            // Set predecessors after all items are loaded
            setTimeout(() => {
                console.log('=== Setting Predecessors ===');
                // Simply call updatePredecessorsDropdowns - it will use data-predecessor attributes
                updatePredecessorsDropdowns();
                console.log('=== Finished Setting Predecessors ===');
            }, 500);

            // Prevent form submission on Enter key
            document.getElementById('templateForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') {
                    e.preventDefault();
                    return false;
                }
            });

            // Form validation
            document.getElementById('templateForm').addEventListener('submit', function(e) {
                const selectedItems = container.querySelectorAll('tr[data-item-id]');
                if (selectedItems.length === 0) {
                    e.preventDefault();
                    alert('{{ __('general.select_at_least_one_work_item') }}');
                    return false;
                }

                // Validate required fields in items
                let hasErrors = false;
                selectedItems.forEach(row => {
                    const quantityInput = row.querySelector('.total-quantity');
                    if (!quantityInput.value || parseFloat(quantityInput.value) <= 0) {
                        quantityInput.classList.add('is-invalid');
                        hasErrors = true;
                    } else {
                        quantityInput.classList.remove('is-invalid');
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert('{{ __('general.please_set_quantity_for_all_items') }}');
                    return false;
                }
            });
        });


    </script>

    <style>
        .modern-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .gradient-header {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            padding: 1rem 1.5rem;
        }

        .divider {
            border-top: 2px dashed #e5e7eb;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            border: none;
            border-radius: 2rem;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 0.5rem 1rem rgba(37, 99, 235, 0.3);
        }

        .form-control,
        .form-select {
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
            border-color: #3b82f6;
        }

        .work-items-container {
            background: #f8fafc !important;
        }

        .work-item-option {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            padding: 0.5rem !important;
        }

        .work-item-option:hover {
            background-color: #e2e8f0;
        }

        .work-item-option.selected-item {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
            border: 1px solid #3b82f6;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
        }

        .work-item-option.selected-item label {
            color: #1e40af;
            font-weight: 600;
        }

        #selected-items-table {
            font-size: 0.875rem;
        }

        #selected-items-table th {
            white-space: nowrap;
            font-size: 0.8rem;
        }

        #selected-items-table .form-control-sm,
        #selected-items-table .form-select-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }

        .drag-handle {
            cursor: move;
            color: #6c757d;
        }

        .sortable-table tr {
            cursor: move;
        }

        .sortable-table tr.sortable-ghost {
            opacity: 0.4;
        }

        .notes-textarea {
            resize: vertical;
            min-height: 60px;
        }

        .weekly-holiday-checkbox {
            margin-right: 0.25rem;
        }

        .form-check-label.small {
            font-size: 0.8rem;
        }

        .nav-tabs .nav-link.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
        }
    </style>
@endsection
