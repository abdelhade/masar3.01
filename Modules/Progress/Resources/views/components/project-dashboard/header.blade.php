
<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h2 mb-2"><i class="fas fa-chart-line me-2"></i>{{ __('general.project_dashboard') }}</h1>
            <p class="mb-0 opacity-75">{{ $project->name }}@if($project->client) - {{ $project->client->cname }}@endif</p>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="status-badge bg-{{ $projectStatus['color'] }}">
                <i class="fas fa-{{ $projectStatus['icon'] }} me-1"></i>
                {{ $projectStatus['message'] }}
            </span>
            <div class="mt-2 text-white-50">
                <small><i class="fas fa-sync-alt me-1"></i>{{ __('general.last_updated') }}:
                    {{ now()->format('d/m/Y H:i') }}</small>
            </div>
        </div>
    </div>
</div>

