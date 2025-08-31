@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('العملاء'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('العملاء'), 'url' => route('clients.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>تعديل عميل</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('crm.clients.update', $client->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="name">الاسم</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $client->name) }}" placeholder="ادخل الاسم">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="type">النوع</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="person" {{ old('type', $client->type) == 'person' ? 'selected' : '' }}>
                                        شخص</option>
                                    <option value="company" {{ old('type', $client->type) == 'company' ? 'selected' : '' }}>
                                        شركة</option>
                                </select>
                                @error('type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="phone">الهاتف</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone', $client->phone) }}" placeholder="ادخل رقم الهاتف">
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="email">الإيميل</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $client->email) }}" placeholder="example@email.com">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="address">العنوان</label>
                                <input type="text" class="form-control" id="address" name="address"
                                    value="{{ old('address', $client->address) }}" placeholder="العنوان الكامل">
                                @error('address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="notes">الملاحظات</label>
                                <input type="text" class="form-control" id="notes" name="notes"
                                    value="{{ old('notes', $client->notes) }}" placeholder="أي ملاحظات إضافية">
                                @error('notes')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> حفظ التعديلات
                            </button>

                            <a href="{{ route('clients.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
