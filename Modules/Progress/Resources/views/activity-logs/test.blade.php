@extends('progress::layouts.app')

@section('title', __('activity-logs.test_activity_logs'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('activity-logs.test_activity_logs') }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>{{ __('activity-logs.activity_log_system_working') }}</h5>
                        <p>{{ __('activity-logs.activities_created') }}: <strong>{{ App\Models\Activity::count() }}</strong></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                                            <div class="card-header">
                                <h6>{{ __('activity-logs.quick_statistics') }}</h6>
                            </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('activity-logs.total_activities') }}:</span>
                                            <span class="badge badge-primary">{{ App\Models\Activity::count() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('activity-logs.project_activities') }}:</span>
                                            <span class="badge badge-success">{{ App\Models\Activity::inLog('projects')->count() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('activity-logs.employee_activities') }}:</span>
                                            <span class="badge badge-info">{{ App\Models\Activity::inLog('employees')->count() }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ __('activity-logs.client_activities') }}:</span>
                                            <span class="badge badge-warning">{{ App\Models\Activity::inLog('clients')->count() }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                                            <div class="card-header">
                                <h6>{{ __('activity-logs.latest_activities') }}</h6>
                            </div>
                                <div class="card-body">
                                    @php
                                        $recentActivities = App\Models\Activity::with(['causer', 'subject'])
                                            ->orderBy('created_at', 'desc')
                                            ->limit(5)
                                            ->get();
                                    @endphp
                                    
                                    @foreach($recentActivities as $activity)
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $activity->description }}</strong>
                                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="text-muted">
                                                <small>
                                                    @if($activity->causer)
                                                        {{ __('activity-logs.by') }}: {{ $activity->causer->name }}
                                                    @else
                                                        {{ __('activity-logs.by') }}: {{ __('activity-logs.system') }}
                                                    @endif
                                                    | 
                                                    @if($activity->subject)
                                                        {{ class_basename($activity->subject) }} #{{ $activity->subject->id }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <a href="{{ route('progress.activity-logs.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-history"></i> {{ __('activity-logs.view_all_activities') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
