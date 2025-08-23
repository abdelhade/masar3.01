@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الأنشطة'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('الأنشطة'), 'url' => route('activities.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>تعديل نشاط</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('activities.update', $activity->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">

                            {{-- عنوان النشاط --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="title">عنوان النشاط</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    placeholder="ادخل عنوان النشاط" value="{{ old('title', $activity->title) }}">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- نوع النشاط --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="type">النوع</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="0" {{ old('type', $activity->type) == 0 ? 'selected' : '' }}>مكالمة
                                    </option>
                                    <option value="1" {{ old('type', $activity->type) == 1 ? 'selected' : '' }}>رسالة
                                    </option>
                                    <option value="2" {{ old('type', $activity->type) == 2 ? 'selected' : '' }}>اجتماع
                                    </option>
                                </select>
                                @error('type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تاريخ النشاط --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="activity_date">تاريخ النشاط</label>
                                <input type="date" class="form-control" id="activity_date" name="activity_date"
                                    value="{{ old('activity_date', $activity->activity_date?->format('Y-m-d')) }}">
                                @error('activity_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- وقت النشاط --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="scheduled_at">الوقت</label>
                                <input type="time" class="form-control" id="scheduled_at" name="scheduled_at"
                                    value="{{ old('scheduled_at', $activity->scheduled_at?->format('H:i')) }}">
                                @error('scheduled_at')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- العميل --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="client_id">العميل</label>
                                <select name="client_id" class="form-control">
                                    <option value="">اختر العميل</option>
                                    @foreach ($clients as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('client_id', $activity->client_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الموظف المكلف --}}
                            <div class="mb-3 col-lg-3">
                                <label class="form-label" for="assigned_to">المكلف</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">اختر الموظف</option>
                                    @foreach ($users as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('assigned_to', $activity->assigned_to) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الوصف --}}
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="description">الوصف</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="اكتب تفاصيل النشاط">{{ old('description', $activity->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> حفظ التعديلات
                            </button>

                            <a href="{{ route('activities.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
