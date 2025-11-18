@can('view Manufacturing Invoices')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('manufacturing.index') }}">
            <i class="ti-control-record"></i>{{ __('Manufacturing Invoices') }}
        </a>
    </li>
@endcan

@can('view Manufacturing Invoices')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('manufacturing.statistics') }}">
            <i class="ti-control-record"></i>{{ __('Statistics') }}
        </a>
    </li>
@endcan

@can('create Manufacturing Invoices')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('manufacturing.create') }}">
            <i class="ti-control-record"></i>{{ __('Create Manufacturing Invoice') }}
        </a>
    </li>
@endcan

@can('view Manufacturing Stages')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('manufacturing.stages.index') }}">
            <i class="ti-control-record"></i>{{ __('Manufacturing Stages') }}
        </a>
    </li>
@endcan

@can('view Manufacturing Orders')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('manufacturing.orders.create') }}">
            <i class="ti-control-record"></i>{{ __('Manufacturing Orders') }}
        </a>
    </li>
@endcan
