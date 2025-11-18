@can('view transfers')
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.index') }}">
        <i class="ti-control-record"></i>{{ __('navigation.cash_transfers') }}
    </a>
</li>
@endcan
@can('view transfer-statistics')
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Transfers Statistics') }}
    </a>
</li>
@endcan

@canany(['view cash-to-cash', 'create cash-to-cash'])
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_cash']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.cash_to_cash_transfer') }}
    </a>
</li>
@endcanany

@canany(['view cash-to-bank', 'create cash-to-bank'])
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'cash_to_bank']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.cash_to_bank_transfer') }}
    </a>
</li>
@endcanany

@canany(['view bank-to-cash', 'create bank-to-cash'])
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_cash']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.bank_to_cash_transfer') }}
    </a>
</li>
@endcanany
@canany(['view bank-to-bank', 'create bank-to-bank'])
<li class="nav-item">
    <a class="nav-link" href="{{ route('transfers.create', ['type' => 'bank_to_bank']) }}">
        <i class="ti-control-record"></i>{{ __('navigation.bank_to_bank_transfer') }}
    </a>
</li>
@endcanany


