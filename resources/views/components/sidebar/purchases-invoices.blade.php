@php
    $purchases = [
        11 => 'فاتورة مشتريات',
        13 => 'مردود مشتريات',
        15 => 'أمر شراء',
        17 => 'عرض سعر من مورد',
        24 => 'فاتورة خدمه',
    ];
    // $viewPermissions = collect($purchases)->map(fn($label) => 'عرض ' . $label)->toArray();
@endphp

{{-- @canany($viewPermissions) --}}
{{-- <li class="li-main border-bottom pb-1 mb-2">
        <a href="javascript:void(0);">
            <i data-feather="shopping-bag" style="color:#28a745" class="align-self-center menu-icon"></i>
            <span>{{ __('ادارة المشتريات') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>

        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
@foreach ($purchases as $type => $label)
    {{-- @can('عرض ' . $label) --}}
    <li class="nav-item">
        <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
            <i class="ti-control-record"></i> {{ __($label) }}
        </a>
    </li>
    {{-- @endcan --}}
@endforeach
{{-- </ul>
    </li> --}}
{{-- @endcanany --}}
