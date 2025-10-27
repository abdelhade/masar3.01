/**
 * Employee Manager Alpine.js Component
 * 
 * يدير جميع تفاعلات صفحة إدارة الموظفين
 * يتزامن مع Livewire للحفظ والتحديث
 * 
 * @param {Object} config - إعدادات Component
 * @returns {Object} Alpine component
 */
export default (config = {}) => ({
    // ==========================================
    // State - مزامن مع Livewire
    // ==========================================
    showModal: config.showModal || false,
    showViewModal: config.showViewModal || false,
    kpiIds: config.kpiIds || [],
    kpiWeights: config.kpiWeights || {},
    selectedKpiId: config.selectedKpiId || '',
    currentImageUrl: config.currentImageUrl || null,
    isEdit: config.isEdit || false,
    
    // ==========================================
    // Local State - محلي فقط
    // ==========================================
    kpis: config.kpis || [],
    activeTab: 'personal',
    notifications: [],
    imagePreview: null,
    showPassword: false,
    
    // KPI Search
    kpiSearch: '',
    kpiSearchOpen: false,
    kpiSearchIndex: -1,
    
    // ==========================================
    // Computed Properties - محسوبة تلقائياً
    // ==========================================
    
    /**
     * مجموع أوزان KPI
     * يُحسب تلقائياً عند تغيير kpiWeights
     */
    get totalKpiWeight() {
        let total = 0;
        this.kpiIds.forEach(kpiId => {
            const weight = parseInt(this.kpiWeights[kpiId]) || 0;
            total += weight;
        });
        return total;
    },
    
    /**
     * حالة الوزن (success, warning, danger)
     */
    get weightStatus() {
        if (this.totalKpiWeight === 100) return 'success';
        if (this.totalKpiWeight > 100) return 'danger';
        return 'warning';
    },
    
    /**
     * رسالة الوزن
     */
    get weightMessage() {
        if (this.totalKpiWeight === 100) {
            return 'ممتاز! تم اكتمال النسبة بنجاح. يمكنك الآن حفظ البيانات.';
        } else if (this.totalKpiWeight > 100) {
            return `المجموع الحالي ${this.totalKpiWeight}% أكبر من 100%. يرجى تقليل الأوزان.`;
        } else {
            return `المجموع الحالي ${this.totalKpiWeight}% أقل من 100%. يرجى إكمال الأوزان.`;
        }
    },
    
    /**
     * KPIs المتاحة (غير المضافة)
     */
    get availableKpis() {
        return this.kpis.filter(kpi => !this.kpiIds.includes(kpi.id));
    },
    
    /**
     * KPIs المفلترة بناءً على البحث
     */
    get filteredKpis() {
        if (!this.kpiSearch || this.kpiSearch.trim() === '') {
            return this.availableKpis;
        }
        
        const search = this.kpiSearch.toLowerCase().trim();
        return this.availableKpis.filter(kpi => {
            const nameMatch = kpi.name.toLowerCase().includes(search);
            const descMatch = kpi.description && kpi.description.toLowerCase().includes(search);
            return nameMatch || descMatch;
        });
    },
    
    /**
     * KPIs المرئية (أول 10)
     */
    get visibleKpis() {
        return this.filteredKpis.slice(0, 10);
    },
    
    /**
     * معلومات KPI محدد
     */
    get selectedKpiInfo() {
        if (!this.selectedKpiId) return null;
        return this.kpis.find(kpi => kpi.id == this.selectedKpiId);
    },
    
    /**
     * KPIs المرتبة حسب الوزن (من الأكبر للأصغر)
     */
    get sortedKpisByWeight() {
        return [...this.kpiIds].sort((a, b) => {
            const weightA = this.kpiWeights[a] || 0;
            const weightB = this.kpiWeights[b] || 0;
            return weightB - weightA; // ترتيب تنازلي
        });
    },
    
    /**
     * عدد KPIs النشطة (بوزن > 0)
     */
    get activeKpiCount() {
        return this.kpiIds.filter(id => 
            (this.kpiWeights[id] || 0) > 0
        ).length;
    },
    
    /**
     * هل يمكن الحفظ؟
     */
    get canSave() {
        // يمكن الحفظ إذا:
        // 1. لا توجد KPIs (مسموح)
        // 2. أو المجموع = 100%
        return this.totalKpiWeight === 100 || this.kpiIds.length === 0;
    },
    
    /**
     * عدد KPIs بدون وزن
     */
    get kpisWithoutWeight() {
        return this.kpiIds.filter(id => 
            !this.kpiWeights[id] || this.kpiWeights[id] === 0
        ).length;
    },
    
    /**
     * متوسط الوزن لكل KPI
     */
    get averageWeight() {
        if (this.kpiIds.length === 0) return 0;
        return Math.round(this.totalKpiWeight / this.kpiIds.length);
    },
    
    // ==========================================
    // Lifecycle Methods
    // ==========================================
    
    /**
     * تهيئة Component
     */
    init() {
        console.log('🚀 Employee Manager initialized');
        
        // Watchers
        this.setupWatchers();
        
        // Event Listeners
        this.setupEventListeners();
        
        // Cleanup on destroy
        this.$el.addEventListener('destroy', () => this.cleanup());
    },
    
    /**
     * إعداد Watchers
     */
    setupWatchers() {
        // مراقبة showModal لإدارة body class
        this.$watch('showModal', (value) => {
            document.body.classList.toggle('modal-open', value);
            if (!value) {
                this.resetImagePreview();
            }
        });
        
        // مراقبة showViewModal
        this.$watch('showViewModal', (value) => {
            document.body.classList.toggle('modal-open', value);
        });
        
        // مراقبة kpiWeights للحساب التلقائي
        this.$watch('kpiWeights', () => {
            // totalKpiWeight سيُحدّث تلقائياً
            console.log('📊 KPI weights updated:', this.totalKpiWeight);
        });
        
        // مراقبة إضافة/حذف KPIs
        this.$watch('kpiIds', (newIds, oldIds) => {
            if (!oldIds) return; // أول تهيئة
            
            const added = newIds.filter(id => !oldIds.includes(id));
            const removed = oldIds.filter(id => !newIds.includes(id));
            
            if (added.length > 0) {
                console.log('➕ KPIs added:', added);
                // يمكن إضافة logic إضافي هنا
            }
            
            if (removed.length > 0) {
                console.log('➖ KPIs removed:', removed);
            }
        });
        
        // مراقبة تغيير التبويب
        this.$watch('activeTab', (newTab, oldTab) => {
            if (!oldTab) return; // أول تهيئة
            console.log(`📑 Tab changed: ${oldTab} → ${newTab}`);
            
            // حفظ التبويب في localStorage
            try {
                localStorage.setItem('lastActiveEmployeeTab', newTab);
            } catch (e) {
                console.warn('Failed to save tab to localStorage', e);
            }
        });
        
        // تحذير عند الوزن الخاطئ
        this.$watch('totalKpiWeight', (total) => {
            if (total > 100 && this.kpiIds.length > 0) {
                this.addNotification('warning', 'تحذير: المجموع أكبر من 100%');
            }
        });
        
        // استعادة التبويب الأخير عند الفتح
        this.$watch('showModal', (value) => {
            if (value) {
                try {
                    const lastTab = localStorage.getItem('lastActiveEmployeeTab');
                    if (lastTab) {
                        this.activeTab = lastTab;
                    }
                } catch (e) {
                    console.warn('Failed to restore tab from localStorage', e);
                }
            }
        });
    },
    
    /**
     * إعداد Event Listeners
     */
    setupEventListeners() {
        // الاستماع لإشعارات Livewire
        window.addEventListener('notify', (e) => {
            this.addNotification(e.detail.type, e.detail.message);
        });
        
        // إغلاق dropdown عند الضغط خارجه
        document.addEventListener('click', (e) => {
            if (this.kpiSearchOpen && !this.$el.contains(e.target)) {
                this.closeKpiSearch();
            }
        });
    },
    
    /**
     * تنظيف عند الإزالة
     */
    cleanup() {
        console.log('🧹 Employee Manager cleanup');
        document.body.classList.remove('modal-open');
        this.notifications = [];
    },
    
    // ==========================================
    // Modal Management
    // ==========================================
    
    /**
     * إغلاق مودال الموظف
     */
    closeEmployeeModal() {
        this.showModal = false;
        this.$wire.closeModal();
        this.resetImagePreview();
        this.activeTab = 'personal';
    },
    
    /**
     * إغلاق مودال العرض
     */
    closeViewEmployeeModal() {
        this.showViewModal = false;
        this.$wire.closeView();
    },
    
    // ==========================================
    // Tab Management
    // ==========================================
    
    /**
     * التبديل بين التبويبات
     */
    switchTab(tab) {
        this.activeTab = tab;
        console.log('📑 Switched to tab:', tab);
    },
    
    // ==========================================
    // Image Handling
    // ==========================================
    
    /**
     * معالجة تغيير الصورة
     */
    handleImageChange(event) {
        const file = event.target.files[0];
        if (!file) {
            this.resetImagePreview();
            return;
        }
        
        // التحقق من نوع الملف
        if (!file.type.startsWith('image/')) {
            this.addNotification('error', 'يرجى اختيار صورة صالحة');
            this.resetImagePreview();
            return;
        }
        
        // التحقق من حجم الملف (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            this.addNotification('error', 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت');
            this.resetImagePreview();
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.imagePreview = e.target.result;
        };
        reader.onerror = () => {
            this.addNotification('error', 'حدث خطأ أثناء قراءة الصورة');
            this.resetImagePreview();
        };
        reader.readAsDataURL(file);
    },
    
    /**
     * إعادة تعيين معاينة الصورة
     */
    resetImagePreview() {
        this.imagePreview = null;
    },
    
    // ==========================================
    // Password Toggle
    // ==========================================
    
    /**
     * إظهار/إخفاء كلمة المرور
     */
    togglePassword() {
        this.showPassword = !this.showPassword;
    },
    
    // ==========================================
    // KPI Management
    // ==========================================
    
    /**
     * الحصول على اسم KPI
     */
    getKpiName(kpiId) {
        const kpi = this.kpis.find(k => k.id == kpiId);
        return kpi ? kpi.name : '';
    },
    
    /**
     * الحصول على وصف KPI (مختصر)
     */
    getKpiDescription(kpiId) {
        const kpi = this.kpis.find(k => k.id == kpiId);
        if (!kpi || !kpi.description) return '';
        return kpi.description.length > 50 
            ? kpi.description.substring(0, 50) + '...' 
            : kpi.description;
    },
    
    /**
     * اختيار KPI من القائمة
     */
    selectKpi(kpi) {
        this.selectedKpiId = kpi.id;
        this.closeKpiSearch();
        this.kpiSearch = '';
        console.log('✅ KPI selected:', kpi.name);
    },
    
    /**
     * مسح اختيار KPI
     */
    clearKpiSelection() {
        this.selectedKpiId = '';
        this.kpiSearch = '';
        console.log('❌ KPI selection cleared');
    },
    
    /**
     * فتح/إغلاق قائمة البحث
     */
    toggleKpiSearch() {
        this.kpiSearchOpen = !this.kpiSearchOpen;
        if (this.kpiSearchOpen) {
            this.kpiSearchIndex = -1;
        }
    },
    
    /**
     * إغلاق قائمة البحث
     */
    closeKpiSearch() {
        this.kpiSearchOpen = false;
        this.kpiSearchIndex = -1;
    },
    
    /**
     * التنقل لأسفل في قائمة البحث
     */
    navigateKpiDown() {
        if (this.kpiSearchIndex < this.filteredKpis.length - 1) {
            this.kpiSearchIndex++;
            this.scrollToSelected();
        }
    },
    
    /**
     * التنقل لأعلى في قائمة البحث
     */
    navigateKpiUp() {
        if (this.kpiSearchIndex > 0) {
            this.kpiSearchIndex--;
            this.scrollToSelected();
        }
    },
    
    /**
     * اختيار العنصر المحدد حالياً
     */
    selectCurrentKpi() {
        if (this.kpiSearchIndex >= 0 && this.kpiSearchIndex < this.filteredKpis.length) {
            this.selectKpi(this.filteredKpis[this.kpiSearchIndex]);
        }
    },
    
    /**
     * التمرير إلى العنصر المحدد
     */
    scrollToSelected() {
        this.$nextTick(() => {
            const selected = this.$el.querySelector('.kpi-item.selected');
            if (selected) {
                selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    },
    
    /**
     * إضافة KPI (يستدعي Livewire method)
     */
    async addKpi() {
        if (!this.selectedKpiId) {
            this.addNotification('error', 'يرجى اختيار معدل الأداء');
            return;
        }
        
        try {
            await this.$wire.addKpi();
            // سيتم إضافة notification من Livewire
            this.clearKpiSelection();
            this.switchTab('kpi'); // البقاء في تبويب KPI
        } catch (error) {
            console.error('Error adding KPI:', error);
            this.addNotification('error', 'حدث خطأ أثناء إضافة معدل الأداء');
        }
    },
    
    /**
     * حذف KPI (يستدعي Livewire method)
     */
    async removeKpi(kpiId) {
        try {
            await this.$wire.removeKpi(kpiId);
            // سيتم إضافة notification من Livewire
        } catch (error) {
            console.error('Error removing KPI:', error);
            this.addNotification('error', 'حدث خطأ أثناء حذف معدل الأداء');
        }
    },
    
    /**
     * تحديث وزن KPI
     */
    updateKpiWeight(kpiId, value) {
        // التحقق من القيمة
        const weight = parseInt(value) || 0;
        if (weight < 0 || weight > 100) {
            this.addNotification('warning', 'الوزن يجب أن يكون بين 0 و 100');
            return;
        }
        
        this.kpiWeights[kpiId] = weight;
        console.log(`📝 KPI ${kpiId} weight updated to ${weight}%`);
    },
    
    /**
     * توزيع متساوي للأوزان على جميع KPIs
     */
    distributeWeightsEvenly() {
        const count = this.kpiIds.length;
        if (count === 0) {
            this.addNotification('warning', 'لا توجد معدلات أداء لتوزيع الأوزان عليها');
            return;
        }
        
        const weight = Math.floor(100 / count);
        const remainder = 100 - (weight * count);
        
        this.kpiIds.forEach((kpiId, index) => {
            // توزيع الباقي على أول KPIs
            this.kpiWeights[kpiId] = weight + (index < remainder ? 1 : 0);
        });
        
        this.addNotification('success', 'تم توزيع الأوزان بالتساوي');
        console.log('⚖️ Weights distributed evenly');
    },
    
    /**
     * مسح جميع الأوزان
     */
    clearAllWeights() {
        if (this.kpiIds.length === 0) {
            this.addNotification('warning', 'لا توجد معدلات أداء');
            return;
        }
        
        this.kpiIds.forEach(kpiId => {
            this.kpiWeights[kpiId] = 0;
        });
        this.addNotification('info', 'تم مسح جميع الأوزان');
        console.log('🗑️ All weights cleared');
    },
    
    /**
     * التحقق من صلاحية أوزان KPI
     */
    validateKpiWeights() {
        const errors = [];
        
        // لا توجد KPIs
        if (this.kpiIds.length === 0) {
            errors.push('لم يتم إضافة أي معدلات أداء');
        }
        
        // المجموع ليس 100%
        if (this.kpiIds.length > 0 && this.totalKpiWeight !== 100) {
            errors.push(`مجموع الأوزان ${this.totalKpiWeight}% وليس 100%`);
        }
        
        // KPIs بدون وزن
        this.kpiIds.forEach(kpiId => {
            const weight = this.kpiWeights[kpiId] || 0;
            if (weight <= 0) {
                errors.push(`${this.getKpiName(kpiId)} بدون وزن`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors
        };
    },
    
    /**
     * تعيين وزن محدد لجميع KPIs
     */
    setEqualWeight(weight) {
        if (this.kpiIds.length === 0) {
            this.addNotification('warning', 'لا توجد معدلات أداء');
            return;
        }
        
        if (weight < 0 || weight > 100) {
            this.addNotification('error', 'الوزن يجب أن يكون بين 0 و 100');
            return;
        }
        
        this.kpiIds.forEach(kpiId => {
            this.kpiWeights[kpiId] = weight;
        });
        
        this.addNotification('success', `تم تعيين وزن ${weight}% لجميع معدلات الأداء`);
    },
    
    // ==========================================
    // Notifications
    // ==========================================
    
    /**
     * إضافة إشعار
     */
    addNotification(type, message) {
        const id = Date.now() + Math.random();
        this.notifications.push({ id, type, message });
        
        console.log(`🔔 Notification [${type}]:`, message);
        
        // إزالة تلقائية بعد 3 ثوان
        setTimeout(() => {
            this.removeNotification(id);
        }, 3000);
    },
    
    /**
     * إزالة إشعار
     */
    removeNotification(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    },
    
    // ==========================================
    // Utility Methods
    // ==========================================
    
    /**
     * تنسيق التاريخ
     */
    formatDate(date) {
        if (!date) return '';
        return new Date(date).toLocaleDateString('ar-SA');
    },
    
    /**
     * تنسيق الرقم
     */
    formatNumber(number, decimals = 0) {
        if (!number) return '0';
        return Number(number).toLocaleString('ar-SA', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    },
    
    /**
     * Debug helper
     */
    debug() {
        console.group('🔍 Employee Manager State');
        console.log('Modal:', this.showModal);
        console.log('Active Tab:', this.activeTab);
        console.log('KPI IDs:', this.kpiIds);
        console.log('KPI Weights:', this.kpiWeights);
        console.log('Total Weight:', this.totalKpiWeight);
        console.log('Weight Status:', this.weightStatus);
        console.groupEnd();
    }
});

