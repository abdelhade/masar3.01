
<div class="col-lg-6">
    <div class="stat-card">
        <h4 class="gradient-text fw-bold mb-4">
            <i class="fas fa-project-diagram me-2"></i>{{ __('general.project_timeline') }}
        </h4>

        <div class="timeline">
            @if($project->start_date)
            <div class="timeline-item">
                <div class="fw-bold text-primary">{{ __('general.start_date') }}</div>
                <div class="text-muted">
                    {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</div>
                <small class="text-success">{{ __('general.project_kickoff') }}</small>
            </div>
            @endif

            <div class="timeline-item">
                <div class="fw-bold text-primary">{{ __('general.current_date') }}</div>
                <div class="text-muted">{{ now()->format('d/m/Y') }}</div>
                <small class="text-info">{{ __('general.progress') }}:
                    {{ number_format($overallProgress, 1) }}%</small>
            </div>

            @if ($project->end_date)
                <div class="timeline-item">
                    <div class="fw-bold text-primary">{{ __('general.end_date') }}</div>
                    <div class="text-muted">
                        {{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}</div>
                    <small class="text-warning">{{ __('general.expected_completion') }}</small>
                </div>
            @endif
        </div>

        <div class="mt-4 p-3 bg-light rounded">
            <h6 class="fw-bold mb-3">{{ __('general.project_information') }}</h6>
            <div class="row">
                <div class="col-6 mb-2">
                    <small class="text-muted">{{ __('general.client') }}:</small>
                    <div class="fw-medium">{{ $project->client->name ?? __('general.not_specified') }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted">{{ __('general.working_zone') }}:</small>
                    <div class="fw-medium">{{ $project->working_zone }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted">{{ __('general.status') }}:</small>
                    <div class="fw-medium text-capitalize">{{ $project->status }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted">{{ __('general.project_duration') }}:</small>
                    <div class="fw-medium">{{ $daysPassed + $daysRemaining }}
                        {{ __('general.days') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

