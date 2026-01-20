@extends('progress::layouts.daily-progress')

@section('title', 'Gantt Chart - ' . $project->name)

@section('content')
<div class="container-fluid" x-data="ganttApp()" x-init="initGantt()">
    
    <!-- 1. Header Section -->
    <div class="card mb-4 border-0 shadow-sm overflow-hidden text-white" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3">
                        <i class="las la-chart-bar fs-2 text-white"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-white">Gantt Chart - {{ $project->project_code ? $project->project_code . ' - ' : '' }} {{ $project->name }}</h3>
                        <div class="text-white-50 small mt-1">Interactive Timeline View</div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('progress.project.index') }}" class="btn btn-light text-primary fw-bold shadow-sm border-0">
                    <i class="las la-arrow-left me-1"></i> {{ __('general.back_to_projects') }}
                </a>
                <a href="{{ route('daily_progress.create') }}" class="btn btn-success fw-bold shadow-sm border-0 text-white">
                    <i class="las la-plus me-1"></i> {{ __('general.add_progress') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- 2. Project Info Cards -->
    <div class="row g-3 mb-4">
        <!-- Client -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <label class="text-muted small fw-bold text-uppercase mb-2">Client</label>
                    <div class="fs-5 fw-bold text-dark">{{ $project->client->name }}</div>
                </div>
            </div>
        </div>
        <!-- Status -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <label class="text-muted small fw-bold text-uppercase mb-2">Project Status</label>
                    <div>
                        <span class="badge {{ $project->status == 'active' ? 'bg-success' : 'bg-secondary' }} fs-6 px-3 py-2 rounded-2">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Working Zone -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <label class="text-muted small fw-bold text-uppercase mb-2">Working Zone</label>
                    <div class="fs-5 fw-bold text-dark">{{ $project->working_zone ?? __('general.not_specified') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Tasks -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="display-4 fw-bold text-primary mb-1">{{ $project->items->count() }}</div>
                    <div class="text-muted small text-uppercase fw-bold letter-spacing-1">TOTAL TASKS</div>
                </div>
            </div>
        </div>
        
        <!-- Completed -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="display-4 fw-bold text-success mb-1">{{ $project->items->where('remaining_quantity', 0)->count() }}</div>
                    <div class="text-muted small text-uppercase fw-bold letter-spacing-1">COMPLETED</div>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="display-4 fw-bold text-info mb-1">{{ $project->items->filter(fn($i) => $i->completed_quantity > 0 && $i->remaining_quantity > 0)->count() }}</div>
                    <div class="text-muted small text-uppercase fw-bold letter-spacing-1">IN PROGRESS</div>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="display-4 fw-bold text-warning mb-1">{{ $project->items->where('completed_quantity', 0)->count() }}</div>
                    <div class="text-muted small text-uppercase fw-bold letter-spacing-1">PENDING</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Controls & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                
                <!-- View & Zoom -->
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold me-1">View:</span>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm shadow-none" :class="viewMode === 'day' ? 'btn-primary' : 'btn-outline-secondary'" @click="changeViewMode('day')">Daily</button>
                            <button type="button" class="btn btn-sm shadow-none" :class="viewMode === 'week' ? 'btn-primary' : 'btn-outline-secondary'" @click="changeViewMode('week')">Weekly</button>
                            <button type="button" class="btn btn-sm shadow-none" :class="viewMode === 'month' ? 'btn-primary' : 'btn-outline-secondary'" @click="changeViewMode('month')">Monthly</button>
                        </div>
                    </div>
                    
                    <div class="vr mx-2"></div>
                    
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold me-1">Zoom:</span>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary shadow-none" @click="zoomIn()"><i class="las la-search-plus"></i></button>
                            <button class="btn btn-primary shadow-none" @click="resetZoom()">Reset</button>
                            <button class="btn btn-outline-secondary shadow-none" @click="zoomOut()"><i class="las la-search-minus"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="position-relative">
                        <i class="las la-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control form-control-sm ps-5 rounded" placeholder="Search by name, category..." x-model="searchQuery" @input="filterTasks()" style="width: 200px;">
                    </div>
                    
                    <select class="form-select form-select-sm rounded" x-model="statusFilter" @change="filterTasks()" style="width: 130px;">
                        <option value="all">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                    </select>
                    
                    <div class="d-flex gap-1">
                        <input type="date" class="form-control form-control-sm rounded" placeholder="From" style="width: 120px;">
                        <input type="date" class="form-control form-control-sm rounded" placeholder="To" style="width: 120px;">
                    </div>
                    
                    <button class="btn btn-primary btn-sm rounded px-3"><i class="las la-filter me-1"></i> Filter</button>
                    <button class="btn btn-secondary btn-sm rounded px-3"><i class="las la-undo me-1"></i> Reset</button>
                </div>
            </div>
            
            <!-- Legend & Refresh -->
            <div class="mt-4 d-flex flex-column align-items-center justify-content-center border-top pt-3">
                <div class="d-flex flex-wrap justify-content-center gap-4 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #fbbf24;"></span>
                        <span class="small fw-bold text-muted">Pending</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #3b82f6;"></span>
                        <span class="small fw-bold text-muted">In Progress</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #10b981;"></span>
                        <span class="small fw-bold text-muted">Completed</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #ef4444;"></span>
                        <span class="small fw-bold text-muted">Critical</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #f97316;"></span>
                        <span class="small fw-bold text-muted">Delayed</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #b91c1c;"></span>
                        <span class="small fw-bold text-muted">Today Line</span>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-sm shadow-sm px-4 fw-bold" @click="initGantt()">
                    <i class="las la-sync me-2"></i> Refresh Chart
                </button>
            </div>
        </div>
    </div>

    <!-- 5. Gantt Chart Area -->
    <div class="gantt-container-wrapper card border-0 shadow-sm overflow-hidden mb-5" style="height: 600px; display: flex; flex-direction: column;">
        <!-- Timeline Header -->
        <div class="gantt-header-wrapper" style="overflow: hidden; flex: 0 0 auto; border-bottom: 1px solid #eee;">
            <div class="gantt-header" :style="'transform: translateX(-' + scrollX + 'px); min-width: ' + totalWidth + 'px; padding-left: 250px;'">
                <!-- Top scale (Months/Years) -->
                <div class="d-flex date-row-top">
                    <!-- Js will render this -->
                    <template x-for="col in headerGroups" :key="col.id">
                        <div class="header-group" :style="'width: ' + col.width + 'px;'" x-text="col.label"></div>
                    </template>
                </div>
                <!-- Bottom scale (Days/Weeks) -->
                <div class="d-flex date-row-bottom">
                    <template x-for="(col, index) in dateColumns" :key="col.dateStr">
                        <div class="date-cell" 
                            :class="{'weekend': col.isWeekend}"
                            :style="'width: ' + columnWidth + 'px;'" 
                            x-text="col.label"></div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Task Rows and Grid -->
        <div class="gantt-body-wrapper" x-ref="ganttBody" @scroll="handleScroll()" style="overflow: auto; flex: 1 1 auto; position: relative;">
            
            <!-- Grid Lines Layer -->
            <div class="gantt-grid" :style="'min-width: ' + totalWidth + 'px; padding-left: 250px;'">
                <template x-for="col in dateColumns" :key="col.dateStr">
                   <div class="grid-column" :class="{'weekend': col.isWeekend, 'today': col.isToday}" :style="'width: ' + columnWidth + 'px;'"></div>
                </template>
            </div>

            <!-- Dependencies Layer -->
            <svg class="dependency-layer" :style="'width: ' + (totalWidth + 250) + 'px; height: ' + totalHeight + 'px;'">
                <template x-for="line in dependencyLines" :key="line.id">
                   <path :d="line.path" fill="none" stroke="#adb5bd" stroke-width="2" marker-end="url(#arrowhead)" stroke-dasharray="4" />
                </template>
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#adb5bd" />
                    </marker>
                </defs>
            </svg>

            <!-- Tasks Layer -->
            <div class="gantt-tasks" :style="'min-width: ' + totalWidth + 'px; padding-bottom: 20px;'">
                <template x-for="(task, index) in filteredTasks" :key="task.id">
                    <div class="gantt-row" @mouseenter="hoveredTask = task.id" @mouseleave="hoveredTask = null">
                        <!-- Task Info Column (Sticky) -->
                        <div class="task-label-col sticky-left bg-white border-end p-2 px-3" style="position: absolute; left: 0; width: 250px; z-index: 20; height: 50px;">
                             <div class="d-flex flex-column justify-content-center h-100 w-100" :style="'transform: translateX(' + scrollX + 'px);'">
                                <div class="fw-bold text-dark text-truncate mb-1" :title="task.name" x-text="task.name" style="font-size: 0.9rem;"></div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small" style="font-size: 0.75rem;">
                                        Qty: <span class="fw-bold text-dark" x-text="task.completed_quantity + '/' + task.total_quantity"></span> 
                                        Unit: <span class="fw-bold text-dark" x-text="task.unit"></span>
                                    </span>
                                </div>
                                <div class="mt-1 d-flex gap-1" v-if="task.progress > 0">
                                    <div class="progress w-100" style="height: 4px;">
                                        <div class="progress-bar" role="progressbar" :style="'width: ' + task.progress + '%'" :class="getBarClass(task)"></div>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Bar -->
                        <div class="task-bar-wrapper" :style="'margin-left: 250px; height: 50px; position: relative; width: ' + totalWidth + 'px;'">
                             <div class="task-bar rounded shadow-sm d-flex align-items-center justify-content-center" 
                                  :class="getBarClass(task)"
                                  :style="'left: ' + task.left + 'px; width: ' + task.width + 'px;'"
                                  @click="openTaskModal(task)">
                                  
                                  <!-- Progress Fill -->
                                  <!-- <div class="bar-progress" :style="'width: ' + task.progress + '%'"></div> -->
                                  
                                  <!-- Label (Percentage Center) -->
                                  <div class="bar-label text-white fw-bold small" style="z-index: 5; font-size: 0.75rem;" x-text="task.progress + '%'"></div>

                                  <!-- Tooltip -->
                                  <div class="gantt-tooltip shadow" x-show="hoveredTask === task.id" x-transition>
                                      <div class="fw-bold mb-1" x-text="task.name"></div>
                                      <div class="small mb-1"><span class="text-muted">Start:</span> <span x-text="task.startDateDisplay"></span></div>
                                      <div class="small mb-1"><span class="text-muted">End:</span> <span x-text="task.endDateDisplay"></span></div>
                                      <div class="small"><span class="text-muted">Progress:</span> <span x-text="task.progress + '%'"></span></div>
                                  </div>
                             </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>

    <!-- 6. Footer -->
    <div class="text-center text-muted small mt-4 mb-3">
        Crafted with <i class="las la-heart text-danger"></i> by CORE HOUSE TEAM
    </div>

    <!-- Task Details Modal -->
    <div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" x-text="selectedTask?.name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex gap-3 mb-4">
                        <div class="text-center p-3 bg-light rounded flex-fill">
                            <small class="text-muted d-block">Start Date</small>
                            <div class="fw-bold" x-text="selectedTask?.startDateDisplay"></div>
                        </div>
                        <div class="text-center p-3 bg-light rounded flex-fill">
                            <small class="text-muted d-block">End Date</small>
                            <div class="fw-bold" x-text="selectedTask?.endDateDisplay"></div>
                        </div>
                         <div class="text-center p-3 bg-light rounded flex-fill">
                            <small class="text-muted d-block">Duration</small>
                            <div class="fw-bold" x-text="selectedTask?.duration + ' Days'"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                         <div class="d-flex justify-content-between mb-1">
                             <span class="fw-bold">Progress</span>
                             <span class="fw-bold" x-text="selectedTask?.progress + '%'"></span>
                         </div>
                         <div class="progress" style="height: 10px;">
                             <div class="progress-bar" role="progressbar" :style="'width: ' + selectedTask?.progress + '%'" :class="getBarClass(selectedTask)"></div>
                         </div>
                    </div>

                    <div class="mb-3" x-show="selectedTask?.notes">
                        <h6 class="fw-bold">Notes</h6>
                        <p class="text-muted small bg-light p-2 rounded" x-text="selectedTask?.notes"></p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Styles -->
<style>
    /* Gantt Core Styles */
    .gantt-header-wrapper {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.85rem;
    }
    .date-row-top {
        border-bottom: 1px solid #dee2e6;
    }
    .header-group {
        padding: 8px 5px;
        text-align: center;
        border-right: 1px solid #dee2e6;
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background: #e9ecef;
    }
    .date-row-bottom {
        display: flex;
    }
    .date-cell {
        height: 30px;
        line-height: 30px;
        text-align: center;
        border-right: 1px solid #eee;
        font-size: 0.75rem;
        flex-shrink: 0;
        background: #fff;
    }
    .date-cell.weekend {
        background-color: #f8f9fa;
        color: #adb5bd;
    }

    .gantt-body-wrapper {
        background-color: #fff;
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }
    
    .gantt-grid {
        position: absolute;
        top: 0;
        height: 100%;
        display: flex;
        pointer-events: none;
        z-index: 0;
    }
    .grid-column {
        border-right: 1px solid #f8f9fa;
        flex-shrink: 0;
        height: 100%;
    }
    .grid-column.weekend {
        background-color: #f8f9fa;
    }
    .grid-column.today {
        border-left: 2px solid #e03131; /* Red line for today */
    }

    .dependency-layer {
        position: absolute;
        top: 0;
        left: 0; 
        z-index: 10;
        pointer-events: none;
    }

    .gantt-tasks {
        position: relative;
        z-index: 2;
        padding-top: 10px;
    }
    
    .gantt-row {
        height: 50px; /* Row height */
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f1f3f5;
        position: relative;
    }
    .task-label-col {
        height: 100%;
        display: flex;
        align-items: center;
        border-right: 1px solid #eee;
        background: white;
    }

    .task-bar {
        position: absolute;
        top: 25%; 
        height: 50% !important; 
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.75rem;
        line-height: 24px;
        white-space: nowrap;
        overflow: visible;
        transition: transform 0.1s;
    }
    .task-bar:hover {
        transform: translateY(-2px);
        filter: brightness(0.95);
        z-index: 100; /* Bring to front on hover */
    }
    
    .bar-progress {
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 4px 0 0 4px;
    }
    .bar-label {
        position: relative;
        z-index: 2;
    }

    /* Status Colors (Gradients) */
    .bar-completed { background: linear-gradient(90deg, #10b981 0%, #34d399 100%); }
    .bar-in-progress { background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%); }
    .bar-delayed { background: linear-gradient(90deg, #ef4444 0%, #f87171 100%); }
    .bar-pending { background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%); }

    /* Tooltip */
    .gantt-tooltip {
        position: absolute;
        bottom: 130%;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: white;
        padding: 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        pointer-events: none;
        z-index: 1000;
        min-width: 150px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .gantt-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }
    
    /* Scrollbar Styling */
    .gantt-body-wrapper::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .gantt-body-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .gantt-body-wrapper::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 4px;
    }
    .gantt-body-wrapper::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
</style>

@php
    // Prepare Data for JS
    $tasksData = $project->items->map(function($item) {
        return [
            'id' => $item->id,
            'name' => $item->workItem ? $item->workItem->name : 'N/A',
            'unit' => $item->workItem ? $item->workItem->unit : '', 
            'startDate' => $item->start_date,
            'endDate' => $item->end_date,
            'notes' => $item->notes,
            'progress' => $item->calc_comp_percent ?? round((($item->completed_quantity / max($item->total_quantity, 1)) * 100), 1),
            'predecessor' => $item->predecessor, 
            'dependency_type' => $item->dependency_type,
            'completed_quantity' => number_format($item->completed_quantity, 2),
            'total_quantity' => number_format($item->total_quantity, 2),
            'remaining_quantity' => $item->remaining_quantity
        ];
    });
@endphp

<!-- Scripts -->
<script>
    function ganttApp() {
        return {
            // Data
            project: @json($project),
            rawTasks: @json($tasksData),
            
            // State
            tasks: [],
            filteredTasks: [],
            dependencyLines: [],
            dateColumns: [],
            headerGroups: [],
            
            // Settings
            viewMode: 'day', // day, week, month
            columnWidth: 26, // Increased slightly for readability
            zoomLevel: 1,
            
            // Filters
            searchQuery: '',
            statusFilter: 'all',
            
            // UI
            scrollX: 0,
            totalWidth: 0,
            totalHeight: 0,
            hoveredTask: null,
            selectedTask: null,
            modalInstance: null,
            
            // Helpers
            minDate: null,
            maxDate: null,
            sidebarWidth: 250,

            initGantt() {
                // Initialize Data
                this.tasks = this.rawTasks.map(t => ({
                    ...t,
                    startObj: new Date(t.startDate),
                    endObj: new Date(t.endDate),
                    startDateDisplay: t.startDate,
                    endDateDisplay: t.endDate
                }));

                // Determine Timeline Range
                if (this.tasks.length > 0) {
                    const startDates = this.tasks.map(t => t.startObj.getTime());
                    const endDates = this.tasks.map(t => t.endObj.getTime());
                    this.minDate = new Date(Math.min(...startDates));
                    this.maxDate = new Date(Math.max(...endDates));
                    
                    // Buffer dates
                    this.minDate.setDate(this.minDate.getDate() - 7);
                    this.maxDate.setDate(this.maxDate.getDate() + 30);
                } else {
                    this.minDate = new Date();
                    this.minDate.setDate(this.minDate.getDate() - 7);
                    this.maxDate = new Date();
                    this.maxDate.setDate(this.maxDate.getDate() + 30);
                }

                this.render();
                
                // Initialize Modal
                this.modalInstance = new bootstrap.Modal(document.getElementById('taskDetailsModal'));
            },

            render() {
                 this.generateDateColumns();
                 this.calculateTaskPositions();
                 this.filterTasks(); 
            },

            generateDateColumns() {
                this.dateColumns = [];
                this.headerGroups = [];
                
                let current = new Date(this.minDate);
                const end = new Date(this.maxDate);
                let dayCount = 0;

                // Adjust column width based on view mode
                if (this.viewMode === 'day') this.columnWidth = 26 * this.zoomLevel;
                if (this.viewMode === 'week') this.columnWidth = 14 * this.zoomLevel; 
                if (this.viewMode === 'month') this.columnWidth = 8 * this.zoomLevel;

                let currentMonthGroup = null;

                while (current <= end) {
                    const isWeekend = current.getDay() === 5 || current.getDay() === 6; // Fri/Sat
                    const isToday = current.toDateString() === new Date().toDateString();
                    
                    this.dateColumns.push({
                        dateObj: new Date(current),
                        dateStr: current.toISOString().split('T')[0],
                        label: current.getDate(),
                        dayName: current.toLocaleDateString('en-US', { weekday: 'narrow' }),
                        isWeekend: isWeekend,
                        isToday: isToday,
                        index: dayCount
                    });

                    // Header Groups (Months)
                    const monthKey = current.toLocaleString('default', { month: 'short', year: 'numeric' });
                    if (!currentMonthGroup || currentMonthGroup.label !== monthKey) {
                        currentMonthGroup = { label: monthKey, width: 0, id: monthKey };
                        this.headerGroups.push(currentMonthGroup);
                    }
                    currentMonthGroup.width += this.columnWidth;

                    current.setDate(current.getDate() + 1);
                    dayCount++;
                }
                
                this.totalWidth = dayCount * this.columnWidth;
            },

            calculateTaskPositions() {
                const oneDay = 24 * 60 * 60 * 1000;
                
                this.tasks.forEach(task => {
                    // Start Index from MinDate
                    const diffTime = task.startObj - this.minDate;
                    const startIndex = Math.max(0, Math.floor(diffTime / oneDay));
                    
                    // Duration
                    const durationTime = task.endObj - task.startObj;
                    const durationDays = Math.max(1, Math.floor(durationTime / oneDay) + 1);
                    task.duration = durationDays;

                    // Position
                    task.left = startIndex * this.columnWidth;
                    task.width = durationDays * this.columnWidth;
                });
            },

             calculateDependencies() {
                this.dependencyLines = [];
                const taskMap = {};
                // Map logical ID to task object
                this.tasks.forEach(t => taskMap[t.id] = t);

                this.filteredTasks.forEach((task, index) => {
                    if (task.predecessor && taskMap[task.predecessor]) {
                        const parent = taskMap[task.predecessor];
                        
                        // Check if parent is visible in filtered view
                        // If not, we might skipping drawing relation or draw to edge
                        const parentVisible = this.filteredTasks.find(t => t.id === parent.id);
                        if (!parentVisible) return;
                        
                        const parentIndex = this.filteredTasks.findIndex(t => t.id === parent.id);
                        const taskIndex = index; 
                        
                        // Coordinates relative to Chart Area (excluding sidebar)
                        // Add Sidebar Width (250px) to X coordinates
                        
                        const x1 = parent.left + parent.width + this.sidebarWidth;
                        const y1 = (parentIndex * 50) + 25; // 50px row height, 25 center
                        
                        const x2 = task.left + this.sidebarWidth;
                        const y2 = (taskIndex * 50) + 25;
                        
                        // Draw Path: Bezier Curve or L-Shape
                        // Simple L shape: Move right 10px, go vertically, move right to target
                        const midX = (x1 + x2) / 2;
                        
                        let path = '';
                        if (x2 > x1 + 20) {
                             // Simple forward dependency
                             path = `M ${x1} ${y1} L ${x1 + 10} ${y1} L ${x1 + 10} ${y2} L ${x2} ${y2}`;
                        } else {
                             // Backward dependency (Overlap) - Loop around
                             path = `M ${x1} ${y1} L ${x1 + 10} ${y1} L ${x1 + 10} ${y1 + 20} L ${x2 - 10} ${y1 + 20} L ${x2 - 10} ${y2} L ${x2} ${y2}`;
                        }
                        
                        this.dependencyLines.push({
                            id: `dep-${parent.id}-${task.id}`,
                            path: path
                        });
                    }
                });
                
                this.totalHeight = Math.max(600, this.filteredTasks.length * 50); 
            },

            filterTasks() {
                this.filteredTasks = this.tasks.filter(t => {
                    const matchesSearch = t.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                    let matchesStatus = true;
                    if (this.statusFilter === 'completed') matchesStatus = t.progress >= 100;
                    if (this.statusFilter === 'in_progress') matchesStatus = t.progress > 0 && t.progress < 100;
                    if (this.statusFilter === 'pending') matchesStatus = t.progress === 0;
                    
                    return matchesSearch && matchesStatus;
                });
                
                // Recalculate dependencies after filtering
                this.calculateDependencies();
            },

            // Interactivity
            changeViewMode(mode) {
                this.viewMode = mode;
                this.render();
            },
            
            zoomIn() {
                if (this.zoomLevel < 2) {
                    this.zoomLevel += 0.2;
                    this.render();
                }
            },
            
            zoomOut() {
                if (this.zoomLevel > 0.5) {
                    this.zoomLevel -= 0.2;
                    this.render();
                }
            },
            
            resetZoom() {
                this.viewMode = 'day';
                this.zoomLevel = 1;
                this.render();
            },

            handleScroll() {
                const el = this.$refs.ganttBody;
                this.scrollX = el.scrollLeft;
            },

            openTaskModal(task) {
                this.selectedTask = task;
                this.modalInstance.show();
            },

            // Helpers
            getBarClass(task) {
                if (task.progress >= 100) return 'bar-completed';
                if (task.progress > 0) return 'bar-in-progress';
                
                // Check delay
                const today = new Date();
                if (today > task.endObj && task.progress < 100) return 'bar-delayed';
                
                return 'bar-pending';
            }
        }
    }
</script>
@endsection
