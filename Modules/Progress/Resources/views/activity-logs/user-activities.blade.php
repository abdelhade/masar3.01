@extends('progress::layouts.app')

@section('title', __('activity-logs.activities') . ' - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-clock"></i> 
                        {{ __('activity-logs.activities') }} {{ __('activity-logs.by') }} {{ $user->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('progress.activity-logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('activity-logs.back_to_all_activities') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-user-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        <p class="mb-0">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('progress.activity-logs.user-activities', $user->id) }}" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="log_name" class="mr-2">{{ __('activity-logs.log_name') }}:</label>
                                    <select name="log_name" id="log_name" class="form-control form-control-sm">
                                        <option value="">{{ __('activity-logs.all_logs') }}</option>
                                        @foreach($activities->pluck('log_name')->filter()->unique() as $logName)
                                            <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                                {{ $logName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2">
                                    <button type="submit" class="btn btn-primary btn-sm">{{ __('activity-logs.filter') }}</button>
                                    <a href="{{ route('progress.activity-logs.user-activities', $user->id) }}" class="btn btn-secondary btn-sm">{{ __('activity-logs.clear') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('activity-logs.id') }}</th>
                                    <th>{{ __('activity-logs.description') }}</th>
                                    <th>{{ __('activity-logs.event') }}</th>
                                    <th>{{ __('activity-logs.subject') }}</th>
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
                                            <span class="badge badge-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : ($activity->event == 'deleted' ? 'danger' : 'info')) }}">
                                                {{ ucfirst($activity->event ?? 'custom') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($activity->subject)
                                                <a href="{{ route('progress.activity-logs.subject-activities', [class_basename($activity->subject), $activity->subject->id]) }}">
                                                    {{ class_basename($activity->subject) }} #{{ $activity->subject->id }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->log_name)
                                                <span class="badge badge-secondary">{{ $activity->log_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <a href="{{ route('progress.activity-logs.show', $activity) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> {{ __('activity-logs.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('activity-logs.no_activities_for_user') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="d-flex justify-content-center">
                        {{ $activities->links() }}
                    </div>

                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-light">
                                <strong>{{ __('activity-logs.summary') }}:</strong> 
                                {{ $activities->total() }} {{ __('activity-logs.activities_found') }} {{ __('activity-logs.for') }} {{ $user->name }}
                                @if(request('log_name'))
                                    {{ __('activity-logs.in_log') }} "{{ request('log_name') }}"
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when log name changes
    $('#log_name').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush
