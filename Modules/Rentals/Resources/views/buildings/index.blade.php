@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Buildings and Units'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Buildings and Units')],
        ],
    ])
    <div class="container-fluid px-4">
        @can('view Rentals Statistics')
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        {{ __('Total Buildings') }}</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $buildings->count() }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        {{ __('Total Units') }}</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $units->count() }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-home fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        {{ __('Rented Units') }}</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $units->where('status', \Modules\Rentals\Enums\UnitStatus::RENTED)->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-key fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stats-card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        {{ __('Available Units') }}</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $units->where('status', \Modules\Rentals\Enums\UnitStatus::AVAILABLE)->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-door-open fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Units Navigation Tabs -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill" id="unitsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3" id="buildings-tab" data-bs-toggle="tab" data-bs-target="#buildingsPane" type="button" role="tab">
                            <i class="fas fa-building me-2"></i> {{ __('Residential Buildings & Units') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3" id="items-tab" data-bs-toggle="tab" data-bs-target="#itemsPane" type="button" role="tab">
                            <i class="fas fa-tshirt me-2"></i> {{ __('Rental Items (Suits, Dresses, etc.)') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="unitsTabsContent">
            <!-- Buildings & Units Pane -->
            <div class="tab-pane fade show active" id="buildingsPane" role="tabpanel">
                <div id="buildingsSection" class="section-container">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-9">
                            <h2 class="section-title mb-0">
                                <i class="fas fa-city text-primary me-2"></i>
                                {{ __('Residential Buildings') }}
                            </h2>
                        </div>
                        @can('create Buildings')
                            <div class="col-md-3 text-end">
                                <a href="{{ route('rentals.buildings.create') }}" class="btn btn-primary px-4">
                                    <i class="fas fa-plus me-1"></i> {{ __('Add New Building') }}
                                </a>
                            </div>
                        @endcan
                    </div>

                    <div class="row">
                        @forelse ($buildings as $building)
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                <div class="card building-card shadow border-0 h-100">
                                    <div class="card-header bg-gradient-primary text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">
                                                <i class="fas fa-building me-2"></i>
                                                {{ $building->name }}
                                            </h5>
                                            <div class="dropdown">
                                                @can('create Unit')
                                                    <a href="{{ route('rentals-units.create', $building->id) }}"
                                                        class="btn btn-sm btn-info" type="button" title="{{ __('Add Unit') }}">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                @endcan
                                                @can('edit Buildings')
                                                    <a href="{{ route('rentals.buildings.edit', $building->id) }}"
                                                        class="btn btn-sm btn-success" type="button" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="building-info">
                                            <div class="info-item mb-2">
                                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                <span class="text-muted">{{ $building->address ?: __('No address specified') }}</span>
                                            </div>

                                            <div class="row mb-3 text-center">
                                                <div class="col-6 border-end">
                                                    <div class="h4 mb-0">{{ $building->floors ?: 0 }}</div>
                                                    <div class="text-xs text-muted text-uppercase">{{ __('Floors') }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="h4 mb-0">{{ $building->units->count() }}</div>
                                                    <div class="text-xs text-muted text-uppercase">{{ __('Units') }}</div>
                                                </div>
                                            </div>

                                            <div class="units-status mt-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 smaller fw-bold">{{ __('Occupancy Status') }}</h6>
                                                </div>
                                                <div class="row g-1 text-center">
                                                    @foreach (\Modules\Rentals\Enums\UnitStatus::cases() as $status)
                                                        <div class="col-4">
                                                            <div class="p-1 rounded bg-light border">
                                                                <div class="fw-bold small">{{ $building->units->where('status', $status)->count() }}</div>
                                                                <div class="text-xxs text-muted">{{ $status->label() }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @can('view Unit')
                                        <div class="card-footer bg-light border-0">
                                            <div class="d-grid">
                                                <a href="{{ route('rentals.buildings.show', $building->id) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>{{ __('View Details & Units') }}
                                                </a>
                                            </div>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center py-5">
                                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                                    <p>{{ __('No buildings found. Start by adding your first building.') }}</p>
                                    @can('create Buildings')
                                        <a href="{{ route('rentals.buildings.create') }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus"></i> {{ __('Add Building') }}
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Items Pane -->
            <div class="tab-pane fade" id="itemsPane" role="tabpanel">
                <div class="section-container">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-9">
                            <h2 class="section-title mb-0">
                                <i class="fas fa-tshirt text-primary me-2"></i>
                                {{ __('Rental Inventory Items') }}
                            </h2>
                        </div>
                        @can('create Unit')
                            <div class="col-md-3 text-end">
                                <a href="{{ route('rentals-units.create') }}" class="btn btn-success px-4">
                                    <i class="fas fa-plus me-1"></i> {{ __('Add Rental Item') }}
                                </a>
                            </div>
                        @endcan
                    </div>

                    <div class="row">
                        @forelse ($itemUnits as $unit)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card unit-card shadow-sm border-0 h-100 overflow-hidden">
                                    <div class="card-header border-0 pb-0 bg-white">
                                        <div class="d-flex justify-content-between align-items-top">
                                            <span class="badge @if($unit->status == \Modules\Rentals\Enums\UnitStatus::AVAILABLE) bg-success-soft text-success @elseif($unit->status == \Modules\Rentals\Enums\UnitStatus::RENTED) bg-danger-soft text-danger @else bg-warning-soft text-warning @endif p-2">
                                                <i class="fas fa-circle me-1 small"></i> {{ $unit->status->label() }}
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-link link-dark p-0" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    @can('edit Unit')
                                                        <li><a class="dropdown-item" href="{{ route('rentals.units.edit', $unit->id) }}"><i class="fas fa-edit me-2 text-success"></i> {{ __('Edit') }}</a></li>
                                                    @endcan
                                                    @can('delete Unit')
                                                        <li>
                                                            <form action="{{ route('rentals.units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i> {{ __('Delete') }}</button>
                                                            </form>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body pt-2 text-center">
                                        <div class="mb-3">
                                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                                <i class="fas fa-tshirt fa-2x text-primary"></i>
                                            </div>
                                        </div>
                                        <h5 class="card-title fw-bold mb-1">{{ $unit->name }}</h5>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-barcode me-1"></i> {{ $unit->item->code ?? __('No Code') }}
                                        </p>
                                        
                                        @if($unit->details)
                                            <p class="text-xs text-muted text-truncate px-2" title="{{ $unit->details }}">{{ $unit->details }}</p>
                                        @endif
                                    </div>
                                    <div class="card-footer bg-light border-0 pt-0">
                                        <div class="row g-0 text-center border-top py-2">
                                            <div class="col-12">
                                                @if($unit->status == \Modules\Rentals\Enums\UnitStatus::AVAILABLE)
                                                    <a href="{{ route('rentals.leases.create', ['unit_id' => $unit->id]) }}" class="btn btn-sm btn-primary w-100">
                                                        <i class="fas fa-file-contract me-1"></i> {{ __('Create Lease') }}
                                                    </a>
                                                @else
                                                    <span class="text-muted small italic">{{ __('Currently Rented') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-light text-center py-5 border">
                                    <i class="fas fa-tshirt fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">{{ __('No rental items found.') }}</p>
                                    @can('create Unit')
                                        <a href="{{ route('rentals-units.create') }}" class="btn btn-outline-success">
                                            <i class="fas fa-plus"></i> {{ __('Add Item') }}
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link href="{{ asset('assets/css/custom-css/rentals.css') }}" rel="stylesheet" />
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter functionality
            const buildingsBtn = document.getElementById('buildingsView');
            const unitsBtn = document.getElementById('unitsView');
            const allBtn = document.getElementById('allView');

            const buildingsSection = document.getElementById('buildingsSection');

            function resetButtons() {
                [buildingsBtn, unitsBtn, allBtn].forEach(btn => {
                    btn.classList.remove('active', 'btn-primary', 'btn-success', 'btn-info');
                    btn.classList.add('btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
                });
            }

            function showSection(section, button, btnClass) {
                resetButtons();
                buildingsSection.style.display = 'none';

                if (section) {
                    section.style.display = 'block';
                } else {
                    buildingsSection.style.display = 'block';
                }

                button.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
                button.classList.add('active', btnClass);
            }

            buildingsBtn?.addEventListener('click', () => {
                showSection(buildingsSection, buildingsBtn, 'btn-primary');
            });

            allBtn?.addEventListener('click', () => {
                showSection(null, allBtn, 'btn-info');
            });

            // Add animation delays for staggered loading effect
            const cards = document.querySelectorAll('.building-card, .unit-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Tooltip initialization if using Bootstrap tooltips
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
@endsection
