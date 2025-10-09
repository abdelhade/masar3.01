@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('مراحل التصنيع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('مراحل التصنيع'), 'url' => route('manufacturing.stages.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>تعديل مرحلة التصنيع: {{ $manufacturingStage->name }}</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('manufacturing.stages.update', $manufacturingStage->id) }}" method="POST"
                        onsubmit="disableButton()">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">اسم المرحلة <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="ادخل اسم المرحلة" value="{{ old('name', $manufacturingStage->name) }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-2">
                                <label class="form-label" for="order">الترتيب <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="order" name="order" min="1"
                                    value="{{ old('order', (int) $manufacturingStage->order) }}">
                                @error('order')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="estimated_duration">المدة التقديرية (ساعات)</label>
                                <input type="number" class="form-control" id="estimated_duration" name="estimated_duration"
                                    step="0.01" min="0"
                                    value="{{ old('estimated_duration', $manufacturingStage->estimated_duration) }}">
                                @error('estimated_duration')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="cost">التكلفة <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="cost" name="cost" step="0.01"
                                    min="0" value="{{ old('cost', $manufacturingStage->cost) }}">
                                @error('cost')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">الوصف</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="اكتب وصف المرحلة">{{ old('description', $manufacturingStage->description) }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $manufacturingStage->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">نشط</label>
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
