@extends('progress::layouts.daily-progress')

{{-- Sidebar is now handled by the layout itself --}}

@section('title', __('general.daily_progress_list'))

@section('content')
    <div class="container" x-data="{ search: '' }">
        <div class="main-card card shadow-lg border-0">
            <div class="card-header text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%); border-radius: 0.75rem 0.75rem 0 0;">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> {{ __('general.daily_progress_list') }}</h5>
                @can('create daily-progress')
                <a href="{{ route('daily_progress.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> {{ __('general.add_progress') }}
                </a>
                @endcan
            </div>

            <div class="card-body bg-light">
                <!-- Filters Section -->
                <form method="GET" action="{{ route('daily_progress.index') }}" class="row g-3 mb-4">
                    <input type="hidden" name="view_all" value="{{ request('view_all') }}">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.project') }}</label>
                        <select name="project_id" class="form-select" onchange="this.form.submit()">
                            <option value="">{{ __('general.all_projects') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('general.date') }}</label>
                        <input type="date" name="progress_date" value="{{ request('progress_date') }}"
                            class="form-control" onchange="this.form.submit()">
                    </div>
                    
                    <!-- Client Side Search -->
                    <div class="col-md-4">
                        <label class="form-label">{{ __('general.search') }} ({{ __('general.client_side') }})</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" x-model="search" 
                                placeholder="{{ __('general.search_placeholder') }}...">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                         <a href="{{ route('daily_progress.index', ['view_all' => 1]) }}" class="btn btn-secondary w-100">
                            <i class="fas fa-list me-1"></i> {{ __('general.view_all') }}
                        </a>
                    </div>
                </form>

                <!-- Grouped Data Display -->
                @forelse($groupedProgress as $projectId => $subprojects)
                    @php
                        // Get Project Name from the first item of the first subproject
                        $firstRecord = $subprojects->first()->first();
                        $projectName = $firstRecord->project->name ?? 'Unknown Project';
                        
                        // Construct a search string containing all searchable text within this project block
                        $searchableText = $projectName;
                        foreach($subprojects as $subname => $items) {
                            $searchableText .= ' ' . $subname;
                            foreach($items as $item) {
                                $searchableText .= ' ' . ($item->projectItem->workItem->name ?? '') . ' ' . ($item->employee->name ?? '');
                            }
                        }
                    @endphp

                    <div class="card mb-4 border shadow-sm"
                         x-data="{ expanded: true }"
                         x-show="search === '' || '{{ addslashes($searchableText) }}'.toLowerCase().includes(search.toLowerCase())"
                         data-search="{{ $searchableText }}">
                        
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" 
                             @click="expanded = !expanded" style="cursor: pointer;">
                            <h5 class="mb-0 text-primary fw-bold">
                                <i class="fas fa-chevron-down me-2 transition-transform" :class="{ 'rotate-180': !expanded }"></i>
                                <i class="fas fa-project-diagram me-2"></i> {{ $projectName }}
                            </h5>
                            <span class="badge bg-light text-dark border">
                                {{ __('general.records') }}: {{ $subprojects->flatten()->count() }}
                            </span>
                        </div>

                        <div class="card-body p-0" x-show="expanded" x-collapse>
                            @foreach($subprojects as $subprojectName => $items)
                                <div class="subproject-section border-bottom">
                                    @if($subprojectName !== 'عام' && $subprojectName !== '' && $subprojectName !== null)
                                        <div class="px-4 py-2 bg-soft-primary border-bottom">
                                            <small class="text-uppercase text-primary fw-bold">
                                                <i class="fas fa-folder-open me-1"></i> {{ $subprojectName }}
                                            </small>
                                        </div>
                                    @endif

                                    <div class="list-group list-group-flush">
                                        @foreach($items as $progress)
                                            <div class="list-group-item p-3 border-bottom hover-bg-light transition-all" 
                                                 x-show="search === '' || '{{ addslashes($projectName) }} {{ addslashes($subprojectName) }} {{ addslashes($progress->projectItem->workItem->name ?? '') }} {{ addslashes($progress->employee->name ?? '') }}'.toLowerCase().includes(search.toLowerCase())">
                                                
                                                <div class="row align-items-center">
                                                    <!-- Icon & Work Item Info -->
                                                    <div class="col-md-4 mb-2 mb-md-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm rounded bg-soft-primary text-primary d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-tasks fs-4"></i>
                                                            </div>
                                                            <div>
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <h6 class="mb-0 fw-bold text-dark me-2">{{ $progress->projectItem->workItem->name ?? '-' }}</h6>
                                                                    @if($progress->projectItem->workItem?->category)
                                                                        <span class="badge bg-light text-secondary border small" style="font-size: 0.65rem;">
                                                                            {{ $progress->projectItem->workItem->category->name }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                
                                                                <div class="d-flex flex-wrap gap-2 small text-muted">
                                                                    @if($progress->projectItem->is_measurable)
                                                                        <span class="text-success" title="{{ __('general.measurable') }}">
                                                                            <i class="fas fa-ruler-combined me-1"></i> {{ __('general.measurable') }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-secondary" title="{{ __('general.not_measurable') }}">
                                                                            <i class="fas fa-ruler-horizontal me-1"></i> {{ __('general.not_measurable') }}
                                                                        </span>
                                                                    @endif

                                                                    @if($progress->notes)
                                                                        <span class="border-start ps-2 d-inline-block text-truncate" style="max-width: 200px;" title="{{ $progress->notes }}">
                                                                            <i class="fas fa-sticky-note me-1 text-warning"></i> {{ $progress->notes }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Quantity & Completion -->
                                                    <div class="col-md-3 mb-2 mb-md-0">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-soft-primary text-primary rounded-pill me-2 border border-primary-subtle" title="{{ __('general.quantity') }}">
                                                                {{ $progress->quantity }}
                                                                <span class="opacity-75 ms-1">{{ $progress->projectItem->workItem->unit ?? '' }}</span>
                                                            </span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 6px; background-color: #e9ecef; border-radius: 10px;">
                                                                @php
                                                                    $barColor = $progress->completion_percentage >= 100 ? 'bg-success' : ($progress->completion_percentage > 50 ? 'bg-info' : 'bg-warning');
                                                                @endphp
                                                                <div class="progress-bar {{ $barColor }}" role="progressbar" 
                                                                     style="width: {{ $progress->completion_percentage }}%; border-radius: 10px;" 
                                                                     aria-valuenow="{{ $progress->completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <span class="small fw-bold text-muted">{{ $progress->completion_percentage }}%</span>
                                                        </div>
                                                    </div>

                                                    <!-- Date & People -->
                                                    <div class="col-md-3 mb-2 mb-md-0">
                                                        <div class="d-flex flex-column small">
                                                            <div class="text-muted mb-2">
                                                                <i class="far fa-calendar-alt me-1 opacity-50"></i> 
                                                                {{ \Carbon\Carbon::parse($progress->progress_date)->format('Y-m-d') }}
                                                            </div>
                                                            
                                                            <!-- Employee & Added By -->
                                                            <div class="d-flex flex-column gap-1">
                                                                @if($progress->employee)
                                                                    <div class="d-flex align-items-center text-muted" title="{{ __('general.employee') }}">
                                                                        <div class="avatar-sm rounded-circle bg-soft-info text-info d-flex align-items-center justify-content-center me-2 fw-bold" 
                                                                             style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                                            {{ substr($progress->employee->name, 0, 1) }}
                                                                        </div>
                                                                        <span class="text-truncate" style="max-width: 140px;">
                                                                            {{ $progress->employee->name }}
                                                                        </span>
                                                                    </div>
                                                                @endif

                                                                @if($progress->user)
                                                                    <div class="d-flex align-items-center text-secondary small" title="{{ __('general.added_by') }}">
                                                                        <i class="fas fa-user-edit me-2 opacity-50 ms-1" style="width: 24px; text-align: center;"></i>
                                                                        <span class="text-truncate fst-italic" style="max-width: 140px; font-size: 0.8rem;">
                                                                            {{ __('general.added_by') }}: {{ $progress->user->name }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="col-md-2 text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            @can('edit daily-progress')
                                                            <a href="{{ route('daily_progress.edit', $progress) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="{{ __('general.edit') }}">
                                                                <i class="fas fa-edit me-1"></i> {{ __('general.edit') }}
                                                            </a>
                                                            @endcan
                                                            @can('delete daily-progress')
                                                            <form action="{{ route('daily_progress.destroy', $progress) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button onclick="return confirm('{{ __('general.confirm_delete') }}')"
                                                                    type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="{{ __('general.delete') }}">
                                                                    <i class="fas fa-trash me-1"></i> {{ __('general.delete') }}
                                                                </button>
                                                            </form>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <img src="{{ asset('images/no-data.svg') }}" alt="No Data" style="height: 150px; opacity: 0.5;" onerror="this.style.display='none'">
                        <h4 class="text-muted mt-3">{{ __('general.no_data') }}</h4>
                        <p class="text-muted">{{ __('general.no_records_found') }}</p>
                    </div>
                @endforelse
                
                <div class="text-center mt-4 text-muted small">
                    <i class="fas fa-info-circle me-1"></i> {{ __('general.showing_all_records') }}
                </div>
            </div>
        </div>
    </div>
@endsection
