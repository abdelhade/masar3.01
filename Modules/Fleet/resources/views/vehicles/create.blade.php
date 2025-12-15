@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Vehicles'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Vehicles'), 'url' => route('fleet.vehicles.index')],
            ['label' => __('fleet::Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('fleet::Add New') }} {{ __('fleet::Vehicle') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('fleet.vehicles.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="plate_number" class="form-label">
                                    {{ __('fleet::Plate Number') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="plate_number" id="plate_number"
                                    class="form-control @error('plate_number') is-invalid @enderror"
                                    value="{{ old('plate_number') }}" required>
                                @error('plate_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vehicle_type_id" class="form-label">
                                    {{ __('fleet::Vehicle Type') }} <span class="text-danger">*</span>
                                </label>
                                <select name="vehicle_type_id" id="vehicle_type_id"
                                    class="form-control @error('vehicle_type_id') is-invalid @enderror" required>
                                    <option value="">{{ __('fleet::Choose') }} {{ __('fleet::Vehicle Type') }}</option>
                                    @foreach ($vehicleTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    {{ __('fleet::Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="driver_id" class="form-label">
                                    {{ __('fleet::Driver') }}
                                </label>
                                <select name="driver_id" id="driver_id"
                                    class="form-control @error('driver_id') is-invalid @enderror">
                                    <option value="">{{ __('fleet::Choose') }} {{ __('fleet::Driver') }}</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}"
                                            {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
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
                            <div class="col-md-4 mb-3">
                                <label for="model" class="form-label">{{ __('fleet::Model') }}</label>
                                <input type="text" name="model" id="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model') }}">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label">{{ __('fleet::Year') }}</label>
                                <input type="number" name="year" id="year"
                                    class="form-control @error('year') is-invalid @enderror"
                                    value="{{ old('year') }}" min="1900" max="{{ date('Y') + 1 }}">
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="color" class="form-label">{{ __('fleet::Color') }}</label>
                                <input type="text" name="color" id="color"
                                    class="form-control @error('color') is-invalid @enderror"
                                    value="{{ old('color') }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="chassis_number" class="form-label">{{ __('fleet::Chassis Number') }}</label>
                                <input type="text" name="chassis_number" id="chassis_number"
                                    class="form-control @error('chassis_number') is-invalid @enderror"
                                    value="{{ old('chassis_number') }}">
                                @error('chassis_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="engine_number" class="form-label">{{ __('fleet::Engine Number') }}</label>
                                <input type="text" name="engine_number" id="engine_number"
                                    class="form-control @error('engine_number') is-invalid @enderror"
                                    value="{{ old('engine_number') }}">
                                @error('engine_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_mileage" class="form-label">
                                    {{ __('fleet::Current Mileage') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.01" name="current_mileage" id="current_mileage"
                                    class="form-control @error('current_mileage') is-invalid @enderror"
                                    value="{{ old('current_mileage', 0) }}" required min="0">
                                @error('current_mileage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">
                                    {{ __('fleet::Status') }} <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status"
                                    class="form-control @error('status') is-invalid @enderror" required>
                                    @foreach(\Modules\Fleet\Enums\VehicleStatus::cases() as $status)
                                        <option value="{{ $status->value }}"
                                            {{ old('status', 'available') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="purchase_date" class="form-label">{{ __('fleet::Purchase Date') }}</label>
                                <input type="date" name="purchase_date" id="purchase_date"
                                    class="form-control @error('purchase_date') is-invalid @enderror"
                                    value="{{ old('purchase_date') }}">
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="purchase_cost" class="form-label">{{ __('fleet::Purchase Cost') }}</label>
                                <input type="number" step="0.01" name="purchase_cost" id="purchase_cost"
                                    class="form-control @error('purchase_cost') is-invalid @enderror"
                                    value="{{ old('purchase_cost') }}" min="0">
                                @error('purchase_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">{{ __('fleet::Is Active') }}</label>
                                <select name="is_active" id="is_active"
                                    class="form-control @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ __('fleet::Active') }}</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ __('fleet::Inactive') }}</option>
                                </select>
                                @error('is_active')
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
                            <a href="{{ route('fleet.vehicles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('fleet::Back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

