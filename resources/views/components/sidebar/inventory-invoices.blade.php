@php
    $inventory = [
        18 => 'فاتورة توالف',
        19 => 'امر صرف',
        20 => 'امر اضافة',
        21 => 'تحويل من مخزن لمخزن',
    ];
@endphp


<li class="nav-item">
    <a class="nav-link" href="{{ route('inventory.statistics') }}">
        <i class="ti-control-record"></i>Inventory Statistics
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('invoice-templates.index') }}">
        <i class="ti-control-record"></i>نماذج الفواتير
    </a>
</li>

@foreach ($inventory as $type => $label)
    @can('view ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
