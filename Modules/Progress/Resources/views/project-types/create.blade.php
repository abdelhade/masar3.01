@extends('progress::layouts.daily-progress')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.add_project_type'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('general.project_types'), 'url' => route('project.types.index')],
            ['label' => __('general.create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 text-white fw-bold"><i class="las la-plus-circle me-2"></i>{{ __('general.add_project_type') }}</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('project.types.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-lg-12">
                                <label class="form-label fw-bold" for="name">{{ __('general.name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg" id="name" name="name"
                                        placeholder="{{ __('general.name') }}" value="{{ old('name') }}">
                                    <span class="input-group-text bg-light text-muted"><i class="las la-file-alt"></i></span>
                                </div>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-2">
                             <a href="{{ route('project.types.index') }}" class="btn btn-secondary px-4 btn-lg">
                                <i class="las la-times"></i> {{ __('general.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 btn-lg">
                                <i class="las la-save"></i> {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
