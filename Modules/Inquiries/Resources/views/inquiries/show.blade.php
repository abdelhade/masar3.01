@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Inquiry Details'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Inquiries'), 'url' => route('inquiries.index')],
            ['label' => __('Details')],
        ],
    ])

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Tajawal', sans-serif;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 1rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
        }

        .info-value {
            color: #212529;
        }

        .document-card {
            transition: transform 0.2s;
        }

        .document-card:hover {
            transform: scale(1.02);
        }

        .comments-list .alert {
            border-radius: 8px;
        }

        /* Work Types Styles */
        .work-types-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            background-color: #f8f9fc;
        }

        .selected-work-types {
            margin-bottom: 15px;
        }

        .work-type-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background-color: white;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            margin-bottom: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .work-type-name {
            font-weight: 500;
            color: #3a3b45;
        }

        .work-type-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .add-update-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e3e6f0;
        }

        /* Status badges */
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Inquiry Details') }}: {{ $inquiry->project->name ?? '' }}
                            </h4>
                            <div>
                                <span class="badge bg-light text-dark">{{ __('Inquiry Number') }}: {{ $inquiry->id }}</span>
                                <a href="{{ route('inquiries.edit', $inquiry->id) }}" class="btn btn-sm btn-light ms-2">
                                    <i class="fas fa-edit me-1"></i>
                                    {{ __('Edit') }}
                                </a>
                                <a href="{{ route('inquiries.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-right me-1"></i>
                                    {{ __('Back To List') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Project Data Section -->
                        <div class="row">
                            <div class="col-4">
                                <div class="mb-4">
                                    <h5 class="section-title">
                                        <i class="fas fa-project-diagram me-2"></i>
                                        {{ __('Project Data') }}
                                    </h5>
                                    <div class="card">
                                        <div class="card-body">

                                            <div class="info-row">
                                                <span class="info-label">{{ __('Project:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->project?->name ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Inquiry Date:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->inquiry_date?->format('Y-m-d') ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Required Submission Date:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->req_submittal_date?->format('Y-m-d') ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Project Start Date:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->project_start_date?->format('Y-m-d') ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('City:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->city?->title ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Town:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->town?->title ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Distance:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->town_distance ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Inquiry Status:') }}</span>
                                                <span class="info-value">
                                                    @if ($inquiry->status)
                                                        <span
                                                            class="badge status-badge
                                                        @if ($inquiry->status->value == 'new') bg-primary
                                                        @elseif($inquiry->status->value == 'in_progress') bg-warning
                                                        @elseif($inquiry->status->value == 'completed') bg-success
                                                        @elseif($inquiry->status->value == 'cancelled') bg-danger
                                                        @else bg-secondary @endif">
                                                            {{ $inquiry->status->label() }}
                                                        </span>
                                                    @else
                                                        {{ __('Not Specified') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('KON Status:') }}</span>
                                                <span class="info-value">
                                                    @if ($inquiry->status_for_kon)
                                                        <span
                                                            class="badge status-badge
                                                        @if ($inquiry->status_for_kon->value == 'pending') bg-warning
                                                        @elseif($inquiry->status_for_kon->value == 'approved') bg-success
                                                        @elseif($inquiry->status_for_kon->value == 'rejected') bg-danger
                                                        @else bg-secondary @endif">
                                                            {{ $inquiry->status_for_kon->label() }}
                                                        </span>
                                                    @else
                                                        {{ __('Not Specified') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('KON Title:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->kon_title ? $inquiry->kon_title->label() : __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Project Size:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->projectSize->name ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Client Priority:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->client_priority ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('KON Priority:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->kon_priority ?? __('Not Specified') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estimation Information Section -->
                            <div class="col-4">
                                <div class="mb-4">
                                    <h5 class="section-title">
                                        <i class="fas fa-calculator me-2"></i>
                                        {{ __('Estimation Information') }}
                                    </h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Tender Number:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->tender_number ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Tender ID:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->tender_id ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Estimation Start Date:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->estimation_start_date?->format('Y-m-d') ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Estimation End Date:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->estimation_finished_date?->format('Y-m-d') ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Submission Date:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->submitting_date?->format('Y-m-d') ?? __('Not Specified') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Total Value:') }}</span>
                                                <span
                                                    class="info-value">{{ number_format($inquiry->total_project_value ?? 0, 2) }}
                                                    {{ __('SAR') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Work Types Section -->
                                <div class="mb-4">
                                    <h5 class="section-title">
                                        <i class="fas fa-sitemap me-2"></i>
                                        {{ __('Selected Work Types') }}
                                    </h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="work-types-section">
                                                @if (!empty($allWorkTypes) && count($allWorkTypes) > 0)
                                                    <h6 class="text-primary mb-3">
                                                        <i class="fas fa-list-check me-2"></i>
                                                        {{ __('Selected Works') }} ({{ count($allWorkTypes) }})
                                                    </h6>

                                                    <div class="selected-work-types">
                                                        @foreach ($allWorkTypes as $index => $item)
                                                            <div class="work-type-item mb-3">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-start w-100">
                                                                    <div class="flex-grow-1">
                                                                        <div class="mb-2">
                                                                            <span class="badge bg-primary me-2">
                                                                                #{{ $item['order'] + 1 }}
                                                                            </span>
                                                                            <strong
                                                                                class="text-primary">{{ $item['work_type']->name }}</strong>
                                                                        </div>

                                                                        @if (!empty($item['hierarchy_path']))
                                                                            <div class="mb-2">
                                                                                <i
                                                                                    class="fas fa-route text-muted me-2"></i>
                                                                                <small
                                                                                    class="text-muted">{{ __('Hierarchy Path:') }}</small>
                                                                                <div class="mt-1">
                                                                                    <span class="badge bg-light text-dark">
                                                                                        {{ implode(' → ', $item['hierarchy_path']) }}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        @endif

                                                                        @if (!empty($item['description']))
                                                                            <div>
                                                                                <i
                                                                                    class="fas fa-info-circle text-info me-2"></i>
                                                                                <small
                                                                                    class="text-muted">{{ __('Description:') }}</small>
                                                                                <p class="mb-0 mt-1 text-dark">
                                                                                    {{ $item['description'] }}</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div>
                                                                        <span class="badge bg-success">
                                                                            <i class="fas fa-check me-1"></i>
                                                                            {{ __('Selected') }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if ($inquiry->final_work_type)
                                                        <div class="alert alert-info mt-3">
                                                            <i class="fas fa-edit me-2"></i>
                                                            <strong>{{ __('Final General Description:') }}</strong>
                                                            <p class="mb-0 mt-2">{{ $inquiry->final_work_type }}</p>
                                                        </div>
                                                    @endif
                                                @elseif($inquiry->workType)
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        {{ __('There Is Only One Main Work Type') }}
                                                    </div>

                                                    <div class="work-type-item">
                                                        <div
                                                            class="d-flex justify-content-between align-items-start w-100">
                                                            <div class="flex-grow-1">
                                                                <strong
                                                                    class="text-primary">{{ $inquiry->workType->name }}</strong>

                                                                @if (!empty($workTypePath))
                                                                    <div class="mt-2">
                                                                        <small
                                                                            class="text-muted">{{ __('Hierarchy Path:') }}</small>
                                                                        <div class="mt-1">
                                                                            <span class="badge bg-light text-dark">
                                                                                {{ implode(' → ', $workTypePath) }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if ($inquiry->final_work_type)
                                                                    <div class="mt-2">
                                                                        <small
                                                                            class="text-muted">{{ __('Description:') }}</small>
                                                                        <p class="mb-0 mt-1">
                                                                            {{ $inquiry->final_work_type }}</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <span class="badge bg-secondary">{{ __('Main') }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning text-center py-3">
                                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                        <p class="mb-0">{{ __('No Works Selected') }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Work Type and Inquiry Source Section -->
                            <div class="col-4">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="section-title">
                                            <i class="fas fa-stream me-2"></i>
                                            {{ __('Inquiry Source') }}
                                        </h5>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Hierarchy Path:') }}</span>
                                                    <span
                                                        class="info-value">{{ !empty($inquirySourcePath) ? implode(' → ', $inquirySourcePath) : __('Not Specified') }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Final Description:') }}</span>
                                                    <span
                                                        class="info-value">{{ $inquiry->final_inquiry_source ?? __('Not Specified') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Quotation State Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="section-title">
                                            <i class="fas fa-file-invoice-dollar me-2"></i>
                                            {{ __('Quotation Status') }}
                                        </h5>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Quotation Status:') }}</span>
                                                    <span class="info-value">
                                                        @if ($inquiry->quotation_state)
                                                            <span
                                                                class="badge bg-{{ $inquiry->quotation_state->color() }}">
                                                                {{ $inquiry->quotation_state->label() }}
                                                            </span>
                                                        @else
                                                            {{ __('Not Specified') }}
                                                        @endif
                                                    </span>
                                                </div>
                                                @if (
                                                    $inquiry->quotation_state &&
                                                        in_array($inquiry->quotation_state, [
                                                            \Modules\Inquiries\Enums\QuotationStateEnum::REJECTED,
                                                            \Modules\Inquiries\Enums\QuotationStateEnum::RE_ESTIMATION,
                                                        ]))
                                                    <div class="info-row">
                                                        <span class="info-label">{{ __('Rejection Reason:') }}</span>
                                                        <span
                                                            class="info-value">{{ $inquiry->rejection_reason ?? __('Not Specified') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stakeholders Section -->
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="section-title">
                                            <i class="fas fa-users me-2"></i>
                                            {{ __('Stakeholders') }}
                                        </h5>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Client:') }}</span>
                                                    <span
                                                        class="info-value">{{ $inquiry->client?->cname ?? __('Not Specified') }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Main Contractor:') }}</span>
                                                    <span
                                                        class="info-value">{{ $inquiry->mainContractor?->cname ?? __('Not Specified') }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Consultant:') }}</span>
                                                    <span
                                                        class="info-value">{{ $inquiry->consultant?->cname ?? __('Not Specified') }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Owner:') }}</span>
                                                    <span
                                                        class="info-value">{{ $inquiry->owner?->cname ?? __('Not Specified') }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="info-label">{{ __('Assigned Engineer:') }}</span>
                                                    <span
                                                        class="info-value">{{ $inquiry->assignedEngineer?->cname ?? __('Not Specified') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Types and Units Section -->
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-list me-2"></i>
                                {{ __('Types and Units') }}
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Types:') }}</span>
                                                <span class="info-value">
                                                    @if (!empty($inquiry->types))
                                                        {{ is_array($inquiry->types) ? implode(', ', $inquiry->types) : $inquiry->types }}
                                                    @else
                                                        {{ __('Not Specified') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Units:') }}</span>
                                                <span class="info-value">
                                                    @if (!empty($inquiry->unit))
                                                        {{ is_array($inquiry->unit) ? implode(', ', $inquiry->unit) : $inquiry->unit }}
                                                    @else
                                                        {{ __('Not Specified') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">{{ __('Type Note:') }}</span>
                                                <span
                                                    class="info-value">{{ $inquiry->type_note ?? __('Not Specified') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-file-alt me-2"></i>
                                {{ __('Project Documents') }}
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    @if ($inquiry->projectDocuments->isNotEmpty())
                                        <div class="row">
                                            @foreach ($inquiry->projectDocuments as $document)
                                                <div class="col-md-2 mb-3">
                                                    <div class="card document-card">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-file fa-2x text-primary mb-2"></i>
                                                            <h6>{{ $document->name }}</h6>
                                                            @if ($document->name === 'other' && $document->pivot->description)
                                                                <p class="text-muted">{{ $document->pivot->description }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No Documents Available') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Existing Documents Section -->
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-folder-open me-2"></i>
                                {{ __('Uploaded Documents') }}
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    @if ($inquiry->getMedia('inquiry-documents')->isNotEmpty())
                                        <div class="row">
                                            @foreach ($inquiry->getMedia('inquiry-documents') as $media)
                                                <div class="col-md-2 mb-3">
                                                    <div class="card document-card">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                                            <h6>{{ $media->file_name }}</h6>
                                                            <p class="text-muted">
                                                                {{ number_format($media->size / 1024, 2) }} KB</p>
                                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye me-1"></i>
                                                                {{ __('View') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No Uploaded Documents') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Submittal Checklist Section -->
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-check-square me-2"></i>
                                {{ __('Required Submission Checklist') }}
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    @if ($inquiry->submittalChecklists->isNotEmpty())
                                        <div class="row">
                                            @foreach ($inquiry->submittalChecklists as $item)
                                                <div class="col-md-2 mb-3">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                                            <h6>{{ $item->name }}</h6>
                                                            <p class="text-muted">{{ __('Value') }}:
                                                                {{ $item->score }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-success fw-bold">
                                            {{ __('Total Score:') }} <span
                                                class="badge bg-success">{{ $inquiry->total_submittal_score }}</span>
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No Required Submissions') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Working Conditions Section -->
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('Working Conditions') }}
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    @if ($inquiry->workConditions->isNotEmpty())
                                        <div class="row">
                                            @foreach ($inquiry->workConditions as $condition)
                                                <div class="col-md-3 mb-3">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-tools fa-2x text-danger mb-2"></i>
                                                            <h6>{{ $condition->name }}</h6>
                                                            <p class="text-muted">{{ __('Value') }}:
                                                                {{ $condition->score }}</p>
                                                            @if ($condition->pivot->value)
                                                                <p class="text-muted">{{ __('Selected Option') }}:
                                                                    {{ $condition->pivot->value }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-list-check fa-2x text-primary mb-2"></i>
                                                <h6>{{ __('Total Conditions Score') }}</h6>
                                                <span
                                                    class="badge bg-primary">{{ $inquiry->total_conditions_score }}</span>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                                <h6>{{ __('Project Difficulty') }}</h6>
                                                <span class="badge bg-warning">{{ $inquiry->project_difficulty }}</span>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                                <h6>{{ __('Difficulty Classification') }}</h6>
                                                <span
                                                    class="badge @if ($inquiry->project_difficulty == 1) bg-success @elseif($inquiry->project_difficulty == 2) bg-warning @elseif($inquiry->project_difficulty == 3) bg-danger @else bg-dark @endif">
                                                    @if ($inquiry->project_difficulty == 1)
                                                        {{ __('Easy') }}
                                                    @elseif($inquiry->project_difficulty == 2)
                                                        {{ __('Medium') }}
                                                    @elseif($inquiry->project_difficulty == 3)
                                                        {{ __('Hard') }}
                                                    @else
                                                        {{ __('Very Hard') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No Working Conditions') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <div class="mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-comments me-2"></i>
                                {{ __('Comments') }}
                            </h5>
                            <div class="card">
                                <div class="card-body">
                                    @if ($inquiry->comments->isNotEmpty())
                                        <div class="comments-list">
                                            @foreach ($inquiry->comments as $comment)
                                                <div class="alert alert-secondary mb-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong><i class="fas fa-user me-1"></i>
                                                                {{ $comment->user?->name ?? __('Unknown') }}</strong>
                                                            <small class="text-muted ms-2">
                                                                <i class="fas fa-clock me-1"></i>
                                                                {{ $comment->created_at->format('Y-m-d H:i') }}
                                                            </small>
                                                            <p class="mb-0 mt-2">{{ $comment->comment }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No Comments Available') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
