@extends('progress::layouts.daily-progress')

@section('title', $projectTemplate->name)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                    <i class="las la-layer-group text-white fs-1"></i>
                </div>
                <div>
                    <h2 class="mb-1 fw-bold text-dark">{{ $projectTemplate->name }}</h2>
                    <p class="text-muted mb-0">
                        <i class="las la-calendar-alt me-1"></i> {{ __('general.created_at') }}: {{ $projectTemplate->created_at->format('Y-m-d') }}
                    </p>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-3 mt-sm-0">
                @can('edit progress-project-templates')
                <a href="{{ route('project.template.edit', $projectTemplate->id) }}" class="btn btn-warning fw-bold px-4 rounded-pill">
                    <i class="las la-edit me-1"></i> {{ __('general.edit') }}
                </a>
                @endcan
                <a href="{{ route('project.template.index') }}" class="btn btn-outline-secondary fw-bold px-4 rounded-pill">
                    <i class="las la-arrow-left me-1"></i> {{ __('general.back') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold text-primary mb-4">
                    <i class="las la-info-circle me-1"></i> {{ __('general.basic_information') }}
                </h5>
                
                <div class="mb-3">
                    <label class="d-block text-muted small fw-bold text-uppercase mb-1">{{ __('general.status') }}</label>
                    <span class="badge bg-success px-3 py-2 rounded-pill">{{ __('general.status_active') }}</span>
                </div>

                <div class="mb-3">
                    <label class="d-block text-muted small fw-bold text-uppercase mb-1">{{ __('general.project_type') }}</label>
                    <h6 class="fw-bold text-dark">{{ $projectTemplate->projectType->name ?? __('general.not_specified') }}</h6>
                </div>

                <div class="mb-0">
                    <label class="d-block text-muted small fw-bold text-uppercase mb-1">{{ __('general.total_items') }}</label>
                    <h6 class="fw-bold text-primary">
                        <span class="fs-4">{{ $projectTemplate->items->count() }}</span> {{ __('general.items') }}
                    </h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-primary mb-0">
                    <i class="las la-list-ol me-2"></i> {{ __('general.template_items') }} 
                    <span class="badge bg-primary ms-1">{{ $projectTemplate->items->count() }}</span>
                </h5>
                <small class="text-muted"><i class="las la-sort me-1"></i> {{ __('general.sorted_by_order') }}</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-dark text-white text-uppercase small">
                            <tr>
                                <th class="py-3 px-3" style="width: 50px;">#</th>
                                <th class="py-3">{{ __('general.item_name') }}</th>
                                <th class="py-3 text-center">{{ __('general.unit') }}</th>
                                <th class="py-3 text-center">{{ __('general.default_quantity') }}</th>
                                <th class="py-3 text-center">{{ __('general.estimated_daily_qty') }}</th>
                                <th class="py-3 text-center">{{ __('general.duration') }}</th>
                                <th class="py-3 text-center">{{ __('general.predecessor') }}</th>
                                <th class="py-3 text-center">{{ __('general.dependency_type') }}</th>
                                <th class="py-3 text-center">{{ __('general.lag') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectTemplate->items as $index => $item)
                                <tr>
                                    <td class="px-3 fw-bold text-primary">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->workItem->name ?? '—' }}</div>
                                        @if($item->subproject_name)
                                            <small class="text-muted"><i class="las la-tag me-1"></i> {{ $item->subproject_name }}</small>
                                        @elseif($item->workItem && $item->workItem->category)
                                            <small class="text-muted"><i class="las la-tag me-1"></i> {{ $item->workItem->category->name }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $item->workItem->unit ?? '—' }}</span></td>
                                    <td class="text-center fw-bold text-primary">{{ number_format($item->total_quantity, 2) }}</td>
                                    <td class="text-center text-info">{{ number_format($item->estimated_daily_qty, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark px-2 rounded-pill">{{ $item->duration }} {{ __('general.days') }}</span>
                                    </td>
                                    <td class="text-center text-muted small">
                                        @if($item->predecessorItem)
                                            <span class="badge bg-light text-secondary border">
                                                 <i class="las la-link me-1"></i> #{{ $item->predecessorItem->item_order + 1 }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->dependency_type)
                                            <span class="badge bg-primary bg-opacity-75">{{ __('general.' . $item->dependency_type) }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->lag != 0)
                                            <span class="badge bg-secondary">{{ $item->lag > 0 ? '+' : '' }}{{ $item->lag }}</span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="las la-box-open fs-1 d-block mb-3 opacity-50"></i>
                                        {{ __('general.no_items_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Statistics -->
<div class="row mt-4">
    <div class="col-12 mb-3">
        <h5 class="fw-bold text-primary"><i class="las la-chart-bar me-2"></i> {{ __('general.template_statistics') }}</h5>
    </div>
    
    <!-- Total Quantity -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white border-0 shadow-sm text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                 <i class="las la-calculator fs-1 mb-2 opacity-75"></i>
                <h2 class="fw-bold mb-0 text-white">{{ number_format($projectTemplate->items->sum('total_quantity'), 2) }}</h2>
                <small class="text-white-50 text-uppercase fw-bold mt-2">{{ __('general.total_quantity') }}</small>
            </div>
        </div>
    </div>

    <!-- Total Duration -->
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white border-0 shadow-sm text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                <i class="las la-clock fs-1 mb-2 opacity-75"></i>
                <h2 class="fw-bold mb-0 text-white">{{ $projectTemplate->items->sum('duration') }}</h2>
                <small class="text-white-50 text-uppercase fw-bold mt-2">{{ __('general.total_duration_days') }}</small>
            </div>
        </div>
    </div>

    <!-- Items with Dependencies -->
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white border-0 shadow-sm text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                 <i class="las la-project-diagram fs-1 mb-2 opacity-75"></i>
                <h2 class="fw-bold mb-0 text-white">{{ $projectTemplate->items->whereNotNull('predecessor')->count() }}</h2>
                <small class="text-white-50 text-uppercase fw-bold mt-2">{{ __('general.items_with_dependencies') }}</small>
            </div>
        </div>
    </div>

    <!-- Items with Notes -->
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark border-0 shadow-sm text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                 <i class="las la-sticky-note fs-1 mb-2 opacity-75"></i>
                <h2 class="fw-bold mb-0 text-dark">{{ $projectTemplate->items->whereNotNull('notes')->count() }}</h2>
                <small class="text-dark-50 text-uppercase fw-bold mt-2">{{ __('general.items_with_notes') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="text-center text-muted small mt-5 mb-3">
    Crafted with <i class="las la-heart text-danger"></i> by CORE HOUSE TEAM
</div>

@endsection
