@extends('progress::layouts.daily-progress')

@section('title', __('projects.list'))

@section('content')
    <div class="m-2 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('projects.list') }}</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickAddProjectModal">
                <i class="fas fa-bolt me-1"></i> إضافة سريعة
            </button>
            <a href="{{ route('progress.project.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> {{ __('projects.new') }}
            </a>
        </div>
    </div>

    <div class="card border-0 rounded-0">
        <div class="card-header border-0">
            <div class="table-responsive" style="overflow-x: auto;">
                <table id="myTable" class="table table-striped mb-0 w-100" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('projects.name') }}</th>
                            <th>{{ __('projects.client') }}</th>
                            <th>{{ __('general.status') }}</th>
                            <th>{{ __('general.type_of_project') }}</th>
                            <th>{{ __('projects.start_date') }}</th>
                            <th>{{ __('projects.end_date') }}</th>
                            {{-- @canany(['projects-view', 'projects-edit', 'projects-progress', 'projects-delete']) --}}
                            <th>{{ __('projects.actions') }}</th>
                            {{-- @endcanany --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->client->name ?? __('general.not_specified') }}</td>
                                <td>
                                    @switch($project->status)
                                        @case('in_progress')
                                            <span class="badge bg-success">{{ __('general.active') }}</span>
                                        @break

                                        @case('completed')
                                            <span class="badge bg-secondary">{{ __('general.completed') }}</span>
                                        @break

                                        @case('pending')
                                            <span class="badge bg-warning">{{ __('general.pending') }}</span>
                                        @break

                                        @case('cancelled')
                                            <span class="badge bg-danger">{{ __('general.cancelled') }}</span>
                                        @break
                                    @endswitch
                                </td>

                                <td>
                                    {{ $project->type ? $project->type->name : __('general.not_specified') }}
                                </td>
                                <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '' }}
                                </td>
                                <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '' }}
                                </td>
                                {{-- @canany(abilities: ['projects-view', 'projects-edit', 'projects-progress', 'projects-delete']) --}}
                                <td>
                                    {{-- @can('projects-view') --}}
                                    <a href="{{ route('progress.project.show', $project) }}"
                                        class="btn btn-icon-square-sm btn-primary" style='font-size:10px;'
                                        title="{{ __('general.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- @endcan
                                        @can('projects-edit') --}}
                                    <a href="{{ route('progress.project.edit', $project) }}"
                                        class="btn btn-icon-square-sm btn-success" style='font-size:10px;'
                                        title="{{ __('general.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- @endcan
                                        @can('projects-progress') --}}
                                    <a href="{{ route('projects.progress/state', $project) }}"
                                        class="btn btn-icon-square-sm btn-info" style='font-size:10px;'
                                        title="{{ __('general.progress_report') }}">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                     <a href="{{ route('projects.gantt', $project) }}"
                                        class="btn btn-icon-square-sm btn-warning" style='font-size:10px;'
                                        title="Gantt Chart">
                                        <i class="fas fa-stream"></i>
                                    </a>
                                    {{-- @endcan
                                        @can('projects-delete') --}}
                                    <form action="{{ route('progress.project.destroy', $project) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon-square-sm "
                                            style='font-size:10px;'
                                            onclick="return confirm('{{ __('projects.confirm_delete') }}')"
                                            title="{{ __('general.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    {{-- @endcan --}}
                                </td>
                                {{-- @endcanany --}}
                            </tr>
<style>
    /* Custom Gradients */
    .bg-gradient-active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .bg-gradient-pending {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    .bg-gradient-completed {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }
    .bg-gradient-cancelled {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    .project-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .filter-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .modal-content {
        border-radius: 15px;
        border: none;
    }
    
    .progress-thin {
        height: 6px;
        border-radius: 3px;
    }
</style>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('projects.list') }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark">{{ __('projects.list') }}</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('progress.project.index', ['status' => 'draft']) }}" class="btn btn-outline-warning rounded-pill">
                <i class="las la-file-alt me-1"></i> Drafts 
                @if(isset($draftsCount) && $draftsCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $draftsCount }}</span>
                @endif
            </a>
            {{-- @can('projects-create') --}}
            <a href="{{ route('progress.project.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="las la-plus me-1"></i> {{ __('projects.new') }}
            </a>
            {{-- @endcan --}}
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="las la-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 rounded-end-pill" placeholder="Search project name or client...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select rounded-pill">
                        <option value="">{{ __('general.status') }}: All</option>
                        <option value="active">{{ __('general.active') }}</option>
                        <option value="pending">{{ __('general.pending') }}</option>
                        <option value="completed">{{ __('general.completed') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="typeFilter" class="form-select rounded-pill">
                        <option value="">{{ __('general.type_of_project') }}: All</option>
                        @foreach($projectTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="clientFilter" class="form-select rounded-pill">
                        <option value="">{{ __('projects.client') }}: All</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-light rounded-pill text-muted" onclick="resetFilters()">
                        <i class="las la-undo me-1"></i> Reset
                    </button>
                </div>
            </div>
            <div class="mt-2 text-muted small ps-2">
                Showing <span id="visibleCount">{{ $projects->count() }}</span> projects
            </div>
        </div>
    </div>

    <!-- Quick Add Project Modal -->
    <div class="modal fade" id="quickAddProjectModal" tabindex="-1" aria-labelledby="quickAddProjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('progress.project.quickStore') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="quickAddProjectModalLabel">إضافة مشروع سريعة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم المشروع <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="أدخل اسم المشروع">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ المشروع</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Projects Grid -->
    <div class="row g-4" id="projectsGrid">
        @foreach($projects as $project)
            @php
                // Determine Status Color & gradient
                $statusColor = 'secondary';
                $gradientClass = 'bg-gradient-active';
                $statusLabel = __('general.active');
                
                if($project->status === 'completed') {
                    $gradientClass = 'bg-gradient-completed';
                    $statusLabel = __('general.completed');
                    $statusColor = 'success';
                } elseif($project->status === 'pending') {
                    $gradientClass = 'bg-gradient-pending';
                    $statusLabel = __('general.pending');
                    $statusColor = 'warning';
                } elseif($project->status === 'cancelled') {
                    $gradientClass = 'bg-gradient-cancelled';
                    $statusLabel = __('general.cancelled');
                    $statusColor = 'danger';
                }

                // Calculate Progress (Example logic if items exist)
                $totalQty = $project->items->sum('total_quantity');
                $completedQty = $project->items->sum('completed_quantity'); // Assuming this field exists or needs calculation
                // If not in DB, we rely on controller or model to append it.
                // Assuming controller didn't append 'completed_quantity' to project root, 
                // but we might need to rely on what show() does. 
                // For list view performance, we might just assume 0 or check if loaded.
                $progressPercent = $totalQty > 0 ? round(($completedQty / $totalQty) * 100) : 0;
                
                $progressColor = 'primary';
                if($progressPercent < 25) $progressColor = 'danger';
                elseif($progressPercent < 50) $progressColor = 'warning';
                elseif($progressPercent < 75) $progressColor = 'info';
                else $progressColor = 'success';
            @endphp

            <div class="col-md-6 col-lg-4 project-item" 
                 data-name="{{ strtolower($project->name) }}" 
                 data-client="{{ strtolower($project->client->name ?? '') }}"
                 data-status="{{ $project->status }}"
                 data-type="{{ $project->project_type_id }}"
                 data-client-id="{{ $project->client_id }}">
                 
                <div class="card project-card h-100 shadow-sm">
                    <!-- Card Header -->
                    <div class="card-header border-0 {{ $gradientClass }} text-white p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1 text-white">{{ $project->name }}</h5>
                                <div class="small opacity-75"><i class="las la-building me-1"></i> {{ $project->client->name ?? 'N/A' }}</div>
                            </div>
                            <span class="badge bg-white text-dark bg-opacity-25 backdrop-blur">{{ $statusLabel }}</span>
                        </div>
                        {{-- Subprojects Button --}}
                        {{-- Assuming we detect subprojects if items have subproject_name --}}
                        @if($project->items->whereNotNull('subproject_name')->count() > 0)
                        <div class="mt-3">
                             <button class="btn btn-sm btn-light text-primary border-0 shadow-sm" onclick="openSubprojectsModal({{ $project->id }})">
                                <i class="las la-sitemap me-1"></i> Subprojects
                             </button>
                        </div>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <span class="fw-bold text-dark">{{ $progressPercent }}%</span>
                            <small class="text-muted">Progress</small>
                        </div>
                        <div class="progress progress-thin mb-4 bg-light">
                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="small text-muted mb-1">{{ __('general.type_of_project') }}</div>
                                    <div class="fw-bold text-dark small text-truncate">{{ $project->type->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="small text-muted mb-1">Items</div>
                                    <div class="fw-bold text-dark small">{{ $project->items_count }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="small text-muted mb-1">Start</div>
                                    <div class="fw-bold text-dark small">{{ $project->start_date }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <div class="small text-muted mb-1">End</div>
                                    <div class="fw-bold text-dark small">{{ $project->end_date ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-outline-primary rounded-circle" title="Detail View" onclick="openDetailsModal({{ json_encode($project) }})">
                            <i class="las la-search-plus"></i>
                        </button>
                        <div class="d-flex gap-2">
                             <a href="{{ route('progress.project.show', $project->id) }}" class="btn btn-sm btn-light text-primary" title="Dashboard">
                                <i class="las la-eye"></i>
                            </a>
                            <a href="{{ route('progress.project.edit', $project->id) }}" class="btn btn-sm btn-light text-success" title="Edit">
                                <i class="las la-pen"></i>
                            </a>
                            <button class="btn btn-sm btn-light text-warning" title="Copy" onclick="openCopyModal({{ $project->id }}, '{{ $project->name }}')">
                                <i class="las la-copy"></i>
                            </button>
                            <button class="btn btn-sm btn-light text-info" title="Save as Template" onclick="openTemplateModal({{ $project->id }})">
                                <i class="las la-save"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Project Details Modal (Redesigned) -->
    <div class="modal fade" id="projectDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content overflow-hidden border-0">
                <!-- Header -->
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="las la-cube me-2"></i> <span id="detailModalTitle">Project Name</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Body -->
                <div class="modal-body p-0">
                    <div id="detailLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                    <div id="detailContent" style="display: none;">
                        <div class="p-4">
                            <!-- Top Bar: Title & Status -->
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <h4 class="fw-bold text-dark mb-1" id="d_name">Project Name</h4>
                                    <div class="text-muted"><i class="las la-building"></i> <span id="d_client">Client Name</span></div>
                                </div>
                                <span class="badge bg-success px-3 py-2 rounded-pill fs-6" id="d_status">Active</span>
                            </div>

                            <!-- Progress Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-primary"><i class="las la-chart-line me-1"></i> Progress</span>
                                    <span class="fw-bold text-primary" id="d_progress_text">0%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 5px; background-color: #e9ecef;">
                                    <div class="progress-bar bg-primary" id="d_progress_bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Basic Information -->
                                <div class="col-md-5 border-end">
                                    <h6 class="text-primary fw-bold mb-3"><i class="las la-info-circle me-1"></i> Basic Information</h6>
                                    <ul class="list-unstyled d-flex flex-column gap-3">
                                        <li>
                                            <div class="text-muted small mb-1"><i class="las la-tag me-1"></i> Type of Project</div>
                                            <div class="fw-bold text-dark" id="d_type">Board Piles</div>
                                        </li>
                                        <li>
                                            <div class="text-muted small mb-1"><i class="las la-calendar-check me-1"></i> Start Date</div>
                                            <div class="fw-bold text-dark" id="d_start">15-11-2025</div>
                                        </li>
                                        <li>
                                            <div class="text-muted small mb-1"><i class="las la-calendar-times me-1"></i> End Date</div>
                                            <div class="fw-bold text-dark" id="d_end">13-05-2026</div>
                                        </li>
                                        <li>
                                            <div class="text-muted small mb-1"><i class="las la-map-marker me-1"></i> Working Zone</div>
                                            <div class="fw-bold text-dark" id="d_zone">Suwaihan</div>
                                        </li>
                                        <li>
                                            <div class="text-muted small mb-1"><i class="las la-clock me-1"></i> Created At</div>
                                            <div class="fw-bold text-dark" id="d_created">27-11-2025</div>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Statistics -->
                                <div class="col-md-7">
                                    <h6 class="text-dark fw-bold mb-3"><i class="las la-chart-bar me-1"></i> {{ __('general.statistics') }}</h6>
                                    <div class="row g-3">
                                        <!-- Items -->
                                        <div class="col-6">
                                            <div class="p-3 bg-light rounded text-center h-100">
                                                <i class="las la-list fs-1 text-primary mb-2"></i>
                                                <div class="h3 fw-bold mb-0 text-dark" id="d_items_count">64</div>
                                                <div class="small text-muted">{{ __('general.items') }}</div>
                                            </div>
                                        </div>
                                        <!-- Subprojects -->
                                        <div class="col-6">
                                            <div class="p-3 bg-light rounded text-center h-100">
                                                <i class="las la-sitemap fs-1 text-success mb-2"></i>
                                                <div class="h3 fw-bold mb-0 text-dark" id="d_sub_count">8</div>
                                                <div class="small text-muted">{{ __('general.subprojects') }}</div>
                                            </div>
                                        </div>
                                        <!-- Employees -->
                                        <div class="col-6">
                                            <div class="p-3 bg-light rounded text-center h-100">
                                                <i class="las la-users fs-1 text-info mb-2"></i>
                                                <div class="h3 fw-bold mb-0 text-dark" id="d_emp_count">6</div>
                                                <div class="small text-muted">{{ __('general.employees') }}</div>
                                            </div>
                                        </div>
                                        <!-- Working Days -->
                                        <div class="col-6">
                                            <div class="p-3 bg-light rounded text-center h-100">
                                                <i class="las la-calendar-day fs-1 text-warning mb-2"></i>
                                                <div class="h3 fw-bold mb-0 text-dark" id="d_work_days">5</div>
                                                <div class="small text-muted">{{ __('general.working_days') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                                        <div class="text-muted"><i class="las la-stopwatch me-1"></i> {{ __('general.daily_work_hours') }}</div>
                                        <div class="fw-bold" id="d_hours">8 {{ __('general.hours') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-end gap-2 py-3">
                     <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i> Close
                     </button>
                     <a href="#" id="btn_view" class="btn btn-primary px-4">
                        <i class="las la-eye me-1"></i> View
                     </a>
                     <a href="#" id="btn_edit" class="btn btn-success px-4">
                        <i class="las la-edit me-1"></i> Edit
                     </a>
                     <a href="#" id="btn_report" class="btn btn-info text-white px-4">
                        <i class="las la-file-alt me-1"></i> Progress Report
                     </a>
                     <a href="#" id="btn_gantt" class="btn btn-outline-primary px-4">
                        <i class="las la-chart-bar me-1"></i> Gantt Chart
                     </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="subprojectsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Subprojects & Weights</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="subprojectsLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    
                    <div id="subprojectsContent" style="display: none;">
                        <div class="alert alert-info small border-0 bg-info bg-opacity-10 text-info">
                            <i class="las la-info-circle me-1"></i> Weights are calculated based on quantities. Adjustments here (if enabled) allow re-balancing. Total must equal 100%.
                        </div>

                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Subproject Name</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-center" style="width: 150px;">Weight (%)</th>
                                    <th class="text-center">Progress</th>
                                </tr>
                            </thead>
                            <tbody id="subprojectsTableBody">
                                <!-- Filled by JS -->
                            </tbody>
                            <tfoot class="fw-bold bg-light">
                                <tr>
                                    <td colspan="2" class="text-end">Total Weight:</td>
                                    <td class="text-center"><span id="totalWeight" class="text-success">100%</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- <div class="modal-footer border-0"> -->
                    <!-- Save button can be added here if backend supports weight update -->
                    <!-- <button type="button" class="btn btn-primary rounded-pill">Save Weights</button> -->
                <!-- </div> -->
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Filtering Logic
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const clientFilter = document.getElementById('clientFilter');
    const visibleCount = document.getElementById('visibleCount');
    const projects = document.querySelectorAll('.project-item');

    function filterProjects() {
        const term = searchInput.value.toLowerCase();
        const status = statusFilter.value;
        const type = typeFilter.value;
        const client = clientFilter.value;
        
        let count = 0;

        projects.forEach(project => {
            const name = project.dataset.name;
            const clientName = project.dataset.client;
            const pStatus = project.dataset.status;
            const pType = project.dataset.type;
            const pClient = project.dataset.clientId;

            const matchesSearch = name.includes(term) || clientName.includes(term);
            const matchesStatus = status === '' || pStatus === status;
            const matchesType = type === '' || pType === type;
            const matchesClient = client === '' || pClient === client;

            if (matchesSearch && matchesStatus && matchesType && matchesClient) {
                project.style.display = 'block';
                count++;
            } else {
                project.style.display = 'none';
            }
        });

        visibleCount.textContent = count;
    }

    [searchInput, statusFilter, typeFilter, clientFilter].forEach(el => {
        el.addEventListener('input', filterProjects);
        el.addEventListener('change', filterProjects);
    });

    function resetFilters() {
        searchInput.value = '';
        statusFilter.value = '';
        typeFilter.value = '';
        clientFilter.value = '';
        filterProjects();
    }

    // Modal Logic
    function openDetailsModal(projectData) { 
        const modal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
        const id = projectData.id || projectData; 
        
        modal.show();

        const loader = document.getElementById('detailLoader');
        const content = document.getElementById('detailContent');
        
        loader.style.display = 'block';
        content.style.display = 'none';

        // Fetch Details
        fetch(`/projects/${id}/details`)
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                content.style.display = 'block';

                document.getElementById('detailModalTitle').textContent = data.name;
                document.getElementById('d_name').textContent = data.name;
                document.getElementById('d_client').textContent = data.client_name;
                
                // Status Badge
                const statusEl = document.getElementById('d_status');
                statusEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                statusEl.className = 'badge px-3 py-2 rounded-pill fs-6';
                if(data.status === 'completed') statusEl.classList.add('bg-success');
                else if(data.status === 'pending') statusEl.classList.add('bg-warning', 'text-dark');
                else statusEl.classList.add('bg-success');

                // Progress
                document.getElementById('d_progress_text').textContent = data.progress + '%';
                document.getElementById('d_progress_bar').style.width = data.progress + '%';

                // Basic Info
                document.getElementById('d_type').textContent = data.type_name;
                document.getElementById('d_start').textContent = data.start_date;
                document.getElementById('d_end').textContent = data.end_date || '-';
                document.getElementById('d_zone').textContent = data.working_zone;
                document.getElementById('d_created').textContent = data.created_at;

                // Stats
                document.getElementById('d_items_count').textContent = data.items_count;
                document.getElementById('d_sub_count').textContent = data.subprojects_count;
                document.getElementById('d_emp_count').textContent = data.employees_count;
                document.getElementById('d_work_days').textContent = data.working_days;
                document.getElementById('d_hours').textContent = data.daily_work_hours + ' {{ __('general.hours') }}';

                // Footer Buttons (Fix routes if needed)
                const baseUrl = `{{ url('projects') }}`;
                document.getElementById('btn_view').href = `${baseUrl}/${data.id}`;
                document.getElementById('btn_edit').href = `${baseUrl}/${data.id}/edit`;
                document.getElementById('btn_report').href = `${baseUrl}/${data.id}/progress`;
                document.getElementById('btn_gantt').href = `${baseUrl}/${data.id}/gantt`;
            })
            .catch(err => {
                console.error(err);
                loader.innerHTML = '<span class="text-danger">Failed to load data.</span>';
            });
    }

    function openCopyModal(id, name) {
        const modal = new bootstrap.Modal(document.getElementById('copyProjectModal'));
        document.getElementById('copySourceId').value = id;
        document.querySelector('#copyProjectModal input[name="name"]').value = 'Copy of ' + name;
        modal.show();
    }
    
    function openTemplateModal(id) {
        const modal = new bootstrap.Modal(document.getElementById('saveAsTemplateModal'));
        // Set hidden ID input if implementing actual save logic
        modal.show();
    }

    function openSubprojectsModal(id) {
        const modal = new bootstrap.Modal(document.getElementById('subprojectsModal'));
        modal.show();
        
        const loader = document.getElementById('subprojectsLoader');
        const content = document.getElementById('subprojectsContent');
        const tbody = document.getElementById('subprojectsTableBody');
        
        loader.style.display = 'block';
        content.style.display = 'none';
        tbody.innerHTML = '';

        // Fetch Subprojects
        fetch(`/projects/${id}/subprojects`)
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                content.style.display = 'block';
                
                let totalWeight = 0;

                data.forEach(sub => {
                    totalWeight += parseFloat(sub.weight);
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="fw-bold text-dark">${sub.name}</td>
                        <td class="text-center"><span class="badge bg-light text-dark border">${sub.items_count}</span></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-center weight-input" value="${sub.weight}" step="0.1" disabled>
                                <span class="input-group-text">%</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: ${sub.progress}%"></div>
                                </div>
                                <small>${sub.progress}%</small>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                document.getElementById('totalWeight').textContent = Math.round(totalWeight) + '%';
            })
            .catch(err => {
                console.error(err);
                loader.innerHTML = '<span class="text-danger">Failed to load data.</span>';
            });
    }
</script>
@endpush
@endsection
