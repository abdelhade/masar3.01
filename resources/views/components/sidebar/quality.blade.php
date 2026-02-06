@can('view quality')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>{{ __('Quality Dashboard') }}</span>
        </a>
    </li>
@endcan

@canany(['view inspections', 'create inspections'])
    <li class="nav-item">
        <a class="nav-link collapsed" href="#quality-inspections" data-bs-toggle="collapse" role="button" aria-expanded="false"
            aria-controls="quality-inspections">
            <i class="fas fa-clipboard-check"></i>
            <span>{{ __('Quality Inspections') }}</span>
            <i class="ti-angle-down"></i>
        </a>
        <div class="collapse" id="quality-inspections">
            <ul class="nav flex-column sub-menu">
                @can('view inspections')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.inspections.index') }}">
                            <i class="ti-control-record"></i>{{ __('All Inspections') }}
                        </a>
                    </li>
                @endcan
                @can('create inspections')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.inspections.create') }}">
                            <i class="ti-control-record"></i>{{ __('New Inspection') }}
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany

@can('view standards')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.standards.index') }}">
            <i class="fas fa-ruler-combined"></i>
            <span>{{ __('Quality Standards') }}</span>
        </a>
    </li>
@endcan

@canany(['view ncr', 'create ncr'])
    <li class="nav-item">
        <a class="nav-link collapsed" href="#quality-ncr" data-bs-toggle="collapse" role="button" aria-expanded="false"
            aria-controls="quality-ncr">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ __('Non-Conformance (NCR)') }}</span>
            <i class="ti-angle-down"></i>
        </a>
        <div class="collapse" id="quality-ncr">
            <ul class="nav flex-column sub-menu">
                @can('view ncr')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.ncr.index') }}">
                            <i class="ti-control-record"></i>{{ __('All Reports') }}
                        </a>
                    </li>
                @endcan
                @can('create ncr')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quality.ncr.create') }}">
                            <i class="ti-control-record"></i>{{ __('New Report') }}
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany

@can('view capa')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.capa.index') }}">
            <i class="fas fa-tools"></i>
            <span>{{ __('Corrective Actions (CAPA)') }}</span>
        </a>
    </li>
@endcan

@can('view batches')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.batches.index') }}">
            <i class="fas fa-barcode"></i>
            <span>{{ __('Batch Tracking') }}</span>
        </a>
    </li>
@endcan

@can('view rateSuppliers')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.suppliers.index') }}">
            <i class="fas fa-star"></i>
            <span>{{ __('Supplier Ratings') }}</span>
        </a>
    </li>
@endcan

@can('view certificates')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.certificates.index') }}">
            <i class="fas fa-certificate"></i>
            <span>{{ __('Certificates & Compliance') }}</span>
        </a>
    </li>
@endcan

@can('view audits')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('quality.audits.index') }}">
            <i class="fas fa-search"></i>
            <span>{{ __('Internal Audit') }}</span>
        </a>
    </li>
@endcan
