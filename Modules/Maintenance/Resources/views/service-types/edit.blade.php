@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('أنواع الصيانة'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('أنواع الصيانة'), 'url' => route('service.types.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('تعديل نوع الصيانة') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('service.types.update', $type->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">{{ __('الاسم') }}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="ادخل الاسم" value="{{ old('name', $type->name) }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-8">
                                <label class="form-label" for="description">{{ __('الوصف') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="ادخل الوصف">{{ old('description', $type->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> {{ __('تحديث') }}
                            </button>

                            <a href="{{ route('service.types.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('إلغاء') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
