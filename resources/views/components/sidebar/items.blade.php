{{-- @canany(['عرض الوحدات', 'عرض التصنيفات', 'عرض الأسعار', 'عرض الأصناف'])
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="box" style="color:#1cc88a" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.items') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
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
    'عرض المقاسات',
    'عرض الطباعه',
    'عرض الاماكن',
    'عرض المواقع',
    'عرض التصنيفات',
    'عرض
    المجموعات',
    ])
    <livewire:item-management.notes.notesNames />
@endcan
<!-- {{-- item movement --}}
                                                                                                                                                      @can('عرض تقرير حركة صنف')
    <li class="nav-item">
                                     <a class="nav-link font-family-cairo fw-bold" href="{{ route('item-movement') }}">
                                     <i class="ti-control-record"></i>{{ __('navigation.item_movement_report') }}
                                     </a>
                                      </li>
@endcan
                                                                                                                                                                    {{-- item movement --}} -->
{{-- </ul>
    </li> --}}
{{-- @endcanany --}}
