@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Task'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Tasks'), 'url' => route('tasks.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Task') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Client Name --}}
                            <div class="col-md-4 mb-3">
                                <x-dynamic-search name="client_id" :label="__('Client')" column="cname"
                                    model="App\Models\Client" :placeholder="__('Search for client...')" :required="false" :class="'form-select'"
                                    :selected="$task->client_id" />
                            </div>

                            {{-- User Name --}}
                            <div class="mb-3 col-lg-4">
                                <label for="user_id" class="form-label">{{ __('User') }}</label>
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

                            {{-- Task Type --}}
                            <div class="mb-3 col-lg-4">
                                <label class="form-label" for="task_type_id">{{ __('Task Type') }}</label>
                                <select name="task_type_id" id="task_type_id" class="form-control">
                                    <option value="">{{ __('-- Select Task Type --') }}</option>
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

                            {{-- Title --}}
                            <div class="mb-3 col-lg-4">
                                <label for="title" class="form-label">{{ __('Task Title') }}</label>
                                <input type="text" name="title" id="title" class="form-control"
                                    value="{{ old('title', $task->title) }}">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Priority --}}
                            <div class="mb-3 col-lg-2">
                                <label for="priority" class="form-label">{{ __('Priority') }}</label>
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

                            {{-- Status --}}
                            <div class="mb-3 col-lg-2">
                                <label for="status" class="form-label">{{ __('Task Status') }}</label>
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

                            {{-- Start Date --}}
                            <div class="mb-3 col-lg-2">
                                <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ old('start_date', \Carbon\Carbon::parse($task->start_date)->format('Y-m-d')) }}">
                                @error('start_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Due Date --}}
                            <div class="mb-3 col-lg-2">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control"
                                    value="{{ old('due_date', \Carbon\Carbon::parse($task->due_date)->format('Y-m-d')) }}">
                                @error('due_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Client Comment --}}
                            <div class="mb-3 col-lg-6">
                                <label for="client_comment" class="form-label">{{ __('Client Comment') }}</label>
                                <textarea name="client_comment" id="client_comment" class="form-control">{{ old('client_comment', $task->client_comment) }}</textarea>
                                @error('client_comment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- User Comment --}}
                            <div class="mb-3 col-lg-6">
                                <label for="user_comment" class="form-label">{{ __('User Comment') }}</label>
                                <textarea name="user_comment" id="user_comment" class="form-control">{{ old('user_comment', $task->user_comment) }}</textarea>
                                @error('user_comment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Attachment --}}
                            <div class="mb-3 col-lg-12">
                                <label for="attachment" class="form-label">{{ __('Attachment (Image or File)') }}</label>
                                <input type="file" name="attachment" id="attachment" class="form-control">
                                @if ($task->getFirstMediaUrl('attachments'))
                                    <p class="mt-2">
                                        <a href="{{ $task->getFirstMediaUrl('attachments') }}"
                                            target="_blank">{{ __('View Current Attachment') }}</a>
                                    </p>
                                @endif
                                @error('attachment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-success me-2">
                                <i class="las la-save"></i> {{ __('Update') }}
                            </button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
