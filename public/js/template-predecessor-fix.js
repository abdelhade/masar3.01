// Template Predecessor Fix
// This script fixes the issue with predecessor data not being saved/loaded correctly in templates

document.addEventListener('DOMContentLoaded', function() {
    // Override the template loading function to fix predecessor handling
    const originalTemplateCheckboxes = document.querySelectorAll('.template-checkbox');
    
    originalTemplateCheckboxes.forEach(checkbox => {
        // Remove existing event listeners
        const newCheckbox = checkbox.cloneNode(true);
        checkbox.parentNode.replaceChild(newCheckbox, checkbox);
        
        // Add new event listener with fixed predecessor handling
        newCheckbox.addEventListener('change', function() {
            const templateId = this.value;

            if (this.checked) {
                console.log(`🔄 Loading template ${templateId} with fixed predecessor handling...`);
                
                // Load template data via AJAX
                fetch(`/progress/project-templates/${templateId}/data`)
                    .then(response => response.json())
                    .then(templateData => {
                        console.log('📦 Template data received:', templateData);
                        
                        // Apply template project settings
                        applyTemplateSettings(templateData);
                        
                        // Load template items with proper predecessor mapping
                        loadTemplateItemsWithPredecessorFix(templateData, templateId);
                    })
                    .catch(error => {
                        console.error('❌ Error loading template:', error);
                        alert('خطأ في تحميل القالب');
                    });
            } else {
                // Remove template items
                removeTemplateItems(templateId);
            }
        });
    });
});

function applyTemplateSettings(templateData) {
    console.log('⚙️ Applying template settings...');
    
    if (templateData.status) {
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) statusSelect.value = templateData.status;
    }
    
    if (templateData.project_type_id) {
        const typeSelect = document.querySelector('select[name="project_type_id"]');
        if (typeSelect) typeSelect.value = templateData.project_type_id;
    }
    
    if (templateData.working_days) {
        const workingDaysInput = document.querySelector('input[name="working_days"]');
        if (workingDaysInput) workingDaysInput.value = templateData.working_days;
    }
    
    if (templateData.daily_work_hours) {
        const dailyHoursInput = document.querySelector('input[name="daily_work_hours"]');
        if (dailyHoursInput) dailyHoursInput.value = templateData.daily_work_hours;
    }
    
    if (templateData.weekly_holidays) {
        const holidaysInput = document.getElementById('weekly_holidays_input');
        if (holidaysInput) {
            holidaysInput.value = templateData.weekly_holidays;
            // Update checkboxes
            const holidays = templateData.weekly_holidays.split(',');
            const checkboxes = document.querySelectorAll('.weekly-holiday-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = holidays.includes(cb.value);
            });
        }
    }
    
    if (templateData.working_zone) {
        const workingZoneInput = document.querySelector('input[name="working_zone"]');
        if (workingZoneInput) workingZoneInput.value = templateData.working_zone;
    }
}

function loadTemplateItemsWithPredecessorFix(templateData, templateId) {
    console.log('📋 Loading template items with predecessor fix...');
    
    const categories = templateData.categories || [];
    const workItemToRowMapping = new Map(); // Map work_item_id to row_id
    const predecessorMappings = new Map(); // Store predecessor relationships
    
    // First pass: Create all items and build mapping
    categories.forEach(category => {
        const items = category.items || [];
        items.forEach(item => {
            console.log(`➕ Adding item: ${item.name} (work_item_id: ${item.work_item_id})`);
            
            // Create item row
            const row = window.addItemToContainer(
                item.work_item_id, 
                '', 
                null, 
                `template-${templateId}`
            );
            
            if (row) {
                // Store mapping
                workItemToRowMapping.set(item.work_item_id, row.dataset.itemId);
                
                // Store predecessor relationship if exists
                if (item.predecessor) {
                    predecessorMappings.set(item.work_item_id, item.predecessor);
                    console.log(`🔗 Stored predecessor mapping: ${item.work_item_id} -> ${item.predecessor}`);
                }
                
                // Fill in template values
                fillTemplateItemValues(row, item);
            }
        });
    });
    
    console.log('🗺️ Work item to row mapping:', Array.from(workItemToRowMapping.entries()));
    console.log('🔗 Predecessor mappings:', Array.from(predecessorMappings.entries()));
    
    // Second pass: Set predecessors using the mapping
    setTimeout(() => {
        console.log('🔄 Setting predecessors...');
        
        // Update all predecessor dropdowns first
        if (typeof window.updatePredecessorsDropdowns === 'function') {
            window.updatePredecessorsDropdowns();
        }
        
        // Set predecessor values - now predecessor is work_item_id, need to find corresponding row_id
        predecessorMappings.forEach((predecessorWorkItemId, currentWorkItemId) => {
            const currentRowId = workItemToRowMapping.get(currentWorkItemId);
            const predecessorRowId = workItemToRowMapping.get(predecessorWorkItemId);
            
            console.log(`🎯 Setting predecessor for ${currentWorkItemId} (row: ${currentRowId}) -> ${predecessorWorkItemId} (row: ${predecessorRowId})`);
            
            if (currentRowId && predecessorRowId) {
                const currentRow = document.querySelector(`tr[data-item-id="${currentRowId}"]`);
                if (currentRow) {
                    const predecessorSelect = currentRow.querySelector('.predecessor-select');
                    if (predecessorSelect) {
                        // Set the predecessor to the row_id that corresponds to the work_item_id
                        predecessorSelect.value = predecessorRowId;
                        console.log(`✅ Successfully set predecessor: ${predecessorRowId}`);
                        
                        // Trigger change event to update dependencies
                        predecessorSelect.dispatchEvent(new Event('change'));
                    } else {
                        console.warn('⚠️ Predecessor select not found in row');
                    }
                } else {
                    console.warn(`⚠️ Current row not found: ${currentRowId}`);
                }
            } else {
                console.warn(`⚠️ Missing row IDs - current: ${currentRowId}, predecessor: ${predecessorRowId}`);
            }
        });
        
        // Trigger date calculations
        if (typeof window.calculateAllDatesAndDurations === 'function') {
            window.calculateAllDatesAndDurations();
        }
        
        console.log('✅ Template loading completed with predecessor fix!');
    }, 1000); // Increased timeout to ensure all elements are ready
}

function fillTemplateItemValues(row, item) {
    console.log(`📝 Filling values for item: ${item.name}`);
    
    // Fill in template values
    const totalQtyInput = row.querySelector('.total-quantity');
    if (totalQtyInput) totalQtyInput.value = item.default_quantity || 0;
    
    const estimatedQtyInput = row.querySelector('.estimated-daily-qty');
    if (estimatedQtyInput) estimatedQtyInput.value = item.estimated_daily_qty || 0;
    
    const durationInput = row.querySelector('.duration-input');
    if (durationInput) durationInput.value = item.duration || 0;
    
    const lagInput = row.querySelector('.lag-input');
    if (lagInput) lagInput.value = item.lag || 0;
    
    const notesTextarea = row.querySelector('.notes-textarea');
    if (notesTextarea) notesTextarea.value = item.notes || '';
    
    const dependencySelect = row.querySelector('.dependency-type-select');
    if (dependencySelect) dependencySelect.value = item.dependency_type || 'end_to_start';
}

function removeTemplateItems(templateId) {
    console.log(`🗑️ Removing template ${templateId} items...`);
    
    document.querySelectorAll(`tr[data-template-source="template-${templateId}"]`)
        .forEach(el => {
            const itemId = el.dataset.itemId;
            const workItemCheckbox = document.querySelector(`input[value="${itemId}"].work-item-checkbox`);
            if (workItemCheckbox) {
                workItemCheckbox.checked = false;
                if (typeof window.updateWorkItemStyle === 'function') {
                    window.updateWorkItemStyle(workItemCheckbox);
                }
            }
            el.remove();
        });
    
    // Update dropdowns and calculations
    if (typeof window.updatePredecessorsDropdowns === 'function') {
        window.updatePredecessorsDropdowns();
    }
    if (typeof window.calculateAllDatesAndDurations === 'function') {
        window.calculateAllDatesAndDurations();
    }
}

// Debug function to check predecessor mappings
window.debugPredecessors = function() {
    console.log('🔍 Debug: Current predecessor mappings');
    const rows = document.querySelectorAll('tr[data-item-id]');
    rows.forEach(row => {
        const workItemInput = row.querySelector('input[name*="[work_item_id]"]');
        const predecessorSelect = row.querySelector('.predecessor-select');
        if (workItemInput && predecessorSelect) {
            console.log(`Row ${row.dataset.itemId}: work_item_id=${workItemInput.value}, predecessor=${predecessorSelect.value}`);
        }
    });
};