@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Project Size'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Project Sizes'), 'url' => route('project-size.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Project Size') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('project-size.update', $projectSize->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name', $projectSize->name) }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-6">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $projectSize->description) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('Save') }}
                            </button>
                            <a href="{{ route('project-size.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
