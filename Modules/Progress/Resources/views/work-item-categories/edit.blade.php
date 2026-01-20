@extends('progress::layouts.daily-progress')

@section('title', __('general.edit_work_item_category'))

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.edit_work_item_category'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('general.work_item_categories'), 'url' => route('work-item-categories.index')],
            ['label' => __('general.edit')],
        ],
    ])

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="las la-edit me-2"></i> {{ __('general.edit_work_item_category') }}: {{ $category->name }}
                    </h5>
                    <a href="{{ route('work-item-categories.index') }}" class="btn btn-sm btn-light text-primary fw-bold">
                        <i class="las la-arrow-right me-1"></i> {{ __('general.back') }}
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('work-item-categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-lg-12">
                                <label for="name" class="form-label fw-bold">{{ __('general.name') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name" name="name"
                                        value="{{ old('name', $category->name) }}" required>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-tag"></i></span>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <a href="{{ route('work-item-categories.index') }}" class="btn btn-outline-secondary btn-lg me-2">
                                <i class="las la-times"></i> {{ __('general.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="las la-save"></i> {{ __('general.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
