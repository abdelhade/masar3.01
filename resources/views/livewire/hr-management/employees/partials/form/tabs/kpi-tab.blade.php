{{-- KPI Tab --}}
<div wire:ignore.self x-data="{
    // Local state (Alpine.js only)
    selectedKpiId: '',
    kpiSearch: '',
    kpiSearchOpen: false,
    kpiSearchIndex: -1,
    
    // Two-way binding with Livewire (without .live to prevent re-renders)
    kpiIds: $wire.entangle('kpi_ids'),
    kpiWeights: $wire.entangle('kpi_weights'),
    kpis: @js($kpis) || [],
    
    init() {
        // Ensure arrays are properly initialized
        if (!Array.isArray(this.kpiIds)) {
            this.kpiIds = [];
        }
        if (!this.kpiWeights || typeof this.kpiWeights !== 'object') {
            this.kpiWeights = {};
        }
    },
    
    // Computed properties
    get availableKpis() {
        if (!Array.isArray(this.kpiIds) || !Array.isArray(this.kpis)) {
            return this.kpis || [];
        }
        return this.kpis.filter(kpi => !this.kpiIds.includes(kpi.id));
    },
    
    get filteredKpis() {
        if (!this.kpiSearch) return this.availableKpis;
        const search = this.kpiSearch.toLowerCase();
        return this.availableKpis.filter(kpi =>
            kpi.name.toLowerCase().includes(search) ||
            (kpi.description && kpi.description.toLowerCase().includes(search))
        );
    },
    
    get totalKpiWeight() {
        if (!Array.isArray(this.kpiIds) || !this.kpiWeights) {
            return 0;
        }
        let total = 0;
        this.kpiIds.forEach(kpiId => {
            total += parseInt(this.kpiWeights[kpiId]) || 0;
        });
        return total;
    },
    
    get weightMessage() {
        if (this.totalKpiWeight === 100) {
            return 'ممتاز! تم اكتمال النسبة بنجاح. يمكنك الآن حفظ البيانات.';
        } else if (this.totalKpiWeight > 100) {
            return `المجموع الحالي ${this.totalKpiWeight}% أكبر من 100%. يرجى تقليل الأوزان.`;
        } else {
            return `المجموع الحالي ${this.totalKpiWeight}% أقل من 100%. يرجى إكمال الأوزان.`;
        }
    },
    
    // Methods
    getKpiName(kpiId) {
        const kpi = this.kpis.find(k => k.id == kpiId);
        return kpi ? kpi.name : '';
    },
    
    getKpiDescription(kpiId) {
        const kpi = this.kpis.find(k => k.id == kpiId);
        return kpi && kpi.description ? kpi.description.substring(0, 50) + '...' : '';
    },
    
    selectKpi(kpi) {
        this.selectedKpiId = kpi.id;
        this.kpiSearchOpen = false;
        this.kpiSearch = '';
        this.kpiSearchIndex = -1;
    },
    
    clearKpiSelection() {
        this.selectedKpiId = '';
        this.kpiSearch = '';
        this.kpiSearchOpen = false;
        this.kpiSearchIndex = -1;
    },
    
    toggleDropdown() {
        this.kpiSearchOpen = !this.kpiSearchOpen;
        if (this.kpiSearchOpen) {
            this.$nextTick(() => {
                this.kpiSearchIndex = -1;
            });
        }
    },
    
    openDropdown() {
        if (!this.kpiSearchOpen) {
            this.kpiSearchOpen = true;
        }
    },
    
    navigateKpiDown() {
        if (this.kpiSearchIndex < this.filteredKpis.length - 1) {
            this.kpiSearchIndex++;
        }
    },
    
    navigateKpiUp() {
        if (this.kpiSearchIndex > 0) {
            this.kpiSearchIndex--;
        }
    },
    
    selectCurrentKpi() {
        if (this.kpiSearchIndex >= 0 && this.kpiSearchIndex < this.filteredKpis.length) {
            this.selectKpi(this.filteredKpis[this.kpiSearchIndex]);
        }
    },
    
    addKpi() {
        if (!this.selectedKpiId) {
            return;
        }
        
        // Check if KPI already exists
        const ids = Array.isArray(this.kpiIds) ? this.kpiIds : [];
        if (ids.includes(this.selectedKpiId)) {
            return;
        }
        
        // Add KPI - entangle syncs automatically with Livewire
        this.kpiIds = [...ids, this.selectedKpiId];
        this.kpiWeights = {...(this.kpiWeights || {}), [this.selectedKpiId]: 0};
        
        // Clear selection
        this.clearKpiSelection();
    },
    
    updateKpiWeight(kpiId, value) {
        // Update weight - entangle syncs automatically
        this.kpiWeights = {...(this.kpiWeights || {}), [kpiId]: parseInt(value) || 0};
    },
    
    removeKpi(kpiId) {
        if (confirm('{{ __('هل أنت متأكد من حذف هذا المعدل؟') }}')) {
            // Remove KPI - entangle syncs automatically
            const ids = Array.isArray(this.kpiIds) ? this.kpiIds : [];
            this.kpiIds = ids.filter(id => id != kpiId);
            
            const weights = {...(this.kpiWeights || {})};
            delete weights[kpiId];
            this.kpiWeights = weights;
        }
    }
}">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient-primary text-white py-2">
            <h6 class="card-title mb-0 font-hold fw-bold">
                <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء للموظف') }}
            </h6>
        </div>
        <div class="card-body py-3">
            <!-- إضافة معدل أداء جديد -->
            <div class="card border-primary mb-3">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold text-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('إضافة معدل أداء جديد') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-dark">{{ __('اختر معدل الأداء') }}
                                <span class="text-danger">*</span></label>
                            <div class="position-relative kpi-dropdown-container">
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                        :value="selectedKpiId ? getKpiName(selectedKpiId) : kpiSearch"
                                        @input="kpiSearch = $event.target.value; if (selectedKpiId) { clearKpiSelection(); } openDropdown()"
                                        @focus="openDropdown()"
                                        @click="openDropdown()"
                                        @keydown.escape="kpiSearchOpen = false"
                                        @keydown.arrow-down.prevent="navigateKpiDown()"
                                        @keydown.arrow-up.prevent="navigateKpiUp()"
                                        @keydown.enter.prevent="selectCurrentKpi()"
                                        :placeholder="selectedKpiId ? '' : '{{ __('ابحث عن معدل الأداء...') }}'"
                                        autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button"
                                        @click="toggleDropdown()">
                                        <i class="fas" :class="kpiSearchOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" type="button"
                                        x-show="selectedKpiId"
                                        @click="clearKpiSelection()"
                                        title="{{ __('مسح الاختيار') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Dropdown Results -->
                                <div x-show="kpiSearchOpen && filteredKpis.length > 0"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 employee-dropdown"
                                    style="z-index: 999999 !important; max-height: 250px; overflow-y: auto; top: 100%; right: 0;"
                                    @click.away="kpiSearchOpen = false"
                                    x-cloak>
                                    <template x-for="(kpi, index) in filteredKpis" :key="kpi.id">
                                        <div class="p-2 border-bottom cursor-pointer"
                                            @click="selectKpi(kpi)"
                                            :class="kpiSearchIndex === index ? 'bg-primary text-white' : 'hover-bg-light'"
                                            @mouseenter="kpiSearchIndex = index">
                                            <div class="fw-bold" x-text="kpi.name"></div>
                                            <small x-text="kpi.description" x-show="kpi.description"></small>
                                        </div>
                                    </template>
                                </div>

                                <!-- No Results -->
                                <div x-show="kpiSearchOpen && kpiSearch && filteredKpis.length === 0"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 p-3 text-center text-muted employee-dropdown"
                                    style="z-index: 999999 !important; top: 100%; right: 0;"
                                    @click.away="kpiSearchOpen = false"
                                    x-cloak>
                                    <i class="fas fa-search me-2"></i>{{ __('لا توجد نتائج') }}
                                </div>
                            </div>
                            @error('selected_kpi_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-main btn-lg w-100"
                                @click="addKpi()"
                                :disabled="!selectedKpiId"
                                :class="{ 'opacity-50 cursor-not-allowed': !selectedKpiId }">
                                <i class="fas fa-plus me-2"></i>{{ __('إضافة') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معدلات الأداء المضافة -->
            <template x-if="kpiIds && Array.isArray(kpiIds) && kpiIds.length > 0">
                <div>
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="fas fa-list me-2"></i>{{ __('معدلات الأداء المضافة') }}
                    </h6>
                    <div class="row g-3 mb-3">
                        <template x-for="kpiId in kpiIds" :key="kpiId">
                            <div class="col-md-4 col-sm-6">
                                <div class="card border-success h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="card-title fw-bold text-success mb-1"
                                                    x-text="getKpiName(kpiId)"></h6>
                                                <small class="text-muted" x-text="getKpiDescription(kpiId)"></small>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                @click="removeKpi(kpiId)"
                                                :disabled="$root.isRedirecting"
                                                :class="{ 'opacity-50 cursor-not-allowed': $root.isRedirecting }"
                                                title="{{ __('حذف') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label fw-bold text-dark small">{{ __('الوزن النسبي') }}</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" 
                                                    :value="kpiWeights[kpiId] || 0"
                                                    @input="updateKpiWeight(kpiId, $event.target.value)"
                                                    @keydown.enter.prevent
                                                    min="0" max="100" step="1">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- مؤشر المجموع -->
                    <div class="alert py-2"
                        :class="{
                            'alert-success': totalKpiWeight === 100,
                            'alert-danger': totalKpiWeight > 100,
                            'alert-warning': totalKpiWeight < 100
                        }">
                        <i class="fas me-2"
                            :class="{
                                'fa-check-circle': totalKpiWeight === 100,
                                'fa-times-circle': totalKpiWeight > 100,
                                'fa-exclamation-triangle': totalKpiWeight < 100
                            }"></i>
                        <span x-text="weightMessage"></span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="card shadow-sm"
                        :class="{
                            'border-success': totalKpiWeight === 100,
                            'border-danger': totalKpiWeight > 100,
                            'border-warning': totalKpiWeight < 100
                        }">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="card-title fw-bold mb-0"
                                    :class="{
                                        'text-success': totalKpiWeight === 100,
                                        'text-danger': totalKpiWeight > 100,
                                        'text-warning': totalKpiWeight < 100
                                    }">
                                    <i class="fas fa-calculator me-2"></i>{{ __('المجموع الحالي للأوزان') }}
                                </h6>
                                <span class="badge text-white"
                                    :class="{
                                        'bg-success': totalKpiWeight === 100,
                                        'bg-danger': totalKpiWeight > 100,
                                        'bg-warning': totalKpiWeight < 100
                                    }"
                                    x-text="totalKpiWeight + '%'"></span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar"
                                    :style="'width: ' + Math.min(totalKpiWeight, 100) + '%'"
                                    :class="{
                                        'bg-success': totalKpiWeight === 100,
                                        'bg-danger': totalKpiWeight > 100,
                                        'bg-warning': totalKpiWeight < 100
                                    }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- رسالة عند عدم وجود KPIs -->
            <template x-if="!kpiIds || !Array.isArray(kpiIds) || kpiIds.length === 0">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('لم يتم إضافة أي معدلات أداء بعد. استخدم النموذج أعلاه لإضافة معدلات الأداء.') }}
                </div>
            </template>
        </div>
    </div>
</div>
