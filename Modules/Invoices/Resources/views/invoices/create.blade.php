@extends('admin.dashboard')

@section('body_class', 'invoice-page-fixed')

@section('sidebar')
    @if (in_array($type, [10, 12, 14, 16, 22, 26]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($type, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($type, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection

@section('content')

    @push('styles')
        <style>
            /* Body padding for fixed footer - NO SCROLL */
            /* NO PAGE SCROLL - Fixed layout components */
            body.invoice-page-fixed {
                height: 100vh !important;
                overflow: hidden !important;
            }

            .invoice-page-fixed .page-wrapper {
                height: 100vh !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            }

            .invoice-page-fixed .page-content {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                padding: 0 !important;
            }

            .invoice-page-fixed .container-fluid,
            .invoice-page-fixed .container-fluid>.row {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-page-fixed #invoice-app {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                background: #fff;
            }

            .invoice-page-fixed #invoice-form {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            }

            .invoice-page-fixed .invoice-scroll-container {
                flex: 1 !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                padding: 15px !important;
            }

            /* Allow dropdown to show outside scroll container */
            .invoice-scroll-container .table-responsive {
                overflow: visible !important;
            }

            .invoice-page-fixed #invoice-fixed-footer {
                flex-shrink: 0 !important;
                width: 100% !important;
                z-index: 10;
            }

            /* Header styling to match image */
            .invoice-header-card {
                background: #f8f9fa;
                border: 2px solid #6c757d;
                border-radius: 8px;
                margin-bottom: 15px;
            }

            .invoice-header-card .card-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 6px 6px 0 0;
                padding: 10px 15px;
            }

            /* Table header styling to match image */
            .invoice-data-grid thead th {
                background: linear-gradient(135deg, #a8c0ff 0%, #c5d9ff 100%) !important;
                color: #2c3e50;
                font-weight: 600;
                text-align: center;
                border: 1px solid #90a4ae;
            }

            /* Search row styling */
            .invoice-data-grid .search-row {
                background: #e3f2fd !important;
            }

            text-align: center;
            }

            .footer-value.total {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-size: 1.2rem;
            }

            /* Button styling */
            .btn-save-invoice {
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                border: none;
                color: white;
                font-weight: 600;
                padding: 12px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
                transition: all 0.3s ease;
            }

            .btn-save-invoice:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
            }


            /* Hidden class */
            .hidden {
                display: none !important;
            }

            /* Select2 dropdown positioning */
            .select2-container {
                z-index: 9999 !important;
            }

            .select2-dropdown {
                z-index: 99999 !important;
            }

            /* Search dropdown must be above everything */
            .search-results-dropdown {
                z-index: 999999 !important;
            }
        </style>
    @endpush
    {{-- Pure HTML - No Alpine --}}
    <div id="invoice-app">
        <form id="invoice-form">
            @csrf

            {{-- Part 1: Invoice Header --}}
            <div class="invoice-header-card">
                @include('invoices::components.invoices.invoice-head', [
                    'type' => $type,
                    'branches' => $branches,
                    'acc1Role' => in_array($type, [10, 12, 14, 16, 19, 22]) ? __('Customer') : __('Supplier'),
                    'acc2Role' => __('Store'),
                    'acc1Options' => $acc1Options,
                    'acc2List' => $acc2List,
                    'employees' => $employees,
                    'deliverys' => $deliverys,
                    'cashAccounts' => $cashAccounts,
                    'showBalance' => setting('show_balance', '1') === '1',
                    'currentBalance' => 0,
                    'balanceAfterInvoice' => 0,
                    'currency_id' => 1,
                    'currency_rate' => 1,
                ])
            </div>

            @include('invoices::components.invoices.invoice-item-table', [
                'type' => $type,
                'branchId' => $branchId,
            ])
        </form>
    </div>

    {{-- Invoice Footer - NOT fixed, at bottom of content --}}
    <div class="invoice-footer-container">
        @include('invoices::components.invoices.invoice-footer', [
            'type' => $type,
            'vatPercentage' => setting('vat_percentage', 15),
            'withholdingTaxPercentage' => setting('withholding_tax_percentage', 0),
            'showBalance' => setting('show_balance', '1') === '1',
            'cashAccounts' => $cashAccounts,
        ])
    </div>
@endsection

@section('script')
    {{-- Main Invoice JavaScript --}}
        <script>
            console.log('🚀 Invoice System Loading...');
            console.log('📍 Script started at:', new Date().toISOString());
            console.log('📍 Document ready state:', document.readyState);

            // Invoice State (Global)
            window.InvoiceApp = {
                // Config
                type: {{ $type }},
                branchId: {{ $branchId ?? 'null' }},
                vatPercentage: {{ setting('vat_percentage', 15) }},
                withholdingTaxPercentage: {{ setting('withholding_tax_percentage', 0) }},

                // Template columns
                visibleColumns: ['item_name', 'code', 'unit', 'quantity', 'price', 'discount', 'sub_value'],
                allColumns: {
                    'item_name': '{{ __('Item Name') }}',
                    'code': '{{ __('Code') }}',
                    'unit': '{{ __('Unit') }}',
                    'quantity': '{{ __('Quantity') }}',
                    'batch_number': '{{ __('Batch Number') }}',
                    'expiry_date': '{{ __('Expiry Date') }}',
                    'price': '{{ __('Price') }}',
                    'discount': '{{ __('Discount') }}',
                    'sub_value': '{{ __('Value') }}',
                    'length': '{{ __('Length') }}',
                    'width': '{{ __('Width') }}',
                    'height': '{{ __('Height') }}',
                    'density': '{{ __('Density') }}'
                },

                // Data
                invoiceItems: [],
                allItems: [],
                fuse: null,

                // Totals
                subtotal: 0,
                discountPercentage: 0,
                discountValue: 0,
                additionalPercentage: 0,
                additionalValue: 0,
                vatValue: 0,
                withholdingTaxValue: 0,
                totalAfterAdditional: 0,
                receivedFromClient: 0,
                remaining: 0,

                // Search
                searchResults: [],
                selectedIndex: -1,

                // Initialize
                init() {
                    console.log('🎬 Initializing Invoice App...');
                    console.log('✅ jQuery version:', jQuery.fn.jquery);
                    console.log('✅ Select2 available:', typeof jQuery.fn.select2);

                    this.initializeSelect2();
                    this.loadDefaultTemplate();
                    this.setDefaultValues();
                    this.loadItems();
                    this.attachEventListeners();
                    this.renderItems();
                    console.log('✅ Invoice App Ready');
                },

                // Load default template
                loadDefaultTemplate() {
                    const templateSelect = document.getElementById('invoice-template');
                    if (templateSelect) {
                        const defaultOption = templateSelect.querySelector('option[selected]');
                        if (defaultOption) {
                            const columnsJson = defaultOption.getAttribute('data-columns');
                            if (columnsJson) {
                                try {
                                    this.visibleColumns = JSON.parse(columnsJson);
                                    console.log('✅ Default template loaded. Columns:', this.visibleColumns);
                                    this.updateTableHeaders();
                                } catch (error) {
                                    console.error('❌ Error parsing default template columns:', error);
                                }
                            }
                        }
                    }
                },

                // Initialize Select2 for searchable dropdowns
                initializeSelect2() {
                    // Initialize Select2 for acc1 (Customer/Supplier) with search
                    $('#acc1-id').select2({
                        theme: 'bootstrap-5',
                        placeholder: 'ابحث عن عميل/مورد...',
                        allowClear: true,
                        dropdownParent: $('#invoice-app'),
                        language: {
                            noResults: () => 'لا توجد نتائج',
                            searching: () => 'جاري البحث...'
                        }
                    }).on('change', (e) => {
                        const accountId = e.target.value;
                        if (accountId) {
                            this.updateAccountBalance(accountId);
                        }
                    });

                    // Initialize Select2 for acc2 (Store) with search
                    $('#acc2-id').select2({
                        theme: 'bootstrap-5',
                        placeholder: 'ابحث عن مخزن...',
                        allowClear: true,
                        dropdownParent: $('#invoice-app'),
                        language: {
                            noResults: () => 'لا توجد نتائج',
                            searching: () => 'جاري البحث...'
                        }
                    });

                    console.log('✅ Select2 initialized for acc1 and acc2');
                },

                // Set default values from settings
                setDefaultValues() {
                    // Set default employee
                    const defaultEmployeeId = '{{ $defaultEmployeeId ?? '' }}';
                    if (defaultEmployeeId) {
                        document.getElementById('emp-id').value = defaultEmployeeId;
                        console.log('✅ Default employee set:', defaultEmployeeId);
                    }

                    // Set default delivery
                    const defaultDeliveryId = '{{ $defaultDeliveryId ?? '' }}';
                    if (defaultDeliveryId) {
                        document.getElementById('delivery-id').value = defaultDeliveryId;
                        console.log('✅ Default delivery set:', defaultDeliveryId);
                    }

                    // Set default store
                    const defaultStoreId = '{{ $defaultStoreId ?? '' }}';
                    if (defaultStoreId) {
                        document.getElementById('acc2-id').value = defaultStoreId;
                        console.log('✅ Default store set:', defaultStoreId);
                    }

                    // Set default customer/supplier based on invoice type
                    const invoiceType = {{ $type }};
                    const defaultCustomerId = '{{ $defaultCustomerId ?? '' }}';
                    const defaultSupplierId = '{{ $defaultSupplierId ?? '' }}';

                    if ([10, 12, 14, 16, 19, 22].includes(invoiceType) && defaultCustomerId) {
                        // Sales invoices - set default customer
                        $('#acc1-id').val(defaultCustomerId).trigger('change');
                        console.log('✅ Default customer set:', defaultCustomerId);
                    } else if ([11, 13, 15, 17, 20, 23].includes(invoiceType) && defaultSupplierId) {
                        // Purchase invoices - set default supplier
                        $('#acc1-id').val(defaultSupplierId).trigger('change');
                        console.log('✅ Default supplier set:', defaultSupplierId);
                    }
                },

                // Load items from API
                loadItems() {
                    console.log('📡 Loading items...');
                    console.log('🔗 Branch ID:', this.branchId, 'Type:', this.type);
                    const url = `/api/items/lite?branch_id=${this.branchId}&type=${this.type}&_t=${Date.now()}`;
                    console.log('🌐 Fetching from:', url);

                    this.updateStatus('جاري تحميل الأصناف...', 'primary');

                    fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            console.log('📨 Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('📦 Received data:', data);
                            console.log('📦 Data type:', typeof data, 'Is array:', Array.isArray(data));

                            if (Array.isArray(data)) {
                                console.log('📦 Items count:', data.length);
                                if (data.length > 0) {
                                    console.log('📦 First 3 items:', data.slice(0, 3));
                                }
                                this.allItems = data;
                                this.updateStatus('تم تحميل ' + data.length + ' صنف - البحث جاهز ✓', 'success');
                                console.log('✅ Items loaded successfully. Total:', this.allItems.length);
                            } else {
                                console.error('❌ Response is not an array:', data);
                                this.allItems = [];
                                this.updateStatus('خطأ: البيانات المستلمة غير صحيحة', 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('❌ Error loading items:', error);
                            console.error('❌ Error details:', error.message);
                            this.allItems = [];
                            this.updateStatus('خطأ في تحميل الأصناف: ' + error.message, 'danger');
                        });
                },

                // Attach event listeners
                attachEventListeners() {
                    // Search input
                    const searchInput = document.getElementById('search-input');
                    if (searchInput) {
                        searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
                        searchInput.addEventListener('keydown', (e) => this.handleSearchKeydown(e));
                    }

                    // Form submit
                    const form = document.getElementById('invoice-form');
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            e.preventDefault();
                            this.saveInvoice();
                        });
                    }

                    // Discount/Additional inputs
                    document.getElementById('discount-percentage')?.addEventListener('input', (e) => {
                        this.discountPercentage = parseFloat(e.target.value) || 0;
                        this.calculateTotals();
                    });

                    document.getElementById('discount-value')?.addEventListener('input', (e) => {
                        this.discountValue = parseFloat(e.target.value) || 0;
                        this.calculateTotals();
                    });

                    document.getElementById('additional-percentage')?.addEventListener('input', (e) => {
                        this.additionalPercentage = parseFloat(e.target.value) || 0;
                        this.calculateTotals();
                    });

                    document.getElementById('additional-value')?.addEventListener('input', (e) => {
                        this.additionalValue = parseFloat(e.target.value) || 0;
                        this.calculateTotals();
                    });

                    document.getElementById('received-from-client')?.addEventListener('input', (e) => {
                        this.receivedFromClient = parseFloat(e.target.value) || 0;
                        this.calculateTotals();
                    });

                    // Template selector
                    document.getElementById('invoice-template')?.addEventListener('change', (e) => {
                        const selectedOption = e.target.options[e.target.selectedIndex];
                        const columnsJson = selectedOption.getAttribute('data-columns');
                        if (columnsJson) {
                            try {
                                this.visibleColumns = JSON.parse(columnsJson);
                                console.log('✅ Template changed. Visible columns:', this.visibleColumns);
                                this.updateTableHeaders();
                                this.renderItems();
                            } catch (error) {
                                console.error('❌ Error parsing columns:', error);
                            }
                        }
                    });

                    console.log('✅ Event listeners attached');
                },

                // Update table headers based on visible columns
                updateTableHeaders() {
                    const thead = document.querySelector('.invoice-data-grid thead tr');
                    if (!thead) return;

                    // Clear existing headers (except action column)
                    thead.innerHTML = '';

                    // Add headers for visible columns
                    this.visibleColumns.forEach(col => {
                        const th = document.createElement('th');
                        th.className = 'font-bold fw-bold text-center';
                        th.style.fontSize = '0.8rem';
                        th.textContent = this.allColumns[col] || col;
                        thead.appendChild(th);
                    });

                    // Add action column
                    const actionTh = document.createElement('th');
                    actionTh.className = 'font-bold fw-bold text-center';
                    actionTh.style.fontSize = '0.8rem';
                    actionTh.textContent = '{{ __('Action') }}';
                    thead.appendChild(actionTh);

                    console.log('✅ Table headers updated');
                },

                // Handle search
                handleSearch(term) {
                    console.log('🔍 handleSearch called with term:', term);
                    console.log('📦 allItems count:', this.allItems.length);
                    console.log('📦 allItems sample:', this.allItems.slice(0, 2));

                    if (!term || term.length < 1) {
                        console.log('⚠️ Term too short, hiding results');
                        this.hideSearchResults();
                        return;
                    }

                    console.log('🔍 Searching for:', term);

                    // Simple vanilla JS search - no Fuse.js
                    const lowerTerm = term.toLowerCase();
                    this.searchResults = this.allItems.filter(item => {
                        const nameMatch = item.name && item.name.toLowerCase().includes(lowerTerm);
                        const codeMatch = item.code && item.code.toString().toLowerCase().includes(lowerTerm);
                        const barcodeMatch = item.barcode && item.barcode.toLowerCase().includes(lowerTerm);
                        return nameMatch || codeMatch || barcodeMatch;
                    }).slice(0, 50);

                    this.selectedIndex = this.searchResults.length > 0 ? 0 : -1;

                    console.log('📋 Found', this.searchResults.length, 'results');
                    console.log('📋 First 3 results:', this.searchResults.slice(0, 3));

                    this.renderSearchResults();
                    this.showSearchResults();
                },

                // Handle search keydown
                handleSearchKeydown(e) {
                    const dropdown = document.getElementById('search-results-dropdown');
                    if (!dropdown || dropdown.classList.contains('hidden')) return;

                    switch (e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            if (this.selectedIndex < this.searchResults.length - 1) {
                                this.selectedIndex++;
                                this.renderSearchResults();
                            }
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            if (this.selectedIndex > 0) {
                                this.selectedIndex--;
                                this.renderSearchResults();
                            }
                            break;
                        case 'Enter':
                            e.preventDefault();
                            if (this.selectedIndex >= 0 && this.searchResults[this.selectedIndex]) {
                                this.addItem(this.searchResults[this.selectedIndex]);
                            } else {
                                const searchInput = document.getElementById('search-input');
                                if (searchInput && searchInput.value.trim()) {
                                    this.createNewItem(searchInput.value.trim());
                                }
                            }
                            break;
                        case 'Escape':
                            e.preventDefault();
                            this.hideSearchResults();
                            break;
                    }
                },

                // Render search results
                renderSearchResults() {
                    console.log('🎨 renderSearchResults called. Results count:', this.searchResults.length);
                    const dropdown = document.getElementById('search-results-dropdown');

                    if (!dropdown) {
                        console.error('❌ Dropdown element not found! ID: search-results-dropdown');
                        console.log('Available elements:', document.querySelectorAll('[id*="search"]'));
                        return;
                    }

                    console.log('✅ Dropdown element found:', dropdown);
                    dropdown.innerHTML = '';

                    if (this.searchResults.length === 0) {
                        const searchInput = document.getElementById('search-input');
                        const searchTerm = searchInput?.value || '';
                        console.log('📝 No results, showing create button for:', searchTerm);

                        if (searchTerm.trim().length > 0) {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action text-primary';
                            btn.innerHTML = '<i class="fas fa-plus-circle me-2"></i><strong>إنشاء صنف جديد: ' + searchTerm +
                                '</strong>';
                            btn.onclick = () => this.createNewItem(searchTerm);
                            dropdown.appendChild(btn);
                            console.log('✅ Create button added');
                        }
                    } else {
                        console.log('📝 Rendering', this.searchResults.length, 'result items');
                        this.searchResults.forEach((item, index) => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between';
                            if (index === this.selectedIndex) btn.classList.add('active');
                            btn.innerHTML = `
                            <div>
                                <strong>${item.name || 'بدون اسم'}</strong>
                                <small class="text-muted ms-2">كود: ${item.code || '-'}</small>
                            </div>
                            <span class="badge bg-primary">${(item.price || 0).toFixed(2)} ج.م</span>
                        `;
                            btn.onclick = () => this.addItem(item);
                            dropdown.appendChild(btn);
                        });
                        console.log('✅ Items rendered');
                    }

                    console.log('✅ Dropdown HTML updated. Children count:', dropdown.children.length);
                    console.log('Dropdown innerHTML length:', dropdown.innerHTML.length);
                },

                // Show/hide search results
                showSearchResults() {
                    console.log('👁️ showSearchResults called');
                    const dropdown = document.getElementById('search-results-dropdown');
                    const searchInput = document.getElementById('search-input');

                    if (!dropdown) {
                        console.error('❌ Dropdown element not found in showSearchResults!');
                        return;
                    }

                    if (!searchInput) {
                        console.error('❌ Search input not found!');
                        return;
                    }

                    // Calculate position relative to search input (for fixed positioning)
                    const rect = searchInput.getBoundingClientRect();
                    console.log('📍 Search input position:', {
                        top: rect.top,
                        bottom: rect.bottom,
                        left: rect.left,
                        width: rect.width
                    });

                    dropdown.style.top = (rect.bottom) + 'px';
                    dropdown.style.left = rect.left + 'px';
                    dropdown.style.width = Math.max(rect.width * 2, 400) + 'px';

                    dropdown.classList.remove('hidden');
                    dropdown.style.display = 'block';

                    console.log('✅ Dropdown shown with styles:', {
                        top: dropdown.style.top,
                        left: dropdown.style.left,
                        width: dropdown.style.width,
                        display: dropdown.style.display,
                        visibility: window.getComputedStyle(dropdown).visibility,
                        zIndex: window.getComputedStyle(dropdown).zIndex
                    });
                },

                hideSearchResults() {
                    const dropdown = document.getElementById('search-results-dropdown');
                    if (dropdown) {
                        dropdown.classList.add('hidden');
                        dropdown.style.display = 'none';
                    }
                },

                // Add item to invoice
                addItem(item) {
                    console.log('➕ Adding item:', item);

                    // Ensure we have required fields
                    if (!item.id || !item.name) {
                        console.error('❌ Invalid item data:', item);
                        this.updateStatus('خطأ: بيانات الصنف غير صحيحة', 'danger');
                        return;
                    }

                    // Get default unit
                    const defaultUnitId = item.default_unit_id || item.unit_id || (item.units && item.units.length > 0 ?
                        item.units[0].id : 1);

                    const newItem = {
                        id: Date.now(), // Temporary ID
                        item_id: item.id,
                        name: item.name,
                        code: item.code || '',
                        unit_id: defaultUnitId,
                        quantity: 1,
                        price: parseFloat(item.price) || 0,
                        item_price: parseFloat(item.price) || 0,
                        discount: 0,
                        sub_value: parseFloat(item.price) || 0,
                        batch_number: '',
                        expiry_date: null,
                        available_units: item.units || []
                    };

                    this.invoiceItems.push(newItem);
                    this.renderItems();
                    this.calculateTotals();

                    // Clear search
                    const searchInput = document.getElementById('search-input');
                    if (searchInput) searchInput.value = '';
                    this.hideSearchResults();

                    this.updateStatus('✓ تم إضافة الصنف', 'success');

                    // Focus on quantity
                    setTimeout(() => {
                        const qtyField = document.getElementById('quantity-' + (this.invoiceItems.length - 1));
                        if (qtyField) {
                            qtyField.focus();
                            qtyField.select();
                        }
                    }, 100);

                    console.log('✅ Item added. Total items:', this.invoiceItems.length);
                },

                // Create new item
                createNewItem(name) {
                    if (!name || name.trim().length === 0) return;

                    console.log('➕ Creating new item:', name);
                    this.updateStatus('جاري إنشاء الصنف...', 'primary');

                    fetch('/api/items/quick-create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                name: name.trim(),
                                code: 'AUTO',
                                price: 0,
                                unit_id: 1
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.item) {
                                this.allItems.push(data.item);
                                this.updateStatus('تم تحميل ' + this.allItems.length + ' صنف - البحث جاهز ✓',
                                    'success');
                                this.addItem(data.item);
                            }
                        })
                        .catch(error => {
                            console.error('❌ Error creating item:', error);
                            this.updateStatus('خطأ في إنشاء الصنف', 'danger');
                        });

                    this.hideSearchResults();
                },

                // Render items table
                renderItems() {
                    const tbody = document.getElementById('invoice-items-tbody');
                    if (!tbody) return;

                    // Find the search row
                    const searchRow = tbody.querySelector('.search-row');

                    if (this.invoiceItems.length === 0) {
                        // Remove all rows except search row
                        const rows = tbody.querySelectorAll('tr:not(.search-row)');
                        rows.forEach(row => row.remove());
                        return;
                    }

                    // Generate items HTML
                    const itemsHTML = this.invoiceItems.map((item, index) => this.renderItemRow(item, index)).join('');

                    // Insert items BEFORE search row
                    if (searchRow) {
                        // Remove old item rows (keep search row)
                        const rows = tbody.querySelectorAll('tr:not(.search-row)');
                        rows.forEach(row => row.remove());

                        // Insert new items before search row
                        searchRow.insertAdjacentHTML('beforebegin', itemsHTML);
                    } else {
                        // Fallback: just set innerHTML
                        tbody.innerHTML = itemsHTML;
                    }

                    // Attach event listeners to inputs
                    this.attachItemEventListeners();
                },

                // Render single item row
                renderItemRow(item, index) {
                    let html =
                        `<tr data-index="${index}" onclick="InvoiceApp.showItemDetails(${index})" style="cursor: pointer;">`;

                    // Render columns based on visible columns
                    this.visibleColumns.forEach(col => {
                        html += this.renderColumn(col, item, index);
                    });

                    // Action column
                    html += `
                    <td class="action-cell" style="width: 50px;" onclick="event.stopPropagation();">
                        <button type="button" class="btn btn-link text-danger p-0" onclick="InvoiceApp.removeItem(${index})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>`;

                    return html;
                },

                // Render single column
                renderColumn(columnName, item, index) {
                    switch (columnName) {
                        case 'item_name':
                            return `
                            <td style="width: 18%;">
                                <div class="static-text" style="font-weight: 900; font-size: 1.2rem; color: #000;">
                                    ${item.name}
                                </div>
                            </td>`;

                        case 'code':
                            return `
                            <td style="width: 10%;">
                                <div class="static-text">${item.code || ''}</div>
                            </td>`;

                        case 'unit':
                            return `
                            <td style="width: 10%;" onclick="event.stopPropagation();">
                                <select id="unit-${index}" class="form-control" data-index="${index}" data-field="unit">
                                    ${(item.available_units || []).map(unit => `
                                                                <option value="${unit.id}" data-u-val="${unit.u_val}" ${unit.id == item.unit_id ? 'selected' : ''}>
                                                                    ${unit.name}
                                                                </option>
                                                            `).join('')}
                                </select>
                            </td>`;

                        case 'quantity':
                            return `
                            <td style="width: 10%;" onclick="event.stopPropagation();">
                                <input type="number" id="quantity-${index}" class="form-control text-center"
                                       value="${item.quantity}" step="0.001" min="0"
                                       data-index="${index}" data-field="quantity">
                            </td>`;

                        case 'batch_number':
                            return `
                            <td style="width: 12%;" onclick="event.stopPropagation();">
                                <input type="text" id="batch-${index}" class="form-control text-center"
                                       value="${item.batch_number || ''}"
                                       data-index="${index}" data-field="batch">
                            </td>`;

                        case 'expiry_date':
                            return `
                            <td style="width: 12%;" onclick="event.stopPropagation();">
                                <input type="date" id="expiry-${index}" class="form-control text-center"
                                       value="${item.expiry_date || ''}"
                                       data-index="${index}" data-field="expiry">
                            </td>`;

                        case 'price':
                            return `
                            <td style="width: 15%;" onclick="event.stopPropagation();">
                                <input type="number" id="price-${index}" class="form-control text-center"
                                       value="${item.price}" step="0.01"
                                       data-index="${index}" data-field="price">
                            </td>`;

                        case 'discount':
                            return `
                            <td style="width: 15%;" onclick="event.stopPropagation();">
                                <input type="number" id="discount-${index}" class="form-control text-center"
                                       value="${item.discount}" step="0.01"
                                       data-index="${index}" data-field="discount">
                            </td>`;

                        case 'sub_value':
                            return `
                            <td style="width: 15%;" onclick="event.stopPropagation();">
                                <input type="number" id="sub-value-${index}" class="form-control text-center"
                                       value="${item.sub_value}" readonly>
                            </td>`;

                        case 'length':
                        case 'width':
                        case 'height':
                        case 'density':
                            return `
                            <td style="width: 10%;" onclick="event.stopPropagation();">
                                <input type="number" id="${columnName}-${index}" class="form-control text-center"
                                       value="${item[columnName] || 0}" step="0.01"
                                       data-index="${index}" data-field="${columnName}">
                            </td>`;

                        default:
                            return `<td></td>`;
                    }
                },

                // Attach event listeners to item inputs
                attachItemEventListeners() {
                    // Quantity, price, discount inputs
                    document.querySelectorAll('[data-field="quantity"], [data-field="price"], [data-field="discount"]')
                        .forEach(input => {
                            input.addEventListener('input', (e) => {
                                const index = parseInt(e.target.dataset.index);
                                const field = e.target.dataset.field;
                                const value = parseFloat(e.target.value) || 0;

                                this.invoiceItems[index][field] = value;
                                this.calculateItemTotal(index);
                            });

                            input.addEventListener('focus', (e) => e.target.select());
                        });

                    // Unit select
                    document.querySelectorAll('[data-field="unit"]').forEach(select => {
                        select.addEventListener('change', (e) => {
                            const index = parseInt(e.target.dataset.index);
                            const selectedOption = e.target.options[e.target.selectedIndex];
                            const uVal = parseFloat(selectedOption.dataset.uVal) || 1;

                            this.invoiceItems[index].unit_id = parseInt(e.target.value);
                            this.invoiceItems[index].price = (this.invoiceItems[index].item_price || 0) *
                                uVal;

                            this.calculateItemTotal(index);
                            this.renderItems();
                        });
                    });

                    // Batch and expiry
                    document.querySelectorAll('[data-field="batch"], [data-field="expiry"]').forEach(input => {
                        input.addEventListener('input', (e) => {
                            const index = parseInt(e.target.dataset.index);
                            const field = e.target.dataset.field;

                            if (field === 'batch') {
                                this.invoiceItems[index].batch_number = e.target.value;
                            } else if (field === 'expiry') {
                                this.invoiceItems[index].expiry_date = e.target.value;
                            }
                        });
                    });
                },

                // Calculate item total
                calculateItemTotal(index) {
                    const item = this.invoiceItems[index];
                    const quantity = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.price) || 0;
                    const discount = parseFloat(item.discount) || 0;

                    item.sub_value = parseFloat(((quantity * price) - discount).toFixed(2));

                    // Update display
                    const subValueInput = document.getElementById('sub-value-' + index);
                    if (subValueInput) {
                        subValueInput.value = item.sub_value;
                    }

                    this.calculateTotals();
                },

                // Calculate totals
                calculateTotals() {
                    // Subtotal
                    this.subtotal = this.invoiceItems.reduce((sum, item) => sum + (parseFloat(item.sub_value) || 0), 0);
                    this.subtotal = parseFloat(this.subtotal.toFixed(2));

                    // Discount
                    if (this.discountPercentage > 0) {
                        this.discountValue = parseFloat(((this.subtotal * this.discountPercentage) / 100).toFixed(2));
                    } else if (this.subtotal > 0 && this.discountValue > 0) {
                        this.discountPercentage = parseFloat(((this.discountValue / this.subtotal) * 100).toFixed(2));
                    }

                    const afterDiscount = parseFloat((this.subtotal - this.discountValue).toFixed(2));

                    // Additional
                    if (this.additionalPercentage > 0) {
                        this.additionalValue = parseFloat(((afterDiscount * this.additionalPercentage) / 100).toFixed(2));
                    } else if (afterDiscount > 0 && this.additionalValue > 0) {
                        this.additionalPercentage = parseFloat(((this.additionalValue / afterDiscount) * 100).toFixed(2));
                    }

                    const afterAdditional = parseFloat((afterDiscount + this.additionalValue).toFixed(2));

                    // VAT
                    this.vatValue = parseFloat(((afterAdditional * this.vatPercentage) / 100).toFixed(2));

                    // Withholding Tax
                    this.withholdingTaxValue = parseFloat(((afterAdditional * this.withholdingTaxPercentage) / 100).toFixed(
                        2));

                    // Total
                    this.totalAfterAdditional = parseFloat((afterAdditional + this.vatValue - this.withholdingTaxValue)
                        .toFixed(2));

                    // Remaining
                    this.remaining = parseFloat((this.totalAfterAdditional - this.receivedFromClient).toFixed(2));

                    this.updateTotalsDisplay();
                },

                // Update totals display
                updateTotalsDisplay() {
                    // Update all display fields
                    const displayUpdates = {
                        'display-subtotal': this.subtotal.toFixed(2),
                        'display-total': this.totalAfterAdditional.toFixed(2),
                        'display-received': this.receivedFromClient.toFixed(2),
                        'display-remaining': this.remaining.toFixed(2)
                    };

                    Object.entries(displayUpdates).forEach(([id, value]) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = value;
                    });

                    // Update readonly input fields
                    const inputUpdates = {
                        'vat-value-display': this.vatValue.toFixed(2),
                        'withholding-tax-value-display': this.withholdingTaxValue.toFixed(2)
                    };

                    Object.entries(inputUpdates).forEach(([id, value]) => {
                        const el = document.getElementById(id);
                        if (el) el.value = value;
                    });

                    // Update remaining color
                    const remainingEl = document.getElementById('display-remaining');
                    if (remainingEl) {
                        remainingEl.classList.remove('text-danger', 'text-success');
                        if (this.remaining > 0.01) {
                            remainingEl.classList.add('text-danger');
                        } else if (this.remaining < -0.01) {
                            remainingEl.classList.add('text-success');
                        }
                    }
                },

                // Remove item
                removeItem(index) {
                    if (confirm('هل تريد حذف هذا الصنف؟')) {
                        this.invoiceItems.splice(index, 1);
                        this.renderItems();
                        this.calculateTotals();
                    }
                },

                // Show item details in footer (Client-Side)
                showItemDetails(index) {
                    const item = this.invoiceItems[index];
                    if (!item) return;

                    console.log('📋 Showing item details:', item);

                    // Update footer with item details
                    document.getElementById('selected-item-name').textContent = item.name || '-';
                    document.getElementById('selected-item-store').textContent =
                        '-'; // Store name would come from item data
                    document.getElementById('selected-item-available').textContent =
                        '-'; // Available quantity would come from API
                    document.getElementById('selected-item-total').textContent = '-'; // Total quantity would come from API
                    document.getElementById('selected-item-unit').textContent = item.unit_name || '-';
                    document.getElementById('selected-item-price').textContent = (item.price || 0).toFixed(2);
                    document.getElementById('selected-item-last-price').textContent = '-'; // Would come from API
                    document.getElementById('selected-item-avg-cost').textContent = '-'; // Would come from API
                },

                /**
                 * Update account balance when account changes
                 */
                updateAccountBalance(accountId) {
                    if (!accountId) {
                        this.currentBalance = 0;
                        this.calculateBalance();
                        return;
                    }

                    // Fetch account balance from API
                    fetch(`/api/accounts/${accountId}/balance`)
                        .then(response => response.json())
                        .then(data => {
                            this.currentBalance = parseFloat(data.balance) || 0;
                            this.calculateBalance();

                            // Update display
                            const balanceDisplay = document.getElementById('current-balance-header');
                            if (balanceDisplay) {
                                balanceDisplay.textContent = this.currentBalance.toFixed(2);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching account balance:', error);
                        });
                },

                /**
                 * Calculate balance after invoice
                 */
                calculateBalance() {
                    const invoiceType = parseInt(this.type) || 10;

                    if ([10, 12, 14, 16].includes(invoiceType)) {
                        // Sales invoices - increase debit balance
                        this.calculatedBalanceAfter = this.currentBalance + this.totalAfterAdditional;
                    } else {
                        // Purchase invoices - decrease debit balance
                        this.calculatedBalanceAfter = this.currentBalance - this.totalAfterAdditional;
                    }

                    // Update display
                    const balanceAfterDisplay = document.getElementById('balance-after-header');
                    if (balanceAfterDisplay) {
                        balanceAfterDisplay.textContent = this.calculatedBalanceAfter.toFixed(2);
                        balanceAfterDisplay.className = this.calculatedBalanceAfter < 0 ? 'badge bg-danger' :
                            'badge bg-success';
                    }
                },

                // Save invoice
                saveInvoice() {
                    console.log('💾 Saving invoice...');

                    if (this.invoiceItems.length === 0) {
                        alert('يرجى إضافة صنف واحد على الأقل');
                        return;
                    }

                    const invoiceData = {
                        type: this.type,
                        branch_id: this.branchId,
                        acc1_id: document.getElementById('acc1-id')?.value,
                        acc2_id: document.getElementById('acc2-id')?.value,
                        emp_id: document.getElementById('emp-id')?.value,
                        delivery_id: document.getElementById('delivery-id')?.value,
                        pro_date: document.getElementById('pro-date')?.value,
                        accural_date: document.getElementById('accural-date')?.value,
                        serial_number: document.getElementById('serial-number')?.value,
                        cash_box_id: document.getElementById('cash-box-id')?.value,
                        notes: document.getElementById('notes')?.value,
                        items: this.invoiceItems,
                        discount_percentage: this.discountPercentage,
                        discount_value: this.discountValue,
                        additional_percentage: this.additionalPercentage,
                        additional_value: this.additionalValue,
                        vat_percentage: this.vatPercentage,
                        vat_value: this.vatValue,
                        withholding_tax_percentage: this.withholdingTaxPercentage,
                        withholding_tax_value: this.withholdingTaxValue,
                        received_from_client: this.receivedFromClient,
                        subtotal: this.subtotal,
                        total: this.totalAfterAdditional,
                    };

                    fetch('/api/invoices', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(invoiceData),
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.data) {
                                alert('تم حفظ الفاتورة بنجاح');
                                window.location.href = `/invoices/${result.data.id}`;
                            } else {
                                alert('خطأ: ' + (result.message || 'فشل حفظ الفاتورة'));
                            }
                        })
                        .catch(error => {
                            console.error('❌ Error saving invoice:', error);
                            alert('حدث خطأ أثناء حفظ الفاتورة');
                        });
                },

                // Update status message
                updateStatus(text, type = 'info') {
                    const status = document.getElementById('search-status');
                    if (status) {
                        status.innerHTML = text;
                        status.className = 'text-' + type;
                    }
                },

                // Debug function - test search manually
                testSearch(term = 'test') {
                    console.log('🧪 Testing search with term:', term);
                    console.log('📦 Total items loaded:', this.allItems.length);
                    console.log('📦 First 5 items:', this.allItems.slice(0, 5));

                    // Check if dropdown exists
                    const dropdown = document.getElementById('search-results-dropdown');
                    console.log('Dropdown exists:', !!dropdown);
                    if (dropdown) {
                        console.log('Dropdown display:', dropdown.style.display);
                        console.log('Dropdown classes:', dropdown.className);
                    }

                    // Check if search input exists
                    const searchInput = document.getElementById('search-input');
                    console.log('Search input exists:', !!searchInput);
                    if (searchInput) {
                        console.log('Search input value:', searchInput.value);
                    }

                    // Run search
                    this.handleSearch(term);
                },

                // Debug function - show dropdown manually
                forceShowDropdown() {
                    console.log('🔧 Force showing dropdown...');
                    const dropdown = document.getElementById('search-results-dropdown');
                    if (dropdown) {
                        dropdown.style.display = 'block';
                        dropdown.style.visibility = 'visible';
                        dropdown.style.opacity = '1';
                        dropdown.style.position = 'fixed';
                        dropdown.style.top = '200px';
                        dropdown.style.left = '200px';
                        dropdown.style.zIndex = '999999';
                        dropdown.style.background = 'white';
                        dropdown.style.border = '2px solid red';
                        dropdown.style.padding = '20px';
                        dropdown.innerHTML =
                            '<div style="color: red; font-size: 20px;">TEST DROPDOWN - إذا ظهرت هذه الرسالة، الـ dropdown موجود!</div>';
                        dropdown.classList.remove('hidden');
                        console.log('✅ Dropdown forced to show');
                    } else {
                        console.error('❌ Dropdown not found!');
                    }
                }
            };

            // Initialize when DOM is ready AND jQuery + Select2 are loaded
            function initWhenReady() {
                if (typeof jQuery === 'undefined') {
                    console.log('⏳ Waiting for jQuery...');
                    setTimeout(initWhenReady, 100);
                    return;
                }

                if (typeof jQuery.fn.select2 === 'undefined') {
                    console.log('⏳ Waiting for Select2...');
                    setTimeout(initWhenReady, 100);
                    return;
                }

                console.log('✅ jQuery and Select2 loaded');
                InvoiceApp.init();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWhenReady);
            } else {
                initWhenReady();
            }

            // Expose reload function
            window.reloadSearchItems = () => InvoiceApp.loadItems();

            console.log('✅ Script loaded successfully');
            console.log('✅ InvoiceApp object:', typeof window.InvoiceApp);
            console.log('✅ InvoiceApp.init:', typeof window.InvoiceApp.init);
        </script>
@endsection
