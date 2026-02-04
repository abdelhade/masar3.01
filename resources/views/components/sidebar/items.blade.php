@can('view item-statistics')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('items.statistics') }}">
            <i class="ti-control-record"></i>{{ __('Items Statistics') }}
        </a>
    </li>
@endcan

@can('view units')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('units.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.units') }}
        </a>
    </li>
@endcan

@can('view items')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('items.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.items') }}
        </a>
    </li>
@endcan

@can('view prices')
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('prices.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.prices') }}
        </a>
    </li>
@endcan

@can('view varibals')
    <livewire:item-management.notes.notesNames />
    <li class="nav-item">
        <a class="nav-link font-hold fw-bold" href="{{ route('varibals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.varibals') }}
        </a>
    </li>
    <livewire:item-management.varibals.varibalslinks />
@endcan
