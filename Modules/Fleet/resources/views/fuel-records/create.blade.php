@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Fuel Records'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Fuel Records'), 'url' => route('fleet.fuel-records.index')],
            ['label' => __('fleet::Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('fleet::Add New') }} {{ __('fleet::Fuel Record') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('fleet.fuel-records.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_id" class="form-label">
                                    {{ __('fleet::Vehicle') }} <span class="text-danger">*</span>
                                </label>
                                <select name="vehicle_id" id="vehicle_id"
                                    class="form-control @error('vehicle_id') is-invalid @enderror" required>
                                    <option value="">{{ __('fleet::Choose') }} {{ __('fleet::Vehicle') }}</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}"
                                            {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}
                                            data-mileage="{{ $vehicle->current_mileage }}">
                                            {{ $vehicle->name }} ({{ $vehicle->plate_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="trip_id" class="form-label">
                                    {{ __('fleet::Trip') }}
                                </label>
                                <select name="trip_id" id="trip_id"
                                    class="form-control @error('trip_id') is-invalid @enderror">
                                    <option value="">{{ __('fleet::Choose') }} {{ __('fleet::Trip') }} ({{ __('fleet::Optional') }})</option>
                                    @foreach ($trips as $trip)
                                        <option value="{{ $trip->id }}"
                                            {{ old('trip_id') == $trip->id ? 'selected' : '' }}>
                                            {{ $trip->trip_number }} - {{ $trip->start_location }} → {{ $trip->end_location }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('trip_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fuel_date" class="form-label">
                                    {{ __('fleet::Fuel Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="fuel_date" id="fuel_date"
                                    class="form-control @error('fuel_date') is-invalid @enderror"
                                    value="{{ old('fuel_date', now()->format('Y-m-d')) }}" required>
                                @error('fuel_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="fuel_type" class="form-label">
                                    {{ __('fleet::Fuel Type') }} <span class="text-danger">*</span>
                                </label>
                                <select name="fuel_type" id="fuel_type"
                                    class="form-control @error('fuel_type') is-invalid @enderror" required>
                                    @foreach(\Modules\Fleet\Enums\FuelType::cases() as $type)
                                        <option value="{{ $type->value }}"
                                            {{ old('fuel_type', 'gasoline') == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fuel_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="mileage_at_fueling" class="form-label">
                                    {{ __('fleet::Mileage at Fueling') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" name="mileage_at_fueling" id="mileage_at_fueling"
                                    class="form-control @error('mileage_at_fueling') is-invalid @enderror"
                                    value="{{ old('mileage_at_fueling') }}" required min="0">
                                @error('mileage_at_fueling')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">
                                    {{ __('fleet::Quantity') }} (L) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" name="quantity" id="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity') }}" required min="0">
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cost" class="form-label">
                                    {{ __('fleet::Cost') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" name="cost" id="cost"
                                    class="form-control @error('cost') is-invalid @enderror"
                                    value="{{ old('cost') }}" required min="0">
                                @error('cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="station_name" class="form-label">{{ __('fleet::Station Name') }}</label>
                                <input type="text" name="station_name" id="station_name"
                                    class="form-control @error('station_name') is-invalid @enderror"
                                    value="{{ old('station_name') }}">
                                @error('station_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="receipt_number" class="form-label">{{ __('fleet::Receipt Number') }}</label>
                                <input type="text" name="receipt_number" id="receipt_number"
                                    class="form-control @error('receipt_number') is-invalid @enderror"
                                    value="{{ old('receipt_number') }}">
                                @error('receipt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="notes" class="form-label">{{ __('fleet::Notes') }}</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-3">
                                <x-branches::branch-select :branches="$branches" />
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('fleet::Save') }}
                            </button>
                            <a href="{{ route('fleet.fuel-records.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('fleet::Back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('vehicle_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const mileage = selectedOption.getAttribute('data-mileage');
            if (mileage) {
                document.getElementById('mileage_at_fueling').value = mileage;
            }
        });
    </script>
@endsection

