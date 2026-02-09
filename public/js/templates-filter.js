/**
 * Templates Filter Script
 * Handles filtering and display of project templates
 */

(function() {
    'use strict';

    console.log('🎯 Templates Filter Script Loaded');

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('🚀 Initializing Templates Filter...');

        const templatesFilter = document.getElementById('templates-filter');
        const clearFilterBtn = document.getElementById('clear-templates-filter');
        const templatesList = document.getElementById('templates-list');
        const templateItems = document.querySelectorAll('.template-item');

        console.log('📋 Found elements:', {
            filter: !!templatesFilter,
            clearBtn: !!clearFilterBtn,
            list: !!templatesList,
            itemsCount: templateItems.length
        });

        if (!templatesFilter || !templatesList) {
            console.warn('⚠️ Templates filter elements not found');
            return;
        }

        // Filter templates on input
        templatesFilter.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            console.log('🔍 Filtering templates with:', searchTerm);
            filterTemplates(searchTerm);
        });

        // Clear filter button
        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', function() {
                templatesFilter.value = '';
                filterTemplates('');
                templatesFilter.focus();
            });
        }

        // Initial check - make sure all templates are visible
        console.log('✅ Templates should be visible now');
        filterTemplates('');
    }

    function filterTemplates(searchTerm) {
        const templateItems = document.querySelectorAll('.template-item');
        let visibleCount = 0;

        templateItems.forEach(function(item) {
            const templateName = item.getAttribute('data-template-name') || '';
            const templateDesc = item.getAttribute('data-template-desc') || '';
            
            const matches = templateName.includes(searchTerm) || templateDesc.includes(searchTerm);
            
            if (matches) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        console.log(`📊 Visible templates: ${visibleCount} / ${templateItems.length}`);
    }

})();
