@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الصيانة'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الصيانة'), 'url' => route('maintenances.index')],
            ['label' => __('إنشاء')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('إضافة عملية صيانة جديدة') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('maintenances.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- اسم العميل --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="client_name">{{ __('اسم العميل') }}</label>
                                <input type="text" class="form-control" id="client_name" name="client_name"
                                    placeholder="ادخل اسم العميل" value="{{ old('client_name') }}">
                                @error('client_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- رقم التليفون --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="client_phone">{{ __('رقم التليفون') }}</label>
                                <input type="text" class="form-control" id="client_phone" name="client_phone"
                                    placeholder="ادخل رقم التليفون" value="{{ old('client_phone') }}">
                                @error('client_phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- البند --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="item_name">{{ __('البند') }}</label>
                                <input type="text" class="form-control" id="item_name" name="item_name"
                                    placeholder="ادخل البند" value="{{ old('item_name') }}">
                                @error('item_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- التاريخ --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="date">{{ __('تاريخ الصيانة') }}</label>
                                <input type="date" class="form-control" id="date" name="date"
                                    value="{{ old('date') }}">
                                @error('date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تاريخ الاستحقاق --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="accural_date">{{ __('تاريخ الاستحقاق') }}</label>
                                <input type="date" class="form-control" id="accural_date" name="accural_date"
                                    value="{{ old('accural_date') }}">
                                @error('accural_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- رقم البند --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="item_number">{{ __('رقم البند') }}</label>
                                <input type="text" class="form-control" id="item_number" name="item_number"
                                    placeholder="ادخل رقم البند" value="{{ old('item_number') }}">
                                @error('item_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- نوع الصيانة --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="service_type_id">{{ __('نوع الصيانة') }}</label>
                                <select class="form-select" id="service_type_id" name="service_type_id">
                                    <option value="">{{ __('اختر نوع الصيانة') }}</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" @selected(old('service_type_id') == $type->id)>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_type_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الحالة --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="status">{{ __('الحالة') }}</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">{{ __('اختر الحالة') }}</option>
                                    @foreach (\Modules\Maintenance\Enums\MaintenanceStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected(old('status') == $status->value)>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <x-branches::branch-select :branches="$branches" />
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('حفظ') }}
                            </button>

                            <a href="{{ route('maintenances.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('إلغاء') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
