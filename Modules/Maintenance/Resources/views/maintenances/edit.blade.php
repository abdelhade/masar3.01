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
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('تعديل عملية صيانة') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('maintenances.update', $maintenance->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            {{-- اسم العميل --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="client_name">{{ __('اسم العميل') }}</label>
                                <input type="text" class="form-control" id="client_name" name="client_name"
                                    placeholder="ادخل اسم العميل"
                                    value="{{ old('client_name', $maintenance->client_name) }}">
                                @error('client_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- رقم التليفون --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="client_phone">{{ __('رقم التليفون') }}</label>
                                <input type="text" class="form-control" id="client_phone" name="client_phone"
                                    placeholder="ادخل رقم التليفون"
                                    value="{{ old('client_phone', $maintenance->client_phone) }}">
                                @error('client_phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- البند --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="item_name">{{ __('البند') }}</label>
                                <input type="text" class="form-control" id="item_name" name="item_name"
                                    placeholder="ادخل البند" value="{{ old('item_name', $maintenance->item_name) }}">
                                @error('item_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- التاريخ --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="date">{{ __('التاريخ') }}</label>
                                <input type="date" class="form-control" id="date" name="date"
                                    value="{{ old('date', $maintenance->date?->format('Y-m-d')) }}">
                                @error('date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تاريخ الاستحقاق --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="due_date">{{ __('تاريخ الاستحقاق') }}</label>
                                <input type="date" class="form-control" id="due_date" name="due_date"
                                    value="{{ old('due_date', $maintenance->due_date?->format('Y-m-d')) }}">
                                @error('due_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>


                            {{-- رقم البند --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="item_number">{{ __('رقم البند') }}</label>
                                <input type="text" class="form-control" id="item_number" name="item_number"
                                    placeholder="ادخل رقم البند"
                                    value="{{ old('item_number', $maintenance->item_number) }}">
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
                                        <option value="{{ $type->id }}" @selected(old('service_type_id', $maintenance->service_type_id) == $type->id)>
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
                                        <option value="{{ $status->value }}" @selected(old('status', $maintenance->status->value) == $status->value)>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('حفظ التعديل') }}
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
