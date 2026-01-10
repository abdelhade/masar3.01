<div class="dashboard-header mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-1 text-white fw-bold">{{ $project->name }}</h1>
            <p class="mb-0 text-white-50">
                <i class="fas fa-building me-2"></i>{{ $project->client->name ?? __('general.not_specified') }}
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-flex flex-column align-items-md-end">
                <span class="badge bg-{{ $projectStatus['color'] }} status-badge mb-2">
                    <i class="fas {{ $projectStatus['icon'] }} me-1"></i>
                    {{ $projectStatus['message'] }}
                </span>
                <small class="text-white-50">
                    <i class="fas fa-clock me-1"></i>
                    {{ __('general.last_updated') }}: {{ now()->format('d/m/Y H:i') }}
                </small>
            </div>
        </div>
    </div>
</div>
