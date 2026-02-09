@php
    $sales = [
        10 => 'Sales Invoice',
        12 => 'Sales Return',
        14 => 'Sales Order',
        16 => 'Quotation to Customer',
        22 => 'Booking Order',
        26 => 'Pricing Agreement',
    ];
@endphp

<li class="nav-item">
    <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
        <i class="ti-control-record"></i>{{ __('Discounts Statistics') }}
    </a>
</li>

@can('view Allowed Discounts')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.index', ['type' => 30]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.allowed_discounts') }}
        </a>
    </li>
@endcan

@can('create Allowed Discounts')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.create', ['type' => 30, 'q' => md5(30)]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.allowed_discount') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <a class="nav-link" href="{{ route('sales.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Sales Statistics') }}
    </a>
</li>

@can('view Invoice Templates')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('invoice-templates.index') }}">
            <i class="ti-control-record"></i>{{ __('Invoice Templates') }}
        </a>
    </li>
@endcan

@foreach ($sales as $type => $label)
    @can('view ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
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
