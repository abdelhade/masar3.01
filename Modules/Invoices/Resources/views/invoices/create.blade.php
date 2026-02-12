@extends('admin.dashboard')

@section('sidebar')
    @if (in_array($type, [10, 12, 14, 16, 22, 26]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($type, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($type, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection

@push('styles')
    <style>
        /* Remove body padding - footer will be in content area */
        body {
            padding-bottom: 0 !important;
        }

        /* Main content wrapper */
        #invoice-app {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 100px);
            padding-bottom: 20px;
        }

        /* Invoice footer - NOT fixed, stays at bottom of content */
        .invoice-footer-container {
            margin-top: auto;
            background: white;
            border-top: 3px solid #dee2e6;
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        /* Invoice table container */
        .invoice-scroll-container {
            flex: 1;
            min-height: 400px;
            max-height: calc(100vh - 500px);
            overflow-y: auto;
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

        /* Footer styling to match image */
        .invoice-footer-container {
            background: linear-gradient(to bottom, #ffffff 0%, #f5f5f5 100%);
        }

        .footer-section {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }

        .footer-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .footer-value {
            font-size: 1rem;
            font-weight: 700;
            color: #212529;
            padding: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
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

        /* Ensure content doesn't overlap with sidebar */
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 768px) {
            .main-content {
                margin-left: 250px; /* Sidebar width */
            }
        }

        /* Hidden class */
        .hidden {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    {{-- Pure HTML - No Alpine --}}
    <div id="invoice-app">
        <form id="invoice-form">
            @csrf

            {{-- Invoice Header --}}
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

            {{-- Invoice Items Table --}}
            @include('invoices::components.invoices.invoice-item-table', [
                'type' => $type,
                'branchId' => $branchId,
            ])
        </form>

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
    </div>
@endsection

@push('scripts')
    {{-- Main Invoice JavaScript --}}
    <script>
        console.log('🚀 Invoice System Loading...');

        // Invoice State (Global)
        window.InvoiceApp = {
            // Config
            type: {{ $type }},
            branchId: {{ $branchId ?? 'null' }},
            vatPercentage: {{ setting('vat_percentage', 15) }},
            withholdingTaxPercentage: {{ setting('withholding_tax_percentage', 0) }},

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
                this.initializeSelect2();
                this.setDefaultValues();
                this.loadItems();
                this.attachEventListeners();
                this.renderItems();
                console.log('✅ Invoice App Ready');
            },

            // Initialize Select2 for searchable dropdowns
            initializeSelect2() {
                // Initialize Select2 for acc1 (Customer/Supplier) with search
                $('#acc1-id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'ابحث عن عميل/مورد...',
                    allowClear: true,
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

                console.log('✅ Select2 initialized for acc1');
            },

            // Set default values from settings
            setDefaultValues() {
                // Set default employee
                const defaultEmployeeId = '{{ $defaultEmployeeId ?? "" }}';
                if (defaultEmployeeId) {
                    document.getElementById('emp-id').value = defaultEmployeeId;
                    console.log('✅ Default employee set:', defaultEmployeeId);
                }

                // Set default delivery
                const defaultDeliveryId = '{{ $defaultDeliveryId ?? "" }}';
                if (defaultDeliveryId) {
                    document.getElementById('delivery-id').value = defaultDeliveryId;
                    console.log('✅ Default delivery set:', defaultDeliveryId);
                }

                // Set default store
                const defaultStoreId = '{{ $defaultStoreId ?? "" }}';
                if (defaultStoreId) {
                    document.getElementById('acc2-id').value = defaultStoreId;
                    console.log('✅ Default store set:', defaultStoreId);
                }

                // Set default customer/supplier based on invoice type
                const invoiceType = {{ $type }};
                const defaultCustomerId = '{{ $defaultCustomerId ?? "" }}';
                const defaultSupplierId = '{{ $defaultSupplierId ?? "" }}';

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
                const url = `/api/items/lite?branch_id=${this.branchId}&type=${this.type}&_t=${Date.now()}`;

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('📦 Received', data.length, 'items');
                    this.allItems = data;

                    if (typeof Fuse !== 'undefined') {
                        this.fuse = new Fuse(this.allItems, {
                            keys: ['name', 'code', 'barcode'],
                            threshold: 0.3,
                            ignoreLocation: true
                        });
                        console.log('✅ Fuse initialized');
                        this.updateStatus('تم تحميل ' + data.length + ' صنف - البحث جاهز ✓', 'success');
                    }
                })
                .catch(error => {
                    console.error('❌ Error loading items:', error);
                    this.updateStatus('خطأ في تحميل الأصناف', 'danger');
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

                console.log('✅ Event listeners attached');
            },

            // Handle search
            handleSearch(term) {
                if (!term || term.length < 1) {
                    this.hideSearchResults();
                    return;
                }

                if (!this.fuse) {
                    console.error('❌ Fuse not ready');
                    return;
                }

                const results = this.fuse.search(term);
                this.searchResults = results.map(r => r.item).slice(0, 50);
                this.selectedIndex = this.searchResults.length > 0 ? 0 : -1;

                this.renderSearchResults();
                this.showSearchResults();
            },

            // Handle search keydown
            handleSearchKeydown(e) {
                const dropdown = document.getElementById('search-results-dropdown');
                if (!dropdown || dropdown.classList.contains('hidden')) return;

                switch(e.key) {
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
                const dropdown = document.getElementById('search-results-dropdown');
                if (!dropdown) return;

                dropdown.innerHTML = '';

                if (this.searchResults.length === 0) {
                    const searchInput = document.getElementById('search-input');
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action text-primary';
                    btn.innerHTML = '<i class="fas fa-plus-circle me-2"></i><strong>إنشاء صنف جديد: ' + (searchInput?.value || '') + '</strong>';
                    btn.onclick = () => this.createNewItem(searchInput?.value || '');
                    dropdown.appendChild(btn);
                } else {
                    this.searchResults.forEach((item, index) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between';
                        if (index === this.selectedIndex) btn.classList.add('active');
                        btn.innerHTML = `
                            <div>
                                <strong>${item.name}</strong>
                                <small class="text-muted ms-2">كود: ${item.code}</small>
                            </div>
                            <span class="badge bg-primary">${item.price || 0} ج.م</span>
                        `;
                        btn.onclick = () => this.addItem(item);
                        dropdown.appendChild(btn);
                    });
                }
            },

            // Show/hide search results
            showSearchResults() {
                const dropdown = document.getElementById('search-results-dropdown');
                if (dropdown) {
                    dropdown.classList.remove('hidden');
                    dropdown.style.display = 'block';
                }
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
                console.log('➕ Adding item:', item.name);

                const newItem = {
                    id: Date.now(), // Temporary ID
                    item_id: item.id,
                    name: item.name,
                    code: item.code,
                    unit_id: item.default_unit_id || item.unit_id || 1,
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

                        // Re-init Fuse
                        if (typeof Fuse !== 'undefined') {
                            this.fuse = new Fuse(this.allItems, {
                                keys: ['name', 'code', 'barcode'],
                                threshold: 0.3,
                                ignoreLocation: true
                            });
                        }

                        this.updateStatus('تم تحميل ' + this.allItems.length + ' صنف - البحث جاهز ✓', 'success');
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

                if (this.invoiceItems.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="20" class="p-3 text-center">
                                <div class="alert alert-info mb-0">
                                    لم يتم إضافة أصناف. استخدم البحث أعلاه لإضافة أصناف.
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = this.invoiceItems.map((item, index) => this.renderItemRow(item, index)).join('');

                // Attach event listeners to inputs
                this.attachItemEventListeners();
            },

            // Render single item row
            renderItemRow(item, index) {
                const showBatchExpiry = [10, 11, 12, 13, 19, 20].includes(this.type);

                return `
                    <tr data-index="${index}">
                        <td style="width: 18%;">
                            <div class="static-text" style="font-weight: 900; font-size: 1.2rem; color: #000;">
                                ${item.name}
                            </div>
                        </td>
                        <td style="width: 10%;">
                            <div class="static-text">${item.code}</div>
                        </td>
                        <td style="width: 10%;">
                            <select id="unit-${index}" class="form-control" data-index="${index}" data-field="unit">
                                ${(item.available_units || []).map(unit => `
                                    <option value="${unit.id}" data-u-val="${unit.u_val}" ${unit.id == item.unit_id ? 'selected' : ''}>
                                        ${unit.name}
                                    </option>
                                `).join('')}
                            </select>
                        </td>
                        <td style="width: 10%;">
                            <input type="number" id="quantity-${index}" class="form-control text-center"
                                   value="${item.quantity}" step="0.001" min="0"
                                   data-index="${index}" data-field="quantity">
                        </td>
                        ${showBatchExpiry ? `
                            <td style="width: 12%;">
                                <input type="text" id="batch-${index}" class="form-control text-center"
                                       value="${item.batch_number || ''}"
                                       data-index="${index}" data-field="batch">
                            </td>
                            <td style="width: 12%;">
                                <input type="date" id="expiry-${index}" class="form-control text-center"
                                       value="${item.expiry_date || ''}"
                                       data-index="${index}" data-field="expiry">
                            </td>
                        ` : ''}
                        <td style="width: 15%;">
                            <input type="number" id="price-${index}" class="form-control text-center"
                                   value="${item.price}" step="0.01"
                                   data-index="${index}" data-field="price">
                        </td>
                        <td style="width: 15%;">
                            <input type="number" id="discount-${index}" class="form-control text-center"
                                   value="${item.discount}" step="0.01"
                                   data-index="${index}" data-field="discount">
                        </td>
                        <td style="width: 15%;">
                            <input type="number" id="sub-value-${index}" class="form-control text-center"
                                   value="${item.sub_value}" readonly>
                        </td>
                        <td class="action-cell" style="width: 50px;">
                            <button type="button" class="btn btn-link text-danger p-0" onclick="InvoiceApp.removeItem(${index})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            },

            // Attach event listeners to item inputs
            attachItemEventListeners() {
                // Quantity, price, discount inputs
                document.querySelectorAll('[data-field="quantity"], [data-field="price"], [data-field="discount"]').forEach(input => {
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
                        this.invoiceItems[index].price = (this.invoiceItems[index].item_price || 0) * uVal;

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
                this.withholdingTaxValue = parseFloat(((afterAdditional * this.withholdingTaxPercentage) / 100).toFixed(2));

                // Total
                this.totalAfterAdditional = parseFloat((afterAdditional + this.vatValue - this.withholdingTaxValue).toFixed(2));

                // Remaining
                this.remaining = parseFloat((this.totalAfterAdditional - this.receivedFromClient).toFixed(2));

                this.updateTotalsDisplay();
            },

            // Update totals display
            updateTotalsDisplay() {
                const updates = {
                    'subtotal-display': this.subtotal,
                    'discount-value-display': this.discountValue,
                    'additional-value-display': this.additionalValue,
                    'vat-value-display': this.vatValue,
                    'withholding-tax-value-display': this.withholdingTaxValue,
                    'total-display': this.totalAfterAdditional,
                    'remaining-display': this.remaining
                };

                Object.entries(updates).forEach(([id, value]) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value.toFixed(2);
                });
            },

            // Remove item
            removeItem(index) {
                if (confirm('هل تريد حذف هذا الصنف؟')) {
                    this.invoiceItems.splice(index, 1);
                    this.renderItems();
                    this.calculateTotals();
                }
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
                    balanceAfterDisplay.className = this.calculatedBalanceAfter < 0 ? 'badge bg-danger' : 'badge bg-success';
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
            }
        };

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => InvoiceApp.init());
        } else {
            InvoiceApp.init();
        }

        // Expose reload function
        window.reloadSearchItems = () => InvoiceApp.loadItems();
    </script>
@endpush
