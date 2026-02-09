@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Activity Log'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Activity Log')],
        ],
    ])

    <div class="row">
        <div class="col-12">
            {{-- Filters --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('activitylog.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('User') }}</label>
                                <select name="user_id" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Activity Type') }}</label>
                                <select name="event" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($events as $event)
                                        <option value="{{ $event }}"
                                            {{ request('event') == $event ? 'selected' : '' }}>
                                            {{ $event }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Object Type') }}</label>
                                <select name="subject_type" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($subjectTypes as $type)
                                        <option value="{{ $type }}"
                                            {{ request('subject_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('From Date') }}</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('To Date') }}</label>
                                <input type="date" name="date_to" class="form-control"
                                    value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> {{ __('Search') }}
                                </button>
                                <a href="{{ route('activitylog.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt me-2"></i>
                        {{ __('User Activity Log') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Activity Type') }}</th>
                                    <th>{{ __('Object') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration + ($activities->currentPage() - 1) * $activities->perPage() }}
                                        </td>
                                        <td>
                                            @if ($activity->causer)
                                                <span class="fw-bold">{{ $activity->causer->name }}</span>
                                                <br>
                                                <small class="text-muted">{{ $activity->causer->email }}</small>
                                            @else
                                                <span class="text-muted">{{ __('System') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $activity->description }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $eventColors = [
                                                    'created' => 'success',
                                                    'updated' => 'warning',
                                                    'deleted' => 'danger',
                                                ];
                                                $color = $eventColors[$activity->event] ?? 'info';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ $activity->event ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($activity->subject)
                                                <span class="badge bg-secondary">
                                                    {{ class_basename($activity->subject_type) }}
                                                </span>
                                                <br>
                                                <small class="text-muted">ID: {{ $activity->subject_id }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $activity->created_at->format('Y-m-d H:i:s') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('activitylog.show', $activity->id) }}"
                                                class="btn btn-sm btn-info" title="{{ __('View Details') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                {{ __('No activities') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
