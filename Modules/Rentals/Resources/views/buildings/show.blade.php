@extends('admin.dashboard')

@section('content')
    <div class="container-fluid px-4">
        <br>
        <div class="row">
            <h2 class="mb-4 col-4">
                <i class="fas fa-building text-primary me-2"></i>
                {{ $building->name }}
            </h2>

            <p class="text-muted col-4">
                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                {{ $building->address }}
            </p>
        </div>

        <a href="{{ route('rentals-units.create', $building->id) }}" class="btn btn-info " type="button">
            <i class="fas fa-plus"></i> أضافة وحدات
        </a>
        <br>
        <br>
        <div class="row">
            @foreach ($building->units as $unit)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card unit-card shadow border-0 h-100">
                        <div class="card-header unit-header status-{{ strtolower($unit->status->value ?? 'available') }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-home me-2"></i>
                                    وحدة {{ $unit->name }}
                                </h6>

                                <a href="{{ route('rentals.units.edit', $unit->id) }}" class="btn btn-sm btn-success me-2">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>

                                {{-- Delete form --}}
                                <form action="{{ route('rentals.units.destroy', $unit->id) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف الوحدة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger me-2">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </form>
                                <span class="badge badge-status">
                                    {{ $unit->status->label ?? 'متاحة' }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($unit->floor)
                                <p><i class="fas fa-layer-group me-2 text-info"></i> الطابق {{ $unit->floor }}</p>
                            @endif
                            @if ($unit->area)
                                <p><i class="fas fa-ruler-combined me-2 text-warning"></i> {{ $unit->area }} م²</p>
                            @endif

                            @php
                                $activeLease = $unit->leases
                                    ->where('status', \Modules\Rentals\Enums\LeaseStatus::ACTIVE)
                                    ->first();
                            @endphp
                            @if ($activeLease)
                                <div class="alert alert-light p-2">
                                    <small class="text-success">
                                        <i class="fas fa-user me-1"></i>
                                        مؤجرة للسيد/ة {{ $activeLease->client->name }}
                                    </small><br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        حتى {{ $activeLease->end_date->format('Y/m/d') }}
                                    </small>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-light">
                            <div class="btn-group w-100">
                                @if ($unit->status === \Modules\Rentals\Enums\UnitStatus::AVAILABLE)
                                    <a href="{{ route('rentals.leases.create', $unit->id) }}"
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-file-contract me-1"></i> عقد جديد
                                    </a>
                                @elseif($activeLease)
                                    <a href="{{ route('rentals.leases.show', $activeLease->id) }}"
                                        class="btn btn-info btn-sm">
                                        <i class="fas fa-file-alt me-1"></i> العقد الحالي
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
