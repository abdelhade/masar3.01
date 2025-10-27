@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('أنواع العملاء'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('أنواع العملاء'), 'url' => route('client-types.index')],
            ['label' => __('إضافة جديده')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>اضافة نوع عميل جديد</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('client-types.store') }}" method="POST" onsubmit="disableButton()">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="title">الاسم</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    placeholder="ادخل اسم النوع">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <x-branches::branch-select :branches="$branches" />
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> حفظ
                            </button>

                            <a href="{{ route('client-types.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
