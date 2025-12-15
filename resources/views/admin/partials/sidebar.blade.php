@php
    $section = request('sidebar') ?? session('sidebar', 'all');
    $map = [
        'main' => null,
        'accounts' => ['components.sidebar.accounts'],
        'items' => ['components.sidebar.items'],
        'discounts' => ['components.sidebar.discounts'],
        'manufacturing' => ['components.sidebar.manufacturing'],
        'permissions' => ['components.sidebar.permissions'],
        'crm' => ['components.sidebar.crm'],
        'sales-invoices' => ['components.sidebar.sales-invoices'],
        'purchases-invoices' => ['components.sidebar.purchases-invoices'],
        'inventory-invoices' => ['components.sidebar.inventory-invoices'],
        'vouchers' => ['components.sidebar.vouchers'],
        'transfers' => ['components.sidebar.transfers'],
        'multi-vouchers' => ['components.sidebar.merit-vouchers'],
        'contract-journals' => ['components.sidebar.contract-journals'],
        'Assets-operations' => ['components.sidebar.multi-vouchers'],
        'depreciation' => ['components.sidebar.depreciation'],
        'basic_journal-journals' => ['components.sidebar.journals'],
        'projects' => ['components.sidebar.projects'],
        'departments' => ['components.sidebar.departments'],
        'settings' => ['components.sidebar.settings'],
        'rentals' => ['components.sidebar.rentals'],
        'service' => ['components.sidebar.service'],
        'shipping' => ['components.sidebar.shipping'],
        'fleet' => ['components.sidebar.fleet'],
        'POS' => ['components.sidebar.POS'],
        'daily_progress' => ['components.sidebar.daily_progress'],
        'inquiries' => ['components.sidebar.inquiries'],
        'checks' => ['components.sidebar.checks'],
    ];
    $allowed = $section === 'all' ? 'all' : $map[$section] ?? [];
@endphp

<!-- Left Sidenav -->
<div class="left-sidenav">

    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">

            <li class="menu-label my-2"><a href="{{ route('home') }}">{{ config('public_settings.campany_name') }}</a>
            </li>

            <li class="nav-item border-bottom pb-1 mb-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link d-flex align-items-center gap-2 font-hold fw-bold">
                    <i data-feather="home" style="color:#4e73df" class="menu-icon"></i>
                    {{ __('navigation.home') }}
                </a>

            </li>

            @if ($section !== 'all')
                <li class="nav-item mb-2">
                    <div class="alert alert-info d-flex align-items-center justify-content-between"
                        style="margin: 0; padding: 0.5rem 0.75rem;">
                        <small class="mb-0">
                            <i data-feather="filter" style="width: 14px; height: 14px;" class="me-1"></i>
                            عرض: {{ $section }}
                        </small>
                        {{-- <a href="{{ request()->url() }}?sidebar=all" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            عرض الجميع
                        </a> --}}
                    </div>
                </li>
            @endif

            @if (View::hasSection('sidebar-filter'))
                @yield('sidebar-filter')
            @else
                @if ($allowed === 'all' || in_array('components.sidebar.accounts', $allowed))
                    @include('components.sidebar.accounts')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.items', $allowed))
                    @include('components.sidebar.items')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.discounts', $allowed))
                    @include('components.sidebar.discounts')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.manufacturing', $allowed))
                    @include('components.sidebar.manufacturing')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.permissions', $allowed))
                    @include('components.sidebar.permissions')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.crm', $allowed))
                    @include('components.sidebar.crm')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.sales-invoices', $allowed))
                    @include('components.sidebar.sales-invoices')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.purchases-invoices', $allowed))
                    @include('components.sidebar.purchases-invoices')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.inventory-invoices', $allowed))
                    @include('components.sidebar.inventory-invoices')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.vouchers', $allowed))
                    @include('components.sidebar.vouchers')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.transfers', $allowed))
                    @include('components.sidebar.transfers')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.merit-vouchers', $allowed))
                    @include('components.sidebar.merit-vouchers')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.contract-journals', $allowed))
                    @include('components.sidebar.contract-journals')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.multi-vouchers', $allowed))
                    @include('components.sidebar.multi-vouchers')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.depreciation', $allowed))
                    @include('components.sidebar.depreciation')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.journals', $allowed))
                    @include('components.sidebar.journals')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.projects', $allowed))
                    @include('components.sidebar.projects')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.departments', $allowed))
                    @include('components.sidebar.departments')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.settings', $allowed))
                    @include('components.sidebar.settings')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.rentals', $allowed))
                    @include('components.sidebar.rentals')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.service', $allowed))
                    @include('components.sidebar.service')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.shipping', $allowed))
                    @include('components.sidebar.shipping')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.fleet', $allowed))
                    @include('components.sidebar.fleet')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.POS', $allowed))
                    @include('components.sidebar.POS')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.daily_progress', $allowed))
                    @include('components.sidebar.daily_progress')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.inquiries', $allowed))
                    @include('components.sidebar.inquiries')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.checks', $allowed))
                    @include('components.sidebar.checks')
                @endif
                @if ($allowed === 'all' || in_array('components.sidebar.installments', $allowed))
                    @include('components.sidebar.installments')
                @endif
            @endif

        </ul>
    </div>
</div>
