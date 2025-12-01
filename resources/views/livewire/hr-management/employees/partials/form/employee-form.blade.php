{{-- Employee Form with Alpine.js Tabs --}}
<div class="container-fluid" style="direction: rtl;">
    @php
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

    <!-- Navigation Tabs - Alpine.js -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        @foreach($tabs as $tabKey => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold @if($errors->hasAny($tab['errors'])) text-danger @endif"
                        :class="{ 'active': activeTab === '{{ $tabKey }}' }"
                        @click="switchTab('{{ $tabKey }}')"
                        type="button">
                    <i class="fas {{ $tab['icon'] }} me-2"></i>{{ $tab['label'] }}
                    @if($errors->hasAny($tab['errors']))
                        <span class="badge bg-danger ms-2">{{ $errors->hasAny($tab['errors']) ? count(array_filter($tab['errors'], fn($e) => $errors->has($e))) : 0 }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <!-- Tab Content with Lazy Loading -->
    <div class="tab-content">
        @foreach($tabs as $tabKey => $tab)
            @php
                // Convert tab key to file name
                $tabFileName = match($tabKey) {
                    'Accounting' => 'accounting',
                    'leaveBalances' => 'leave-balances',
                    default => strtolower($tabKey)
                };
            @endphp
            <div x-show="activeTab === '{{ $tabKey }}'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                @include("livewire.hr-management.employees.partials.form.tabs.{$tabFileName}-tab")
            </div>
        @endforeach
    </div>
</div>
