@extends('progress::layouts.app')

@section('title', __('activity-logs.activity_log'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>
                        {{ __('activity-logs.activity_log') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('progress.activity-logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('activity-logs.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="150">{{ __('activity-logs.id') }}:</th>
                                    <td>{{ $activity->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('activity-logs.description') }}:</th>
                                    <td>{{ $activity->description }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('activity-logs.event') }}:</th>
                                    <td>
                                        <span class="badge badge-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : ($activity->event == 'deleted' ? 'danger' : 'info')) }}">
                                            <i class="fas fa-{{ $activity->event == 'created' ? 'plus' : ($activity->event == 'updated' ? 'edit' : ($activity->event == 'deleted' ? 'trash' : 'info-circle')) }}"></i>
                                            {{ ucfirst($activity->event ?? 'custom') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('activity-logs.log_name') }}:</th>
                                    <td>
                                        @if($activity->log_name)
                                            <span class="badge badge-secondary">{{ $activity->log_name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('activity-logs.created_at') }}:</th>
                                    <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('activity-logs.updated_at') }}:</th>
                                    <td>{{ $activity->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="150">{{ __('activity-logs.causer') }}:</th>
                                    <td>
                                        @if($activity->causer)
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $activity->causer->name }}</strong><br>
                                                    <small class="text-muted">{{ $activity->causer->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('activity-logs.system') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('activity-logs.subject') }}:</th>
                                    <td>
                                        @if($activity->subject)
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="fas fa-file-alt fa-2x text-info"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ class_basename($activity->subject) }}</strong><br>
                                                    <small class="text-muted">ID: {{ $activity->subject->id }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($activity->batch_uuid)
                                <tr>
                                    <th>{{ __('activity-logs.batch_uuid') }}:</th>
                                    <td><code>{{ $activity->batch_uuid }}</code></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    
                    @if($activity->properties && count($activity->properties) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-database text-secondary me-2"></i> {{ __('activity-logs.properties') }}</h5>

                            @if(isset($activity->properties['old']) || isset($activity->properties['attributes']))
                                <div class="row">
                                    @if(isset($activity->properties['old']))
                                    <div class="col-md-6">
                                        <h6 class="text-danger">{{ __('activity-logs.old_values') }}</h6>
                                        <table class="table table-bordered table-sm table-striped">
                                            @foreach($activity->properties['old'] as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>{{ $value ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                    @endif

                                    @if(isset($activity->properties['attributes']))
                                    <div class="col-md-6">
                                        <h6 class="text-success">{{ __('activity-logs.new_values') }}</h6>
                                        <table class="table table-bordered table-sm table-striped">
                                            @foreach($activity->properties['attributes'] as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>{{ $value ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ __('activity-logs.property') }}</th>
                                                <th>{{ __('activity-logs.value') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activity->properties as $key => $value)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        @if(is_array($value))
                                                            <pre class="mb-0"><code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code></pre>
                                                        @elseif(is_bool($value))
                                                            <span class="badge badge-{{ $value ? 'success' : 'danger' }}">
                                                                {{ $value ? 'True' : 'False' }}
                                                            </span>
                                                        @elseif(is_null($value))
                                                            <span class="text-muted">null</span>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    
                    @if($activity->subject)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-link text-info me-2"></i> {{ __('activity-logs.related_activities') }} {{ class_basename($activity->subject) }}</h5>
                            <a href="{{ route('progress.activity-logs.subject-activities', [class_basename($activity->subject), $activity->subject->id]) }}"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-history"></i> {{ __('activity-logs.view_all_activities_for') }} {{ class_basename($activity->subject) }}
                            </a>
                        </div>
                    </div>
                    @endif

                    
                    @if($activity->causer)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-user-clock text-primary me-2"></i> {{ __('activity-logs.related_activities') }} {{ __('activity-logs.causer') }}</h5>
                            <a href="{{ route('progress.activity-logs.user-activities', $activity->causer->id) }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-user-clock"></i> {{ __('activity-logs.view_all_activities_by') }} {{ $activity->causer->name }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
