<li class="menu-title mt-2">{{ __('Items Module') }}</li>

@can('view item-statistics')
<li class="nav-item">
    <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('items.statistics') ? 'active' : '' }}" 
       href="{{ route('items.statistics') }}"
       style="{{ request()->routeIs('items.statistics') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
        <i class="las la-chart-pie font-18"></i>{{ __('Items Statistics') }}
    </a>
</li>
@endcan

@can('view units')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('units.index') ? 'active' : '' }}" 
           href="{{ route('units.index') }}"
           style="{{ request()->routeIs('units.index') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-balance-scale font-18"></i>{{ __('navigation.units') }}
        </a>
    </li>
@endcan

@can('view items')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('items.index') ? 'active' : '' }}" 
           href="{{ route('items.index') }}"
           style="{{ request()->routeIs('items.index') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-boxes font-18"></i>{{ __('navigation.items') }}
        </a>
    </li>
@endcan

@can('view prices')
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('prices.index') ? 'active' : '' }}" 
           href="{{ route('prices.index') }}"
           style="{{ request()->routeIs('prices.index') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-tags font-18"></i>{{ __('navigation.prices') }}
        </a>
    </li>
@endcan

@can('view varibals')
    <livewire:item-management.notes.notesNames />
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 font-hold fw-bold transition-base {{ request()->routeIs('varibals.index') ? 'active' : '' }}" 
           href="{{ route('varibals.index') }}"
           style="{{ request()->routeIs('varibals.index') ? 'background-color: rgba(52, 211, 163, 0.1); color: #34d3a3;' : '' }}">
            <i class="las la-cog font-18"></i>{{ __('navigation.varibals') }}
        </a>
    </li>
    <livewire:item-management.varibals.varibalslinks />
@endcan

