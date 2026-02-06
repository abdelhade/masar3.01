
<div class="col-lg-6">
    <div class="client-info-card">
        <h4 class="gradient-text fw-bold mb-4">
            <i class="fas fa-building me-2"></i>{{ __('general.client_information') }}
        </h4>

        @if($project->client)
        <div class="row">
            <div class="col-md-4 text-center mb-3">
                <div class="client-avatar mx-auto mb-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($project->client->name) }}&background=4f46e5&color=fff&size=80"
                        alt="{{ $project->client->name }}" class="rounded-circle" width="80"
                        height="80">
                </div>
                <h5 class="fw-bold">{{ $project->client->name }}</h5>
            </div>
            <div class="col-md-8">
                <div class="client-details">
                    <div class="mb-2">
                        <small class="text-muted">{{ __('general.contact_person') }}:</small>
                        <div class="fw-medium">
                            {{ $project->client->contact_person ?? __('general.not_specified') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('general.email') }}:</small>
                        <div class="fw-medium">
                            {{ $project->client->email ?? __('general.not_specified') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('general.phone') }}:</small>
                        <div class="fw-medium">
                            {{ $project->client->phone ?? __('general.not_specified') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('general.address') }}:</small>
                        <div class="fw-medium">
                            {{ $project->client->address ?? __('general.not_specified') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('general.projects_count') }}:</small>
                        <div class="fw-medium">
                            {{ $project->client->projects_count ?? $project->client->projects()->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-building fa-2x text-muted mb-2"></i>
            <p class="text-muted">{{ __('general.no_client_assigned') }}</p>
        </div>
        @endif
    </div>
</div>

