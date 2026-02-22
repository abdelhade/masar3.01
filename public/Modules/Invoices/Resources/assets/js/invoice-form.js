/**
 * Invoice Form Alpine.js Component
 * Handles all client-side calculations and UI interactions
 * Converted from Livewire to pure Alpine.js + API calls
 */

// ========================================
// Global Utility Functions
// ========================================

/**
 * Format number without trailing zeros
 */
window.formatNumber = function(num) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    const numStr = parseFloat(num).toString();
    if (numStr.indexOf('.') === -1) {
        return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    const parts = numStr.split('.');
    parts[1] = parts[1].replace(/0+$/, '');
    if (parts[1] === '') {
        return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + parts[1];
};

/**
 * Format number with fixed decimals
 */
window.formatNumberFixed = function(num, decimals = 2) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    const formatted = parseFloat(num).toFixed(decimals);
    return formatted.replace(/\.?0+$/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

// Initialize immediately if Alpine is already loaded, otherwise wait for alpine:init
if (window.Alpine) {
    initInvoiceForm();
} else {
    document.addEventListener('alpine:init', initInvoiceForm);
}

function initInvoiceForm() {
    // ========================================
    // Alpine Stores
    // ========================================
    
    if (!Alpine.store('invoiceNavigation')) {
        Alpine.store('invoiceNavigation', {
            moveToNextField: null,
            calculateRowTotal: null,
            editableFieldsOrder: []
        });
    }
    
    if (!Alpine.store('invoiceValues')) {
        Alpine.store('invoiceValues', {
            subtotal: 0,
            discountValue: 0,
            discountPercentage: 0,
            additionalValue: 0,
            additionalPercentage: 0,
            totalAfterAdditional: 0,
            remaining: 0,
            receivedFromClient: 0
        });
    }
    
    // ========================================
    // Main Invoice Form Component
    // ========================================
    
    Alpine.data('invoiceForm', (config = {}) => ({
        // ==================== STATE ====================
        
        // Invoice data
        invoice: {
            type: config.type || 10,
            branch_id: config.branchId || null,
            acc1_id: null,
            acc2_id: null,
            pro_date: new Date().toISOString().split('T')[0],
            notes: '',
            currency_id: 1,
            exchange_rate: 1,
        },
        
        // Invoice items
        invoiceItems: [],
        
        // Calculations
        calculations: {
            subtotal: 0,
            discount_percentage: 0,
            discount_value: 0,
            additional_percentage: 0,
            additional_value: 0,
            vat_percentage: 15,
            vat_value: 0,
            withholding_tax_percentage: 0,
            withholding_tax_value: 0,
            total_after_additional: 0,
            received_from_client: 0,
            remaining: 0,
        },
        
        // Data loaded from server
        data: {
            accounts: {
                customers: [],
                suppliers: [],
                cash_accounts: [],
                cost_centers: [],
            },
            templates: [],
            settings: {},
            branches: [],
            price_types: [],
            units: [],
            currencies: [],
        },
        
        // UI state
        ui: {
            loading: false,
            saving: false,
            searchTerm: '',
            searchResults: [],
            selectedSearchIndex: -1,
            selectedAccount: null,
            showBalance: false,
            accountBalance: 0,
            balanceAfterInvoice: 0,
            selectedTemplate: null,
            focusedItemIndex: null,
        },
        
        // ==================== INITIALIZATION ====================
        
        async init() {
            console.log('Invoice Form initialized', config);
            
            // Load initial data
            await this.loadInitialData();
            
            // Setup keyboard shortcuts
            this.setupKeyboardShortcuts();
            
            // Load draft if exists
            this.loadDraft();
            
            // Start auto-save
            this.startAutoSave();
            
            // If editing, load invoice data
            if (config.invoiceId) {
                await this.loadInvoiceForEdit(config.invoiceId);
            } else {
                // Add first empty row
                this.addRow();
            }
        },
        
        async loadInitialData() {
            this.ui.loading = true;
            
            try {
                const response = await fetch(
                    `/api/invoices/initial-data?type=${this.invoice.type}&branch_id=${this.invoice.branch_id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );
                
                if (!response.ok) {
                    throw new Error('Failed to load initial data');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    this.data = result.data;
                    this.calculations.vat_percentage = this.data.settings.vat_percentage || 15;
                    this.ui.showBalance = this.data.settings.show_balance || false;
                    this.invoice.currency_id = this.data.settings.default_currency_id || 1;
                }
                
            } catch (error) {
                console.error('Error loading initial data:', error);
                this.showError('فشل تحميل البيانات الأولية');
            } finally {
                this.ui.loading = false;
            }
        },
        
        async loadInvoiceForEdit(invoiceId) {
            this.ui.loading = true;
            
            try {
                const response = await fetch(`/api/invoices/${invoiceId}/edit-data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load invoice');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Load invoice data
                    Object.assign(this.invoice, result.data.invoice);
                    
                    // Load items with operation_item_id for sync
                    this.invoiceItems = result.data.items.map(item => ({
                        ...item,
                        // Ensure operation_item_id is preserved for sync
                        operation_item_id: item.operation_item_id,
                        // Ensure all numeric fields are numbers
                        quantity: parseFloat(item.quantity) || 0,
                        price: parseFloat(item.price) || 0,
                        discount: parseFloat(item.discount) || 0,
                        sub_value: parseFloat(item.sub_value) || 0,
                    }));
                    
                    // Load calculations from invoice
                    this.calculations.discount_percentage = this.invoice.discount_percentage || 0;
                    this.calculations.discount_value = this.invoice.discount_value || 0;
                    this.calculations.additional_percentage = this.invoice.additional_percentage || 0;
                    this.calculations.additional_value = this.invoice.additional_value || 0;
                    this.calculations.vat_percentage = this.invoice.vat_percentage || 0;
                    this.calculations.vat_value = this.invoice.vat_value || 0;
                    this.calculations.withholding_tax_percentage = this.invoice.withholding_tax_percentage || 0;
                    this.calculations.withholding_tax_value = this.invoice.withholding_tax_value || 0;
                    this.calculations.received_from_client = this.invoice.received_from_client || 0;
                    
                    // Recalculate totals
                    this.calculateTotals();
                    
                    // Load account data
                    if (this.invoice.acc1_id) {
                        this.onAccountChange(this.invoice.acc1_id);
                    }
                }
                
            } catch (error) {
                console.error('Error loading invoice:', error);
                this.showError('فشل تحميل الفاتورة');
            } finally {
                this.ui.loading = false;
            }
        },
        
        // ==================== CLIENT-SIDE CALCULATIONS ====================
        
        calculateItemTotal(index) {
            const item = this.invoiceItems[index];
            if (!item) return;
            
            const quantity = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.price) || 0;
            const discount = parseFloat(item.discount) || 0;
            const additional = parseFloat(item.additional) || 0;
            
            // Calculate total
            let total = quantity * price;
            total = total - discount + additional;
            
            item.sub_value = this.round(total);
            
            // Recalculate invoice totals
            this.calculateTotals();
        },
        
        calculateTotals() {
            // 1. Calculate subtotal
            this.calculations.subtotal = this.invoiceItems.reduce(
                (sum, item) => sum + (parseFloat(item.sub_value) || 0),
                0
            );
            
            // 2. Calculate discount
            if (this.calculations.discount_percentage > 0) {
                this.calculations.discount_value = 
                    (this.calculations.subtotal * this.calculations.discount_percentage) / 100;
            }
            
            // 3. Calculate additional
            if (this.calculations.additional_percentage > 0) {
                this.calculations.additional_value = 
                    (this.calculations.subtotal * this.calculations.additional_percentage) / 100;
            }
            
            // 4. After discount and additional
            const afterDiscountAndAdditional = 
                this.calculations.subtotal - 
                this.calculations.discount_value + 
                this.calculations.additional_value;
            
            // 5. Calculate VAT
            if (this.calculations.vat_percentage > 0) {
                this.calculations.vat_value = 
                    (afterDiscountAndAdditional * this.calculations.vat_percentage) / 100;
            }
            
            // 6. Calculate withholding tax
            if (this.calculations.withholding_tax_percentage > 0) {
                this.calculations.withholding_tax_value = 
                    (afterDiscountAndAdditional * this.calculations.withholding_tax_percentage) / 100;
            }
            
            // 7. Calculate total
            this.calculations.total_after_additional = 
                afterDiscountAndAdditional + 
                this.calculations.vat_value - 
                this.calculations.withholding_tax_value;
            
            // 8. Calculate remaining
            this.calculations.remaining = 
                this.calculations.total_after_additional - 
                this.calculations.received_from_client;
            
            // 9. Calculate balance after invoice
            this.calculateBalanceAfterInvoice();
            
            // Round all values
            this.roundCalculations();
        },
        
        calculateBalanceAfterInvoice() {
            if (!this.ui.showBalance || !this.ui.selectedAccount) return;
            
            const currentBalance = parseFloat(this.ui.selectedAccount.balance) || 0;
            const invoiceTotal = this.calculations.total_after_additional;
            
            // Based on invoice type
            if ([10, 12, 14, 16].includes(this.invoice.type)) {
                // Sales invoices - increase debit balance
                this.ui.balanceAfterInvoice = currentBalance + invoiceTotal;
            } else {
                // Purchase invoices - decrease debit balance
                this.ui.balanceAfterInvoice = currentBalance - invoiceTotal;
            }
        },
        
        roundCalculations() {
            const decimalPlaces = this.data.settings.decimal_places || 2;
            
            Object.keys(this.calculations).forEach(key => {
                if (typeof this.calculations[key] === 'number') {
                    this.calculations[key] = this.round(this.calculations[key], decimalPlaces);
                }
            });
            
            this.ui.balanceAfterInvoice = this.round(this.ui.balanceAfterInvoice, decimalPlaces);
        },
        
        round(value, decimals = 2) {
            return Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals);
        },
        
        // When discount percentage changes
        onDiscountPercentageChange() {
            this.calculations.discount_value = 
                (this.calculations.subtotal * this.calculations.discount_percentage) / 100;
            this.calculateTotals();
        },
        
        // When discount value changes
        onDiscountValueChange() {
            if (this.calculations.subtotal > 0) {
                this.calculations.discount_percentage = 
                    (this.calculations.discount_value / this.calculations.subtotal) * 100;
            }
            this.calculateTotals();
        },
        
        // When additional percentage changes
        onAdditionalPercentageChange() {
            this.calculations.additional_value = 
                (this.calculations.subtotal * this.calculations.additional_percentage) / 100;
            this.calculateTotals();
        },
        
        // When additional value changes
        onAdditionalValueChange() {
            if (this.calculations.subtotal > 0) {
                this.calculations.additional_percentage = 
                    (this.calculations.additional_value / this.calculations.subtotal) * 100;
            }
            this.calculateTotals();
        },
        
        // ==================== CLIENT-SIDE VALIDATION ====================
        
        validateInvoice() {
            const errors = [];
            
            // Validate basic fields
            if (!this.invoice.acc1_id) {
                errors.push('يجب اختيار الحساب الأول');
            }
            
            if (!this.invoice.acc2_id) {
                errors.push('يجب اختيار الحساب الثاني');
            }
            
            if (!this.invoice.pro_date) {
                errors.push('يجب اختيار التاريخ');
            }
            
            if (this.invoiceItems.length === 0) {
                errors.push('يجب إضافة صنف واحد على الأقل');
            }
            
            // Validate items
            this.invoiceItems.forEach((item, index) => {
                if (!item.item_id) {
                    errors.push(`الصنف رقم ${index + 1}: يجب اختيار صنف`);
                }
                if (!item.unit_id) {
                    errors.push(`الصنف رقم ${index + 1}: يجب اختيار وحدة`);
                }
                if (!item.quantity || item.quantity <= 0) {
                    errors.push(`الصنف رقم ${index + 1}: الكمية غير صحيحة`);
                }
                if (item.price === undefined || item.price < 0) {
                    errors.push(`الصنف رقم ${index + 1}: السعر غير صحيح`);
                }
            });
            
            // Validate credit limit
            if (this.ui.selectedAccount && this.ui.selectedAccount.credit_limit) {
                const creditLimit = parseFloat(this.ui.selectedAccount.credit_limit);
                if (this.ui.balanceAfterInvoice > creditLimit) {
                    errors.push(`تجاوز حد الائتمان: ${creditLimit.toFixed(2)}`);
                }
            }
            
            // Validate expired items
            if (this.data.settings.prevent_expired_items) {
                this.invoiceItems.forEach((item, index) => {
                    if (item.expiry_date && new Date(item.expiry_date) < new Date()) {
                        errors.push(`الصنف رقم ${index + 1}: منتهي الصلاحية`);
                    }
                });
            }
            
            return errors;
        },
        
        // ==================== ITEMS MANAGEMENT ====================
        
        addRow() {
            this.invoiceItems.push({
                item_id: null,
                item_name: '',
                unit_id: null,
                quantity: 1,
                price: 0,
                discount: 0,
                additional: 0,
                sub_value: 0,
                batch_number: '',
                expiry_date: '',
                notes: '',
                available_units: [],
                stock_quantity: 0,
            });
            
            // Focus on new row
            this.$nextTick(() => {
                const newIndex = this.invoiceItems.length - 1;
                this.focusItemSearch(newIndex);
            });
        },
        
        removeRow(index) {
            if (this.invoiceItems.length === 1) {
                this.showWarning('يجب أن تحتوي الفاتورة على صنف واحد على الأقل');
                return;
            }
            
            this.invoiceItems.splice(index, 1);
            this.calculateTotals();
        },
        
        duplicateRow(index) {
            const item = { ...this.invoiceItems[index] };
            this.invoiceItems.splice(index + 1, 0, item);
            this.calculateTotals();
        },
        
        // ==================== ITEM SEARCH ====================
        
        searchItems: Alpine.debounce(async function() {
            if (this.ui.searchTerm.length < 2) {
                this.ui.searchResults = [];
                this.ui.selectedSearchIndex = -1;
                return;
            }
            
            try {
                const response = await fetch(
                    `/api/invoices/items/search?term=${encodeURIComponent(this.ui.searchTerm)}&branch_id=${this.invoice.branch_id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );
                
                if (!response.ok) {
                    throw new Error('Search failed');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    this.ui.searchResults = result.items;
                    this.ui.selectedSearchIndex = this.ui.searchResults.length > 0 ? 0 : -1;
                }
                
            } catch (error) {
                console.error('Search error:', error);
                this.showError('فشل البحث عن الأصناف');
            }
        }, 300),
        
        handleSearchKeydown(event) {
            const hasResults = this.ui.searchResults.length > 0;
            
            if (event.key === 'Enter') {
                event.preventDefault();
                if (hasResults && this.ui.selectedSearchIndex >= 0) {
                    const selectedItem = this.ui.searchResults[this.ui.selectedSearchIndex];
                    this.addItemFromSearch(selectedItem.id);
                }
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (this.ui.selectedSearchIndex < this.ui.searchResults.length - 1) {
                    this.ui.selectedSearchIndex++;
                }
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (this.ui.selectedSearchIndex > 0) {
                    this.ui.selectedSearchIndex--;
                }
            } else if (event.key === 'Escape') {
                this.ui.searchResults = [];
                this.ui.selectedSearchIndex = -1;
            }
        },
        
        async addItemFromSearch(itemId) {
            try {
                const response = await fetch(
                    `/api/invoices/items/${itemId}/details?customer_id=${this.invoice.acc1_id}&branch_id=${this.invoice.branch_id}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );
                
                if (!response.ok) {
                    throw new Error('Failed to get item details');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    const itemData = result.data;
                    const defaultUnit = itemData.units[0];
                    
                    this.invoiceItems.push({
                        item_id: itemData.item.id,
                        item_name: itemData.item.name,
                        item_code: itemData.item.code,
                        unit_id: defaultUnit.id,
                        quantity: 1,
                        price: itemData.last_price || defaultUnit.price1,
                        discount: 0,
                        additional: 0,
                        sub_value: itemData.last_price || defaultUnit.price1,
                        batch_number: '',
                        expiry_date: '',
                        notes: '',
                        available_units: itemData.units,
                        stock_quantity: itemData.stock_quantity,
                    });
                    
                    this.calculateTotals();
                    this.ui.searchTerm = '';
                    this.ui.searchResults = [];
                    this.ui.selectedSearchIndex = -1;
                }
                
            } catch (error) {
                console.error('Error adding item:', error);
                this.showError('فشل إضافة الصنف');
            }
        },
        
        // ==================== ACCOUNT MANAGEMENT ====================
        
        onAccountChange(accountId) {
            const allAccounts = [
                ...this.data.accounts.customers,
                ...this.data.accounts.suppliers,
                ...this.data.accounts.cash_accounts,
                ...this.data.accounts.cost_centers,
            ];
            
            const account = allAccounts.find(a => a.id == accountId);
            
            if (account) {
                this.ui.selectedAccount = account;
                this.ui.accountBalance = parseFloat(account.balance) || 0;
                this.invoice.currency_id = account.currency_id || 1;
                this.calculateBalanceAfterInvoice();
            }
        },
        
        // ==================== SAVE INVOICE ====================
        
        async saveInvoice(andPrint = false) {
            // Validate
            const errors = this.validateInvoice();
            if (errors.length > 0) {
                this.showError(errors.join('\n'));
                return;
            }
            
            this.ui.saving = true;
            
            try {
                const payload = {
                    ...this.invoice,
                    ...this.calculations,
                    items: this.invoiceItems.map(item => ({
                        operation_item_id: item.operation_item_id || null, // ✅ Critical for sync
                        item_id: item.item_id,
                        unit_id: item.unit_id,
                        quantity: item.quantity,
                        price: item.price,
                        discount: item.discount || 0,
                        discount_percentage: item.discount_percentage || 0,
                        discount_value: item.discount || 0, // Use discount as discount_value
                        additional: item.additional || 0,
                        sub_value: item.sub_value,
                        batch_number: item.batch_number || null,
                        expiry_date: item.expiry_date || null,
                        notes: item.notes || null,
                    })),
                };
                
                const url = config.invoiceId 
                    ? `/api/invoices/${config.invoiceId}` 
                    : '/api/invoices';
                
                const method = config.invoiceId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showSuccess(result.message);
                    
                    // Clear draft
                    this.clearDraft();
                    
                    // Redirect or print
                    if (andPrint) {
                        window.open(`/invoice/print/${result.invoice.id}`, '_blank');
                    }
                    
                    // Redirect to invoice list or details
                    setTimeout(() => {
                        window.location.href = `/invoices?type=${this.invoice.type}`;
                    }, 1000);
                    
                } else {
                    this.showError(result.message || 'فشل حفظ الفاتورة');
                    
                    if (result.errors && result.errors.length > 0) {
                        this.showError(result.errors.join('\n'));
                    }
                }
                
            } catch (error) {
                console.error('Save error:', error);
                this.showError('حدث خطأ أثناء الحفظ');
            } finally {
                this.ui.saving = false;
            }
        },
        
        async saveAndPrint() {
            await this.saveInvoice(true);
        },
        
        // ==================== DRAFT MANAGEMENT ====================
        
        saveDraft() {
            const draft = {
                invoice: this.invoice,
                items: this.invoiceItems,
                calculations: this.calculations,
                timestamp: new Date().toISOString(),
            };
            
            localStorage.setItem(`invoice_draft_${this.invoice.type}`, JSON.stringify(draft));
        },
        
        loadDraft() {
            const draftKey = `invoice_draft_${this.invoice.type}`;
            const draftJson = localStorage.getItem(draftKey);
            
            if (draftJson) {
                try {
                    const draft = JSON.parse(draftJson);
                    
                    // Ask user if they want to load draft
                    if (confirm('تم العثور على مسودة. هل تريد تحميلها؟')) {
                        this.invoice = draft.invoice;
                        this.invoiceItems = draft.items;
                        this.calculations = draft.calculations;
                        this.calculateTotals();
                    } else {
                        this.clearDraft();
                    }
                } catch (error) {
                    console.error('Error loading draft:', error);
                    this.clearDraft();
                }
            }
        },
        
        clearDraft() {
            localStorage.removeItem(`invoice_draft_${this.invoice.type}`);
        },
        
        startAutoSave() {
            // Auto-save every 30 seconds
            setInterval(() => {
                if (this.invoiceItems.length > 0 && !config.invoiceId) {
                    this.saveDraft();
                }
            }, 30000);
        },
        
        // ==================== KEYBOARD SHORTCUTS ====================
        
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Ctrl+S to save
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    this.saveInvoice();
                }
                
                // Ctrl+N to add new row
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    this.addRow();
                }
                
                // Ctrl+P to save and print
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    this.saveAndPrint();
                }
            });
        },
        
        focusItemSearch(index) {
            const searchInput = document.querySelector(`#item-search-${index}`);
            if (searchInput) {
                searchInput.focus();
            }
        },
        
        // ==================== UTILITIES ====================
        
        showError(message) {
            // Using SweetAlert2 or similar
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ!',
                    text: message,
                    confirmButtonText: 'حسناً',
                });
            } else {
                alert(message);
            }
        },
        
        showSuccess(message) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'نجح!',
                    text: message,
                    confirmButtonText: 'حسناً',
                    timer: 2000,
                });
            } else {
                alert(message);
            }
        },
        
        showWarning(message) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تحذير!',
                    text: message,
                    confirmButtonText: 'حسناً',
                });
            } else {
                alert(message);
            }
        },
        
        formatNumber(value, decimals = 2) {
            return parseFloat(value || 0).toFixed(decimals);
        },
        
        formatCurrency(value) {
            const decimals = this.data.settings.decimal_places || 2;
            return new Intl.NumberFormat('ar-SA', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(value || 0);
        },
    }));
}
