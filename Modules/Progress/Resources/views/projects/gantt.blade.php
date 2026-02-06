@extends('progress::layouts.app')

@section('title', __('general.gantt_chart') . ' - ' . $project->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gantt.css') }}">
@endpush

@section('content')

    <div class="container-fluid">
        <div class="gantt-container">
            <div class="gantt-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-chart-gantt me-3"></i>{{ __('general.gantt_chart') }} - {{ $project->name }}
                        </h2>
                        <p class="mb-0 opacity-75">{{ __('general.interactive_timeline_view') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('progress.projects.index') }}" class="btn btn-light btn-modern">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('general.back_to_projects') }}
                        </a>
                        <a href="{{ route('progress.daily-progress.create') }}?project_id={{ $project->id }}"
                            class="btn btn-success btn-modern ms-2">
                            <i class="fas fa-plus me-2"></i>{{ __('general.add_progress') }}
                        </a>
                    </div>
                </div>
            </div>

            
            <div class="container my-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">{{ __('general.client') }}</h6>
                                <h5>{{ $project->client->cname }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">{{ __('general.project_status') }}</h6>
                                <h5>
                                    <span
                                        class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'pending' ? 'warning' : 'primary') }}">
                                        {{ __('general.status_' . $project->status) }}
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">{{ __('general.working_zone') }}</h6>
                                <h5>{{ $project->working_zone }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="row" id="statsContainer">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="stats-number text-primary" id="totalTasks">0</div>
                            <div class="stats-label">{{ __('general.total_tasks') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="stats-number text-success" id="completedTasks">0</div>
                            <div class="stats-label">{{ __('general.completed') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="stats-number text-info" id="activeTasks">0</div>
                            <div class="stats-label">{{ __('general.in_progress') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="stats-number text-warning" id="pendingTasks">0</div>
                            <div class="stats-label">{{ __('general.pending') }}</div>
                        </div>
                    </div>
                </div>

                
                <div class="gantt-controls">
                    <div class="view-controls">
                        <span class="me-2">{{ __('general.view') }}:</span>
                        <button class="view-btn active" data-view="daily">{{ __('general.daily') }}</button>
                        <button class="view-btn" data-view="weekly">{{ __('general.weekly') }}</button>
                        <button class="view-btn" data-view="monthly">{{ __('general.monthly') }}</button>
                    </div>

                    <div class="zoom-controls">
                        <span class="me-2">{{ __('general.zoom') }}:</span>
                        <button class="zoom-btn" data-zoom="out"><i class="fas fa-search-minus"></i></button>
                        <button class="zoom-btn active" data-zoom="reset">{{ __('general.reset') }}</button>
                        <button class="zoom-btn" data-zoom="in"><i class="fas fa-search-plus"></i></button>
                    </div>

                    <div class="filter-controls">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="taskSearch" placeholder="Search by name, category, unit, notes...">
                        </div>
                        <select class="form-select" id="statusFilter" style="width: 150px;">
                            <option value="all">{{ __('general.all_statuses') }}</option>
                            <option value="pending">{{ __('general.pending') }}</option>
                            <option value="active">{{ __('general.in_progress') }}</option>
                            <option value="completed">{{ __('general.completed') }}</option>
                            <option value="delayed">Delayed Items</option>
                            <option value="early">Early Items</option>
                        </select>
                        <input type="date" id="dateFrom" class="form-control" style="width: 150px;" placeholder="From Date">
                        <input type="date" id="dateTo" class="form-control" style="width: 150px;" placeholder="To Date">
                        <button class="btn btn-primary btn-sm" id="applyFilter">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <button class="btn btn-secondary btn-sm" id="resetFilter">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                    </div>
                </div>

                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);"></div>
                        <span>{{ __('general.pending') }}</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);"></div>
                        <span>{{ __('general.in_progress') }}</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #10b981, #059669);"></div>
                        <span>{{ __('general.completed') }}</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #ef4444, #dc2626);"></div>
                        <span>{{ __('general.critical') }}</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(135deg, #f97316, #ea580c);"></div>
                        <span>{{ __('general.delayed') }}</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color bg-danger"></div>
                        <span>{{ __('general.today_line') }}</span>
                    </div>
                </div>

                
                <div class="text-center mb-4">
                    <button class="btn btn-primary-modern btn-modern me-2" onclick="refreshChart()">
                        <i class="fas fa-sync-alt me-2"></i>{{ __('general.refresh_chart') }}
                    </button>

                </div>
            </div>

            
            <div class="gantt-chart">
                
                <div class="timeline-header">
                    <div class="d-flex">
                        <div class="task-info">
                            <strong>{{ __('general.tasks') }}</strong>
                        </div>
                        <div class="timeline-dates flex-grow-1" id="dateHeader">
                            
                        </div>
                    </div>
                </div>

                
                <div id="ganttRows">
                    
                </div>

                
                <div id="dependencyLines">
                    
                </div>

             
              
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="taskModalTitle">{{ __('general.task_details') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="taskModalBody">
                    
                </div>
                <div class="modal-footer">
                    <a href="#" id="addProgressBtn" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>{{ __('general.add_progress') }}
                    </a>
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('general.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let projectData = null;
            let dateColumns = [];
            let filteredTasks = [];
            const msPerDay = 24 * 60 * 60 * 1000;

            // إعدادات العرض
            let currentView = 'daily';
            let currentZoom = 1;
            const zoomLevels = {
                daily: [0.5, 0.75, 1, 1.5, 2],
                weekly: [0.5, 0.75, 1, 1.25, 1.5],
                monthly: [0.5, 0.75, 1, 1.25, 1.5]
            };

            // تهيئة عناصر التحكم
            initializeControls();

            // جلب بيانات المشروع
            async function loadProjectData() {
                try {
                    const response = await fetch(`{{ route('progress.projects.gantt.data', $project->id) }}`);
                    projectData = await response.json();
                    filteredTasks = [...projectData.tasks];
                    initializeGanttChart();
                } catch (error) {
                    console.error('Error loading project data:', error);
                    showToast('{{ __('general.error_loading_data') }}', 'error');
                }
            }

            function initializeControls() {
                // أزرار عرض البيانات
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove(
                            'active'));
                        this.classList.add('active');
                        currentView = this.dataset.view;
                        initializeGanttChart();
                    });
                });

                // أزرار التكبير والتصغير
                document.querySelectorAll('.zoom-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const zoomAction = this.dataset.zoom;
                        if (zoomAction === 'reset') {
                            currentZoom = 1;
                        } else if (zoomAction === 'in') {
                            currentZoom = Math.min(2, currentZoom + 0.25);
                        } else if (zoomAction === 'out') {
                            currentZoom = Math.max(0.5, currentZoom - 0.25);
                        }

                        document.querySelectorAll('.zoom-btn').forEach(b => b.classList.remove(
                            'active'));
                        this.classList.add('active');
                        applyZoom();
                    });
                });

                // البحث والتصفية
                document.getElementById('taskSearch').addEventListener('input', filterTasks);
                document.getElementById('statusFilter').addEventListener('change', filterTasks);
                document.getElementById('dateFrom').addEventListener('change', filterTasks);
                document.getElementById('dateTo').addEventListener('change', filterTasks);
                document.getElementById('applyFilter').addEventListener('click', filterTasks);
                document.getElementById('resetFilter').addEventListener('click', resetFilters);
            }

            function filterTasks() {
                const searchTerm = document.getElementById('taskSearch').value.toLowerCase();
                const statusFilter = document.getElementById('statusFilter').value;
                const dateFrom = document.getElementById('dateFrom').value;
                const dateTo = document.getElementById('dateTo').value;

                filteredTasks = projectData.tasks.filter(task => {
                    const matchesSearch = task.name.toLowerCase().includes(searchTerm) ||
                        (task.description && task.description.toLowerCase().includes(searchTerm)) ||
                        (task.work_item && task.work_item.category && task.work_item.category.toLowerCase().includes(searchTerm)) ||
                        (task.unit && task.unit.toLowerCase().includes(searchTerm)) ||
                        (task.work_item && task.work_item.notes && task.work_item.notes.toLowerCase().includes(searchTerm));
                    
                    let matchesStatus = true;
                    if (statusFilter === 'delayed') {
                        // Delayed items: items that passed their end date and are not completed
                        const today = new Date();
                        const taskEndDate = new Date(task.end_date);
                        matchesStatus = taskEndDate < today && task.status !== 'completed';
                    } else if (statusFilter === 'early') {
                        // Early items: completed items that finished before their end date
                        const taskEndDate = new Date(task.end_date);
                        const today = new Date();
                        matchesStatus = task.status === 'completed' && today < taskEndDate;
                    } else if (statusFilter !== 'all') {
                        matchesStatus = task.status === statusFilter;
                    }

                    // Date range filtering
                    let matchesDateRange = true;
                    if (dateFrom || dateTo) {
                        const taskStartDate = new Date(task.start_date);
                        const taskEndDate = new Date(task.end_date);
                        
                        if (dateFrom) {
                            const fromDate = new Date(dateFrom);
                            matchesDateRange = matchesDateRange && (taskStartDate >= fromDate || taskEndDate >= fromDate);
                        }
                        
                        if (dateTo) {
                            const toDate = new Date(dateTo);
                            matchesDateRange = matchesDateRange && (taskStartDate <= toDate || taskEndDate <= toDate);
                        }
                    }

                    return matchesSearch && matchesStatus && matchesDateRange;
                });

                renderTaskRows();
                updateStats();
            }

            function resetFilters() {
                document.getElementById('taskSearch').value = '';
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('dateFrom').value = '';
                document.getElementById('dateTo').value = '';
                filterTasks();
            }

            function initializeGanttChart() {
                if (!projectData || !projectData.tasks) return;

                updateStats();
                generateDateColumns();
                renderDateHeader();
                renderTaskRows();
                positionTodayLine();
                renderDependencies();
            }

            function updateStats() {
                const tasks = filteredTasks;
                const totalTasks = tasks.length;
                const completedTasks = tasks.filter(t => t.status === 'completed').length;
                const activeTasks = tasks.filter(t => t.status === 'active').length;
                const pendingTasks = tasks.filter(t => t.status === 'pending').length;

                document.getElementById('totalTasks').textContent = totalTasks;
                document.getElementById('completedTasks').textContent = completedTasks;
                document.getElementById('activeTasks').textContent = activeTasks;
                document.getElementById('pendingTasks').textContent = pendingTasks;
            }

            function generateDateColumns() {
                if (!projectData || !projectData.tasks.length) return;

                const tasks = filteredTasks.length ? filteredTasks : projectData.tasks;
                const startDate = new Date(Math.min(...tasks.map(t => new Date(t.start_date))));
                const endDate = new Date(Math.max(...tasks.map(t => new Date(t.end_date))));

                dateColumns = [];
                const currentDate = new Date(startDate);

                while (currentDate <= endDate) {
                    dateColumns.push(new Date(currentDate));
                    currentDate.setDate(currentDate.getDate() + 1);
                }
            }

            function renderDateHeader() {
                const dateHeader = document.getElementById('dateHeader');
                dateHeader.innerHTML = '';

                const columnWidth = 60 * currentZoom; // زيادة العرض الأساسي من 40 إلى 60

                dateColumns.forEach((date, index) => {
                    const dateDiv = document.createElement('div');
                    dateDiv.className = 'date-column';
                    dateDiv.style.minWidth = `${columnWidth}px`;
                    dateDiv.style.maxWidth = `${columnWidth * 1.5}px`;
                    dateDiv.textContent = date.getDate();

                    // تمييز عطلات نهاية الأسبوع
                    if (date.getDay() === 5 || date.getDay() === 6) { // الجمعة والسبت
                        dateDiv.classList.add('weekend');
                    }

                    // تمييز اليوم الحالي
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (date.getTime() === today.getTime()) {
                        dateDiv.classList.add('today');
                    }

                    // عرض الشهر في أول يوم أو عند بداية شهر جديد
                    if (date.getDate() === 1 || index === 0) {
                        dateDiv.style.borderLeft = '2px solid #3b82f6';
                        dateDiv.innerHTML =
                            `<div style="font-size: 0.65rem; color: #3b82f6; margin-bottom: 2px; font-weight: 700;">${date.toLocaleDateString('en', {month: 'short'})}</div><div style="font-size: 0.75rem;">${date.getDate()}</div>`;
                    }

                    dateHeader.appendChild(dateDiv);
                });
            }

            function applyZoom() {
                const dateColumns = document.querySelectorAll('.date-column');
                const columnWidth = 60 * currentZoom; // تطابق مع renderDateHeader

                dateColumns.forEach(col => {
                    col.style.minWidth = `${columnWidth}px`;
                    col.style.maxWidth = `${columnWidth * 1.5}px`;
                });

                renderTaskRows();
                positionTodayLine();
                renderDependencies();
            }

            function renderTaskRows() {
                const ganttRows = document.getElementById('ganttRows');
                ganttRows.innerHTML = '';

                filteredTasks.forEach(task => {
                    console.log('Task data:', task); // Debug log
                    const row = document.createElement('div');
                    row.className = 'gantt-row';
                    row.setAttribute('data-task-id', task.id);

                    row.innerHTML = `
                <div class="task-info">
                    <div class="task-name" title="${task.name}">${task.name}</div>
                    <div class="task-details">
                        <div class="task-details-compact">
                            <span class="task-detail-item"><strong>Qty:</strong> ${task.completed_quantity || 0}/${task.total_quantity || 0}</span>
                            <span class="task-detail-item"><strong>Unit:</strong> ${task.unit || 'N/A'}</span>
                        </div>
                        <div class="d-flex gap-1 align-items-center flex-wrap">
                            <span class="badge ${getStatusBadgeClass(task.status)}">${getStatusText(task.status)}</span>
                            ${task.is_critical ? '<span class="badge bg-danger">Critical</span>' : ''}
                            ${task.subproject ? '<span class="badge bg-info">' + task.subproject + '</span>' : ''}
                        </div>
                    </div>
                </div>
                <div class="timeline-content">
                    ${generateTaskBar(task)}
                </div>
            `;

                    row.addEventListener('click', () => showTaskDetails(task));
                    makeTaskDraggable(row, task);
                    ganttRows.appendChild(row);
                });
            }

            function generateTaskBar(task) {
                const taskStartDate = new Date(task.start_date);
                const taskEndDate = new Date(task.end_date);

                if (dateColumns.length === 0) return '';

                const firstDate = dateColumns[0];
                const lastDate = dateColumns[dateColumns.length - 1];

                const taskStartIndex = Math.max(0, Math.floor((taskStartDate - firstDate) / msPerDay));
                const taskEndIndex = Math.min(dateColumns.length - 1, Math.floor((taskEndDate - firstDate) /
                    msPerDay));
                const taskDuration = taskEndIndex - taskStartIndex + 1;

                const leftPercent = (taskStartIndex / dateColumns.length) * 100;
                const widthPercent = (taskDuration / dateColumns.length) * 100;

                // تحديد إذا كانت المهمة متأخرة
                const today = new Date();
                const isDelayed = taskEndDate < today && task.status !== 'completed';
                const taskClass = isDelayed ? 'task-bar delayed' :
                    task.is_critical ? 'task-bar critical' :
                    `task-bar ${task.status}`;

                return `
            <div class="${taskClass}"
                 style="left: ${leftPercent}%; width: ${widthPercent}%;"
                 data-task-id="${task.id}"
                 onmouseenter="showAdvancedTooltip(event, ${JSON.stringify(task).replace(/"/g, '&quot;')})"
                 onmouseleave="hideAdvancedTooltip()">
                <div class="progress-overlay" style="width: ${task.progress}%;"></div>
                <div class="progress-text">${task.progress}%</div>
            </div>
        `;
            }

            function makeTaskDraggable(row, task) {
                const taskBar = row.querySelector('.task-bar');
                if (!taskBar) return;

                taskBar.setAttribute('draggable', 'true');

                taskBar.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', task.id);
                    taskBar.classList.add('task-dragging');
                });

                taskBar.addEventListener('dragend', () => {
                    taskBar.classList.remove('task-dragging');
                });
            }

            function positionTodayLine() {
                if (dateColumns.length === 0) return;

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const firstDate = dateColumns[0];
                const lastDate = dateColumns[dateColumns.length - 1];

                if (today >= firstDate && today <= lastDate) {
                    const dayIndex = Math.floor((today - firstDate) / msPerDay);
                    const leftPercent = ((dayIndex + 0.5) / dateColumns.length) * 100;

                    const todayLine = document.getElementById('todayLine');
                    todayLine.style.left = `calc(320px + ${leftPercent}%)`;
                    todayLine.style.display = 'block';
                }
            }

            function renderDependencies() {
                const dependencyLines = document.getElementById('dependencyLines');
                dependencyLines.innerHTML = '';

                if (!projectData.dependencies) return;

                projectData.dependencies.forEach(dep => {
                    const fromTask = projectData.tasks.find(t => t.id === dep.from_task_id);
                    const toTask = projectData.tasks.find(t => t.id === dep.to_task_id);

                    if (fromTask && toTask) {
                        drawDependencyArrow(fromTask, toTask, dep.type);
                    }
                });
            }

            function drawDependencyArrow(fromTask, toTask, type) {
                const fromBar = document.querySelector(`.task-bar[data-task-id="${fromTask.id}"]`);
                const toBar = document.querySelector(`.task-bar[data-task-id="${toTask.id}"]`);

                if (!fromBar || !toBar) return;

                const fromRect = fromBar.getBoundingClientRect();
                const toRect = toBar.getBoundingClientRect();
                const containerRect = document.querySelector('.gantt-chart').getBoundingClientRect();

                const fromX = fromRect.right - containerRect.left;
                const fromY = fromRect.top + fromRect.height / 2 - containerRect.top;
                const toX = toRect.left - containerRect.left;
                const toY = toRect.top + toRect.height / 2 - containerRect.top;

                // رسم خط التبعية
                const line = document.createElement('div');
                line.className = 'dependency-line';

                const length = Math.sqrt(Math.pow(toX - fromX, 2) + Math.pow(toY - fromY, 2));
                const angle = Math.atan2(toY - fromY, toX - fromX) * 180 / Math.PI;

                line.style.width = `${length}px`;
                line.style.left = `${fromX}px`;
                line.style.top = `${fromY}px`;
                line.style.transform = `rotate(${angle}deg)`;
                line.style.transformOrigin = '0 0';

                // رسم السهم
                const arrow = document.createElement('div');
                arrow.className = 'dependency-arrow';
                arrow.style.left = `${toX - 5}px`;
                arrow.style.top = `${toY - 5}px`;

                dependencyLines.appendChild(line);
                dependencyLines.appendChild(arrow);
            }

            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'completed':
                        return 'bg-success';
                    case 'in_progress':
                    case 'active':
                        return 'bg-primary';
                    case 'pending':
                        return 'bg-warning text-dark';
                    case 'delayed':
                        return 'bg-danger';
                    default:
                        return 'bg-secondary';
                }
            }

            function getStatusText(status) {
                const statusMap = {
                    'completed': '{{ __('general.completed') }}',
                    'in_progress': '{{ __('general.in_progress') }}',
                    'active': '{{ __('general.in_progress') }}',
                    'pending': '{{ __('general.pending') }}',
                    'delayed': 'متأخر'
                };
                return statusMap[status] || '{{ __('general.unknown') }}';
            }

            function showTaskDetails(task) {
                const modal = new bootstrap.Modal(document.getElementById('taskModal'));

                document.getElementById('taskModalTitle').textContent = task.name;
                document.getElementById('taskModalBody').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>{{ __('general.general_info') }}</h6>
                    <ul class="list-unstyled">
<li>
  <strong>{{ __('general.start_date') }}:</strong>
  ${ new Date(task.start_date).toLocaleDateString('en-GB') }
</li>
<li>
  <strong>{{ __('general.end_date') }}:</strong>
  ${ new Date(task.end_date).toLocaleDateString('en-GB') }
</li>

                        <li><strong>{{ __('general.status') }}:</strong> <span class="badge ${getStatusBadgeClass(task.status)}">${getStatusText(task.status)}</span></li>

                        ${task.is_critical ? '<li><strong>{{ __('general.critical_task') }}:</strong> <span class="badge bg-danger">{{ __('general.yes') }}</span></li>' : ''}
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>{{ __('general.progress') }}</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar ${task.status === 'completed' ? 'bg-success' : 'bg-primary'}"
                             style="width: ${task.progress}%">${task.progress}%</div>
                    </div>
                    <ul class="list-unstyled">
                        <li><strong>{{ __('general.completed_quantity') }}:</strong> ${task.completed_quantity} ${task.unit}</li>
                        <li><strong>{{ __('general.total_quantity') }}:</strong> ${task.total_quantity} ${task.unit}</li>
                        <li><strong>{{ __('general.estimated_daily_qty') }}:</strong> ${task.estimated_daily_qty} ${task.unit}</li>
                        <li><strong>{{ __('general.remaining_days') }}:</strong> ${calculateRemainingDays(task)}</li>
                    </ul>
                </div>
            </div>
            ${task.description ? `<div class="mt-3"><h6>{{ __('general.description') }}</h6><p>${task.description}</p></div>` : ''}
        `;

                // تحديث رابط إضافة التقدم
                document.getElementById('addProgressBtn').href =
                    `{{ route('progress.daily-progress.create') }}?project_id={{ $project->id }}&item_id=${task.id}`;

                modal.show();
            }

            function calculateRemainingDays(task) {
                const today = new Date();
                const endDate = new Date(task.end_date);
                const diffTime = endDate - today;
                const diffDays = Math.ceil(diffTime / msPerDay);
                return diffDays > 0 ? diffDays : 0;
            }

            function showAdvancedTooltip(event, task) {
                const tooltip = document.createElement('div');
                tooltip.className = 'advanced-tooltip';
                tooltip.innerHTML = `
            <strong>${task.name}</strong><br>
            {{ __('general.progress') }}: ${task.progress}%<br>
            {{ __('general.start_date') }}: ${new Date(task.start_date).toLocaleDateString('ar')}<br>
            {{ __('general.end_date') }}: ${new Date(task.end_date).toLocaleDateString('ar')}<br>
            {{ __('general.status') }}: ${getStatusText(task.status)}<br>
            Unit: ${task.unit}<br>
            ${task.work_item && task.work_item.category ? `Category: ${task.work_item.category}<br>` : ''}
            ${task.is_critical ? '{{ __('general.critical_task') }}: {{ __('general.yes') }}<br>' : ''}
            ${task.description ? `{{ __('general.description') }}: ${task.description.substring(0, 100)}...` : ''}
            ${task.work_item && task.work_item.notes ? `<br>Notes: ${task.work_item.notes.substring(0, 100)}...` : ''}
        `;

                document.body.appendChild(tooltip);

                const updateTooltipPosition = (e) => {
                    tooltip.style.left = (e.clientX + 10) + 'px';
                    tooltip.style.top = (e.clientY + 10) + 'px';
                };

                updateTooltipPosition(event);
                event.target.addEventListener('mousemove', updateTooltipPosition);

                window.advancedTooltip = {
                    element: tooltip,
                    updateFunction: updateTooltipPosition
                };
            }

            function hideAdvancedTooltip() {
                if (window.advancedTooltip) {
                    window.advancedTooltip.element.remove();
                    window.advancedTooltip = null;
                }
            }

            function refreshChart() {
                const refreshBtn = document.querySelector('.btn-primary-modern');
                const originalText = refreshBtn.innerHTML;

                refreshBtn.innerHTML =
                    '<i class="fas fa-spinner fa-spin me-2"></i>{{ __('general.refreshing') }}...';
                refreshBtn.disabled = true;

                loadProjectData().then(() => {
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.disabled = false;
                    showToast('{{ __('general.chart_updated_successfully') }}', 'success');
                }).catch(() => {
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.disabled = false;
                    showToast('{{ __('general.error_refreshing_chart') }}', 'error');
                });
            }

            async function exportToPNG() {
                showToast('{{ __('general.preparing_export') }}...', 'info');

                // محاكاة عملية التصدير
                await new Promise(resolve => setTimeout(resolve, 1500));
                showToast('{{ __('general.export_png_success') }}', 'success');
            }

            async function exportToPDF() {
                showToast('{{ __('general.preparing_export') }}...', 'info');

                // محاكاة عملية التصدير
                await new Promise(resolve => setTimeout(resolve, 2000));
                showToast('{{ __('general.export_pdf_success') }}', 'success');
            }

            async function exportToExcel() {
                showToast('{{ __('general.preparing_export') }}...', 'info');

                // محاكاة عملية التصدير
                await new Promise(resolve => setTimeout(resolve, 1000));
                showToast('{{ __('general.export_excel_success') }}', 'success');
            }

            function showToast(message, type) {
                // إزالة أي toast موجود مسبقاً
                const existingToasts = document.querySelectorAll('.custom-toast');
                existingToasts.forEach(toast => toast.remove());

                const toast = document.createElement('div');
                toast.className =
                    `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} custom-toast position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
                toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 3000);
            }

            // جعل الدوال متاحة عالميًا
            window.refreshChart = refreshChart;
            window.exportToPNG = exportToPNG;
            window.exportToPDF = exportToPDF;
            window.exportToExcel = exportToExcel;
            window.showAdvancedTooltip = showAdvancedTooltip;
            window.hideAdvancedTooltip = hideAdvancedTooltip;

            // تحميل البيانات عند تحميل الصفحة
            loadProjectData();

            // تحديث المخطط عند تغيير حجم النافذة
            window.addEventListener('resize', () => {
                setTimeout(() => {
                    positionTodayLine();
                    renderDependencies();
                }, 100);
            });
        });
    </script>
@endsection
