<li class="nav-item">
    <a class="nav-link font-family-cairo fw-bold" href="{{ route('items.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Items Statistics') }}
    </a>
</li>

@can('عرض الوحدات')
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="{{ route('units.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.units') }}
        </a>
    </li>
@endcan
@can('عرض الأصناف')
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="{{ route('items.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.items') }}
        </a>
    </li>
@endcan
@can('عرض الأسعار')
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="{{ route('prices.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.prices') }}
        </a>
    </li>
@endcan
@canany([
    'عرض الاماكن',
    'عرض التصنيفات',
    'عرض
    المجموعات',
    ])
    <livewire:item-management.notes.notesNames />
    <li class="nav-item">
        <a class="nav-link font-family-cairo fw-bold" href="{{ route('varibals.index') }}">
            <i class="ti-control-record"></i>{{ __('navigation.varibals') }}
        </a>
    </li>
    <livewire:item-management.varibals.varibalslinks />
@endcan
