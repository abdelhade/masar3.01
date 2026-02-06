


@if(!isset($csrfIncluded))
    @csrf
@endif


@php
    $project = $project ?? null;
    $projectItems = $projectItems ?? [];
    $workItems = $workItems ?? [];
    $templates = $templates ?? collect([]);
    $clients = $clients ?? collect([]);
    $employees = $employees ?? collect([]);
    $projectTypes = $projectTypes ?? collect([]);
    
    // Convert subprojects to array with all fields including unit
    $subprojectsArray = [];
    if (isset($subprojects) && $subprojects) {
        foreach ($subprojects as $subproject) {
            $subprojectsArray[] = [
                'id' => $subproject->id ?? null,
                'name' => $subproject->name ?? '',
                'start_date' => $subproject->start_date ? $subproject->start_date->format('Y-m-d') : null,
                'end_date' => $subproject->end_date ? $subproject->end_date->format('Y-m-d') : null,
                'total_quantity' => $subproject->total_quantity ?? 0,
                'unit' => $subproject->unit ?? null,
                'description' => $subproject->description ?? null,
            ];
        }
    }
@endphp

<script>
    window.projectFormData = <?php echo json_encode([
                                    'workItems' => $workItems ?? [],
                                    'project' => $project ?? null,
                                    'projectItems' => $projectItems ?? [],
                                    'subprojects' => $subprojectsArray
                                ]); ?>;
</script>

<div class="container-fluid">

    
    
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                {{ __('general.basic_info') }}
            </h5>
        </div>
        <div class="card-body">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="project-name-input" class="form-label fw-bold">
                        <i class="fas fa-folder-open me-1"></i>
                        {{ __('general.project_name') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                        name="name"
                        id="project-name-input"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', optional($project)->name) }}"
                        required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="client-id-select" class="form-label fw-bold">
                        <i class="fas fa-user-tie me-1"></i>
                        {{ __('general.client') }}
                        <span class="text-danger">*</span>
                    </label>
                    <select name="client_id"
                        id="client-id-select"
                        class="form-select @error('client_id') is-invalid @enderror"
                        required>
                        <option value="">{{ __('general.select_client') }}</option>
                        @foreach ($clients as $client)
                        <option value="{{ $client->id }}"
                            @selected(old('client_id', optional($project)->client_id) == $client->id)>
                            {{ $client->cname }}
                        </option>
                        @endforeach
                    </select>
                    @error('client_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label fw-bold">
                        <i class="fas fa-tasks me-1"></i>
                        {{ __('general.status') }}
                    </label>
                    <select name="status"
                        id="status"
                        class="form-select @error('status') is-invalid @enderror">
                        <option value="pending" @selected(old('status', optional($project)->status ?: 'pending') == 'pending')>
                            {{ __('general.status_pending') }}
                        </option>
                        <option value="active" @selected(old('status', optional($project)->status) == 'active')>
                            {{ __('general.status_active') }}
                        </option>
                        <option value="completed" @selected(old('status', optional($project)->status) == 'completed')>
                            {{ __('general.status_completed') }}
                        </option>
                    </select>
                    @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="project_type_id" class="form-label fw-bold">
                        <i class="fas fa-diagram-project me-1"></i>
                        {{ __('general.project__type') }}
                    </label>
                    <select name="project_type_id"
                        id="project_type_id"
                        class="form-select @error('project_type_id') is-invalid @enderror">
                        <option value="">{{ __('general.select_project_type') }}</option>
                        @foreach ($projectTypes as $type)
                        <option value="{{ $type->id }}"
                            @selected(old('project_type_id', optional($project)->project_type_id) == $type->id)>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('project_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="start_date" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ __('general.start_date') }}
                    </label>
                    <input type="date"
                        name="start_date"
                        id="start_date"
                        class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', ($project && $project->start_date) ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}">
                    @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="end_date" class="form-label fw-bold">
                        <i class="fas fa-calendar-check me-1"></i>
                        {{ __('general.end_date') }}
                    </label>
                    <input type="date"
                        name="end_date"
                        id="end_date"
                        class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date', ($project && $project->end_date) ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '') }}"
                        readonly>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('general.auto_calculated') }}
                    </small>
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            
            <div class="mb-3">
                <label for="description" class="form-label fw-bold">
                    <i class="fas fa-align-left me-1"></i>
                    {{ __('general.description') }}
                </label>
                <textarea name="description"
                    id="description"
                    class="form-control @error('description') is-invalid @enderror"
                    rows="3"
                    maxlength="1000"
                    placeholder="{{ __('general.description_placeholder') }}">{{ old('description', optional($project)->description) }}</textarea>
                <div class="form-text text-end">
                    <span id="char-count">0</span> / 1000
                </div>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    
    
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-clock me-2"></i>
                {{ __('general.work_schedule') }}
            </h5>
        </div>
        <div class="card-body">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="working_days" class="form-label fw-bold">
                        <i class="fas fa-calendar-week me-1"></i>
                        {{ __('general.working_days_per_week') }}
                        <span class="badge bg-info ms-2">{{ __('general.auto_calculated') }}</span>
                    </label>
                    <input type="number"
                        name="working_days"
                        id="working_days"
                        class="form-control @error('working_days') is-invalid @enderror"
                        min="1"
                        max="7"
                        readonly
                        style="background-color: #e9ecef; cursor: not-allowed;"
                        value="{{ old('working_days', optional($project)->working_days ?? 5) }}">
                    <small class="form-text text-muted">{{ __('general.working_days_auto_hint') }}</small>
                    @error('working_days')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="daily_work_hours" class="form-label fw-bold">
                        <i class="fas fa-business-time me-1"></i>
                        {{ __('general.daily_work_hours') }}
                    </label>
                    <input type="number"
                        name="daily_work_hours"
                        id="daily_work_hours"
                        class="form-control @error('daily_work_hours') is-invalid @enderror"
                        min="1"
                        max="24"
                        value="{{ old('daily_work_hours', optional($project)->daily_work_hours ?? 8) }}">
                    <small class="form-text text-muted">{{ __('general.daily_work_hours_hint') }}</small>
                    @error('daily_work_hours')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            
            <div class="mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-umbrella-beach me-1"></i>
                    {{ __('general.weekly_holidays') }}
                </label>

                @php
                $oldHolidays = old('weekly_holidays', optional($project)->weekly_holidays ?? '');
                $holidaysArray = is_array($oldHolidays)
                ? $oldHolidays
                : ($oldHolidays ? explode(',', $oldHolidays) : []);

                $daysOfWeek = [
                'sunday' => ['value' => 0, 'icon' => '🌞', 'short' => 'Su'],
                'monday' => ['value' => 1, 'icon' => '🌙', 'short' => 'Mo'],
                'tuesday' => ['value' => 2, 'icon' => '⚡', 'short' => 'Tu'],
                'wednesday' => ['value' => 3, 'icon' => '🌟', 'short' => 'We'],
                'thursday' => ['value' => 4, 'icon' => '⭐', 'short' => 'Th'],
                'friday' => ['value' => 5, 'icon' => '🕌', 'short' => 'Fr'],
                'saturday' => ['value' => 6, 'icon' => '🎯', 'short' => 'Sa'],
                ];
                @endphp

                <div class="d-flex flex-wrap gap-2">
                    @foreach ($daysOfWeek as $dayName => $dayData)
                    <div class="flex-fill" style="min-width: 80px;">
                        <input class="btn-check holiday-checkbox"
                            type="checkbox"
                            id="holiday-{{ $dayName }}"
                            value="{{ $dayData['value'] }}"
                            @checked(in_array((string)$dayData['value'], $holidaysArray))>
                        <label class="btn btn-outline-danger w-100 d-flex flex-column align-items-center py-2" 
                               for="holiday-{{ $dayName }}"
                               style="transition: all 0.3s ease;">
                            <span style="font-size: 1.5rem;">{{ $dayData['icon'] }}</span>
                            <small class="mt-1">{{ __('general.' . $dayName) }}</small>
                        </label>
                    </div>
                    @endforeach
                </div>

                <input type="hidden" 
                       name="weekly_holidays" 
                       id="weekly-holidays-input" 
                       value="{{ old('weekly_holidays', optional($project)->weekly_holidays ?? '') }}">
                <small class="form-text text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('general.weekly_holidays_hint') }}
                </small>
            </div>
        </div>
    </div>

    
    
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i>
                    {{ __('general.select_templates') }}
                    <span class="badge bg-light text-dark ms-2">{{ __('general.optional') }}</span>
                </h5>
                <div class="flex-grow-1" style="max-width: 300px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-dark">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="templates-filter" 
                               class="form-control" 
                               placeholder="{{ __('general.search_templates') }}...">
                        <button type="button" 
                                class="btn btn-outline-light" 
                                id="clear-templates-filter"
                                title="{{ __('general.clear_filter') }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @php
            $templatesCollection = is_array($templates) ? collect($templates) : $templates;
            @endphp

            @if($templatesCollection && $templatesCollection->count() > 0)
            <div class="list-group" id="templates-list" style="max-height: 400px; overflow-y: auto;">
                @foreach ($templatesCollection as $template)
                @php
                $templateArray = is_array($template) ? $template : $template->toArray();
                $templateId = $templateArray['id'] ?? ($template->id ?? '');
                $templateName = $templateArray['name'] ?? ($template->name ?? '');
                $templateDesc = $templateArray['description'] ?? ($template->description ?? '');
                $templateType = $templateArray['type'] ?? 'template';
                
                // Get items_count - prioritize from model attribute, then from array, then calculate
                $templateCount = 0;
                if (is_object($template)) {
                    // Check if items_count attribute exists (from withCount)
                    if (isset($template->items_count)) {
                        $templateCount = $template->items_count;
                        $templateArray['items_count'] = $template->items_count;
                    } elseif (method_exists($template, 'items')) {
                        // Fallback: count items relationship
                        $templateCount = $template->items()->count();
                        $templateArray['items_count'] = $templateCount;
                    }
                } elseif (isset($templateArray['items_count'])) {
                    $templateCount = $templateArray['items_count'];
                }
                
                // Ensure items_count is in the array for JSON encoding
                if (!isset($templateArray['items_count'])) {
                    $templateArray['items_count'] = $templateCount;
                }
                @endphp

                <div class="list-group-item template-item" data-template-name="{{ strtolower($templateName) }}" data-template-desc="{{ strtolower($templateDesc ?? '') }}">
                    <div class="form-check">
                        <input class="form-check-input template-checkbox"
                            type="checkbox"
                            id="template_{{ $templateId }}"
                            value="{{ $templateId }}"
                            data-template='@json($templateArray)'>
                        <label class="form-check-label w-100" for="template_{{ $templateId }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        @if($templateType === 'draft')
                                        <i class="fas fa-file-alt me-1 text-warning"></i>
                                        @else
                                        <i class="fas fa-layer-group me-1 text-primary"></i>
                                        @endif
                                        {{ $templateName }}
                                    </h6>
                                    @if ($templateDesc)
                                    <p class="mb-0 text-muted small">{{ $templateDesc }}</p>
                                    @endif
                                </div>
                                <span class="badge {{ $templateType === 'draft' ? 'bg-warning' : 'bg-primary' }} rounded-pill">
                                    {{ $templateCount }} {{ __('general.items') }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            <small class="form-text text-muted d-block mt-2">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('general.multiple_template_selection_hint') }}
            </small>
            @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                {{ __('general.no_templates_available') }}
            </div>
            @endif
        </div>
    </div>

    
    
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-search me-2"></i>
                {{ __('general.select_items_for_project') }}
            </h5>
        </div>
        <div class="card-body">
            
            <div class="mb-3" style="position: relative;">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text"
                        id="work-item-search"
                        class="form-control"
                        placeholder="{{ __('general.search_work_items') }}"
                        autocomplete="off">
                    <span class="input-group-text" id="search-loading" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                    </span>
                </div>

                
                <div id="search-results"
                    class="list-group position-absolute w-100 shadow-lg"
                    style="display: none; z-index: 1000; max-height: 400px; overflow-y: auto;">
                    <div class="list-group-item bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold" id="results-count">0 {{ __('general.results') }}</span>
                        <button type="button" class="btn-close btn-sm" id="close-search"></button>
                    </div>
                    <div id="search-results-list"></div>
                    <div class="list-group-item bg-light text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('general.click_to_add_item') }}
                        </small>
                    </div>
                </div>
            </div>

            <small class="form-text text-muted">
                <i class="fas fa-keyboard me-1"></i>
                {{ __('general.type_to_search_items') }}
            </small>
        </div>
    </div>

    
    
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                {{ __('general.selected_items') }}
            </h5>
            <small class="text-white-50">
                <i class="fas fa-grip-vertical me-1"></i>
                {{ __('general.drag_to_reorder') }}
            </small>
        </div>
        <div class="card-body">
            
            <div class="row mb-3">
                
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-filter"></i>
                        </span>
                        <input type="text"
                            id="items-filter"
                            class="form-control"
                            placeholder="{{ __('general.filter_items') }}">
                        <button type="button"
                            id="reset-filter"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                
                <div class="col-md-3 text-center">
                    <div class="btn-group" role="group" aria-label="View mode">
                        <button type="button"
                            class="btn btn-outline-primary active"
                            id="flat-view-btn"
                            title="عرض عادي">
                            <i class="fas fa-list"></i> عادي
                        </button>
                        <button type="button"
                            class="btn btn-outline-primary"
                            id="grouped-view-btn"
                            title="عرض حسب الفئات">
                            <i class="fas fa-layer-group"></i> مجمع
                        </button>
                    </div>
                </div>

                
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-tasks"></i>
                        </span>
                        <select class="form-select" id="bulk-action-select">
                            <option value="">-- عمليات جماعية --</option>
                            <option value="delete">🗑️ حذف المحدد</option>
                            <option value="duplicate">📋 نسخ المحدد</option>
                            <option value="move">📁 نقل لمشروع فرعي</option>
                            <option value="export">📊 تصدير المحدد (CSV)</option>
                        </select>
                        <button type="button"
                            class="btn btn-primary"
                            id="bulk-execute-btn"
                            title="تنفيذ العملية المحددة">
                            <i class="fas fa-play"></i>
                            تنفيذ
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle me-1"></i>
                        حدد البنود أولاً ثم اختر العملية
                    </small>
                </div>
            </div>

    
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="selected-items-table">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAllItems" class="form-check-input">
                            </th>
                            <th style="width: 40px;" class="text-center">
                                <i class="fas fa-grip-vertical"></i>
                            </th>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="min-width: 300px;">{{ __('general.item_name') }}</th>
                            <th style="min-width: 200px;">المشروع الفرعي</th>
                            <th style="min-width: 350px;">{{ __('general.notes') }}</th>
                            <th style="width: 100px;" class="text-center">قابل للقياس</th>
                            <th style="width: 120px;">{{ __('general.total_quantity') }}</th>
                            <th style="width: 120px;">{{ __('general.estimated_daily_qty') }}</th>
                            <th style="width: 100px;">{{ __('general.estimated_duration') }}</th>
                            <th style="width: 150px;">{{ __('general.predecessor') }}</th>
                            <th style="width: 150px;">{{ __('general.dependency_type') }}</th>
                            <th style="width: 100px;">{{ __('general.lag') }}</th>
                            <th style="width: 140px;">{{ __('general.start_date') }}</th>
                            <th style="width: 140px;">{{ __('general.end_date') }}</th>
                            <th style="width: 100px;" class="text-center">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="selected-items-container" class="sortable-table">
                        
                    </tbody>
                </table>
            </div>

            
            <div id="empty-state" class="text-center py-5 d-none">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">{{ __('general.no_items_selected') }}</p>
                <small class="text-muted">{{ __('general.search_items_to_add') }}</small>
            </div>
        </div>
    </div>

    
    
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-map-marker-alt me-2"></i>
                {{ __('general.location_and_team') }}
            </h5>
        </div>
        <div class="card-body">
            
            <div class="mb-4">
                <label for="working_zone" class="form-label fw-bold">
                    <i class="fas fa-map-marked-alt me-1"></i>
                    {{ __('general.working_zone') }}
                </label>
                <input type="text"
                    name="working_zone"
                    id="working_zone"
                    class="form-control @error('working_zone') is-invalid @enderror"
                    value="{{ old('working_zone', optional($project)->working_zone) }}"
                    placeholder="{{ __('general.working_zone_placeholder') }}">
                @error('working_zone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            
            <div class="mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-users me-1"></i>
                    {{ __('general.employees') }}
                </label>

                @if($employees && $employees->count() > 0)
                <div class="row g-3">
                    @foreach ($employees as $employee)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body p-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="employees[]"
                                        id="employee-{{ $employee->id }}"
                                        value="{{ $employee->id }}"
                                        @checked(in_array($employee->id, old('employees', optional($project)->employees ? $project->employees->pluck('id')->toArray() : [])))>
                                    <label class="form-check-label w-100" for="employee-{{ $employee->id }}">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                {{ strtoupper(substr($employee->name, 0, 2)) }}
                                            </div>
                                            <div class="flex-grow-1 small">
                                                <div class="fw-bold">{{ $employee->name }}</div>
                                                @if($employee->position)
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $employee->position }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <small class="form-text text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('general.select_multiple_by_clicking') }}
                </small>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('general.no_employees_available') }}
                </div>
                @endif

                @error('employees')
                <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    
    
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-center">
                
                <div class="d-flex flex-column flex-md-row gap-2">
                    <button type="submit"
                        name="save_type"
                        value="normal"
                        class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>
                        {{ __('general.save') }}
                    </button>

                    @if(!isset($project) || empty($project->id))
                    <button type="submit"
                        name="save_as_draft"
                        value="1"
                        class="btn btn-warning btn-lg">
                        <i class="fas fa-file-alt me-2"></i>
                        {{ __('general.save_as_draft') }}
                    </button>
                    @endif

                    <button type="button"
                        id="saveAsTemplateBtn"
                        class="btn btn-info btn-lg">
                        <i class="fas fa-layer-group me-2"></i>
                        {{ __('general.save_as_template') }}
                    </button>
                </div>

                
                <div class="d-flex gap-2">
                    <a href="{{ route('progress.projects.index') }}"
                        class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>
                        {{ __('general.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- Modal for Save as Template -->
<div class="modal fade" id="saveAsTemplateModal" tabindex="-1" aria-labelledby="saveAsTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveAsTemplateModalLabel">
                    <i class="fas fa-layer-group me-2"></i>{{ __('general.save_as_template') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">{{ __('general.template_save_info') }}</p>
                <div class="mb-3">
                    <label for="templateName" class="form-label">{{ __('general.template_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="templateName" required placeholder="{{ __('general.enter_template_name') }}">
                </div>
                <div class="mb-3">
                    <label for="templateDescription" class="form-label">{{ __('general.description') }}</label>
                    <textarea class="form-control" id="templateDescription" rows="3" placeholder="{{ __('general.enter_template_description') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirmSaveTemplateBtn">
                    <i class="fas fa-save me-1"></i>{{ __('general.save_template') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveAsTemplateBtn = document.getElementById('saveAsTemplateBtn');
    const saveAsTemplateModal = new bootstrap.Modal(document.getElementById('saveAsTemplateModal'));
    const confirmSaveTemplateBtn = document.getElementById('confirmSaveTemplateBtn');
    const templateNameInput = document.getElementById('templateName');
    const templateDescriptionInput = document.getElementById('templateDescription');
    const projectForm = document.getElementById('projectForm');

    if (saveAsTemplateBtn) {
        saveAsTemplateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate form has items
            const itemsContainer = document.getElementById('selected-items-container');
            if (!itemsContainer || itemsContainer.querySelectorAll('tr[data-item-id]').length === 0) {
                alert('{{ __('general.select_at_least_one_work_item') }}');
                return;
            }

            // Reset modal inputs
            templateNameInput.value = '';
            templateDescriptionInput.value = '';
            
            // Show modal
            saveAsTemplateModal.show();
        });
    }

    if (confirmSaveTemplateBtn) {
        confirmSaveTemplateBtn.addEventListener('click', function() {
            const templateName = templateNameInput.value.trim();
            
            if (!templateName) {
                alert('{{ __('general.please_enter_template_name') }}');
                templateNameInput.focus();
                return;
            }

            // Disable button
            confirmSaveTemplateBtn.disabled = true;
            confirmSaveTemplateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.saving') }}...';

            // Get form data
            const formData = new FormData(projectForm);
            
            // Add template name and description
            formData.append('name', templateName);
            formData.append('description', templateDescriptionInput.value.trim());
            
            // Remove project-specific fields
            formData.delete('save_type');
            formData.delete('save_as_draft');
            formData.delete('_token');
            
            // ✅ Add missing fields for template items (to match project_items structure)
            // Get all item rows from the form
            const itemRows = document.querySelectorAll('#selected-items-container tr[data-item-id]');
            itemRows.forEach(row => {
                const rowId = row.dataset.itemId;
                
                // Get total_quantity and add as default_quantity (for template compatibility)
                const totalQtyInput = row.querySelector(`input[name="items[${rowId}][total_quantity]"]`);
                if (totalQtyInput && totalQtyInput.value) {
                    if (!formData.has(`items[${rowId}][default_quantity]`)) {
                        formData.append(`items[${rowId}][default_quantity]`, totalQtyInput.value);
                    }
                }
                
                // Get end_date value for planned_end_date fallback
                const endDateInput = row.querySelector(`input[name="items[${rowId}][end_date]"]`);
                const endDate = endDateInput ? endDateInput.value : '';
                
                // Add planned_end_date (use end_date as fallback)
                if (!formData.has(`items[${rowId}][planned_end_date]`)) {
                    formData.append(`items[${rowId}][planned_end_date]`, endDate);
                }
                
                // Add shift (empty by default)
                if (!formData.has(`items[${rowId}][shift]`)) {
                    formData.append(`items[${rowId}][shift]`, '');
                }
                
                // Add item_label (empty by default)
                if (!formData.has(`items[${rowId}][item_label]`)) {
                    formData.append(`items[${rowId}][item_label]`, '');
                }
                
                // Add daily_quantity (empty by default)
                if (!formData.has(`items[${rowId}][daily_quantity]`)) {
                    formData.append(`items[${rowId}][daily_quantity]`, '');
                }
            });
            
            // ✅ Add project settings (working_days, daily_work_hours, weekly_holidays)
            const workingDaysInput = document.getElementById('working_days');
            if (workingDaysInput && workingDaysInput.value) {
                formData.append('working_days', workingDaysInput.value);
            }
            
            const dailyWorkHoursInput = document.getElementById('daily_work_hours');
            if (dailyWorkHoursInput && dailyWorkHoursInput.value) {
                formData.append('daily_work_hours', dailyWorkHoursInput.value);
            }
            
            const weeklyHolidaysInput = document.getElementById('weekly-holidays-input');
            if (weeklyHolidaysInput && weeklyHolidaysInput.value) {
                formData.append('weekly_holidays', weeklyHolidaysInput.value);
            }
            
            // ✅ Collect subprojects from form (they are already in formData, but ensure they're included)
            // Subprojects are already collected by FormData from hidden inputs with name="subprojects[...]"
            
            // Send to template store endpoint
            fetch('{{ route("progress.project-templates.store-from-form") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || '{{ __('general.template_saved_successfully') }}');
                    saveAsTemplateModal.hide();
                } else {
                    alert(data.message || 'حدث خطأ أثناء حفظ القالب');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء حفظ القالب: ' + error.message);
            })
            .finally(() => {
                confirmSaveTemplateBtn.disabled = false;
                confirmSaveTemplateBtn.innerHTML = '<i class="fas fa-save me-1"></i>{{ __('general.save_template') }}';
            });
        });
    }
});
</script>

<!-- Modal for Moving Items to Subproject -->
<div class="modal fade" id="moveToSubprojectModal" tabindex="-1" aria-labelledby="moveToSubprojectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="moveToSubprojectModalLabel">
                    <i class="fas fa-folder-open me-2"></i>
                    نقل البنود إلى مشروع فرعي
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    سيتم نقل <strong id="moveItemsCount">0</strong> بند إلى المشروع الفرعي المحدد
                </p>
                <div class="mb-3">
                    <label for="subprojectSelect" class="form-label fw-bold">
                        <i class="fas fa-folder me-1"></i>
                        اختر المشروع الفرعي:
                    </label>
                    <select class="form-select" id="subprojectSelect" size="8">
                        <option value="">-- مشروع جديد --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="newSubprojectName" class="form-label fw-bold">
                        <i class="fas fa-plus-circle me-1"></i>
                        أو أدخل اسم مشروع فرعي جديد:
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="newSubprojectName" 
                           placeholder="اسم المشروع الفرعي الجديد">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1"></i>
                        اتركه فارغاً إذا اخترت مشروعاً من القائمة أعلاه
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </button>
                <button type="button" class="btn btn-primary" id="confirmMoveBtn">
                    <i class="fas fa-check me-1"></i>
                    نقل
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/project-form.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const templatesFilter = document.getElementById('templates-filter');
    const clearFilterBtn = document.getElementById('clear-templates-filter');
    const templatesList = document.getElementById('templates-list');
    
    if (templatesFilter && templatesList) {
        // Filter templates on input
        templatesFilter.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const templateItems = templatesList.querySelectorAll('.template-item');
            let visibleCount = 0;
            
            templateItems.forEach(item => {
                const templateName = item.dataset.templateName || '';
                const templateDesc = item.dataset.templateDesc || '';
                const matches = templateName.includes(searchTerm) || templateDesc.includes(searchTerm);
                
                if (matches || searchTerm === '') {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show message if no results
            let noResultsMsg = templatesList.querySelector('.no-results-message');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'alert alert-info no-results-message mb-0';
                    noResultsMsg.innerHTML = '<i class="fas fa-info-circle me-2"></i>{{ __('general.no_templates_found') }}';
                    templatesList.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = '';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        });
        
        // Clear filter
        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', function() {
                templatesFilter.value = '';
                templatesFilter.dispatchEvent(new Event('input'));
                templatesFilter.focus();
            });
        }
        
        // Clear filter on Escape key
        templatesFilter.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
            }
        });
    }
});

// Work Items Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('work-item-search');
    const searchResults = document.getElementById('search-results');
    const searchResultsList = document.getElementById('search-results-list');
    const resultsCount = document.getElementById('results-count');
    const closeSearchBtn = document.getElementById('close-search');
    const searchLoading = document.getElementById('search-loading');
    
    let searchTimeout;
    const workItems = @json($workItems ?? []);
    
    if (searchInput && searchResults) {
        // Search on input
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchLoading.style.display = 'inline-block';
            
            searchTimeout = setTimeout(() => {
                // Filter work items
                const filtered = workItems.filter(item => 
                    item.name.toLowerCase().includes(query) ||
                    (item.unit && item.unit.toLowerCase().includes(query)) ||
                    (item.description && item.description.toLowerCase().includes(query))
                ).slice(0, 50);
                
                // Display results
                searchResultsList.innerHTML = '';
                resultsCount.textContent = `${filtered.length} {{ __('general.results') }}`;
                
                if (filtered.length === 0) {
                    searchResultsList.innerHTML = `
                        <div class="list-group-item text-center text-muted">
                            <i class="fas fa-inbox me-2"></i>
                            {{ __('general.no_results_found') }}
                        </div>
                    `;
                } else {
                    filtered.forEach(item => {
                        const resultItem = document.createElement('button');
                        resultItem.type = 'button';
                        resultItem.className = 'list-group-item list-group-item-action';
                        resultItem.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${item.name}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-ruler me-1"></i>${item.unit || '-'}
                                        ${item.category ? `<i class="fas fa-folder ms-2 me-1"></i>${item.category.name}` : ''}
                                    </small>
                                </div>
                                <i class="fas fa-plus-circle text-primary"></i>
                            </div>
                        `;
                        
                        resultItem.addEventListener('click', function() {
                            // Trigger Alpine.js addItem method if available
                            // Or dispatch custom event
                            const event = new CustomEvent('add-work-item', { 
                                detail: { item: item } 
                            });
                            document.dispatchEvent(event);
                            
                            // Clear search
                            searchInput.value = '';
                            searchResults.style.display = 'none';
                            searchLoading.style.display = 'none';
                        });
                        
                        searchResultsList.appendChild(resultItem);
                    });
                }
                
                searchResults.style.display = 'block';
                searchLoading.style.display = 'none';
            }, 300);
        });
        
        // Close search results
        if (closeSearchBtn) {
            closeSearchBtn.addEventListener('click', function() {
                searchResults.style.display = 'none';
                searchInput.value = '';
            });
        }
        
        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        // Close on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchResults.style.display = 'none';
                this.value = '';
            }
        });
    }
    
    // Listen for add-work-item event
    document.addEventListener('add-work-item', function(e) {
        const item = e.detail.item;
        addWorkItemToTable(item);
    });
    
    // Function to add work item to table
    function addWorkItemToTable(item) {
        const container = document.getElementById('selected-items-container');
        if (!container) return;
        
        const rowId = `item_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        const rowIndex = container.querySelectorAll('tr').length;
        
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', rowId);
        row.setAttribute('data-work-item-id', item.id);
        row.className = 'item-row';
        
        row.innerHTML = `
            <td class="text-center">
                <i class="fas fa-grip-vertical drag-handle text-muted" style="cursor: move;"></i>
            </td>
            <td class="text-center">${rowIndex + 1}</td>
            <td>
                <input type="hidden" name="items[${rowId}][work_item_id]" value="${item.id}">
                <input type="hidden" name="items[${rowId}][name]" value="${item.name}">
                <strong>${item.name}</strong>
                ${item.category ? `<br><small class="text-muted"><i class="fas fa-folder me-1"></i>${item.category.name}</small>` : ''}
            </td>
            <td>
                <input type="text" name="items[${rowId}][unit]" class="form-control form-control-sm" value="${item.unit || ''}" readonly>
            </td>
            <td>
                <input type="text" name="items[${rowId}][subproject_name]" class="form-control form-control-sm" placeholder="{{ __('general.optional') }}">
            </td>
            <td>
                <input type="number" name="items[${rowId}][total_quantity]" class="form-control form-control-sm quantity-input" value="1" min="0" step="0.01" required>
            </td>
            <td>
                <input type="number" name="items[${rowId}][estimated_daily_qty]" class="form-control form-control-sm daily-qty-input" value="1" min="0" step="0.01" required>
            </td>
            <td>
                <input type="number" name="items[${rowId}][duration]" class="form-control form-control-sm duration-input" value="1" min="1" readonly>
            </td>
            <td>
                <input type="date" name="items[${rowId}][start_date]" class="form-control form-control-sm" value="{{ $project->start_date ?? date('Y-m-d') }}">
            </td>
            <td>
                <input type="date" name="items[${rowId}][end_date]" class="form-control form-control-sm" value="{{ $project->start_date ?? date('Y-m-d') }}">
            </td>
            <td>
                <select name="items[${rowId}][predecessor]" class="form-select form-select-sm predecessor-select">
                    <option value="">{{ __('general.none') }}</option>
                </select>
            </td>
            <td>
                <select name="items[${rowId}][dependency_type]" class="form-select form-select-sm">
                    <option value="end_to_start">{{ __('general.end_to_start') }}</option>
                    <option value="start_to_start">{{ __('general.start_to_start') }}</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowId}][lag]" class="form-control form-control-sm" value="0">
            </td>
            <td class="text-center">
                <div class="form-check form-switch">
                    <input type="checkbox" name="items[${rowId}][is_measurable]" class="form-check-input" checked value="1">
                </div>
            </td>
            <td>
                <textarea name="items[${rowId}][notes]" class="form-control form-control-sm" rows="1"></textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="{{ __('general.delete') }}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        container.appendChild(row);
        
        // Update row numbers
        updateRowNumbers();
        
        // Update predecessor dropdowns
        updatePredecessorDropdowns();
        
        // Add event listeners for this row
        addRowEventListeners(row);
        
        // Show success message
        showToast('success', '{{ __('general.item_added_successfully') }}');
    }
    
    // Update row numbers
    function updateRowNumbers() {
        const rows = document.querySelectorAll('#selected-items-container tr');
        rows.forEach((row, index) => {
            const numberCell = row.querySelector('td:nth-child(2)');
            if (numberCell) {
                numberCell.textContent = index + 1;
            }
        });
    }
    
    // Update predecessor dropdowns
    function updatePredecessorDropdowns() {
        const rows = document.querySelectorAll('#selected-items-container tr');
        const items = Array.from(rows).map((row, index) => ({
            id: row.getAttribute('data-item-id'),
            name: row.querySelector('input[name*="[name]"]')?.value || `البند ${index + 1}`,
            index: index
        }));
        
        rows.forEach((row, currentIndex) => {
            const select = row.querySelector('.predecessor-select');
            if (!select) return;
            
            const currentValue = select.value;
            select.innerHTML = '<option value="">{{ __('general.none') }}</option>';
            
            items.forEach((item, itemIndex) => {
                if (itemIndex < currentIndex) { // Only show previous items
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${itemIndex + 1}. ${item.name}`;
                    if (item.id === currentValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                }
            });
        });
    }
    
    // Add event listeners to a row
    function addRowEventListeners(row) {
        // Remove button
        const removeBtn = row.querySelector('.remove-item-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (confirm('{{ __('general.confirm_delete') }}')) {
                    row.remove();
                    updateRowNumbers();
                    updatePredecessorDropdowns();
                }
            });
        }
        
        // Calculate duration on quantity/daily change
        const quantityInput = row.querySelector('.quantity-input');
        const dailyQtyInput = row.querySelector('.daily-qty-input');
        const durationInput = row.querySelector('.duration-input');
        
        function calculateDuration() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const dailyQty = parseFloat(dailyQtyInput.value) || 1;
            
            if (quantity > 0 && dailyQty > 0) {
                const duration = Math.ceil(quantity / dailyQty);
                durationInput.value = duration;
            }
        }
        
        if (quantityInput) quantityInput.addEventListener('input', calculateDuration);
        if (dailyQtyInput) dailyQtyInput.addEventListener('input', calculateDuration);
    }
    
    // Show toast notification
    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Initialize existing rows
    document.querySelectorAll('#selected-items-container tr').forEach(row => {
        addRowEventListeners(row);
    });
    
    // Initialize Sortable if available
    if (typeof Sortable !== 'undefined') {
        const container = document.getElementById('selected-items-container');
        if (container) {
            new Sortable(container, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function() {
                    updateRowNumbers();
                    updatePredecessorDropdowns();
                }
            });
        }
    }
});
</script>
