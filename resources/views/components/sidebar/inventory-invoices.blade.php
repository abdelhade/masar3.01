@php
    $inventory = [
        18 => 'Damaged Goods Invoice',
        19 => 'Dispatch Order',
        20 => 'Addition Order',
        21 => 'Store-to-Store Transfer',
    ];
@endphp

<li class="nav-item">
    <a class="nav-link" href="{{ route('inventory.statistics') }}">
        <i class="ti-control-record"></i>{{ __('Inventory Statistics') }}
    </a>
</li>

@can('view Invoice Templates')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('invoice-templates.index') }}">
            <i class="ti-control-record"></i>{{ __('Invoice Templates') }}
        </a>
    </li>
@endcan

@foreach ($inventory as $type => $label)
    @can('view ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
