@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Unit'),
        'items' => array_merge(
            [
                ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
                ['label' => __('Buildings and Units'), 'url' => route('rentals.buildings.index')],
            ],
            $building 
                ? [['label' => $building->name, 'url' => route('rentals.buildings.show', $building->id)]] 
                : [],
            [
                ['label' => __('Edit Unit')],
            ]
        ),
    ])

    <div class="container-fluid px-4">
        @can('edit Unit')
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('Edit Unit:') }} <strong class="text-primary">{{ $unit->name }}</strong>
                        @if($building)
                            {{ __('in building:') }} <strong class="text-success">{{ $building->name }}</strong>
                        @else
                            <span class="badge bg-info ms-2">{{ __('Rental Item') }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body" x-data="{ unitType: '{{ old('unit_type', $unit->unit_type) }}' }">
                    <form action="{{ route('rentals.units.update', $unit->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">{{ __('Unit Type') }}</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="unit_type" id="typeBuilding" value="building" x-model="unitType" @checked(old('unit_type', $unit->unit_type) === 'building')>
                                        <label class="form-check-label" for="typeBuilding">{{ __('Residential Unit (Building/Apartment)') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="unit_type" id="typeItem" value="item" x-model="unitType" @checked(old('unit_type', $unit->unit_type) === 'item')>
                                        <label class="form-check-label" for="typeItem">{{ __('Inventory Item (Suit, Dress, etc.)') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Building Selection --}}
                            <div class="col-md-3 mb-3" x-show="unitType === 'building'">
                                <label for="building_id" class="form-label">{{ __('Building') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <select name="building_id" id="building_id" class="form-select @error('building_id') is-invalid @enderror" :required="unitType === 'building'">
                                        <option value="">{{ __('Select Building') }}</option>
                                        @foreach(\Modules\Rentals\Models\RentalsBuilding::all() as $b)
                                            <option value="{{ $b->id }}" @selected(old('building_id', $unit->building_id) == $b->id)>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('building_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Item Selection --}}
                            <div class="col-md-3 mb-3" x-show="unitType === 'item'" x-cloak>
                                <label for="item_id" class="form-label">{{ __('Inventory Item') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tshirt"></i></span>
                                    <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" :required="unitType === 'item'">
                                        <option value="">{{ __('Select Item') }}</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" @selected(old('item_id', $unit->item_id) == $item->id)>{{ $item->name }} ({{ $item->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('item_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Unit Name --}}
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label">{{ __('Unit Name/Number') }} <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="name" id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $unit->name) }}" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Area --}}
                            <div class="col-md-2 mb-3" x-show="unitType === 'building'">
                                <label for="area" class="form-label">{{ __('Area (m²)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler-combined"></i></span>
                                    <input type="number" step="0.01" name="area" id="area"
                                        class="form-control @error('area') is-invalid @enderror"
                                        value="{{ old('area', $unit->area) }}">
                                </div>
                                @error('area')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Floor --}}
                            <div class="col-md-2 mb-3" x-show="unitType === 'building'">
                                <label for="floor" class="form-label">{{ __('Floor') }} <span
                                        class="text-danger" x-show="unitType === 'building'">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                    <select name="floor" id="floor"
                                        class="form-select @error('floor') is-invalid @enderror" :required="unitType === 'building'">
                                        <option value="">{{ __('Select Floor') }}</option>
                                        @foreach ($floors as $floor)
                                            <option value="{{ $floor }}"
                                                {{ old('floor', $unit->floor) == $floor ? 'selected' : '' }}>
                                                {{ $floor }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('floor')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-2 mb-3">
                                <label for="status" class="form-label">{{ __('Status') }} <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <select name="status" id="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach (\Modules\Rentals\Enums\UnitStatus::cases() as $status)
                                            <option value="{{ $status->value }}"
                                                {{ old('status', $unit->status) == $status->value ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Details --}}
                            <div class="col-12 mb-3">
                                <label for="details" class="form-label">{{ __('Additional Details / Specifications') }}</label>
                                <textarea name="details" id="details" class="form-control @error('details') is-invalid @enderror" rows="4">{{ old('details', $unit->details) }}</textarea>
                                @error('details')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="card-footer text-end bg-transparent border-top pt-3 pe-0">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> {{ __('Update Unit') }}
                            </button>
                            <a href="{{ $building ? route('rentals.buildings.show', $building->id) : route('rentals.buildings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
