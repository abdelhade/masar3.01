
<div class="container-fluid pt-4">
    <div class="actions-section">
        <h4 class="gradient-text fw-bold mb-3">
            <i class="fas fa-cogs me-2"></i>{{ __('general.actions') }}
        </h4>
        <div class="d-flex flex-wrap">
            <a href="{{ route('progress.projects.edit', $project->id) }}" class="btn btn-warning action-btn">
                <i class="fas fa-edit me-1"></i>{{ __('general.edit_project') }}
            </a>

            @can('delete progress-projects')
            <form action="{{ route('progress.projects.destroy', $project->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger action-btn" onclick="return confirm('{{ __('general.confirm_delete_project') }}')">
                    <i class="fas fa-trash me-1"></i>{{ __('general.delete_project') }}
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>

