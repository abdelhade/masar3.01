@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('جهات اتصال الشركات'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('جهات اتصال الشركات'), 'url' => route('client-contacts.index')],
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
                    <form action="{{ route('client-contacts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <x-dynamic-search name="client_id" label="الشركة" column="cname" model="App\Models\Client"
                                    placeholder="ابحث عن شركة..." :required="false" :filters="['type' => [2, 3, 4, 5, 6]]" :class="'form-select'" />
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">الاسم</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="email">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email') }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="phone">الهاتف</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone') }}">
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="position">المنصب</label>
                                <input type="text" class="form-control" id="position" name="position"
                                    value="{{ old('position') }}">
                                @error('position')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                                <i class="las la-save"></i> حفظ
                            </button>

                            <a href="{{ route('client-contacts.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
