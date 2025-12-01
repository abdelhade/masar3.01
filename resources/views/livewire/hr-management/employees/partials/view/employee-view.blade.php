{{-- Employee View Partial with Tabs --}}
<div class="container-fluid employee-view-container" style="direction: rtl;">
    @php
        $viewEmployee = $this->employee ?? $this->viewEmployee ?? null;
    @endphp
    @if ($viewEmployee)
        {{-- Header Section with Employee Name and Actions --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="d-flex align-items-center">
                                <div class="employee-avatar me-3">
                                    @php
                                        $employeeImage = $viewEmployee->image_url;
                                        $hasImage = $viewEmployee->hasMedia('employee_images') && $employeeImage;
                                    @endphp
                                    @if ($hasImage)
                                        <img src="{{ $employeeImage }}"
                                             alt="{{ e($viewEmployee->name) }}"
                                             class="rounded-circle border-3 border-white shadow-lg"
                                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                             loading="lazy"
                                             @click="previewImageUrl = '{{ $employeeImage }}'; isLightboxVisible = true"
                                             onerror="this.src='{{ asset('assets/images/avatar-placeholder.svg') }}'">
                                    @else
                                        <img src="{{ asset('assets/images/avatar-placeholder.svg') }}"
                                             alt="{{ e($viewEmployee->name) }}"
                                             class="rounded-circle border-3 border-white shadow-lg"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    @endif
                                </div>
                                <div>
                                    <h4 class="mb-1 fw-bold font-hold">
                                        <i class="fas fa-user me-2"></i>{{ e($viewEmployee->name) }}
                                    </h4>
                                    <p class="mb-0 opacity-75">
                                        <i class="fas fa-briefcase me-2"></i>{{ $viewEmployee->job?->title ?? __('غير محدد') }}
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-building me-2"></i>{{ $viewEmployee->department?->title ?? __('غير محدد') }}
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'personal' }"
                        @click="switchViewTab('personal')"
                        type="button">
                    <i class="fas fa-user me-2"></i>{{ __('البيانات الشخصية') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'location' }"
                        @click="switchViewTab('location')"
                        type="button">
                    <i class="fas fa-map-marker-alt me-2"></i>{{ __('الموقع') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'job' }"
                        @click="switchViewTab('job')"
                        type="button">
                    <i class="fas fa-briefcase me-2"></i>{{ __('الوظيفة') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'attendance' }"
                        @click="switchViewTab('attendance')"
                        type="button">
                    <i class="fas fa-clock me-2"></i>{{ __('الحضور') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'accounting' }"
                        @click="switchViewTab('accounting')"
                        type="button">
                    <i class="fas fa-calculator me-2"></i>{{ __('الحسابات') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'kpi' }"
                        @click="switchViewTab('kpi')"
                        type="button">
                    <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        :class="{ 'active': activeViewTab === 'leaveBalances' }"
                        @click="switchViewTab('leaveBalances')"
                        type="button">
                    <i class="fas fa-calendar-check me-2"></i>{{ __('رصيد الإجازات') }}
                </button>
            </li>
        </ul>

        {{-- Tab Content with Lazy Loading --}}
        <div class="tab-content">
            {{-- Personal Tab (always loaded) --}}
            <div x-show="activeViewTab === 'personal'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                @include('livewire.hr-management.employees.partials.view.tabs.personal-tab')
            </div>

            {{-- Location Tab (lazy loaded) --}}
            <div x-show="activeViewTab === 'location'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-data="{ loaded: loadedTabs.has('location') }"
                 x-init="if (!loaded) { loadedTabs.add('location'); loaded = true; }">
                <template x-if="loaded">
                    <div>
                        @php
                            $locationEmployee = method_exists($this, 'employeeWithLocation') ? $this->employeeWithLocation : $viewEmployee;
                        @endphp
                        @include('livewire.hr-management.employees.partials.view.tabs.location-tab', ['viewEmployee' => $locationEmployee ?? $viewEmployee])
                    </div>
                </template>
            </div>

            {{-- Job Tab (always loaded - uses basic employee data) --}}
            <div x-show="activeViewTab === 'job'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                @include('livewire.hr-management.employees.partials.view.tabs.job-tab')
            </div>

            {{-- Attendance Tab (always loaded - uses basic employee data) --}}
            <div x-show="activeViewTab === 'attendance'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                @include('livewire.hr-management.employees.partials.view.tabs.attendance-tab')
            </div>

            {{-- Accounting Tab (lazy loaded) --}}
            <div x-show="activeViewTab === 'accounting'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-data="{ loaded: loadedTabs.has('accounting') }"
                 x-init="if (!loaded) { loadedTabs.add('accounting'); loaded = true; }">
                <template x-if="loaded">
                    <div>
                        @php
                            $accountEmployee = method_exists($this, 'employeeWithAccount') ? $this->employeeWithAccount : $viewEmployee;
                        @endphp
                        @include('livewire.hr-management.employees.partials.view.tabs.accounting-tab', ['viewEmployee' => $accountEmployee ?? $viewEmployee])
                    </div>
                </template>
            </div>

            {{-- KPI Tab (lazy loaded) --}}
            <div x-show="activeViewTab === 'kpi'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-data="{ loaded: loadedTabs.has('kpi') }"
                 x-init="if (!loaded) { loadedTabs.add('kpi'); loaded = true; }">
                <template x-if="loaded">
                    <div>
                        @php
                            $kpiEmployee = method_exists($this, 'employeeWithKpis') ? $this->employeeWithKpis : $viewEmployee;
                        @endphp
                        @include('livewire.hr-management.employees.partials.view.tabs.kpi-tab', ['viewEmployee' => $kpiEmployee ?? $viewEmployee])
                    </div>
                </template>
            </div>

            {{-- Leave Balances Tab (lazy loaded) --}}
            <div x-show="activeViewTab === 'leaveBalances'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-data="{ loaded: loadedTabs.has('leaveBalances') }"
                 x-init="if (!loaded) { loadedTabs.add('leaveBalances'); loaded = true; }">
                <template x-if="loaded">
                    <div>
                        @php
                            $leaveEmployee = method_exists($this, 'employeeWithLeaveBalances') ? $this->employeeWithLeaveBalances : $viewEmployee;
                        @endphp
                        @include('livewire.hr-management.employees.partials.view.tabs.leave-balances-tab', ['viewEmployee' => $leaveEmployee ?? $viewEmployee])
                    </div>
                </template>
            </div>
        </div>
    @else
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ __('لا توجد بيانات لعرضها.') }}
        </div>
    @endif


</div>

@push('styles')
@include('livewire.hr-management.employees.partials.view.style')
@endpush

