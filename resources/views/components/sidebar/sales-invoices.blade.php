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
