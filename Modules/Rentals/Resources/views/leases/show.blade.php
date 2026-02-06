@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    {{-- Breadcrumb --}}
    @include('components.breadcrumb', [
        'title' => __('Lease Details'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Leases'), 'url' => route('rentals.leases.index')],
            ['label' => __('Lease Details')],
        ],
    ])

    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-contract me-2"></i>
                            {{ __('Lease Details') }}
                        </h5>
                        <div>
                            @can('edit Leases')
                                <a href="{{ route('rentals.leases.edit', $lease->id) }}" class="btn btn-success">
                                    <i class="fas fa-edit me-2"></i>{{ __('Edit Lease') }}
                                </a>
                            @endcan
                            <a href="{{ route('rentals.leases.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('Back to List') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Unit Information --}}
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-building me-2"></i>{{ __('Unit Information') }}
                                    </h6>
                                    <div class="mb-2">
                                        <strong>{{ __('Unit Type') }}:</strong>
                                        <span class="ms-2">
                                            {{ ($lease->unit->unit_type ?? 'building') === 'item' ? __('Rental Item') : __('Residential Unit') }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ ($lease->unit->unit_type ?? 'building') === 'item' ? __('Item') : __('Unit') }}:</strong>
                                        <span class="ms-2">
                                            @if(($lease->unit->unit_type ?? 'building') === 'item' && $lease->unit->item)
                                                {{ $lease->unit->item->name }}
                                            @else
                                                {{ optional($lease->unit)->name ?? __('N/A') }}
                                            @endif
                                        </span>
                                    </div>
                                    @if ($lease->unit && $lease->unit->unit_type === 'item' && $lease->unit->item)
                                        <div class="mb-2">
                                            <strong>{{ __('Reference Item') }}:</strong>
                                            <span class="ms-2">
                                                {{ $lease->unit->item->name }} ({{ $lease->unit->item->code }})
                                            </span>
                                        </div>
                                    @endif
                                    @if ($lease->unit && ($lease->unit->unit_type ?? 'building') === 'building' && $lease->unit->building)
                                        <div class="mb-2">
                                            <strong>{{ __('Building') }}:</strong>
                                            <span class="ms-2">
                                                {{ $lease->unit->building->name }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>{{ __('Address') }}:</strong>
                                            <span class="ms-2">
                                                {{ $lease->unit->building->address ?? __('N/A') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Client Information --}}
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-user me-2"></i>{{ __('Client Information') }}
                                    </h6>
                                    <div class="mb-2">
                                        <strong>{{ __('Client Name') }}:</strong>
                                        <span class="ms-2">
                                            {{ optional($lease->client)->cname ?? __('N/A') }}
                                        </span>
                                    </div>
                                    @if ($lease->client)
                                        @if ($lease->client->email)
                                            <div class="mb-2">
                                                <strong>{{ __('Email') }}:</strong>
                                                <span class="ms-2">{{ $lease->client->email }}</span>
                                            </div>
                                        @endif
                                        @if ($lease->client->phone)
                                            <div class="mb-2">
                                                <strong>{{ __('Phone') }}:</strong>
                                                <span class="ms-2">{{ $lease->client->phone }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Lease Dates --}}
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-calendar-alt me-2"></i>{{ __('Lease Period & Type') }}
                                    </h6>
                                    <div class="mb-2">
                                        <strong>{{ __('Rent Type') }}:</strong>
                                        <span class="ms-2">
                                            {{ ucfirst(__($lease->rent_type ?? 'monthly')) }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ __('Start Date') }}:</strong>
                                        <span class="ms-2">
                                            {{ $lease->start_date?->format('Y/m/d') ?? __('N/A') }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ __('End Date') }}:</strong>
                                        <span class="ms-2">
                                            {{ $lease->end_date?->format('Y/m/d') ?? __('N/A') }}
                                        </span>
                                    </div>
                                    @if ($lease->start_date && $lease->end_date)
                                        @php
                                            $daysRemaining = now()->diffInDays($lease->end_date, false);
                                        @endphp
                                        <div class="mb-2">
                                            <strong>{{ __('Days Remaining') }}:</strong>
                                            <span class="ms-2 {{ $daysRemaining < 30 ? 'text-danger' : 'text-success' }}">
                                                {{ $daysRemaining > 0 ? $daysRemaining : __('Expired') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Financial Information --}}
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-money-bill-wave me-2"></i>{{ __('Financial Information') }}
                                    </h6>
                                    <div class="mb-2">
                                        <strong>{{ __('Rent Amount') }}:</strong>
                                        <span class="ms-2 text-success fw-bold">
                                            {{ number_format($lease->rent_amount, 2) }} {{ __('SAR') }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ __('Account') }}:</strong>
                                        <span class="ms-2">
                                            {{ optional($lease->account)->aname ?? __('N/A') }}
                                        </span>
                                    </div>
                                    @if ($lease->payment_method)
                                        <div class="mb-2">
                                            <strong>{{ __('Payment Method') }}:</strong>
                                            <span class="ms-2">{{ $lease->payment_method }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-toggle-on me-2"></i>{{ __('Status') }}
                                    </h6>
                                    <div class="mb-2">
                                        <span class="badge {{ match($lease->status) {
                                            \Modules\Rentals\Enums\LeaseStatus::PENDING => 'bg-warning',
                                            \Modules\Rentals\Enums\LeaseStatus::ACTIVE => 'bg-success',
                                            \Modules\Rentals\Enums\LeaseStatus::EXPIRED => 'bg-secondary',
                                            \Modules\Rentals\Enums\LeaseStatus::TERMINATED => 'bg-danger',
                                            default => 'bg-secondary'
                                        } }} fs-6">
                                            {{ $lease->status->label() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            @if ($lease->notes)
                                <div class="col-12 mb-4">
                                    <div class="border rounded p-3">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-sticky-note me-2"></i>{{ __('Notes') }}
                                        </h6>
                                        <p class="mb-0">{{ $lease->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ __('Created') }}: {{ $lease->created_at?->format('Y/m/d H:i') ?? __('N/A') }}
                                @if ($lease->updated_at && $lease->updated_at != $lease->created_at)
                                    | {{ __('Updated') }}: {{ $lease->updated_at->format('Y/m/d H:i') }}
                                @endif
                            </small>
                            <div>
                                @can('delete Leases')
                                    <form action="{{ route('rentals.leases.destroy', $lease->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this lease?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash me-2"></i>{{ __('Delete') }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

