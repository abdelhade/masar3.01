@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.shipping')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Drivers'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Drivers'), 'url' => route('drivers.index')],
            ['label' => __('Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Add New Driver') }}</h2>
                </div>
                <div class="card-body">
                    @can('create Drivers')
                        <form action="{{ route('drivers.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="name">{{ __('Name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="{{ __('Enter driver name') }}" value="{{ old('name') }}">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="phone">{{ __('Phone Number') }}</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        placeholder="{{ __('Enter phone number') }}" value="{{ old('phone') }}">
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="vehicle_type">{{ __('Vehicle Type') }}</label>
                                    <input type="text" class="form-control" id="vehicle_type" name="vehicle_type"
                                        placeholder="{{ __('Enter vehicle type') }}" value="{{ old('vehicle_type') }}">
                                    @error('vehicle_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="is_available">{{ __('Status') }}</label>
                                    <select class="form-control" id="is_available" name="is_available">
                                        <option value="1" {{ old('is_available', 1) == 1 ? 'selected' : '' }}>
                                            {{ __('Available') }}
                                        </option>
                                        <option value="0" {{ old('is_available') == 0 ? 'selected' : '' }}>
                                            {{ __('Unavailable') }}
                                        </option>
                                    </select>
                                    @error('is_available')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <x-branches::branch-select :branches="$branches" />

                            </div>

                            <div class="d-flex justify-content-start mt-4">
                                <button type="submit" class="btn btn-main me-2">
                                    <i class="las la-save"></i> {{ __('Save') }}
                                </button>
                                <a href="{{ route('drivers.index') }}" class="btn btn-danger">
                                    <i class="las la-times"></i> {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
