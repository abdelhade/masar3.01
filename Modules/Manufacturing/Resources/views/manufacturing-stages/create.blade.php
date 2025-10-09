@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('مراحل التصنيع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('مراحل التصنيع'), 'url' => route('manufacturing.stages.index')],
            ['label' => __('إنشاء')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-header">
                    <h2>إضافة مرحلة جديدة</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('manufacturing.stages.store') }}" method="POST" onsubmit="disableButton()">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="name">اسم المرحلة</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="ادخل اسم المرحلة" value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="order">الترتيب</label>
                                <input type="number" class="form-control" id="order" name="order"
                                    placeholder="ادخل ترتيب المرحلة" value="{{ old('order') }}">
                                @error('order')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="estimated_duration">المدة التقديرية (ساعة)</label>
                                <input type="number" step="0.1" class="form-control" id="estimated_duration"
                                    name="estimated_duration" placeholder="ادخل المدة بالساعات"
                                    value="{{ old('estimated_duration') }}">
                                @error('estimated_duration')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="cost">التكلفة</label>
                                <input type="number" step="0.01" class="form-control" id="cost" name="cost"
                                    placeholder="ادخل تكلفة المرحلة" value="{{ old('cost') }}">
                                @error('cost')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="is_active">الحالة</label>
                                <select name="is_active" id="is_active" class="form-control">
                                    <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                                @error('is_active')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-8">
                                <label class="form-label" for="description">الوصف</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="ادخل وصف المرحلة">{{ old('description') }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <x-branches::branch-select :branches="$branches" />


                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> حفظ
                            </button>

                            <a href="{{ route('manufacturing.stages.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
