/**
 * Simple Search - Pure Vanilla JavaScript
 * No Alpine.js, No Livewire - Just plain JavaScript
 */

(function() {
    'use strict';
    
    console.log('🚀 Simple Search Script Loading...');
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSearch);
    } else {
        initSearch();
    }
    
    function initSearch() {
        console.log('🎬 Initializing Simple Search...');
        
        const searchInput = document.getElementById('search-input');
        if (!searchInput) {
            console.error('❌ Search input not found!');
            return;
        }
        
        console.log('✅ Search input found');
        
        // Search state
        const state = {
            allItems: [],
            searchResults: [],
            selectedIndex: -1,
            fuse: null,
            loading: false
        };
        
        // Load items from API
        loadItems();
        
        // Setup event listeners
        searchInput.addEventListener('input', handleInput);
        searchInput.addEventListener('focus', handleFocus);
        searchInput.addEventListener('keydown', handleKeydown);
        
        console.log('✅ Event listeners attached');
        
        function loadItems() {
            console.log('📡 Loading items from API...');
            state.loading = true;
            updateLoadingStatus(true);
            
            // Get branch and type from page
            const branchId = document.querySelector('[name="branch_id"]')?.value || '';
            const type = document.querySelector('[name="type"]')?.value || '10';
            
            const url = `/api/items/lite?branch_id=${branchId}&type=${type}&_t=${Date.now()}`;
            console.log('📡 Fetching from:', url);
            
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('📡 Response status:', response.status);
                if (!response.ok) throw new Error('Failed to load items');
                return response.json();
            })
            .then(data => {
                console.log('📦 Received', data.length, 'items');
                state.allItems = data;
                
                // Initialize Fuse.js
                if (typeof Fuse !== 'undefined') {
                    state.fuse = new Fuse(state.allItems, {
                        keys: ['name', 'code', 'barcode'],
                        threshold: 0.3,
                        ignoreLocation: true
                    });
                    console.log('✅ Fuse.js initialized');
                } else {
                    console.error('❌ Fuse.js not loaded!');
                }
                
                updateLoadingStatus(false);
                state.loading = false;
            })
            .catch(error => {
                console.error('❌ Error loading items:', error);
                updateLoadingStatus(false);
                state.loading = false;
            });
        }
        
        function handleInput(e) {
            const searchTerm = e.target.value.trim();
            console.log('⌨️ Input:', searchTerm);
            
            if (searchTerm.length < 1) {
                hideResults();
                return;
            }
            
            search(searchTerm);
        }
        
        function handleFocus(e) {
            const searchTerm = e.target.value.trim();
            if (searchTerm.length > 0 && state.searchResults.length > 0) {
                showResults();
            }
        }
        
        function handleKeydown(e) {
            if (!isResultsVisible()) return;
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectNext();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    selectPrevious();
                    break;
                case 'Enter':
                    e.preventDefault();
                    addSelectedItem();
                    break;
                case 'Escape':
                    e.preventDefault();
                    hideResults();
                    break;
            }
        }
        
        function search(searchTerm) {
            console.log('🔍 Searching for:', searchTerm);
            
            if (!state.fuse) {
                console.error('❌ Fuse.js not initialized');
                return;
            }
            
            const results = state.fuse.search(searchTerm);
            state.searchResults = results.map(r => r.item).slice(0, 50);
            state.selectedIndex = state.searchResults.length > 0 ? 0 : -1;
            
            console.log('📋 Found', state.searchResults.length, 'results');
            
            renderResults();
            showResults();
        }
        
        function renderResults() {
            const dropdown = document.getElementById('search-results-dropdown');
            if (!dropdown) {
                console.error('❌ Dropdown not found');
                return;
            }
            
            dropdown.innerHTML = '';
            
            if (state.searchResults.length === 0) {
                // Show "Create New Item" button
                const createBtn = document.createElement('button');
                createBtn.type = 'button';
                createBtn.className = 'list-group-item list-group-item-action text-primary';
                createBtn.innerHTML = `
                    <i class="fas fa-plus-circle me-2"></i>
                    <strong>إنشاء صنف جديد:</strong>
                    <span class="ms-2 fw-bold">${searchInput.value}</span>
                `;
                createBtn.onclick = () => createNewItem(searchInput.value);
                dropdown.appendChild(createBtn);
            } else {
                // Show results
                state.searchResults.forEach((item, index) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                    if (index === state.selectedIndex) {
                        btn.classList.add('active');
                    }
                    btn.innerHTML = `
                        <div>
                            <strong>${item.name}</strong>
                            <small class="text-muted ms-2">كود: ${item.code}</small>
                        </div>
                        <span class="badge bg-primary">${item.price || 0} ج.م</span>
                    `;
                    btn.onclick = () => addItem(item);
                    dropdown.appendChild(btn);
                });
            }
        }
        
        function showResults() {
            const dropdown = document.getElementById('search-results-dropdown');
            if (dropdown) {
                dropdown.style.display = 'block';
            }
        }
        
        function hideResults() {
            const dropdown = document.getElementById('search-results-dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            state.searchResults = [];
            state.selectedIndex = -1;
        }
        
        function isResultsVisible() {
            const dropdown = document.getElementById('search-results-dropdown');
            return dropdown && dropdown.style.display === 'block';
        }
        
        function selectNext() {
            if (state.selectedIndex < state.searchResults.length - 1) {
                state.selectedIndex++;
                renderResults();
            }
        }
        
        function selectPrevious() {
            if (state.selectedIndex > 0) {
                state.selectedIndex--;
                renderResults();
            }
        }
        
        function addSelectedItem() {
            if (state.selectedIndex >= 0 && state.searchResults[state.selectedIndex]) {
                addItem(state.searchResults[state.selectedIndex]);
            }
        }
        
        function addItem(item) {
            console.log('➕ Adding item:', item.name);
            
            // Get Alpine component
            const form = document.querySelector('form[x-data*="invoiceCalculations"]');
            if (!form || !form._x_dataStack || !form._x_dataStack[0]) {
                console.error('❌ Alpine component not found');
                alert('خطأ: لم يتم العثور على مكون الفاتورة');
                return;
            }
            
            const invoiceComponent = form._x_dataStack[0];
            
            // Add item to invoice
            const newItem = {
                id: item.id,
                item_id: item.id,
                name: item.name,
                code: item.code,
                unit_id: item.default_unit_id || item.unit_id,
                quantity: 1,
                price: item.price || 0,
                item_price: item.price || 0,
                discount: 0,
                sub_value: item.price || 0,
                batch_number: '',
                expiry_date: null,
                available_units: item.units || []
            };
            
            invoiceComponent.invoiceItems.push(newItem);
            const newIndex = invoiceComponent.invoiceItems.length - 1;
            
            // Calculate totals
            if (invoiceComponent.calculateItemTotal) {
                invoiceComponent.calculateItemTotal(newIndex);
            }
            
            // Clear search
            searchInput.value = '';
            hideResults();
            
            // Focus on quantity field
            setTimeout(() => {
                const quantityField = document.getElementById(`quantity-${newIndex}`);
                if (quantityField) {
                    quantityField.focus();
                    quantityField.select();
                }
            }, 100);
            
            console.log('✅ Item added successfully');
        }
        
        function createNewItem(itemName) {
            const createUrl = `/items/create?name=${encodeURIComponent(itemName)}`;
            window.open(createUrl, '_blank');
            hideResults();
            searchInput.value = '';
        }
        
        function updateLoadingStatus(loading) {
            const statusEl = document.getElementById('search-status');
            if (statusEl) {
                if (loading) {
                    statusEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري تحميل الأصناف...';
                    statusEl.className = 'text-danger';
                } else if (state.allItems.length > 0) {
                    statusEl.innerHTML = `تم تحميل <strong>${state.allItems.length}</strong> صنف <i class="fas fa-check-circle text-success ms-2"></i> البحث جاهز`;
                    statusEl.className = 'text-muted';
                }
            }
        }
        
        // Expose reload function
        window.reloadSearchItems = loadItems;
        
        console.log('✅ Simple Search initialized successfully');
    }
})();
