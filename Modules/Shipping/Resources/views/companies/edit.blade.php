@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('شركات الشحن'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('شركات الشحن'), 'url' => route('companies.index')],
            ['label' => __('تعديل')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('تعديل شركة شحن') }}</h2>
                </div>
                <div class="card-body">
                    {{-- @can('تعديل شركات الشحن') --}}
                        <form action="{{ route('companies.update', $company) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="name">{{ __('الاسم') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="{{ __('ادخل اسم الشركة') }}" value="{{ old('name', $company->name) }}">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="email">{{ __('البريد الإلكتروني') }}</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="{{ __('example@email.com') }}" value="{{ old('email', $company->email) }}">
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="phone">{{ __('الهاتف') }}</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        placeholder="{{ __('ادخل رقم الهاتف') }}" value="{{ old('phone', $company->phone) }}">
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="address">{{ __('العنوان') }}</label>
                                    <textarea class="form-control" id="address" name="address" placeholder="{{ __('العنوان الكامل') }}">{{ old('address', $company->address) }}</textarea>
                                    @error('address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="base_rate">{{ __('السعر الأساسي') }}</label>
                                    <input type="number" class="form-control" id="base_rate" name="base_rate" step="0.01"
                                        placeholder="{{ __('ادخل السعر الأساسي') }}"
                                        value="{{ old('base_rate', $company->base_rate) }}">
                                    @error('base_rate')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="is_active">{{ __('الحالة') }}</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="1"
                                            {{ old('is_active', $company->is_active) == 1 ? 'selected' : '' }}>
                                            {{ __('نشط') }}</option>
                                        <option value="0"
                                            {{ old('is_active', $company->is_active) == 0 ? 'selected' : '' }}>
                                            {{ __('غير نشط') }}</option>
                                    </select>
                                    @error('is_active')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-start mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="las la-save"></i> {{ __('حفظ') }}
                                </button>
                                <a href="{{ route('companies.index') }}" class="btn btn-danger">
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
