@extends('progress::layouts.app')

@section('title', __('activity-logs.activities') . ' - ' . class_basename($subject))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-history me-2"></i>
                        {{ __('activity-logs.activities') }} {{ __('activity-logs.for') }} {{ class_basename($subject) }} #{{ $subject->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('progress.activity-logs.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('activity-logs.back_to_all_activities') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    
                    <div class="alert alert-info shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-file-alt fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ class_basename($subject) }} #{{ $subject->id }}</h6>
                                <p class="mb-0">
                                    @if(method_exists($subject, 'name'))
                                        <strong>{{ __('general.name') }}:</strong> {{ $subject->name }}
                                    @elseif(method_exists($subject, 'title'))
                                        <strong>{{ __('general.title') }}:</strong> {{ $subject->title }}
                                    @elseif(method_exists($subject, 'description'))
                                        <strong>{{ __('general.description') }}:</strong> {{ Str::limit($subject->description, 100) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    
                    <form method="GET" action="{{ route('progress.activity-logs.subject-activities', [class_basename($subject), $subject->id]) }}"
                          class="row g-2 align-items-end mb-4">
                        <div class="col-md-4">
                            <label for="log_name" class="form-label">{{ __('activity-logs.log_name') }}</label>
                            <select name="log_name" id="log_name" class="form-select form-select-sm">
                                <option value="">{{ __('activity-logs.all_logs') }}</option>
                                @foreach($activities->pluck('log_name')->filter()->unique() as $logName)
                                    <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                        {{ $logName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter"></i> {{ __('activity-logs.filter') }}
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('progress.activity-logs.subject-activities', [class_basename($subject), $subject->id]) }}"
                               class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-times"></i> {{ __('activity-logs.clear') }}
                            </a>
                        </div>
                    </form>

                    
                    <div class="table-responsive mb-4">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('activity-logs.description') }}</th>
                                    <th>{{ __('activity-logs.event') }}</th>
                                    <th>{{ __('activity-logs.causer') }}</th>
                                    <th>{{ __('activity-logs.log_name') }}</th>
                                    <th>{{ __('activity-logs.date') }}</th>
                                    <th>{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->id }}</td>
                                        <td>{{ $activity->description }}</td>
                                        <td>
                                            <span class="badge bg-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : ($activity->event == 'deleted' ? 'danger' : 'info')) }}">
                                                <i class="fas fa-{{ $activity->event == 'created' ? 'plus' : ($activity->event == 'updated' ? 'edit' : ($activity->event == 'deleted' ? 'trash' : 'info-circle')) }}"></i>
                                                {{ ucfirst($activity->event ?? 'custom') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($activity->causer)
                                                <a href="{{ route('progress.activity-logs.user-activities', $activity->causer->id) }}"
                                                   class="text-decoration-none">
                                                    <i class="fas fa-user text-primary"></i> {{ $activity->causer->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->log_name)
                                                <span class="badge bg-secondary">{{ $activity->log_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><i class="fas fa-clock text-muted"></i> {{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <a href="{{ route('progress.activity-logs.show', $activity) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i> {{ __('activity-logs.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ __('activity-logs.no_activities_for_subject') }} {{ class_basename($subject) }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="d-flex justify-content-center">
                        {{ $activities->links() }}
                    </div>

                    
                    <div class="alert alert-light border-start border-4 border-primary shadow-sm">
                        <strong><i class="fas fa-chart-bar text-primary"></i> {{ __('activity-logs.summary') }}:</strong>
                        {{ $activities->total() }} {{ __('activity-logs.activities_found') }} {{ __('activity-logs.for') }}
                        {{ class_basename($subject) }} #{{ $subject->id }}
                        @if(request('log_name'))
                            {{ __('activity-logs.in_log') }} <span class="badge bg-info">"{{ request('log_name') }}"</span>
                        @endif
                    </div>

                    
                    @if($activities->count() > 0)
                    <div class="mt-4">
                        <h5 class="mb-3"><i class="fas fa-stream text-primary"></i> {{ __('activity-logs.timeline_view') }}</h5>
                        <div class="position-relative ps-4 border-start border-3 border-secondary">
                            @foreach($activities as $activity)
                                <div class="mb-4">
                                    <span class="position-absolute top-0 start-0 translate-middle p-2 rounded-circle bg-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : ($activity->event == 'deleted' ? 'danger' : 'info')) }}">
                                        <i class="fas fa-{{ $activity->event == 'created' ? 'plus' : ($activity->event == 'updated' ? 'edit' : ($activity->event == 'deleted' ? 'trash' : 'info') }} text-white"></i>
                                    </span>
                                    <div class="ms-4">
                                        <h6>
                                            @if($activity->causer)
                                                <a href="{{ route('progress.activity-logs.user-activities', $activity->causer->id) }}" class="fw-bold text-decoration-none">
                                                    {{ $activity->causer->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                            <small class="text-muted">– {{ $activity->created_at->format('M d, Y H:i') }}</small>
                                        </h6>
                                        <p class="mb-1">{{ $activity->description }}</p>
                                        <a href="{{ route('progress.activity-logs.show', $activity) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> {{ __('activity-logs.view_details') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(function() {
    $('#log_name').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
