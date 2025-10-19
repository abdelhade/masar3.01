<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Transfers Statistics') }}
    </a>
</li>

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
