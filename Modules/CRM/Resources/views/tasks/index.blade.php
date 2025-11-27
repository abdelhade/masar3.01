@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Tasks'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Tasks')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Tasks')
                <a href="{{ route('tasks.create') }}" class="btn btn-main font-hold fw-bold">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add New Task') }}
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="tasks-table" filename="tasks-table" :excel-label="__('Export Excel')"
                            :pdf-label="__('Export PDF')" :print-label="__('Print')" />

                        <table id="tasks-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Task Type') }}</th>
                                    <th>{{ __('Task Title') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    @canany(['edit Tasks', 'delete Tasks'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $task)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ optional($task->client)->cname }}</td>
                                        <td>{{ optional($task->user)->name }}</td>
                                        <td>{{ $task->taskType->title }}</td>
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
                                                @php
                                                    $media = $task->getFirstMedia('tasks');
                                                @endphp
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#attachmentModal{{ $task->id }}">
                                                    <i class="fas fa-paperclip"></i> {{ __('View') }}
                                                </button>

                                                <div class="modal fade" id="attachmentModal{{ $task->id }}"
                                                    tabindex="-1" aria-labelledby="attachmentModalLabel"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="attachmentModalLabel">
                                                                    {{ __('Task Attachment') }}</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                @if ($media && Str::contains($media->mime_type, 'image'))
                                                                    <img src="{{ $media->getUrl('thumb') }}"
                                                                        class="img-fluid"
                                                                        alt="{{ __('Task Attachment') }}">
                                                                @else
                                                                    <div class="d-flex justify-content-center">
                                                                        <a href="{{ $media->getUrl() }}"
                                                                            class="btn btn-primary" download>
                                                                            <i
                                                                                class="fas fa-download me-2"></i>{{ __('Download File') }}
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">{{ __('Close') }}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('No Attachment') }}</span>
                                            @endif
                                        </td>
                                        @canany(['edit Tasks', 'delete Tasks'])
                                            <td>
                                                @can('edit Tasks')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('tasks.edit', $task->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete Tasks')
                                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this task?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No tasks added yet') }}
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
