@php
    $purchases = [
        11 => 'فاتورة مشتريات',
        13 => 'مردود مشتريات',
        15 => 'أمر شراء',
        17 => 'عرض سعر من مورد',
        24 => 'فاتورة خدمه',
        25 => 'طلب احتياج',
    ];
@endphp

<li class="nav-item">
    <a class="nav-link" href="{{ route('discounts.general-statistics') }}">
        <i class="ti-control-record"></i>{{ __('Discounts.Statistics') }}
    </a>
</li>

@can('عرض قائمة الخصومات المكتسبة')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.index', ['type' => 31]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.earned_discounts') }}
        </a>
    </li>
@endcan

@can('عرض خصم مكتسب')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('discounts.create', ['type' => 31, 'q' => md5(31)]) }}">
            <i class="ti-control-record"></i>{{ __('navigation.earned_discount') }}
        </a>
    </li>
@endcan

<li class="nav-item">
    <a class="nav-link" href="{{ route('purchases.statistics') }}">
        <i class="ti-control-record"></i>Purchases Statistics
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('invoice-templates.index') }}">
        <i class="ti-control-record"></i>نماذج الفواتير
    </a>
</li>

@foreach ($purchases as $type => $label)
    {{-- @can('عرض ' . $label) --}}
    <li class="nav-item">
        <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
            <i class="ti-control-record"></i> {{ __($label) }}
        </a>
    </li>
    {{-- @endcan --}}
@endforeach

<li class="nav-item">
    <a class="nav-link" href="{{ route('invoices.track.search') }}">
        <i class="ti-control-record"></i> تتبع مسار الفاتورة
    </a>
</li>
