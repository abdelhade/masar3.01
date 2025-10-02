@php
    $sales = [
        10 => 'فاتورة مبيعات',
        12 => 'مردود مبيعات',
        14 => 'أمر بيع',
        16 => 'عرض سعر لعميل',
        22 => 'أمر حجز',
    ];
    $viewPermissions = collect($sales)->map(fn($label) => 'عرض ' . $label)->toArray();
@endphp

{{-- @canany($viewPermissions)
    <li class="li-main border-bottom pb-1 mb-2">
        <a href="javascript:void(0);">
            <i data-feather="shopping-cart" style="color:#e74a3b" class="align-self-center menu-icon"></i>
            <span>{{ __('ادارة المبيعات') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>

        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
@foreach ($sales as $type => $label)
    @can('عرض ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
{{-- </ul>
    </li>
@endcanany --}}
