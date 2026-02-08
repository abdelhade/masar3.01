@php
    $salesItems = [
        10 => 'فاتورة مبيعات',
        12 => 'مردود مبيعات',
        14 => 'أمر بيع',
        16 => 'عرض سعر لعميل',
        22 => 'أمر حجز',
    ];
@endphp

@php
    $viewPermissions = [];
    foreach ($salesItems as $type => $label) {
        $viewPermissions[] = 'عرض ' . $label;
    }
@endphp

@canany($viewPermissions)
    <li class="li-main">
        <a href="javascript:void(0);">
            <i data-feather="shopping-cart" style="color:#e74a3b" class="align-self-center menu-icon"></i>
            <span>{{ __('Sales Management') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>

        <ul class="sub-menu mm-collapse" aria-expanded="false">
            @foreach ($salesItems as $type => $label)
                @can('عرض ' . $label)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                            <i class="ti-control-record"></i> {{ __($label) }}
                        </a>
                    </li>
                @endcan
            @endforeach
        </ul>
    </li>
@endcanany
