@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Tasks Kanban'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Tasks'), 'url' => route('tasks.index')],
            ['label' => __('Kanban')],
        ],
    ])

    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> {{ __('List View') }}
            </a>
            @can('create Tasks')
                <a href="{{ route('tasks.create') }}" class="btn btn-main">
                    <i class="fas fa-plus"></i> {{ __('Add New Task') }}
                </a>
            @endcan
        </div>
    </div>

    <!-- Filters (Optional - can be expanded) -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('tasks.kanban') }}" method="GET" class="row align-items-center g-2">
                <div class="col-auto">
                    <label class="fw-bold">{{ __('Filter By:') }}</label>
                </div>
                <div class="col-auto">
                    <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All Users') }}</option>
                        @foreach (\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="kanban-board-container" style="overflow-x: auto; min-height: 70vh;" x-data="kanbanBoard()">
        <div class="d-flex flex-nowrap pb-3" style="gap: 1rem;">
            @foreach ($statuses as $status)
                <div class="kanban-column card border-top-0"
                    style="min-width: 300px; max-width: 300px; background-color: #f8f9fa;">
                    <div class="card-header bg-white border-bottom-0 border-top-3 border-{{ $status->color() }} pt-3 pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-{{ $status->color() }}">
                                {{ $status->label() }}
                            </h6>
                            <span class="badge bg-light text-dark rounded-pill border">
                                {{ $tasks->where('status', $status->value)->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-2" @dragover.prevent="dragOver($event)"
                        @drop="drop($event, '{{ $status->value }}')"
                        style="overflow-y: auto; max-height: calc(100vh - 250px);">

                        @forelse ($tasks->where('status', $status->value) as $task)
                            <div class="card mb-2 shadow-sm task-card" draggable="true"
                                @dragstart="dragStart($event, {{ $task->id }})"
                                style="cursor: grab; border-left: 3px solid var(--bs-{{ (is_string($task->priority) ? \Modules\CRM\Enums\TaskPriorityEnum::tryFrom($task->priority)?->color() : $task->priority?->color()) ?? 'secondary' }});">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted text-uppercase" style="font-size: 0.7rem;">
                                            {{ $task->taskType->title ?? __('Task') }}
                                        </small>

                                        @php
                                            $priority = is_string($task->priority)
                                                ? \Modules\CRM\Enums\TaskPriorityEnum::tryFrom($task->priority)
                                                : $task->priority;
                                            $pColor = $priority ? $priority->color() : 'secondary';
                                            $pLabel = $priority ? $priority->label() : __('Normal');
                                        @endphp
                                        <span class="badge bg-soft-{{ $pColor }} text-{{ $pColor }}"
                                            style="font-size: 0.65rem;">
                                            {{ $pLabel }}
                                        </span>
                                    </div>

                                    <h6 class="card-title mb-2 text-truncate" title="{{ $task->title }}">
                                        <a href="{{ route('tasks.edit', $task->id) }}"
                                            class="text-dark text-decoration-none">
                                            {{ $task->title }}
                                        </a>
                                    </h6>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">
                                            <i class="las la-user-circle"></i>
                                            {{ optional($task->client)->cname ?? __('No Client') }}
                                        </small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <div class="d-flex align-items-center">
                                            @if (optional($task->user)->avatar)
                                                <img src="{{ optional($task->user)->avatar }}" class="rounded-circle me-1"
                                                    width="20" height="20">
                                            @else
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-1"
                                                    style="width: 20px; height: 20px; font-size: 10px;">
                                                    {{ substr(optional($task->user)->name ?? 'U', 0, 1) }}
                                                </div>
                                            @endif
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                {{ Str::limit(optional($task->user)->name, 10) }}
                                            </small>
                                        </div>

                                        <small
                                            class="text-{{ \Carbon\Carbon::parse($task->due_date)->isPast() && $status->value != 'مكتملة' ? 'danger' : 'muted' }}"
                                            style="font-size: 0.75rem;">
                                            <i class="las la-calendar"></i>
                                            {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-3 text-muted dashed-border">
                                <small>{{ __('No tasks') }}</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function kanbanBoard() {
            return {
                draggingTaskId: null,

                dragStart(event, taskId) {
                    this.draggingTaskId = taskId;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', taskId);
                    // Visual feedback
                    event.target.style.opacity = '0.5';
                },

                dragOver(event) {
                    event.preventDefault(); // Necessary to allow dropping
                    event.dataTransfer.dropEffect = 'move';
                    return false;
                },

                drop(event, newStatus) {
                    event.preventDefault();
                    // Reset opacity
                    document.querySelectorAll('.task-card').forEach(el => el.style.opacity = '1');

                    const taskId = this
                        .draggingTaskId; // event.dataTransfer.getData('text/plain'); // Sometimes dataTransfer is empty in Chrome on drop if not handled right

                    if (!taskId) return;

                    // AJAX Update
                    fetch('{{ route('tasks.updateStatus') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                task_id: taskId,
                                status: newStatus
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reload to reflect changes
                                window.location.reload();
                            } else {
                                alert('Error updating status');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Something went wrong');
                        });
                }
            }
        }
    </script>

    <style>
        .kanban-column {
            transition: all 0.3s ease;
        }

        .bg-soft-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .bg-soft-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .bg-soft-secondary {
            background-color: rgba(108, 117, 125, 0.1);
        }
    </style>
@endsection
