{{-- Employee Form with Alpine.js Tabs --}}
@php
    // Get isEdit from Livewire component or default to false
    $isEdit = $isEdit ?? $this->isEdit ?? false;
    $tabs = [
            'personal' => [
                'icon' => 'fa-user',
                'label' => __('البيانات الشخصية'),
                'errors' => ['name', 'email', 'phone', 'status', 'image', 'gender', 'date_of_birth', 'nationalId', 'marital_status', 'education', 'information']
            ],
            'location' => [
                'icon' => 'fa-map-marker-alt',
                'label' => __('الموقع'),
                'errors' => ['country_id', 'city_id', 'state_id', 'town_id']
            ],
            'job' => [
                'icon' => 'fa-briefcase',
                'label' => __('الوظيفة'),
                'errors' => ['job_id', 'department_id', 'date_of_hire', 'date_of_fire', 'job_level']
            ],
            'salary' => [
                'icon' => 'fa-money-bill-wave',
                'label' => __('المرتبات'),
                'errors' => ['salary', 'salary_type']
            ],
            'attendance' => [
                'icon' => 'fa-clock',
                'label' => __('الحضور'),
                'errors' => ['finger_print_id', 'finger_print_name', 'shift_id', 'additional_hour_calculation', 'additional_day_calculation', 'late_hour_calculation', 'late_day_calculation']
            ],
            'kpi' => [
                'icon' => 'fa-chart-line',
                'label' => __('معدلات الأداء'),
                'errors' => ['kpi_ids', 'kpi_weights', 'selected_kpi_id']
            ],
            'Accounting' => [
                'icon' => 'fa-chart-line',
                'label' => __('الحسابات'),
                'errors' => ['salary_basic_account_id', 'opening_balance']
            ],
            'leaveBalances' => [
                'icon' => 'fa-calendar-check',
                'label' => __('رصيد الإجازات'),
                'errors' => ['leave_balances', 'selected_leave_type_id']
            ],
        ];
    @endphp

<div x-data="{
    activeTab: 'personal',
    isEdit: @js($isEdit ?? false),
    isRedirecting: false,
    init() {
        // Listen for employee saved event to disable all buttons and dispatch browser event
        if (window.Livewire) {
            this.$wire.on('employee-saved', () => {
                this.isRedirecting = true;
                
                // Dispatch custom browser event for other Alpine components
                window.dispatchEvent(new CustomEvent('employee-redirect-started'));
                
                // Disable all buttons immediately
                this.$nextTick(() => {
                    document.querySelectorAll('button, a.btn').forEach(btn => {
                        if (!btn.hasAttribute('data-keep-enabled')) {
                            btn.disabled = true;
                            btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                        }
                    });
                });
            });
        }
        
        // For new employee (create mode), always start with first tab
        // For edit mode, restore active tab from localStorage
        if (!this.isEdit) {
            // Clear saved tab for new employee
            localStorage.removeItem('employeeFormActiveTab');
            this.activeTab = 'personal';
        } else {
            // Restore active tab from localStorage for edit mode
            const savedTab = localStorage.getItem('employeeFormActiveTab');
            if (savedTab) {
                this.activeTab = savedTab;
            } else {
                this.activeTab = 'personal';
            }
        }
        
        // Initialize the active tab immediately after DOM is ready
        this.$nextTick(() => {
            this.switchTab(this.activeTab, false);
        });
        
        // Watch for tab changes and save to localStorage
        this.$watch('activeTab', (value) => {
            if (value && !this.isRedirecting) {
                localStorage.setItem('employeeFormActiveTab', value);
            }
        });
        
        // Re-apply tab state after Livewire updates (e.g., validation errors)
        if (window.Livewire) {
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Re-apply tab visibility after Livewire morphs the DOM
                this.$nextTick(() => {
                    this.switchTab(this.activeTab, false);
                });
            });
        }
    },
    switchTab(tabName, saveToStorage = true) {
        if (!tabName || this.isRedirecting) return;
        
        this.activeTab = tabName;
        if (saveToStorage && !this.isRedirecting) {
            localStorage.setItem('employeeFormActiveTab', tabName);
        }
        
        // Update Bootstrap tabs immediately
        // Remove active class from all tabs and hide them
        const allLinks = document.querySelectorAll('#employeeFormTabs .nav-link');
        const allPanes = document.querySelectorAll('#employeeFormTabsContent .tab-pane');
        
        allLinks.forEach(link => {
            link.classList.remove('active');
            link.setAttribute('aria-selected', 'false');
        });
        
        allPanes.forEach(pane => {
            pane.classList.remove('show', 'active');
            // Use visibility instead of display to keep Alpine.js working
            pane.style.visibility = 'hidden';
            pane.style.position = 'absolute';
            pane.style.opacity = '0';
            pane.style.pointerEvents = 'none';
            pane.style.height = '0';
            pane.style.overflow = 'hidden';
        });
        
        // Add active class to selected tab and show it
        const tabButton = document.querySelector(`#${tabName}-tab`);
        const tabPane = document.querySelector(`#${tabName}-content`);
        
        if (tabButton) {
            tabButton.classList.add('active');
            tabButton.setAttribute('aria-selected', 'true');
        }
        if (tabPane) {
            tabPane.classList.add('show', 'active');
            // Show active tab properly
            tabPane.style.visibility = 'visible';
            tabPane.style.position = 'relative';
            tabPane.style.opacity = '1';
            tabPane.style.pointerEvents = 'auto';
            tabPane.style.height = 'auto';
            tabPane.style.overflow = 'visible';
        }
        
        // Ensure Alpine reactivity updates
        this.$nextTick(() => {
            // Double-check the tab is visible and others are hidden
            allPanes.forEach(pane => {
                if (pane.id === `${tabName}-content`) {
                    pane.classList.add('show', 'active');
                    pane.style.visibility = 'visible';
                    pane.style.position = 'relative';
                    pane.style.opacity = '1';
                    pane.style.pointerEvents = 'auto';
                    pane.style.height = 'auto';
                    pane.style.overflow = 'visible';
                } else {
                    pane.classList.remove('show', 'active');
                    pane.style.visibility = 'hidden';
                    pane.style.position = 'absolute';
                    pane.style.opacity = '0';
                    pane.style.pointerEvents = 'none';
                    pane.style.height = '0';
                    pane.style.overflow = 'hidden';
                }
            });
        });
    },
    isActiveTab(tabName) {
        return this.activeTab === tabName;
    }
}" x-init="init()">
    <!-- Navigation Tabs - Bootstrap -->
    <ul class="nav nav-tabs mb-3" role="tablist" id="employeeFormTabs">
        @foreach($tabs as $tabKey => $tab)
            @php
                // Calculate error count for this tab using Livewire error bag
                $tabErrorCount = 0;
                $processedFields = [];
                
                // Get Livewire errors only
                $livewireErrors = $this->getErrorBag();
                
                if ($livewireErrors->any()) {
                    foreach ($tab['errors'] as $errorKey) {
                        // Check for direct errors
                        if ($livewireErrors->has($errorKey) && !in_array($errorKey, $processedFields)) {
                            $errorMessages = $livewireErrors->get($errorKey);
                            $tabErrorCount += is_array($errorMessages) ? count($errorMessages) : 1;
                            $processedFields[] = $errorKey;
                        }
                        
                        // Check for nested errors (e.g., leave_balances.0.max_monthly_days)
                        foreach ($livewireErrors->keys() as $errorField) {
                            if (str_starts_with($errorField, $errorKey . '.') && !in_array($errorField, $processedFields)) {
                                $errorMessages = $livewireErrors->get($errorField);
                                $tabErrorCount += is_array($errorMessages) ? count($errorMessages) : 1;
                                $processedFields[] = $errorField;
                            }
                        }
                    }
                }
            @endphp
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold @if($tabErrorCount > 0) text-danger @endif"
                        id="{{ $tabKey }}-tab"
                        type="button"
                        role="tab"
                        aria-controls="{{ $tabKey }}-content"
                        :aria-selected="isActiveTab('{{ $tabKey }}')"
                        @click.prevent="switchTab('{{ $tabKey }}')"
                        :class="{ 'active': isActiveTab('{{ $tabKey }}') }"
                        style="@if($tabErrorCount > 0) border-bottom: 2px solid #dc3545 !important; @endif">
                    <i class="fas {{ $tab['icon'] }} me-2"></i>
                    <span>{{ $tab['label'] }}</span>
                    @if($tabErrorCount > 0)
                        <span class="badge bg-danger ms-2 rounded-pill" 
                              wire:key="error-count-{{ $tabKey }}-{{ $tabErrorCount }}"
                              style="font-size: 0.7rem; font-weight: bold; padding: 0.2em 0.6em; min-width: 1.5em; display: inline-flex; align-items: center; justify-content: center;"
                              title="{{ __('عدد الأخطاء في هذا التاب: ') . $tabErrorCount }}">
                            {{ $tabErrorCount }}
                        </span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="employeeFormTabsContent" style="min-height: auto;">
        @foreach($tabs as $tabKey => $tab)
            @php
                // Convert tab key to file name
                $tabFileName = match($tabKey) {
                    'Accounting' => 'accounting',
                    'leaveBalances' => 'leave-balances',
                    default => strtolower($tabKey)
                };
                $isFirstTab = $loop->first;
            @endphp
            <div class="tab-pane fade @if($isFirstTab) show active @endif"
                 :class="{ 'show active': isActiveTab('{{ $tabKey }}') }"
                 id="{{ $tabKey }}-content"
                 role="tabpanel"
                 aria-labelledby="{{ $tabKey }}-tab"
                 tabindex="0"
                 style="min-height: auto; padding: 0; @if(!$isFirstTab) visibility: hidden; position: absolute; opacity: 0; pointer-events: none; height: 0; overflow: hidden; @else visibility: visible; position: relative; opacity: 1; pointer-events: auto; height: auto; overflow: visible; @endif">
                @include("livewire.hr-management.employees.partials.form.tabs.{$tabFileName}-tab")
            </div>
        @endforeach
    </div>
</div>
