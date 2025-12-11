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
                                             onclick="openLightbox({{ json_encode($employeeImage) }})"
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

        {{-- Navigation Tabs - Bootstrap --}}
        <ul class="nav nav-tabs mb-3" role="tablist" id="employeeViewTabs">
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold active"
                        id="personal-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#personal-view-content"
                        type="button"
                        role="tab"
                        aria-controls="personal-view-content"
                        aria-selected="true">
                    <i class="fas fa-user me-2"></i>{{ __('البيانات الشخصية') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="location-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#location-view-content"
                        type="button"
                        role="tab"
                        aria-controls="location-view-content"
                        aria-selected="false">
                    <i class="fas fa-map-marker-alt me-2"></i>{{ __('الموقع') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="job-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#job-view-content"
                        type="button"
                        role="tab"
                        aria-controls="job-view-content"
                        aria-selected="false">
                    <i class="fas fa-briefcase me-2"></i>{{ __('الوظيفة') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="attendance-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#attendance-view-content"
                        type="button"
                        role="tab"
                        aria-controls="attendance-view-content"
                        aria-selected="false">
                    <i class="fas fa-clock me-2"></i>{{ __('الحضور') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="accounting-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#accounting-view-content"
                        type="button"
                        role="tab"
                        aria-controls="accounting-view-content"
                        aria-selected="false">
                    <i class="fas fa-calculator me-2"></i>{{ __('الحسابات') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="kpi-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#kpi-view-content"
                        type="button"
                        role="tab"
                        aria-controls="kpi-view-content"
                        aria-selected="false">
                    <i class="fas fa-chart-line me-2"></i>{{ __('معدلات الأداء') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="leaveBalances-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#leaveBalances-view-content"
                        type="button"
                        role="tab"
                        aria-controls="leaveBalances-view-content"
                        aria-selected="false">
                    <i class="fas fa-calendar-check me-2"></i>{{ __('رصيد الإجازات') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-hold fw-bold"
                        id="otherDetails-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#otherDetails-view-content"
                        type="button"
                        role="tab"
                        aria-controls="otherDetails-view-content"
                        aria-selected="false">
                    <i class="fas fa-info-circle me-2"></i>{{ __('تفاصيل أخرى') }}
                </button>
            </li>
        </ul>

        {{-- Tab Content - Bootstrap --}}
        <div class="tab-content" id="employeeViewTabsContent">
            {{-- Personal Tab --}}
            <div class="tab-pane fade show active"
                 id="personal-view-content"
                 role="tabpanel"
                 aria-labelledby="personal-tab"
                 tabindex="0">
                @include('livewire.hr-management.employees.partials.view.tabs.personal-tab')
            </div>

            {{-- Location Tab --}}
            <div class="tab-pane fade"
                 id="location-view-content"
                 role="tabpanel"
                 aria-labelledby="location-tab"
                 tabindex="0">
                @php
                    $locationEmployee = method_exists($this, 'employeeWithLocation') ? $this->employeeWithLocation : $viewEmployee;
                @endphp
                @include('livewire.hr-management.employees.partials.view.tabs.location-tab', ['viewEmployee' => $locationEmployee ?? $viewEmployee])
            </div>

            {{-- Job Tab --}}
            <div class="tab-pane fade"
                 id="job-view-content"
                 role="tabpanel"
                 aria-labelledby="job-tab"
                 tabindex="0">
                @include('livewire.hr-management.employees.partials.view.tabs.job-tab')
            </div>

            {{-- Attendance Tab --}}
            <div class="tab-pane fade"
                 id="attendance-view-content"
                 role="tabpanel"
                 aria-labelledby="attendance-tab"
                 tabindex="0">
                @include('livewire.hr-management.employees.partials.view.tabs.attendance-tab')
            </div>

            {{-- Accounting Tab --}}
            <div class="tab-pane fade"
                 id="accounting-view-content"
                 role="tabpanel"
                 aria-labelledby="accounting-tab"
                 tabindex="0">
                @php
                    $accountEmployee = method_exists($this, 'employeeWithAccount') ? $this->employeeWithAccount : $viewEmployee;
                @endphp
                @include('livewire.hr-management.employees.partials.view.tabs.accounting-tab', ['viewEmployee' => $accountEmployee ?? $viewEmployee])
            </div>

            {{-- KPI Tab --}}
            <div class="tab-pane fade"
                 id="kpi-view-content"
                 role="tabpanel"
                 aria-labelledby="kpi-tab"
                 tabindex="0">
                @php
                    $kpiEmployee = method_exists($this, 'employeeWithKpis') ? $this->employeeWithKpis : $viewEmployee;
                @endphp
                @include('livewire.hr-management.employees.partials.view.tabs.kpi-tab', ['viewEmployee' => $kpiEmployee ?? $viewEmployee])
            </div>

            {{-- Leave Balances Tab --}}
            <div class="tab-pane fade"
                 id="leaveBalances-view-content"
                 role="tabpanel"
                 aria-labelledby="leaveBalances-tab"
                 tabindex="0">
                @php
                    $leaveEmployee = method_exists($this, 'employeeWithLeaveBalances') ? $this->employeeWithLeaveBalances : $viewEmployee;
                @endphp
                @include('livewire.hr-management.employees.partials.view.tabs.leave-balances-tab', ['viewEmployee' => $leaveEmployee ?? $viewEmployee])
            </div>

            {{-- Other Details Tab --}}
            <div class="tab-pane fade"
                 id="otherDetails-view-content"
                 role="tabpanel"
                 aria-labelledby="otherDetails-tab"
                 tabindex="0">
                @php
                    $otherDetailsEmployee = method_exists($this, 'employeeWithOtherDetails') ? $this->employeeWithOtherDetails : $viewEmployee;
                @endphp
                @include('livewire.hr-management.employees.partials.view.tabs.other-details-tab', ['viewEmployee' => $otherDetailsEmployee ?? $viewEmployee])
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

