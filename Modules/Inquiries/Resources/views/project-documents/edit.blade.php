@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Project Documents'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Project Documents'), 'url' => route('inquiry.documents.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Document') }}</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('inquiry.documents.update', $document->id) }}" method="POST"
                        onsubmit="disableButton()">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">{{ __('Document Name') }}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $document->name) }}"
                                    placeholder="{{ __('Enter Document Name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> {{ __('Update') }}
                            </button>

                            <a href="{{ route('inquiry.documents.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
