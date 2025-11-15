@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Building'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Buildings and Units'), 'url' => route('rentals.buildings.index')],
            ['label' => $building->name, 'url' => route('rentals.buildings.edit', $building->id)],
            ['label' => __('Edit Building')],
        ],
    ])

    <div class="container-fluid px-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('Edit Building Data:') }} <strong class="text-primary">{{ $building->name }}</strong>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rentals.buildings.update', $building->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- Building Name --}}
                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">{{ __('Building Name') }} <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $building->name) }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div class="col-md-4 mb-3">
                            <label for="address" class="form-label">{{ __('Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="address" id="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    value="{{ old('address', $building->address) }}">
                            </div>
                            @error('address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Floors --}}
                        <div class="col-md-2 mb-3">
                            <label for="floors" class="form-label">{{ __('Number of Floors') }} <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                <input type="number" name="floors" id="floors"
                                    class="form-control @error('floors') is-invalid @enderror"
                                    value="{{ old('floors', $building->floors) }}" required step="1">
                            </div>
                            @error('floors')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Area --}}
                        <div class="col-md-2 mb-3">
                            <label for="area" class="form-label">{{ __('Area (m²)') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-ruler-combined"></i></span>
                                <input type="number" step="0.01" name="area" id="area"
                                    class="form-control @error('area') is-invalid @enderror"
                                    value="{{ old('area', $building->area) }}">
                            </div>
                            @error('area')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Details --}}
                        <div class="col-12 mb-3">
                            <label for="details" class="form-label">{{ __('Additional Details') }}</label>
                            <textarea name="details" id="details" class="form-control @error('details') is-invalid @enderror" rows="4">{{ old('details', $building->details) }}</textarea>
                            @error('details')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer text-end bg-transparent border-top pt-3 pe-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('Update Building') }}
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
