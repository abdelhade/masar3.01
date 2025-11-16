@php
    $sales = [
        10 => 'فاتورة مبيعات',
        12 => 'مردود مبيعات',
        14 => 'امر بيع',
        16 => 'عرض سعر لعميل',
        22 => 'امر حجز',
        26 => 'اتفاقية تسعير',
    ];
@endphp


<li class="nav-item">
    <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
        <i class="ti-control-record"></i>{{ __('Discounts.Statistics') }}
    </a>
</li>

@can('view قائمة الخصومات المسموح بها')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.index', ['type' => 30]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.allowed_discounts') }}
        </a>
    </li>
@endcan

@can('create خصم مسموح به')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.create', ['type' => 30, 'q' => md5(30)]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.allowed_discount') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <a class="nav-link" href="{{ route('sales.statistics') }}">
        <i class="ti-control-record"></i>Sales Statistics
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('invoice-templates.index') }}">
        <i class="ti-control-record"></i>نماذج الفواتير
    </a>
</li>

@foreach ($sales as $type => $label)
    @can('view ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
