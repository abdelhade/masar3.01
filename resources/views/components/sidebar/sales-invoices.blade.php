@php
    $sales = [
        10 => 'فاتورة مبيعات',
        12 => 'مردود مبيعات',
        14 => 'أمر بيع',
        16 => 'عرض سعر لعميل',
        22 => 'أمر حجز',
        26 => 'اتفاقية تسعير',
    ];
    $viewPermissions = collect($sales)->map(fn($label) => 'عرض ' . $label)->toArray();
@endphp


<li class="nav-item">
    <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
        <i class="ti-control-record"></i>{{ __('Discounts.Statistics') }}
    </a>
</li>

@can('عرض قائمة الخصومات المسموح بها')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.index', ['type' => 30]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.allowed_discounts') }}
        </a>
    </li>
@endcan

@can('عرض خصم مسموح به')
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
    @can('عرض ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
