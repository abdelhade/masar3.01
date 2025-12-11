{{-- Employee Form with Alpine.js Tabs --}}
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

    <!-- Navigation Tabs - Bootstrap -->
    <ul class="nav nav-tabs mb-3" role="tablist" id="employeeFormTabs">
        @foreach($tabs as $tabKey => $tab)
            <li class="nav-item" role="presentation" wire:key="tab-nav-{{ $tabKey }}">
                <button class="nav-link font-hold fw-bold @if($loop->first) active @endif @if($errors->hasAny($tab['errors'])) text-danger @endif"
                        id="{{ $tabKey }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $tabKey }}-content"
                        type="button"
                        role="tab"
                        aria-controls="{{ $tabKey }}-content"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    <i class="fas {{ $tab['icon'] }} me-2"></i>{{ $tab['label'] }}
                    @if($errors->hasAny($tab['errors']))
                        <span class="badge bg-danger ms-2" wire:key="error-badge-{{ $tabKey }}-{{ $errors->count() }}">{{ $errors->hasAny($tab['errors']) ? count(array_filter($tab['errors'], fn($e) => $errors->has($e))) : 0 }}</span>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="employeeFormTabsContent">
        @foreach($tabs as $tabKey => $tab)
            @php
                // Convert tab key to file name
                $tabFileName = match($tabKey) {
                    'Accounting' => 'accounting',
                    'leaveBalances' => 'leave-balances',
                    default => strtolower($tabKey)
                };
            @endphp
            <div wire:key="tab-content-{{ $tabKey }}"
                 class="tab-pane fade @if($loop->first) show active @endif"
                 id="{{ $tabKey }}-content"
                 role="tabpanel"
                 aria-labelledby="{{ $tabKey }}-tab"
                 tabindex="0">
                @include("livewire.hr-management.employees.partials.form.tabs.{$tabFileName}-tab")
            </div>
        @endforeach
    </div>
