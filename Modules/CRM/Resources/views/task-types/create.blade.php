@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Task Types'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Task Types'), 'url' => route('tasks.types.index')],
            ['label' => __('Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Add New') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.types.store') }}" method="POST" onsubmit="disableButton()">
                        @csrf
                        <div class="mb-3 col-lg-4">
                            <label class="form-label" for="title">{{ __('Title') }}</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="{{ __('Enter the name') }}">
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn"><i class="las la-save"></i>
                                {{ __('Save') }}</button>
                            <a href="{{ route('tasks.types.index') }}" class="btn btn-danger"><i class="las la-times"></i>
                                {{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
