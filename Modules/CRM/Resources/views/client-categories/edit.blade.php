@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تصنيفات العملاء'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('تصنيفات العملاء'), 'url' => route('client.categories.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>تعديل التصنيف</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.categories.update', $category->id) }}" method="POST"
                        onsubmit="disableButton()">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- الاسم -->
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">الاسم</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $category->name) }}" placeholder="ادخل الاسم">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- الوصف -->
                            <div class="mb-3 col-lg-8">
                                <label class="form-label" for="description">الوصف</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="اكتب وصف للتصنيف">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> حفظ التعديلات
                            </button>

                            <a href="{{ route('client.categories.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
