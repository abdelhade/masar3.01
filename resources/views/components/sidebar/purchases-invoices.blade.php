{{-- Purchases Invoices Sidebar --}}
@php
    $purchases = [
        11 => 'Purchase Invoice',
        13 => 'Purchase Return',
        15 => 'Purchase Order',
        17 => 'Quotation from Supplier',
        24 => 'Service Invoice',
        25 => 'Requisition',
    ];
@endphp

<li class="nav-item">
    <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
        <i class="ti-control-record"></i>{{ __('Discounts.Statistics') }}
    </a>
</li>

@can('view Earned Discounts List')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.index', ['type' => 31]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.earned_discounts') }}
        </a>
    </li>
@endcan

@can('create Earned Discount')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.create', ['type' => 31, 'q' => md5(31)]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.earned_discount') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <a class="nav-link" href="{{ route('purchases.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Purchases Statistics') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('invoice-templates.index') }}">
        <i class="ti-control-record"></i>{{ __('Invoice Templates') }}
    </a>
</li>

{{-- Loop through all purchases invoices with permissions --}}
@foreach ($purchases as $type => $label)
    @can('view ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach

<li class="nav-item">
    <a class="nav-link" href="{{ route('invoices.track.search') }}">
        <i class="ti-control-record"></i> {{ __('Track Invoice Path') }}
    </a>
</li>
