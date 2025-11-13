@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Client Categories'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Client Categories'), 'url' => route('client.categories.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Category') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.categories.update', $category->id) }}" method="POST"
                        onsubmit="disableButton()">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- Name -->
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">{{ __('Name') }}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $category->name) }}" placeholder="{{ __('Enter the name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3 col-lg-8">
                                <label class="form-label" for="description">{{ __('Description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="{{ __('Enter a description for the category') }}">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> {{ __('Save Changes') }}
                            </button>

                            <a href="{{ route('client.categories.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
