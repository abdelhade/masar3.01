@extends('progress::layouts.daily-progress')

@section('title', __('projects.list'))

@section('content')


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
            @can('create progress-projects')
            <a href="{{ route('progress.project.create') }}" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm">
                <i class="las la-plus me-1"></i> إنشاء مشروع
            </a>
            @endcan
            @can('view progress-projects')
            <a href="{{ route('progress.project.index', ['status' => 'draft']) }}" class="btn btn-warning btn-sm rounded-pill text-dark fw-bold bg-opacity-10 border-warning">
                <i class="las la-file-alt me-1"></i> المسودات 
                @if(isset($draftsCount) && $draftsCount > 0)
                    <span class="badge bg-warning text-dark ms-1 rounded-circle">{{ $draftsCount }}</span>
                @endif
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-premium filter-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="las la-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control form-control-premium border-start-0 rounded-end-pill" placeholder="Search project name or client...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select form-control-premium rounded-pill">
                        <option value="">{{ __('general.status') }}: All</option>
                        <option value="active">{{ __('general.active') }}</option>
                        <option value="pending">{{ __('general.pending') }}</option>
                        <option value="completed">{{ __('general.completed') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="typeFilter" class="form-select form-control-premium rounded-pill">
                        <option value="">{{ __('general.type_of_project') }}: All</option>
                        @foreach($projectTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="clientFilter" class="form-select form-control-premium rounded-pill">
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
    
    <!-- Copy Project Modal -->
    <div class="modal fade" id="copyProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="copyProjectForm" action="" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">نسخ المشروع</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="copyName" class="form-label">اسم النسخة الجديدة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="copyName" name="name" required placeholder="مثال: نسخة من مشروع X">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">نسخ المشروع</button>
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
                // For list view performance, we might just assume 0 or check if loaded.
                $progressPercent = $totalQty > 0 ? round(($completedQty / $totalQty) * 100) : 0;
                
                $progressColor = 'primary';
                $progressPercent = $project->items->sum('total_quantity') > 0 
                    ? round(($project->daily_progress_sum_quantity / $project->items->sum('total_quantity')) * 100, 2) 
                    : 0;
            @endphp
            <div class="col-md-6 col-lg-6 col-xl-6 project-item" 
                 data-name="{{ strtolower($project->name) }}" 
                 data-client="{{ strtolower($project->client->name ?? '') }}"
                 data-status="{{ $project->status }}"
                 data-type="{{ $project->type_id }}"
                 data-client-id="{{ $project->client_id }}">
                
                <div class="card card-premium h-100 border-0 shadow-sm overflow-hidden">
                    <!-- Premium Header (Green) -->
                    <div class="card-header card-premium-header border-0 p-4 position-relative" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
                        <!-- Top Row: Status & Title -->
                        <div class="d-flex justify-content-between align-items-start w-100">
                             <!-- Status (Left) -->
                            <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 fw-normal shadow-sm">
                                <i class="las la-check-circle me-1"></i> {{ __('general.active') }}
                            </span>

                            <!-- Title & Client (Right) -->
                            <div class="text-end text-white">
                                <h5 class="fw-bold mb-1">{{ $project->name }}</h5>
                                <div class="small opacity-75 d-flex align-items-center justify-content-end gap-1">
                                    {{ $project->client->name ?? 'N/A' }} <i class="las la-building"></i>
                                </div>
                            </div>
                        </div>

                         <!-- Subprojects Badge (Bottom Right Absolute) -->
                         @if($project->items->whereNotNull('subproject_name')->count() > 0)
                            <div class="position-absolute" style="bottom: 15px; right: 15px; cursor: pointer;" onclick="openSubprojectsModal({{ $project->id }})">
                                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 small border border-white border-opacity-25 hover-scale">
                                    {{ __('general.subprojects') }} ({{ $project->items->whereNotNull('subproject_name')->unique('subproject_name')->count() }}) <i class="las la-sitemap ms-1"></i> 
                                </span>
                            </div>
                         @endif
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-4 pt-4">
                        <!-- Progress Section -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                             <span class="fw-bold text-primary fs-5">{{ $progressPercent }}%</span>
                             <span class="text-muted fw-bold small">التقدم <i class="las la-stream ms-1"></i></span>
                        </div>
                        <div class="progress mb-4 bg-light" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $progressPercent }}%; border-radius: 5px;"></div>
                        </div>

                        <!-- Stats Grid (RTL Flow) -->
                        <div class="row g-3">
                            <!-- Top Right: Items (Items) -->
                             <div class="col-6">
                                <div class="stat-box text-center rounded-2 p-3 h-100" style="background-color: #f8f9fa;">
                                    <div class="d-flex justify-content-between w-100 mb-2 text-muted small">
                                         <i class="las la-list fs-5 text-success"></i>
                                         <span>عنصر</span>
                                    </div>
                                    <div class="fw-bold text-dark fs-5 text-end">{{ $project->items_count }}</div>
                                </div>
                            </div>

                            <!-- Top Left: Type -->
                            <div class="col-6">
                                <div class="stat-box text-center rounded-2 p-3 h-100" style="background-color: #f8f9fa;">
                                     <div class="d-flex justify-content-between w-100 mb-2 text-muted small">
                                         <i class="las la-tag fs-5 text-primary"></i>
                                         <span>نوع المشروع</span>
                                    </div>
                                    <div class="fw-bold text-dark small text-end text-truncate" style="max-width: 100%;">
                                        {{ $project->type->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bottom Right: End Date (Teal) -->
                             <div class="col-6">
                                <div class="stat-box text-center rounded-2 p-3 h-100" style="background-color: #e0f2f1;"> <!-- Teal/Greenish -->
                                     <div class="d-flex justify-content-between w-100 mb-2 text-muted small">
                                         <i class="las la-calendar-check fs-5 text-success"></i>
                                         <span>تاريخ الانتهاء</span>
                                    </div>
                                    <div class="fw-bold text-dark small text-end" dir="ltr">{{ $project->end_date ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- Bottom Left: Start Date (Reddish) -->
                            <div class="col-6">
                                <div class="stat-box text-center rounded-2 p-3 h-100" style="background-color: #ffebee;"> <!-- Light Red -->
                                     <div class="d-flex justify-content-between w-100 mb-2 text-muted small">
                                         <i class="las la-calendar-alt fs-5 text-danger"></i>
                                         <span>تاريخ البدء</span>
                                    </div>
                                    <div class="fw-bold text-dark small text-end" dir="ltr">{{ $project->start_date }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-white border-0 p-3 pb-4">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                             <!-- Small Actions Left -->
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm px-2 rounded-3" title="copy" onclick="openCopyModal({{ $project->id }}, '{{ addslashes($project->name) }}')">
                                    <i class="las la-copy"></i>
                                </button>
                                @can('edit progress-projects')
                                <a href="{{ route('progress.project.edit', $project->id) }}" class="btn btn-outline-success btn-sm px-2 rounded-3" title="Edit">
                                    <i class="las la-edit"></i>
                                </a>
                                @endcan
                                @can('view progress-projects')
                                <a href="{{ route('progress.project.show', $project->id) }}" class="btn btn-outline-info btn-sm px-2 rounded-3" title="View">
                                    <i class="las la-eye"></i>
                                </a>
                                @endcan
                                @can('delete progress-projects')
                                <form action="{{ route('progress.project.destroy', $project->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟ سيتم حذف جميع البيانات المرتبطة به.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-2 rounded-3" title="Delete">
                                        <i class="las la-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>

                            <!-- Large Details Button Right -->
                            <button type="button" onclick="openDetailsModal({{ $project->id }})" class="btn btn-details text-center shadow-sm" style="max-width: 150px;">
                                <i class="las la-info-circle me-1"></i> عرض التفاصيل
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Project Details Modal (Redesigned matching Image) -->
    <div class="modal fade" id="projectDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%; width: 90%; margin-left: auto; margin-right: auto;">
            <div class="modal-content overflow-hidden border-0">
                <!-- Header: Blue with Title -->
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="las la-project-diagram me-2"></i>
                        <span id="detailModalTitle">Project Name</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Body -->
                <div class="modal-body p-4 bg-white">
                    <div id="detailLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                    <div id="detailContent" style="display: none;">
                        
                        <!-- Top Section: Name (Left) & Status (Right) -->
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <!-- Left: Name & Client -->
                            <div class="text-start">
                                <h4 class="fw-bold text-dark mb-1" id="d_name">Project Name</h4>
                                <div class="text-muted"><i class="las la-building me-1"></i> <span id="d_client">Client Name</span></div>
                            </div>
                            
                            <!-- Right: Status Badge -->
                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6" id="d_status">Active</span>
                        </div>

                        <!-- Progress Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-primary"><i class="las la-chart-line me-1"></i> Progress</span>
                                <span class="fw-bold text-primary fs-5" id="d_progress_text">0%</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 6px; background-color: #e9ecef;">
                                <div class="progress-bar bg-danger" id="d_progress_bar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">

                        <div class="row g-4">
                            <!-- Left Column: Basic Information -->
                            <div class="col-md-5 border-end"> <!-- Changed border-start to border-end for LTR -->
                                <h6 class="text-primary fw-bold mb-3 d-flex align-items-center justify-content-start">
                                    <i class="las la-info-circle me-2"></i> Basic Information
                                </h6>
                                <ul class="list-unstyled d-flex flex-column gap-3 text-start">
                                    <li>
                                        <div class="text-muted small mb-1"><i class="las la-tag me-1 text-primary"></i> Type of Project</div>
                                        <div class="fw-bold text-dark ps-4" id="d_type">-</div>
                                    </li>
                                    <li>
                                        <div class="text-muted small mb-1"><i class="las la-calendar-check me-1 text-primary"></i> Start Date</div>
                                        <div class="fw-bold text-dark ps-4" id="d_start" dir="ltr">-</div>
                                    </li>
                                    <li>
                                        <div class="text-muted small mb-1"><i class="las la-calendar-times me-1 text-primary"></i> End Date</div>
                                        <div class="fw-bold text-dark ps-4" id="d_end" dir="ltr">-</div>
                                    </li>
                                    <li>
                                        <div class="text-muted small mb-1"><i class="las la-map-marker me-1 text-primary"></i> Working Zone</div>
                                        <div class="fw-bold text-dark ps-4" id="d_zone">-</div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Right Column: Statistics -->
                            <div class="col-md-7">
                                <h6 class="text-primary fw-bold mb-3 d-flex align-items-center justify-content-start">
                                    <i class="las la-chart-bar me-2"></i> General Statistics
                                </h6>
                                <div class="row g-3">
                                     <!-- Top Left: Items -->
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded text-center h-100 d-flex flex-column justify-content-center align-items-center stats-card">
                                            <i class="las la-list fs-1 text-primary mb-2"></i>
                                            <div class="h3 fw-bold mb-0 text-dark" id="d_items_count">-</div>
                                            <div class="small text-muted">items</div>
                                        </div>
                                    </div>
                                    <!-- Top Right: Subprojects -->
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded text-center h-100 d-flex flex-column justify-content-center align-items-center stats-card">
                                            <i class="las la-sitemap fs-1 text-success mb-2"></i>
                                            <div class="h3 fw-bold mb-0 text-dark" id="d_sub_count">-</div>
                                            <div class="small text-muted">subprojects</div>
                                        </div>
                                    </div>
                                    <!-- Bottom Left: Employees -->
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded text-center h-100 d-flex flex-column justify-content-center align-items-center stats-card">
                                            <i class="las la-users fs-1 text-info mb-2"></i>
                                            <div class="h3 fw-bold mb-0 text-dark" id="d_emp_count">-</div>
                                            <div class="small text-muted">employees</div>
                                        </div>
                                    </div>
                                    <!-- Bottom Right: Working Days -->
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded text-center h-100 d-flex flex-column justify-content-center align-items-center stats-card">
                                            <i class="las la-calendar-day fs-1 text-warning mb-2"></i>
                                            <div class="h3 fw-bold mb-0 text-dark" id="d_work_days">-</div>
                                            <div class="small text-muted">working days</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3 p-3 bg-secondary bg-opacity-10 rounded d-flex justify-content-between align-items-center">
                                    <div class="text-muted"><i class="las la-stopwatch me-1"></i> Daily Work Hours</div>
                                    <div class="fw-bold text-dark" id="d_hours">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Right Aligned Actions -->
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-end p-3 gap-2">
                     <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">
                        <i class="las la-times me-1"></i> Close
                     </button>
                     @can('view progress-projects')
                     <a href="#" id="btn_view" class="btn btn-primary text-white shadow-sm">
                        <i class="las la-eye me-1"></i> View
                     </a>
                     @endcan
                     @can('edit progress-projects')
                     <a href="#" id="btn_edit" class="btn btn-success text-white shadow-sm">
                        <i class="las la-edit me-1"></i> Edit
                     </a>
                     @endcan
                     @can('view progress-projects')
                     <a href="#" id="btn_report" class="btn btn-info text-white shadow-sm">
                        <i class="las la-file-alt me-1"></i> Progress Report
                     </a>
                     @endcan
                     @can('view progress-projects')
                     <a href="#" id="btn_gantt" class="btn btn-outline-primary shadow-sm">
                        <i class="las la-stream me-1"></i> Gantt Chart
                     </a>
                     @endcan
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="subprojectsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header bg-white border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Subprojects Management</h5>
                        <p class="text-muted small mb-0">Manage weights and view progress for subprojects.</p>
                    </div>
                     <!-- Top Stats Badge -->
                    <div class="d-flex align-items-center gap-3">
                         <div class="d-flex align-items-center p-2 rounded bg-light border">
                             <div class="me-3 pe-3 border-end">
                                 <div class="small text-muted fw-bold">Total Weighted Progress</div>
                                 <div class="d-flex align-items-center">
                                     <span id="overallWeightedProgress" class="h5 mb-0 fw-bold text-primary">0%</span>
                                 </div>
                             </div>
                             <div>
                                 <div class="small text-muted fw-bold">Total Weight</div>
                                 <div class="d-flex align-items-center">
                                     <span id="totalWeightDisplay" class="h5 mb-0 fw-bold text-success">100%</span>
                                     <i id="weightWarning" class="las la-exclamation-triangle text-danger ms-2" style="display:none;" title="Weights must equal 100%"></i>
                                 </div>
                             </div>
                         </div>
                         <button onclick="updateAllWeights()" class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm px-3">
                            <i class="las la-save me-1"></i> Update All Weights
                         </button>
                         <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div id="subprojectsLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    
                    <div id="subprojectsContent" style="display: none;">
                        <input type="hidden" id="currentProjectId">
                        
                        <div class="alert alert-info small border-0 bg-white shadow-sm d-flex align-items-center">
                            <i class="las la-info-circle fs-4 me-2 text-info"></i>
                            <div>
                                <strong>Tip:</strong> Weights determine how much each subproject contributes to the overall project progress.
                                <span id="manualModeBadge" class="badge bg-warning text-dark ms-2" style="display:none;">Manual Weights Active</span>
                            </div>
                        </div>

                        <!-- Subprojects List -->
                        <div class="row g-4" id="subprojectsList">
                            <!-- Filled by JS -->
                        </div>
                    </div>
                </div>
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
                // document.getElementById('d_created').textContent = data.created_at; // Removed as element doesn't exist

                // Stats
                document.getElementById('d_items_count').textContent = data.items_count;
                document.getElementById('d_sub_count').textContent = data.subprojects_count;
                document.getElementById('d_emp_count').textContent = data.employees_count;
                document.getElementById('d_work_days').textContent = data.working_days;
                document.getElementById('d_hours').textContent = data.daily_work_hours + ' {{ __('general.hours') }}';

                // Footer Buttons (Fix routes)
                const baseUrl = `{{ url('projects') }}`; // Ensure this resolves to /projects
                document.getElementById('btn_view').href = `${baseUrl}/${data.id}`;
                document.getElementById('btn_edit').href = `${baseUrl}/${data.id}/edit`;
                
                // Charts Links
                // Check if routes are named 'projects.progress/state' and 'projects.gantt'
                // URL structure: /projects/{id}/progress and /projects/{id}/gantt
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
        const form = document.getElementById('copyProjectForm');
        form.action = `/projects/${id}/replicate`;
        document.getElementById('copyName').value = 'نسخة من ' + name;
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
        
        document.getElementById('currentProjectId').value = id;
        const loader = document.getElementById('subprojectsLoader');
        const content = document.getElementById('subprojectsContent');
        const listContainer = document.getElementById('subprojectsList');
        
        loader.style.display = 'block';
        content.style.display = 'none';
        listContainer.innerHTML = '';

        // Fetch Subprojects
        fetch(`/projects/${id}/subprojects`)
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                content.style.display = 'block';
                
                // Update Top Stats
                document.getElementById('overallWeightedProgress').textContent = data.overall_weighted_progress + '%';
                document.getElementById('totalWeightDisplay').textContent = data.total_weight + '%';
                
                checkWeightWarning(data.total_weight);

                if(data.manual_weights_enabled) {
                    document.getElementById('manualModeBadge').style.display = 'inline-block';
                } else {
                    document.getElementById('manualModeBadge').style.display = 'none';
                }

                data.subprojects.forEach((sub, index) => {
                    const card = document.createElement('div');
                    card.className = 'col-12';
                    
                    // Generate Items HTML
                    let itemsHtml = '';
                    if(sub.items && sub.items.length > 0) {
                        itemsHtml = `<div class="accordion mt-3" id="accordionMain_${index}">`;
                        
                        // Split Measurable / Others
                        const measurable = sub.items.filter(i => i.is_measurable);
                        const others = sub.items.filter(i => !i.is_measurable);

                        if(measurable.length > 0) {
                            itemsHtml += `
                                <div class="accordion-item border-0 bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 shadow-none bg-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMeasurable_${index}">
                                            <i class="las la-ruler-combined me-2 text-primary"></i> Measurable Items (${measurable.length})
                                        </button>
                                    </h2>
                                    <div id="collapseMeasurable_${index}" class="accordion-collapse collapse" data-bs-parent="#accordionMain_${index}">
                                        <div class="accordion-body p-0 pt-2">
                                            <ul class="list-group list-group-flush custom-list-group">
                                                ${measurable.map(item => `
                                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-3 py-2 border-0">
                                                        <div class="small fw-bold text-dark w-50 text-truncate">${item.name}</div>
                                                        <div class="w-50 d-flex align-items-center gap-2">
                                                            <div class="progress flex-grow-1" style="height: 5px;">
                                                                <div class="progress-bar bg-info" style="width: ${item.progress}%"></div>
                                                            </div>
                                                            <span class="small text-muted">${item.progress}%</span>
                                                        </div>
                                                    </li>
                                                `).join('')}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Others (Add Non-measurable logic if needed)
                        itemsHtml += `</div>`;
                    }

                    card.innerHTML = `
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <!-- Left: Info -->
                                    <div class="col-md-5">
                                        <h5 class="fw-bold text-dark mb-1">${sub.name}</h5>
                                        <div class="small text-muted mb-2">
                                            <span class="badge bg-light text-dark border me-1">${sub.items_count} Items</span>
                                            <span class="text-secondary"><i class="las la-cubes"></i> Qty: ${Number(sub.completed_qty).toLocaleString()} / ${Number(sub.total_qty).toLocaleString()}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-gradient-active" style="width: ${sub.progress}%"></div>
                                            </div>
                                            <span class="fw-bold text-primary">${sub.progress}%</span>
                                        </div>
                                    </div>

                                    <!-- Middle: Center Divider -->
                                    <div class="col-md-1 d-none d-md-flex justify-content-center">
                                        <div class="vr h-100 opacity-25"></div>
                                    </div>

                                    <!-- Right: Weight Control -->
                                    <div class="col-md-6 border-start-md">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="small fw-bold text-muted">Subproject Weight</label>
                                            <span class="badge bg-soft-primary text-primary">Contribution</span>
                                        </div>
                                        <div class="input-group">
                                            <input type="number" class="form-control fw-bold weight-input" 
                                                data-subproject="${sub.name}" 
                                                value="${sub.weight}" 
                                                step="0.01" min="0" max="100"
                                                oninput="recalcTotalWeight()">
                                            <span class="input-group-text bg-white text-muted">%</span>
                                        </div>
                                        <div class="form-text small mt-1">Adjusts impact on total project progress.</div>
                                    </div>
                                </div>
                                
                                <!-- Items Dropdown -->
                                ${itemsHtml}
                            </div>
                        </div>
                    `;
                    listContainer.appendChild(card);
                });
            })
            .catch(err => {
                console.error(err);
                loader.innerHTML = '<span class="text-danger">Failed to load data.</span>';
            });
    }

    function recalcTotalWeight() {
        let total = 0;
        document.querySelectorAll('.weight-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        const display = document.getElementById('totalWeightDisplay');
        display.textContent = Math.round(total * 10) / 10 + '%';
        
        checkWeightWarning(total);
    }

    function checkWeightWarning(total) {
        const warning = document.getElementById('weightWarning');
        const display = document.getElementById('totalWeightDisplay');
        
        // Tolerance for float errors
        if(Math.abs(total - 100) > 0.1) {
            warning.style.display = 'inline-block';
            display.classList.remove('text-success');
            display.classList.add('text-warning');
        } else {
            warning.style.display = 'none';
            display.classList.remove('text-warning');
            display.classList.add('text-success');
        }
    }

    function updateAllWeights() {
        const id = document.getElementById('currentProjectId').value;
        const weights = {};
        let total = 0;

        document.querySelectorAll('.weight-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            weights[input.dataset.subproject] = val;
            total += val;
        });

        // Optional: Confirm if not 100%
        if(Math.abs(total - 100) > 0.1) {
            if(!confirm('Total weight is not 100%. Do you want to save anyway?')) return;
        }

        const btn = event.target; // Simple ref, though might fail if icon clicked. Better select by known ID if implemented.
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="las la-spinner la-spin"></i> Saving...';
        btn.disabled = true;

        fetch(`/projects/${id}/subprojects/update-all-weights`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ weights: weights })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // alert('Weights updated successfully!');
                // We could show a toast instead.
                // Just refresh the modal to confirm persistence visually? 
                // Or just reset button.
                
                 // Show success badge/toast logic here if available, else simple alert
            } else {
                alert('Failed to update.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error updating weights.');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endpush
@endsection
