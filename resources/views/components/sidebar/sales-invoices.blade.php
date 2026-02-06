@php
    $sales = [
        10 => ['label' => 'Sales Invoice', 'icon' => 'las la-file-invoice-dollar'],
        12 => ['label' => 'Sales Return', 'icon' => 'las la-undo-alt'],
        14 => ['label' => 'Sales Order', 'icon' => 'las la-shopping-cart'],
        16 => ['label' => 'Quotation to Customer', 'icon' => 'las la-file-alt'],
        22 => ['label' => 'Booking Order', 'icon' => 'las la-calendar-check'],
        26 => ['label' => 'Pricing Agreement', 'icon' => 'las la-handshake'],
    ];
    $currentType = request('type');
@endphp

<li class="menu-title mt-2">{{ __('Sales Module') }}</li>

<li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('discounts.general-statistics') ? 'active' : '' }}" 
       href="{{ route('discounts.general-statistics') }}"
       style="{{ request()->routeIs('discounts.general-statistics') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
        <i class="las la-percentage font-18"></i>{{ __('Discounts Statistics') }}
    </a>
</li>

@can('view Allowed Discounts')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('discounts.index') && request('type') == 30 ? 'active' : '' }}" 
           href="{{ route('discounts.index', ['type' => 30]) }}"
           style="{{ request()->routeIs('discounts.index') && request('type') == 30 ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-percent font-18"></i>{{ __('navigation.allowed_discounts') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('sales.statistics') ? 'active' : '' }}" 
       href="{{ route('sales.statistics') }}"
       style="{{ request()->routeIs('sales.statistics') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
        <i class="las la-broadcast-tower font-18"></i>{{ __('Sales Statistics') }}
    </a>
</li>

@can('view Invoice Templates')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('invoice-templates.*') ? 'active' : '' }}" 
           href="{{ route('invoice-templates.index') }}"
           style="{{ request()->routeIs('invoice-templates.*') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-layer-group font-18"></i>{{ __('Invoice Templates') }}
        </a>
    </li>
@endcan

<li class="menu-title mt-3">{{ __('Invoices') }}</li>

@foreach ($sales as $type => $data)
    @can('view ' . $data['label'])
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ $currentType == $type ? 'active' : '' }}" 
               href="{{ route('invoices.index', ['type' => $type]) }}"
               style="{{ $currentType == $type ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
                <i class="{{ $data['icon'] }} font-18"></i> {{ __($data['label']) }}
            </a>
        </li>
    @endcan
@endforeach
