@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('السائقون'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('السائقون'), 'url' => route('drivers.index')],
            ['label' => __('إنشاء')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('إضافة سائق جديد') }}</h2>
                </div>
                <div class="card-body">
                    {{-- @can('إضافة السائقين') --}}
                        <form action="{{ route('drivers.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="name">{{ __('الاسم') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="{{ __('ادخل اسم السائق') }}" value="{{ old('name') }}">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="phone">{{ __('رقم الهاتف') }}</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        placeholder="{{ __('ادخل رقم الهاتف') }}" value="{{ old('phone') }}">
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="vehicle_type">{{ __('نوع المركبة') }}</label>
                                    <input type="text" class="form-control" id="vehicle_type" name="vehicle_type"
                                        placeholder="{{ __('ادخل نوع المركبة') }}" value="{{ old('vehicle_type') }}">
                                    @error('vehicle_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="is_available">{{ __('الحالة') }}</label>
                                    <select class="form-control" id="is_available" name="is_available">
                                        <option value="1" {{ old('is_available', 1) == 1 ? 'selected' : '' }}>
                                            {{ __('متاح') }}</option>
                                        <option value="0" {{ old('is_available') == 0 ? 'selected' : '' }}>
                                            {{ __('غير متاح') }}</option>
                                    </select>
                                    @error('is_available')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-start mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="las la-save"></i> {{ __('حفظ') }}
                                </button>
                                <a href="{{ route('drivers.index') }}" class="btn btn-danger">
                                    <i class="las la-times"></i> {{ __('إلغاء') }}
                                </a>
                            </div>
                        </form>
                    {{-- @endcan --}}
                </div>
            </div>
        </div>
    </div>
@endsection
