@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Trips'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Trips'), 'url' => route('fleet.trips.index')],
            ['label' => __('fleet::Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('fleet::Edit') }} {{ __('fleet::Trip') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('fleet.trips.update', $trip->id) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                            {{ old('vehicle_id', $trip->vehicle_id) == $vehicle->id ? 'selected' : '' }}
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
                                <label for="driver_id" class="form-label">
                                    {{ __('fleet::Driver') }} <span class="text-danger">*</span>
                                </label>
                                <select name="driver_id" id="driver_id"
                                    class="form-control @error('driver_id') is-invalid @enderror" required>
                                    <option value="">{{ __('fleet::Choose') }} {{ __('fleet::Driver') }}</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}"
                                            {{ old('driver_id', $trip->driver_id) == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('driver_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_location" class="form-label">
                                    {{ __('fleet::Start Location') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="start_location" id="start_location"
                                    class="form-control @error('start_location') is-invalid @enderror"
                                    value="{{ old('start_location', $trip->start_location) }}" required>
                                @error('start_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_location" class="form-label">
                                    {{ __('fleet::End Location') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="end_location" id="end_location"
                                    class="form-control @error('end_location') is-invalid @enderror"
                                    value="{{ old('end_location', $trip->end_location) }}" required>
                                @error('end_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">
                                    {{ __('fleet::Start Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="start_date" id="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date', $trip->start_date->format('Y-m-d\TH:i')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">{{ __('fleet::End Date') }}</label>
                                <input type="datetime-local" name="end_date" id="end_date"
                                    class="form-control @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date', $trip->end_date?->format('Y-m-d\TH:i')) }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_mileage" class="form-label">
                                    {{ __('fleet::Start Mileage') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" name="start_mileage" id="start_mileage"
                                    class="form-control @error('start_mileage') is-invalid @enderror"
                                    value="{{ old('start_mileage', $trip->start_mileage) }}" required min="0">
                                @error('start_mileage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_mileage" class="form-label">{{ __('fleet::End Mileage') }}</label>
                                <input type="number" step="0.01" name="end_mileage" id="end_mileage"
                                    class="form-control @error('end_mileage') is-invalid @enderror"
                                    value="{{ old('end_mileage', $trip->end_mileage) }}" min="0">
                                @error('end_mileage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    {{ __('fleet::Status') }} <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status"
                                    class="form-control @error('status') is-invalid @enderror" required>
                                    @foreach(\Modules\Fleet\Enums\TripStatus::cases() as $status)
                                        <option value="{{ $status->value }}"
                                            {{ old('status', $trip->status->value) == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="purpose" class="form-label">{{ __('fleet::Purpose') }}</label>
                                <input type="text" name="purpose" id="purpose"
                                    class="form-control @error('purpose') is-invalid @enderror"
                                    value="{{ old('purpose', $trip->purpose) }}">
                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="notes" class="form-label">{{ __('fleet::Notes') }}</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $trip->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-3">
                                <x-branches::branch-select :branches="$branches" :selected="$trip->branch_id" />
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('fleet::Update') }}
                            </button>
                            <a href="{{ route('fleet.trips.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('fleet::Back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

