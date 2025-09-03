@extends('admin.dashboard')

@section('title', __('general.add_new_work_item'))

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.add_new_work_item'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('general.work_items_management'), 'url' => route('work.items.index')],
            ['label' => __('انشاء')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('general.add_new_work_item') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('work.items.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-lg-6">
                                <label class="form-label">{{ __('general.item_name') }}</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-6">
                                <label class="form-label">{{ __('general.unit_of_measurement') }}</label>
                                <input type="text" name="unit"
                                    class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit') }}"
                                    required>
                                @error('unit')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('general.description') }}</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('general.save') }}
                            </button>
                            <a href="{{ route('work.items.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('general.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
