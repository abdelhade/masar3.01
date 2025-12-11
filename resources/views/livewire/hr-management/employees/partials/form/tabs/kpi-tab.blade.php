{{-- KPI Tab --}}
<div>
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
                                        @input="kpiSearch = $event.target.value; selectedKpiId = ''; kpiSearchOpen = true"
                                        @click="kpiSearchOpen = true"
                                        @keydown.escape="kpiSearchOpen = false"
                                        @keydown.arrow-down.prevent="navigateKpiDown()"
                                        @keydown.arrow-up.prevent="navigateKpiUp()"
                                        @keydown.enter.prevent="selectCurrentKpi()"
                                        :placeholder="selectedKpiId ? '' : '{{ __('ابحث عن معدل الأداء...') }}'"
                                        autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button"
                                        @click="kpiSearchOpen = !kpiSearchOpen">
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
                                    class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 employee-dropdown"
                                    style="z-index: 999999 !important; max-height: 250px; overflow-y: auto; top: 100%; right: 0;"
                                    @click.away="kpiSearchOpen = false"
                                    x-cloak>
                                    <template x-for="(kpi, index) in filteredKpis" :key="kpi.id">
                                        <div class="p-2 border-bottom cursor-pointer"
                                            @click="selectKpi(kpi); kpiSearchOpen = false"
                                            :class="kpiSearchIndex === index ? 'bg-primary text-white' : 'hover-bg-light'">
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
                                @click="$wire.addKpi()" wire:loading.attr="disabled" 
                                :disabled="!selectedKpiId">
                                <span wire:loading.remove wire:target="addKpi">
                                    <i class="fas fa-plus me-2"></i>{{ __('إضافة') }}
                                </span>
                                <span wire:loading wire:target="addKpi">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('جاري الإضافة...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معدلات الأداء المضافة -->
            <template x-if="kpiIds.length > 0">
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
                                                @click="$wire.removeKpi(kpiId)" title="{{ __('حذف') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label fw-bold text-dark small">{{ __('الوزن النسبي') }}</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" :value="kpiWeights[kpiId] || 0"
                                                    @input="kpiWeights[kpiId] = parseInt($event.target.value) || 0"
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
            <template x-if="kpiIds.length === 0">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('لم يتم إضافة أي معدلات أداء بعد. استخدم النموذج أعلاه لإضافة معدلات الأداء.') }}
                </div>
            </template>
        </div>
    </div>
</div>
