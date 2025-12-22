// Alpine.js Manufacturing Calculator Component
// Support both alpine:init event and direct initialization
function initManufacturingCalculator() {
    if (typeof Alpine === 'undefined') {
        // Alpine not loaded yet, wait for it
        setTimeout(initManufacturingCalculator, 100);
        return;
    }

    Alpine.data('manufacturingCalculator', () => ({
        products: [],
        rawMaterials: [],
        expenses: [],
        
        // Computed totals
        get totalRawMaterialsCost() {
            return this.rawMaterials.reduce((sum, item) => 
                sum + (parseFloat(item.total_cost) || 0), 0
            );
        },
        
        get totalProductsCost() {
            return this.products.reduce((sum, item) => 
                sum + (parseFloat(item.total_cost) || 0), 0
            );
        },
        
        get totalExpenses() {
            return this.expenses.reduce((sum, item) => 
                sum + (parseFloat(item.amount) || 0), 0
            );
        },
        
        get totalManufacturingCost() {
            return this.totalRawMaterialsCost + this.totalExpenses;
        },
        
        get unitCostPerProduct() {
            const totalQty = this.products.reduce((sum, p) => 
                sum + (parseFloat(p.quantity) || 0), 0
            );
            return totalQty > 0 ? this.totalManufacturingCost / totalQty : 0;
        },
        
        // Initialize from Livewire data
        initFromLivewire() {
            // تحميل البيانات من Livewire
            this.products = this.$wire.selectedProducts || [];
            this.rawMaterials = this.$wire.selectedRawMaterials || [];
            this.expenses = this.$wire.additionalExpenses || [];
            
            // توزيع النسب تلقائياً عند التحميل
            if (this.products.length > 0) {
                this.distributePercentagesEqually();
            }
            
            // مراقبة تغييرات Livewire
            this.$wire.$watch('selectedProducts', (value) => {
                this.products = value || [];
            });
            
            this.$wire.$watch('selectedRawMaterials', (value) => {
                this.rawMaterials = value || [];
            });
            
            this.$wire.$watch('additionalExpenses', (value) => {
                this.expenses = value || [];
            });
            
            // مراقبة التغييرات
            this.$watch('products', (newProducts, oldProducts) => {
                // توزيع تلقائي عند إضافة منتجات جديدة فقط إذا لم تكن النسب محددة
                if (newProducts.length > (oldProducts?.length || 0)) {
                    const hasPercentages = newProducts.some(p => (parseFloat(p.cost_percentage) || 0) > 0);
                    if (!hasPercentages) {
                        this.distributePercentagesEqually();
                    }
                }
                this.syncToLivewire();
            });
            this.$watch('rawMaterials', () => this.syncToLivewire());
            this.$watch('expenses', () => this.syncToLivewire());
        },
        
        // Product methods
        updateProductTotal(index) {
            const product = this.products[index];
            if (!product) return;
            
            const qty = parseFloat(product.quantity) || 0;
            const cost = parseFloat(product.average_cost) || 0;
            product.total_cost = parseFloat((qty * cost).toFixed(2));
            
            this.syncToLivewire();
        },
        
        updateProductPercentages() {
            const count = this.products.length;
            if (count === 0) return;
            
            const percentage = parseFloat((100 / count).toFixed(2));
            this.products.forEach(p => p.cost_percentage = percentage);
        },
        
        // Raw material methods
        updateRawMaterialTotal(index) {
            const material = this.rawMaterials[index];
            if (!material) return;
            
            const qty = parseFloat(material.quantity) || 0;
            const cost = parseFloat(material.average_cost) || 0;
            material.total_cost = parseFloat((qty * cost).toFixed(2));
            
            this.syncToLivewire();
        },
        
        updateRawMaterialUnit(index) {
            const material = this.rawMaterials[index];
            if (!material || !material.unit_id) return;
            
            const unit = material.unitsList?.find(u => u.id == material.unit_id);
            if (!unit) return;
            
            material.unit_cost = parseFloat(unit.cost || 0);
            material.available_quantity = unit.available_qty || 0;
            
            const baseCost = parseFloat(material.base_cost) || 0;
            const conversionFactor = unit.available_qty || 1;
            material.average_cost = parseFloat((baseCost * conversionFactor).toFixed(2));
            
            this.updateRawMaterialTotal(index);
        },
        
        // Update all totals (client-side only, faster)
        updateTotals() {
            this.syncToLivewire();
        },
        
        // Cost distribution
        distributeCostsByPercentage() {
            if (this.products.length === 0) {
                this.showAlert('خطأ!', 'لا توجد منتجات لتوزيع التكلفة عليها', 'error');
                return;
            }
            
            const totalCost = this.totalManufacturingCost;
            
            this.products.forEach((product, index) => {
                const percentage = parseFloat(product.cost_percentage) || 0;
                const quantity = parseFloat(product.quantity) || 1;
                
                if (percentage > 0 && quantity > 0) {
                    // حساب التكلفة المخصصة لهذا المنتج
                    const allocatedCost = (totalCost * percentage) / 100;
                    
                    // حساب تكلفة الوحدة
                    const unitCost = allocatedCost / quantity;
                    
                    product.average_cost = parseFloat(unitCost.toFixed(2));
                    product.unit_cost = parseFloat(unitCost.toFixed(2));
                    product.total_cost = parseFloat(allocatedCost.toFixed(2));
                }
            });
            
            this.syncToLivewire();
            this.showAlert('تم!', 'تم توزيع التكاليف بنجاح', 'success');
        },
        
        // توزيع النسب بالتساوي - فقط إذا لم تكن محددة
        distributePercentagesEqually() {
            const count = this.products.length;
            if (count === 0) return;
            
            // تحقق من وجود نسب محددة مسبقاً
            const hasExistingPercentages = this.products.some(p => (parseFloat(p.cost_percentage) || 0) > 0);
            if (hasExistingPercentages) return;
            
            const percentage = parseFloat((100 / count).toFixed(2));
            const remainder = parseFloat((100 - (percentage * count)).toFixed(2));
            
            this.products.forEach((product, index) => {
                product.cost_percentage = percentage;
                // إضافة الباقي للمنتج الأول
                if (index === 0 && remainder !== 0) {
                    product.cost_percentage = parseFloat((percentage + remainder).toFixed(2));
                }
            });
        },
        
        // Quantity multiplier
        applyQuantityMultiplier(multiplier) {
            const mult = parseFloat(multiplier);
            if (!mult || mult <= 0) {
                this.showAlert('خطأ!', 'المضاعف يجب أن يكون أكبر من صفر', 'error');
                return;
            }
            
            this.rawMaterials.forEach((material, index) => {
                material.quantity = parseFloat((parseFloat(material.quantity) * mult).toFixed(2));
                this.updateRawMaterialTotal(index);
            });
            
            this.products.forEach((product, index) => {
                product.quantity = parseFloat((parseFloat(product.quantity) * mult).toFixed(2));
                this.updateProductTotal(index);
            });
            
            this.updateTotals();
            this.showAlert('تم!', 'تم مضاعفة الكميات بنجاح', 'success');
        },
        
        // Format currency helper
        formatCurrency(amount) {
            const num = parseFloat(amount) || 0;
            return new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num) + ' ج.م';
        },
        
        // Sync to Livewire (less frequent, optimized)
        syncToLivewire: Alpine.debounce(function() {
            if (this.$wire) {
                // فقط عند الحاجة للحفظ أو التحديثات المهمة
                this.$wire.call('syncFromAlpine', 
                    this.products, 
                    this.rawMaterials, 
                    this.expenses,
                    {
                        totalRawMaterialsCost: this.totalRawMaterialsCost,
                        totalProductsCost: this.totalProductsCost,
                        totalExpenses: this.totalExpenses,
                        totalManufacturingCost: this.totalManufacturingCost
                    }
                );
            }
        }, 100),
        
        // Sync immediately for save operations
        syncForSave() {
            if (this.$wire) {
                this.$wire.call('syncFromAlpine', 
                    this.products, 
                    this.rawMaterials, 
                    this.expenses,
                    {
                        totalRawMaterialsCost: this.totalRawMaterialsCost,
                        totalProductsCost: this.totalProductsCost,
                        totalExpenses: this.totalExpenses,
                        totalManufacturingCost: this.totalManufacturingCost
                    }
                );
            }
        },
        
        // Alert helper
        showAlert(title, text, icon) {
            if (window.Swal) {
                Swal.fire({ title, text, icon });
            } else if (this.$wire) {
                this.$wire.dispatch('show-alert', { title, text, icon });
            }
        }
    }));

    // Product Search Component
    Alpine.data('productSearch', () => ({
    searchTerm: '',
    results: [],
    selectedIndex: -1,
    isLoading: false,
    showNoResults: false,
    
    init() {
        // Initialize with empty state
        this.results = [];
        this.selectedIndex = -1;
        this.showNoResults = false;
        this.isLoading = false;
        
        // Watch for changes (only triggers on actual changes, not initial value)
        this.$watch('searchTerm', (value, oldValue) => {
            // Skip initialization - only trigger on actual changes
            if (oldValue === undefined) {
                return;
            }
            
            // Skip if value hasn't actually changed
            if (value === oldValue) {
                return;
            }
            
            // Skip if value is empty or too short
            if (!value || value.trim().length < 2) {
                this.results = [];
                this.selectedIndex = -1;
                this.showNoResults = false;
                this.isLoading = false;
                return;
            }
            
            // Only search if user actually typed something (value changed and is valid)
            if (value.trim().length >= 2) {
                this.debouncedSearch(value);
            }
        });
    },
    
    debouncedSearch: Alpine.debounce(function(term) {
        this.performSearch(term);
    }, 300),
    
    async performSearch(term) {
        const trimmedTerm = term.trim();
        if (trimmedTerm.length < 2) {
            this.results = [];
            this.selectedIndex = -1;
            this.showNoResults = false;
            this.isLoading = false;
            return;
        }
        
        // Check if $wire is available
        if (!this.$wire) {
            console.warn('Livewire wire not available yet');
            this.isLoading = false;
            return;
        }
        
        this.isLoading = true;
        this.showNoResults = false;
        try {
            const response = await this.$wire.call('searchProducts', trimmedTerm);
            this.results = response || [];
            this.selectedIndex = -1;
            this.showNoResults = this.results.length === 0 && trimmedTerm.length >= 2;
        } catch (error) {
            console.error('Search error:', error);
            this.results = [];
            this.showNoResults = trimmedTerm.length >= 2; // Only show if term is valid
        } finally {
            this.isLoading = false;
        }
    },
    
    handleKeyDown() {
        if (this.results.length > 0) {
            this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
        }
    },
    
    handleKeyUp() {
        if (this.results.length > 0) {
            this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
        }
    },
    
    handleEnter() {
        if (this.results.length > 0 && this.selectedIndex >= 0) {
            const item = this.results[this.selectedIndex];
            this.selectItem(item);
        }
    },
    
    selectItem(item) {
        if (!this.$wire) {
            console.warn('Livewire wire not available');
            return;
        }
        this.$wire.call('addProductFromSearch', item.id);
        this.searchTerm = '';
        this.results = [];
        this.selectedIndex = -1;
    }
    }));

    // Raw Material Search Component
    Alpine.data('rawMaterialSearch', () => ({
    searchTerm: '',
    results: [],
    selectedIndex: -1,
    isLoading: false,
    showNoResults: false,
    
    init() {
        // Initialize with empty state
        this.results = [];
        this.selectedIndex = -1;
        this.showNoResults = false;
        this.isLoading = false;
        
        // Watch for changes (only triggers on actual changes, not initial value)
        this.$watch('searchTerm', (value, oldValue) => {
            // Skip initialization - only trigger on actual changes
            if (oldValue === undefined) {
                return;
            }
            
            // Skip if value hasn't actually changed
            if (value === oldValue) {
                return;
            }
            
            // Skip if value is empty or too short
            if (!value || value.trim().length < 2) {
                this.results = [];
                this.selectedIndex = -1;
                this.showNoResults = false;
                this.isLoading = false;
                return;
            }
            
            // Only search if user actually typed something (value changed and is valid)
            if (value.trim().length >= 2) {
                this.debouncedSearch(value);
            }
        });
    },
    
    debouncedSearch: Alpine.debounce(function(term) {
        this.performSearch(term);
    }, 300),
    
    async performSearch(term) {
        const trimmedTerm = term.trim();
        if (trimmedTerm.length < 2) {
            this.results = [];
            this.selectedIndex = -1;
            this.showNoResults = false;
            this.isLoading = false;
            return;
        }
        
        // Check if $wire is available
        if (!this.$wire) {
            console.warn('Livewire wire not available yet');
            this.isLoading = false;
            return;
        }
        
        this.isLoading = true;
        this.showNoResults = false;
        try {
            const response = await this.$wire.call('searchRawMaterials', trimmedTerm);
            this.results = response || [];
            this.selectedIndex = -1;
            this.showNoResults = this.results.length === 0 && trimmedTerm.length >= 2;
        } catch (error) {
            console.error('Search error:', error);
            this.results = [];
            this.showNoResults = trimmedTerm.length >= 2; // Only show if term is valid
        } finally {
            this.isLoading = false;
        }
    },
    
    handleKeyDown() {
        if (this.results.length > 0) {
            this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
        }
    },
    
    handleKeyUp() {
        if (this.results.length > 0) {
            this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
        }
    },
    
    handleEnter() {
        if (this.results.length > 0 && this.selectedIndex >= 0) {
            const item = this.results[this.selectedIndex];
            this.selectItem(item);
        }
    },
    
    selectItem(item) {
        if (!this.$wire) {
            console.warn('Livewire wire not available');
            return;
        }
        this.$wire.call('addRawMaterialFromSearch', item.id);
        this.searchTerm = '';
        this.results = [];
        this.selectedIndex = -1;
    }
    }));
}

// Initialize when Alpine is ready
let initialized = false;

function tryInit() {
    if (initialized) return;
    
    if (typeof Alpine !== 'undefined' && typeof Alpine.data === 'function') {
        initManufacturingCalculator();
        initialized = true;
    }
}

// Try immediate initialization if Alpine is already loaded
tryInit();

// Listen for alpine:init event
document.addEventListener('alpine:init', function() {
    tryInit();
});

// Fallback: try initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(tryInit, 100);
    });
} else {
    setTimeout(tryInit, 100);
}

