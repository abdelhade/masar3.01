@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('انواع المهمات'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('انواع المهمات'), 'url' => route('tasks.types.index')],
            ['label' => __('انشاء')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>اضافة جديده</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.types.store') }}" method="POST">
                        @csrf
                        <div class="mb-3 col-lg-4">
                            <label class="form-label" for="title">العنوان</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="ادخل الاسم">
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2"><i class="las la-save"></i> حفظ</button>
                            <a href="{{ route('tasks.types.index') }}" class="btn btn-danger"><i class="las la-times"></i>
                                إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
