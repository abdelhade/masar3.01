@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Rentals Reports'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Rentals Reports')],
        ],
    ])

    <div class="container-fluid px-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill" id="reportsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3" id="active-rentals-tab" data-bs-toggle="tab" data-bs-target="#activeRentalsPane" type="button" role="tab">
                            <i class="fas fa-file-contract me-2"></i> {{ __('Active Rentals') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3" id="available-units-tab" data-bs-toggle="tab" data-bs-target="#availableUnitsPane" type="button" role="tab">
                            <i class="fas fa-door-open me-2"></i> {{ __('Available Units/Items') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content" id="reportsTabsContent">
            {{-- Active Rentals Pane --}}
            <div class="tab-pane fade show active" id="activeRentalsPane" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 text-success"><i class="fas fa-check-circle me-2"></i>{{ __('Active Rentals List') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Client') }}</th>
                                        <th>{{ __('Unit/Item') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th>{{ __('Remaining Days') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($activeLeases as $lease)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm rounded-circle bg-light-primary text-primary me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user-circle"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ optional($lease->client)->cname }}</div>
                                                        <small class="text-muted">{{ optional($lease->client)->phone }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold">
                                                    @if(($lease->unit->unit_type ?? 'building') === 'item' && $lease->unit->item)
                                                        {{ $lease->unit->item->name }}
                                                    @else
                                                        {{ optional($lease->unit)->name }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ ($lease->unit->unit_type ?? 'building') === 'item' ? 'bg-info' : 'bg-primary' }}">
                                                    {{ ($lease->unit->unit_type ?? 'building') === 'item' ? __('Item') : __('Unit') }}
                                                </span>
                                            </td>
                                            <td>{{ $lease->start_date?->format('Y/m/d') }}</td>
                                            <td>{{ $lease->end_date?->format('Y/m/d') }}</td>
                                            <td>
                                                @php
                                                    $days = now()->diffInDays($lease->end_date, false);
                                                @endphp
                                                <span class="badge {{ $days < 7 ? 'bg-danger' : 'bg-success' }}">
                                                    {{ $days }} {{ __('Days') }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($lease->rent_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('rentals.leases.show', $lease->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>{{ __('No active rentals found.') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Available Units Pane --}}
            <div class="tab-pane fade" id="availableUnitsPane" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 text-primary"><i class="fas fa-box-open me-2"></i>{{ __('Available Units/Items List') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Building / Code') }}</th>
                                        <th>{{ __('Floor') }}</th>
                                        <th>{{ __('Area') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($availableUnits as $unit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-bold">{{ $unit->name }}</td>
                                            <td>
                                                <span class="badge {{ ($unit->unit_type ?? 'building') === 'item' ? 'bg-info' : 'bg-primary' }}">
                                                    {{ ($unit->unit_type ?? 'building') === 'item' ? __('Item') : __('Unit') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(($unit->unit_type ?? 'building') === 'item' && $unit->item)
                                                    <span class="text-muted"><i class="fas fa-barcode me-1"></i> {{ $unit->item->code }}</span>
                                                @elseif(($unit->unit_type ?? 'building') === 'building' && $unit->building)
                                                    <span class="text-muted"><i class="fas fa-building me-1"></i> {{ $unit->building->name }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $unit->floor ?? '-' }}</td>
                                            <td>{{ $unit->area ? $unit->area . ' m²' : '-' }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('create Leases')
                                                        <a href="{{ route('rentals.leases.create', ['unit_id' => $unit->id]) }}" class="btn btn-sm btn-success">
                                                            <i class="fas fa-file-contract me-1"></i> {{ __('Lease') }}
                                                        </a>
                                                    @endcan
                                                    @can('edit Unit')
                                                        <a href="{{ route('rentals.units.edit', $unit->id) }}" class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                                <p>{{ __('No available units/items found.') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
