@extends('progress::layouts.app')

@section('title', __('general.dashboard'))

@section('content')
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            border: none;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .accordion-button {
            font-weight: 600;
            color: #0d6efd;
        }
        .accordion-button:not(.collapsed) {
            background-color: #e9f2ff;
            color: #0a58ca;
            box-shadow: none;
        }
        .accordion-item {
            border: none;
            margin-bottom: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .accordion-body {
            background: #fff;
            border-top: 1px solid #f1f1f1;
        }
        .btn-group .btn {
            border-radius: 6px !important;
        }
        .alert {
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">

                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold text-primary">
                        <i class="bi bi-recycle me-2"></i>{{ __('general.recycle_bin_title') }}
                    </h1>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-arrow-right me-1"></i> {{ __('general.back') }}
                    </a>
                </div>

                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                
                <div class="accordion" id="recycleBinAccordion">

                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="projectsHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#projectsCollapse" aria-expanded="true"
                                    aria-controls="projectsCollapse">
                                <i class="bi bi-kanban me-2"></i>{{ __('general.projects') }}
                                ({{ $deletedProjects->count() }})
                            </button>
                        </h2>
                        <div id="projectsCollapse" class="accordion-collapse collapse show"
                             aria-labelledby="projectsHeading" data-bs-parent="#recycleBinAccordion">
                            <div class="accordion-body">
                                @if($deletedProjects->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('general.name') }}</th>
                                                    <th>{{ __('general.deleted_at') }}</th>
                                                    <th class="text-end">{{ __('general.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($deletedProjects as $project)
                                                    <tr>
                                                        <td>{{ $project->name }}</td>
                                                        <td>
                                                            <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                                            {{ $project->deleted_at->format('Y-m-d H:i') }}
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="btn-group">
                                                                @can('edit progress-recycle-bin')
                                                                <a href="{{ route('progress.recycle-bin.restore', ['type' => 'project', 'id' => $project->id]) }}"
                                                                   class="btn btn-sm btn-success">
                                                                    <i class="fas fa-undo me-1"></i> {{ __('general.restore') }}
                                                                </a>
                                                                @endcan
                                                                @can('delete progress-recycle-bin')
                                                                <a href="{{ route('progress.recycle-bin.force-delete', ['type' => 'project', 'id' => $project->id]) }}"
                                                                   class="btn btn-sm btn-danger"
                                                                   onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                                    <i class="fas fa-trash me-1"></i> {{ __('general.force_delete') }}
                                                                </a>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="clientsHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#clientsCollapse" aria-expanded="false"
                                    aria-controls="clientsCollapse">
                                <i class="bi bi-people me-2"></i>{{ __('general.clients') }}
                                ({{ $deletedClients->count() }})
                            </button>
                        </h2>
                        <div id="clientsCollapse" class="accordion-collapse collapse"
                             aria-labelledby="clientsHeading" data-bs-parent="#recycleBinAccordion">
                            <div class="accordion-body">
                                @if($deletedClients->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('general.name') }}</th>
                                                    <th>{{ __('general.deleted_at') }}</th>
                                                    <th class="text-end">{{ __('general.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($deletedClients as $client)
                                                    <tr>
                                                        <td>{{ $client->cname }}</td>
                                                        <td>
                                                            <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                                            {{ $client->deleted_at->format('Y-m-d H:i') }}
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="btn-group">
                                                                @can('edit progress-recycle-bin')
                                                                <a href="{{ route('progress.recycle-bin.restore', ['type' => 'client', 'id' => $client->id]) }}"
                                                                   class="btn btn-sm btn-success">
                                                                    <i class="fas fa-undo me-1"></i> {{ __('general.restore') }}
                                                                </a>
                                                                @endcan
                                                                @can('delete progress-recycle-bin')
                                                                <a href="{{ route('progress.recycle-bin.force-delete', ['type' => 'client', 'id' => $client->id]) }}"
                                                                   class="btn btn-sm btn-danger"
                                                                   onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                                    <i class="fas fa-trash me-1"></i> {{ __('general.force_delete') }}
                                                                </a>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    
<div class="accordion-item">
    <h2 class="accordion-header" id="employeesHeading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#employeesCollapse" aria-expanded="false" aria-controls="employeesCollapse">
            <i class="bi bi-person-badge me-2"></i>{{ __('general.employees') }}
            ({{ $deletedEmployees->count() }})
        </button>
    </h2>
    <div id="employeesCollapse" class="accordion-collapse collapse"
         aria-labelledby="employeesHeading" data-bs-parent="#recycleBinAccordion">
        <div class="accordion-body">
            @if($deletedEmployees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('general.name') }}</th>
                                <th>{{ __('general.deleted_at') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedEmployees as $employee)
                                <tr>
                                    <td>{{ $employee->name }}</td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                        {{ $employee->deleted_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('edit progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.restore', ['type' => 'employee', 'id' => $employee->id]) }}"
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-undo me-1"></i>{{ __('general.restore') }}
                                            </a>
                                            @endcan
                                            @can('delete progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.force-delete', ['type' => 'employee', 'id' => $employee->id]) }}"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                <i class="fas fa-trash me-1"></i>{{ __('general.force_delete') }}
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                </p>
            @endif
        </div>
    </div>
</div>


<div class="accordion-item">
    <h2 class="accordion-header" id="workItemsHeading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#workItemsCollapse" aria-expanded="false" aria-controls="workItemsCollapse">
            <i class="bi bi-list-check me-2"></i>{{ __('general.work_items') }}
            ({{ $deletedWorkItems->count() }})
        </button>
    </h2>
    <div id="workItemsCollapse" class="accordion-collapse collapse"
         aria-labelledby="workItemsHeading" data-bs-parent="#recycleBinAccordion">
        <div class="accordion-body">
            @if($deletedWorkItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('general.name') }}</th>
                                <th>{{ __('general.deleted_at') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedWorkItems as $workItem)
                                <tr>
                                    <td>{{ $workItem->name }}</td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                        {{ $workItem->deleted_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('edit progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.restore', ['type' => 'work-item', 'id' => $workItem->id]) }}"
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-undo me-1"></i>{{ __('general.restore') }}
                                            </a>
                                            @endcan
                                            @can('delete progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.force-delete', ['type' => 'work-item', 'id' => $workItem->id]) }}"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                <i class="fas fa-trash me-1"></i>{{ __('general.force_delete') }}
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                </p>
            @endif
        </div>
    </div>
</div>


<div class="accordion-item">
    <h2 class="accordion-header" id="templatesHeading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#templatesCollapse" aria-expanded="false" aria-controls="templatesCollapse">
            <i class="bi bi-layers me-2"></i>{{ __('general.templates') }}
            ({{ $deletedTemplates->count() }})
        </button>
    </h2>
    <div id="templatesCollapse" class="accordion-collapse collapse"
         aria-labelledby="templatesHeading" data-bs-parent="#recycleBinAccordion">
        <div class="accordion-body">
            @if($deletedTemplates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('general.name') }}</th>
                                <th>{{ __('general.deleted_at') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedTemplates as $template)
                                <tr>
                                    <td>{{ $template->name }}</td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                        {{ $template->deleted_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('edit progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.restore', ['type' => 'template', 'id' => $template->id]) }}"
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-undo me-1"></i>{{ __('general.restore') }}
                                            </a>
                                            @endcan
                                            @can('delete progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.force-delete', ['type' => 'template', 'id' => $template->id]) }}"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                <i class="fas fa-trash me-1"></i>{{ __('general.force_delete') }}
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                </p>
            @endif
        </div>
    </div>
</div>


<div class="accordion-item">
    <h2 class="accordion-header" id="typesHeading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#typesCollapse" aria-expanded="false" aria-controls="typesCollapse">
            <i class="bi bi-tags me-2"></i>{{ __('general.types') }}
            ({{ $deletedTypes->count() }})
        </button>
    </h2>
    <div id="typesCollapse" class="accordion-collapse collapse"
         aria-labelledby="typesHeading" data-bs-parent="#recycleBinAccordion">
        <div class="accordion-body">
            @if($deletedTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('general.name') }}</th>
                                <th>{{ __('general.deleted_at') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedTypes as $type)
                                <tr>
                                    <td>{{ $type->name }}</td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                        {{ $type->deleted_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('edit progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.restore', ['type' => 'type', 'id' => $type->id]) }}"
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-undo me-1"></i>{{ __('general.restore') }}
                                            </a>
                                            @endcan
                                            @can('delete progress-recycle-bin')
                                            <a href="{{ route('progress.recycle-bin.force-delete', ['type' => 'type', 'id' => $type->id]) }}"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                <i class="fas fa-trash me-1"></i>{{ __('general.force_delete') }}
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                </p>
            @endif
        </div>
    </div>

</div>


<div class="accordion-item">
    <h2 class="accordion-header" id="dailyProgressHeading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#dailyProgressCollapse" aria-expanded="false" aria-controls="dailyProgressCollapse">
            <i class="bi bi-clipboard2-check me-2"></i>{{ __('general.daily_progress') }}
            ({{ $deletedDailyProgress->count() }})
        </button>
    </h2>
    <div id="dailyProgressCollapse" class="accordion-collapse collapse"
         aria-labelledby="dailyProgressHeading" data-bs-parent="#recycleBinAccordion">
        <div class="accordion-body">
            @if($deletedDailyProgress->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('general.description') }}</th>
                                <th>{{ __('general.deleted_at') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedDailyProgress as $progress)
                                <tr>
                                    <td>{{ Str::limit($progress->description, 50) }}</td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1 text-secondary"></i>
                                        {{ $progress->deleted_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            @can('edit progress-recycle-bin')
                                                <a href="{{ route('progress.recycle-bin.restore', ['type' => 'daily-progress', 'id' => $progress->id]) }}"
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-undo me-1"></i>{{ __('general.restore') }}
                                                </a>
                                            @endcan

                                            @can('delete progress-recycle-bin')
                                                <a href="{{ route('progress.recycle-bin.permanent-delete', ['type' => 'daily-progress', 'id' => $progress->id]) }}"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('{{ __('general.confirm_delete') }}')">
                                                    <i class="fas fa-trash me-1"></i>{{ __('general.force_delete') }}
                                                </a>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">
                    <i class="bi bi-folder-x me-1"></i>{{ __('general.no_items') }}
                </p>
            @endif
        </div>
    </div>
</div>


                </div>
            </div>
        </div>
    </div>
@endsection
