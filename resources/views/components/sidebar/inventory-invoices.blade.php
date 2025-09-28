@php
    $inventory = [
        18 => 'فاتورة تالف',
        19 => ' فواتير أمر صرف',
        20 => 'أمر إضافة',
        21 => 'تحويل من مخزن لمخزن',
    ];
    $viewPermissions = collect($inventory)->map(fn($label) => 'عرض ' . $label)->toArray();
@endphp

{{-- @canany($viewPermissions)
    <li class="li-main">
        <a href="javascript:void(0);">
            <i data-feather="archive" style="color:#f39c12" class="align-self-center menu-icon"></i>
            <span>{{ __('ادارة المخزون') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>

        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
@foreach ($inventory as $type => $label)
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
