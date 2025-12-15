@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Vehicle Types'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('fleet::Vehicle Types'), 'url' => route('fleet.vehicle-types.index')],
            ['label' => __('fleet::Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('fleet::Add New') }} {{ __('fleet::Vehicle Type') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('fleet.vehicle-types.store') }}" method="POST">
                        @csrf
                        <div class="row">

                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="name">
                                    {{ __('fleet::Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="{{ __('fleet::Name') }}"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="is_active">
                                    {{ __('fleet::Is Active') }}
                                </label>
                                <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ __('fleet::Active') }}</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ __('fleet::Inactive') }}</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-12">
                                <label class="form-label" for="description">{{ __('fleet::Description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3" placeholder="{{ __('fleet::Description') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('fleet::Save') }}
                            </button>

                            <a href="{{ route('fleet.vehicle-types.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('fleet::Cancel') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

