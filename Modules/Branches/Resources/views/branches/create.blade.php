@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.settings')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Branches'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Branches'), 'url' => route('branches.index')],
            ['label' => __('Create')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Add New Branch') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('branches.store') }}" method="POST" onsubmit="disableButton()">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">{{ __('Name') }}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="{{ __('Enter the name') }}" value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="code">{{ __('Branch Code') }}</label>
                                <input type="text" class="form-control" id="code" name="code"
                                    placeholder="{{ __('Enter branch code') }}" value="{{ old('code') }}">
                                @error('code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="address">{{ __('Address') }}</label>
                                <input type="text" class="form-control" id="address" name="address"
                                    placeholder="{{ __('Enter address') }}" value="{{ old('address') }}">
                                @error('address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-lg-4 d-flex align-items-center justify-content-between">
                                <label class="form-label mb-0" for="is_active">{{ __('Status') }}</label>

                                <!-- Hidden input لإرسال القيمة 0 لو checkbox مش متفعل -->
                                <input type="hidden" name="is_active" value="0">

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <span id="isActiveLabel">{{ old('is_active', 1) ? __('Active') : __('Inactive') }}</span>
                                    </label>
                                </div>

                                @error('is_active')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> {{ __('Save') }}
                            </button>

                            <a href="{{ route('branches.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // تغيير نص Label تلقائياً عند الضغط على Switch
        const switchInput = document.getElementById('is_active');
        const switchLabel = switchInput.nextElementSibling;

        switchInput.addEventListener('change', function() {
            switchLabel.textContent = this.checked ? '{{ __('Active') }}' : '{{ __('Inactive') }}';
        });
    </script>
@endpush
