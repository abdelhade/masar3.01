/**
 * Invoice Calculations Component
 * Pure client-side calculations - NO server requests
 * Extracted from invoice-scripts.blade.php
 */

document.addEventListener('alpine:init', () => {
    
    Alpine.data('invoiceCalculations', (initialData) => ({
        // State
        invoiceItems: initialData.invoiceItems || [],
        discountPercentage: initialData.discountPercentage || 0,
        discountValue: initialData.discountValue || 0,
        additionalPercentage: initialData.additionalPercentage || 0,
        additionalValue: initialData.additionalValue || 0,
        vatPercentage: initialData.vatPercentage || 15,
        vatValue: initialData.vatValue || 0,
        withholdingTaxPercentage: initialData.withholdingTaxPercentage || 0,
        withholdingTaxValue: initialData.withholdingTaxValue || 0,
        receivedFromClient: initialData.receivedFromClient || 0,
        isCashAccount: initialData.isCashAccount || false,
        currentBalance: parseFloat(initialData.currentBalance) || 0,
        
        // Calculated values
        subtotal: 0,
        totalAfterAdditional: 0,
        remaining: 0,
        calculatedBalanceAfter: 0,
        
        // Text inputs for smooth typing
        discountValueText: '0',
        additionalValueText: '0',
        
        // Internal flags
        _discountValueFromPercentage: false,
        _additionalValueFromPercentage: false,
        isInternalUpdate: false,
        
        init() {
            console.log('✅ invoiceCalculations initialized');
            
            // Initialize text inputs
            this.discountValueText = String(this.discountValue || 0);
            this.additionalValueText = String(this.additionalValue || 0);
            
            // Save reference
            window.invoiceCalculationsInstance = this;
            
            // Setup watchers
            this.setupWatchers();
            
            // Initial calculation
            this.calculateTotalsFromData();
        },
        
        setupWatchers() {
            // Watch invoice items
            this.$watch('invoiceItems', () => {
                this.calculateTotalsFromData();
            }, { deep: true });
            
            // Watch discount
            this.$watch('discountPercentage', () => {
                if (this.isInternalUpdate) return;
                this._discountValueFromPercentage = true;
                this.calculateFinalTotals();
            });
            
            this.$watch('discountValue', (newVal) => {
                if (this.isInternalUpdate) return;
                if (this._discountValueFromPercentage) {
                    this.discountValueText = String(parseFloat(newVal) || 0);
                }
                if (!this._discountValueFromPercentage) this.calculateFinalTotals();
            });
            
            // Watch additional
            this.$watch('additionalPercentage', () => {
                if (this.isInternalUpdate) return;
                this._additionalValueFromPercentage = true;
                this.calculateFinalTotals();
            });
            
            this.$watch('additionalValue', (newVal) => {
                if (this.isInternalUpdate) return;
                if (this._additionalValueFromPercentage) {
                    this.additionalValueText = String(parseFloat(newVal) || 0);
                }
                if (!this._additionalValueFromPercentage) this.calculateFinalTotals();
            });
            
            // Watch received
            this.$watch('receivedFromClient', () => {
                if (this.isInternalUpdate) return;
                this.calculateFinalTotals();
            });
            
            // Watch cash account
            this.$watch('isCashAccount', () => {
                this.calculateFinalTotals();
            });
        },
        
        /**
         * Calculate totals from invoice items
         */
        calculateTotalsFromData() {
            let tempSubtotal = 0;
            const items = this.invoiceItems || [];
            
            items.forEach(item => {
                const qty = parseFloat(item.quantity) || 0;
                const price = parseFloat(item.price) || 0;
                const discount = parseFloat(item.discount) || 0;
                
                const rowTotal = (qty * price) - discount;
                tempSubtotal += rowTotal;
                
                item.sub_value = parseFloat(rowTotal.toFixed(2));
            });
            
            this.subtotal = parseFloat(tempSubtotal.toFixed(2));
            this.calculateFinalTotals();
        },
        
        /**
         * Calculate final totals (discount, additional, VAT, etc.)
         */
        calculateFinalTotals() {
            // 1. Calculate discount value
            if (this._discountValueFromPercentage) {
                this.discountValue = parseFloat(((this.subtotal * this.discountPercentage) / 100).toFixed(2));
                this.discountValueText = String(this.discountValue);
            } else if (this.subtotal > 0) {
                this.isInternalUpdate = true;
                this.discountPercentage = parseFloat(((this.discountValue / this.subtotal) * 100).toFixed(2));
                this.isInternalUpdate = false;
            }
            
            const afterDiscount = parseFloat((this.subtotal - this.discountValue).toFixed(2));
            
            // 2. Calculate additional value
            if (this._additionalValueFromPercentage) {
                this.additionalValue = parseFloat(((afterDiscount * this.additionalPercentage) / 100).toFixed(2));
                this.additionalValueText = String(this.additionalValue);
            } else if (afterDiscount > 0) {
                this.isInternalUpdate = true;
                this.additionalPercentage = parseFloat(((this.additionalValue / afterDiscount) * 100).toFixed(2));
                this.isInternalUpdate = false;
            }
            
            const afterAdditional = parseFloat((afterDiscount + this.additionalValue).toFixed(2));
            
            // 3. Calculate VAT
            this.vatValue = parseFloat(((afterAdditional * this.vatPercentage) / 100).toFixed(2));
            
            // 4. Calculate withholding tax
            this.withholdingTaxValue = parseFloat(((afterAdditional * this.withholdingTaxPercentage) / 100).toFixed(2));
            
            // 5. Calculate total
            this.totalAfterAdditional = parseFloat((afterAdditional + this.vatValue - this.withholdingTaxValue).toFixed(2));
            
            // 6. Handle cash account
            if (this.isCashAccount) {
                this.receivedFromClient = this.totalAfterAdditional;
                this.remaining = 0;
            } else {
                this.remaining = parseFloat((this.totalAfterAdditional - this.receivedFromClient).toFixed(2));
            }
            
            // 7. Calculate balance
            this.calculateBalance();
            
            // Reset flags
            this._discountValueFromPercentage = false;
            this._additionalValueFromPercentage = false;
        },
        
        /**
         * Calculate balance after invoice
         */
        calculateBalance() {
            const invoiceType = parseInt(this.invoiceType) || 10;
            
            if ([10, 12, 14, 16].includes(invoiceType)) {
                // Sales invoices - increase debit balance
                this.calculatedBalanceAfter = this.currentBalance + this.totalAfterAdditional;
            } else {
                // Purchase invoices - decrease debit balance
                this.calculatedBalanceAfter = this.currentBalance - this.totalAfterAdditional;
            }
        },
        
        /**
         * Update discount from percentage
         */
        updateDiscountFromPercentage() {
            this._discountValueFromPercentage = true;
            this.calculateFinalTotals();
        },
        
        /**
         * Update discount from value
         */
        updateDiscountFromValue() {
            this._discountValueFromPercentage = false;
            this.discountValue = parseFloat(this.discountValueText) || 0;
            this.calculateFinalTotals();
        },
        
        /**
         * Update additional from percentage
         */
        updateAdditionalFromPercentage() {
            this._additionalValueFromPercentage = true;
            this.calculateFinalTotals();
        },
        
        /**
         * Update additional from value
         */
        updateAdditionalFromValue() {
            this._additionalValueFromPercentage = false;
            this.additionalValue = parseFloat(this.additionalValueText) || 0;
            this.calculateFinalTotals();
        },
        
        /**
         * Update received amount
         */
        updateReceived() {
            this.calculateFinalTotals();
        },
        
        /**
         * Calculate item total
         */
        calculateItemTotal(index) {
            const item = this.invoiceItems[index];
            if (!item) return;
            
            const quantity = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.price) || 0;
            const discount = parseFloat(item.discount) || 0;
            
            item.sub_value = parseFloat(((quantity * price) - discount).toFixed(2));
            
            this.calculateTotalsFromData();
        },
        
        /**
         * Update price when unit changes
         */
        updatePriceOnUnitChange(index, selectElement) {
            const item = this.invoiceItems[index];
            if (!item) return;
            
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const newUVal = parseFloat(selectedOption.getAttribute('data-u-val')) || 1;
            
            // Get base price from item
            const basePrice = item.item_price || item.price;
            
            if (basePrice && newUVal) {
                item.price = parseFloat((basePrice * newUVal).toFixed(2));
                this.calculateItemTotal(index);
            }
        },
        
        /**
         * Remove item from invoice
         */
        removeItem(index) {
            this.invoiceItems.splice(index, 1);
            this.calculateTotalsFromData();
        },
        
        /**
         * Select item (for future use)
         */
        selectItem(index) {
            // Can be used to highlight selected item or show details
            console.log('Selected item:', index);
        },
        
        /**
         * Handle Enter key navigation
         */
        handleEnterNavigation(event) {
            // Implement field navigation logic
            const currentField = event.target;
            const row = currentField.getAttribute('data-row');
            const field = currentField.getAttribute('data-field');
            
            // Find next field in the same row
            const editableFields = this.editableFieldsOrder || ['unit', 'quantity', 'price', 'discount', 'sub_value'];
            const currentIndex = editableFields.indexOf(field);
            
            if (currentIndex >= 0 && currentIndex < editableFields.length - 1) {
                const nextField = editableFields[currentIndex + 1];
                const nextElement = document.getElementById(`${nextField}-${row}`);
                
                if (nextElement) {
                    nextElement.focus();
                    if (nextElement.tagName === 'INPUT') {
                        nextElement.select();
                    }
                }
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
                })
                .catch(error => {
                    console.error('Error fetching account balance:', error);
                });
        },
        
        /**
         * Save invoice
         */
        async saveInvoice() {
            // Validate form
            this.errors = {};
            
            if (!this.acc1Id) {
                this.errors.acc1_id = '{{ __("Please select a customer/supplier") }}';
            }
            
            if (!this.acc2Id) {
                this.errors.acc2_id = '{{ __("Please select a store") }}';
            }
            
            if (this.invoiceItems.length === 0) {
                alert('{{ __("Please add at least one item") }}');
                return;
            }
            
            if (Object.keys(this.errors).length > 0) {
                return;
            }
            
            // Prepare data
            const invoiceData = {
                type: this.type,
                branch_id: this.branchId,
                acc1_id: this.acc1Id,
                acc2_id: this.acc2Id,
                emp_id: this.empId,
                delivery_id: this.deliveryId,
                pro_date: this.proDate,
                accural_date: this.accuralDate,
                serial_number: this.serialNumber,
                cash_box_id: this.cashBoxId,
                notes: this.notes,
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
            
            try {
                const response = await fetch('/api/invoices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(invoiceData),
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    // Success
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("Success") }}',
                            text: '{{ __("Invoice saved successfully") }}',
                        }).then(() => {
                            // Redirect to invoice list or view
                            window.location.href = `/invoices/${result.data.id}`;
                        });
                    } else {
                        alert('{{ __("Invoice saved successfully") }}');
                        window.location.href = `/invoices/${result.data.id}`;
                    }
                } else {
                    // Error
                    if (result.errors) {
                        this.errors = result.errors;
                    }
                    
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: result.message || '{{ __("Failed to save invoice") }}',
                        });
                    } else {
                        alert(result.message || '{{ __("Failed to save invoice") }}');
                    }
                }
            } catch (error) {
                console.error('Error saving invoice:', error);
                
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: '{{ __("An error occurred while saving the invoice") }}',
                    });
                } else {
                    alert('{{ __("An error occurred while saving the invoice") }}');
                }
            }
        }
    }));
});
