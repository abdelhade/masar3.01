@extends('admin.dashboard')

@section('title', __('general.edit_project_type'))

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.edit_project_type'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('general.project_types'), 'url' => route('project.types.index')],
            ['label' => __('general.edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('general.edit_project_type') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('project.types.update', $projectType->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">{{ __('general.name') }}</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $projectType->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('general.update') }}
                            </button>
                            <a href="{{ route('project.types.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('general.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
