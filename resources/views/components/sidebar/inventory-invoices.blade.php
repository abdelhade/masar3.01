@php
    $inventory = [
        18 => ['label' => 'Damaged Goods Invoice', 'icon' => 'las la-box-open'],
        19 => ['label' => 'Dispatch Order', 'icon' => 'las la-file-export'],
        20 => ['label' => 'Addition Order', 'icon' => 'las la-file-import'],
        21 => ['label' => 'Store-to-Store Transfer', 'icon' => 'las la-exchange-alt'],
    ];
    $currentType = request('type');
@endphp

<li class="menu-title mt-2">{{ __('Inventory Module') }}</li>

<li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('inventory.statistics') ? 'active' : '' }}" 
       href="{{ route('inventory.statistics') }}"
       style="{{ request()->routeIs('inventory.statistics') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
        <i class="las la-chart-pie font-18"></i>{{ __('Inventory Statistics') }}
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

@foreach ($inventory as $t => $data)
    @can('view ' . $data['label'])
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ $currentType == $t ? 'active' : '' }}" 
               href="{{ route('invoices.index', ['type' => $t]) }}"
               style="{{ $currentType == $t ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
                <i class="{{ $data['icon'] }} font-18"></i> {{ __($data['label']) }}
            </a>
        </li>
    @endcan
@endforeach
