@php
    $accountTypes = [
        'clients' => ['label' => 'navigation.clients', 'icon' => 'las la-user-tag'],
        'suppliers' => ['label' => 'navigation.suppliers', 'icon' => 'las la-truck-loading'],
        'funds' => ['label' => 'navigation.funds', 'icon' => 'las la-wallet'],
        'banks' => ['label' => 'navigation.banks', 'icon' => 'las la-university'],
        'employees' => ['label' => 'navigation.employees', 'icon' => 'las la-user-tie'],
        'warhouses' => ['label' => 'navigation.warehouses', 'icon' => 'las la-warehouse'],
        'expenses' => ['label' => 'navigation.expenses', 'icon' => 'las la-file-invoice-dollar'],
        'revenues' => ['label' => 'navigation.revenues', 'icon' => 'las la-hand-holding-usd'],
        'creditors' => ['label' => 'navigation.various_creditors', 'icon' => 'las la-user-minus'],
        'debtors' => ['label' => 'navigation.various_debtors', 'icon' => 'las la-user-plus'],
        'partners' => ['label' => 'navigation.partners', 'icon' => 'las la-users'],
        'current-partners' => ['label' => 'navigation.current_partners', 'icon' => 'las la-user-friends'],
        'assets' => ['label' => 'navigation.fixed_assets', 'icon' => 'las la-building'],
        'rentables' => ['label' => 'navigation.rentable_assets', 'icon' => 'las la-key'],
        'check-portfolios-incoming' => ['label' => 'حافظات أوراق القبض', 'icon' => 'las la-file-alt'],
        'check-portfolios-outgoing' => ['label' => 'حافظات أوراق الدفع', 'icon' => 'las la-file-signature'],
    ];

    $currentType = request('type');
@endphp

<li class="menu-title mt-2">{{ __('Accounts Module') }}</li>

@can('view basicData-statistics')
<li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('accounts.basic-data-statistics') ? 'active' : '' }}" 
       href="{{ route('accounts.basic-data-statistics') }}"
       style="{{ request()->routeIs('accounts.basic-data-statistics') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
        <i class="las la-chart-bar font-18"></i>{{ __('إحصائيات البيانات الأساسية') }}
    </a>
</li>
@endcan

@foreach ($accountTypes as $type => $data)
    @can('view ' . (isset($permissionMap[$type]) ? $permissionMap[$type] : $type))
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ $currentType == $type ? 'active' : '' }}" 
               href="{{ route('accounts.index', ['type' => $type]) }}"
               style="{{ $currentType == $type ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
                <i class="{{ $data['icon'] }} font-18"></i>{{ __($data['label']) }}
            </a>
        </li>
    @endcan
@endforeach



