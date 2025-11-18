@extends('admin.dashboard')
@section('sidebar')
    @include('components.sidebar.manufacturing')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Manufacturing Stages'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Manufacturing Stages'), 'url' => route('manufacturing.stages.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Manufacturing Stage') }}: {{ $manufacturingStage->name }}</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('manufacturing.stages.update', $manufacturingStage->id) }}" method="POST"
                        onsubmit="disableButton()">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">{{ __('Stage Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="{{ __('Enter stage name') }}"
                                    value="{{ old('name', $manufacturingStage->name) }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-8">
                                <label class="form-label" for="description">{{ __('Description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                    placeholder="{{ __('Write stage description') }}">{{ old('description', $manufacturingStage->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> {{ __('Save') }}
                            </button>

                            <a href="{{ route('manufacturing.stages.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
