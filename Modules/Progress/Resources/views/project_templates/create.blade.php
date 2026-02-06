@extends('progress::layouts.app')

@section('title', __('general.create_template'))

@section('content')
    <div class="card modern-card">
        <div class="card-header gradient-header text-white">
            <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>{{ __('general.create_template') }}</h5>
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>خطأ في التحقق من البيانات</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="error-alert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>خطأ</h5>
                <div id="error-message"></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
                <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>نجح</h5>
                <div id="success-message"></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <form action="{{ route('progress.project-templates.store') }}" method="POST" id="templateForm">
                @csrf
                <input type="hidden" name="prevent_enter_submission" value="1">

                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-layer-group me-1"></i>{{ __('general.template_name') }}
                        </label>
                        <input type="text" name="name" class="form-control rounded-pill shadow-sm"
                            value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-diagram-project me-1"></i>{{ __('general.project__type') }}
                        </label>
                        <select name="project_type_id" class="form-select rounded-pill shadow-sm">
                            <option value="">{{ __('general.select_project_type') }}</option>
                            @foreach ($projectTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('project_type_id') == $type->id)>
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
                    <textarea name="description" class="form-control shadow-sm rounded-3" rows="3">{{ old('description') }}</textarea>
                </div>



                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary">
                        <i class="fas fa-list-check me-1"></i>{{ __('general.select_items_for_template') }}
                    </label>

                    
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
                                <div class="form-check py-2 work-item-option" data-item-id="{{ $item->id }}"
                                    data-category="{{ $item->category_id }}">
                                    <input class="form-check-input work-item-checkbox" type="checkbox"
                                        id="work_item_{{ $item->id }}" value="{{ $item->id }}"
                                        data-unit="{{ $item->unit }}" data-name="{{ $item->name }}"
                                        data-expected-daily="{{ $item->expected_quantity_per_day ?? 0 }}"
                                        data-duration="{{ $item->duration ?? 0 }}">
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
                                    <div class="form-check py-2 work-item-option" data-item-id="{{ $item->id }}"
                                        data-category="{{ $item->category_id }}">
                                        <input class="form-check-input work-item-checkbox" type="checkbox"
                                            id="work_item_cat_{{ $item->id }}" value="{{ $item->id }}"
                                            data-unit="{{ $item->unit }}" data-name="{{ $item->name }}"
                                            data-expected-daily="{{ $item->expected_quantity_per_day ?? 0 }}"
                                            data-duration="{{ $item->duration ?? 0 }}">
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ __('general.selected_items') }}</h6>
                        <small class="text-muted">{{ __('general.drag_to_reorder') }}</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle shadow-sm" id="selected-items-table">
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
                                    <th style="width: 120px;">{{ __('general.total_quantity') }}</th>
                                    <th style="width: 120px;">{{ __('general.estimated_daily_qty') }}</th>
                                    <th style="width: 100px;">{{ __('general.estimated_duration') }}</th>
                                    <th style="width: 150px;">{{ __('general.predecessor') }}</th>
                                    <th style="width: 150px;">{{ __('general.dependency_type') }}</th>
                                    <th style="width: 100px;">{{ __('general.lag') }}</th>
                                    <th style="width: 140px;">{{ __('general.start_date') }}</th>
                                    <th style="width: 140px;">{{ __('general.end_date') }}</th>
                                    <th style="width: 100px;" class="text-center">{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="selected-items-container" class="sortable-table"></tbody>
                        </table>
                    </div>
                </div>


                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-gradient me-2 px-4">
                        <i class="fas fa-save me-1"></i> {{ __('general.save_template') }}
                    </button>

                    <a href="{{ route('progress.project-templates.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> {{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const workItems = @json($workItems);
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
                            dragHandle.innerHTML =
                                `<i class="fas fa-grip-vertical text-muted"></i>${orderInput.outerHTML}`;
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
            function addItemToContainer(itemId) {
                if (!itemId) return;
                if (document.querySelector(`tr[data-item-id="${itemId}"]`)) return;

                const item = workItems.find(i => i.id == itemId);
                if (!item) return;

                const name = item?.name || `Item #${itemId}`;
                const unit = item?.unit || '';
                const expectedDaily = item?.expected_quantity_per_day || 0;
                const baseDuration = item?.duration || 0;

                const row = document.createElement('tr');
                row.dataset.itemId = itemId;

                const rowCount = container.querySelectorAll('tr[data-item-id]').length;

                row.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input item-checkbox">
                    </td>
                    <td class="drag-handle text-center" style="cursor: move;">
                        <i class="fas fa-grip-vertical text-muted"></i>
                        <input type="hidden" class="item-order" name="items[${itemId}][item_order]" value="${rowCount}">
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">${rowCount + 1}</span>
                    </td>
                    <td>
                        <input type="hidden" name="items[${itemId}][work_item_id]" value="${itemId}">
                        <div class="fw-semibold">${name}</div>
                        <small class="text-muted">${__('general.base_duration')}: ${baseDuration} ${__('general.days')}</small>
                    </td>
                    <td>
                        <input type="text" 
                               name="items[${itemId}][subproject_name]" 
                               class="form-control form-control-sm subproject-input" 
                               placeholder="اختر أو أدخل مشروع فرعي"
                               list="subproject-list-${itemId}">
                        <datalist id="subproject-list-${itemId}">
                        </datalist>
                    </td>
                    <td>
                        <textarea name="items[${itemId}][notes]"
                                  class="form-control form-control-sm notes-textarea"
                                  rows="2"
                                  placeholder="${__('general.notes_placeholder')}"></textarea>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="items[${itemId}][is_measurable]" class="form-check-input" value="1" checked>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${itemId}][default_quantity]"
                               class="form-control form-control-sm total-quantity"
                               value="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${itemId}][estimated_daily_qty]"
                               class="form-control form-control-sm estimated-daily-qty"
                               value="${expectedDaily || '0'}">
                    </td>
                    <td>
                        <input type="number" step="1" min="0"
                               name="items[${itemId}][duration]"
                               class="form-control form-control-sm duration-input"
                               value="${baseDuration}">
                    </td>
                    <td>
                        <select name="items[${itemId}][predecessor]" class="form-select form-select-sm predecessor-select">
                            <option value="">{{ __('general.none') }}</option>
                        </select>
                    </td>
                    <td>
                        <select name="items[${itemId}][dependency_type]" class="form-select form-select-sm dependency-type-select">
                            <option value="end_to_start">${__('general.after_end')}</option>
                            <option value="start_to_start">${__('general.after_start')}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1"
                               name="items[${itemId}][lag]"
                               class="form-control form-control-sm lag-input"
                               value="0"
                               placeholder="${__('general.positive_or_negative')}">
                    </td>
                    <td>
                        <input type="date" name="items[${itemId}][start_date]" 
                               class="form-control form-control-sm item-start-date"
                               value="${new Date().toISOString().split('T')[0]}">
                    </td>
                    <td>
                        <input type="date" name="items[${itemId}][end_date]" 
                               class="form-control form-control-sm item-end-date" 
                               value="${new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}"
                               readonly>
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
                const originalSubproject = originalRow.querySelector('.subproject-input')?.value || '';
                const originalIsMeasurable = originalRow.querySelector('input[name*="[is_measurable]"]')?.checked || false;
                const originalStartDate = originalRow.querySelector('.item-start-date')?.value || '';
                const originalEndDate = originalRow.querySelector('.item-end-date')?.value || '';

                row.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input item-checkbox">
                    </td>
                    <td class="drag-handle text-center" style="cursor: move;">
                        <i class="fas fa-grip-vertical text-muted"></i>
                        <input type="hidden" class="item-order" name="items[${newItemId}][item_order]" value="${rowCount}">
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">${rowCount + 1}</span>
                    </td>
                    <td>
                        <input type="hidden" name="items[${newItemId}][work_item_id]" value="${itemId}">
                        <div class="fw-semibold">${name}</div>
                        <small class="text-muted">${__('general.base_duration')}: ${baseDuration} ${__('general.days')}</small>
                    </td>
                    <td>
                        <input type="text" 
                               name="items[${newItemId}][subproject_name]" 
                               class="form-control form-control-sm subproject-input" 
                               placeholder="اختر أو أدخل مشروع فرعي"
                               list="subproject-list-${newItemId}"
                               value="${originalSubproject}">
                        <datalist id="subproject-list-${newItemId}">
                        </datalist>
                    </td>
                    <td>
                        <textarea name="items[${newItemId}][notes]"
                                  class="form-control form-control-sm notes-textarea"
                                  rows="2"
                                  placeholder="${__('general.notes_placeholder')}">${originalNotes}</textarea>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" name="items[${newItemId}][is_measurable]" class="form-check-input" value="1" ${originalIsMeasurable ? 'checked' : ''}>
                    </td>
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
                        <input type="date" name="items[${newItemId}][start_date]" 
                               class="form-control form-control-sm item-start-date"
                               value="${originalStartDate || new Date().toISOString().split('T')[0]}">
                    </td>
                    <td>
                        <input type="date" name="items[${newItemId}][end_date]" 
                               class="form-control form-control-sm item-end-date" 
                               value="${originalEndDate || new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}"
                               readonly>
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
                const allRows = container.querySelectorAll('tr[data-item-id]');
                allRows.forEach(currentRow => {
                    const currentItemId = currentRow.dataset.itemId;
                    const predecessorSelect = currentRow.querySelector('.predecessor-select');
                    const currentValue = predecessorSelect.value;
                    predecessorSelect.innerHTML = '<option value="">{{ __('general.none') }}</option>';

                    allRows.forEach(otherRow => {
                        const otherItemId = otherRow.dataset.itemId;
                        if (otherItemId !== currentItemId) {
                            const itemName = otherRow.querySelector('td:nth-child(4) .fw-semibold')
                                .textContent;
                            const option = document.createElement('option');
                            option.value = otherItemId;
                            option.textContent = itemName;
                            if (currentValue === otherItemId) {
                                option.selected = true;
                            }
                            predecessorSelect.appendChild(option);
                        }
                    });
                });
            }

            // Select All Items functionality
            const selectAllCheckbox = document.getElementById('selectAllItems');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const itemCheckboxes = container.querySelectorAll('.item-checkbox');
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }

            // Update select all checkbox when individual checkboxes change
            container.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-checkbox')) {
                    const itemCheckboxes = container.querySelectorAll('.item-checkbox');
                    const checkedCount = container.querySelectorAll('.item-checkbox:checked').length;
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = checkedCount === itemCheckboxes.length && itemCheckboxes.length > 0;
                        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < itemCheckboxes.length;
                    }
                }
            });

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

            // Prevent form submission on Enter key
            document.getElementById('templateForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') {
                    e.preventDefault();
                    return false;
                }
            });

            // Function to show error message
            function showError(message, errors = null) {
                const errorAlert = document.getElementById('error-alert');
                const errorMessage = document.getElementById('error-message');
                const successAlert = document.getElementById('success-alert');
                
                successAlert.classList.add('d-none');
                errorMessage.innerHTML = '<strong>' + message + '</strong>';
                
                if (errors) {
                    let errorsHtml = '<ul class="mb-0 mt-2">';
                    if (typeof errors === 'object') {
                        Object.keys(errors).forEach(key => {
                            if (Array.isArray(errors[key])) {
                                errors[key].forEach(error => {
                                    errorsHtml += '<li>' + error + '</li>';
                                });
                            } else {
                                errorsHtml += '<li>' + errors[key] + '</li>';
                            }
                        });
                    }
                    errorsHtml += '</ul>';
                    errorMessage.innerHTML += errorsHtml;
                }
                
                errorAlert.classList.remove('d-none');
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Function to show success message
            function showSuccess(message) {
                const successAlert = document.getElementById('success-alert');
                const successMessage = document.getElementById('success-message');
                const errorAlert = document.getElementById('error-alert');
                
                errorAlert.classList.add('d-none');
                successMessage.innerHTML = '<strong>' + message + '</strong>';
                successAlert.classList.remove('d-none');
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Form validation
            document.getElementById('templateForm').addEventListener('submit', function(e) {
                const selectedItems = container.querySelectorAll('tr[data-item-id]');
                if (selectedItems.length === 0) {
                    e.preventDefault();
                    showError('{{ __('general.select_at_least_one_work_item') }}');
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
                    showError('{{ __('general.please_set_quantity_for_all_items') }}');
                    return false;
                }

                // Submit form via AJAX to catch errors
                e.preventDefault();
                
                const form = this;
                const formData = new FormData(form);
                
                // Debug: Log form data
                console.log('=== Form Data Debug ===');
                const itemsData = {};
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('items[')) {
                        const match = key.match(/items\[([^\]]+)\]\[([^\]]+)\]/);
                        if (match) {
                            const itemId = match[1];
                            const field = match[2];
                            if (!itemsData[itemId]) {
                                itemsData[itemId] = {};
                            }
                            itemsData[itemId][field] = value;
                        }
                    }
                }
                console.log('Items to be sent:', itemsData);
                console.log('Total items count:', Object.keys(itemsData).length);
                
                // Show loading
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...';

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    return response.json().then(data => {
                        if (!response.ok) {
                            throw { status: response.status, data: data };
                        }
                        return data;
                    });
                })
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message || 'تم إنشاء القالب بنجاح');
                        setTimeout(() => {
                            window.location.href = '{{ route("progress.project-templates.index") }}';
                        }, 1500);
                    } else {
                        showError(data.message || 'حدث خطأ أثناء حفظ القالب', data.errors);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    let errorMessage = 'حدث خطأ أثناء حفظ القالب';
                    let errors = null;
                    
                    if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        }
                        if (error.data.errors) {
                            errors = error.data.errors;
                        }
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    showError(errorMessage, errors);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
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

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #selected-items-table {
            font-size: 0.875rem;
            min-width: 2000px; /* Minimum width to ensure all columns are visible */
        }

        #selected-items-table th {
            white-space: nowrap;
            font-size: 0.8rem;
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #0d6efd;
            color: white;
        }

        #selected-items-table .form-control-sm,
        #selected-items-table .form-select-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }

        #selected-items-table td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0.5rem !important;
        }

        #selected-items-table td:nth-child(4) {
            white-space: normal;
            word-wrap: break-word;
            overflow: visible;
        }

        #selected-items-table td:nth-child(6) {
            white-space: normal;
            word-wrap: break-word;
            overflow: hidden !important;
        }

        #selected-items-table input[type="text"],
        #selected-items-table input[type="number"],
        #selected-items-table input[type="date"],
        #selected-items-table select {
            width: 100% !important;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Responsive styles for smaller screens */
        @media (max-width: 768px) {
            .table-responsive {
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
            }

            #selected-items-table {
                font-size: 0.75rem;
            }

            #selected-items-table th,
            #selected-items-table td {
                padding: 0.375rem !important;
            }

            #selected-items-table .form-control-sm,
            #selected-items-table .form-select-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
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
