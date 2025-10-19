@php
    $inventory = [
        18 => 'فاتورة تالف',
        19 => 'فواتير أمر صرف',
        20 => 'أمر إضافة',
        21 => 'تحويل من مخزن لمخزن',
    ];
    $viewPermissions = collect($inventory)->map(fn($label) => 'عرض ' . $label)->toArray();
@endphp

<li class="nav-item">
    <a class="nav-link" href="{{ route('inventory.statistics') }}">
        <i class="ti-control-record"></i>Inventory Statistics
    </a>
</li>
@foreach ($inventory as $type => $label)
    @can('عرض ' . $label)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('invoices.index', ['type' => $type]) }}">
                <i class="ti-control-record"></i> {{ __($label) }}
            </a>
        </li>
    @endcan
@endforeach
