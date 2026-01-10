{{-- 
    Invoice Scripts Component
    
    هذا الملف يحتوي على جميع Alpine.js components والـ scripts المشتركة بين 
    صفحات إنشاء وتعديل الفواتير.
    
    المكونات:
    - invoiceSearch: البحث عن الأصناف وإضافتها
    - invoiceCalculations: حسابات الفاتورة والتنقل بين الحقول
    - Alpine stores: لمشاركة البيانات بين المكونات
    
    الأهداف:
    - تقليل طلبات السيرفر إلى أقل حد ممكن
    - جميع الحسابات تتم في Alpine.js (client-side)
    - المزامنة مع Livewire فقط عند الحفظ أو تغيير البيانات الحرجة
--}}

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // انتظار تحميل Alpine.js من Livewire
    document.addEventListener('alpine:init', () => {
        console.log('✅ Alpine:init event fired - registering invoice components');
        
        // ========================================
        // Alpine Stores للمشاركة بين المكونات
        // ========================================
        
        // Store للتنقل والحسابات
        if (!Alpine.store('invoiceNavigation')) {
            Alpine.store('invoiceNavigation', {
                moveToNextField: null,
                calculateRowTotal: null,
                editableFieldsOrder: [] // ✅ ترتيب الحقول الديناميكي من Template
            });
        }
        
        // Store للقيم الحسابية (للمشاركة مع footer)
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
        // Global Functions للوصول من أي مكان
        // ========================================
        
        /**
         * ✅ تنسيق الأرقام بدون أصفار زائدة
         */
        window.formatNumber = function(num) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            // تحويل إلى رقم ثم إزالة الأصفار الزائدة
            const numStr = parseFloat(num).toString();
            // إذا كان عدد صحيح، لا نعرض فاصلة عشرية
            if (numStr.indexOf('.') === -1) {
                return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            // إذا كان عشري، نزيل الأصفار الزائدة من النهاية
            const parts = numStr.split('.');
            parts[1] = parts[1].replace(/0+$/, ''); // إزالة الأصفار من النهاية
            if (parts[1] === '') {
                return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + parts[1];
        };
        
        /**
         * ✅ تنسيق الأرقام مع منزلتين عشريتين (للعرض فقط)
         */
        window.formatNumberFixed = function(num, decimals = 2) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            const formatted = parseFloat(num).toFixed(decimals);
            // إزالة الأصفار الزائدة من النهاية
            return formatted.replace(/\.?0+$/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        };
        
        /**
         * التنقل بالـ Enter بين الحقول
         * يمكن استدعاؤها من @keydown.enter في أي حقل
         */
        window.handleEnterNavigation = function(event) {
            const form = event.target.closest('form');
            if (!form) return;
            
            // الوصول عبر Alpine store
            if (Alpine.store('invoiceNavigation')?.moveToNextField) {
                Alpine.store('invoiceNavigation').moveToNextField(event);
                return;
            }
            
            // Fallback: الوصول عبر _x_dataStack
            if (form._x_dataStack?.[0]?.moveToNextField) {
                form._x_dataStack[0].moveToNextField(event);
                return;
            }
            
            // Fallback: الوصول عبر window
            if (window.invoiceCalculationsInstance?.moveToNextField) {
                window.invoiceCalculationsInstance.moveToNextField(event);
                return;
            }
            
            console.error('moveToNextField not found');
        };
        
        /**
         * حساب إجمالي الصف
         * يمكن استدعاؤها من @input في حقول الكمية/السعر/الخصم
         */
        window.handleCalculateRowTotal = function(index) {
            // الوصول عبر Alpine store
            if (Alpine.store('invoiceNavigation')?.calculateRowTotal) {
                Alpine.store('invoiceNavigation').calculateRowTotal(index);
                return;
            }
            
            // Fallback: الوصول عبر form
            const form = document.querySelector('form[x-data*="invoiceCalculations"]');
            if (form?._x_dataStack?.[0]?.calculateRowTotal) {
                form._x_dataStack[0].calculateRowTotal(index);
                return;
            }
            
            // Fallback: الوصول عبر window
            if (window.invoiceCalculationsInstance?.calculateRowTotal) {
                window.invoiceCalculationsInstance.calculateRowTotal(index);
            }
        };
        
        /**
         * ✅ تحديث الكمية عند keyup (لا requests)
         */
        window.handleQuantityKeyup = function(index, event) {
            var val = parseFloat(event.target.value) || 0;
            // الوصول إلى Alpine component من form
            var form = event.target.closest('form');
            if (form && form._x_dataStack && form._x_dataStack[0]) {
                var alpineComponent = form._x_dataStack[0];
                if (alpineComponent.$wire && alpineComponent.$wire.invoiceItems && alpineComponent.$wire.invoiceItems[index]) {
                    alpineComponent.$wire.invoiceItems[index].quantity = val;
                }
            }
            window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
        };
        
        /**
         * ✅ تحديث السعر عند keyup (لا requests)
         */
        window.handlePriceKeyup = function(index, event) {
            var val = parseFloat(event.target.value) || 0;
            // الوصول إلى Alpine component من form
            var form = event.target.closest('form');
            if (form && form._x_dataStack && form._x_dataStack[0]) {
                var alpineComponent = form._x_dataStack[0];
                if (alpineComponent.$wire && alpineComponent.$wire.invoiceItems && alpineComponent.$wire.invoiceItems[index]) {
                    alpineComponent.$wire.invoiceItems[index].price = val;
                }
            }
            window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
        };
        
        /**
         * ✅ تحديث الخصم عند keyup (لا requests)
         */
        window.handleDiscountKeyup = function(index, event) {
            var val = parseFloat(event.target.value) || 0;
            // الوصول إلى Alpine component من form
            var form = event.target.closest('form');
            if (form && form._x_dataStack && form._x_dataStack[0]) {
                var alpineComponent = form._x_dataStack[0];
                if (alpineComponent.$wire && alpineComponent.$wire.invoiceItems && alpineComponent.$wire.invoiceItems[index]) {
                    alpineComponent.$wire.invoiceItems[index].discount = val;
                }
            }
            window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
        };
        
        /**
         * ✅ تحديث القيمة عند keyup (لا requests)
         */
        window.handleSubValueKeyup = function(index, event) {
            var val = parseFloat(event.target.value) || 0;
            // الوصول إلى Alpine component من form
            var form = event.target.closest('form');
            if (form && form._x_dataStack && form._x_dataStack[0]) {
                var alpineComponent = form._x_dataStack[0];
                if (alpineComponent.$wire) {
                    if (alpineComponent.$wire.invoiceItems && alpineComponent.$wire.invoiceItems[index]) {
                        alpineComponent.$wire.invoiceItems[index].sub_value = val;
                    }
                    // حساب الكمية من القيمة
                    if (alpineComponent.$wire.call) {
                        alpineComponent.$wire.call('calculateQuantityFromSubValue', index);
                    }
                }
            }
            window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
        };
        
        /**
         * ✅ Sync صف مع Livewire عند blur (لا requests فوري)
         */
        window.handleFieldBlur = function(index, event) {
            var val = parseFloat(event.target.value) || 0;
            var fieldName = event.target.getAttribute('data-field');
            // الوصول إلى Alpine component من form
            var form = event.target.closest('form');
            if (form && form._x_dataStack && form._x_dataStack[0]) {
                var alpineComponent = form._x_dataStack[0];
                if (alpineComponent.$wire && alpineComponent.$wire.invoiceItems && alpineComponent.$wire.invoiceItems[index]) {
                    if (fieldName === 'quantity') {
                        alpineComponent.$wire.invoiceItems[index].quantity = val;
                    } else if (fieldName === 'price') {
                        alpineComponent.$wire.invoiceItems[index].price = val;
                    } else if (fieldName === 'discount') {
                        alpineComponent.$wire.invoiceItems[index].discount = val;
                    } else if (fieldName === 'sub_value') {
                        alpineComponent.$wire.invoiceItems[index].sub_value = val;
                        if (alpineComponent.$wire.call) {
                            alpineComponent.$wire.call('calculateQuantityFromSubValue', index);
                        }
                    }
                }
            }
            // حساب sub_value
            window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
            // Sync مع Livewire
            if (Alpine.store('invoiceNavigation') && Alpine.store('invoiceNavigation').syncRowToLivewire) {
                Alpine.store('invoiceNavigation').syncRowToLivewire(index);
            }
        };
        
        /**
         * تحديث السعر عند تغيير الوحدة (client-side)
         * ✅ New Logic: Calculate price from BASE PRICE instead of ratio
         */
        window.updatePriceClientSide = function(index, selectElement) {
            // جلب معامل التحويل للوحدة الجديدة
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const newUVal = parseFloat(selectedOption.getAttribute('data-u-val')) || 1;
            const lastUVal = parseFloat(selectElement.getAttribute('data-last-u-val')) || 1;
            
            if (newUVal === lastUVal) return;
            
            // ✅ Try to get the base price from Alpine state
            let basePrice = null;
            const form = selectElement.closest('form');
            if (form && form._x_dataStack && form._x_dataStack[0]) {
                const alpineComponent = form._x_dataStack[0];
                if (alpineComponent.invoiceItems && alpineComponent.invoiceItems[index]) {
                    // If item_price exists in invoiceItems, use it as base price
                    basePrice = alpineComponent.invoiceItems[index].item_price;
                }
            }
            
            // حساب السعر الجديد
            const priceField = document.getElementById(`price-${index}`);
            if (priceField) {
                let newPrice;
                
                if (basePrice && basePrice > 0) {
                    // ✅ Calculate from base price: newPrice = basePrice × newUVal
                    newPrice = basePrice * newUVal;
                } else {
                    // ❌ Fallback: use ratio (old logic) - less accurate
                    const currentPrice = parseFloat(priceField.value) || 0;
                    const conversionFactor = newUVal / lastUVal;
                    newPrice = currentPrice * conversionFactor;
                    console.warn(`[Price Update] Base price not found for item ${index}, using ratio fallback`);
                }
                
                const finalPrice = parseFloat(newPrice.toFixed(2));
                priceField.value = finalPrice;
                
                // تحديث Alpine.js مباشرة (هذا سيحفز الـ watcher لإعادة الحساب)
                if (form && form._x_dataStack && form._x_dataStack[0]) {
                    const alpineComponent = form._x_dataStack[0];
                    if (alpineComponent.invoiceItems && alpineComponent.invoiceItems[index]) {
                        // تحديث السعر والوحدة في Alpine state
                        // السعر سيعيد تشغيل الحسابات تلقائياً بسبب watcher invoiceItems
                        alpineComponent.invoiceItems[index].price = finalPrice;
                        alpineComponent.invoiceItems[index].unit_id = selectElement.value;
                        
                        // تحديث Livewire (بدون request فوري)
                        if (alpineComponent.$wire) {
                            alpineComponent.$wire.set(`invoiceItems.${index}.price`, finalPrice, false);
                            alpineComponent.$wire.set(`invoiceItems.${index}.unit_id`, selectElement.value, false);
                        }
                    } else {
                        // Fallback: إذا لم نجد المكون في الـ stack
                        window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
                    }
                } else {
                    // Fallback التقليدي
                    window.handleCalculateRowTotal && window.handleCalculateRowTotal(index);
                }
            }
            
            // حفظ معامل التحويل الجديد
            selectElement.setAttribute('data-last-u-val', newUVal);
        };
        
        // ========================================
        // invoiceSearch Component
        // ========================================
        // ========================================
        // invoiceSearch Component (Client-Side Refactored)
        // ========================================
        Alpine.data('invoiceSearch', (config) => ({
            searchTerm: '',
            barcodeTerm: '',
            searchResults: [],
            allItems: [], // Stores all items fetched from API
            fuse: null,   // Fuse.js instance
            loading: false,
            showResults: false,
            selectedIndex: -1,
            isCreateNewItemSelected: false,
            invoiceType: config.invoiceType || 10,
            branchId: config.branchId || '',
            priceType: config.priceType || 1,
            storeId: config.storeId || '',
            currentItems: config.currentItems || [],
            
            // Internal state
            _keydownHandler: null,
            _refreshInterval: null,
            lastUpdated: null,

            async init() {
                console.log('invoiceSearch (Client-Side) init - config:', config);
                
                // 1. Load Items from API
                await this.loadItems();
                
                // 2. Setup Background Refresh (Every 60 seconds)
                this._refreshInterval = setInterval(() => {
                    console.log('⏰ Auto-refreshing items data...');
                    this.loadItems(true);
                }, 60000);

                // 3. Watch for Livewire changes
                if (this.$wire) {
                    this.$watch('$wire.invoiceItems', (items) => {
                        this.currentItems = items || [];
                    });
                }
                
                this.$nextTick(() => {
                    this.setupKeyboardNavigation();
                });
            },

            async loadItems(isBackground = false) {
                if (!isBackground) this.loading = true;
                
                console.log(isBackground ? '🔄 loadItems (Background)...' : '🚀 loadItems (Manual)...', { branch: this.branchId, type: this.invoiceType });
                
                try {
                    const response = await fetch(`/api/items/lite?branch_id=${this.branchId}&type=${this.invoiceType}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        if (!isBackground) { // Only log error prominently if manual
                            const text = await response.text();
                            console.error('❌ loadItems: Server Error:', text);
                            throw new Error('Failed to fetch items: ' + response.status);
                        }
                        return;
                    }
                    
                    const newData = await response.json();
                    
                    // Only update if we got data
                    if (Array.isArray(newData)) {
                        this.allItems = newData;
                        this.lastUpdated = new Date();
                        
                        // Re-Initialize Fuse.js
                        if (window.Fuse) {
                            const options = {
                                keys: ['name', 'code', 'barcode'], 
                                threshold: 0.3,
                                ignoreLocation: true
                            };
                            this.fuse = new Fuse(this.allItems, options);
                            console.log(`✅ Items refreshed. Count: ${this.allItems.length}. Time: ${this.lastUpdated.toLocaleTimeString()}`);
                        }
                    }

                } catch (error) {
                    console.error('🔥 loadItems: Error:', error);
                    if (!isBackground) {
                         Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحديث البيانات' });
                    }
                } finally {
                    if (!isBackground) this.loading = false;
                }
            },
            
            /**
             * إعداد التنقل بالكيبورد
             */
            setupKeyboardNavigation() {
                const searchInput = document.getElementById('search-input');
                if (!searchInput) return;
                
                const component = this;
                const keydownHandler = (e) => {
                    const searchTerm = component.searchTerm || '';
                    const searchResults = Array.isArray(component.searchResults) ? component.searchResults : [];
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        requestAnimationFrame(() => {
                            if (searchResults.length > 0 || searchTerm.length > 0) component.selectNext();
                        });
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        requestAnimationFrame(() => {
                            if (searchResults.length > 0 || searchTerm.length > 0) component.selectPrevious();
                        });
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        requestAnimationFrame(() => {
                            if (searchResults.length > 0 || searchTerm.length > 0) component.addSelectedItem();
                        });
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        component.clearSearch(true);
                    }
                };
                
                if (this._keydownHandler) {
                    searchInput.removeEventListener('keydown', this._keydownHandler, true);
                }
                searchInput.addEventListener('keydown', keydownHandler, true);
                this._keydownHandler = keydownHandler;
            },
            
            handleSearchFocus() {
                if ((this.searchTerm && this.searchTerm.length > 0) || this.searchResults.length > 0) {
                    this.showResults = true;
                }
            },

            /**
             * البحث عن الأصناف Use Fuse.js
             */
            search() {
                if (!this.searchTerm || this.searchTerm.length < 2) {
                    this.searchResults = [];
                    this.showResults = false;
                    this.selectedIndex = -1;
                    this.isCreateNewItemSelected = false;
                    return;
                }

                this.showResults = true;
                
                if (this.fuse) {
                    // Client-Side Search
                    const results = this.fuse.search(this.searchTerm);
                    // Map back to item and limit results
                    this.searchResults = results.map(result => result.item).slice(0, 50);
                    
                    if (this.searchResults.length > 0) {
                        this.selectedIndex = 0;
                        this.isCreateNewItemSelected = false;
                    } else {
                        // No results -> Suggest creation
                        this.selectedIndex = 0;
                        this.isCreateNewItemSelected = true;
                    }
                }
            },

            /**
             * معالجة إدخال الباركود (Client-Side)
             */
            handleBarcodeEnter() {
                if (!this.barcodeTerm || !this.barcodeTerm.trim()) return;
                
                const term = this.barcodeTerm.trim();
                console.log('🔍 Searching for barcode:', term, 'in', this.allItems.length, 'items');
                
                // البحث في المصفوفة المحلية
                // item.barcode should be an array of strings
                const foundItem = this.allItems.find(item => {
                    if (!item.barcode) return false;
                    // Handle both array and string formats
                    if (Array.isArray(item.barcode)) {
                        return item.barcode.includes(term);
                    } else if (typeof item.barcode === 'string') {
                        return item.barcode === term;
                    }
                    return false;
                });

                if (foundItem) {
                    // إذا وجد الصنف -> إضافة سريعة
                    console.log('✅ Barcode found locally:', foundItem.name, foundItem);
                    this.barcodeTerm = ''; // تفريغ الحقل فوراً
                    this.addItemFast(foundItem);
                } else {
                    console.log('⚠️ Barcode not found locally:', term);
                    // إذا لم يوجد -> فتح نافذة إنشاء صنف جديد
                    // Trigger Livewire event listener 'prompt-create-item-from-barcode'
                    if (typeof Livewire !== 'undefined') {
                        Livewire.dispatch('prompt-create-item-from-barcode', { barcode: term });
                    } else {
                        // Fallback: dispatch custom event
                        window.dispatchEvent(new CustomEvent('prompt-create-item-from-barcode', { detail: { barcode: term } }));
                    }
                    this.barcodeTerm = ''; // تفريغ الحقل بعد إظهار الرسالة
                }
            },

            selectNext() {
                const totalItems = this.searchResults.length;
                if (totalItems === 0 && this.searchTerm?.length > 0) {
                    this.selectedIndex = 0;
                    this.isCreateNewItemSelected = true;
                    return;
                }
                if (totalItems > 0) {
                    this.isCreateNewItemSelected = false;
                    this.selectedIndex = this.selectedIndex < totalItems - 1 ? this.selectedIndex + 1 : 0;
                    this.scrollToSelected();
                }
            },

            selectPrevious() {
                const totalItems = this.searchResults.length;
                if (totalItems === 0 && this.searchTerm?.length > 0) {
                    this.selectedIndex = 0;
                    this.isCreateNewItemSelected = true;
                    return;
                }
                if (totalItems > 0) {
                    this.isCreateNewItemSelected = false;
                    this.selectedIndex = this.selectedIndex > 0 ? this.selectedIndex - 1 : totalItems - 1;
                    this.scrollToSelected();
                }
            },

            scrollToSelected() {
                this.$nextTick(() => {
                    const selected = document.querySelector('.search-item-' + this.selectedIndex);
                    if (selected) {
                        selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                });
            },

            addSelectedItem() {
                if (this.isCreateNewItemSelected || (this.searchResults.length === 0 && this.searchTerm?.length > 0)) {
                    this.createNewItem();
                    return;
                }
                if (this.selectedIndex >= 0 && this.searchResults[this.selectedIndex]) {
                    this.addItemFast(this.searchResults[this.selectedIndex]);
                }
            },

            /**
             * إضافة صنف للفاتورة (يرسل ID للسيرفر فقط)
             */
            async addItemFast(item) {
                if (!item?.id) return;
                
                // 1. UI Optimization: Hide results immediately
                this.showResults = false;
                this.searchTerm = ''; // Clear search immediately
                
                this.loading = true; // Show spinner if needed (optional)
                
                try {
                    // 2. Call Server to Add Item (Calculations happen there)
                    const result = await this.$wire.call('addItemFromSearchFast', item.id);
                    
                    if (result?.success) {
                        // 3. Update Row Total Calculation
                        if (result.index !== undefined) {
                            window.handleCalculateRowTotal(result.index);
                            
                            // 4. Focus Quantity Field
                            this.$nextTick(() => {
                                setTimeout(() => {
                                    const quantityField = document.getElementById(`quantity-${result.index}`);
                                    if (quantityField) {
                                        quantityField.focus();
                                        quantityField.select(); // Select content for easy overwrite
                                    }
                                }, 100);
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error adding item:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: error.message || 'فشل في إضافة الصنف'
                    });
                } finally {
                    this.loading = false;
                }
            },

            async createNewItem() {
                if (!this.searchTerm?.trim()) return;
                
                this.showResults = false;
                const itemName = this.searchTerm.trim();
                this.searchTerm = '';
                
                try {
                    const result = await this.$wire.call('createNewItem', itemName);
                    
                    if (result?.success && result.index !== undefined) {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                const quantityField = document.getElementById(`quantity-${result.index}`);
                                if (quantityField) quantityField.focus();
                            }, 200);
                        });
                    }
                } catch (error) {
                    console.error('Error creating item:', error);
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل في إنشاء الصنف' });
                }
            },

            clearSearch(focusSearch = false) {
                this.searchTerm = '';
                this.searchResults = [];
                this.showResults = false;
                this.selectedIndex = -1;
                this.isCreateNewItemSelected = false;
                
                if (focusSearch) {
                    this.$nextTick(() => {
                        const searchInput = document.getElementById('search-input');
                        if (searchInput) searchInput.focus();
                    });
                }
            },
            
            reinitializeSearch() {
                // No-op for client side usually, or maybe re-fetch items?
                // For now, just ensure keynav works.
                this.$nextTick(() => {
                    setTimeout(() => this.setupKeyboardNavigation(), 150);
                });
            }
        }));

        // ========================================
        // invoiceCalculations Component
        // ========================================
        Alpine.data('invoiceCalculations', (initialData) => ({
            invoiceItems: initialData.invoiceItems || [],
            discountPercentage: initialData.discountPercentage,
            additionalPercentage: initialData.additionalPercentage,
            vatPercentage: initialData.vatPercentage !== undefined ? initialData.vatPercentage : initialData.defaultVatPercentage,
            withholdingTaxPercentage: initialData.withholdingTaxPercentage !== undefined ? initialData.withholdingTaxPercentage : initialData.defaultWithholdingTaxPercentage,
            receivedFromClient: initialData.receivedFromClient,
            dimensionsUnit: initialData.dimensionsUnit || 'cm',
            enableDimensionsCalculation: initialData.enableDimensionsCalculation || false,
            invoiceType: initialData.invoiceType || 10,
            isCashAccount: initialData.isCashAccount || false,
            editableFieldsOrder: initialData.editableFieldsOrder || ['unit', 'quantity', 'batch_number', 'expiry_date', 'length', 'width', 'height', 'density', 'price', 'discount', 'sub_value'],
            currentBalance: parseFloat(initialData.currentBalance) || 0,
            calculatedBalanceAfter: parseFloat(initialData.currentBalance) || 0,
            
            // Calculated values (Initialized from initial data for edit mode support)
            subtotal: initialData.subtotal !== undefined ? initialData.subtotal : 0,
            discountValue: initialData.discountValue,
            additionalValue: initialData.additionalValue,
            vatValue: initialData.vatValue,
            withholdingTaxValue: initialData.withholdingTaxValue,
            totalAfterAdditional: initialData.totalAfterAdditional !== undefined ? initialData.totalAfterAdditional : 0,
            remaining: 0,
            
            // Text inputs for smooth typing (prevent number conversion during typing)
            // Initialize as empty strings, will be set in init() to handle Livewire reactive objects
            discountValueText: '',
            additionalValueText: '',
            
            // Internal flags (for logic control)
            _discountValueFromPercentage: false,
            _additionalValueFromPercentage: false,
            _vatValueFromPercentage: false,
            _calculateDebounceTimer: null,
            _updateDisplaysDebounceTimer: null,
            isInternalUpdate: false, // ✅ Lock to prevent watchers from firing on internal updates

            init() {
                console.log('🚀 invoiceCalculations init start:', {
                    discountValue: this.discountValue,
                    additionalValue: this.additionalValue,
                    additionalPercentage: this.additionalPercentage,
                    subtotal: this.subtotal
                });
                
                // Initialize text inputs from initial values (handle Livewire reactive objects)
                const extractNumericValue = (val) => {
                    if (val == null || val === '') return '0';
                    let num = val;
                    if (typeof val === 'object' && val != null) {
                        num = val.value ?? val.__value ?? val;
                    }
                    const parsed = parseFloat(num);
                    return isNaN(parsed) ? '0' : String(parsed);
                };
                
                this.discountValueText = extractNumericValue(this.discountValue);
                this.additionalValueText = extractNumericValue(this.additionalValue);
                
                // حفظ reference في window
                window.invoiceCalculationsInstance = this;
                
                // حفظ الدوال في Alpine store
                Alpine.store('invoiceNavigation').moveToNextField = (event) => this.moveToNextField(event);
                Alpine.store('invoiceNavigation').calculateRowTotal = (index) => this.calculateRowTotal(index);
                Alpine.store('invoiceNavigation').syncRowToLivewire = (index) => this.syncRowToLivewire(index);
                Alpine.store('invoiceNavigation').editableFieldsOrder = this.editableFieldsOrder;

                // ✅ مراقبة data-is-cash من DOM (احتياطي)
                this.watchCashAccountChanges();
                
                // ✅ مراقبة تغييرات القيم المحسوبة لتحديث store
                this.setupStoreWatchers();
                
                // ✅ إعداد نظام التنقل بالكيبورد
                this.setupTableNavigation();

                // ✅ مراقبة وحساب الرصيد
                this.setupBalanceWatchers();

                // ✅ مراقبة جميع المدخلات المؤثرة على الحسابات (Reactive Engine)
                this.$watch('invoiceItems', () => {
                   this.calculateTotalsFromData();
                }, { deep: true });
                
                this.$watch('discountPercentage', () => {
                    if (this.isInternalUpdate) return;
                    this._discountValueFromPercentage = true;
                    this.calculateFinalTotals();
                });
                this.$watch('discountValue', (newVal) => {
                    if (this.isInternalUpdate) return;
                    // Update text input when value changes from percentage calculation
                    if (this._discountValueFromPercentage) {
                        // Handle Livewire reactive objects
                        let num = newVal;
                        if (typeof newVal === 'object' && newVal != null) {
                            num = newVal.value ?? newVal.__value ?? newVal;
                        }
                        const parsed = parseFloat(num);
                        this.discountValueText = isNaN(parsed) ? '0' : String(parsed);
                    }
                    if (!this._discountValueFromPercentage) this.calculateFinalTotals();
                });
                this.$watch('additionalPercentage', () => {
                    if (this.isInternalUpdate) return;
                    this._additionalValueFromPercentage = true;
                    this.calculateFinalTotals();
                });
                this.$watch('additionalValue', (newVal) => {
                    if (this.isInternalUpdate) return;
                    // Update text input when value changes from percentage calculation
                    if (this._additionalValueFromPercentage) {
                        // Handle Livewire reactive objects
                        let num = newVal;
                        if (typeof newVal === 'object' && newVal != null) {
                            num = newVal.value ?? newVal.__value ?? newVal;
                        }
                        const parsed = parseFloat(num);
                        this.additionalValueText = isNaN(parsed) ? '0' : String(parsed);
                    }
                   if (!this._additionalValueFromPercentage) this.calculateFinalTotals();
                });
                this.$watch('vatPercentage', () => {
                    if (this.isInternalUpdate) return;
                    this._vatValueFromPercentage = true;
                    this.calculateFinalTotals();
                });
                this.$watch('vatValue', () => {
                    if (this.isInternalUpdate) return;
                    if (!this._vatValueFromPercentage) this.calculateFinalTotals();
                });
                this.$watch('receivedFromClient', () => {
                    if (this.isInternalUpdate) return;
                    this.calculateFinalTotals();
                });
                this.$watch('isCashAccount', () => {
                    this.calculateFinalTotals();
                });

                // ✅ مراقبة تغيير العميل لتحديث الحسابات (بدون تصفير القيم المكتوبة يدوياً)
                this.$watch('acc1Id', (newVal) => {
                    if (newVal) {
                        console.log('🔄 Account Changed:', newVal);
                        
                        // ✅ ننتظر قليلاً للتأكد من أن حالة isCashAccount قد زامنت من Livewire
                        setTimeout(() => {
                            this.calculateFinalTotals();
                        }, 50);
                    }
                });

                // ✅ الاستماع لحدث التصفير من Livewire (Brute Force Reset)
                Livewire.on('reset-invoice-parameters', () => {
                    console.log('🧹 Invoice Parameters Reset Triggered | isCash:', this.isCashAccount);
                    this.discountPercentage = 0;
                    this.discountValue = 0;
                    this.additionalPercentage = 0;
                    this.additionalValue = 0;
                    this.receivedFromClient = 0;
                    
                    // ✅ ننتظر قليلاً للتأكد من مزامنة الحالة النقدية
                    setTimeout(() => {
                        this.calculateFinalTotals();
                    }, 50);
                });
                
                // حساب أولي
                this.calculateTotalsFromData();
                
                // ✅ إعداد التنقل بالأسهم
                this.setupTableNavigation();
            },
            
            /**
             * ✅ التحقق من حالة الحساب النقدي من DOM
             */
            checkCashAccountStatus() {
                const invoiceConfig = document.getElementById('invoice-config');
                if (invoiceConfig) {
                    const isCash = invoiceConfig.getAttribute('data-is-cash') === '1';
                    if (this.isCashAccount !== isCash) {
                        this.isCashAccount = isCash;
                        console.log('💰 Cash Account Status Updated:', this.isCashAccount);
                    }
                }
            },
            
            watchCashAccountChanges() {
                const invoiceConfig = document.getElementById('invoice-config');
                if (!invoiceConfig) return;
                
                const observer = new MutationObserver(() => {
                    const isCash = invoiceConfig.getAttribute('data-is-cash') === '1';
                    if (this.isCashAccount !== isCash) {
                        this.isCashAccount = isCash;
                        // ✅ عند تغيير الحساب إلى نقدي: تحديث المدفوع تلقائياً
                        if (this.isCashAccount) {
                            // إعادة حساب الإجماليات أولاً
                            this.updateDisplaysImmediate();
                            // ثم تحديث المدفوع (سيحدث تلقائياً في updateDisplaysImmediate)
                        }
                    }
                });
                observer.observe(invoiceConfig, { attributes: true, attributeFilter: ['data-is-cash'] });
            },
            
            setupStoreWatchers() {
                ['subtotal', 'discountValue', 'additionalValue', 'totalAfterAdditional', 'remaining', 'receivedFromClient'].forEach(prop => {
                    this.$watch(prop, (value) => {
                        if (Alpine.store('invoiceValues')) {
                            Alpine.store('invoiceValues')[prop] = value;
                        }
                    });
                });
            },
            
            /**
             * ✅ حساب الإجماليات فوراً (بدون debounce) - تُستدعى عند init
             */
            calculateInitialTotals() {
                // ✅ استخدام updateDisplaysImmediate التي تحسب من DOM مباشرة
                this.updateDisplaysImmediate();
                
                console.log('calculateInitialTotals - final:', {
                    subtotal: this.subtotal,
                    discountValue: this.discountValue,
                    totalAfterAdditional: this.totalAfterAdditional,
                    remaining: this.remaining
                });
            },

            syncToStore() {
                if (Alpine.store('invoiceValues')) {
                    Alpine.store('invoiceValues').subtotal = this.subtotal;
                    Alpine.store('invoiceValues').discountValue = this.discountValue;
                    Alpine.store('invoiceValues').additionalValue = this.additionalValue;
                    Alpine.store('invoiceValues').totalAfterAdditional = this.totalAfterAdditional;
                    Alpine.store('invoiceValues').remaining = this.remaining;
                    Alpine.store('invoiceValues').receivedFromClient = this.receivedFromClient;
                }
            },

            /**
             * حساب إجمالي الصف (100% في Alpine.js - لا requests)
             * ✅ تحديث فوري مع debounce قصير جداً للسماح بكتابة الأرقام الكبيرة
             */
            calculateRowTotal(index) {
                // مفرغة: يتم الحساب الآن تلقائياً عبر x-model و deep watch على items
            },
            
            /**
             * ✅ تحديث الإجماليات فوراً (بدون debounce)
             */

            
            /**
             * ✅ Sync صف واحد مع Livewire (تُستدعى عند blur)
             */
            syncRowToLivewire(index) {
                if (!this.$wire) return;
                
                const items = this.$wire.invoiceItems || this.invoiceItems;
                const row = items[index];
                if (!row) return;
                
                // تحديث Livewire بالقيم المحسوبة (بدون request فوري)
                this.$wire.set(`invoiceItems.${index}.quantity`, parseFloat(row.quantity) || 0, false);
                this.$wire.set(`invoiceItems.${index}.price`, parseFloat(row.price) || 0, false);
                this.$wire.set(`invoiceItems.${index}.discount`, parseFloat(row.discount) || 0, false);
                this.$wire.set(`invoiceItems.${index}.sub_value`, parseFloat(row.sub_value) || 0, false);
            },

            /**
             * ✅ حساب الإجماليات بناءً على البيانات (Entangled Data)
             * هذا هو المصدر الوحيد للحقيقة الآن
             */
            calculateTotalsFromData() {
                let tempSubtotal = 0;
                const items = this.invoiceItems || [];
                
                // حساب مجموع الصفوف
                items.forEach(item => {
                   const qty = parseFloat(item.quantity) || 0;
                   const price = parseFloat(item.price) || 0;
                   const discount = parseFloat(item.discount) || 0;
                   
                   const rowTotal = (qty * price) - discount;
                   tempSubtotal += rowTotal;
                   
                   // تحديث قيمة الصف في البيانات
                   item.sub_value = parseFloat(rowTotal.toFixed(2));
                });
                
                this.subtotal = parseFloat(tempSubtotal.toFixed(2));
                
                // ✅ حساب القيم النهائية (خصم، إضافي، ضرائب)
                this.calculateFinalTotals();
            },

            /**
             * ✅ المحرك الموحد للحسابات النهائية
             * يضمن تزامن الخصم، الإضافي، المدفوع، والمتبقي
             */
            calculateFinalTotals() {
                if (!this.subtotal && this.isInitialized) {
                     // Only log if subtotal is missing after init
                     console.log('⚠️ calculateFinalTotals: subtotal is 0');
                }

                // 1. حساب قيمة الخصم
                if (this._discountValueFromPercentage) {
                    this.discountValue = parseFloat(((this.subtotal * this.discountPercentage) / 100).toFixed(2));
                    this.discountValueText = this.discountValue != null && this.discountValue !== '' ? String(this.discountValue) : '0';
                } else if (this.subtotal > 0) {
                    // We are updating FROM Value, so calculate Percentage. 
                    this.isInternalUpdate = true;
                    this.discountPercentage = parseFloat(((this.discountValue / this.subtotal) * 100).toFixed(2));
                    this.isInternalUpdate = false;
                }

                const afterDiscount = parseFloat((this.subtotal - this.discountValue).toFixed(2));

                // 2. حساب القيمة الإضافية
                if (this._additionalValueFromPercentage) {
                    this.additionalValue = parseFloat(((afterDiscount * this.additionalPercentage) / 100).toFixed(2));
                    this.additionalValueText = this.additionalValue != null && this.additionalValue !== '' ? String(this.additionalValue) : '0';
                } else if (afterDiscount > 0) {
                    // We are updating FROM Value, so calculate Percentage.
                    this.isInternalUpdate = true;
                    this.additionalPercentage = parseFloat(((this.additionalValue / afterDiscount) * 100).toFixed(2));
                    this.isInternalUpdate = false;
                }
                
                if (this.additionalValue > 0) {
                    console.log('✅ additionalValue confirmed:', this.additionalValue, 'per:', this.additionalPercentage);
                }
                
                const afterAdditional = parseFloat((afterDiscount + this.additionalValue).toFixed(2));

                // 3. حساب ضريبة القيمة المضافة (VAT) - readonly من الإعدادات
                if (this.vatPercentage !== undefined && this.vatValue !== undefined) {
                    if (this._vatValueFromPercentage) {
                         this.vatValue = parseFloat(((afterAdditional * this.vatPercentage) / 100).toFixed(2));
                    } else if (afterAdditional > 0) {
                         // Similar logic if VAT value was editable (it's readonly usually but just in case)
                         // this.vatPercentage = ...
                    }
                }

                // 4. حساب خصم المنبع - readonly من الإعدادات
                if (this.withholdingTaxPercentage !== undefined && this.withholdingTaxValue !== undefined) {
                     this.withholdingTaxValue = parseFloat(((afterAdditional * this.withholdingTaxPercentage) / 100).toFixed(2));
                }
                
                // 5. الإجمالي النهائي (بعد الضريبة وخصم المنبع)
                const vatAmount = parseFloat(this.vatValue || 0);
                const withholdingAmount = parseFloat(this.withholdingTaxValue || 0);
                this.totalAfterAdditional = parseFloat((afterAdditional + vatAmount - withholdingAmount).toFixed(2));
                
                // 6. الحسابات النقدية
                if (this.isCashAccount) {
                    this.receivedFromClient = this.totalAfterAdditional;
                    this.remaining = 0;
                } 
                // 7. الحسابات العادية
                else {
                    // للمحافظة على المبلغ المدفوع حتى لو أصبح الإجمالي صفراً (مثلاً عند حذف صنف)
                    this.remaining = parseFloat((this.totalAfterAdditional - this.receivedFromClient).toFixed(2));
                }
                
                // 8. تحديث الرصيد والمتجر
                this.calculateBalance();
                this.syncToStore();
            },

            // ⚠️ Legacy Wrappers (توجيه الاستدعاءات القديمة للنظام الجديد)
            updateDisplaysImmediate() {
                this.calculateTotalsFromData();
            },
            
            updateDisplays() {
                 this.calculateTotalsFromData();
            },

            // ✅ دوال فارغة لأن Binding يتعامل معها الآن
            calculateRowTotal(index) {},
            syncRowToLivewire(index) {},

            updateDiscountFromPercentage() {
                this._discountValueFromPercentage = true;
                this.calculateFinalTotals();
            },

            updateDiscountFromValue() {
                this._discountValueFromPercentage = false;
                this.calculateFinalTotals();
            },

            updateAdditionalFromPercentage() {
                this._additionalValueFromPercentage = true;
                this.calculateFinalTotals();
            },

            updateAdditionalFromValue() {
                this._additionalValueFromPercentage = false;
                this.calculateFinalTotals();
            },

            updateVatFromPercentage() {
                this._vatValueFromPercentage = true;
                this.calculateFinalTotals();
            },

            updateVatFromValue() {
                this._vatValueFromPercentage = false;
                this.calculateFinalTotals();
            },

            updateReceived() {
                this.calculateFinalTotals();
            },

            /**
             * Calculate aggregated tax (VAT) from all items (for item-level tax mode)
             * @returns {number} Sum of all item VAT values
             */
            calculateAggregatedTax() {
                if (!this.items || !Array.isArray(this.items)) {
                    return 0;
                }
                
                return this.items.reduce((sum, item) => {
                    const itemVat = parseFloat(item.item_vat || 0);
                    return sum + itemVat;
                }, 0);
            },

            /**
             * Calculate aggregated withholding tax from all items (for item-level withholding tax mode)
             * @returns {number} Sum of all item withholding tax values
             */
            calculateAggregatedTaxDiscount() {
                if (!this.items || !Array.isArray(this.items)) {
                    return 0;
                }
                
                return this.items.reduce((sum, item) => {
                    const itemWithholdingTax = parseFloat(item.item_withholding_tax || 0);
                    return sum + itemWithholdingTax;
                }, 0);
            },

            /**
             * ✅ مزامنة جميع القيم إلى Livewire (تُستدعى قبل الحفظ)
             * تزامن: الأصناف + الإجماليات + الخصم + الإضافي
             */
            syncToLivewire() {
                if (!this.$wire) {
                    console.error('syncToLivewire: $wire not available');
                    return;
                }

                console.log('🔄 Syncing to Livewire...', {
                    itemsCount: this.invoiceItems?.length || 0,
                    subtotal: this.subtotal,
                    discountValue: this.discountValue,
                    totalAfterAdditional: this.totalAfterAdditional
                });

                // ✅ 1. إعادة حساب جميع الإجماليات قبل المزامنة
                this.updateDisplaysImmediate();

                // ✅ 2. جمع بيانات الأصناف المحسوبة
                const items = this.$wire.invoiceItems || this.invoiceItems;
                const invoiceItemsData = [];
                if (items && Array.isArray(items)) {
                    items.forEach((item, index) => {
                        // حساب sub_value إذا لم يكن موجوداً
                        const quantity = parseFloat(item.quantity) || 0;
                        const price = parseFloat(item.price) || 0;
                        const discount = parseFloat(item.discount) || 0;
                        const subValue = (quantity * price) - discount;

                        invoiceItemsData.push({
                            quantity: quantity,
                            price: price,
                            discount: discount,
                            sub_value: subValue
                        });

                        // تحديث القيم في Livewire مباشرة
                        this.$wire.set(`invoiceItems.${index}.quantity`, quantity, false);
                        this.$wire.set(`invoiceItems.${index}.price`, price, false);
                        this.$wire.set(`invoiceItems.${index}.discount`, discount, false);
                        this.$wire.set(`invoiceItems.${index}.sub_value`, subValue, false);
                    });
                }

                // ✅ 3. إرسال جميع البيانات إلى Livewire عبر syncFromAlpine
                const alpineData = {
                    invoiceItems: invoiceItemsData,
                    subtotal: this.subtotal,
                    discount_percentage: this.discountPercentage,
                    discount_value: this.discountValue,
                    additional_percentage: this.additionalPercentage,
                    additional_value: this.additionalValue,
                    vat_percentage: this.vatPercentage,
                    vat_value: this.vatValue,
                    withholding_tax_percentage: this.withholdingTaxPercentage,
                    withholding_tax_value: this.withholdingTaxValue,
                    received_from_client: this.receivedFromClient,
                    total_after_additional: this.totalAfterAdditional
                };

                // استدعاء syncFromAlpine في Livewire
                if (this.$wire.call && typeof this.$wire.call === 'function') {
                    this.$wire.call('syncFromAlpine', alpineData);
                }

                // ✅ 4. مزامنة الإجماليات والخصم مباشرة أيضاً
                this.$wire.set('discount_percentage', this.discountPercentage, false);
                this.$wire.set('discount_value', this.discountValue, false);
                this.$wire.set('additional_percentage', this.additionalPercentage, false);
                this.$wire.set('additional_value', this.additionalValue, false);
                this.$wire.set('received_from_client', this.receivedFromClient, false);
                this.$wire.set('subtotal', this.subtotal, false);
                this.$wire.set('total_after_additional', this.totalAfterAdditional, false);

                console.log('✅ Sync completed', alpineData);
            },

            /**
             * ✅ إعداد مراقبات الرصيد
             */
            setupBalanceWatchers() {
                // مراقبة تغيير الرصيد الحالي من Livewire
                if (this.$wire) {
                    this.$watch('$wire.currentBalance', (val) => {
                        this.currentBalance = parseFloat(val) || 0;
                        this.calculateBalance();
                    });
                }

                // مراقبة المتغيرات التي تؤثر على الرصيد
                this.$watch('totalAfterAdditional', () => this.calculateBalance());
                this.$watch('receivedFromClient', () => this.calculateBalance());
                this.$watch('currentBalance', () => this.calculateBalance());
                
                // حساب أولي
                this.calculateBalance();
            },

            /**
             * ✅ حساب الرصيد بعد الفاتورة (مطابق لمنطق PHP)
             */
            calculateBalance() {
                const netTotal = parseFloat(this.totalAfterAdditional) || 0;
                const received = parseFloat(this.receivedFromClient) || 0;
                const type = parseInt(this.invoiceType);
                let effect = 0;

                if (type == 10) { // مبيعات
                    effect = netTotal - received;
                } else if (type == 11) { // مشتريات
                    effect = -(netTotal - received);
                } else if (type == 12) { // مردود مبيعات
                    effect = -netTotal + received;
                } else if (type == 13) { // مردود مشتريات
                    effect = netTotal - received;
                }

                this.calculatedBalanceAfter = (parseFloat(this.currentBalance) || 0) + effect;
                
                // تحديث Store
                if (Alpine.store('invoiceValues')) {
                    Alpine.store('invoiceValues').calculatedBalanceAfter = this.calculatedBalanceAfter;
                }
            },

            /**
             * التنقل بالكيبورد بين الحقول (يدعم الأسهم والـ Enter)
             */
            setupTableNavigation() {
                // استخدام مستمع أحداث على النافذة ليكون أكثر مرونة مع تغيرات DOM
                window.addEventListener('keydown', (e) => {
                    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(e.key)) {
                        this.handleKeyboardNavigation(e);
                    }
                }, true); // true للالتقاط في مرحلة Capture
            },

            handleKeyboardNavigation(e) {
                const field = e.target;
                if (!field || !field.classList?.contains('invoice-field')) return;

                const directions = {
                    'ArrowUp': 'up',
                    'ArrowDown': 'down',
                    'ArrowRight': 'previous', // RTL: Right arrow moves to previous element
                    'ArrowLeft': 'next',      // RTL: Left arrow moves to next element
                    'Enter': 'next'
                };

                const action = directions[e.key];
                if (!action) return;

                // استخراج معرف الحقل والصف
                const id = field.id;
                if (!id) return;
                
                const parts = id.split('-');
                if (parts.length < 2) return;
                
                // التعامل مع المعرفات التي قد تحتوي على - اضافية، نفترض أن الرقم في الآخر
                const rowIndex = parseInt(parts[parts.length - 1]);
                const fieldName = parts.slice(0, parts.length - 1).join('-');
                
                if (isNaN(rowIndex)) return;

                // منع السلوك الافتراضي (مثل تحرك المؤشر أو تقديم النموذج)
                e.preventDefault();

                if (action === 'next' || action === 'previous') {
                    const allFields = Array.from(document.querySelectorAll('.invoice-field'));
                    // تصفية الحقول المرئية فقط
                    const visibleFields = allFields.filter(el => this.isElementAccessible(el));
                    
                    const currentIndex = visibleFields.indexOf(field);
                    if (currentIndex === -1) return;
                    
                    let nextIndex;
                    if (action === 'next') {
                        nextIndex = currentIndex + 1;
                    } else {
                        nextIndex = currentIndex - 1;
                    }
                    
                    if (nextIndex >= 0 && nextIndex < visibleFields.length) {
                        const nextField = visibleFields[nextIndex];
                        nextField.focus();
                        if (nextField.tagName === 'INPUT') nextField.select();
                    } else if (nextIndex >= visibleFields.length) {
                        // الانتقال للبحث عند نهاية الجدول
                        const searchInput = document.getElementById('search-input') || document.getElementById('barcode-search');
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select?.();
                        }
                    }
                } else if (action === 'up' || action === 'down') {
                    // التنقل الرأسي يعتمد على اسم الحقل والصف
                    const targetRow = action === 'down' ? rowIndex + 1 : rowIndex - 1;
                    const nextId = `${fieldName}-${targetRow}`;
                    const nextEl = document.getElementById(nextId);
                    
                    if (nextEl && this.isElementAccessible(nextEl)) {
                        nextEl.focus();
                        if (nextEl.tagName === 'INPUT') nextEl.select();
                    }
                }
            },

            moveToNextField(event) {
                if (event) {
                    // تحويل الاستدعاء إلى نظام التنقل الموحد
                    this.handleKeyboardNavigation(event);
                }
            },

            /**
             * التحقق من إمكانية الوصول للعنصر
             */
            isElementAccessible(element) {
                if (!element) return false;
                if (!document.body.contains(element)) return false;
                
                try {
                    const style = window.getComputedStyle(element);
                    if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return false;
                    if (element.disabled) return false;
                    
                    const rect = element.getBoundingClientRect();
                    return rect.width > 0 && rect.height > 0;
                } catch (error) {
                    return false;
                }
            }
        }));

        console.log('✅ Invoice Alpine components registered successfully');
    });
</script>

{{-- Livewire Events --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('enlarge-menu');
    });

    document.addEventListener('livewire:init', () => {
        if (typeof Livewire === 'undefined') return;
        
        Livewire.on('swal', (data) => {
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon,
            }).then(() => location.reload());
        });
        
        Livewire.on('error', (data) => {
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon,
            });
        });

        Livewire.on('success', (data) => {
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon,
            });
        });
        
        Livewire.on('open-print-window', (event) => {
            const printWindow = window.open(event.url, '_blank');
            if (printWindow) {
                printWindow.onload = () => printWindow.print();
            } else {
                alert("{{ __('Please allow pop-ups in your browser for printing.') }}");
            }
        });
        
        Livewire.on('focus-quantity', (event) => {
            const index = event.index;
            if (index === null || index === undefined) return;
            
            setTimeout(() => {
                const quantityField = document.getElementById(`quantity-${index}`);
                if (quantityField) {
                    quantityField.focus();
                    quantityField.select();
                }
            }, 300);
        });
        
        Livewire.on('focus-field', (event) => {
            setTimeout(() => {
                const field = document.getElementById(`${event.field}-${event.rowIndex}`);
                if (field) {
                    field.focus();
                    field.select?.();
                }
            }, 100);
        });
        
        Livewire.on('focus-search-field', () => {
            setTimeout(() => {
                const searchField = document.getElementById('search-input');
                if (searchField) {
                    searchField.focus();
                    searchField.select?.();
                }
            }, 100);
        });
    });

    document.addEventListener('livewire:initialized', () => {
        if (typeof Livewire === 'undefined') return;
        
        Livewire.on('prompt-create-item-from-barcode', (event) => {
            Swal.fire({
                title: "{{ __('Item not found!') }}",
                text: `{{ __('Barcode ') }}"${event.barcode}"{{ __(' is not registered. Do you want to create a new item?') }}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "{{ __('Yes, create it') }}",
                cancelButtonText: "{{ __('Cancel') }}",
                input: 'text',
                inputLabel: "{{ __('Please enter the new item name') }}",
                inputPlaceholder: "{{ __('Type the item name here...') }}",
                inputValidator: (value) => !value && "{{ __('Item name is required!') }}"
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // ✅ محاولة الوصول إلى Livewire component بطرق متعددة
                    let wireComponent = null;
                    
                    // الطريقة 1: من خلال form element
                    const form = document.querySelector('form[wire\\:id]');
                    if (form) {
                        const wireId = form.getAttribute('wire:id');
                        if (wireId) {
                            wireComponent = Livewire.find(wireId);
                        }
                    }
                    
                    // الطريقة 2: من خلال Alpine component (إذا كان متاحاً)
                    if (!wireComponent && window.invoiceCalculationsInstance?.$wire) {
                        wireComponent = window.invoiceCalculationsInstance.$wire;
                    }
                    
                    // الطريقة 3: البحث في جميع المكونات
                    if (!wireComponent && typeof Livewire !== 'undefined') {
                        const allComponents = Livewire.all();
                        if (allComponents && allComponents.length > 0) {
                            wireComponent = allComponents[0];
                        }
                    }
                    
                    if (wireComponent) {
                        // ✅ استخدام createItemFromPrompt (التي تستدعي createNewItem داخلياً)
                        wireComponent.call('createItemFromPrompt', result.value, event.barcode)
                            .then((response) => {
                                if (response?.success || response?.index !== undefined) {
                                    // ✅ التركيز على حقل الكمية بعد إضافة الصنف
                                    setTimeout(() => {
                                        const quantityField = document.getElementById(`quantity-${response.index}`);
                                        if (quantityField) {
                                            quantityField.focus();
                                            quantityField.select();
                                        }
                                    }, 200);
                                }
                            })
                            .catch((error) => {
                                console.error('Error creating item from barcode:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطأ',
                                    text: 'فشل في إنشاء الصنف: ' + (error.message || 'حدث خطأ غير متوقع')
                                });
                            });
                    } else {
                        console.error('Livewire component not found');
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'لم يتم العثور على مكون Livewire. يرجى إعادة تحميل الصفحة.'
                        });
                    }
                }
            });
        });
    });

    // Item not found event
    document.addEventListener('item-not-found', function(event) {
        const { term = '', type = 'barcode' } = event.detail;
        
        const title = "{{ __('Item not found') }}";
        const text = type === 'barcode' 
            ? "{{ __('The item with the entered barcode was not found. Do you want to add a new item?') }}"
            : `{{ __('Item ') }}"${term}"{{ __(' not found. Do you want to add a new item?') }}`;
        const itemCreateUrl = type === 'barcode'
            ? `{{ route('items.create') }}?barcode=${encodeURIComponent(term)}`
            : `{{ route('items.create') }}?name=${encodeURIComponent(term)}`;

        Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, add item') }}",
            cancelButtonText: "{{ __('No') }}",
            allowEscapeKey: true
        }).then((result) => {
            if (result.isConfirmed) window.open(itemCreateUrl, '_blank');
        });
    });

    // Alpine directive for focus-next
    document.addEventListener('alpine:init', () => {
        Alpine.directive('focus-next', (el, { expression }) => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const nextField = document.getElementById(expression);
                    if (nextField) {
                        nextField.focus();
                        nextField.select?.();
                    }
                }
            });
        });
    });
</script>
