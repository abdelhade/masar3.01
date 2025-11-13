@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Lead Status'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Lead Statuses'), 'url' => route('lead-status.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead-status.update', $leadStatus->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">{{ __('Title') }}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $leadStatus->name) }}" placeholder="{{ __('Enter the name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-2">
                                <label class="form-label" for="order_column">{{ __('Order') }}</label>
                                <input type="text" class="form-control" id="order_column" name="order_column"
                                    value="{{ old('order_column', (int) $leadStatus->order_column) }}"
                                    placeholder="{{ __('Enter the order') }}" pattern="\d*" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                @error('order_column')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="color">{{ __('Color') }}</label>
                                <input type="color" class="form-control form-control-color" id="color" name="color"
                                    value="{{ old('color', $leadStatus->color ?? '#563d7c') }}"
                                    title="{{ __('Choose your color') }}">
                                @error('color')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('Save') }}
                            </button>

                            <a href="{{ route('lead-status.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
