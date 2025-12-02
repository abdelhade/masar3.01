{{-- resources/views/maintenance/periodic/create.blade.php --}}
@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Periodic Maintenance'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Periodic Maintenance'), 'url' => route('periodic.maintenances.index')],
            ['label' => __('Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Add New Periodic Maintenance Schedule') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('periodic.maintenances.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            {{-- Client Name --}}
                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">
                                    {{ __('Client Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="client_name" id="client_name"
                                    class="form-control @error('client_name') is-invalid @enderror"
                                    value="{{ old('client_name') }}" required>
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Client Phone --}}
                            <div class="col-md-6 mb-3">
                                <label for="client_phone" class="form-label">
                                    {{ __('Client Phone') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="client_phone" id="client_phone"
                                    class="form-control @error('client_phone') is-invalid @enderror"
                                    value="{{ old('client_phone') }}" required>
                                @error('client_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Item Name --}}
                            <div class="col-md-6 mb-3">
                                <label for="item_name" class="form-label">
                                    {{ __('Item Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="item_name" id="item_name"
                                    class="form-control @error('item_name') is-invalid @enderror"
                                    value="{{ old('item_name') }}" required>
                                @error('item_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Item Number --}}
                            <div class="col-md-6 mb-3">
                                <label for="item_number" class="form-label">
                                    {{ __('Item Number') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="item_number" id="item_number"
                                    class="form-control @error('item_number') is-invalid @enderror"
                                    value="{{ old('item_number') }}" required>
                                @error('item_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Service Type --}}
                            <div class="col-md-6 mb-3">
                                <label for="service_type_id" class="form-label">
                                    {{ __('Service Type') }} <span class="text-danger">*</span>
                                </label>
                                <select name="service_type_id" id="service_type_id"
                                    class="form-control @error('service_type_id') is-invalid @enderror" required>
                                    <option value="">{{ __('Choose Service Type') }}</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('service_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Branch --}}
                            <div class="col-md-6 mb-3">
                                <x-branches::branch-select :branches="$branches" />

                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Frequency Type --}}
                            <div class="col-md-6 mb-3">
                                <label for="frequency_type" class="form-label">
                                    {{ __('Frequency Type') }} <span class="text-danger">*</span>
                                </label>
                                <select name="frequency_type" id="frequency_type"
                                    class="form-control @error('frequency_type') is-invalid @enderror" required>
                                    <option value="">{{ __('Choose Frequency Type') }}</option>
                                    <option value="daily" {{ old('frequency_type') == 'daily' ? 'selected' : '' }}>يومي
                                    </option>
                                    <option value="weekly" {{ old('frequency_type') == 'weekly' ? 'selected' : '' }}>أسبوعي
                                    </option>
                                    <option value="monthly" {{ old('frequency_type') == 'monthly' ? 'selected' : '' }}>شهري
                                    </option>
                                    <option value="quarterly" {{ old('frequency_type') == 'quarterly' ? 'selected' : '' }}>
                                        ربع سنوي</option>
                                    <option value="semi_annual"
                                        {{ old('frequency_type') == 'semi_annual' ? 'selected' : '' }}>نصف سنوي</option>
                                    <option value="annual" {{ old('frequency_type') == 'annual' ? 'selected' : '' }}>سنوي
                                    </option>
                                    <option value="custom_days"
                                        {{ old('frequency_type') == 'custom_days' ? 'selected' : '' }}>عدد أيام مخصص
                                    </option>
                                </select>
                                @error('frequency_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Frequency Value (Days) for custom_days --}}
                            <div class="col-md-6 mb-3">
                                <label for="frequency_value" class="form-label">
                                    {{ __('Frequency Value (Days)') }}
                                </label>
                                <input type="number" name="frequency_value" id="frequency_value"
                                    class="form-control @error('frequency_value') is-invalid @enderror"
                                    value="{{ old('frequency_value') }}" min="1">
                                @error('frequency_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Start Date --}}
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">
                                    {{ __('Start Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Next Maintenance Date --}}
                            <div class="col-md-6 mb-3">
                                <label for="next_maintenance_date" class="form-label">
                                    {{ __('Next Maintenance Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="next_maintenance_date" id="next_maintenance_date"
                                    class="form-control @error('next_maintenance_date') is-invalid @enderror"
                                    value="{{ old('next_maintenance_date') }}" required>
                                @error('next_maintenance_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Last Maintenance Date --}}
                            <div class="col-md-6 mb-3">
                                <label for="last_maintenance_date" class="form-label">
                                    {{ __('Last Maintenance Date') }}
                                </label>
                                <input type="date" name="last_maintenance_date" id="last_maintenance_date"
                                    class="form-control @error('last_maintenance_date') is-invalid @enderror"
                                    value="{{ old('last_maintenance_date') }}">
                                @error('last_maintenance_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notification Days Before --}}
                            <div class="col-md-6 mb-3">
                                <label for="notification_days_before" class="form-label">
                                    {{ __('Notification Days Before') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="notification_days_before" id="notification_days_before"
                                    class="form-control @error('notification_days_before') is-invalid @enderror"
                                    value="{{ old('notification_days_before', 7) }}" min="1" max="365"
                                    required>
                                @error('notification_days_before')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Notes --}}
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Is Active --}}
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                        value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Is Active') }}
                                    </label>
                                </div>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Save') }}
                            </button>
                            <a href="{{ route('periodic.maintenances.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
