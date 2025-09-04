@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل العميل'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('العملاء'), 'url' => route('clients.index')],
            ['label' => __('تعديل')],
        ],
    ])

    @push('styles')
        <style>
            .form-select {
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 10px 12px;
                transition: all 0.3s ease;
            }

            .form-select:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            }

            .status-container {
                background-color: #f8f9fa;
                border-radius: 8px;
                padding: 15px;
                border: 2px solid #e9ecef;
            }

            .form-switch .form-check-input:checked {
                background-color: #2821eb;
            }

            .form-switch .form-check-input:focus {
                box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
            }

            .status-label {
                font-weight: 600;
                color: #495057;
            }

            @media (max-width: 768px) {
                .status-container {
                    margin-top: 15px;
                }
            }
        </style>
    @endpush

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('clients.update', $client->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            {{-- الاسم --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">اسم العميل</label>
                                <input type="text" name="cname" class="form-control"
                                    value="{{ old('cname', $client->cname) }}">
                                @error('cname')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- البريد --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $client->email) }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الهاتف 1 --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">الهاتف 1</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $client->phone) }}">
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الهاتف 2 --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">الهاتف 2</label>
                                <input type="text" name="phone2" class="form-control"
                                    value="{{ old('phone2', $client->phone2) }}">
                            </div>

                            {{-- العنوان 1 --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">العنوان 1</label>
                                <input type="text" name="address" class="form-control"
                                    value="{{ old('address', $client->address) }}">
                            </div>

                            {{-- العنوان 2 --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">العنوان 2</label>
                                <input type="text" name="address2" class="form-control"
                                    value="{{ old('address2', $client->address2) }}">
                            </div>

                            {{-- تاريخ الميلاد --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">تاريخ الميلاد</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ old('date_of_birth', $client->date_of_birth ? $client->date_of_birth->format('Y-m-d') : '') }}">
                            </div>

                            {{-- الرقم القومي --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">الرقم القومي</label>
                                <input type="text" name="national_id" class="form-control"
                                    value="{{ old('national_id', $client->national_id) }}">
                            </div>

                            <div class="mb-3 col-lg-4">
                                <label class="form-label">الوظيفة</label>
                                <input type="text" name="job" class="form-control"
                                    value="{{ old('job', $client->job) }}">
                            </div>

                            {{-- شخص للتواصل --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">شخص للتواصل</label>
                                <input type="text" name="contact_person" class="form-control"
                                    value="{{ old('contact_person', $client->contact_person) }}">
                            </div>

                            {{-- هاتف التواصل --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">هاتف التواصل</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    value="{{ old('contact_phone', $client->contact_phone) }}">
                            </div>

                            {{-- العلاقة --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label">صلة القرابة</label>
                                <input type="text" name="contact_relation" class="form-control"
                                    value="{{ old('contact_relation', $client->contact_relation) }}">
                            </div>

                            {{-- معلومات إضافية --}}
                            <div class="mb-3 col-lg-12">
                                <label class="form-label">ملاحظات</label>
                                <textarea name="info" class="form-control" rows="2">{{ old('info', $client->info) }}</textarea>
                            </div>

                            {{-- النوع والحالة --}}
                            <div class="row g-3">
                                <!-- حقل النوع -->
                                <div class="mb-3 col-lg-4">
                                    <label class="form-label" for="type">الصفه</label>
                                    <select class="form-control" id="type" name="type">
                                        <option value="person" {{ old('type') == 'person' ? 'selected' : '' }}>شخص</option>
                                        <option value="company" {{ old('type') == 'company' ? 'selected' : '' }}>شركة
                                        </option>
                                    </select>
                                    @error('type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">النوع</label>
                                    <select name="gender" class="form-select">
                                        <option value="">اختر النوع</option>
                                        <option value="male"
                                            {{ old('gender', $client->gender) == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female"
                                            {{ old('gender', $client->gender) == 'female' ? 'selected' : '' }}>أنثى
                                        </option>
                                    </select>
                                </div>

                                <!-- حقل الحالة -->
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">الحالة</label>
                                    <div class="status-container d-flex align-items-center justify-content-between">
                                        <span class="status-label">نشط</span>
                                        <div class="form-check form-switch m-0">
                                            <input type="checkbox" class="form-check-input" id="is_active"
                                                name="is_active" value="1"
                                                {{ old('is_active', $client->is_active) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> تحديث
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
