@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل المهمة'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('المهام'), 'url' => route('tasks.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>تعديل المهمة</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- اسم العميل --}}
                            <div class="mb-3 col-lg-4">
                                <label for="client_id" class="form-label">اسم العميل</label>
                                <select name="client_id" id="client_id" class="form-control">
                                    @foreach ($clients as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('client_id', $task->client_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- اسم المستخدم --}}
                            <div class="mb-3 col-lg-4">
                                <label for="user_id" class="form-label">المستخدم</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    @foreach ($users as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('user_id', $task->user_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- نوع المهمة --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="task_type_id">نوع المهمة</label>
                                <select name="task_type_id" id="task_type_id" class="form-control">
                                    <option value="">-- اختر نوع المهمة --</option>
                                    @foreach ($taskTypes as $id => $title)
                                        <option value="{{ $id }}"
                                            {{ old('task_type_id', $task->task_type_id ?? '') == $id ? 'selected' : '' }}>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('task_type_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>


                            {{-- العنوان --}}
                            <div class="mb-3 col-lg-4">
                                <label for="title" class="form-label">عنوان المهمة</label>
                                <input type="text" name="title" id="title" class="form-control"
                                    value="{{ old('title', $task->title) }}">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الأولوية --}}
                            <div class="mb-3 col-lg-2">
                                <label for="priority" class="form-label">الأولوية</label>
                                <select name="priority" id="priority" class="form-control">
                                    @foreach (\Modules\CRM\Enums\TaskPriorityEnum::cases() as $priority)
                                        <option value="{{ $priority->value }}"
                                            {{ old('priority', $task->priority) == $priority->value ? 'selected' : '' }}>
                                            {{ $priority->value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الحالة --}}
                            <div class="mb-3 col-lg-2">
                                <label for="status" class="form-label">حالة المهمة</label>
                                <select name="status" id="status" class="form-control">
                                    @foreach (\Modules\CRM\Enums\TaskStatusEnum::cases() as $status)
                                        <option value="{{ $status->value }}"
                                            {{ old('status', $task->status) == $status->value ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تاريخ البداية --}}
                            <div class="mb-3 col-lg-2">
                                <label for="start_date" class="form-label">تاريخ البداية</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ old('start_date', \Carbon\Carbon::parse($task->start_date)->format('Y-m-d')) }}">
                                @error('start_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تاريخ التسليم --}}
                            <div class="mb-3 col-lg-2">
                                <label for="delivery_date" class="form-label">تاريخ التسليم</label>
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control"
                                    value="{{ old('delivery_date', \Carbon\Carbon::parse($task->delivery_date)->format('Y-m-d')) }}">
                                @error('delivery_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تعليق العميل --}}
                            <div class="mb-3 col-lg-6">
                                <label for="client_comment" class="form-label">تعليق العميل</label>
                                <textarea name="client_comment" id="client_comment" class="form-control">{{ old('client_comment', $task->client_comment) }}</textarea>
                                @error('client_comment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- تعليق المندوب --}}
                            <div class="mb-3 col-lg-6">
                                <label for="user_comment" class="form-label">تعليق المندوب</label>
                                <textarea name="user_comment" id="user_comment" class="form-control">{{ old('user_comment', $task->user_comment) }}</textarea>
                                @error('user_comment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- المرفقات --}}
                            <div class="mb-3 col-lg-12">
                                <label for="attachment" class="form-label">مرفق (صورة أو ملف)</label>
                                <input type="file" name="attachment" id="attachment" class="form-control">
                                @if ($task->getFirstMediaUrl('attachments'))
                                    <p class="mt-2">
                                        <a href="{{ $task->getFirstMediaUrl('attachments') }}" target="_blank">عرض المرفق
                                            الحالي</a>
                                    </p>
                                @endif
                                @error('attachment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- زر الحفظ --}}
                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-success me-2">
                                <i class="las la-save"></i> تحديث
                            </button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
