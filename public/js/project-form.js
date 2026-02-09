/**
 * Project Form Manager
 * Clean and Simple Implementation with Bootstrap
 */

(function () {
    'use strict';

    // ========================================
    // Configuration
    // ========================================
    const config = {
        searchDebounceMs: 300,
        maxSearchResults: 20
    };

    // ========================================
    // State Management
    // ========================================
    let state = {
        workItems: [],
        project: null,
        projectItems: [],
        itemCounter: 0,
        searchTimeout: null,
        viewMode: 'flat', // 'flat' or 'grouped'
        autoSaveInterval: null,
        lastAutoSaveTime: null,
        originalItemOrder: null // حفظ الترتيب الأصلي للبنود
    };

    // ========================================
    // DOM Elements
    // ========================================
    const elements = {
        form: document.getElementById('projectForm'),
        searchInput: document.getElementById('work-item-search'),
        searchResults: document.getElementById('search-results'),
        searchResultsList: document.getElementById('search-results-list'),
        searchLoading: document.getElementById('search-loading'),
        closeSearch: document.getElementById('close-search'),
        resultsCount: document.getElementById('results-count'),
        itemsFilter: document.getElementById('items-filter'),
        resetFilter: document.getElementById('reset-filter'),
        selectAllItems: document.getElementById('selectAllItems'),
        container: document.getElementById('selected-items-container'),
        startDate: document.getElementById('start_date'),
        endDate: document.getElementById('end_date'),
        weeklyHolidaysInput: document.getElementById('weekly-holidays-input'),
        description: document.getElementById('description'),
        charCount: document.getElementById('char-count'),
        emptyState: document.getElementById('empty-state')
    };

    // ========================================
    // Initialize
    // ========================================
    function init() {
        console.log('🚀 Initializing Project Form...');

        // Load data from server if available
        if (typeof window.projectFormData !== 'undefined') {
            state.workItems = window.projectFormData.workItems || [];
            state.project = window.projectFormData.project || null;
            state.projectItems = window.projectFormData.projectItems || [];
            state.subprojects = window.projectFormData.subprojects || [];

            // Debug: Log project items data
            if (state.projectItems && state.projectItems.length > 0) {
                console.log('📦 Loaded project items:', state.projectItems.map(item => ({
                    id: item.id,
                    predecessor: item.predecessor,
                    work_item_name: item.work_item?.name
                })));
            }
        }

        // Set start_date to today if empty (for new projects)
        if (elements.startDate && !elements.startDate.value) {
            const today = new Date();
            const formattedDate = formatDate(today);
            elements.startDate.value = formattedDate;
            console.log('✅ Set start_date to today:', formattedDate);
        }

        initEventListeners();
        initWeeklyHolidays();
        initCharCounter();
        initSortable();
        loadExistingItems();
        updateEmptyState();

        // Calculate working days on page load
        updateWeeklyHolidays();

        // Start auto-save for drafts (only for new projects)
        startAutoSave();

        // Debug: Log form data before submit & Clean empty predecessors
        if (elements.form) {
            elements.form.addEventListener('submit', function (e) {
                console.log('🚀 ===== FORM SUBMIT START =====');

                // Update all subproject units and quantities before submit
                document.querySelectorAll('.category-group-row').forEach(groupRow => {
                    const categoryHeader = groupRow.querySelector('.category-header');
                    if (!categoryHeader) return;

                    const categoryName = categoryHeader.dataset.category;
                    const categoryId = 'cat_' + categoryName.replace(/[^a-zA-Z0-9]/g, '_');

                    // Update quantity
                    const qtyManualInput = groupRow.querySelector(`.subproject-quantity-input-${categoryId}`);
                    const qtyHiddenInput = groupRow.querySelector(`.subproject-quantity-${categoryId}`);
                    if (qtyManualInput && qtyHiddenInput) {
                        qtyHiddenInput.value = qtyManualInput.value || 0;
                    }

                    // Update unit
                    const unitManualInput = groupRow.querySelector(`.subproject-unit-input-${categoryId}`);
                    const unitHiddenInput = groupRow.querySelector(`.subproject-unit-${categoryId}`);
                    if (unitManualInput && unitHiddenInput) {
                        unitHiddenInput.value = unitManualInput.value || '';
                        console.log(`📦 Updated subproject unit: ${categoryName} = "${unitManualInput.value}"`);
                    }

                    // Update weight
                    const weightManualInput = groupRow.querySelector(`.subproject-weight-input-${categoryId}`);
                    const weightHiddenInput = groupRow.querySelector(`.subproject-weight-${categoryId}`);
                    if (weightManualInput && weightHiddenInput) {
                        weightHiddenInput.value = weightManualInput.value || 1.00;
                        console.log(`📦 Updated subproject weight: ${categoryName} = "${weightManualInput.value}"`);
                    }
                });

                // 🔍 Log all item IDs being sent
                const itemIdInputs = elements.form.querySelectorAll('input[name*="[id]"]:not([name*="work_item_id"])');
                console.log(`📦 Found ${itemIdInputs.length} items with IDs (existing items)`);

                if (itemIdInputs.length === 0) {
                    console.warn('⚠️ NO ITEM IDs FOUND! This means all items will be treated as new!');
                    console.log('🔍 Checking DOM for hidden inputs:');
                    const allRows = elements.container.querySelectorAll('tr[data-item-id]');
                    allRows.forEach((row, idx) => {
                        const hiddenId = row.querySelector('input[name*="[id]"]:not([name*="work_item_id"])');
                        console.log(`  Row ${idx + 1}:`, {
                            rowId: row.dataset.itemId,
                            hasHiddenIdInput: !!hiddenId,
                            hiddenIdValue: hiddenId?.value,
                            hiddenIdName: hiddenId?.name,
                            innerHTML: row.querySelector('td:nth-child(4)')?.innerHTML.substring(0, 200)
                        });
                    });
                }

                itemIdInputs.forEach((input, index) => {
                    console.log(`  Item ${index + 1}:`, {
                        name: input.name,
                        id: input.value,
                        row: input.closest('tr')?.dataset.itemId
                    });
                });

                // Log all predecessor selects BEFORE cleaning
                const predecessorSelects = elements.form.querySelectorAll('.predecessor-select');
                console.log(`📊 Found ${predecessorSelects.length} predecessor selects`);

                predecessorSelects.forEach((select, index) => {
                    const row = select.closest('tr');
                    const itemId = row?.querySelector('input[name*="[id]"]')?.value;
                    console.log(`  Select ${index + 1}:`, {
                        name: select.name,
                        value: select.value,
                        isEmpty: select.value === '' || select.value === null,
                        itemId: itemId || 'NEW'
                    });
                });

                // Clean empty predecessor values before submit
                predecessorSelects.forEach(select => {
                    console.log(`🔍 Checking select:`, {
                        name: select.name,
                        value: select.value,
                        isEmpty: select.value === '' || select.value === null || select.value === undefined
                    });

                    if (select.value === '' || select.value === null || select.value === undefined) {
                        console.log(`  ❌ Removing empty predecessor: ${select.name}`);
                        select.removeAttribute('name'); // Don't send empty values
                    } else {
                        console.log(`  ✅ Keeping predecessor: ${select.name} = ${select.value}`);
                    }
                });

                const formData = new FormData(elements.form);
                console.log('📤 Form Data Summary:');

                let itemsWithIds = 0;
                let itemsNew = 0;
                let predecessorCount = 0;
                let subprojectUnitsCount = 0;

                for (let [key, value] of formData.entries()) {
                    if (key.includes('[id]') && !key.includes('work_item_id')) {
                        itemsWithIds++;
                        console.log(`  ✅ Existing Item: ${key} = ${value}`);
                    }
                    if (key.includes('[predecessor]')) {
                        predecessorCount++;
                        console.log(`  🔗 Predecessor: ${key} = "${value}"`);
                    }
                    if (key.includes('[unit]') && key.includes('subprojects')) {
                        subprojectUnitsCount++;
                        console.log(`  📦 Subproject Unit: ${key} = "${value}"`);
                    }
                }

                console.log(`\n📊 Summary:`);
                console.log(`  - Items with IDs (existing): ${itemsWithIds}`);
                console.log(`  - Predecessors being sent: ${predecessorCount}`);
                console.log(`  - Subproject units being sent: ${subprojectUnitsCount}`);
                console.log('🚀 ===== FORM SUBMIT END =====\n');
            });
        }

        console.log('✅ Project Form initialized');
    }

    // ========================================
    // Event Listeners
    // ========================================
    function initEventListeners() {
        // Form submit validation
        if (elements.form) {
            elements.form.addEventListener('submit', handleFormSubmit);
            elements.form.addEventListener('keydown', handleEnterKey);
        }

        // Search
        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', debounce(handleSearch, config.searchDebounceMs));
        }

        // Close search
        if (elements.closeSearch) {
            elements.closeSearch.addEventListener('click', hideSearchResults);
        }

        // Filter
        if (elements.itemsFilter) {
            elements.itemsFilter.addEventListener('input', function () {
                // Clear any existing timeout
                if (state.searchTimeout) {
                    clearTimeout(state.searchTimeout);
                }
                // Debounce the search for better performance
                state.searchTimeout = setTimeout(filterItems, 150);
            });
            // Also trigger on paste and change events
            elements.itemsFilter.addEventListener('paste', function () {
                setTimeout(filterItems, 150);
            });
            elements.itemsFilter.addEventListener('change', filterItems);
        }

        if (elements.resetFilter) {
            elements.resetFilter.addEventListener('click', resetFilter);
        }

        // Select all
        if (elements.selectAllItems) {
            elements.selectAllItems.addEventListener('change', toggleSelectAll);
        }

        // Bulk execute button
        document.getElementById('bulk-execute-btn')?.addEventListener('click', executeBulkAction);

        // Move to subproject modal confirm button
        document.getElementById('confirmMoveBtn')?.addEventListener('click', function () {
            if (!pendingMoveRows || pendingMoveRows.length === 0) {
                showNotification('warning', 'لا توجد بنود محددة للنقل');
                return;
            }

            const subprojectSelect = document.getElementById('subprojectSelect');
            const newSubprojectInput = document.getElementById('newSubprojectName');

            let selectedSubproject = '';

            // Check if a subproject is selected from dropdown
            if (subprojectSelect && subprojectSelect.value) {
                selectedSubproject = subprojectSelect.value.trim();
            }
            // Check if a new subproject name is entered
            else if (newSubprojectInput && newSubprojectInput.value.trim()) {
                selectedSubproject = newSubprojectInput.value.trim();
            }

            if (!selectedSubproject) {
                showNotification('warning', 'الرجاء اختيار مشروع فرعي أو إدخال اسم جديد');
                return;
            }

            // Execute the move (pass category name if exists)
            executeBulkMove(pendingMoveRows, selectedSubproject, pendingMoveCategoryName);

            // Hide modal
            const modalElement = document.getElementById('moveToSubprojectModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            } else if (modalElement) {
                // Fallback hide
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
            }
        });

        // Clear pending rows and reset modal when modal is hidden
        const moveModal = document.getElementById('moveToSubprojectModal');
        if (moveModal) {
            moveModal.addEventListener('hidden.bs.modal', function () {
                pendingMoveRows = null;
                pendingMoveCategoryName = null;
                pendingMoveSelectAllCheckbox = null;
                pendingMoveBulkActionSelect = null;

                // Reset modal title
                const modalTitle = document.querySelector('#moveToSubprojectModalLabel');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="fas fa-folder-open me-2"></i> نقل البنود إلى مشروع فرعي';
                }
            });
        }

        // Date changes
        if (elements.startDate) {
            elements.startDate.addEventListener('change', calculateAllDates);
        }

        // Weekly holidays
        document.querySelectorAll('.holiday-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                updateWeeklyHolidays();
                calculateAllDates();
            });
        });

        // Event delegation for dynamically added items
        // This handles all input/select changes in item rows
        if (elements.container) {
            elements.container.addEventListener('change', function (e) {
                const target = e.target;
                const row = target.closest('tr[data-item-id]');

                if (row && (
                    target.matches('.predecessor-select') ||
                    target.matches('select[name*="[dependency_type]"]') ||
                    target.matches('input[name*="[lag]"]') ||
                    target.matches('.duration-input') ||
                    target.matches('.estimated-daily-qty')
                )) {
                    console.log(`🔄 Input changed in row ${row.dataset.itemId}:`, target.name, '=', target.value);
                    calculateAllDates();
                }
            });

            elements.container.addEventListener('input', function (e) {
                const target = e.target;
                const row = target.closest('tr[data-item-id]');

                if (row && (
                    target.matches('input[name*="[lag]"]') ||
                    target.matches('.duration-input') ||
                    target.matches('.total-quantity') ||
                    target.matches('.estimated-daily-qty')
                )) {
                    console.log(`🔄 Input value changed in row ${row.dataset.itemId}:`, target.name, '=', target.value);
                    calculateAllDates();
                }
            });
        }

        // Template checkboxes
        document.querySelectorAll('.template-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    const templateData = JSON.parse(this.getAttribute('data-template'));

                    // Check if it's a draft or a template
                    if (templateData.type === 'draft') {
                        loadDraftItems(templateData.id);
                    } else {
                        loadTemplateItems(templateData.id);
                    }
                }
            });
        });

        // View mode toggle buttons
        const flatViewBtn = document.getElementById('flat-view-btn');
        const groupedViewBtn = document.getElementById('grouped-view-btn');

        if (flatViewBtn) {
            flatViewBtn.addEventListener('click', function () {
                switchToFlatView();
            });
        }

        if (groupedViewBtn) {
            groupedViewBtn.addEventListener('click', function () {
                switchToGroupedView();
            });
        }

        // Click outside to close search
        document.addEventListener('click', function (e) {
            if (!elements.searchInput?.contains(e.target) &&
                !elements.searchResults?.contains(e.target)) {
                hideSearchResults();
            }
        });
    }

    // ========================================
    // Weekly Holidays
    // ========================================
    function initWeeklyHolidays() {
        // First, sync from hidden input to checkboxes (for edit mode)
        syncHolidaysFromInput();

        // Then update the hidden input from checkboxes
        updateWeeklyHolidays();
    }

    function syncHolidaysFromInput() {
        // Get the current value from hidden input
        const currentValue = elements.weeklyHolidaysInput?.value || '';

        if (!currentValue) return;

        // Parse the comma-separated values
        const selectedDays = currentValue.split(',').map(v => v.trim()).filter(v => v);

        // Update checkboxes to match
        document.querySelectorAll('.holiday-checkbox').forEach(checkbox => {
            const isSelected = selectedDays.includes(checkbox.value);
            checkbox.checked = isSelected;
        });

        console.log('✅ Synced holidays from input:', selectedDays);
    }

    function updateWeeklyHolidays() {
        const checkboxes = document.querySelectorAll('.holiday-checkbox:checked');
        const values = Array.from(checkboxes).map(cb => cb.value);

        if (elements.weeklyHolidaysInput) {
            elements.weeklyHolidaysInput.value = values.join(',');
        }

        // Calculate working days automatically (7 days - holidays)
        const totalDays = 7;
        const holidaysCount = checkboxes.length;
        const workingDays = totalDays - holidaysCount;

        const workingDaysInput = document.getElementById('working_days');
        if (workingDaysInput) {
            workingDaysInput.value = workingDays;
            console.log(`✅ Working days auto-calculated: ${workingDays} (7 - ${holidaysCount} holidays)`);
        }
    }

    // ========================================
    // Character Counter
    // ========================================
    function initCharCounter() {
        if (elements.description && elements.charCount) {
            const updateCounter = () => {
                const length = elements.description.value.length;
                elements.charCount.textContent = length;
            };

            elements.description.addEventListener('input', updateCounter);
            updateCounter();
        }
    }

    // ========================================
    // Search Functionality
    // ========================================
    async function handleSearch() {
        const query = elements.searchInput.value.trim();

        if (query.length === 0) {
            hideSearchResults();
            return;
        }

        showLoading(true);
        showSearchResults();

        try {
            const response = await fetch(`/progress/work-items/search-ajax?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            renderSearchResults(data.results || []);
        } catch (error) {
            console.error('Search error:', error);
            showSearchError();
        } finally {
            showLoading(false);
        }
    }

    function renderSearchResults(results) {
        elements.searchResultsList.innerHTML = '';

        if (results.length === 0) {
            elements.searchResultsList.innerHTML = `
                <div class="list-group-item text-center text-muted">
                    <i class="fas fa-search me-2"></i>
                    لا توجد نتائج
                </div>
            `;
            elements.resultsCount.textContent = '0 نتائج';
            return;
        }

        results.forEach(item => {
            const div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action';
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${escapeHtml(item.name)}</strong>
                        <span class="badge bg-secondary ms-2">${escapeHtml(item.unit)}</span>
                        ${item.category ? `<br><small class="text-muted"><i class="fas fa-folder me-1"></i>${escapeHtml(item.category)}</small>` : ''}
                        ${item.description ? `<br><small class="text-muted fst-italic">${escapeHtml(item.description)}</small>` : ''}
                    </div>
                    <button type="button" class="btn btn-sm btn-primary add-item-btn">
                        <i class="fas fa-plus me-1"></i>
                        إضافة
                    </button>
                </div>
            `;

            const addBtn = div.querySelector('.add-item-btn');
            addBtn.addEventListener('click', function () {
                console.log('🔵 Add button clicked for item:', item);
                addWorkItem(item);
            });

            elements.searchResultsList.appendChild(div);
        });

        elements.resultsCount.textContent = `${results.length} نتائج`;
    }

    function showSearchResults() {
        if (elements.searchResults) {
            elements.searchResults.style.display = 'block';
        }
    }

    function hideSearchResults() {
        if (elements.searchResults) {
            elements.searchResults.style.display = 'none';
        }
    }

    function showLoading(show) {
        if (elements.searchLoading) {
            elements.searchLoading.style.display = show ? 'block' : 'none';
        }
    }

    function showSearchError() {
        elements.searchResultsList.innerHTML = `
            <div class="list-group-item text-center text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                حدث خطأ في البحث
            </div>
        `;
    }

    // ========================================
    // Add Work Item
    // ========================================
    function addWorkItem(item) {
        console.log('🔵 Adding work item:', item);

        // Add to workItems array if not exists
        if (!state.workItems.find(i => i.id == item.id)) {
            state.workItems.push(item);
            console.log('✅ Item added to state.workItems');
        }

        state.itemCounter++;
        const rowId = `item_${state.itemCounter}_${Date.now()}`;

        const row = createItemRow(rowId, item);
        elements.container.appendChild(row);

        updateItemOrders();
        calculateAllDates();
        hideSearchResults();
        elements.searchInput.value = '';
        updateEmptyState();

        showNotification('success', `تم إضافة: ${item.name}`);
        console.log('✅ Work item added successfully');
    }

    // ========================================
    // Create Item Row
    // ========================================
    function createItemRow(rowId, item, existingData = null) {
        // 🔍 Debug: Log existingData
        if (existingData) {
            console.log('📝 createItemRow with existingData:', {
                rowId: rowId,
                existingData_id: existingData.id,
                existingData_id_type: typeof existingData.id,
                hasId: existingData.hasOwnProperty('id'),
                fullData: existingData
            });
        }

        const tr = document.createElement('tr');
        tr.dataset.itemId = rowId;
        tr.dataset.workItemId = item.id;

        const rowNumber = elements.container.children.length;

        // Build the ID input separately to avoid template string issues
        const itemIdInput = existingData?.id
            ? `<input type="hidden" name="items[${rowId}][id]" value="${existingData.id}">`
            : '';

        tr.innerHTML = `
            <td class="text-center">
                <input type="checkbox" class="form-check-input item-checkbox">
            </td>
            <td class="text-center drag-handle" style="cursor: move;">
                <i class="fas fa-grip-vertical text-muted"></i>
                <input type="hidden" name="items[${rowId}][item_order]" value="${rowNumber}">
            </td>
            <td class="text-center">
                <span class="badge bg-primary">${rowNumber + 1}</span>
            </td>
            <td>
                <input type="hidden" name="items[${rowId}][work_item_id]" value="${item.id}">
                ${itemIdInput}
                <strong>${escapeHtml(item.name)}</strong>
                ${item.category ? `<br><small class="text-muted"><i class="fas fa-folder me-1"></i>${escapeHtml(item.category)}</small>` : ''}
                ${item.unit ? `<br><small class="text-muted"><i class="fas fa-ruler me-1"></i>${escapeHtml(item.unit)}</small>` : ''}
            </td>
            <td>
                <input type="text" 
                       name="items[${rowId}][subproject_name]" 
                       class="form-control form-control-sm subproject-input" 
                       placeholder="اختر أو أدخل مشروع فرعي"
                       list="subproject-list-${rowId}"
                       value="${existingData?.subproject_name || ''}">
                <datalist id="subproject-list-${rowId}">
                </datalist>
            </td>
            <td>
                <input type="text" name="items[${rowId}][notes]" class="form-control form-control-sm" value="${existingData?.notes || ''}">
            </td>
            <td class="text-center">
                <input type="checkbox" name="items[${rowId}][is_measurable]" class="form-check-input" value="1" ${existingData?.is_measurable !== undefined ? (existingData.is_measurable ? 'checked' : '') : 'checked'}>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowId}][total_quantity]" 
                       class="form-control form-control-sm total-quantity" 
                       value="${existingData?.total_quantity || 0}">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowId}][estimated_daily_qty]" 
                       class="form-control form-control-sm estimated-daily-qty" 
                       value="${existingData?.estimated_daily_qty || 0}">
            </td>
            <td>
                <input type="number" step="1" min="0" name="items[${rowId}][duration]" 
                       class="form-control form-control-sm duration-input" 
                       value="${existingData?.duration || 1}">
            </td>
            <td>
                <select name="items[${rowId}][predecessor]" class="form-select form-select-sm predecessor-select">
                    <option value="">بدون</option>
                </select>
            </td>
            <td>
                <select name="items[${rowId}][dependency_type]" class="form-select form-select-sm">
                    <option value="end_to_start" ${(existingData?.dependency_type || 'end_to_start') === 'end_to_start' ? 'selected' : ''}>بعد الانتهاء</option>
                    <option value="start_to_start" ${existingData?.dependency_type === 'start_to_start' ? 'selected' : ''}>بعد البداية</option>
                </select>
            </td>
            <td>
                <input type="number" step="1" name="items[${rowId}][lag]" 
                       class="form-control form-control-sm lag-input" 
                       value="${existingData?.lag || 0}">
            </td>
            <td>
                <input type="date" name="items[${rowId}][start_date]" 
                       class="form-control form-control-sm item-start-date" 
                       value="${existingData?.start_date || ''}">
            </td>
            <td>
                <input type="date" name="items[${rowId}][end_date]" 
                       class="form-control form-control-sm item-end-date" 
                       value="${existingData?.end_date || ''}" 
                       readonly>
            </td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-success duplicate-btn" title="نسخ">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger remove-btn" title="حذف">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        // Attach event listeners
        attachRowEventListeners(tr, item, rowId);

        return tr;
    }

    // ========================================
    // Row Event Listeners
    // ========================================
    function attachRowEventListeners(row, item, rowId) {
        // Checkbox change (removed bulk actions bar update since we removed that feature)
        const checkbox = row.querySelector('.item-checkbox');
        // Auto calculate duration
        const totalQty = row.querySelector('.total-quantity');
        const dailyQty = row.querySelector('.estimated-daily-qty');
        const duration = row.querySelector('.duration-input');

        const calculateDuration = () => {
            const total = parseFloat(totalQty.value || 0);
            const daily = parseFloat(dailyQty.value || 0);
            if (total > 0 && daily > 0) {
                duration.value = Math.ceil(total / daily);
            }
        };

        totalQty?.addEventListener('input', () => {
            calculateDuration();
            calculateAllDates();
        });

        dailyQty?.addEventListener('input', () => {
            calculateDuration();
            calculateAllDates();
        });

        duration?.addEventListener('input', calculateAllDates);
        row.querySelector('.predecessor-select')?.addEventListener('change', calculateAllDates);
        row.querySelector('select[name*="[dependency_type]"]')?.addEventListener('change', calculateAllDates);
        row.querySelector('.lag-input')?.addEventListener('input', calculateAllDates);

        // Subproject input - update datalist on input
        const subprojectInput = row.querySelector('.subproject-input');
        if (subprojectInput) {
            subprojectInput.addEventListener('input', () => {
                updateSubprojectDatalist(subprojectInput);
            });

            subprojectInput.addEventListener('focus', () => {
                updateSubprojectDatalist(subprojectInput);
            });
        }

        // Duplicate button
        row.querySelector('.duplicate-btn')?.addEventListener('click', () => {
            duplicateItem(row, item);
        });

        // Remove button
        row.querySelector('.remove-btn')?.addEventListener('click', () => {
            if (confirm('هل أنت متأكد من حذف هذا البند؟')) {
                row.remove();
                updateItemOrders();
                calculateAllDates();
                updateEmptyState();
                showNotification('info', 'تم الحذف بنجاح');
            }
        });
    }

    // ========================================
    // Subproject Management
    // ========================================
    function updateSubprojectDatalist(inputElement) {
        // Get all unique subproject names from all rows
        const subprojects = new Set();

        document.querySelectorAll('.subproject-input').forEach(input => {
            const value = input.value.trim();
            if (value && value !== inputElement.value.trim()) {
                subprojects.add(value);
            }
        });

        // Update the datalist
        const datalistId = inputElement.getAttribute('list');
        const datalist = document.getElementById(datalistId);

        if (datalist) {
            datalist.innerHTML = '';
            subprojects.forEach(subproject => {
                const option = document.createElement('option');
                option.value = subproject;
                datalist.appendChild(option);
            });
        }
    }

    function getUniqueSubprojects() {
        const subprojects = new Set();

        document.querySelectorAll('.subproject-input').forEach(input => {
            const value = input.value.trim();
            if (value) {
                subprojects.add(value);
            }
        });

        return Array.from(subprojects);
    }

    // ========================================
    // Duplicate Item
    // ========================================
    function duplicateItem(originalRow, item) {
        const isMeasurableCheckbox = originalRow.querySelector('input[name*="[is_measurable]"]');
        const originalData = {
            total_quantity: originalRow.querySelector('.total-quantity')?.value || 0,
            estimated_daily_qty: originalRow.querySelector('.estimated-daily-qty')?.value || 0,
            duration: originalRow.querySelector('.duration-input')?.value || 1,
            notes: originalRow.querySelector('textarea[name*="notes"]')?.value || originalRow.querySelector('input[name*="notes"]')?.value || '',
            is_measurable: isMeasurableCheckbox?.checked !== undefined ? isMeasurableCheckbox.checked : true,
            subproject_name: originalRow.querySelector('.subproject-input')?.value || ''
        };

        state.itemCounter++;
        const newRowId = `item_${state.itemCounter}_${Date.now()}`;
        const newRow = createItemRow(newRowId, item, originalData);

        originalRow.insertAdjacentElement('afterend', newRow);
        updateItemOrders();
        updatePredecessors();
        calculateAllDates();

        showNotification('success', `تم نسخ: ${item.name}`);
    }

    // ========================================
    // Update Functions
    // ========================================
    function updateItemOrders() {
        const rows = elements.container.querySelectorAll('tr[data-item-id]');

        rows.forEach((row, index) => {
            const orderInput = row.querySelector('input[name*="item_order"]');
            const numberBadge = row.querySelector('.badge');

            if (orderInput) orderInput.value = index;
            if (numberBadge) numberBadge.textContent = index + 1;
        });

        updatePredecessors();
    }

    function updatePredecessors() {
        const allRows = elements.container.querySelectorAll('tr[data-item-id]');

        allRows.forEach(currentRow => {
            const currentRowId = currentRow.dataset.itemId;
            const predecessorSelect = currentRow.querySelector('.predecessor-select');
            const currentValue = predecessorSelect?.value;

            if (!predecessorSelect) return;

            predecessorSelect.innerHTML = '<option value="">بدون</option>';

            allRows.forEach((otherRow, index) => {
                const otherRowId = otherRow.dataset.itemId;

                if (otherRowId !== currentRowId) {
                    const itemName = otherRow.querySelector('td:nth-child(4) strong')?.textContent || 'Item';
                    const option = document.createElement('option');
                    option.value = otherRowId;
                    option.textContent = `${index + 1}. ${itemName}`;
                    predecessorSelect.appendChild(option);
                }
            });

            if (currentValue) {
                predecessorSelect.value = currentValue;
            }
        });
    }

    function updateEmptyState() {
        if (!elements.emptyState) return;

        const hasItems = elements.container.children.length > 0;
        elements.emptyState.classList.toggle('d-none', hasItems);
    }

    // ========================================
    // Filter Items
    // ========================================
    function filterItems() {
        const query = elements.itemsFilter.value.trim();
        if (!query) {
            // If query is empty, show all rows and category groups
            const rows = elements.container.querySelectorAll('tr[data-item-id]');
            rows.forEach(row => row.style.display = '');
            const categoryGroups = elements.container.querySelectorAll('.category-group-row');
            categoryGroups.forEach(group => group.style.display = '');
            return;
        }

        const queryLower = query.toLowerCase();
        const rows = elements.container.querySelectorAll('tr[data-item-id]');
        let visibleCount = 0;

        rows.forEach(row => {
            // Search in specific columns: name, category, unit, subproject, notes
            const nameCell = row.querySelector('td:nth-child(4)'); // Item name column
            const subprojectInput = row.querySelector('input[name*="[subproject_name]"]');
            const notesInput = row.querySelector('input[name*="[notes]"]');

            let searchableText = '';

            // Get text from name cell (includes name, category, unit)
            if (nameCell) {
                // Get all text content including nested elements
                const nameText = nameCell.innerText || nameCell.textContent || '';
                searchableText += nameText.toLowerCase();
            }

            // Get subproject name
            if (subprojectInput && subprojectInput.value) {
                searchableText += ' ' + subprojectInput.value.toLowerCase();
            }

            // Get notes
            if (notesInput && notesInput.value) {
                searchableText += ' ' + notesInput.value.toLowerCase();
            }

            // Normalize whitespace and search
            searchableText = searchableText.replace(/\s+/g, ' ').trim();

            // Check if query matches (supports both Arabic and English)
            const matches = searchableText.includes(queryLower);
            row.style.display = matches ? '' : 'none';

            if (matches) {
                visibleCount++;
            }
        });

        // If in grouped view, show/hide category groups based on visible items
        if (state.viewMode === 'grouped') {
            const categoryGroups = elements.container.querySelectorAll('.category-group-row');
            categoryGroups.forEach(group => {
                const groupRows = group.querySelectorAll('tr[data-item-id]');
                const hasVisibleRows = Array.from(groupRows).some(row => row.style.display !== 'none');
                group.style.display = hasVisibleRows ? '' : 'none';
            });
        }
    }

    function resetFilter() {
        elements.itemsFilter.value = '';
        const rows = elements.container.querySelectorAll('tr[data-item-id]');
        rows.forEach(row => row.style.display = '');
    }

    function toggleSelectAll() {
        const checkboxes = elements.container.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => cb.checked = elements.selectAllItems.checked);
    }

    // ========================================
    // Bulk Actions Execute
    // ========================================
    function executeBulkAction() {
        const actionSelect = document.getElementById('bulk-action-select');
        const action = actionSelect?.value;

        if (!action) {
            showNotification('warning', 'الرجاء اختيار عملية أولاً');
            return;
        }

        const selectedRows = getSelectedRows();

        if (selectedRows.length === 0) {
            showNotification('warning', 'الرجاء تحديد بنود أولاً');
            return;
        }

        // Execute the selected action
        switch (action) {
            case 'delete':
                bulkDelete(selectedRows);
                break;
            case 'duplicate':
                bulkDuplicate(selectedRows);
                break;
            case 'move':
                bulkMove(selectedRows);
                break;
            case 'export':
                bulkExport(selectedRows);
                break;
            default:
                showNotification('warning', 'عملية غير معروفة');
        }

        // Reset select
        if (actionSelect) {
            actionSelect.value = '';
        }
    }

    function getSelectedRows() {
        const selected = [];

        // Get from flat view
        const flatCheckboxes = elements.container.querySelectorAll('.item-checkbox:checked');
        flatCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr[data-item-id]');
            if (row) selected.push(row);
        });

        // Get from grouped view
        const groupedCheckboxes = document.querySelectorAll('.sortable-category-items .item-checkbox:checked');
        groupedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr[data-item-id]');
            if (row && !selected.includes(row)) {
                selected.push(row);
            }
        });

        return selected;
    }

    function bulkDelete(selectedRows) {
        if (!confirm(`هل أنت متأكد من حذف ${selectedRows.length} بند؟`)) {
            return;
        }

        selectedRows.forEach(row => row.remove());

        updateItemOrders();
        calculateAllDates();
        updateEmptyState();

        if (elements.selectAllItems) {
            elements.selectAllItems.checked = false;
        }

        showNotification('success', `تم حذف ${selectedRows.length} بند بنجاح`);
    }

    function bulkDuplicate(selectedRows) {
        let duplicatedCount = 0;

        selectedRows.forEach(row => {
            const workItemId = row.dataset.workItemId;
            const workItem = state.workItems.find(item => item.id == workItemId);

            if (workItem) {
                const originalData = {
                    total_quantity: row.querySelector('.total-quantity')?.value || 0,
                    estimated_daily_qty: row.querySelector('.estimated-daily-qty')?.value || 0,
                    duration: row.querySelector('.duration-input')?.value || 1,
                    notes: row.querySelector('textarea[name*="notes"]')?.value || '',
                    subproject_name: row.querySelector('.subproject-input')?.value || ''
                };

                state.itemCounter++;
                const newRowId = `item_${state.itemCounter}_${Date.now()}_${duplicatedCount}`;
                const newRow = createItemRow(newRowId, workItem, originalData);

                row.insertAdjacentElement('afterend', newRow);
                duplicatedCount++;
            }
        });

        updateItemOrders();
        updatePredecessors();
        calculateAllDates();

        if (elements.selectAllItems) {
            elements.selectAllItems.checked = false;
        }

        showNotification('success', `تم نسخ ${duplicatedCount} بند بنجاح`);
    }

    // Store selected rows for bulk move operation
    let pendingMoveRows = null;
    let pendingMoveCategoryName = null; // For subproject bulk move
    let pendingMoveSelectAllCheckbox = null; // For resetting after subproject move
    let pendingMoveBulkActionSelect = null; // For resetting after subproject move

    function bulkMove(selectedRows) {
        // Store rows for later use
        pendingMoveRows = selectedRows;
        pendingMoveCategoryName = null; // Clear category name for regular bulk move
        pendingMoveSelectAllCheckbox = null; // Clear for regular bulk move
        pendingMoveBulkActionSelect = null; // Clear for regular bulk move

        // Get unique subprojects
        const subprojects = getUniqueSubprojects();

        // Update modal with item count
        const itemsCountEl = document.getElementById('moveItemsCount');
        if (itemsCountEl) {
            itemsCountEl.textContent = selectedRows.length;
        }

        // Reset modal title to default
        const modalTitle = document.querySelector('#moveToSubprojectModalLabel');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-folder-open me-2"></i> نقل البنود إلى مشروع فرعي';
        }

        // Populate subprojects select
        const subprojectSelect = document.getElementById('subprojectSelect');
        const newSubprojectInput = document.getElementById('newSubprojectName');

        if (subprojectSelect) {
            // Clear existing options except the first one
            subprojectSelect.innerHTML = '<option value="">-- مشروع جديد --</option>';

            // Add existing subprojects
            subprojects.forEach(subproject => {
                const option = document.createElement('option');
                option.value = subproject;
                option.textContent = subproject;
                subprojectSelect.appendChild(option);
            });
        }

        // Clear new subproject input
        if (newSubprojectInput) {
            newSubprojectInput.value = '';
        }

        // Show modal using Bootstrap
        const modalElement = document.getElementById('moveToSubprojectModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else if (modalElement) {
            // Fallback if Bootstrap is not available
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
        }
    }

    function executeBulkMove(selectedRows, subprojectName, fromCategoryName = null) {
        if (!subprojectName || !subprojectName.trim()) {
            showNotification('warning', 'الرجاء اختيار مشروع فرعي أو إدخال اسم جديد');
            return;
        }

        const trimmedName = subprojectName.trim();

        selectedRows.forEach(row => {
            const subprojectInput = row.querySelector('.subproject-input');
            if (subprojectInput) {
                subprojectInput.value = trimmedName;
            }
        });

        if (elements.selectAllItems) {
            elements.selectAllItems.checked = false;
        }

        const successMessage = fromCategoryName
            ? `✅ تم نقل ${selectedRows.length} بند من "${fromCategoryName}" إلى "${trimmedName}"`
            : `تم نقل ${selectedRows.length} بند إلى "${trimmedName}"`;
        showNotification('success', successMessage);

        // If moving from a subproject category, re-render grouped view and reset UI elements
        if (fromCategoryName && typeof renderGroupedView === 'function') {
            setTimeout(() => renderGroupedView(), 300);

            // Reset select all checkbox and bulk action select if they exist
            if (pendingMoveSelectAllCheckbox) {
                pendingMoveSelectAllCheckbox.checked = false;
            }
            if (pendingMoveBulkActionSelect) {
                pendingMoveBulkActionSelect.value = '';
            }
        }

        // Clear pending rows and UI elements
        pendingMoveRows = null;
        pendingMoveCategoryName = null;
        pendingMoveSelectAllCheckbox = null;
        pendingMoveBulkActionSelect = null;
    }

    function bulkExport(selectedRows) {
        const exportData = [];

        selectedRows.forEach((row, index) => {
            const workItemId = row.dataset.workItemId;
            const workItem = state.workItems.find(item => item.id == workItemId);

            exportData.push({
                '#': index + 1,
                'اسم الصنف': workItem?.name || '',
                'الوحدة': workItem?.unit || '',
                'المشروع الفرعي': row.querySelector('.subproject-input')?.value || '',
                'الملاحظات': row.querySelector('textarea[name*="notes"]')?.value || '',
                'الكمية الإجمالية': row.querySelector('.total-quantity')?.value || '',
                'الكمية اليومية': row.querySelector('.estimated-daily-qty')?.value || '',
                'المدة': row.querySelector('.duration-input')?.value || '',
                'تاريخ البداية': row.querySelector('.item-start-date')?.value || '',
                'تاريخ النهاية': row.querySelector('.item-end-date')?.value || ''
            });
        });

        const csv = convertToCSV(exportData);
        downloadCSV(csv, `selected_items_${Date.now()}.csv`);

        if (elements.selectAllItems) {
            elements.selectAllItems.checked = false;
        }

        showNotification('success', `تم تصدير ${selectedRows.length} بند`);
    }

    function convertToCSV(data) {
        if (data.length === 0) return '';

        const headers = Object.keys(data[0]);
        const csvRows = [];

        csvRows.push(headers.join(','));

        data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header] || '';
                return `"${value}"`;
            });
            csvRows.push(values.join(','));
        });

        return csvRows.join('\n');
    }

    function downloadCSV(csv, filename) {
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }


    // ========================================
    // Circular Dependency Detection
    // ========================================
    function detectCircularDependency(itemsData) {
        const visited = new Set();
        const recursionStack = new Set();
        const circularPath = [];

        function hasCycle(itemId, path = []) {
            if (recursionStack.has(itemId)) {
                // Found circular dependency
                const cycleStart = path.indexOf(itemId);
                circularPath.push(...path.slice(cycleStart), itemId);
                return true;
            }

            if (visited.has(itemId)) {
                return false;
            }

            visited.add(itemId);
            recursionStack.add(itemId);

            const item = itemsData.get(itemId);
            if (item?.predecessorId) {
                if (hasCycle(item.predecessorId, [...path, itemId])) {
                    return true;
                }
            }

            recursionStack.delete(itemId);
            return false;
        }

        // Check all items
        for (const [itemId] of itemsData) {
            if (hasCycle(itemId)) {
                // Get item names for better error message
                const cycleNames = circularPath.map(id => {
                    const row = document.querySelector(`tr[data-item-id="${id}"]`);
                    const itemName = row?.querySelector('td:nth-child(4) strong')?.textContent || id;
                    return itemName;
                });

                console.error('🔴 Circular dependency detected:', circularPath);
                console.error('🔴 Cycle path:', cycleNames.join(' → '));

                return {
                    hasCircular: true,
                    path: circularPath,
                    names: cycleNames
                };
            }
        }

        return { hasCircular: false };
    }

    // ========================================
    // Calculate Dates
    // ========================================
    function calculateAllDates() {
        const startDate = elements.startDate?.value;
        if (!startDate) return;

        const projectStart = new Date(startDate);
        const weeklyHolidays = getWeeklyHolidays();
        const rows = elements.container.querySelectorAll('tr[data-item-id]');
        const itemsData = new Map();

        // Collect data
        rows.forEach(row => {
            const rowId = row.dataset.itemId;
            const duration = parseInt(row.querySelector('.duration-input')?.value || 1);
            const predecessorId = row.querySelector('.predecessor-select')?.value || null;
            const dependencyType = row.querySelector('select[name*="[dependency_type]"]')?.value || 'end_to_start';
            const lagInput = row.querySelector('.lag-input');
            const lag = parseInt(lagInput?.value || 0);

            // Debug lag reading
            if (predecessorId) {
                console.log(`📊 Row ${rowId} - Lag value:`, {
                    lagInput: lagInput,
                    rawValue: lagInput?.value,
                    parsedValue: lag,
                    predecessorId: predecessorId,
                    dependencyType: dependencyType
                });
            }

            itemsData.set(rowId, {
                row,
                duration,
                predecessorId,
                dependencyType,
                lag,
                startDate: null,
                endDate: null,
                calculated: false
            });
        });

        // ✅ Check for circular dependencies first
        const circularCheck = detectCircularDependency(itemsData);
        if (circularCheck.hasCircular) {
            showNotification('error', '⚠️ تبعية دائرية! البنود: ' + circularCheck.names.join(' → '));
            console.error('🔴 Cannot calculate dates due to circular dependency');
            return; // Stop calculation
        }

        // Calculate dates
        console.log('🔄 Recalculating all dates...');
        calculateDatesRecursive(itemsData, projectStart, weeklyHolidays);
        updateProjectEndDate(itemsData);
        console.log('✅ All dates recalculated');

        // Update subproject totals if in grouped view
        if (state.viewMode === 'grouped') {
            updateSubprojectTotals();
        }
    }

    function calculateDatesRecursive(itemsData, projectStart, weeklyHolidays) {
        // Calculate independent items
        itemsData.forEach((data, rowId) => {
            if (!data.predecessorId && !data.calculated) {
                data.startDate = new Date(projectStart);
                data.endDate = calculateWorkingEndDate(data.startDate, data.duration, weeklyHolidays);
                data.calculated = true;
                updateRowDates(data);
            }
        });

        // Calculate dependent items
        let changed = true;
        let iterations = 0;

        while (changed && iterations < 100) {
            changed = false;
            iterations++;

            itemsData.forEach((data, rowId) => {
                if (data.calculated || !data.predecessorId) return;

                const predecessor = itemsData.get(data.predecessorId);
                if (!predecessor || !predecessor.calculated) return;

                // Determine start date based on dependency type
                let baseDate;
                console.log(`📅 Calculating dates for ${rowId}:`, {
                    dependencyType: data.dependencyType,
                    lag: data.lag,
                    predecessorStart: predecessor.startDate?.toDateString(),
                    predecessorEnd: predecessor.endDate?.toDateString(),
                    predecessorId: data.predecessorId
                });

                if (data.dependencyType === 'start_to_start') {
                    // بعد البداية (Start to Start): يبدأ من نفس يوم بداية السابق
                    baseDate = new Date(predecessor.startDate);
                    console.log(`  ✅ START TO START: Using predecessor.startDate = ${predecessor.startDate?.toDateString()}`);

                    // Add/subtract lag if specified (positive = delay, negative = overlap)
                    if (data.lag && data.lag !== 0) {
                        const dateBefore = new Date(baseDate);
                        // Lag: موجب = تأخير، سالب = تداخل
                        baseDate = addWorkingDays(baseDate, data.lag, weeklyHolidays);
                        console.log(`  🚀 LAG APPLIED (SS): ${data.lag} days`, {
                            before: dateBefore.toDateString(),
                            after: baseDate.toDateString(),
                            lagValue: data.lag,
                            direction: data.lag > 0 ? 'تأخير (delay)' : 'تداخل (overlap)'
                        });
                    } else {
                        console.log(`  ⚠️ NO LAG (SS): lag value = ${data.lag}`);
                    }
                } else {
                    // بعد الانتهاء (End to Start): يبدأ من نفس يوم انتهاء السابق
                    // Start from the SAME day as predecessor ends, then add/subtract lag
                    console.log(`  ℹ️ END TO START: Using predecessor.endDate = ${predecessor.endDate?.toDateString()}`);
                    baseDate = new Date(predecessor.endDate);

                    console.log(`  ✅ END TO START: Start from same day = ${baseDate.toDateString()}`);

                    // Add/subtract lag days if specified (positive = delay, negative = overlap)
                    if (data.lag && data.lag !== 0) {
                        const dateBefore = new Date(baseDate);
                        baseDate = addWorkingDays(baseDate, data.lag, weeklyHolidays);
                        console.log(`  🚀 LAG APPLIED (FS): ${data.lag} days`, {
                            before: dateBefore.toDateString(),
                            after: baseDate.toDateString(),
                            lagValue: data.lag,
                            direction: data.lag > 0 ? 'تأخير (delay)' : 'تداخل (overlap)'
                        });
                    } else {
                        console.log(`  ⚠️ NO LAG (FS): lag value = ${data.lag}`);
                    }
                }

                data.startDate = baseDate;
                data.endDate = calculateWorkingEndDate(data.startDate, data.duration, weeklyHolidays);
                data.calculated = true;

                updateRowDates(data);
                changed = true;
            });
        }
    }

    function calculateWorkingEndDate(startDate, durationDays, weeklyHolidays) {
        if (durationDays <= 0) return new Date(startDate);

        let remainingDays = durationDays;
        let currentDate = new Date(startDate);
        let iterations = 0;
        const MAX_ITERATIONS = 10000; // Protection from infinite loop

        while (remainingDays > 0 && iterations < MAX_ITERATIONS) {
            currentDate.setDate(currentDate.getDate() + 1);
            if (!weeklyHolidays.includes(currentDate.getDay())) {
                remainingDays--;
            }
            iterations++;
        }

        if (iterations >= MAX_ITERATIONS) {
            console.error('⚠️ calculateWorkingEndDate: Max iterations reached!', {
                startDate: startDate,
                durationDays: durationDays,
                weeklyHolidays: weeklyHolidays
            });
            showNotification('error', '⚠️ خطأ في حساب التواريخ - المدة كبيرة جداً');
        }

        return currentDate;
    }

    /**
     * Add or subtract working days from a date
     * @param {Date} startDate - Starting date
     * @param {number} days - Number of days (positive = forward, negative = backward)
     * @param {Array} weeklyHolidays - Array of holiday day numbers
     * @returns {Date} - Resulting date
     */
    function addWorkingDays(startDate, days, weeklyHolidays) {
        if (days === 0) return new Date(startDate);

        let remainingDays = Math.abs(days);
        let currentDate = new Date(startDate);
        const direction = days > 0 ? 1 : -1; // 1 للأمام، -1 للخلف

        while (remainingDays > 0) {
            currentDate.setDate(currentDate.getDate() + direction);
            if (!weeklyHolidays.includes(currentDate.getDay())) {
                remainingDays--;
            }
        }

        return currentDate;
    }

    function getWeeklyHolidays() {
        const value = elements.weeklyHolidaysInput?.value || '';
        return value.split(',').map(v => parseInt(v)).filter(v => !isNaN(v));
    }

    function updateRowDates(data) {
        if (!data.row) return;

        const startInput = data.row.querySelector('.item-start-date');
        const endInput = data.row.querySelector('.item-end-date');

        if (startInput && data.startDate) {
            startInput.value = formatDate(data.startDate);
        }

        if (endInput && data.endDate) {
            endInput.value = formatDate(data.endDate);
        }
    }

    function updateProjectEndDate(itemsData) {
        let maxEndDate = null;
        let latestItem = null;

        itemsData.forEach((data, rowId) => {
            if (data.endDate && (!maxEndDate || data.endDate > maxEndDate)) {
                maxEndDate = data.endDate;
                latestItem = rowId;
            }
        });

        if (maxEndDate && elements.endDate) {
            const oldValue = elements.endDate.value;
            const newValue = formatDate(maxEndDate);
            elements.endDate.value = newValue;

            console.log(`📊 Project End Date Updated:`, {
                oldValue: oldValue,
                newValue: newValue,
                maxEndDate: maxEndDate.toDateString(),
                latestItem: latestItem
            });
        }
    }

    // ========================================
    // Sortable
    // ========================================
    function initSortable() {
        if (typeof Sortable === 'undefined' || !elements.container) return;

        Sortable.create(elements.container, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: () => {
                updateItemOrders();
                calculateAllDates();
            }
        });
    }

    // ========================================
    // Load Existing Items (Edit Mode)
    // ========================================
    function loadExistingItems() {
        if (!state.projectItems || state.projectItems.length === 0) {
            console.log('⚠️ No existing items to load');
            return;
        }

        console.log('🔄 Loading existing items...', {
            itemsCount: state.projectItems.length,
            firstItemFull: state.projectItems[0],  // 🔍 عرض أول item بالكامل
            items: state.projectItems.map(item => ({
                id: item.id,
                hasId: item.hasOwnProperty('id'),
                idType: typeof item.id,
                work_item: item.work_item?.name,
                predecessor: item.predecessor
            }))
        });

        // Create a mapping from old project_item.id to new rowId
        let oldToNewMapping = new Map();

        state.projectItems.forEach(projectItem => {
            const workItem = projectItem.work_item;
            if (!workItem) return;

            // Use the same naming convention as create mode: item_X_timestamp
            state.itemCounter++;
            const rowId = `item_${state.itemCounter}_${Date.now()}`;

            // Store mapping for predecessor resolution
            // Convert ID to number to ensure consistency
            const itemId = parseInt(projectItem.id);
            oldToNewMapping.set(itemId, rowId);

            console.log('📝 Created mapping:', {
                projectItemId: projectItem.id,
                itemIdType: typeof itemId,
                rowId: rowId,
                predecessor: projectItem.predecessor,
                predecessorType: typeof projectItem.predecessor
            });

            const row = createItemRow(rowId, workItem, {
                id: projectItem.id, // 🔑 Keep the original project_item ID for updates
                total_quantity: projectItem.total_quantity,
                estimated_daily_qty: projectItem.estimated_daily_qty,
                duration: projectItem.duration,
                notes: projectItem.notes,
                is_measurable: projectItem.is_measurable !== undefined ? projectItem.is_measurable : true,
                subproject_name: projectItem.subproject_name,
                lag: projectItem.lag,
                dependency_type: projectItem.dependency_type,
                start_date: projectItem.start_date,
                end_date: projectItem.end_date
            });

            // Store the old ID in dataset for predecessor resolution
            if (projectItem.predecessor) {
                row.dataset.pendingPredecessor = projectItem.predecessor;
                row.dataset.oldPredecessorId = projectItem.predecessor;

                // Debug logging
                console.log(`📌 Stored predecessor for row ${rowId}:`, {
                    rowId: rowId,
                    oldPredecessorId: projectItem.predecessor,
                    hasMapping: oldToNewMapping.has(projectItem.predecessor)
                });
            }

            // Store the old project_item.id for tracking
            row.dataset.oldProjectItemId = projectItem.id;

            elements.container.appendChild(row);
        });

        // Store mapping in a closure for setTimeout
        const mapping = oldToNewMapping;

        // Debug: Log the mapping
        console.log('📋 Mapping created:', Array.from(mapping.entries()));

        setTimeout(() => {
            // FIRST: Update all predecessor selects with current items
            updatePredecessors();

            // SECOND: Resolve predecessor IDs from old project_item.id to new rowId
            const rows = elements.container.querySelectorAll('tr[data-pending-predecessor]');

            console.log(`🔍 Found ${rows.length} rows with pending predecessors`);

            rows.forEach(row => {
                const oldPredecessorIdStr = row.dataset.oldPredecessorId;
                const oldPredecessorId = parseInt(oldPredecessorIdStr);
                const select = row.querySelector('.predecessor-select');

                console.log(`🔍 Processing row with oldPredecessorId:`, {
                    rawValue: oldPredecessorIdStr,
                    parsedValue: oldPredecessorId,
                    isNaN: isNaN(oldPredecessorId)
                });

                if (select && oldPredecessorId && !isNaN(oldPredecessorId)) {
                    // Try to find the new rowId
                    const newPredecessorRowId = mapping.get(oldPredecessorId);

                    console.log(`🔍 Attempting to map predecessor:`, {
                        oldPredecessorId: oldPredecessorId,
                        newPredecessorRowId: newPredecessorRowId,
                        availableInMapping: mapping.has(oldPredecessorId),
                        mappingSize: mapping.size,
                        mappingKeys: Array.from(mapping.keys())
                    });

                    if (newPredecessorRowId) {
                        const optionExists = select.querySelector(`option[value="${newPredecessorRowId}"]`);
                        console.log(`🔍 Checking if option exists:`, {
                            newPredecessorRowId: newPredecessorRowId,
                            optionExists: optionExists !== null,
                            allOptions: Array.from(select.options).map(opt => opt.value)
                        });

                        if (optionExists) {
                            select.value = newPredecessorRowId;
                            console.log(`✅ Mapped predecessor: ${oldPredecessorId} → ${newPredecessorRowId}`);
                        } else {
                            console.warn(`⚠️ Option not found for predecessor: ${oldPredecessorId} → ${newPredecessorRowId}`);
                        }
                    } else {
                        console.warn(`⚠️ Predecessor ID not found in mapping: ${oldPredecessorId}`);
                    }

                    delete row.dataset.pendingPredecessor;
                    delete row.dataset.oldPredecessorId;
                } else {
                    console.warn(`⚠️ Invalid predecessor data:`, {
                        oldPredecessorId: oldPredecessorId,
                        selectExists: select !== null
                    });
                }
            });

            // THIRD: Recalculate dates - this will overwrite existing dates with calculated ones
            console.log('🔄 Recalculating dates for existing items...');
            calculateAllDates();
            console.log('✅ Dates recalculated');

            // ✅ حفظ الترتيب الأصلي بعد تحميل البنود
            saveOriginalOrder();

            // Debug: Log final state
            console.log('✅ Finished loading existing items');
            const allSelects = elements.container.querySelectorAll('.predecessor-select');
            allSelects.forEach((select, index) => {
                if (select.value) {
                    console.log(`✅ Select ${index + 1} has value:`, select.value);
                }
            });
        }, 100);

        updateEmptyState();
    }

    // ========================================
    // Template & Draft Loading
    // ========================================
    async function loadTemplateItems(templateId) {
        try {
            // Show loading notification
            showNotification('info', 'جاري تحميل بيانات القالب...');

            console.log('🔵 Loading template items for template ID:', templateId);
            console.log('🔗 Fetching URL:', `/progress/project-templates/${templateId}/data`);
            
            const response = await fetch(`/progress/project-templates/${templateId}/data`);
            console.log('📡 Response status:', response.status);
            console.log('📡 Response ok:', response.ok);
            console.log('📡 Response headers:', response.headers);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Response error text:', errorText);
                throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
            }
            
            const data = await response.json();
            console.log('📦 Received data:', data);
            console.log('📊 Items count:', data.items ? data.items.length : 0);

            if (data.items && Array.isArray(data.items)) {
                const itemCount = data.items.length;
                const currentItemCount = elements.container.querySelectorAll('tr[data-item-id]').length;

                // Confirmation message
                let confirmMessage = `سيتم إضافة ${itemCount} بند من القالب`;

                if (currentItemCount > 0) {
                    confirmMessage += `\n\nيوجد حالياً ${currentItemCount} بند في المشروع.`;
                    confirmMessage += `\nسيتم إضافة البنود الجديدة إلى القائمة الموجودة.`;
                }

                confirmMessage += '\n\nهل تريد المتابعة؟';

                // Ask for confirmation
                if (!confirm(confirmMessage)) {
                    // Uncheck the checkbox if user cancels
                    const checkbox = document.querySelector(`.template-checkbox[value="${templateId}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                    showNotification('info', 'تم إلغاء العملية');
                    return;
                }

                // Load items with all template data
                let addedCount = 0;
                // Create mapping: work_item_id -> rowId (for predecessor resolution)
                const workItemToRowIdMap = new Map();
                // Store predecessor relationships: rowId -> predecessor work_item_id
                const predecessorMappings = new Map();

                data.items.forEach(templateItem => {
                    if (templateItem.work_item) {
                        // Add item with existing data from template (like loadDraftItems)
                        const item = templateItem.work_item;
                        state.itemCounter++;
                        const rowId = `item_${state.itemCounter}_${Date.now()}`;

                        // Store mapping
                        workItemToRowIdMap.set(templateItem.work_item_id, rowId);

                        // Store predecessor relationship if exists (predecessor is work_item_id)
                        if (templateItem.predecessor) {
                            predecessorMappings.set(rowId, templateItem.predecessor);
                        }

                        const row = createItemRow(rowId, item, {
                            total_quantity: templateItem.total_quantity || templateItem.default_quantity || 0,
                            estimated_daily_qty: templateItem.estimated_daily_qty || 0,
                            duration: templateItem.duration || 1,
                            notes: templateItem.notes || '',
                            subproject_name: templateItem.subproject_name || '',
                            lag: templateItem.lag || 0,
                            dependency_type: templateItem.dependency_type || 'end_to_start',
                            start_date: templateItem.start_date || '',
                            end_date: templateItem.end_date || '',
                            is_measurable: templateItem.is_measurable !== undefined ? templateItem.is_measurable : true,
                            item_label: templateItem.item_label || '',
                            daily_quantity: templateItem.daily_quantity || '',
                            shift: templateItem.shift || ''
                        });

                        elements.container.appendChild(row);
                        addedCount++;
                    }
                });

                // Update predecessors dropdowns first
                updateItemOrders();
                updatePredecessors();

                // Now set predecessor values (convert work_item_id to rowId)
                predecessorMappings.forEach((predecessorWorkItemId, currentRowId) => {
                    const predecessorRowId = workItemToRowIdMap.get(predecessorWorkItemId);
                    if (predecessorRowId) {
                        const currentRow = document.querySelector(`tr[data-item-id="${currentRowId}"]`);
                        if (currentRow) {
                            const predecessorSelect = currentRow.querySelector('.predecessor-select');
                            if (predecessorSelect) {
                                predecessorSelect.value = predecessorRowId;
                                // Trigger change event to update dependencies
                                predecessorSelect.dispatchEvent(new Event('change'));
                            }
                        }
                    }
                });

                calculateAllDates();
                updateEmptyState();

                // Apply template settings (working_days, daily_work_hours, weekly_holidays)
                if (data.working_days) {
                    const workingDaysInput = document.getElementById('working_days');
                    if (workingDaysInput) {
                        workingDaysInput.value = data.working_days;
                    }
                }

                if (data.daily_work_hours) {
                    const dailyWorkHoursInput = document.getElementById('daily_work_hours');
                    if (dailyWorkHoursInput) {
                        dailyWorkHoursInput.value = data.daily_work_hours;
                    }
                }

                if (data.weekly_holidays) {
                    const weeklyHolidaysInput = document.getElementById('weekly-holidays-input');
                    if (weeklyHolidaysInput) {
                        weeklyHolidaysInput.value = data.weekly_holidays;
                        // Update checkboxes
                        const holidaysArray = data.weekly_holidays.split(',');
                        document.querySelectorAll('.holiday-checkbox').forEach(checkbox => {
                            checkbox.checked = holidaysArray.includes(checkbox.value);
                        });
                    }
                }

                // Apply project_type_id if available
                if (data.project_type_id) {
                    const projectTypeSelect = document.getElementById('project_type_id');
                    if (projectTypeSelect) {
                        projectTypeSelect.value = data.project_type_id;
                    }
                }

                showNotification('success', `✅ تم إضافة ${addedCount} بند من القالب بنجاح`);
            } else {
                console.warn('⚠️ No items found in template data');
                showNotification('warning', 'القالب فارغ - لا توجد بنود للإضافة');

                // Uncheck the checkbox
                const checkbox = document.querySelector(`.template-checkbox[value="${templateId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                }
            }
        } catch (error) {
            console.error('❌ Template loading error:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
            showNotification('error', `حدث خطأ في تحميل القالب: ${error.message}`);

            // Uncheck the checkbox on error
            const checkbox = document.querySelector(`.template-checkbox[value="${templateId}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
        }
    }

    async function loadDraftItems(draftId) {
        try {
            // Show loading notification
            showNotification('info', 'جاري تحميل بيانات المسودة...');

            const response = await fetch(`/progress/projects/${draftId}/items-data`);

            if (!response.ok) {
                throw new Error('Failed to load draft items');
            }

            const data = await response.json();

            if (data.items && Array.isArray(data.items)) {
                const itemCount = data.items.length;
                const currentItemCount = elements.container.querySelectorAll('tr[data-item-id]').length;

                // Confirmation message
                let confirmMessage = `سيتم إضافة ${itemCount} بند من المسودة`;

                if (currentItemCount > 0) {
                    confirmMessage += `\n\nيوجد حالياً ${currentItemCount} بند في المشروع.`;
                    confirmMessage += `\nسيتم إضافة البنود الجديدة إلى القائمة الموجودة.`;
                }

                confirmMessage += '\n\nهل تريد المتابعة؟';

                // Ask for confirmation
                if (!confirm(confirmMessage)) {
                    // Uncheck the checkbox if user cancels
                    const checkbox = document.querySelector(`.template-checkbox[value="${draftId}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                    showNotification('info', 'تم إلغاء العملية');
                    return;
                }

                // Load items
                let addedCount = 0;
                data.items.forEach(projectItem => {
                    if (projectItem.work_item) {
                        // Add item with existing data
                        const item = projectItem.work_item;
                        state.itemCounter++;
                        const rowId = `item_${state.itemCounter}_${Date.now()}`;

                        const row = createItemRow(rowId, item, {
                            total_quantity: projectItem.total_quantity,
                            estimated_daily_qty: projectItem.estimated_daily_qty,
                            duration: projectItem.duration,
                            notes: projectItem.notes,
                            subproject_name: projectItem.subproject_name,
                            lag: projectItem.lag,
                            dependency_type: projectItem.dependency_type
                        });

                        elements.container.appendChild(row);
                        addedCount++;
                    }
                });

                updateItemOrders();
                updatePredecessors();
                calculateAllDates();
                updateEmptyState();

                showNotification('success', `✅ تم تحميل ${addedCount} بند من المسودة بنجاح`);
            } else {
                showNotification('warning', 'المسودة فارغة - لا توجد بنود للإضافة');

                // Uncheck the checkbox
                const checkbox = document.querySelector(`.template-checkbox[value="${draftId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                }
            }
        } catch (error) {
            console.error('Draft loading error:', error);
            showNotification('error', 'حدث خطأ في تحميل المسودة');

            // Uncheck the checkbox on error
            const checkbox = document.querySelector(`.template-checkbox[value="${draftId}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
        }
    }

    // ========================================
    // View Mode Switching (Safe Implementation)
    // ========================================
    function switchToFlatView() {
        state.viewMode = 'flat';

        // Update button states
        document.getElementById('flat-view-btn')?.classList.add('active');
        document.getElementById('grouped-view-btn')?.classList.remove('active');

        // Render flat view
        renderFlatView();

        // Re-initialize sortable
        initSortable();

        showNotification('success', 'العرض العادي مفعّل ✅');
        console.log('✅ Switched to flat view');
    }

    function switchToGroupedView() {
        state.viewMode = 'grouped';

        // Log predecessor relationships before switching
        const rows = elements.container.querySelectorAll('tr[data-item-id]');
        console.log('🔄 Switching to grouped view - Current relationships:',
            Array.from(rows).map(row => ({
                itemId: row.dataset.itemId,
                predecessor: row.querySelector('.predecessor-select')?.value || 'none',
                dependencyType: row.querySelector('select[name*="[dependency_type]"]')?.value || 'end_to_start',
                lag: row.querySelector('.lag-input')?.value || '0'
            }))
        );

        // Update button states
        document.getElementById('grouped-view-btn')?.classList.add('active');
        document.getElementById('flat-view-btn')?.classList.remove('active');

        // Render grouped view
        renderGroupedView();

        showNotification('success', 'العرض حسب الفئات مفعّل ✅');
        console.log('✅ Switched to grouped view');
    }

    // ========================================
    // Grouped View Rendering
    // ========================================
    function renderGroupedView() {
        if (!elements.container) return;

        // Get all current rows in DOM order (preserve current visual order)
        const rows = Array.from(elements.container.querySelectorAll('tr[data-item-id]'));

        if (rows.length === 0) {
            return; // Nothing to group
        }

        // ✅ حفظ الترتيب الأصلي قبل التجميع إذا لم يكن محفوظاً
        if (!state.originalItemOrder) {
            saveOriginalOrder();
        }

        // ✅ استخدام الترتيب الأصلي لترتيب الصفوف قبل التجميع
        const sortedRows = state.originalItemOrder ? restoreOriginalOrder(rows) : rows;

        // ✅ تحديث item_order بناءً على الترتيب الأصلي (وليس الترتيب الحالي)
        sortedRows.forEach((row, index) => {
            const orderInput = row.querySelector('input[name*="[item_order]"]');
            if (orderInput) {
                orderInput.value = index;
            }
        });

        // Group items by category (preserving original order within groups)
        const groupedItems = groupItemsByCategory(sortedRows);

        // Clear container
        elements.container.innerHTML = '';

        // ✅ Sort groups by the minimum item_order in each group to preserve overall order
        const sortedGroups = Array.from(groupedItems.entries()).sort((a, b) => {
            // Get minimum item_order from each group
            const getMinOrder = (rows) => {
                const orders = rows.map(row => {
                    const orderInput = row.querySelector('input[name*="[item_order]"]');
                    return parseInt(orderInput?.value || '999999') || 999999;
                });
                return Math.min(...orders);
            };

            const minOrderA = getMinOrder(a[1]);
            const minOrderB = getMinOrder(b[1]);
            return minOrderA - minOrderB;
        });

        // Render each category group in order
        sortedGroups.forEach(([categoryName, categoryRows]) => {
            const categoryGroup = createCategoryGroup(categoryName, categoryRows);
            elements.container.appendChild(categoryGroup);
        });

        // Load manual subproject quantities from database
        loadSubprojectQuantities();

        // Re-initialize sortable for grouped view
        initSortableForGroups();

        // Verify relationships after rendering
        const allRowsAfter = document.querySelectorAll('.sortable-category-items tr[data-item-id]');
        console.log('✅ Grouped view rendered - Final relationships:',
            Array.from(allRowsAfter).map(row => ({
                itemId: row.dataset.itemId,
                predecessor: row.querySelector('.predecessor-select')?.value || 'none',
                dependencyType: row.querySelector('select[name*="[dependency_type]"]')?.value || 'end_to_start',
                lag: row.querySelector('.lag-input')?.value || '0'
            }))
        );
    }

    function groupItemsByCategory(rows) {
        // ✅ Don't sort - preserve DOM order (rows are already in correct order)
        // Use Map to preserve insertion order
        const groups = new Map();

        rows.forEach(row => {
            // Get subproject name from input, or use "غير مصنف"
            const subprojectInput = row.querySelector('.subproject-input');
            const subprojectName = subprojectInput?.value.trim() || 'بدون مشروع فرعي';

            if (!groups.has(subprojectName)) {
                groups.set(subprojectName, []);
            }

            groups.get(subprojectName).push(row);
        });

        return groups;
    }

    function calculateSubprojectTotals(rows) {
        let totalQty = 0;
        let startDate = null;
        let endDate = null;
        let duration = 0;
        let units = new Map(); // Track unit frequencies

        rows.forEach(row => {
            // Sum total quantities
            const qtyInput = row.querySelector('.total-quantity');
            if (qtyInput) {
                totalQty += parseFloat(qtyInput.value || 0);
            }

            // Track units from work items
            const workItemId = row.dataset.workItemId;
            const workItem = state.workItems.find(item => item.id == workItemId);
            if (workItem && workItem.unit) {
                const currentCount = units.get(workItem.unit) || 0;
                units.set(workItem.unit, currentCount + 1);
            }

            // Get min start date
            const startInput = row.querySelector('.item-start-date');
            if (startInput && startInput.value) {
                if (!startDate || startInput.value < startDate) {
                    startDate = startInput.value;
                }
            }

            // Get max end date
            const endInput = row.querySelector('.item-end-date');
            if (endInput && endInput.value) {
                if (!endDate || endInput.value > endDate) {
                    endDate = endInput.value;
                }
            }
        });

        // Calculate duration in days
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        }

        // Determine the most common unit
        let commonUnit = '';
        let maxCount = 0;
        units.forEach((count, unit) => {
            if (count > maxCount) {
                maxCount = count;
                commonUnit = unit;
            }
        });

        // If multiple units exist, show all of them
        let unitDisplay = commonUnit;
        if (units.size > 1) {
            unitDisplay = Array.from(units.keys()).join('/');
        }

        // Debug log for unit calculation
        if (rows.length > 0 && unitDisplay) {
            console.log('📦 Subproject units calculated:', {
                itemCount: rows.length,
                unitsFound: Array.from(units.entries()),
                displayUnit: unitDisplay
            });
        }

        return {
            totalQty,
            unit: unitDisplay,
            startDate,
            endDate,
            duration
        };
    }

    function updateSubprojectTotals() {
        // Get all category groups
        document.querySelectorAll('.category-group-row').forEach(groupRow => {
            const categoryHeader = groupRow.querySelector('.category-header');
            if (!categoryHeader) return;

            const categoryName = categoryHeader.dataset.category;
            const categoryId = 'cat_' + categoryName.replace(/[^a-zA-Z0-9]/g, '_');
            const tbody = groupRow.querySelector('.sortable-category-items');

            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr[data-item-id]'));
            const totals = calculateSubprojectTotals(rows);

            // Update UI - Duration
            const datesSpan = groupRow.querySelector(`.subproject-dates-${categoryId}`);
            if (datesSpan) {
                datesSpan.textContent = totals.duration > 0 ? `${totals.duration} يوم` : '-';
            }

            // Update UI - Start Date Display
            const startDisplay = groupRow.querySelector(`.subproject-start-display-${categoryId}`);
            if (startDisplay) {
                startDisplay.textContent = totals.startDate || '-';
            }

            // Update UI - End Date Display
            const endDisplay = groupRow.querySelector(`.subproject-end-display-${categoryId}`);
            if (endDisplay) {
                endDisplay.textContent = totals.endDate || '-';
            }

            // Update hidden inputs for dates
            const startDateInput = groupRow.querySelector(`.subproject-start-date-${categoryId}`);
            if (startDateInput) {
                startDateInput.value = totals.startDate || '';
            }

            const endDateInput = groupRow.querySelector(`.subproject-end-date-${categoryId}`);
            if (endDateInput) {
                endDateInput.value = totals.endDate || '';
            }

            // Update hidden quantity input from manual input field
            const qtyManualInput = groupRow.querySelector(`.subproject-quantity-input-${categoryId}`);
            const qtyHiddenInput = groupRow.querySelector(`.subproject-quantity-${categoryId}`);
            if (qtyManualInput && qtyHiddenInput) {
                qtyHiddenInput.value = qtyManualInput.value || 0;
            }

            // Update hidden unit input from manual input field
            const unitManualInput = groupRow.querySelector(`.subproject-unit-input-${categoryId}`);
            const unitHiddenInput = groupRow.querySelector(`.subproject-unit-${categoryId}`);
            if (unitManualInput && unitHiddenInput) {
                unitHiddenInput.value = unitManualInput.value || '';
            }
        });
    }

    function loadSubprojectQuantities() {
        // Load manual quantities and units from state.subprojects (loaded from database)
        if (!state.subprojects || state.subprojects.length === 0) {
            console.log('⚠️ No subprojects to load');
            return;
        }

        console.log('📦 Loading subproject quantities and units:', state.subprojects);

        state.subprojects.forEach(subproject => {
            const categoryId = 'cat_' + subproject.name.replace(/[^a-zA-Z0-9]/g, '_');
            const qtyInput = document.querySelector(`.subproject-quantity-input-${categoryId}`);
            const qtyHidden = document.querySelector(`.subproject-quantity-${categoryId}`);
            const unitInput = document.querySelector(`.subproject-unit-input-${categoryId}`);
            const unitHidden = document.querySelector(`.subproject-unit-${categoryId}`);
            const weightInput = document.querySelector(`.subproject-weight-input-${categoryId}`);
            const weightHidden = document.querySelector(`.subproject-weight-${categoryId}`);

            console.log(`🔍 Loading subproject: ${subproject.name}`, {
                categoryId,
                hasQtyInput: !!qtyInput,
                hasQtyHidden: !!qtyHidden,
                hasUnitInput: !!unitInput,
                hasUnitHidden: !!unitHidden,
                hasWeightInput: !!weightInput,
                hasWeightHidden: !!weightHidden,
                unit: subproject.unit,
                total_quantity: subproject.total_quantity,
                weight: subproject.weight
            });

            if (qtyInput && subproject.total_quantity !== null && subproject.total_quantity !== undefined) {
                qtyInput.value = parseFloat(subproject.total_quantity).toFixed(2);
                console.log(`  ✅ Updated quantity input: ${qtyInput.value}`);
            }

            if (qtyHidden && subproject.total_quantity !== null && subproject.total_quantity !== undefined) {
                qtyHidden.value = subproject.total_quantity;
                console.log(`  ✅ Updated quantity hidden: ${qtyHidden.value}`);
            }

            // Load unit value (even if empty string, to clear any auto-calculated value)
            if (unitInput !== null) {
                unitInput.value = subproject.unit || '';
                console.log(`  ✅ Updated unit input: "${unitInput.value}"`);
            } else {
                console.warn(`  ⚠️ Unit input not found for ${subproject.name}`);
            }

            if (unitHidden !== null) {
                unitHidden.value = subproject.unit || '';
                console.log(`  ✅ Updated unit hidden: "${unitHidden.value}"`);
            } else {
                console.warn(`  ⚠️ Unit hidden not found for ${subproject.name}`);
            }

            // Load weight value
            if (weightInput !== null) {
                weightInput.value = subproject.weight || 1.00;
                console.log(`  ✅ Updated weight input: ${weightInput.value}`);
            }

            if (weightHidden !== null) {
                weightHidden.value = subproject.weight || 1.00;
                console.log(`  ✅ Updated weight hidden: ${weightHidden.value}`);
            }
        });
    }

    function createCategoryGroup(categoryName, rows) {
        // Create group container
        const groupDiv = document.createElement('tr');
        groupDiv.className = 'category-group-row';
        const categoryId = 'cat_' + categoryName.replace(/[^a-zA-Z0-9]/g, '_');

        // Calculate subproject totals
        const subprojectTotals = calculateSubprojectTotals(rows);

        // Check if subproject exists in database and use saved unit if available
        // This ensures that saved units are used instead of auto-calculated ones
        const savedSubproject = state.subprojects?.find(sp => sp.name === categoryName);
        if (savedSubproject) {
            // Use saved unit if available, otherwise keep auto-calculated unit
            if (savedSubproject.unit !== null && savedSubproject.unit !== undefined) {
                subprojectTotals.unit = savedSubproject.unit;
            }
            // Also use saved quantity if available
            if (savedSubproject.total_quantity !== null && savedSubproject.total_quantity !== undefined) {
                subprojectTotals.totalQty = parseFloat(savedSubproject.total_quantity);
            }
        }

        groupDiv.innerHTML = `
            <td colspan="14" class="p-0">
                <div class="category-group">
                    <div class="category-header" data-category="${escapeHtml(categoryName)}">
                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                            <div class="category-title">
                                <span class="category-icon">${getCategoryIcon(categoryName)}</span>
                                <span class="fw-bold">${escapeHtml(categoryName)}</span>
                            </div>
                            
                            <div class="category-data-inline d-flex gap-3 align-items-center">
                                <div class="data-item">
                                    <i class="fas fa-list text-white-50 me-1"></i>
                                    <strong>${rows.length}</strong> بند
                                </div>
                                <div class="vr bg-white opacity-25"></div>
                                <div class="data-item">
                                    <i class="fas fa-boxes text-white-50 me-1"></i>
                                    <span class="text-white-50 small">الكمية:</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control form-control-sm d-inline-block subproject-quantity-input-${categoryId}" 
                                           style="width: 100px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; font-weight: bold;"
                                           value="${subprojectTotals.totalQty.toFixed(2)}"
                                           data-category-id="${categoryId}">
                                    <input type="text" 
                                           class="form-control form-control-sm d-inline-block subproject-unit-input-${categoryId}" 
                                           style="width: 80px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; font-weight: bold; margin-left: 5px;"
                                           placeholder="الوحدة"
                                           value="${subprojectTotals.unit ? escapeHtml(subprojectTotals.unit) : ''}"
                                           data-category-id="${categoryId}">
                                </div>
                                <div class="vr bg-white opacity-25"></div>
                                <div class="data-item" title="من ${subprojectTotals.startDate || '-'} إلى ${subprojectTotals.endDate || '-'}">
                                    <i class="fas fa-calendar-alt text-white-50 me-1"></i>
                                    <span class="text-white-50 small">المدة:</span>
                                    <strong class="subproject-dates-${categoryId}">${subprojectTotals.duration > 0 ? subprojectTotals.duration + ' يوم' : '-'}</strong>
                                </div>
                                <div class="vr bg-white opacity-25"></div>
                                <div class="data-item">
                                    <i class="fas fa-calendar-check text-white-50 me-1"></i>
                                    <span class="text-white-50 small">من:</span>
                                    <strong class="subproject-start-display-${categoryId}">${subprojectTotals.startDate || '-'}</strong>
                                    <span class="text-white-50 small ms-2">إلى:</span>
                                    <strong class="subproject-end-display-${categoryId}">${subprojectTotals.endDate || '-'}</strong>
                                </div>
                                <div class="vr bg-white opacity-25"></div>
                                <div class="data-item">
                                    <i class="fas fa-weight text-white-50 me-1"></i>
                                    <span class="text-white-50 small">الوزن:</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control form-control-sm d-inline-block subproject-weight-input-${categoryId}" 
                                           style="width: 80px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; font-weight: bold;"
                                           value="${subprojectTotals.weight || 1.00}"
                                           data-category-id="${categoryId}">
                                </div>
                            </div>
                        </div>
                        
                        <span class="category-toggle ms-3">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>
                    
                    <!-- Hidden inputs for subproject data -->
                    <input type="hidden" name="subprojects[${categoryId}][name]" value="${escapeHtml(categoryName)}">
                    <input type="hidden" class="subproject-start-date-${categoryId}" name="subprojects[${categoryId}][start_date]" value="${subprojectTotals.startDate || ''}">
                    <input type="hidden" class="subproject-end-date-${categoryId}" name="subprojects[${categoryId}][end_date]" value="${subprojectTotals.endDate || ''}">
                    <input type="hidden" class="subproject-quantity-${categoryId}" name="subprojects[${categoryId}][total_quantity]" value="${subprojectTotals.totalQty}">
                    <input type="hidden" class="subproject-unit-${categoryId}" name="subprojects[${categoryId}][unit]" value="${subprojectTotals.unit ? escapeHtml(subprojectTotals.unit) : ''}">
                    <input type="hidden" class="subproject-weight-${categoryId}" name="subprojects[${categoryId}][weight]" value="${subprojectTotals.weight || 1.00}">
                    
                    <div class="category-body">
                        <!-- Bulk Actions Toolbar for Subproject -->
                        <div class="p-2 bg-light border-bottom d-flex align-items-center gap-2 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input subproject-select-all" 
                                       type="checkbox" 
                                       id="selectAll_${categoryId}"
                                       data-category="${escapeHtml(categoryName)}">
                                <label class="form-check-label small" for="selectAll_${categoryId}">
                                    <strong>تحديد الكل</strong>
                                </label>
                            </div>
                            
                            <div class="vr"></div>
                            
                            <select class="form-select form-select-sm subproject-bulk-action" style="width: auto; min-width: 180px;" data-category="${escapeHtml(categoryName)}">
                                <option value="">-- عمليات جماعية --</option>
                                <option value="delete">🗑️ حذف الكل</option>
                                <option value="duplicate">📋 نسخ الكل</option>
                                <option value="move">📁 نقل لمشروع فرعي آخر</option>
                                <option value="export">📊 تصدير (CSV)</option>
                                <option value="clear-notes">🧹 مسح الملاحظات</option>
                                <option value="set-duration">⏱️ تعيين مدة موحدة</option>
                            </select>
                            
                            <button type="button" 
                                    class="btn btn-sm btn-primary subproject-bulk-execute" 
                                    data-category="${escapeHtml(categoryName)}"
                                    title="تنفيذ العملية">
                                <i class="fas fa-play"></i>
                                تنفيذ
                            </button>
                            
                            <span class="ms-auto text-muted small">
                                <span class="selected-count-${categoryId}">0</span> محدد
                            </span>
                        </div>
                        
                        <table class="table table-bordered table-hover align-middle category-items-table mb-0">
                            <tbody class="sortable-category-items" data-category="${escapeHtml(categoryName)}">
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
        `;

        // ✅ Don't sort - preserve DOM order (rows are already in correct order)
        // Add items to category body
        const tbody = groupDiv.querySelector('.sortable-category-items');
        rows.forEach((row, index) => {
            // Save predecessor, dependency type, and lag values before cloning
            const predecessorSelect = row.querySelector('.predecessor-select');
            const dependencyTypeSelect = row.querySelector('select[name*="[dependency_type]"]');
            const lagInput = row.querySelector('.lag-input');

            const predecessorValue = predecessorSelect?.value || '';
            const dependencyTypeValue = dependencyTypeSelect?.value || 'end_to_start';
            const lagValue = lagInput?.value || '0';

            // Clone the row
            const clonedRow = row.cloneNode(true);
            tbody.appendChild(clonedRow);

            // Restore values after cloning
            const newRow = tbody.lastChild;
            const newPredecessorSelect = newRow.querySelector('.predecessor-select');
            const newDependencyTypeSelect = newRow.querySelector('select[name*="[dependency_type]"]');
            const newLagInput = newRow.querySelector('.lag-input');

            if (newPredecessorSelect) newPredecessorSelect.value = predecessorValue;
            if (newDependencyTypeSelect) newDependencyTypeSelect.value = dependencyTypeValue;
            if (newLagInput) newLagInput.value = lagValue;

            // Log restored values
            if (predecessorValue) {
                console.log(`📌 Restored relationship for ${newRow.dataset.itemId}:`, {
                    predecessor: predecessorValue,
                    dependencyType: dependencyTypeValue,
                    lag: lagValue
                });
            }

            // Re-attach event listeners
            const workItemId = newRow.dataset.workItemId;
            const workItem = state.workItems.find(item => item.id == workItemId);
            if (workItem) {
                attachRowEventListeners(newRow, workItem, newRow.dataset.itemId);
            }

            // ✅ Preserve original item_order (don't change it when grouping)
            // The item_order should remain as it was in flat view
            const originalOrderInput = row.querySelector('input[name*="[item_order]"]');
            if (originalOrderInput) {
                const newOrderInput = newRow.querySelector('input[name*="[item_order]"]');
                if (newOrderInput) {
                    newOrderInput.value = originalOrderInput.value;
                }
            }
        });

        // Add collapse/expand functionality
        const header = groupDiv.querySelector('.category-header');
        header.addEventListener('click', function (e) {
            // Don't toggle if clicking on inputs or buttons
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON' || e.target.closest('input') || e.target.closest('button')) {
                return;
            }
            toggleCategoryGroup(this);
        });

        // Attach bulk actions event listeners
        attachSubprojectBulkActions(groupDiv, categoryName, categoryId);

        // Add event listener for manual quantity input
        const quantityInput = groupDiv.querySelector(`.subproject-quantity-input-${categoryId}`);
        if (quantityInput) {
            quantityInput.addEventListener('input', function () {
                const hiddenInput = groupDiv.querySelector(`.subproject-quantity-${categoryId}`);
                if (hiddenInput) {
                    hiddenInput.value = this.value;
                }
            });

            // Prevent event propagation to avoid toggling category
            quantityInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // Add event listener for manual unit input
        const unitInput = groupDiv.querySelector(`.subproject-unit-input-${categoryId}`);
        if (unitInput) {
            unitInput.addEventListener('input', function () {
                const hiddenInput = groupDiv.querySelector(`.subproject-unit-${categoryId}`);
                if (hiddenInput) {
                    hiddenInput.value = this.value;
                }
            });

            // Prevent event propagation to avoid toggling category
            unitInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        // Add event listener for manual weight input
        const weightInput = groupDiv.querySelector(`.subproject-weight-input-${categoryId}`);
        if (weightInput) {
            weightInput.addEventListener('input', function () {
                const hiddenInput = groupDiv.querySelector(`.subproject-weight-${categoryId}`);
                if (hiddenInput) {
                    hiddenInput.value = this.value || 1.00;
                }
            });

            // Prevent event propagation to avoid toggling category
            weightInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        return groupDiv;
    }

    // ========================================
    // Subproject Bulk Actions
    // ========================================
    function attachSubprojectBulkActions(groupDiv, categoryName, categoryId) {
        const selectAllCheckbox = groupDiv.querySelector('.subproject-select-all');
        const bulkActionSelect = groupDiv.querySelector('.subproject-bulk-action');
        const executeBtn = groupDiv.querySelector('.subproject-bulk-execute');
        const tbody = groupDiv.querySelector('.sortable-category-items');

        // Select All functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                const checkboxes = tbody.querySelectorAll('.item-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSubprojectSelectedCount(categoryId, tbody);
            });
        }

        // Update count when individual checkboxes change
        tbody.addEventListener('change', function (e) {
            if (e.target.classList.contains('item-checkbox')) {
                updateSubprojectSelectedCount(categoryId, tbody);

                // Update select all state
                const allCheckboxes = tbody.querySelectorAll('.item-checkbox');
                const checkedCheckboxes = tbody.querySelectorAll('.item-checkbox:checked');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
                }
            }
        });

        // Execute bulk action
        if (executeBtn) {
            executeBtn.addEventListener('click', function () {
                const action = bulkActionSelect?.value;

                if (!action) {
                    showNotification('warning', 'الرجاء اختيار عملية أولاً');
                    return;
                }

                const selectedRows = Array.from(tbody.querySelectorAll('.item-checkbox:checked'))
                    .map(cb => cb.closest('tr[data-item-id]'))
                    .filter(row => row !== null);

                if (selectedRows.length === 0) {
                    showNotification('warning', `الرجاء تحديد بنود من "${categoryName}" أولاً`);
                    return;
                }

                executeSubprojectBulkAction(action, selectedRows, categoryName, tbody, selectAllCheckbox, bulkActionSelect);
            });
        }
    }

    function updateSubprojectSelectedCount(categoryId, tbody) {
        const countSpan = document.querySelector(`.selected-count-${categoryId}`);
        if (countSpan) {
            const checkedCount = tbody.querySelectorAll('.item-checkbox:checked').length;
            countSpan.textContent = checkedCount;
        }
    }

    function executeSubprojectBulkAction(action, selectedRows, categoryName, tbody, selectAllCheckbox, bulkActionSelect) {
        switch (action) {
            case 'delete':
                if (!confirm(`هل أنت متأكد من حذف ${selectedRows.length} بند من "${categoryName}"؟`)) {
                    return;
                }
                selectedRows.forEach(row => row.remove());
                updateItemOrders();
                calculateAllDates();
                updateEmptyState();
                showNotification('success', `✅ تم حذف ${selectedRows.length} بند من "${categoryName}"`);
                break;

            case 'duplicate':
                let duplicated = 0;
                selectedRows.forEach(row => {
                    const workItemId = row.dataset.workItemId;
                    const workItem = state.workItems.find(item => item.id == workItemId);
                    if (workItem) {
                        duplicateItem(row, workItem);
                        duplicated++;
                    }
                });
                showNotification('success', `✅ تم نسخ ${duplicated} بند في "${categoryName}"`);
                break;

            case 'move':
                // Store rows, category name, and UI elements for modal
                pendingMoveRows = selectedRows;
                pendingMoveCategoryName = categoryName;
                pendingMoveSelectAllCheckbox = selectAllCheckbox;
                pendingMoveBulkActionSelect = bulkActionSelect;

                // Get unique subprojects excluding current category
                const subprojects = getUniqueSubprojects().filter(sp => sp !== categoryName);

                // Update modal with item count and category info
                const itemsCountEl = document.getElementById('moveItemsCount');
                if (itemsCountEl) {
                    itemsCountEl.textContent = selectedRows.length;
                }

                // Update modal title to show source category
                const modalTitle = document.querySelector('#moveToSubprojectModalLabel');
                if (modalTitle) {
                    modalTitle.innerHTML = `<i class="fas fa-folder-open me-2"></i> نقل البنود من "${categoryName}" إلى مشروع فرعي`;
                }

                // Populate subprojects select
                const subprojectSelect = document.getElementById('subprojectSelect');
                const newSubprojectInput = document.getElementById('newSubprojectName');

                if (subprojectSelect) {
                    // Clear existing options except the first one
                    subprojectSelect.innerHTML = '<option value="">-- مشروع جديد --</option>';

                    // Add existing subprojects (excluding current category)
                    subprojects.forEach(subproject => {
                        const option = document.createElement('option');
                        option.value = subproject;
                        option.textContent = subproject;
                        subprojectSelect.appendChild(option);
                    });
                }

                // Clear new subproject input
                if (newSubprojectInput) {
                    newSubprojectInput.value = '';
                }

                // Show modal using Bootstrap
                const modalElement = document.getElementById('moveToSubprojectModal');
                if (modalElement && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else if (modalElement) {
                    // Fallback if Bootstrap is not available
                    modalElement.style.display = 'block';
                    modalElement.classList.add('show');
                }

                // Don't reset select and checkbox here - will be done after move is confirmed
                return; // Return early, actual move will happen in modal confirm handler

            case 'export':
                bulkExport(selectedRows);
                showNotification('success', `✅ تم تصدير ${selectedRows.length} بند من "${categoryName}"`);
                break;

            case 'clear-notes':
                if (!confirm(`هل تريد مسح الملاحظات من ${selectedRows.length} بند في "${categoryName}"؟`)) {
                    return;
                }
                selectedRows.forEach(row => {
                    const notesTextarea = row.querySelector('textarea[name*="notes"]');
                    if (notesTextarea) {
                        notesTextarea.value = '';
                    }
                });
                showNotification('success', `✅ تم مسح الملاحظات من ${selectedRows.length} بند`);
                break;

            case 'set-duration':
                const duration = prompt(`تعيين مدة موحدة لـ ${selectedRows.length} بند في "${categoryName}":\n\nأدخل المدة بالأيام:`);
                if (duration !== null && !isNaN(duration) && parseInt(duration) > 0) {
                    selectedRows.forEach(row => {
                        const durationInput = row.querySelector('.duration-input');
                        if (durationInput) {
                            durationInput.value = parseInt(duration);
                        }
                    });
                    calculateAllDates();
                    showNotification('success', `✅ تم تعيين المدة ${duration} يوم لـ ${selectedRows.length} بند`);
                }
                break;
        }

        // Reset selection
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        if (bulkActionSelect) {
            bulkActionSelect.value = '';
        }

        // Update selected count
        const categoryId = 'cat_' + categoryName.replace(/[^a-zA-Z0-9]/g, '_');
        updateSubprojectSelectedCount(categoryId, tbody);
    }

    function toggleCategoryGroup(headerElement) {
        // Find the category-body element within the same category-group
        const categoryGroup = headerElement.closest('.category-group');
        const body = categoryGroup.querySelector('.category-body');
        const toggle = headerElement.querySelector('.category-toggle i');

        if (!body) return;

        body.classList.toggle('collapsed');
        headerElement.classList.toggle('collapsed');

        if (body.classList.contains('collapsed')) {
            toggle.className = 'fas fa-chevron-right';
        } else {
            toggle.className = 'fas fa-chevron-down';
        }
    }

    function getCategoryIcon(categoryName) {
        // Default icon for subprojects
        if (categoryName === 'بدون مشروع فرعي') {
            return '📋';
        }

        // Return folder icon for all subprojects
        return '📁';
    }

    function initSortableForGroups() {
        // Initialize sortable for each category tbody
        document.querySelectorAll('.sortable-category-items').forEach(tbody => {
            if (typeof Sortable !== 'undefined') {
                Sortable.create(tbody, {
                    group: 'shared', // Allow dragging between categories
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: () => {
                        updateItemOrders();
                        calculateAllDates();
                    }
                });
            }
        });
    }

    function renderFlatView() {
        if (!elements.container) return;

        // Get all rows preserving DOM order (current visual order)
        const allRows = [];

        // Check if we're coming from grouped view
        const categoryGroups = document.querySelectorAll('.category-group-row');

        if (categoryGroups.length > 0) {
            // Coming from grouped view: preserve DOM order within each group
            categoryGroups.forEach(categoryGroup => {
                const categoryRows = categoryGroup.querySelectorAll('.sortable-category-items tr[data-item-id]');
                categoryRows.forEach(row => {
                    allRows.push(row);
                });
            });
        } else {
            // Already in flat view: preserve current DOM order
            const flatRows = elements.container.querySelectorAll('tr[data-item-id]');
            flatRows.forEach(row => {
                allRows.push(row);
            });
        }

        // Also get any standalone rows (not in categories)
        document.querySelectorAll('#selected-items-container > tr[data-item-id]').forEach(row => {
            if (!allRows.includes(row)) {
                allRows.push(row);
            }
        });

        // ✅ استعادة الترتيب الأصلي دائماً إذا كان موجوداً
        let sortedRows;
        if (state.originalItemOrder && state.originalItemOrder.length > 0) {
            sortedRows = restoreOriginalOrder(allRows);
            console.log('🔄 Restoring original order:', state.originalItemOrder);
        } else {
            sortedRows = allRows;
            // ✅ حفظ الترتيب الحالي كترتيب أصلي إذا لم يكن محفوظاً
            if (allRows.length > 0) {
                saveOriginalOrder();
            }
        }

        // Clear container
        elements.container.innerHTML = '';

        // Add all rows back in order
        sortedRows.forEach((row, index) => {
            // Save predecessor, dependency type, and lag values before cloning
            const predecessorSelect = row.querySelector('.predecessor-select');
            const dependencyTypeSelect = row.querySelector('select[name*="[dependency_type]"]');
            const lagInput = row.querySelector('.lag-input');

            const predecessorValue = predecessorSelect?.value || '';
            const dependencyTypeValue = dependencyTypeSelect?.value || 'end_to_start';
            const lagValue = lagInput?.value || '0';

            // Clone the row
            const clonedRow = row.cloneNode(true);
            elements.container.appendChild(clonedRow);

            // Restore values after cloning
            const newRow = elements.container.lastChild;
            const newPredecessorSelect = newRow.querySelector('.predecessor-select');
            const newDependencyTypeSelect = newRow.querySelector('select[name*="[dependency_type]"]');
            const newLagInput = newRow.querySelector('.lag-input');

            if (newPredecessorSelect) newPredecessorSelect.value = predecessorValue;
            if (newDependencyTypeSelect) newDependencyTypeSelect.value = dependencyTypeValue;
            if (newLagInput) newLagInput.value = lagValue;

            // Log restored values
            if (predecessorValue) {
                console.log(`📌 Restored relationship (flat view) for ${newRow.dataset.itemId}:`, {
                    predecessor: predecessorValue,
                    dependencyType: dependencyTypeValue,
                    lag: lagValue
                });
            }

            // Re-attach event listeners
            const workItemId = newRow.dataset.workItemId;
            const workItem = state.workItems.find(item => item.id == workItemId);
            if (workItem) {
                attachRowEventListeners(newRow, workItem, newRow.dataset.itemId);
            }

            // ✅ Update item_order to match the new index (preserve order)
            const orderInput = newRow.querySelector('input[name*="[item_order]"]');
            if (orderInput) {
                orderInput.value = index;
            }

            // ✅ Update badge number
            const badge = newRow.querySelector('.badge.bg-primary');
            if (badge) {
                badge.textContent = index + 1;
            }
        });

        // ✅ item_order already updated above, just update predecessors
        updatePredecessors();

        // ✅ لا نحفظ الترتيب هنا لأنه قد يكون تم استعادته من الترتيب الأصلي
        // الترتيب الأصلي يتم حفظه فقط في loadExistingItems
    }

    // ========================================
    // حفظ الترتيب الأصلي
    // ========================================
    function saveOriginalOrder() {
        // ✅ فقط احفظ إذا لم يكن محفوظاً من قبل
        if (state.originalItemOrder && state.originalItemOrder.length > 0) {
            console.log('⚠️ Original order already saved, skipping...');
            return;
        }

        const rows = elements.container.querySelectorAll('tr[data-item-id]');
        if (rows.length === 0) {
            console.log('⚠️ No rows to save order');
            return;
        }

        state.originalItemOrder = Array.from(rows).map(row => {
            const itemId = row.dataset.itemId;
            const orderInput = row.querySelector('input[name*="[item_order]"]');
            const order = orderInput ? parseInt(orderInput.value) : 999999;
            return { itemId, order };
        });
        console.log('💾 Saved original order:', state.originalItemOrder);
    }

    // ========================================
    // استعادة الترتيب الأصلي
    // ========================================
    function restoreOriginalOrder(rows) {
        if (!state.originalItemOrder || state.originalItemOrder.length === 0) {
            return rows; // لا يوجد ترتيب أصلي محفوظ
        }

        // إنشاء خريطة من itemId إلى order
        const orderMap = new Map();
        state.originalItemOrder.forEach(item => {
            orderMap.set(item.itemId, item.order);
        });

        // ترتيب الصفوف حسب الترتيب الأصلي
        return Array.from(rows).sort((a, b) => {
            const orderA = orderMap.get(a.dataset.itemId) ?? 999999;
            const orderB = orderMap.get(b.dataset.itemId) ?? 999999;
            return orderA - orderB;
        });
    }

    // ========================================
    // Clean Empty Subprojects
    // ========================================
    function cleanEmptySubprojects() {
        console.log('🧹 Cleaning empty subprojects...');

        // Get all subproject names from items
        const subprojectNamesInItems = new Set();
        const allRows = document.querySelectorAll('tr[data-item-id]');

        allRows.forEach(row => {
            const subprojectInput = row.querySelector('.subproject-input');
            const subprojectName = subprojectInput?.value?.trim();
            if (subprojectName) {
                subprojectNamesInItems.add(subprojectName);
            }
        });

        console.log('📦 Subprojects found in items:', Array.from(subprojectNamesInItems));

        // Remove all subproject hidden inputs first
        const existingSubprojectInputs = elements.form.querySelectorAll('input[name^="subprojects["]');
        existingSubprojectInputs.forEach(input => input.remove());

        console.log(`🗑️ Removed ${existingSubprojectInputs.length} old subproject inputs`);

        // Re-create only subprojects that have items
        if (subprojectNamesInItems.size > 0) {
            const fragment = document.createDocumentFragment();
            let subprojectIndex = 0;

            subprojectNamesInItems.forEach(subprojectName => {
                // Get data for this subproject from grouped view if available
                const categoryId = 'cat_' + subprojectName.replace(/[^a-zA-Z0-9]/g, '_');

                // Name (required)
                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = `subprojects[${subprojectIndex}][name]`;
                nameInput.value = subprojectName;
                fragment.appendChild(nameInput);

                // Total quantity (from manual input if exists)
                const qtyHiddenInput = document.querySelector(`.subproject-quantity-${categoryId}`);
                if (qtyHiddenInput && qtyHiddenInput.value) {
                    const qtyInput = document.createElement('input');
                    qtyInput.type = 'hidden';
                    qtyInput.name = `subprojects[${subprojectIndex}][total_quantity]`;
                    qtyInput.value = qtyHiddenInput.value;
                    fragment.appendChild(qtyInput);
                }

                // Unit (from manual input if exists)
                const unitHiddenInput = document.querySelector(`.subproject-unit-${categoryId}`);
                if (unitHiddenInput && unitHiddenInput.value) {
                    const unitInput = document.createElement('input');
                    unitInput.type = 'hidden';
                    unitInput.name = `subprojects[${subprojectIndex}][unit]`;
                    unitInput.value = unitHiddenInput.value;
                    fragment.appendChild(unitInput);
                }

                // Weight (from manual input if exists)
                const weightHiddenInput = document.querySelector(`.subproject-weight-${categoryId}`);
                if (weightHiddenInput && weightHiddenInput.value) {
                    const weightInput = document.createElement('input');
                    weightInput.type = 'hidden';
                    weightInput.name = `subprojects[${subprojectIndex}][weight]`;
                    weightInput.value = weightHiddenInput.value;
                    fragment.appendChild(weightInput);
                }

                console.log(`✅ Created subproject input: "${subprojectName}"`);
                subprojectIndex++;
            });

            elements.form.appendChild(fragment);
            console.log(`✅ Re-created ${subprojectIndex} subproject(s) with items`);
        } else {
            console.log('ℹ️ No subprojects to save (no items have subproject names)');
        }
    }

    // ========================================
    // Form Submit Validation
    // ========================================
    function handleFormSubmit(e) {
        // Clean empty subprojects before validation
        cleanEmptySubprojects();

        // Get the button that was clicked
        const submitButton = e.submitter;
        const isDraft = submitButton?.name === 'save_as_draft';

        // If saving as draft, allow with minimal validation
        if (isDraft) {
            return validateDraftSubmit(e);
        }

        // Full validation for normal project
        return validateFullSubmit(e);
    }

    function validateDraftSubmit(e) {
        const errors = [];

        // Only check project name
        const projectName = document.getElementById('project-name-input')?.value?.trim();
        if (!projectName) {
            errors.push('• اسم المشروع مطلوب');
        }

        if (errors.length > 0) {
            e.preventDefault();
            showValidationErrors('لا يمكن حفظ المسودة', errors);
            return false;
        }

        // Show confirmation for draft
        const itemsCount = elements.container.querySelectorAll('tr[data-item-id]').length;
        const confirmMsg = `حفظ كمسودة:\n\n` +
            `📝 الاسم: ${projectName}\n` +
            `📦 عدد البنود: ${itemsCount}\n\n` +
            `هل تريد المتابعة؟`;

        if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }
        return true;
    }
    function validateFullSubmit(e) {
        const errors = [];
        const projectName = document.getElementById('project-name-input')?.value?.trim();
        if (!projectName) {
            errors.push('• اسم المشروع مطلوب');
        }

        const clientId = document.getElementById('client-id-select')?.value;
        if (!clientId) {
            errors.push('• العميل مطلوب');
        }

        const startDate = elements.startDate?.value;
        if (!startDate) {
            errors.push('• تاريخ البداية مطلوب');
        }

        const workingZone = document.getElementById('working_zone')?.value?.trim();
        if (!workingZone) {
            errors.push('• منطقة العمل مطلوبة');
        }

        const projectTypeId = document.getElementById('project_type_id')?.value;
        if (!projectTypeId) {
            errors.push('• نوع المشروع مطلوب');
        }

        // Check items
        const itemsCount = elements.container.querySelectorAll('tr[data-item-id]').length;
        if (itemsCount === 0) {
            errors.push('• يجب إضافة بند واحد على الأقل للمشروع');
        }

        // Check employees
        const selectedEmployees = document.querySelectorAll('input[name="employees[]"]:checked').length;
        if (selectedEmployees === 0) {
            errors.push('• يجب اختيار موظف واحد على الأقل');
        }

        // If there are errors, prevent submission
        if (errors.length > 0) {
            e.preventDefault();
            showValidationErrors('لا يمكن حفظ المشروع', errors);
            return false;
        }

        // Show confirmation with summary
        const endDate = elements.endDate?.value || 'يحسب تلقائياً';
        const client = document.querySelector('#client-id-select option:checked')?.text || '';

        const confirmMsg = `✅ تأكيد حفظ المشروع:\n\n` +
            `📝 الاسم: ${projectName}\n` +
            `👤 العميل: ${client}\n` +
            `📅 من: ${startDate} إلى: ${endDate}\n` +
            `📦 عدد البنود: ${itemsCount}\n` +
            `👥 عدد الموظفين: ${selectedEmployees}\n` +
            `📍 منطقة العمل: ${workingZone}\n\n` +
            `هل تريد المتابعة؟`;

        if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }

        // Show loading message
        showNotification('info', '⏳ جاري حفظ المشروع...');

        return true;
    }

    function showValidationErrors(title, errors) {
        const errorsList = errors.join('\n');
        const message = `❌ ${title}\n\nالحقول المطلوبة التي لم يتم ملؤها:\n\n${errorsList}\n\nالرجاء إكمال البيانات المطلوبة.`;

        alert(message);

        // Also show as notification
        showNotification('error', `${title} - يرجى إكمال الحقول المطلوبة`);
    }

    // ========================================
    // Handle Enter Key Navigation
    // ========================================
    function handleEnterKey(e) {
        // Check if Enter key was pressed
        if (e.key !== 'Enter' && e.keyCode !== 13) {
            return;
        }

        const target = e.target;
        const tagName = target.tagName.toLowerCase();

        // Allow Enter in textareas and for submit buttons
        if (tagName === 'textarea' ||
            tagName === 'button' ||
            target.type === 'submit') {
            return true;
        }

        // Prevent form submission
        e.preventDefault();

        // Move to next input
        moveToNextInput(target);
        return false;
    }

    function moveToNextInput(currentElement) {
        // Get all focusable elements in the form
        const focusableElements = Array.from(
            elements.form.querySelectorAll(
                'input:not([type="hidden"]):not([disabled]):not([readonly]), ' +
                'select:not([disabled]), ' +
                'textarea:not([disabled]), ' +
                'button:not([disabled])'
            )
        );

        // Find current element index
        const currentIndex = focusableElements.indexOf(currentElement);

        if (currentIndex === -1 || currentIndex === focusableElements.length - 1) {
            return;
        }

        // Get next element
        const nextElement = focusableElements[currentIndex + 1];

        if (nextElement) {
            nextElement.focus();

            // Select text in input fields
            if (nextElement.tagName === 'INPUT' &&
                nextElement.type !== 'checkbox' &&
                nextElement.type !== 'radio' &&
                nextElement.type !== 'date') {
                try {
                    nextElement.select();
                } catch (e) {
                    // Ignore error
                }
            }
        }
    }

    // ========================================
    // Utilities
    // ========================================
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' :
            type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => notification.remove(), 3000);
    }

    // ========================================
    // Auto-save للمسودة
    // ========================================
    function startAutoSave() {
        // Don't auto-save if we're editing an existing project
        if (state.project && state.project.id) {
            console.log('⏭️ Auto-save disabled for existing projects');
            return;
        }

        // Auto-save every 2 minutes
        state.autoSaveInterval = setInterval(() => {
            const itemsCount = elements.container?.querySelectorAll('tr[data-item-id]').length || 0;
            const projectName = document.getElementById('project-name-input')?.value?.trim();

            // Only auto-save if there's a name and at least one item
            if (projectName && itemsCount > 0) {
                saveDraftAjax();
            }
        }, 120000); // 2 minutes = 120000ms

        console.log('✅ Auto-save enabled (every 2 minutes)');
    }

    function saveDraftAjax() {
        // Prevent multiple saves at the same time
        const now = Date.now();
        if (state.lastAutoSaveTime && (now - state.lastAutoSaveTime) < 5000) {
            console.log('⏭️ Auto-save skipped (too soon)');
            return;
        }

        state.lastAutoSaveTime = now;

        console.log('💾 Auto-saving draft...');

        // Clean empty subprojects before saving
        cleanEmptySubprojects();

        const formData = new FormData(elements.form);
        formData.set('save_as_draft', '1');

        // Don't show loading notification for auto-save
        fetch(elements.form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.redirect_url) {
                    console.log('✅ Auto-save successful');
                    showNotification('success', '💾 تم الحفظ التلقائي');

                    // Update project ID if this was a new project
                    if (data.project_id && !state.project?.id) {
                        if (!state.project) state.project = {};
                        state.project.id = data.project_id;
                    }
                } else {
                    console.error('❌ Auto-save failed:', data);
                }
            })
            .catch(error => {
                console.error('❌ Auto-save error:', error);
                // Don't show error notification for auto-save failures
            });
    }

    function stopAutoSave() {
        if (state.autoSaveInterval) {
            clearInterval(state.autoSaveInterval);
            state.autoSaveInterval = null;
            console.log('⏹️ Auto-save stopped');
        }
    }

    // ========================================
    // Initialize on DOM Ready
    // ========================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();