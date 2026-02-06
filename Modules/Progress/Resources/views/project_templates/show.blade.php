@extends('progress::layouts.app')

@section('title', __('general.view_template'))

@section('content')
<div class="container-fluid py-4">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
                    <i class="fas fa-home me-1"></i>{{ __('general.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('progress.project-templates.index') }}" class="text-muted text-decoration-none">
                    <i class="fas fa-layer-group me-1"></i>{{ __('general.project_templates') }}
                </a>
            </li>
            <li class="breadcrumb-item active text-primary" aria-current="page">
                {{ $project_template->name }}
            </li>
        </ol>
    </nav>

    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-layer-group text-white fa-2x"></i>
                                </div>
                                <div>
                                    <h2 class="fw-bold text-primary mb-1">{{ $project_template->name }}</h2>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ __('general.created_at') }}: {{ $project_template->created_at->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>

                            @if($project_template->description)
                            <div class="mb-3">
                                <h6 class="fw-semibold text-dark mb-2">
                                    <i class="fas fa-align-left me-2"></i>{{ __('general.description') }}
                                </h6>
                                <p class="text-muted mb-0 ps-4">{{ $project_template->description }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                <a href="{{ route('progress.project-templates.edit', $project_template) }}"
                                   class="btn btn-warning btn-lg px-4 rounded-pill">
                                    <i class="fas fa-edit me-2"></i>{{ __('general.edit') }}
                                </a>
                                <a href="{{ route('progress.project-templates.index') }}"
                                   class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                                    <i class="fas fa-arrow-left me-2"></i>{{ __('general.back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4">
        
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-light rounded-top-3 py-3">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-info-circle me-2"></i>{{ __('general.basic_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="fw-semibold text-dark">{{ __('general.status') }}</label>
                            <div class="d-flex align-items-center">
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'pending' => 'warning',
                                        'draft' => 'info'
                                    ];
                                    $statusColor = $statusColors[$project_template->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }} rounded-pill px-3 py-2">
                                    {{ __('general.status_' . $project_template->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="fw-semibold text-dark">{{ __('general.project__type') }}</label>
                            <p class="text-muted mb-0">
                                {{ $project_template->projectType->name ?? __('general.not_specified') }}
                            </p>
                        </div>

                        <div class="col-12">
                            <label class="fw-semibold text-dark">{{ __('general.total_items') }}</label>
                            <p class="text-muted mb-0">
                                <span class="fw-bold text-primary">{{ $project_template->items->count() }}</span>
                                {{ __('general.items') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-light rounded-top-3 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-primary">
                            <i class="fas fa-list-check me-2"></i>{{ __('general.template_items') }}
                            <span class="badge bg-primary ms-2">{{ $project_template->items->count() }}</span>
                        </h5>
                        <div class="text-muted small">
                            <i class="fas fa-sort me-1"></i>{{ __('general.sorted_by_order') }}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($project_template->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="ps-4" style="width: 60px;">#</th>
                                        <th>{{ __('general.item_name') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('general.unit') }}</th>
                                        <th class="text-center" style="width: 120px;">{{ __('general.default_quantity') }}</th>
                                        <th class="text-center" style="width: 120px;">{{ __('general.estimated_daily_qty') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('general.duration') }}</th>
                                        <th class="text-center" style="width: 150px;">{{ __('general.predecessor') }}</th>
                                        <th class="text-center" style="width: 120px;">{{ __('general.dependency_type') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('general.lag') }}</th>
                                        <th class="text-center" style="width: 200px;">{{ __('general.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project_template->items->sortBy('item_order') as $index => $item)
                                        <tr class="border-bottom">
                                            <td class="ps-4 fw-bold text-primary">
                                                {{ ($item->item_order ?? $index) + 1 }}
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-tasks text-primary fa-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-semibold mb-1 text-dark">{{ $item->workItem->name ?? 'N/A' }}</h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-tag me-1"></i>
                                                            {{ $item->workItem->category->name ?? __('general.uncategorized') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-3 py-2">
                                                    {{ $item->workItem->unit }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold text-primary fs-6">
                                                    {{ number_format($item->default_quantity, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-semibold text-info">
                                                    {{ $item->estimated_daily_qty ? number_format($item->estimated_daily_qty, 2) : '—' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning text-dark px-3 py-2">
                                                    {{ $item->duration }} {{ __('general.days') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($item->predecessor)
                                                    @php
                                                        // البحث عن الـ predecessor في نفس الـ template
                                                        $predecessorItem = $project_template->items->where('work_item_id', $item->predecessor)->first();
                                                        $predecessorWorkItem = $predecessorItem ? $predecessorItem->workItem : null;
                                                        
                                                        // إذا لم نجد في الـ template، ابحث مباشرة في WorkItem
                                                        if (!$predecessorWorkItem) {
                                                            $predecessorWorkItem = \App\Models\WorkItem::find($item->predecessor);
                                                        }
                                                    @endphp
                                                    
                                                    @if($predecessorWorkItem)
                                                        <span class="badge bg-info text-white px-3 py-2"
                                                              data-bs-toggle="tooltip"
                                                              title="{{ $predecessorWorkItem->name }}">
                                                            <i class="fas fa-link me-1"></i>{{ $predecessorWorkItem->name }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark px-3 py-2"
                                                              data-bs-toggle="tooltip"
                                                              title="Work Item ID: {{ $item->predecessor }}">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>ID: {{ $item->predecessor }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-light text-muted px-3 py-2">
                                                        <i class="fas fa-minus"></i> لا يوجد
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->dependency_type && !empty($item->dependency_type))
                                                    @php
                                                        $depTypes = [
                                                            'end_to_start' => ['text' => 'نهاية إلى بداية', 'color' => 'primary', 'icon' => 'fa-arrow-right'],
                                                            'start_to_start' => ['text' => 'بداية إلى بداية', 'color' => 'info', 'icon' => 'fa-play'],
                                                            'end_to_end' => ['text' => 'نهاية إلى نهاية', 'color' => 'success', 'icon' => 'fa-stop'],
                                                            'start_to_end' => ['text' => 'بداية إلى نهاية', 'color' => 'warning', 'icon' => 'fa-exchange-alt']
                                                        ];
                                                        $depInfo = $depTypes[$item->dependency_type] ?? ['text' => $item->dependency_type, 'color' => 'secondary', 'icon' => 'fa-link'];
                                                    @endphp
                                                    <span class="badge bg-{{ $depInfo['color'] }} px-3 py-2">
                                                        <i class="fas {{ $depInfo['icon'] }} me-1"></i>
                                                        {{ $depInfo['text'] }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted px-3 py-2">
                                                        <i class="fas fa-minus"></i> لا يوجد
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $lagValue = $item->lag ?? 0;
                                                @endphp
                                                @if($lagValue != 0)
                                                    <span class="badge {{ $lagValue > 0 ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                                        <i class="fas {{ $lagValue > 0 ? 'fa-plus' : 'fa-minus' }} me-1"></i>
                                                        {{ $lagValue > 0 ? '+' : '' }}{{ $lagValue }} يوم
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-3 py-2">
                                                        <i class="fas fa-equals me-1"></i>0 يوم
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->notes)
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="popover"
                                                            data-bs-title="{{ __('general.notes') }}"
                                                            data-bs-content="{{ $item->notes }}"
                                                            data-bs-placement="left">
                                                        <i class="fas fa-sticky-note me-1"></i>
                                                        {{ __('general.view_notes') }}
                                                    </button>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-inbox fa-4x text-muted opacity-50"></i>
                            </div>
                            <h5 class="text-muted mb-3">{{ __('general.no_items_found') }}</h5>
                            <p class="text-muted mb-4">{{ __('general.no_template_items_message') }}</p>
                            <a href="{{ route('progress.project-templates.edit', $project_template) }}"
                               class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-plus me-2"></i>{{ __('general.add_items') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    
    @if($project_template->items->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-light rounded-top-3 py-3">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-chart-bar me-2"></i>{{ __('general.template_statistics') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="text-center p-4 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                                <i class="fas fa-calculator fa-2x text-primary mb-3"></i>
                                <h3 class="fw-bold text-primary mb-2">
                                    {{ number_format($project_template->items->sum('default_quantity'), 2) }}
                                </h3>
                                <p class="text-muted mb-0">{{ __('general.total_quantity') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-4 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                                <i class="fas fa-clock fa-2x text-success mb-3"></i>
                                <h3 class="fw-bold text-success mb-2">
                                    {{ $project_template->items->sum('duration') }}
                                </h3>
                                <p class="text-muted mb-0">{{ __('general.total_duration') }} ({{ __('general.days') }})</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-4 bg-info bg-opacity-10 rounded-3 border border-info border-opacity-25">
                                <i class="fas fa-project-diagram fa-2x text-info mb-3"></i>
                                <h3 class="fw-bold text-info mb-2">
                                    {{ $project_template->items->where('predecessor', '!=', null)->count() }}
                                </h3>
                                <p class="text-muted mb-0">{{ __('general.items_with_dependencies') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-4 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25">
                                <i class="fas fa-sticky-note fa-2x text-warning mb-3"></i>
                                <h3 class="fw-bold text-warning mb-2">
                                    {{ $project_template->items->where('notes', '!=', null)->count() }}
                                </h3>
                                <p class="text-muted mb-0">{{ __('general.items_with_notes') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
});
</script>
@endpush

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.breadcrumb {
    background: transparent;
    padding: 0;
}

.breadcrumb-item a:hover {
    color: var(--bs-primary) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.badge {
    font-weight: 500;
}

.bg-primary {
    background: linear-gradient(135deg, #4f46e5, #3b82f6) !important;
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5, #3b82f6);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #4338ca, #3730a3);
    transform: translateY(-1px);
}
</style>
