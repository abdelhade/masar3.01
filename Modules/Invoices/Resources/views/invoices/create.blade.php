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
            #search-results-dropdown * {
                visibility: visible !important;
                opacity: 1 !important;
            }

            #search-results-dropdown>div {
                display: flex !important;
            }
        </style>
    @endpush
    {{-- Pure HTML - No Alpine --}}
    <div id="invoice-app">
        <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}">
            @csrf

            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3">
                    <b><i class="las la-check-circle"></i> {{ session('success') }}</b>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("تم بنجاح") }}',
                            text: '{{ session("success") }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                </script>
            @endif

            {{-- Error Display --}}
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- Hidden inputs for all invoice data --}}
            <input type="hidden" name="type" id="form-type">
            <input type="hidden" name="branch_id" id="form-branch-id">
            <input type="hidden" name="acc1_id" id="form-acc1-id">
            <input type="hidden" name="acc2_id" id="form-acc2-id">
            <input type="hidden" name="pro_date" id="form-pro-date">
            <input type="hidden" name="emp_id" id="form-emp-id">
            <input type="hidden" name="delivery_id" id="form-delivery-id">
            <input type="hidden" name="accural_date" id="form-accural-date">
            <input type="hidden" name="serial_number" id="form-serial-number">
            <input type="hidden" name="cash_box_id" id="form-cash-box-id">
            <input type="hidden" name="notes" id="form-notes">
            <input type="hidden" name="discount_percentage" id="form-discount-percentage">
            <input type="hidden" name="discount_value" id="form-discount-value">
            <input type="hidden" name="additional_percentage" id="form-additional-percentage">
            <input type="hidden" name="additional_value" id="form-additional-value">
            <input type="hidden" name="vat_percentage" id="form-vat-percentage">
            <input type="hidden" name="vat_value" id="form-vat-value">
            <input type="hidden" name="withholding_tax_percentage" id="form-withholding-tax-percentage">
            <input type="hidden" name="withholding_tax_value" id="form-withholding-tax-value">
            <input type="hidden" name="subtotal" id="form-subtotal">
            <input type="hidden" name="total_after_additional" id="form-total-after-additional">
            <input type="hidden" name="received_from_client" id="form-received-from-client">
            <input type="hidden" name="remaining" id="form-remaining">
            <input type="hidden" name="currency_id" id="form-currency-id" value="1">
            <input type="hidden" name="currency_rate" id="form-currency-rate" value="1">
            <div id="form-items-container"></div>

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
            'vatPercentage' => isVatEnabled() ? setting('vat_percentage', 15) : 0,
            'withholdingTaxPercentage' => setting('withholding_tax_percentage', 0),
            'showBalance' => setting('show_balance', '1') === '1',
            'cashAccounts' => $cashAccounts,
        ])
    </div>
@endsection

@section('script')
    @php
        // ✅ Prepare all config as JSON to avoid Blade syntax issues
        $invoiceConfig = [
            'type' => $type,
            'branchId' => $branchId ?? null,
            'vatPercentage' => isVatEnabled() ? setting('vat_percentage', 15) : 0,
            'withholdingTaxPercentage' => setting('withholding_tax_percentage', 0),
            'storeUrl' => route('invoices.store'),
            'translations' => [
                'item_name' => __('Item Name'),
                'code' => __('Code'),
                'unit' => __('Unit'),
                'quantity' => __('Quantity'),
                'batch_number' => __('Batch Number'),
                'expiry_date' => __('Expiry Date'),
                'price' => __('Price'),
                'discount' => __('Discount'),
                'sub_value' => __('Value'),
                'length' => __('Length'),
                'width' => __('Width'),
                'height' => __('Height'),
                'density' => __('Density'),
                'action' => __('Action'),
            ]
        ];
    @endphp

    {{-- Main Invoice JavaScript --}}
    <script>
        // ✅ Config from PHP (safe JSON encoding)
        const CONFIG = @json($invoiceConfig);
        const INVOICE_STORE_URL = CONFIG.storeUrl;
        
        // Invoice State (Global)
        window.InvoiceApp = {
            // Config
            type: CONFIG.type,
            branchId: CONFIG.branchId,
            vatPercentage: CONFIG.vatPercentage,
            withholdingTaxPercentage: CONFIG.withholdingTaxPercentage,
            currencyId: 1, // Default
            exchangeRate: 1, // Default

            // Template columns
            visibleColumns: ['item_name', 'code', 'unit', 'quantity', 'price', 'discount', 'sub_value'],
            allColumns: CONFIG.translations,

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
                this.initializeSelect2();
                this.loadDefaultTemplate();
                this.setDefaultValues();
                this.loadItems();
                this.attachEventListeners();
                this.renderItems();
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
            },

            // Set default values from settings
            setDefaultValues() {
                // Set default employee
                const defaultEmployeeId = '{{ $defaultEmployeeId ?? '' }}';
                if (defaultEmployeeId) {
                    document.getElementById('emp-id').value = defaultEmployeeId;
                }

                // Set default delivery
                const defaultDeliveryId = '{{ $defaultDeliveryId ?? '' }}';
                if (defaultDeliveryId) {
                    document.getElementById('delivery-id').value = defaultDeliveryId;
                }

                // Set default store
                const defaultStoreId = '{{ $defaultStoreId ?? '' }}';
                if (defaultStoreId) {
                    document.getElementById('acc2-id').value = defaultStoreId;
                }

                // Set default customer/supplier based on invoice type
                const invoiceType = {{ $type }};
                const defaultCustomerId = '{{ $defaultCustomerId ?? '' }}';
                const defaultSupplierId = '{{ $defaultSupplierId ?? '' }}';

                if ([10, 12, 14, 16, 19, 22].includes(invoiceType) && defaultCustomerId) {
                    // Sales invoices - set default customer
                    $('#acc1-id').val(defaultCustomerId).trigger('change');
                } else if ([11, 13, 15, 17, 20, 23].includes(invoiceType) && defaultSupplierId) {
                    // Purchase invoices - set default supplier
                    $('#acc1-id').val(defaultSupplierId).trigger('change');
                }
            },

            // Load items from API
            loadItems() {
                const url = `/api/items/lite?branch_id=${this.branchId}&type=${this.type}&_t=${Date.now()}`;
                this.updateStatus('جاري تحميل الأصناف...', 'primary');

                fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {

                        if (Array.isArray(data)) {
                            this.allItems = data;
                            this.updateStatus('تم تحميل ' + data.length + ' صنف - البحث جاهز ✓', 'success');
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
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('#search-results-dropdown') && e.target.id !== 'search-input') {
                        this.hideSearchResults();
                    }
                });
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
                            this.updateTableHeaders();
                            this.renderItems();
                        } catch (error) {
                            console.error('❌ Error parsing columns:', error);
                        }
                    }
                });

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
                actionTh.textContent = CONFIG.translations.action;
                thead.appendChild(actionTh);
            },

            // Handle search
            handleSearch(term) {

                if (!term || term.length < 1) {
                    this.hideSearchResults();
                    return;
                }
                // Simple vanilla JS search - no Fuse.js
                const lowerTerm = term.toLowerCase();
                this.searchResults = this.allItems.filter(item => {
                    const nameMatch = item.name && item.name.toLowerCase().includes(lowerTerm);
                    const codeMatch = item.code && item.code.toString().toLowerCase().includes(lowerTerm);
                    const barcodeMatch = item.barcode && item.barcode.toLowerCase().includes(lowerTerm);
                    return nameMatch || codeMatch || barcodeMatch;
                }).slice(0, 50);

                this.selectedIndex = this.searchResults.length > 0 ? 0 : -1;

                this.renderSearchResults();
                this.showSearchResults();
            },

            // Handle search keydown
            handleSearchKeydown(e) {
                const dropdown = document.getElementById('search-results-dropdown');
                const isDropdownVisible = dropdown && dropdown.style.display === 'block' && !dropdown.classList
                    .contains('hidden');

                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (isDropdownVisible && this.selectedIndex >= 0 && this.searchResults[this.selectedIndex]) {
                        // Add selected item
                        this.addItem(this.searchResults[this.selectedIndex]);
                    } else if (isDropdownVisible && this.searchResults.length > 0) {
                        // Auto-select first result if none selected
                        this.addItem(this.searchResults[0]);
                    } else {
                        // Create new item if no results
                        const searchInput = document.getElementById('search-input');
                        if (searchInput && searchInput.value.trim()) {
                            this.createNewItem(searchInput.value.trim());
                        }
                    }
                    return;
                }

                if (!isDropdownVisible) return;

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (this.selectedIndex < this.searchResults.length - 1) {
                            this.selectedIndex++;
                            this.highlightSelectedResult();
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (this.selectedIndex > 0) {
                            this.selectedIndex--;
                            this.highlightSelectedResult();
                        }
                        break;
                    case 'Escape':
                        e.preventDefault();
                        this.hideSearchResults();
                        break;
                }
            },
            highlightSelectedResult() {
                const dropdown = document.getElementById('search-results-dropdown');
                if (!dropdown) return;

                const items = dropdown.querySelectorAll('.search-result-item');
                items.forEach((item, index) => {
                    if (index === this.selectedIndex) {
                        // ✅ نفس اللون الأزرق الواضح
                        item.style.background = '#5b6ef5 !important';
                        item.style.borderLeft = '5px solid #4051d4';
                        item.style.boxShadow = '0 2px 8px rgba(91, 110, 245, 0.4)';

                        // ✅ خلي النصوص كلها بيضا
                        const allDivs = item.querySelectorAll('div');
                        allDivs.forEach(el => {
                            el.style.color = 'white !important';
                        });

                        // ✅ حتى الـ price badge يبقى أبيض
                        const priceDiv = item.querySelector('div:last-child');
                        if (priceDiv) {
                            priceDiv.style.background = 'rgba(255, 255, 255, 0.2) !important';
                            priceDiv.style.color = 'white !important';
                            priceDiv.style.border = '1px solid white';
                        }

                        item.scrollIntoView({
                            block: 'nearest',
                            behavior: 'smooth'
                        });
                    } else {
                        item.style.background = 'white !important';
                        item.style.borderLeft = 'none';
                        item.style.boxShadow = 'none';

                        // ✅ رجع الألوان الأصلية
                        const leftDiv = item.querySelector('div:first-child');
                        if (leftDiv) {
                            const nameDiv = leftDiv.querySelector('div:first-child');
                            const codeDiv = leftDiv.querySelector('div:last-child');
                            if (nameDiv) nameDiv.style.color = '#000 !important';
                            if (codeDiv) codeDiv.style.color = '#666 !important';
                        }

                        // ✅ رجع لون الـ price badge
                        const priceDiv = item.querySelector('div:last-child');
                        if (priceDiv) {
                            priceDiv.style.background = '#667eea !important';
                            priceDiv.style.color = 'white !important';
                            priceDiv.style.border = 'none';
                        }
                    }
                });
            },

            // Render search results
            renderSearchResults() {
                const dropdown = document.getElementById('search-results-dropdown');
                if (!dropdown) {
                    console.error('❌ Dropdown not found!');
                    return;
                }

                dropdown.innerHTML = '';

                if (this.searchResults.length === 0) {
                    const searchInput = document.getElementById('search-input');
                    const searchTerm = searchInput?.value || '';

                    if (searchTerm.trim().length > 0) {
                        const createBtn = document.createElement('div');
                        createBtn.className = 'create-new-item-btn';
                        createBtn.style.cssText = `
                display: block !important;
                padding: 15px !important;
                cursor: pointer !important;
                background: #667eea !important;
                color: white !important;
                font-size: 16px !important;
                font-weight: bold !important;
                border-bottom: 1px solid #e0e0e0 !important;
                text-align: right !important;
            `;
                        createBtn.textContent = '➕ إنشاء صنف جديد: ' + searchTerm;
                        createBtn.onclick = () => this.createNewItem(searchTerm);
                        createBtn.onmouseenter = function() {
                            this.style.background = '#5568d3 !important';
                        };
                        createBtn.onmouseleave = function() {
                            this.style.background = '#667eea !important';
                        };

                        dropdown.appendChild(createBtn);
                    }
                } else {
                    this.searchResults.forEach((item, index) => {
                        const resultDiv = document.createElement('div');
                        resultDiv.className = 'search-result-item';
                        resultDiv.style.cssText = `
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 12px 15px !important;
                cursor: pointer !important;
                background: white !important;
                border-bottom: 1px solid #e0e0e0 !important;
                min-height: 60px !important;
            `;

                        // Left side
                        const leftDiv = document.createElement('div');
                        leftDiv.style.cssText = `
                display: flex !important;
                flex-direction: column !important;
                gap: 4px !important;
                flex: 1 !important;
            `;

                        const nameDiv = document.createElement('div');
                        nameDiv.style.cssText = `
                color: #000 !important;
                font-size: 16px !important;
                font-weight: bold !important;
                line-height: 1.4 !important;
            `;
                        nameDiv.textContent = item.name || 'بدون اسم';

                        const codeDiv = document.createElement('div');
                        codeDiv.style.cssText = `
                color: #666 !important;
                font-size: 13px !important;
                line-height: 1.4 !important;
            `;
                        codeDiv.textContent = 'كود: ' + (item.code || '-');

                        leftDiv.appendChild(nameDiv);
                        leftDiv.appendChild(codeDiv);

                        // Right side
                        const priceDiv = document.createElement('div');
                        priceDiv.style.cssText = `
                background: #667eea !important;
                color: white !important;
                padding: 8px 15px !important;
                border-radius: 5px !important;
                font-size: 14px !important;
                font-weight: bold !important;
                white-space: nowrap !important;
                margin-right: 10px !important;
            `;
                        priceDiv.textContent = (parseFloat(item.price) || 0).toFixed(2) + ' ج.م';

                        resultDiv.appendChild(leftDiv);
                        resultDiv.appendChild(priceDiv);

                        // ✅ Highlight selected item
                        if (index === this.selectedIndex) {
                            resultDiv.style.background = '#5b6ef5 !important';
                            resultDiv.style.borderLeft = '5px solid #4051d4';
                            resultDiv.style.boxShadow = '0 2px 8px rgba(91, 110, 245, 0.4)';

                            // ✅ خلي النصوص بيضا
                            nameDiv.style.color = 'white !important';
                            codeDiv.style.color = 'white !important';
                            priceDiv.style.background = 'rgba(255, 255, 255, 0.2) !important';
                            priceDiv.style.color = 'white !important';
                            priceDiv.style.border = '1px solid white';
                        }

                        // Hover effects
                        resultDiv.onmouseenter = function() {
                            if (index !== InvoiceApp.selectedIndex) {
                                this.style.background = '#f5f5f5 !important';
                            }
                        };
                        resultDiv.onmouseleave = function() {
                            if (index !== InvoiceApp.selectedIndex) {
                                this.style.background = 'white !important';
                                this.style.borderLeft = 'none';
                            }
                        };

                        resultDiv.onclick = () => this.addItem(item);

                        dropdown.appendChild(resultDiv);
                    });
                }
            },

            // Show/hide search results
            showSearchResults() {
                const dropdown = document.getElementById('search-results-dropdown');
                const searchInput = document.getElementById('search-input');

                if (!dropdown) {
                    console.error('❌ Dropdown element not found!');
                    return;
                }

                if (!searchInput) {
                    console.error('❌ Search input not found!');
                    return;
                }

                // Calculate position relative to search input
                const rect = searchInput.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                // Calculate dropdown width (don't exceed viewport)
                const maxWidth = Math.min(550, viewportWidth - 40); // Max 800px or viewport - 40px margin
                const dropdownWidth = Math.min(rect.width * 2, maxWidth);

                // Calculate left position (ensure it stays in viewport)
                let leftPosition = rect.left;
                if (leftPosition + dropdownWidth > viewportWidth) {
                    // If dropdown exceeds viewport, align to right edge
                    leftPosition = viewportWidth - dropdownWidth - 20; // 20px margin from edge
                }

                // Calculate top position (below search input)
                let topPosition = rect.bottom + 2;

                // If dropdown would go below viewport, show it above input instead
                const estimatedHeight = 300; // Estimated dropdown height
                if (topPosition + estimatedHeight > viewportHeight) {
                    topPosition = rect.top - estimatedHeight - 2;
                }

                // Set ALL required styles with corrected positioning
                dropdown.style.position = 'fixed';
                dropdown.style.top = topPosition + 'px';
                dropdown.style.left = leftPosition + 'px';
                dropdown.style.width = dropdownWidth + 'px';
                dropdown.style.maxWidth = maxWidth + 'px';
                dropdown.style.maxHeight = '400px';
                dropdown.style.overflowY = 'auto';
                dropdown.style.background = 'white';
                dropdown.style.border = '2px solid #667eea'; // Make it more visible
                dropdown.style.borderRadius = '8px';
                dropdown.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.3)'; // Stronger shadow
                dropdown.style.zIndex = '999999';
                dropdown.style.display = 'block';
                dropdown.style.visibility = 'visible';
                dropdown.style.opacity = '1';

                dropdown.classList.remove('hidden');

            },

            hideSearchResults() {
                const dropdown = document.getElementById('search-results-dropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                    dropdown.style.display = 'none';
                }
            },

            // Add item to invoice
            // Add item to invoice
            addItem(item) {
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
                    id: Date.now(),
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

                // ✅ Focus على أول حقل editable في النموذج
                setTimeout(() => {
                    const itemIndex = this.invoiceItems.length - 1;
                    const editableColumns = this.getEditableColumns();

                    if (editableColumns.length > 0) {
                        const firstEditableColumn = editableColumns[0];
                        this.focusField(firstEditableColumn, itemIndex);
                    }
                }, 100);
            },

            // Create new item
            createNewItem(name) {
                if (!name || name.trim().length === 0) return;

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
                                    value="${item.sub_value}" readonly tabindex="-1">
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
                document.querySelectorAll('[id^="sub-value-"]').forEach(input => {
                    input.addEventListener('focus', (e) => {
                        e.preventDefault();
                        e.target.blur(); // ✅ ارجع الـ focus فوراً

                        // ✅ روح على البحث بدلاً منه
                        const searchInput = document.getElementById('search-input');
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select();
                        }
                    });

                    // ✅ امنع keyboard navigation على sub_value
                    input.addEventListener('keydown', (e) => {
                        e.preventDefault();
                        if (e.key === 'Enter' || e.key === 'Tab') {
                            const searchInput = document.getElementById('search-input');
                            if (searchInput) {
                                searchInput.focus();
                                searchInput.select();
                            }
                        }
                    });
                });

                // Quantity, price, discount inputs
                document.querySelectorAll(
                        '[data-field="quantity"], [data-field="price"], [data-field="discount"]')
                    .forEach(input => {
                        input.addEventListener('input', (e) => {
                            const index = parseInt(e.target.dataset.index);
                            const field = e.target.dataset.field;
                            const value = parseFloat(e.target.value) || 0;

                            this.invoiceItems[index][field] = value;
                            this.calculateItemTotal(index);
                        });

                        input.addEventListener('focus', (e) => e.target.select());

                        // ✅ Add keyboard navigation - Enter/Tab to next field
                        input.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const index = parseInt(e.target.dataset.index);
                                const field = e.target.dataset.field;
                                this.moveToNextField(index, field, false);
                            } else if (e.key === 'Tab' && !e.shiftKey) {
                                e.preventDefault();
                                const index = parseInt(e.target.dataset.index);
                                const field = e.target.dataset.field;
                                this.moveToNextField(index, field, false);
                            } else if (e.key === 'Tab' && e.shiftKey) {
                                e.preventDefault();
                                const index = parseInt(e.target.dataset.index);
                                const field = e.target.dataset.field;
                                this.moveToNextField(index, field, true);
                            }
                        });
                    });

                // Unit select
                document.querySelectorAll('[data-field="unit"]').forEach(select => {
                    select.addEventListener('change', (e) => {
                        const index = parseInt(e.target.dataset.index);
                        const selectedOption = e.target.options[e.target.selectedIndex];
                        const uVal = parseFloat(selectedOption.dataset.uVal) || 1;

                        this.invoiceItems[index].unit_id = parseInt(e.target.value);
                        this.invoiceItems[index].price = (this.invoiceItems[index]
                                .item_price || 0) *
                            uVal;

                        this.calculateItemTotal(index);
                        this.renderItems();
                    });

                    // ✅ Add keyboard navigation for select
                    select.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const index = parseInt(e.target.dataset.index);
                            this.moveToNextField(index, 'unit', false);
                        }
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

                    // ✅ Add keyboard navigation
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const index = parseInt(e.target.dataset.index);
                            const field = e.target.dataset.field;
                            this.moveToNextField(index, field, false);
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
                this.subtotal = this.invoiceItems.reduce((sum, item) => sum + (parseFloat(item.sub_value) ||
                    0), 0);
                this.subtotal = parseFloat(this.subtotal.toFixed(2));

                // Discount
                if (this.discountPercentage > 0) {
                    this.discountValue = parseFloat(((this.subtotal * this.discountPercentage) / 100)
                        .toFixed(2));
                } else if (this.subtotal > 0 && this.discountValue > 0) {
                    this.discountPercentage = parseFloat(((this.discountValue / this.subtotal) * 100)
                        .toFixed(2));
                }

                const afterDiscount = parseFloat((this.subtotal - this.discountValue).toFixed(2));

                // Additional
                if (this.additionalPercentage > 0) {
                    this.additionalValue = parseFloat(((afterDiscount * this.additionalPercentage) / 100)
                        .toFixed(2));
                } else if (afterDiscount > 0 && this.additionalValue > 0) {
                    this.additionalPercentage = parseFloat(((this.additionalValue / afterDiscount) * 100)
                        .toFixed(2));
                }

                const afterAdditional = parseFloat((afterDiscount + this.additionalValue).toFixed(2));

                // VAT
                this.vatValue = parseFloat(((afterAdditional * this.vatPercentage) / 100).toFixed(2));

                // Withholding Tax
                this.withholdingTaxValue = parseFloat(((afterAdditional * this.withholdingTaxPercentage) /
                    100).toFixed(
                    2));

                // Total
                this.totalAfterAdditional = parseFloat((afterAdditional + this.vatValue - this
                        .withholdingTaxValue)
                    .toFixed(2));

                // Remaining
                this.remaining = parseFloat((this.totalAfterAdditional - this.receivedFromClient).toFixed(
                    2));

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

                // Update footer with item details
                document.getElementById('selected-item-name').textContent = item.name || '-';
                document.getElementById('selected-item-store').textContent =
                    '-'; // Store name would come from item data
                document.getElementById('selected-item-available').textContent =
                    '-'; // Available quantity would come from API
                document.getElementById('selected-item-total').textContent =
                    '-'; // Total quantity would come from API
                document.getElementById('selected-item-unit').textContent = item.unit_name || '-';
                document.getElementById('selected-item-price').textContent = (item.price || 0).toFixed(2);
                document.getElementById('selected-item-last-price').textContent =
                    '-'; // Would come from API
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

            // Save invoice - NO VALIDATION, just send everything
            submitForm() {
                console.log('🔵 Submitting form...');

                // ✅ Fill hidden inputs with current data
                document.getElementById('form-type').value = this.type;
                document.getElementById('form-branch-id').value = this.branchId;
                
                // For Select2 inputs, use jQuery to get current value
                const acc1Val = $('#acc1-id').val();
                const acc2Val = $('#acc2-id').val();
                
                document.getElementById('form-acc1-id').value = acc1Val || '';
                document.getElementById('form-acc2-id').value = acc2Val || '';
                
                console.log('📊 Submitting Data:', {
                    type: this.type,
                    branch_id: this.branchId,
                    acc1_id: acc1Val,
                    acc2_id: acc2Val,
                    currency_id: this.currencyId,
                    currency_rate: this.exchangeRate
                });

                document.getElementById('form-currency-id').value = this.currencyId || 1;
                document.getElementById('form-currency-rate').value = this.exchangeRate || 1;

                document.getElementById('form-pro-date').value = document.getElementById('pro-date')?.value || '';
                document.getElementById('form-emp-id').value = document.getElementById('emp-id')?.value || '';
                document.getElementById('form-delivery-id').value = document.getElementById('delivery-id')?.value || '';
                document.getElementById('form-accural-date').value = document.getElementById('accural-date')?.value || '';
                document.getElementById('form-serial-number').value = document.getElementById('serial-number')?.value || '';
                document.getElementById('form-cash-box-id').value = document.getElementById('cash-box-id')?.value || '';
                document.getElementById('form-notes').value = document.getElementById('notes')?.value || '';
                document.getElementById('form-discount-percentage').value = this.discountPercentage;
                document.getElementById('form-discount-value').value = this.discountValue;
                document.getElementById('form-additional-percentage').value = this.additionalPercentage;
                document.getElementById('form-additional-value').value = this.additionalValue;
                document.getElementById('form-vat-percentage').value = this.vatPercentage;
                document.getElementById('form-vat-value').value = this.vatValue;
                document.getElementById('form-withholding-tax-percentage').value = this.withholdingTaxPercentage;
                document.getElementById('form-withholding-tax-value').value = this.withholdingTaxValue;
                document.getElementById('form-subtotal').value = this.subtotal;
                document.getElementById('form-total-after-additional').value = this.totalAfterAdditional;
                document.getElementById('form-received-from-client').value = this.receivedFromClient;
                document.getElementById('form-remaining').value = this.remaining;

                // ✅ Add items as hidden inputs
                const itemsContainer = document.getElementById('form-items-container');
                itemsContainer.innerHTML = ''; // Clear previous items

                this.invoiceItems.forEach((item, index) => {
                    // Create hidden inputs for each item field
                    const fields = ['item_id', 'unit_id', 'quantity', 'price', 'discount', 'additional', 'sub_value', 'batch_number', 'expiry_date', 'notes'];
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `items[${index}][${field}]`;
                        input.value = item[field] || '';
                        itemsContainer.appendChild(input);
                    });
                });

                console.log('✅ Form filled, submitting...');

                // ✅ Submit the form
                document.getElementById('invoice-form').submit();
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
                // Check if dropdown exists
                const dropdown = document.getElementById('search-results-dropdown');
                // Check if search input exists
                const searchInput = document.getElementById('search-input');
                // Run search
                this.handleSearch(term);
            },

            // Debug function - show dropdown manually
            forceShowDropdown() {
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
                } else {
                    console.error('❌ Dropdown not found!');
                }
            },
            // Move to next/previous field (Tab Order)
            moveToNextField(currentIndex, currentField, isReverse = false) {
                const fieldMap = {
                    'unit': 'unit',
                    'quantity': 'quantity',
                    'batch': 'batch_number',
                    'expiry': 'expiry_date',
                    'price': 'price',
                    'discount': 'discount'
                };

                const currentColumn = fieldMap[currentField] || currentField;
                const editableColumns = this.getEditableColumns();
                const currentPos = editableColumns.indexOf(currentColumn);

                if (currentPos === -1) {
                    this.focusSearchInput();
                    return;
                }

                if (isReverse) {
                    // Shift+Tab: Go backward
                    if (currentPos > 0) {
                        const prevColumn = editableColumns[currentPos - 1];
                        this.focusField(prevColumn, currentIndex);
                    } else {
                        // First field → go to search
                        this.focusSearchInput();
                    }
                } else {
                    // Enter/Tab: Go forward
                    if (currentPos < editableColumns.length - 1) {
                        const nextColumn = editableColumns[currentPos + 1];
                        this.focusField(nextColumn, currentIndex);
                    } else {
                        // ✅ Last field → Skip delete button, go back to search
                        this.focusSearchInput();
                    }
                }
            },

            // Get editable columns (skip item_name, code, sub_value)
            getEditableColumns() {
                const nonEditable = ['item_name', 'code', 'sub_value'];
                return this.visibleColumns.filter(col => !nonEditable.includes(col));
            },

            // Focus a specific field
            focusField(columnName, index) {
                const fieldId = this.getFieldIdFromColumn(columnName, index);
                if (fieldId) {
                    const el = document.getElementById(fieldId);
                    if (el) {
                        el.focus();
                        if (el.tagName === 'INPUT' && el.type !== 'date') {
                            el.select();
                        }
                        return;
                    }
                }
                this.focusSearchInput();
            },

            // Focus search input helper
            focusSearchInput() {
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            },

            // Get field ID from column name
            getFieldIdFromColumn(columnName, index) {
                const columnToFieldMap = {
                    'unit': 'unit-' + index,
                    'quantity': 'quantity-' + index,
                    'batch_number': 'batch-' + index,
                    'expiry_date': 'expiry-' + index,
                    'price': 'price-' + index,
                    'discount': 'discount-' + index
                };

                return columnToFieldMap[columnName] || null;
            },
        };

        // Initialize when DOM is ready AND jQuery + Select2 are loaded
        function initWhenReady() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initWhenReady, 100);
                return;
            }

            if (typeof jQuery.fn.select2 === 'undefined') {
                setTimeout(initWhenReady, 100);
                return;
            }

            InvoiceApp.init();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWhenReady);
        } else {
            initWhenReady();
        }

        // Expose reload function
        window.reloadSearchItems = () => InvoiceApp.loadItems();
    </script>
@endsection
