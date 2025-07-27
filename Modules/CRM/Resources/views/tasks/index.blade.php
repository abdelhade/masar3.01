@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('المهام'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('المهام')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة المهام') --}}
            <a href="{{ route('tasks.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                اضافه مهمة جديدة
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('العميل') }}</th>
                                    <th>{{ __('المستخدم') }}</th>
                                    <th>{{ __('نوع المهمة') }}</th>
                                    <th>{{ __('عنوان المهمة') }}</th>
                                    <th>{{ __('الأولوية') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('تاريخ البدايه') }}</th>
                                    <th>{{ __('تاريخ التسليم') }}</th>
                                    <th>{{ __('مرفق') }}</th>
                                    {{-- @canany(['تعديل المهام', 'حذف المهام']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $task)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ optional($task->client)->name }}</td>
                                        <td>{{ optional($task->user)->name }}</td>
                                        <td>{{ $task->task_type }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td>
                                            @php
                                                $priority = is_string($task->priority)
                                                    ? \Modules\CRM\Enums\TaskPriorityEnum::tryFrom($task->priority)
                                                    : $task->priority;
                                            @endphp

                                            @if ($priority)
                                                <span class="badge bg-{{ $priority->color() }}">
                                                    {{ $priority->label() }}
                                                </span>
                                            @endif

                                        </td>
                                        <td>
                                            @php
                                                $status = is_string($task->status)
                                                    ? \Modules\CRM\Enums\TaskStatusEnum::tryFrom($task->status)
                                                    : $task->status;
                                            @endphp

                                            @if ($status)
                                                <span class="badge bg-{{ $status->color() }}">
                                                    {{ $status->label() }}
                                                </span>
                                            @endif

                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') }}</td>
                                        <td>
                                            @if ($task->hasMedia('tasks'))
                                                <a href="{{ $task->getFirstMediaUrl('tasks') }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-paperclip"></i> عرض
                                                </a>
                                            @else
                                                <span class="text-muted">لا يوجد</span>
                                            @endif
                                        </td>
                                        {{-- @canany(['تعديل المهام', 'حذف المهام']) --}}
                                        <td>
                                            {{-- @can('تعديل المهام') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('tasks.edit', $task->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف المهام') --}}
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                            {{-- @endcan --}}
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد مهام مضافة حتى الآن
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
