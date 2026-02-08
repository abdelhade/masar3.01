@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
@endsection
@section('title', __('projects.list'))

@section('content')
    <div class="m-2 d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0">{{ __('projects.list') }}</h5>

            
            @can('projects-list')
                <a href="{{ route('progress.projects.drafts') }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-file-alt me-1"></i>
                    {{ __('general.drafts') }}
                    @if (isset($draftsCount) && $draftsCount > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $draftsCount }}</span>
                    @endif
                </a>
            @endcan
        </div>
        @can('create progress-projects')
            <a href="{{ route('progress.projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> {{ __('projects.new') }}
            </a>
        @endcan
    </div>

    
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <label class="form-label text-muted small mb-2">
                        <i class="fas fa-search me-2"></i>{{ __('general.search') }}
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               id="projectSearch" 
                               class="form-control border-0 bg-light" 
                               placeholder="{{ __('general.search_by_name_or_client') }}"
                               style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                
                <div class="col-lg-2 col-md-6 col-sm-12">
                    <label class="form-label text-muted small mb-2">
                        <i class="fas fa-filter me-2"></i>{{ __('general.status') }}
                    </label>
                    <select id="projectStatusFilter" class="form-select border-0 bg-light" style="border-radius: 12px;">
                        <option value="all">{{ __('general.all_statuses') }}</option>
                        <option value="in_progress">{{ __('general.active') }}</option>
                        <option value="completed">{{ __('general.completed') }}</option>
                        <option value="pending">{{ __('general.pending') }}</option>
                    </select>
                </div>

                
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label class="form-label text-muted small mb-2">
                        <i class="fas fa-tag me-2"></i>{{ __('general.type_of_project') }}
                    </label>
                    <select id="projectTypeFilter" class="form-select border-0 bg-light" style="border-radius: 12px;">
                        <option value="all">{{ __('general.all_types') }}</option>
                        @php
                            $uniqueTypes = $projects->pluck('type')->filter()->unique('id');
                        @endphp
                        @foreach($uniqueTypes as $type)
                            @if($type)
                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                
                <div class="col-lg-2 col-md-6 col-sm-12">
                    <label class="form-label text-muted small mb-2">
                        <i class="fas fa-building me-2"></i>{{ __('projects.client') }}
                    </label>
                    <select id="projectClientFilter" class="form-select border-0 bg-light" style="border-radius: 12px;">
                        <option value="all">{{ __('general.all_clients') }}</option>
                        @php
                            $uniqueClients = $projects->pluck('client')->filter()->unique('id');
                        @endphp
                        @foreach($uniqueClients as $client)
                            @if($client)
                                <option value="{{ $client->cname }}">{{ $client->cname }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                
                <div class="col-lg-1 col-md-6 col-sm-12">
                    <button type="button" 
                            id="clearFilters" 
                            class="btn btn-outline-secondary w-100" 
                            title="{{ __('general.clear_filters') }}"
                            style="border-radius: 12px;">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <span id="filterResults">{{ $projects->count() }}</span> 
                            <span>{{ __('general.results') }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 m-2">
        @foreach ($projects as $project)
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card h-100 shadow-lg border-0 project-card" 
                     style="cursor: pointer; 
                            border-radius: 25px; 
                            overflow: hidden;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            background: #ffffff;
                            box-shadow: 0 8px 20px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08);"
                     onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.2), 0 5px 15px rgba(0,0,0,0.1)'"
                     onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08)'"
                     data-project-id="{{ $project->id }}"
                     data-project-name="{{ $project->name }}"
                     data-project-description="{{ $project->description ?? '' }}"
                     data-project-client="{{ $project->client->cname }}"
                     data-project-status="{{ $project->status }}"
                     data-project-type="{{ $project->type ? $project->type->name : __('general.not_specified') }}"
                     data-project-start-date="{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d-m-Y') : '' }}"
                     data-project-end-date="{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d-m-Y') : __('general.in_progress') }}"
                     data-project-working-zone="{{ $project->working_zone ?? '' }}"
                     data-project-working-days="{{ $project->working_days ?? '' }}"
                     data-project-daily-hours="{{ $project->daily_work_hours ?? '' }}"
                     data-project-weekly-holidays="{{ $project->weekly_holidays ?? '' }}"
                     data-project-items-count="{{ $project->projectItems->count() ?? 0 }}"
                    @php
                        // ✅ Count only subprojects that have items (includes virtual "بدون فرعي" subproject)
                        $subprojectNamesWithItems = $project->items()->whereNotNull('subproject_name')->distinct()->pluck('subproject_name')->filter()->toArray();
                        $subprojectsWithItemsCount = $project->subprojects->filter(function ($subproject) use ($subprojectNamesWithItems) {
                            return in_array($subproject->name, $subprojectNamesWithItems);
                        })->count();
                        
                        // Add virtual "بدون فرعي" subproject if there are items without subproject
                        $itemsWithoutSubproject = $project->items()->whereNull('subproject_name')->exists();
                        if ($itemsWithoutSubproject) {
                            $subprojectsWithItemsCount++;
                        }
                    @endphp
                    data-project-subprojects-count="{{ $subprojectsWithItemsCount }}"
                     data-project-employees-count="{{ $project->employees->count() ?? 0 }}"
                     data-project-progress="{{ $project->overall_progress ?? 0 }}"
                     data-project-created="{{ $project->created_at ? \Carbon\Carbon::parse($project->created_at)->format('d-m-Y') : '' }}">
                    <div class="card-header border-0"
                         style="background: linear-gradient(135deg, 
                         @switch($project->status)
                             @case('active') #28a745 @break
                             @case('completed') #6c757d @break
                             @case('pending') #ffc107 @break
                         @endswitch
                         , 
                         @switch($project->status)
                             @case('active') #20c997 @break
                             @case('completed') #5a6268 @break
                             @case('pending') #ffb300 @break
                         @endswitch
                         );
                         border-radius: 25px 25px 0 0;
                         padding: 1.5rem;
                         position: relative;
                         overflow: hidden;">
                        <div style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>
                        <div class="d-flex justify-content-between align-items-start mb-2" style="position: relative; z-index: 1;">
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2); font-size: 1.3rem;">{{ $project->name }}</h5>
                                <small class="text-white-50 d-flex align-items-center mb-3" style="opacity: 0.9;">
                                    <i class="fas fa-building me-2"></i>
                                    {{ $project->client->cname }}
                                </small>
                                
                                @if($subprojectsWithItemsCount > 0)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-light mt-2 view-subprojects-btn"
                                            data-project-id="{{ $project->id }}"
                                            onclick="event.stopPropagation();"
                                            style="border-radius: 12px;
                                                   padding: 0.35rem 0.75rem;
                                                   font-size: 0.75rem;
                                                   border: 1px solid rgba(255,255,255,0.4);
                                                   backdrop-filter: blur(10px);
                                                   transition: all 0.3s ease;"
                                            onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-2px)'"
                                            onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                                        <i class="fas fa-sitemap me-1"></i>
                                        {{ __('general.subprojects') }} ({{ $subprojectsWithItemsCount }})
                                    </button>
                                @endif
                            </div>
                            <div>
                                @switch($project->status)
                                    @case('active')
                                        <span class="badge text-white px-3 py-2" 
                                              style="background: rgba(255,255,255,0.25); 
                                                     backdrop-filter: blur(10px);
                                                     border-radius: 15px;
                                                     border: 1px solid rgba(255,255,255,0.3);
                                                     font-weight: 600;
                                                     box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                            <i class="fas fa-check-circle me-1"></i>{{ __('general.active') }}
                                        </span>
                                    @break

                                    @case('completed')
                                        <span class="badge text-white px-3 py-2" 
                                              style="background: rgba(255,255,255,0.25); 
                                                     backdrop-filter: blur(10px);
                                                     border-radius: 15px;
                                                     border: 1px solid rgba(255,255,255,0.3);
                                                     font-weight: 600;
                                                     box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                            <i class="fas fa-check-double me-1"></i>{{ __('general.completed') }}
                                        </span>
                                    @break

                                    @case('pending')
                                        <span class="badge text-white px-3 py-2" 
                                              style="background: rgba(255,255,255,0.25); 
                                                     backdrop-filter: blur(10px);
                                                     border-radius: 15px;
                                                     border: 1px solid rgba(255,255,255,0.3);
                                                     font-weight: 600;
                                                     box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                            <i class="fas fa-clock me-1"></i>{{ __('general.pending') }}
                                        </span>
                                    @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4" style="background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);">
                        
                        @php
                            $progress = $project->overall_progress ?? 0;
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted small fw-semibold">
                                    <i class="fas fa-tasks me-2"></i>
                                    {{ __('general.progress') }}
                                </span>
                                <span class="fw-bold" 
                                      style="font-size: 1.2rem;
                                             background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                             -webkit-background-clip: text;
                                             -webkit-text-fill-color: transparent;
                                             background-clip: text;">
                                    {{ $progress }}%
                                </span>
                            </div>
                            <div class="progress" 
                                 style="height: 12px; 
                                        border-radius: 15px; 
                                        background: #e9ecef;
                                        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
                                        overflow: hidden;">
                                <div class="progress-bar 
                                    @if($progress >= 75) bg-success
                                    @elseif($progress >= 50) bg-info
                                    @elseif($progress >= 25) bg-warning
                                    @else bg-danger
                                    @endif" 
                                    role="progressbar" 
                                    style="width: {{ $progress }}%; 
                                           border-radius: 15px;
                                           transition: width 0.6s ease;
                                           box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                                           background: linear-gradient(90deg, 
                                           @if($progress >= 75) #28a745, #20c997
                                           @elseif($progress >= 50) #17a2b8, #138496
                                           @elseif($progress >= 25) #ffc107, #ffb300
                                           @else #dc3545, #c82333
                                           @endif
                                           );" 
                                    aria-valuenow="{{ $progress }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 info-card" 
                                     style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                                            border-radius: 15px;
                                            border: 1px solid rgba(0,0,0,0.05);
                                            transition: all 0.3s ease;
                                            cursor: default;"
                                     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(102,126,234,0.15)'"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div class="d-flex align-items-center text-muted mb-2">
                                        <i class="fas fa-tag me-2" style="color: #667eea;"></i>
                                        <small class="fw-semibold">{{ __('general.type_of_project') }}</small>
                                    </div>
                                    <div class="fw-bold" style="color: #2d3748;">
                                        {{ $project->type ? $project->type->name : __('general.not_specified') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 info-card" 
                                     style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                                            border-radius: 15px;
                                            border: 1px solid rgba(0,0,0,0.05);
                                            transition: all 0.3s ease;
                                            cursor: default;"
                                     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(72,187,120,0.15)'"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div class="d-flex align-items-center text-muted mb-2">
                                        <i class="fas fa-list-ul me-2" style="color: #48bb78;"></i>
                                        <small class="fw-semibold">{{ __('general.items') }}</small>
                                    </div>
                                    <div class="fw-bold" style="color: #2d3748; font-size: 1.1rem;">
                                        {{ $project->projectItems->count() ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 info-card" 
                                     style="background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
                                            border-radius: 15px;
                                            border: 1px solid rgba(245,101,101,0.2);
                                            transition: all 0.3s ease;
                                            cursor: default;"
                                     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(245,101,101,0.2)'"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div class="d-flex align-items-center text-muted mb-2">
                                        <i class="fas fa-calendar-alt me-2" style="color: #f56565;"></i>
                                        <small class="fw-semibold">{{ __('projects.start_date') }}</small>
                                    </div>
                                    <div class="fw-semibold small" style="color: #742a2a;">
                                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d-m-Y') : __('general.not_specified') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 info-card" 
                                     style="background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
                                            border-radius: 15px;
                                            border: 1px solid rgba(56,178,172,0.2);
                                            transition: all 0.3s ease;
                                            cursor: default;"
                                     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(56,178,172,0.2)'"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <div class="d-flex align-items-center text-muted mb-2">
                                        <i class="fas fa-calendar-check me-2" style="color: #38b2ac;"></i>
                                        <small class="fw-semibold">{{ __('projects.end_date') }}</small>
                                    </div>
                                    <div class="fw-semibold small" style="color: #234e52;">
                                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d-m-Y') : __('general.in_progress') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center"
                         style="border-radius: 0 0 25px 25px;
                                padding: 1.25rem 1.5rem;
                                background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%) !important;
                                border-top: 1px solid rgba(0,0,0,0.05);">
                        <button type="button" class="btn btn-primary btn-sm view-details-btn"
                                data-project-id="{{ $project->id }}"
                                style="border-radius: 12px;
                                       padding: 0.5rem 1.25rem;
                                       font-weight: 600;
                                       box-shadow: 0 4px 12px rgba(0,123,255,0.3);
                                       transition: all 0.3s ease;
                                       border: none;
                                       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,123,255,0.4)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,123,255,0.3)'">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('general.view_details') }}
                        </button>
                        <div class="d-flex gap-2">
                            @can('view progress-projects')
                                <a href="{{ route('progress.projects.dashboard', $project->id) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    title="{{ __('general.view') }}"
                                    onclick="event.stopPropagation();"
                                    style="border-radius: 10px;
                                           padding: 0.4rem 0.8rem;
                                           transition: all 0.3s ease;
                                           border-width: 2px;"
                                    onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,123,255,0.2)'"
                                    onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endcan
                            @can('edit progress-projects')
                                <a href="{{ route('progress.projects.edit', $project) }}"
                                    class="btn btn-sm btn-outline-success"
                                    title="{{ __('general.edit') }}"
                                    onclick="event.stopPropagation();"
                                    style="border-radius: 10px;
                                           padding: 0.4rem 0.8rem;
                                           transition: all 0.3s ease;
                                           border-width: 2px;"
                                    onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(40,167,69,0.2)'"
                                    onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            <button type="button"
                                class="btn btn-sm btn-outline-info copy-project-btn"
                                title="{{ __('general.copy_project') }}"
                                data-project-id="{{ $project->id }}"
                                data-project-name="{{ $project->name }}"
                                onclick="event.stopPropagation();"
                                style="border-radius: 10px;
                                       padding: 0.4rem 0.8rem;
                                       transition: all 0.3s ease;
                                       border-width: 2px;"
                                onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(23,162,184,0.2)'"
                                onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    
    <div class="modal fade" id="saveAsTemplateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('general.save_as_template') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('general.template_name') }}</label>
                        <input type="text" class="form-control" id="modal_template_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('general.description') }}</label>
                        <textarea class="form-control" id="modal_template_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="saveTemplateBtn">{{ __('general.save_template') }}</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="copyProjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('general.copy_project') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('general.project_name') }}</label>
                        <input type="text" class="form-control" id="copy_project_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="copyProjectBtn">{{ __('general.copy_project') }}</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="projectDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="modal_project_name">
                        <i class="fas fa-project-diagram me-2"></i>
                        {{ __('general.project_details') }}
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="projectDetailsContent">
                    
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('general.loading') }}...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>{{ __('general.close') }}
                    </button>
                    <div id="modalActionButtons" class="d-flex gap-2">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="subprojectsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-gradient text-white" 
                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h4 class="modal-title" id="subprojectsModalTitle">
                        <i class="fas fa-sitemap me-2"></i>
                        {{ __('general.subprojects') }}
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="subprojectsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('general.loading') }}...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 12px;">
                        <i class="fas fa-times me-1"></i>{{ __('general.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentProjectId = null;
            
            // Store translations in variables
            const translations = {
                active: '{{ __('general.active') }}',
                completed: '{{ __('general.completed') }}',
                pending: '{{ __('general.pending') }}',
                progress: '{{ __('general.progress') }}',
                basicInfo: '{{ __('general.basic_information') }}',
                statistics: '{{ __('general.statistics') }}',
                typeOfProject: '{{ __('general.type_of_project') }}',
                startDate: '{{ __('projects.start_date') }}',
                endDate: '{{ __('projects.end_date') }}',
                workingZone: '{{ __('general.working_zone') }}',
                createdAt: '{{ __('general.created_at') }}',
                notSpecified: '{{ __('general.not_specified') }}',
                inProgress: '{{ __('general.in_progress') }}',
                items: '{{ __('general.items') }}',
                subprojects: '{{ __('general.subprojects') }}',
                employees: '{{ __('general.employees') }}',
                workingDays: '{{ __('general.working_days') }}',
                dailyWorkHours: '{{ __('general.daily_work_hours') }}',
                hours: '{{ __('general.hours') }}',
                description: '{{ __('general.description') }}',
                view: '{{ __('general.view') }}',
                edit: '{{ __('general.edit') }}',
                progressReport: '{{ __('general.progress_report') }}',
                ganttChart: '{{ __('general.gantt_chart') }}'
            };
            
            // Handle project card clicks and view details button
            document.querySelectorAll('.project-card, .view-details-btn').forEach(element => {
                element.addEventListener('click', function(e) {
                    // Don't trigger if clicking on action buttons
                    if (e.target.closest('a, button:not(.view-details-btn), form')) {
                        return;
                    }
                    
                    const card = this.closest('.project-card') || this.closest('.card');
                    if (!card) return;
                    
                    const projectId = card.getAttribute('data-project-id');
                    if (!projectId) return;
                    
                    // Get all project data from data attributes
                    const projectData = {
                        id: projectId,
                        name: card.getAttribute('data-project-name'),
                        description: card.getAttribute('data-project-description'),
                        client: card.getAttribute('data-project-client'),
                        status: card.getAttribute('data-project-status'),
                        type: card.getAttribute('data-project-type'),
                        startDate: card.getAttribute('data-project-start-date'),
                        endDate: card.getAttribute('data-project-end-date'),
                        workingZone: card.getAttribute('data-project-working-zone'),
                        workingDays: card.getAttribute('data-project-working-days'),
                        dailyHours: card.getAttribute('data-project-daily-hours'),
                        weeklyHolidays: card.getAttribute('data-project-weekly-holidays'),
                        itemsCount: card.getAttribute('data-project-items-count'),
                        subprojectsCount: card.getAttribute('data-project-subprojects-count'),
                        employeesCount: card.getAttribute('data-project-employees-count'),
                        progress: card.getAttribute('data-project-progress'),
                        created: card.getAttribute('data-project-created')
                    };
                    
                    // Fill modal with project data
                    fillProjectDetailsModal(projectData, translations);
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
                    modal.show();
                });
            });
            
            // Function to fill project details modal
            function fillProjectDetailsModal(data, t) {
                // Update modal title
                document.getElementById('modal_project_name').innerHTML = 
                    '<i class="fas fa-project-diagram me-2"></i>' + data.name;
                
                // Get status badge HTML
                let statusBadge = '';
                switch(data.status) {
                    case 'in_progress':
                        statusBadge = '<span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i>' + t.active + '</span>';
                        break;
                    case 'completed':
                        statusBadge = '<span class="badge bg-secondary px-3 py-2"><i class="fas fa-check-double me-1"></i>' + t.completed + '</span>';
                        break;
                    case 'pending':
                        statusBadge = '<span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-clock me-1"></i>' + t.pending + '</span>';
                        break;
                }
                
                // Calculate progress color
                const progress = parseFloat(data.progress) || 0;
                let progressColor = 'bg-danger';
                if (progress >= 75) progressColor = 'bg-success';
                else if (progress >= 50) progressColor = 'bg-info';
                else if (progress >= 25) progressColor = 'bg-warning';
                
                // Build modal content
                const content = `
                    <div class="row g-4">
                        
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-2">${data.name}</h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-building me-2"></i>${data.client}
                                    </p>
                                </div>
                                ${statusBadge}
                            </div>
                        </div>
                        
                        
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="fas fa-chart-line me-2 text-primary"></i>
                                            ${t.progress}
                                        </h6>
                                        <h4 class="mb-0 fw-bold text-primary">${progress}%</h4>
                                    </div>
                                    <div class="progress" style="height: 20px; border-radius: 10px;">
                                        <div class="progress-bar ${progressColor}" 
                                             role="progressbar" 
                                             style="width: ${progress}%" 
                                             aria-valuenow="${progress}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <div class="card border-0 h-100">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                        ${t.basicInfo}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-tag me-2"></i>${t.typeOfProject}
                                        </small>
                                        <div class="fw-semibold">${data.type || t.notSpecified}</div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-calendar-alt me-2"></i>${t.startDate}
                                        </small>
                                        <div class="fw-semibold">${data.startDate || t.notSpecified}</div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-calendar-check me-2"></i>${t.endDate}
                                        </small>
                                        <div class="fw-semibold">${data.endDate || t.inProgress}</div>
                                    </div>
                                    ${data.workingZone ? `
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-map-marker-alt me-2"></i>${t.workingZone}
                                        </small>
                                        <div class="fw-semibold">${data.workingZone}</div>
                                    </div>
                                    ` : ''}
                                    ${data.created ? `
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-calendar-plus me-2"></i>${t.createdAt}
                                        </small>
                                        <div class="fw-semibold">${data.created}</div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <div class="card border-0 h-100">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                                        ${t.statistics}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-list-ul fa-2x text-primary mb-2"></i>
                                                <h4 class="mb-0 fw-bold">${data.itemsCount || 0}</h4>
                                                <small class="text-muted">${t.items}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-sitemap fa-2x text-success mb-2"></i>
                                                <h4 class="mb-0 fw-bold">${data.subprojectsCount || 0}</h4>
                                                <small class="text-muted">${t.subprojects}</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                                <h4 class="mb-0 fw-bold">${data.employeesCount || 0}</h4>
                                                <small class="text-muted">${t.employees}</small>
                                            </div>
                                        </div>
                                        ${data.workingDays ? `
                                        <div class="col-6">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-calendar-week fa-2x text-warning mb-2"></i>
                                                <h4 class="mb-0 fw-bold">${data.workingDays}</h4>
                                                <small class="text-muted">${t.workingDays}</small>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                    ${data.dailyHours ? `
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">
                                                <i class="fas fa-clock me-2"></i>${t.dailyWorkHours}
                                            </span>
                                            <span class="fw-bold">${data.dailyHours} ${t.hours}</span>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                        
                        
                        ${data.description ? `
                        <div class="col-12">
                            <div class="card border-0">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0">
                                        <i class="fas fa-align-left me-2 text-primary"></i>
                                        ${t.description}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">${data.description}</p>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                // Update modal content
                document.getElementById('projectDetailsContent').innerHTML = content;
                
                // Update action buttons
                const actionButtons = document.getElementById('modalActionButtons');
                let buttonsHtml = '';
                
                // Check permissions and add buttons
                @php
                    $canView = auth()->user()->can('projects-view');
                    $canEdit = auth()->user()->can('edit progress-projects');
                    $canProgress = auth()->user()->can('projects-progress');
                    $canGantt = auth()->user()->can('projects-gantt');
                @endphp
                
                @if($canView)
                    buttonsHtml += `<a href="/projects/${data.id}/dashboard" class="btn btn-primary"><i class="fas fa-eye me-1"></i>${t.view}</a>`;
                @endif
                @if($canEdit)
                    buttonsHtml += `<a href="/projects/${data.id}/edit" class="btn btn-success"><i class="fas fa-edit me-1"></i>${t.edit}</a>`;
                @endif
                @if($canProgress)
                    buttonsHtml += `<a href="/projects/${data.id}/progress" class="btn btn-info"><i class="fas fa-chart-bar me-1"></i>${t.progressReport}</a>`;
                @endif
                @if($canGantt)
                    buttonsHtml += `<a href="/projects/${data.id}/gantt" class="btn btn-outline-primary"><i class="fas fa-chart-gantt me-1"></i>${t.ganttChart}</a>`;
                @endif
                
                actionButtons.innerHTML = buttonsHtml;
            }
            
            // Handle save as template button clicks
            document.querySelectorAll('.save-as-template-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentProjectId = this.getAttribute('data-project-id');
                    const projectName = this.getAttribute('data-project-name');
                    
                    // Pre-fill template name with project name
                    document.getElementById('modal_template_name').value = projectName + ' - Template';
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('saveAsTemplateModal'));
                    modal.show();
                });
            });
            
            // Handle copy project button clicks
            document.querySelectorAll('.copy-project-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentProjectId = this.getAttribute('data-project-id');
                    const projectName = this.getAttribute('data-project-name');
                    
                    // Pre-fill project name
                    document.getElementById('copy_project_name').value = projectName + ' - Copy';
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('copyProjectModal'));
                    modal.show();
                });
            });
            
            // Handle save template
            document.getElementById('saveTemplateBtn').addEventListener('click', function() {
                const templateName = document.getElementById('modal_template_name').value;
                const templateDescription = document.getElementById('modal_template_description').value;
                
                if (!templateName.trim()) {
                    alert('{{ __('general.template_name_required') }}');
                    return;
                }
                
                if (!currentProjectId) {
                    alert('{{ __('general.error_occurred') }}');
                    return;
                }
                
                // Create form data
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('template_name', templateName);
                formData.append('template_description', templateDescription);
                
                // Show loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.saving') }}...';
                this.disabled = true;
                
                // Send request
                fetch(`/projects/${currentProjectId}/save-as-template`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    alert('{{ __('general.template_created_successfully') }}');
                    
                    // Reset form
                    document.getElementById('modal_template_name').value = '';
                    document.getElementById('modal_template_description').value = '';
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('saveAsTemplateModal'));
                    modal.hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('general.error_occurred') }}');
                })
                .finally(() => {
                    // Reset button
                    this.innerHTML = '{{ __('general.save_template') }}';
                    this.disabled = false;
                });
            });
            
            // Handle copy project
            document.getElementById('copyProjectBtn').addEventListener('click', function() {
                const projectName = document.getElementById('copy_project_name').value;
                
                if (!projectName.trim()) {
                    alert('{{ __('general.project_name_required') }}');
                    return;
                }
                
                if (!currentProjectId) {
                    alert('{{ __('general.error_occurred') }}');
                    return;
                }
                
                // Show loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.copying') }}...';
                this.disabled = true;
                
                // Send request
                fetch(`/projects/${currentProjectId}/copy`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: projectName
                    })
                })
                .then(response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => ({ json: true, data }));
                    } else {
                        // If redirect, follow it
                        return { json: false, redirect: response.url || response.redirected };
                    }
                })
                .then(result => {
                    if (result.json) {
                        const data = result.data;
                        if (data.success) {
                            // Reset form
                            document.getElementById('copy_project_name').value = '';
                            
                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('copyProjectModal'));
                            modal.hide();
                            
                            // Redirect to edit page or reload
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || '{{ __('general.error_occurred') }}');
                            // Reset button
                            this.innerHTML = '{{ __('general.copy_project') }}';
                            this.disabled = false;
                        }
                    } else {
                        // Handle redirect response
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('general.error_occurred') }}');
                    // Reset button
                    this.innerHTML = '{{ __('general.copy_project') }}';
                    this.disabled = false;
                });
            });
        });
        
        // Handle subprojects button clicks
        document.querySelectorAll('.view-subprojects-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const projectId = this.getAttribute('data-project-id');
                if (!projectId) return;
                
                currentProjectId = projectId;
                loadSubprojects(projectId);
                
                const modal = new bootstrap.Modal(document.getElementById('subprojectsModal'));
                modal.show();
            });
        });
        
        // Load subprojects data
        function loadSubprojects(projectId) {
            const content = document.getElementById('subprojectsContent');
            content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('general.loading') }}...</span></div></div>';
            
            fetch(`/api/projects/${projectId}/subprojects`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Subprojects data received:', data);
                
                // Handle both formats: direct array or object with success property
                let subprojectsArray = null;
                
                if (Array.isArray(data)) {
                    // Data is directly an array
                    subprojectsArray = data;
                } else if (data.success && Array.isArray(data.subprojects)) {
                    // Data is an object with success and subprojects
                    subprojectsArray = data.subprojects;
                } else if (Array.isArray(data.subprojects)) {
                    // Data is an object with subprojects array
                    subprojectsArray = data.subprojects;
                }
                
                if (subprojectsArray && subprojectsArray.length > 0) {
                    console.log('Rendering', subprojectsArray.length, 'subprojects');
                    renderSubprojects(subprojectsArray);
                } else {
                    console.warn('No subprojects found or invalid data:', data);
                    content.innerHTML = '<div class="alert alert-warning text-center">{{ __('general.no_subprojects_found') }}</div>';
                }
            })
            .catch(error => {
                console.error('Error loading subprojects:', error);
                console.error('Error details:', error.message);
                content.innerHTML = '<div class="alert alert-danger text-center">{{ __('general.error_loading_subprojects') }}<br><small>' + error.message + '</small></div>';
            });
        }
        
        // Render subprojects - دالة منفصلة لعرض subprojects في صفحة /projects
        function renderSubprojects(subprojects) {
            console.log('renderSubprojects called with:', subprojects);
            
            const content = document.getElementById('subprojectsContent');
            
            // Validate input
            if (!content) {
                console.error('subprojectsContent element not found!');
                return;
            }
            
            // Check if subprojects is valid array
            if (!Array.isArray(subprojects)) {
                console.error('subprojects is not an array:', subprojects);
                content.innerHTML = '<div class="alert alert-danger text-center" style="border-radius: 15px;">{{ __('general.error_loading_subprojects') }}</div>';
                return;
            }
            
            if (subprojects.length === 0) {
                console.log('No subprojects to render');
                content.innerHTML = '<div class="alert alert-info text-center" style="border-radius: 15px;">{{ __('general.no_subprojects_found') }}</div>';
                return;
            }
            
            console.log('Rendering', subprojects.length, 'subprojects');
            
            // Calculate total weighted progress
            const totalWeightedProgress = subprojects.reduce((sum, sp) => sum + (sp.weighted_progress || 0), 0);
            const totalProgress = subprojects.reduce((sum, sp) => sum + (sp.progress || 0), 0);
            
            // Calculate total weighted progress excluding items > 100%
            const totalWeightedProgressUnder100 = subprojects.reduce((sum, sp) => sum + (sp.weighted_progress_under_100 || 0), 0);
            const totalProgressUnder100 = subprojects.reduce((sum, sp) => sum + (sp.progress_under_100 || 0), 0);
            
            let html = `
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-white p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><i class="fas fa-chart-line me-2"></i>{{ __('general.total_weighted_progress') }}</h5>
                                        <p class="mb-0 text-white-50">{{ __('general.total_weighted_progress_description') }}</p>
                                    </div>
                                    <div class="text-end d-flex align-items-center gap-3">
                                        <div>
                                            <div class="mb-2">
                                                <b class="mb-0 fw-bold" >${totalProgress.toFixed(2)}%</b>/
                                                <b class="mb-0 fw-bold" style="">${totalWeightedProgress.toFixed(2)}%</b>
                                            </div>
                                            <div class="border-top pt-2" style="border-color: rgba(255,255,255,0.3) !important;">
                                                <small class="text-white-50 d-block mb-1">( :: {{ __('general.scalable_ratio_not_included_above_100') }} ::)</small>
                                                <b class="mb-0 fw-bold">${totalProgressUnder100.toFixed(2)}%</b>/
                                                <b class="mb-0 fw-bold" style="font-size: 2.5rem;">${totalWeightedProgressUnder100.toFixed(2)}%</b>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-light btn-sm update-all-weights-btn"
                                                style="border-radius: 10px; font-weight: 600; white-space: nowrap;"
                                                onclick="updateAllSubprojectsWeights()">
                                            <i class="fas fa-save me-1"></i>{{ __('general.update_all_weights') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
            `;
            
            subprojects.forEach(subproject => {
                const progress = subproject.progress || 0;
                const progressUnder100 = subproject.progress_under_100 || 0;
                const weight = subproject.weight || 0;
                const weightedProgress = subproject.weighted_progress || 0;
                const weightedProgressUnder100 = subproject.weighted_progress_under_100 || 0;
                
                let progressColor = 'bg-danger';
                if (progress >= 75) progressColor = 'bg-success';
                else if (progress >= 50) progressColor = 'bg-info';
                else if (progress >= 25) progressColor = 'bg-warning';
                
                let progressUnder100Color = 'bg-danger';
                if (progressUnder100 >= 75) progressUnder100Color = 'bg-success';
                else if (progressUnder100 >= 50) progressUnder100Color = 'bg-info';
                else if (progressUnder100 >= 25) progressUnder100Color = 'bg-warning';
                
                html += `
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-header bg-white border-0 pb-2" style="border-radius: 15px 15px 0 0;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-sitemap me-2 text-primary"></i>
                                        ${subproject.name}
                                    </h6>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">
                                            <i class="fas fa-tasks me-1"></i>{{ __(' progress') }}
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small">${progress.toFixed(2)}%</span>
                                            ${progressUnder100 !== progress ? `
                                                <span class="fw-bold ">(${progressUnder100.toFixed(2)}%)</span>
                                            ` : ''}
                                        </div>
                                    </div>
                                
                                  
                                </div>
                                
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <div class="d-flex align-items-center text-muted mb-1">
                                                <i class="fas fa-weight me-2"></i>
                                                <small class="fw-semibold">{{ __('general.weight') }}</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" 
                                                       class="form-control form-control-sm weight-input" 
                                                       data-subproject-id="${subproject.id}"
                                                       value="${weight}"
                                                       min="0"
                                                       max="100"
                                                       step="0.1"
                                                       style="border-radius: 8px; border: 2px solidrgb(64, 160, 255);"
                                                       onchange="this.style.borderColor='#28a745'">
                                                <span class="text-muted small">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <div class="d-flex align-items-center text-muted mb-1">
                                                <i class="fas fa-chart-bar me-2"></i>
                                                <small class="fw-semibold">{{ __('general.weighted_progress') }}</small>
                                            </div>
                                        
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-list-ul me-1"></i>{{ __('general.total') }}
                                        </small>
                                        <div class="fw-semibold">${subproject.total_quantity || 0}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-check-circle me-1"></i>{{ __('general.completed') }}
                                        </small>
                                        <div class="fw-semibold">${subproject.completed_quantity || 0}</div>
                                    </div>
                                </div>
                                
                                ${subproject.items && subproject.items.length > 0 ? `
                                <div class="mt-4 border-top pt-3">
                                    ${(() => {
                                        // Separate measurable and non-measurable items
                                        const measurableItems = subproject.items.filter(item => item.is_measurable ?? false);
                                        const nonMeasurableItems = subproject.items.filter(item => !(item.is_measurable ?? false));
                                        const totalItems = subproject.items.length;
                                        
                                        const accordionId = `itemsAccordion_${subproject.id}`;
                                        
                                        return `
                                            <div class="accordion" id="${accordionId}">
                                                <div class="accordion-item" style="border-radius: 10px; border: 1px solid #e9ecef;">
                                                    <h2 class="accordion-header" id="heading_${accordionId}">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_${accordionId}" aria-expanded="false" aria-controls="collapse_${accordionId}" style="border-radius: 10px;">
                                                            <i class="fas fa-list-ul me-2 text-primary"></i>
                                                            <span class="fw-semibold">{{ __('general.items') }}</span>
                                                            <span class="badge bg-primary ms-2">${totalItems}</span>
                                                            ${measurableItems.length > 0 && nonMeasurableItems.length > 0 ? `
                                                                <span class="badge bg-success ms-2">${measurableItems.length} {{ __('general.measurable') }}</span>
                                                                <span class="badge bg-secondary ms-2">${nonMeasurableItems.length} {{ __('general.not_measurable') }}</span>
                                                            ` : ''}
                                                        </button>
                                                    </h2>
                                                    <div id="collapse_${accordionId}" class="accordion-collapse collapse" aria-labelledby="heading_${accordionId}" data-bs-parent="#${accordionId}">
                                                        <div class="accordion-body p-3">
                                                            ${measurableItems.length > 0 ? `
                                                                <div class="mb-3">
                                                                    ${nonMeasurableItems.length > 0 ? `<h6 class="mb-2 text-success"><i class="fas fa-check-circle me-1"></i>{{ __('general.measurable_items') }}</h6>` : ''}
                                                                    <div class="list-group" style="border-radius: 10px;">
                                                                        ${measurableItems.map(item => {
                                                                            const itemProgress = parseFloat(item.progress || 0);
                                                                            const progressColor = itemProgress >= 75 ? 'success' : 
                                                                                                  itemProgress >= 50 ? 'info' : 
                                                                                                  itemProgress >= 25 ? 'warning' : 'danger';
                                                                            
                                                                            return `
                                                                                <div class="list-group-item" style="border-radius: 8px; margin-bottom: 8px; border: 1px solid #e9ecef;">
                                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                                        <div class="flex-grow-1">
                                                                                            <div class="d-flex align-items-center mb-1">
                                                                                                <span class="fw-semibold">${item.name}</span>
                                                                                            </div>
                                                                                            <div class="small text-muted mb-2">
                                                                                                <span>{{ __('general.completed') }}: ${parseFloat(item.completed_quantity || 0).toFixed(2)} / ${parseFloat(item.total_quantity || 0).toFixed(2)} ${item.unit}</span>
                                                                                            </div>
                                                                                            <div class="progress" style="height: 6px; border-radius: 5px;">
                                                                                                <div class="progress-bar bg-${progressColor}" 
                                                                                                     role="progressbar" 
                                                                                                     style="width: ${itemProgress}%" 
                                                                                                     aria-valuenow="${itemProgress}" 
                                                                                                     aria-valuemin="0" 
                                                                                                     aria-valuemax="100">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="text-end ms-2">
                                                                                            <span class="badge bg-${progressColor}">${itemProgress.toFixed(1)}%</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            `;
                                                                        }).join('')}
                                                                    </div>
                                                                </div>
                                                            ` : ''}
                                                            
                                                            ${nonMeasurableItems.length > 0 ? `
                                                                <div>
                                                                    ${measurableItems.length > 0 ? `<h6 class="mb-2 text-secondary"><i class="fas fa-info-circle me-1"></i>{{ __('general.non_measurable_items') }}</h6>` : ''}
                                                                    <div class="list-group" style="border-radius: 10px;">
                                                                        ${nonMeasurableItems.map(item => {
                                                                            return `
                                                                                <div class="list-group-item bg-light" style="border-radius: 8px; margin-bottom: 8px; border: 1px solid #e9ecef;">
                                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                                        <div class="flex-grow-1">
                                                                                            <div class="d-flex align-items-center mb-1">
                                                                                                <span class="fw-semibold">${item.name}</span>
                                                                                                <span class="badge bg-secondary ms-2" style="">
                                                                                                    <i class="fas fa-info-circle me-1"></i>{{ __('general.not_measurable') }}
                                                                                                </span>
                                                                                            </div>
                                                                                            <div class="small text-muted">
                                                                                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                                                                                {{ __('general.not_included_in_progress_calculation') }}
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            `;
                                                                        }).join('')}
                                                                    </div>
                                                                </div>
                                                            ` : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    })()}
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            content.innerHTML = html;
        }
        
        // Update subproject weight
        function updateSubprojectWeight(subprojectId) {
            const input = document.querySelector(`.weight-input[data-subproject-id="${subprojectId}"]`);
            if (!input || !currentProjectId) return;
            
            const weight = parseFloat(input.value);
            if (isNaN(weight) || weight < 0) {
                alert('{{ __('general.weight_must_be_positive') }}');
                return;
            }
            
            // Calculate total of all weights
            const allWeightInputs = document.querySelectorAll('.weight-input');
            let totalWeight = 0;
            allWeightInputs.forEach(wInput => {
                const wValue = parseFloat(wInput.value) || 0;
                totalWeight += wValue;
            });
            
            // Check if total equals 100
            if (Math.abs(totalWeight - 100) > 0.01) { // Allow small floating point differences
                alert('{{ __('general.total_weights_must_equal_100') }}: ' + totalWeight.toFixed(2) + '%');
                return;
            }
            
            const btn = document.querySelector(`.update-weight-btn[data-subproject-id="${subprojectId}"]`);
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.updating') }} <b>...</b>';
            btn.disabled = true;
            
            fetch(`/api/projects/${currentProjectId}/subprojects/${subprojectId}/update-weight`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ weight: weight })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the weighted progress in the UI
                    const card = input.closest('.card');
                    const weightedProgressElement = card.querySelector('.fw-bold.text-primary');
                    if (weightedProgressElement && data.subproject) {
                        weightedProgressElement.textContent = data.subproject.weighted_progress.toFixed(2) + '%';
                    }
                    
                    // Reset input border
                    input.style.borderColor = '#e9ecef';
                    
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.style.borderRadius = '10px';
                    const successMsg = data.message || '{{ __('general.weight_updated_successfully') }}';
                    alert.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + successMsg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    card.querySelector('.card-body').insertBefore(alert, card.querySelector('.card-body').firstChild);
                    
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);
                    
                    // Recalculate total weighted progress
                    loadSubprojects(currentProjectId);
                } else {
                    alert(data.message || '{{ __('general.error_occurred') }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __('general.error_occurred') }}');
            })
            .finally(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }
        
        // Update all subprojects weights
        function updateAllSubprojectsWeights() {
            if (!currentProjectId) {
                alert('{{ __('general.error_occurred') }}');
                return;
            }
            
            // Get all weight inputs
            const allWeightInputs = document.querySelectorAll('.weight-input');
            const weights = {};
            let totalWeight = 0;
            
            // Collect all weights
            allWeightInputs.forEach(input => {
                const subprojectId = input.getAttribute('data-subproject-id');
                const weight = parseFloat(input.value) || 0;
                if (subprojectId) {
                    weights[subprojectId] = weight;
                    totalWeight += weight;
                }
            });
            
            // Validate total equals 100
            if (Math.abs(totalWeight - 100) > 0.01) {
                alert('{{ __('general.total_weights_must_equal_100') }}: ' + totalWeight.toFixed(2) + '%');
                return;
            }
            
            // Get button and show loading
            const btn = document.querySelector('.update-all-weights-btn');
            if (!btn) return;
            
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('general.updating') }}...';
            btn.disabled = true;
            
            // Send request
            fetch(`/api/projects/${currentProjectId}/subprojects/update-all-weights`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ weights: weights })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset all input borders
                    allWeightInputs.forEach(input => {
                        input.style.borderColor = '#e9ecef';
                    });
                    
                    // Show success message
                    const content = document.getElementById('subprojectsContent');
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.style.borderRadius = '10px';
                    alert.style.marginBottom = '1rem';
                    const successMsg = data.message || '{{ __('general.all_weights_updated_successfully') }}';
                    alert.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + successMsg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    content.insertBefore(alert, content.firstChild);
                    
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);
                    
                    // Reload subprojects to update weighted progress
                    loadSubprojects(currentProjectId);
                } else {
                    alert(data.message || '{{ __('general.error_occurred') }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __('general.error_occurred') }}');
            })
            .finally(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }
        
        // Make functions globally available
        window.updateSubprojectWeight = updateSubprojectWeight;
        window.updateAllSubprojectsWeights = updateAllSubprojectsWeights;
        
        // Update project progress bars with totalWeightedProgressUnder100
        function updateProjectProgressBars() {
            document.querySelectorAll('.project-card').forEach(card => {
                const projectId = card.getAttribute('data-project-id');
                if (!projectId) return;
                
                // Check if project has subprojects
                const subprojectsCount = parseInt(card.getAttribute('data-project-subprojects-count') || '0');
                if (subprojectsCount === 0) return;
                
                fetch(`/api/projects/${projectId}/subprojects`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.subprojects) {
                        const subprojects = data.subprojects;
                        
                        // Calculate total weighted progress excluding items > 100%
                        const totalWeightedProgressUnder100 = subprojects.reduce((sum, sp) => sum + (sp.weighted_progress_under_100 || 0), 0);
                        
                        // Update progress percentage text
                        const progressText = card.querySelector('.fw-bold[style*="background: linear-gradient"]');
                        if (progressText) {
                            progressText.textContent = totalWeightedProgressUnder100.toFixed(1) + '%';
                        }
                        
                        // Update progress bar
                        const progressBar = card.querySelector('.progress-bar[role="progressbar"]');
                        if (progressBar) {
                            const progressValue = Math.min(totalWeightedProgressUnder100, 100);
                            progressBar.style.width = progressValue + '%';
                            progressBar.setAttribute('aria-valuenow', progressValue);
                            
                            // Update progress bar color based on new value
                            progressBar.className = 'progress-bar ' + 
                                (progressValue >= 75 ? 'bg-success' :
                                 progressValue >= 50 ? 'bg-info' :
                                 progressValue >= 25 ? 'bg-warning' : 'bg-danger');
                            
                            // Update gradient background
                            const gradientColors = 
                                progressValue >= 75 ? '#28a745, #20c997' :
                                progressValue >= 50 ? '#17a2b8, #138496' :
                                progressValue >= 25 ? '#ffc107, #ffb300' : '#dc3545, #c82333';
                            
                            progressBar.style.background = `linear-gradient(90deg, ${gradientColors})`;
                        }
                    }
                })
                .catch(error => {
                    console.error(`Error loading progress for project ${projectId}:`, error);
                });
            });
        }
        
        // Update progress bars on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateProjectProgressBars();
        });
    </script>

    
    <script src="{{ asset('js/projects-filter.js') }}"></script>
@endsection

