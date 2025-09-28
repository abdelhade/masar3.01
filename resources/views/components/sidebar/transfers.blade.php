{{-- @can('عرض التحويلات النقدية')
    <li class="li-main">
        <a href="javascript: void(0);">
            <i data-feather="repeat" style="color:#20c997" class="align-self-center menu-icon"></i>
            <span>{{ __('navigation.cash_transfers') }}</span>
            <span class="menu-arrow"><i class="mdi mdi-chevron-right"></i></span>
        </a>
        <ul class="sub-menu mm-collapse" aria-expanded="false"> --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_cash']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.cash_to_cash_transfer') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_bank']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.cash_to_bank_transfer') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_cash']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.bank_to_cash_transfer') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_bank']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.bank_to_bank_transfer') }}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.cash_transfers') }}
    </a>
</li>
{{-- </ul>
    </li>
@endcan --}}
