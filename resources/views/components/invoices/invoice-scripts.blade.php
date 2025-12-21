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
         */
        window.updatePriceClientSide = function(index, selectElement) {
            // جلب معامل التحويل للوحدة الجديدة
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const newUVal = parseFloat(selectedOption.getAttribute('data-u-val')) || 1;
            const lastUVal = parseFloat(selectElement.getAttribute('data-last-u-val')) || 1;
            
            if (newUVal === lastUVal) return;
            
            // تحديث السعر بناءً على معامل التحويل
            const priceField = document.getElementById(`price-${index}`);
            if (priceField) {
                const currentPrice = parseFloat(priceField.value) || 0;
                const conversionFactor = newUVal / lastUVal;
                const newPrice = currentPrice * conversionFactor;
                priceField.value = newPrice.toFixed(2);
                
                // تحديث Livewire
                if (typeof Livewire !== 'undefined') {
                    const component = Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
                    if (component) {
                        component.set(`invoiceItems.${index}.price`, newPrice, false);
                    }
                }
                
                // تحديث الإجمالي
                window.handleCalculateRowTotal(index);
            }
            
            // حفظ معامل التحويل الجديد
            selectElement.setAttribute('data-last-u-val', newUVal);
        };
        
        // ========================================
        // invoiceSearch Component
        // ========================================
        Alpine.data('invoiceSearch', (config) => ({
            searchTerm: '',
            searchResults: [],
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
            _searchDebounceTimer: null,

            init() {
                console.log('invoiceSearch init - config:', config);
                
                // مراقبة تغييرات invoiceItems من Livewire
                if (this.$wire) {
                    this.$watch('$wire.invoiceItems', (items) => {
                        this.currentItems = items || [];
                    });
                }
                
                this.$nextTick(() => {
                    this.setupKeyboardNavigation();
                });
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
                    const isLoading = component.loading || false;
                    
                    // انتظار انتهاء التحميل
                    if (isLoading && ['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key)) {
                        setTimeout(() => keydownHandler(e), 100);
                        return;
                    }
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        e.stopPropagation();
                        requestAnimationFrame(() => {
                            if (searchResults.length > 0 || searchTerm.length > 0) {
                                component.selectNext();
                            }
                        });
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        e.stopPropagation();
                        requestAnimationFrame(() => {
                            if (searchResults.length > 0 || searchTerm.length > 0) {
                                component.selectPrevious();
                            }
                        });
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        e.stopPropagation();
                        requestAnimationFrame(() => {
                            if (searchResults.length > 0 || searchTerm.length > 0) {
                                component.addSelectedItem();
                            }
                        });
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        e.stopPropagation();
                        component.clearSearch(true);
                    }
                };
                
                // إزالة الـ listener القديم
                if (this._keydownHandler) {
                    searchInput.removeEventListener('keydown', this._keydownHandler, true);
                }
                
                // إضافة الـ listener الجديد
                searchInput.addEventListener('keydown', keydownHandler, true);
                this._keydownHandler = keydownHandler;
            },
            
            handleSearchFocus() {
                const hasSearchTerm = this.searchTerm?.length > 0;
                const hasResults = this.searchResults?.length > 0;
                
                if (hasSearchTerm || hasResults) {
                    this.showResults = true;
                    if (hasResults && this.selectedIndex < 0) {
                        this.selectedIndex = 0;
                        this.isCreateNewItemSelected = false;
                    } else if (hasSearchTerm && !hasResults) {
                        this.selectedIndex = 0;
                        this.isCreateNewItemSelected = true;
                    }
                }
            },

            /**
             * البحث عن الأصناف - مع debounce
             */
            async search() {
                if (!this.searchTerm || this.searchTerm.length < 2) {
                    this.searchResults = [];
                    this.showResults = false;
                    this.selectedIndex = -1;
                    this.isCreateNewItemSelected = false;
                    return;
                }

                this.loading = true;
                this.showResults = true;

                try {
                    // استخدام Livewire method للبحث
                    const data = await this.$wire.call('searchItems', this.searchTerm);
                    this.searchResults = Array.isArray(data) ? data : [];
                    
                    if (this.searchResults.length > 0) {
                        this.selectedIndex = 0;
                        this.isCreateNewItemSelected = false;
                    } else if (this.searchTerm.length > 0) {
                        this.selectedIndex = 0;
                        this.isCreateNewItemSelected = true;
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    this.searchResults = [];
                    this.isCreateNewItemSelected = this.searchTerm.length > 0;
                } finally {
                    this.loading = false;
                }
            },

            selectNext() {
                const totalItems = this.searchResults.length;
                
                if (totalItems === 0 && this.searchTerm?.length > 0) {
                    this.selectedIndex = 0;
                    this.isCreateNewItemSelected = true;
                    this.showResults = true;
                    return;
                }
                
                if (totalItems > 0) {
                    this.isCreateNewItemSelected = false;
                    this.showResults = true;
                    this.selectedIndex = this.selectedIndex < totalItems - 1 ? this.selectedIndex + 1 : 0;
                    this.scrollToSelected();
                }
            },

            selectPrevious() {
                const totalItems = this.searchResults.length;
                
                if (totalItems === 0 && this.searchTerm?.length > 0) {
                    this.selectedIndex = 0;
                    this.isCreateNewItemSelected = true;
                    this.showResults = true;
                    return;
                }
                
                if (totalItems > 0) {
                    this.isCreateNewItemSelected = false;
                    this.showResults = true;
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
             * إضافة صنف للفاتورة (سريع - يستخدم Livewire)
             */
            async addItemFast(item) {
                if (!item?.id) return;
                
                this.loading = true;
                
                try {
                    const result = await this.$wire.call('addItemFromSearchFast', item.id);
                    
                    if (result?.success) {
                        // تحديث الحسابات
                        window.handleCalculateRowTotal(result.index);
                        
                        // مسح البحث والتركيز على الكمية
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.clearSearch(false);
                                
                                const quantityField = document.getElementById(`quantity-${result.index}`);
                                if (quantityField) {
                                    quantityField.focus();
                                    quantityField.select();
                                }
                            }, 200);
                        });
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

            /**
             * إنشاء صنف جديد
             */
            async createNewItem() {
                if (!this.searchTerm?.trim()) return;
                
                const itemName = this.searchTerm.trim();
                this.clearSearch();
                
                try {
                    const result = await this.$wire.call('createNewItem', itemName);
                    
                    if (result?.success || result?.index !== undefined) {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                const quantityField = document.getElementById(`quantity-${result.index}`);
                                if (quantityField) {
                                    quantityField.focus();
                                    quantityField.select();
                                }
                            }, 200);
                        });
                    }
                } catch (error) {
                    console.error('Error creating item:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'فشل في إنشاء الصنف: ' + (error.message || '')
                    });
                }
            },

            clearSearch(focusSearch = false) {
                this.searchTerm = '';
                this.searchResults = [];
                this.showResults = false;
                this.selectedIndex = -1;
                this.isCreateNewItemSelected = false;
                
                this.$nextTick(() => {
                    const searchInput = document.getElementById('search-input');
                    if (searchInput) {
                        searchInput.value = '';
                        if (focusSearch) {
                            setTimeout(() => searchInput.focus(), 50);
                        }
                    }
                });
            },
            
            reinitializeSearch() {
                this.searchTerm = this.searchTerm || '';
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
            discountPercentage: parseFloat(initialData.discountPercentage) || 0,
            additionalPercentage: parseFloat(initialData.additionalPercentage) || 0,
            receivedFromClient: parseFloat(initialData.receivedFromClient) || 0,
            dimensionsUnit: initialData.dimensionsUnit || 'cm',
            enableDimensionsCalculation: initialData.enableDimensionsCalculation || false,
            invoiceType: initialData.invoiceType || 10,
            isCashAccount: initialData.isCashAccount || false,
            items: initialData.items || [],
            editableFieldsOrder: initialData.editableFieldsOrder || ['quantity', 'price', 'discount', 'sub_value'],
            currentBalance: parseFloat(initialData.currentBalance) || 0,
            calculatedBalanceAfter: parseFloat(initialData.currentBalance) || 0,
            
            // Calculated values
            subtotal: 0,
            discountValue: 0,
            additionalValue: 0,
            totalAfterAdditional: 0,
            remaining: 0,
            
            // Internal flags
            _discountValueFromPercentage: true,
            _additionalValueFromPercentage: true,
            _calculateDebounceTimer: null,
            _updateDisplaysDebounceTimer: null,

            init() {
                console.log('invoiceCalculations init', {
                    isCashAccount: this.isCashAccount,
                    totalAfterAdditional: this.totalAfterAdditional,
                    receivedFromClient: this.receivedFromClient
                });
                
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
                
                // ✅ مراقبة وحساب الرصيد
                this.setupBalanceWatchers();

                // ✅ مراقبة جميع المدخلات المؤثرة على الحسابات (Reactive Engine)
                this.$watch('items', () => this.calculateTotalsFromData(), { deep: true });
                this.$watch('discountPercentage', () => {
                    this._discountValueFromPercentage = true;
                    this.calculateFinalTotals();
                });
                this.$watch('discountValue', () => {
                    if (!this._discountValueFromPercentage) this.calculateFinalTotals();
                });
                this.$watch('additionalPercentage', () => {
                    this._additionalValueFromPercentage = true;
                    this.calculateFinalTotals();
                });
                this.$watch('additionalValue', () => {
                   if (!this._additionalValueFromPercentage) this.calculateFinalTotals();
                });
                this.$watch('receivedFromClient', () => this.calculateFinalTotals());
                this.$watch('isCashAccount', () => this.calculateFinalTotals());

                // ✅ مراقبة تغيير العميل لتصفير الخصومات والمبالغ المدفوعة
                this.$watch('acc1Id', (newVal) => {
                    if (newVal) {
                        console.log('🔄 Account Changed:', newVal);
                        this.discountPercentage = 0;
                        this.discountValue = 0;
                        this.additionalPercentage = 0;
                        this.additionalValue = 0;
                        this.receivedFromClient = 0;
                        
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
                const items = this.items || [];
                
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
                // 1. حساب قيمة الخصم
                if (this._discountValueFromPercentage) {
                    this.discountValue = parseFloat(((this.subtotal * this.discountPercentage) / 100).toFixed(2));
                } else if (this.subtotal > 0) {
                    this.discountPercentage = parseFloat(((this.discountValue / this.subtotal) * 100).toFixed(2));
                }

                const afterDiscount = parseFloat((this.subtotal - this.discountValue).toFixed(2));

                // 2. حساب القيمة الإضافية
                if (this._additionalValueFromPercentage) {
                    this.additionalValue = parseFloat(((afterDiscount * this.additionalPercentage) / 100).toFixed(2));
                } else if (afterDiscount > 0) {
                    this.additionalPercentage = parseFloat(((this.additionalValue / afterDiscount) * 100).toFixed(2));
                }
                
                // 3. الإجمالي النهائي
                this.totalAfterAdditional = parseFloat((afterDiscount + this.additionalValue).toFixed(2));
                
                // 4. الحسابات النقدية
                if (this.isCashAccount) {
                    this.receivedFromClient = this.totalAfterAdditional;
                    this.remaining = 0;
                } 
                // 5. الحسابات العادية
                else {
                    // للمحافظة على المبلغ المدفوع حتى لو أصبح الإجمالي صفراً (مثلاً عند حذف صنف)
                    this.remaining = parseFloat((this.totalAfterAdditional - this.receivedFromClient).toFixed(2));
                }
                
                // 7. تحديث الرصيد والمتجر
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

            updateReceived() {
                this.calculateFinalTotals();
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
             * التنقل بالكيبورد بين الحقول
             * يستخدم الترتيب الديناميكي من Template
             */
            moveToNextField(event) {
                if (!event?.target) return;
                
                event.preventDefault();
                event.stopPropagation();
                
                const currentField = event.target;
                const currentId = currentField.id;
                if (!currentId) return;
                
                // استخراج اسم الحقل ورقم الصف
                const parts = currentId.split('-');
                if (parts.length < 2) return;
                
                const fieldName = parts[0];
                const rowIndex = parseInt(parts[1]);
                if (isNaN(rowIndex)) return;
                
                // ✅ استخدام ترتيب الحقول الديناميكي
                const fieldOrder = this.editableFieldsOrder || ['quantity', 'price', 'discount', 'sub_value'];
                const currentFieldIndex = fieldOrder.indexOf(fieldName);
                
                // إذا كان الحقل unit، اذهب للكمية مباشرة
                if (fieldName === 'unit') {
                    const quantityField = document.getElementById(`quantity-${rowIndex}`);
                    if (quantityField && this.isElementAccessible(quantityField)) {
                        setTimeout(() => {
                            quantityField.focus();
                            quantityField.select?.();
                        }, 50);
                        return;
                    }
                }
                
                if (currentFieldIndex === -1) return;
                
                // البحث عن الحقل التالي في نفس الصف
                let nextField = null;
                for (let i = currentFieldIndex + 1; i < fieldOrder.length; i++) {
                    const nextFieldId = `${fieldOrder[i]}-${rowIndex}`;
                    nextField = document.getElementById(nextFieldId);
                    if (nextField && this.isElementAccessible(nextField)) break;
                    nextField = null;
                }
                
                // إذا لم يوجد، ابحث في الصف التالي
                if (!nextField) {
                    const nextRowIndex = rowIndex + 1;
                    for (const fname of fieldOrder) {
                        const nextFieldId = `${fname}-${nextRowIndex}`;
                        nextField = document.getElementById(nextFieldId);
                        if (nextField && this.isElementAccessible(nextField)) break;
                        nextField = null;
                    }
                }
                
                // إذا لم يوجد صف تالي، ارجع لحقل البحث
                if (!nextField) {
                    nextField = document.getElementById('search-input') || document.getElementById('barcode-search');
                }
                
                // التركيز على الحقل التالي
                if (nextField) {
                    setTimeout(() => {
                        try {
                            nextField.focus();
                            nextField.select?.();
                        } catch (e) {
                            console.error('Error focusing field:', e);
                        }
                    }, 50);
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
                    if (element.disabled || element.readOnly) return false;
                    
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
