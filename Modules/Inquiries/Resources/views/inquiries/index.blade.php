@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Inquiries'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Inquiries')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            <!-- أزرار التحكم -->


            <div class="d-flex justify-content-between align-items-center mb-3">
                @can('create Inquiries')
                    <a href="{{ route('inquiries.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('Add New Inquiry') }}
                    </a>
                @endcan
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#columnsModal">
                        <i class="fas fa-columns me-2"></i>
                        {{ __('Manage Columns') }}
                    </button>
                    <button type="button" class="btn btn-info" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                        <i class="fas fa-filter me-2"></i>
                        {{ __('Filters') }}
                    </button>
                    <form action="{{ route('inquiries.preferences.reset') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning"
                            onclick="return confirm('{{ __('Reset all preferences?') }}')">
                            <i class="fas fa-undo me-2"></i>
                            {{ __('Reset') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- قسم الفلاتر -->
            <div class="collapse mb-3" id="filtersCollapse">
                <div class="card card-body">
                    <form id="filtersForm" method="GET" action="{{ route('inquiries.index') }}">
                        <div class="row g-3">
                            <!-- Project Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Project') }}</label>
                                <input type="text" name="filters[project]" class="form-control"
                                    value="{{ $filters['project'] ?? '' }}" placeholder="{{ __('Search project...') }}">
                            </div>

                            <!-- Client Filter (Dropdown) -->
                            <div class="col-md-3">
                                <label>{{ __('Client') }}</label>
                                <select name="filters[client]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['clients'] as $client)
                                        <option value="{{ $client->id }}"
                                            {{ ($filters['client'] ?? '') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                            @if ($client->type == 'company')
                                                <small class="text-muted">({{ __('Company') }})</small>
                                            @else
                                                <small class="text-muted">({{ __('Person') }})</small>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Main Contractor Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Main Contractor') }}</label>
                                <select name="filters[main_contractor]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['main_contractors'] as $contractor)
                                        <option value="{{ $contractor->id }}"
                                            {{ ($filters['main_contractor'] ?? '') == $contractor->id ? 'selected' : '' }}>
                                            {{ $contractor->name }}
                                            @if ($contractor->type == 'company')
                                                <small class="text-muted">({{ __('Company') }})</small>
                                            @else
                                                <small class="text-muted">({{ __('Person') }})</small>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Consultant Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Consultant') }}</label>
                                <select name="filters[consultant]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['consultants'] as $consultant)
                                        <option value="{{ $consultant->id }}"
                                            {{ ($filters['consultant'] ?? '') == $consultant->id ? 'selected' : '' }}>
                                            {{ $consultant->name }}
                                            @if ($consultant->type == 'company')
                                                <small class="text-muted">({{ __('Company') }})</small>
                                            @else
                                                <small class="text-muted">({{ __('Person') }})</small>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Owner Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Owner') }}</label>
                                <select name="filters[owner]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['owners'] as $owner)
                                        <option value="{{ $owner->id }}"
                                            {{ ($filters['owner'] ?? '') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }}
                                            @if ($owner->type == 'company')
                                                <small class="text-muted">({{ __('Company') }})</small>
                                            @else
                                                <small class="text-muted">({{ __('Person') }})</small>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assigned Engineer Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Assigned Engineer') }}</label>
                                <select name="filters[assigned_engineer]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['engineers'] as $engineer)
                                        <option value="{{ $engineer->id }}"
                                            {{ ($filters['assigned_engineer'] ?? '') == $engineer->id ? 'selected' : '' }}>
                                            {{ $engineer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status For KON Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Status For KON') }}</label>
                                <select name="filters[status_for_kon]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['status_for_kon'] as $status)
                                        <option value="{{ $status->value }}"
                                            {{ ($filters['status_for_kon'] ?? '') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- KON Title Filter -->
                            <div class="col-md-3">
                                <label>{{ __('KON Title') }}</label>
                                <select name="filters[kon_title]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['kon_titles'] as $title)
                                        <option value="{{ $title->value }}"
                                            {{ ($filters['kon_title'] ?? '') == $title->value ? 'selected' : '' }}>
                                            {{ $title->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Client Priority Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Client Priority') }}</label>
                                <select name="filters[client_priority]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['client_priorities'] as $priority)
                                        <option value="{{ $priority->value }}"
                                            {{ ($filters['client_priority'] ?? '') == $priority->value ? 'selected' : '' }}>
                                            {{ $priority->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- KON Priority Filter -->
                            <div class="col-md-3">
                                <label>{{ __('KON Priority') }}</label>
                                <select name="filters[kon_priority]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['kon_priorities'] as $priority)
                                        <option value="{{ $priority->value }}"
                                            {{ ($filters['kon_priority'] ?? '') == $priority->value ? 'selected' : '' }}>
                                            {{ $priority->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Status') }}</label>
                                <select name="filters[status]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['statuses'] as $status)
                                        <option value="{{ $status->value }}"
                                            {{ ($filters['status'] ?? '') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pricing Status Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Pricing Status') }}</label>
                                <select name="filters[pricing_status]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['pricing_statuses'] ?? [] as $status)
                                        <option value="{{ $status->id }}"
                                            {{ ($filters['pricing_status'] ?? '') == $status->id ? 'selected' : '' }}>
                                            {{ __($status->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <!-- Date Range Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Inquiry Date From') }}</label>
                                <input type="date" name="filters[inquiry_date][from]" class="form-control"
                                    value="{{ $filters['inquiry_date']['from'] ?? '' }}">
                            </div>

                            <div class="col-md-3">
                                <label>{{ __('Inquiry Date To') }}</label>
                                <input type="date" name="filters[inquiry_date][to]" class="form-control"
                                    value="{{ $filters['inquiry_date']['to'] ?? '' }}">
                            </div>

                            <!-- Work Type Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Work Type') }}</label>
                                <select name="filters[work_type]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['work_types'] as $workType)
                                        <option value="{{ $workType->id }}"
                                            {{ ($filters['work_type'] ?? '') == $workType->id ? 'selected' : '' }}>
                                            {{ $workType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Inquiry Source Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Inquiry Source') }}</label>
                                <select name="filters[inquiry_source]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['inquiry_sources'] as $source)
                                        <option value="{{ $source->id }}"
                                            {{ ($filters['inquiry_source'] ?? '') == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tender Number Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Tender Number') }}</label>
                                <input type="text" name="filters[tender_number]" class="form-control"
                                    value="{{ $filters['tender_number'] ?? '' }}"
                                    placeholder="{{ __('Search tender number...') }}">
                            </div>

                            <!-- Project Difficulty Filter -->
                            <div class="col-md-3">
                                <label>{{ __('Difficulty') }}</label>
                                <select name="filters[project_difficulty]" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($filterData['difficulties'] as $level => $label)
                                        <option value="{{ $level }}"
                                            {{ ($filters['project_difficulty'] ?? '') == $level ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>{{ __('Apply Filters') }}
                            </button>
                            <a href="{{ route('inquiries.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>{{ __('Clear Filters') }}
                            </a>
                            <button type="button" id="saveFilters" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>{{ __('Save Filters') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- الجدول -->
            <div class="card">
                <div class="card-body">
                    <x-table-export-actions table-id="inquiries-table" filename="inquiries"
                        excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                        print-label="{{ __('Print') }}" />

                    <div class="table-responsive">
                        <table id="inquiries-table" class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    @foreach ($visibleColumns as $column)
                                        <th class="text-center">{{ $availableColumns[$column] ?? $column }}</th>
                                    @endforeach
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inquiries as $inquiry)
                                    <tr>
                                        @foreach ($visibleColumns as $column)
                                            <td class="text-center">
                                                @if ($column === 'id')
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        {{ $inquiry->id }}
                                                        @if ($inquiry->created_at->diffInHours(now()) < 48)
                                                            <span class="badge bg-success pulse-badge">NEW</span>
                                                        @endif
                                                    </div>
                                                @elseif($column === 'project')
                                                    {{ $inquiry->project?->name ?? '-' }}
                                                @elseif($column === 'client')
                                                    {{ $inquiry->client?->cname ?? '-' }}
                                                @elseif($column === 'main_contractor')
                                                    {{ $inquiry->mainContractor?->cname ?? '-' }}
                                                @elseif($column === 'consultant')
                                                    {{ $inquiry->consultant?->cname ?? '-' }}
                                                @elseif($column === 'owner')
                                                    {{ $inquiry->owner?->cname ?? '-' }}
                                                @elseif($column === 'assigned_engineer')
                                                    {{ $inquiry->assignedEngineer?->cname ?? '-' }}
                                                @elseif($column === 'inquiry_date')
                                                    {{ $inquiry->inquiry_date?->format('Y-m-d') ?? '-' }}
                                                @elseif($column === 'req_submittal_date')
                                                    {{ $inquiry->req_submittal_date?->format('Y-m-d') ?? '-' }}
                                                @elseif($column === 'project_start_date')
                                                    {{ $inquiry->project_start_date?->format('Y-m-d') ?? '-' }}
                                                @elseif($column === 'status')
                                                    <span class="badge bg-{{ $inquiry->status->color() }}">
                                                        {{ $inquiry->status->label() }}
                                                    </span>
                                                @elseif($column === 'status_for_kon')
                                                    @if ($inquiry->status_for_kon)
                                                        <span class="badge bg-{{ $inquiry->status_for_kon->color() }}">
                                                            {{ $inquiry->status_for_kon->label() }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                @elseif($column === 'quotation_state')
                                                    @if ($inquiry->pricingStatus)
                                                        <span class="badge"
                                                            style="background-color: {{ $inquiry->pricingStatus->color }}">
                                                            {{ __($inquiry->pricingStatus->name) }}
                                                        </span>
                                                        @if ($inquiry->pricing_reason)
                                                            <i class="las la-info-circle text-info"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $inquiry->pricing_reason }}"></i>
                                                        @endif
                                                    @else
                                                        -
                                                    @endif
                                                @elseif($column === 'work_type')
                                                    {{ $inquiry->workType?->name ?? '-' }}
                                                @elseif($column === 'inquiry_source')
                                                    {{ $inquiry->inquirySource?->name ?? '-' }}
                                                @elseif($column === 'city')
                                                    {{ $inquiry->city?->name ?? '-' }}
                                                @elseif($column === 'town')
                                                    {{ $inquiry->town?->name ?? '-' }}
                                                @elseif($column === 'total_project_value')
                                                    {{ number_format($inquiry->total_project_value ?? 0, 2) }}
                                                @elseif($column === 'client_priority')
                                                    @if ($inquiry->client_priority)
                                                        <span class="badge bg-{{ $inquiry->client_priority->color() }}">
                                                            {{ $inquiry->client_priority->label() }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                @elseif($column === 'kon_priority')
                                                    @if ($inquiry->kon_priority)
                                                        <span class="badge bg-{{ $inquiry->kon_priority->color() }}">
                                                            {{ $inquiry->kon_priority->label() }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                @elseif($column === 'project_difficulty')
                                                    @php
                                                        $diffColors = [
                                                            1 => 'success',
                                                            2 => 'info',
                                                            3 => 'warning',
                                                            4 => 'danger',
                                                        ];
                                                        $diffLabels = [
                                                            1 => 'Easy',
                                                            2 => 'Medium',
                                                            3 => 'Hard',
                                                            4 => 'Very Hard',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $diffColors[$inquiry->project_difficulty] ?? 'secondary' }}">
                                                        {{ __($diffLabels[$inquiry->project_difficulty] ?? 'N/A') }}
                                                    </span>
                                                @elseif($column === 'tender_number')
                                                    {{ $inquiry->tender_number ?? '-' }}
                                                @elseif($column === 'kon_title')
                                                    {{ $inquiry->kon_title?->label() ?? '-' }}
                                                @else
                                                    {{ $inquiry->$column ?? '-' }}
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <div class="btn-group">

                                                @can('view Inquiries')
                                                    <a class="btn btn-primary btn-sm"
                                                        href="{{ route('inquiries.show', $inquiry->id) }}">
                                                        <i class="las la-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('edit Inquiries')
                                                    <a class="btn btn-success btn-sm"
                                                        href="{{ route('inquiries.edit', $inquiry->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Inquiries')
                                                    <form action="{{ route('inquiries.destroy', $inquiry->id) }}"
                                                        method="POST" style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan

                                                @can('edit Inquiries')
                                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#commentModal-{{ $inquiry->id }}">
                                                        <i class="las la-comment"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="20" class="text-center">
                                            <div class="alert alert-info py-3 mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No inquiries found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $inquiries->links() }}
                    </div>
                </div>
            </div>

            <!-- Modal لإدارة الأعمدة -->
            <div class="modal fade" id="columnsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-columns me-2"></i>
                                {{ __('Manage Visible Columns') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">{{ __('Select the columns you want to display in the table') }}</p>
                            <form id="columnsForm">
                                <div class="row g-3">
                                    @foreach ($availableColumns as $key => $label)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="visible_columns[]"
                                                    value="{{ $key }}" id="col_{{ $key }}"
                                                    {{ in_array($key, $visibleColumns) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="col_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="saveColumns">
                                <i class="fas fa-save me-2"></i>{{ __('Save & Reload') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Modals -->
            @forelse ($inquiries as $inquiry)
                <div class="modal fade" id="commentModal-{{ $inquiry->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-comments me-2"></i>
                                    {{ __('Inquiry Comments') }}: {{ $inquiry->project->name ?? '' }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <livewire:inquiries::inquiry-comments :inquiryId="$inquiry->id" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>{{ __('Close') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    </div>

    <!-- CSS للـ NEW Badge Animation -->
    <style>
        .pulse-badge {
            animation: pulse 2s infinite;
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        .table th {
            white-space: nowrap;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>

    <!-- JavaScript للـ AJAX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // حفظ الأعمدة
            document.getElementById('saveColumns')?.addEventListener('click', function() {
                const form = document.getElementById('columnsForm');
                const formData = new FormData(form);
                const visibleColumns = formData.getAll('visible_columns[]');

                if (visibleColumns.length === 0) {
                    alert('{{ __('Please select at least one column') }}');
                    return;
                }

                fetch('{{ route('inquiries.preferences.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            visible_columns: visibleColumns
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('An error occurred while saving') }}');
                    });
            });

            // حفظ الفلاتر
            document.getElementById('saveFilters')?.addEventListener('click', function(e) {
                e.preventDefault();

                const form = document.getElementById('filtersForm');
                const formData = new FormData(form);
                const filters = {};

                formData.forEach((value, key) => {
                    // تحويل filters[key] إلى object
                    const match = key.match(/filters\[([^\]]+)\](?:\[([^\]]+)\])?/);
                    if (match) {
                        const mainKey = match[1];
                        const subKey = match[2];

                        if (subKey) {
                            if (!filters[mainKey]) filters[mainKey] = {};
                            filters[mainKey][subKey] = value;
                        } else {
                            filters[mainKey] = value;
                        }
                    }
                });

                fetch('{{ route('inquiries.preferences.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            filters: filters
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('{{ __('Filters saved successfully') }}');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('An error occurred while saving') }}');
                    });
            });
        });
    </script>
@endsection
