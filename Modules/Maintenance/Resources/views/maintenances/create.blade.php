@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Maintenance'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Maintenance'), 'url' => route('maintenances.index')],
            ['label' => __('Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Add New') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('maintenances.store') }}" method="POST">
                        @csrf

                        <div class="row">
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

                            <div class="col-md-6 mb-3">
                                <label for="client_phone" class="form-label">
                                    {{ __('Client Phone') }}
                                </label>
                                <input type="text" name="client_phone" id="client_phone"
                                    class="form-control @error('client_phone') is-invalid @enderror"
                                    value="{{ old('client_phone') }}">
                                @error('client_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
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

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    {{ __('Status') }} <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status"
                                    class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="">{{ __('Choose Status') }}</option>
                                    <option value="1" {{ old('status') == '0' ? 'selected' : '' }}>
                                        {{ __('Pending') }}
                                    </option>
                                    <option value="2" {{ old('status') == '1' ? 'selected' : '' }}>
                                        {{ __('In Progress') }}
                                    </option>
                                    <option value="3" {{ old('status') == '2' ? 'selected' : '' }}>
                                        {{ __('Completed') }}
                                    </option>
                                    <option value="4" {{ old('status') == '3' ? 'selected' : '' }}>
                                        {{ __('Cancelled') }}
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">
                                    {{ __('Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="date" id="date"
                                    class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', now()->format('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="accural_date" class="form-label">
                                    {{ __('Accural Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="accural_date" id="accural_date"
                                    class="form-control @error('accural_date') is-invalid @enderror"
                                    value="{{ old('accural_date') }}" required>
                                @error('accural_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row" x-data="{ spare: {{ old('spare_parts_cost', 0) }}, labor: {{ old('labor_cost', 0) }}, total: {{ old('total_cost', 0) }} }">
                            <div class="col-md-3 mb-3">
                                <label for="asset_id" class="form-label">
                                    {{ __('Asset (Accounting)') }}
                                </label>
                                <select name="asset_id" id="asset_id"
                                    class="form-control @error('asset_id') is-invalid @enderror">
                                    <option value="">{{ __('Choose Asset') }}</option>
                                    @foreach ($assets as $asset)
                                        <option value="{{ $asset->id }}"
                                            {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->asset_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="depreciation_item_id" class="form-label">
                                    {{ __('Asset (Direct)') }}
                                </label>
                                <select name="depreciation_item_id" id="depreciation_item_id"
                                    class="form-control @error('depreciation_item_id') is-invalid @enderror">
                                    <option value="">{{ __('Choose Asset') }}</option>
                                    @foreach ($depreciationItems as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('depreciation_item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('depreciation_item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="maintenance_type" class="form-label">
                                    {{ __('Maintenance Type') }}
                                </label>
                                <select name="maintenance_type" id="maintenance_type"
                                    class="form-control @error('maintenance_type') is-invalid @enderror">
                                    <option value="">{{ __('Choose Type') }}</option>
                                    <option value="periodic"
                                        {{ old('maintenance_type') == 'periodic' ? 'selected' : '' }}>{{ __('Periodic') }}
                                    </option>
                                    <option value="emergency"
                                        {{ old('maintenance_type') == 'emergency' ? 'selected' : '' }}>
                                        {{ __('Emergency') }}</option>
                                    <option value="repair" {{ old('maintenance_type') == 'repair' ? 'selected' : '' }}>
                                        {{ __('Repair') }}</option>
                                </select>
                                @error('maintenance_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="spare_parts_cost" class="form-label">
                                    {{ __('Spare Parts Cost') }}
                                </label>
                                <input type="number" step="0.01" name="spare_parts_cost" id="spare_parts_cost"
                                    class="form-control @error('spare_parts_cost') is-invalid @enderror"
                                    x-model.number="spare" @input="total = spare + labor">
                                @error('spare_parts_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="labor_cost" class="form-label">
                                    {{ __('Labor Cost') }}
                                </label>
                                <input type="number" step="0.01" name="labor_cost" id="labor_cost"
                                    class="form-control @error('labor_cost') is-invalid @enderror" x-model.number="labor"
                                    @input="total = spare + labor">
                                @error('labor_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="total_cost" class="form-label">
                                    {{ __('Total Cost') }}
                                </label>
                                <input type="number" step="0.01" name="total_cost" id="total_cost"
                                    class="form-control @error('total_cost') is-invalid @enderror" x-model.number="total"
                                    readonly>
                                @error('total_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-9 mb-3">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
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
                                <i class="fas fa-save me-2"></i>{{ __('Save') }}
                            </button>
                            <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
