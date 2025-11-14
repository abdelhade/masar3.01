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

        <!-- Buildings Section -->
        <div id="buildingsSection" class="section-container">
            <div class="row mb-3">
                <div class="col-10">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-building text-primary me-2"></i>
                            {{ __('Residential Buildings') }}
                        </h2>
                    </div>
                </div>

                @can('create Buildings')
                    <div class="col-2 text-end">
                        <a href="{{ route('rentals.buildings.create') }}" class="btn btn-lg btn-primary">
                            <i class="fas fa-plus me-1"></i> {{ __('Add New Building') }}
                        </a>
                    </div>
                @endcan
            </div>

            <div class="row">
                @foreach ($buildings as $building)
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card building-card shadow-lg border-0 h-100">
                            <div class="card-header bg-gradient-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-building me-2"></i>
                                        {{ $building->name }}
                                    </h5>
                                    <div class="dropdown">
                                        @can('create Unit')
                                            <a href="{{ route('rentals-units.create', $building->id) }}"
                                                class="btn btn-sm btn-info" type="button">
                                                <i class="fas fa-plus"></i> {{ __('Add Units') }}
                                            </a>
                                        @endcan

                                        @can('edit Buildings')
                                            <a href="{{ route('rentals.buildings.edit', $building->id) }}"
                                                class="btn btn-sm btn-success" type="button">
                                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                        @endcan

                                        @can('delete Buildings')
                                            <form action="{{ route('rentals.buildings.destroy', $building->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this building?') }}')">
                                                    <i class="fas fa-trash"></i> {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="building-info">
                                    <div class="info-item mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <span
                                            class="text-muted">{{ $building->address ?: __('No address specified') }}</span>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="info-box text-center">
                                                <i class="fas fa-layer-group text-info"></i>
                                                <div class="info-number">{{ $building->floors ?: 0 }}</div>
                                                <div class="info-label">{{ __('Floor') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box text-center">
                                                <i class="fas fa-home text-success"></i>
                                                <div class="info-number">{{ $building->units->count() }}</div>
                                                <div class="info-label">{{ __('Unit') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($building->area)
                                        <div class="info-item mb-2">
                                            <i class="fas fa-ruler-combined text-warning me-2"></i>
                                            <span class="text-muted">{{ $building->area }} م²</span>
                                        </div>
                                    @endif

                                    <div class="units-status mt-3">
                                        <h6 class="mb-2">{{ __('Units Status:') }}</h6>
                                        <div class="row text-center">
                                            @foreach (\Modules\Rentals\Enums\UnitStatus::cases() as $status)
                                                <div class="col-4">
                                                    <div class="status-item {{ strtolower($status->name) }}">
                                                        <div class="status-number">
                                                            {{ $building->units->where('status', $status)->count() }}
                                                        </div>
                                                        <div class="status-label">{{ $status->label() }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @can('view Unit')
                                <div class="card-footer bg-light">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('rentals.buildings.show', $building->id) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>{{ __('View Units') }}
                                        </a>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </div>
                @endforeach
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
