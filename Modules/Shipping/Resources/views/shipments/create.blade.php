@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الشحنات'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الشحنات'), 'url' => route('shipments.index')],
            ['label' => __('إنشاء')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('إضافة شحنة جديدة') }}</h2>
                </div>
                <div class="card-body">
                    {{-- @can('إضافة الشحنات') --}}
                    <form action="{{ route('shipments.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="tracking_number">{{ __('رقم التتبع') }}</label>
                                <input type="text" class="form-control" id="tracking_number" name="tracking_number"
                                    placeholder="{{ __('ادخل رقم التتبع') }}" value="{{ old('tracking_number') }}">
                                @error('tracking_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="shipping_company_id">{{ __('شركة الشحن') }}</label>
                                <select class="form-control" id="shipping_company_id" name="shipping_company_id">
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('shipping_company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('shipping_company_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="customer_name">{{ __('اسم العميل') }}</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                    placeholder="{{ __('ادخل اسم العميل') }}" value="{{ old('customer_name') }}">
                                @error('customer_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="customer_address">{{ __('عنوان العميل') }}</label>
                                <textarea class="form-control" id="customer_address" name="customer_address" placeholder="{{ __('العنوان الكامل') }}">{{ old('customer_address') }}</textarea>
                                @error('customer_address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="weight">{{ __('الوزن (كجم)') }}</label>
                                <input type="number" class="form-control" id="weight" name="weight" step="0.01"
                                    placeholder="{{ __('ادخل الوزن') }}" value="{{ old('weight') }}">
                                @error('weight')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="status">{{ __('الحالة') }}</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>
                                        {{ __('معلق') }}</option>
                                    <option value="in_transit" {{ old('status') == 'in_transit' ? 'selected' : '' }}>
                                        {{ __('في الطريق') }}</option>
                                    <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>
                                        {{ __('تم التسليم') }}</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('حفظ') }}
                            </button>
                            <a href="{{ route('shipments.index') }}" class="btn btn-danger">
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
