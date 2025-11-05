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

    <div class="container-fluid">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-clipboard-list text-primary me-2"></i>
                    {{ __('Inquiry') }} #{{ $inquiry->id }}
                </h1>
                <p class="text-muted mb-0 mt-1">{{ $inquiry->tender_id ?? __('No Tender ID') }}</p>
            </div>
            <div>
                @if ($inquiry->is_draft)
                    <span class="badge bg-warning text-dark me-2 px-3 py-2">
                        <i class="fas fa-pencil-alt me-1"></i>
                        {{ __('Draft') }}
                    </span>
                @endif
                <a href="{{ route('inquiries.edit', $inquiry->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('inquiries.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Column 1: Project Information -->
            <div class="col-xl-4 col-lg-6 mb-4">
                <!-- Basic Info -->
                <div class="card border-left-primary shadow mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <h5 class="mb-0">{{ __('Project Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Project') }}:</strong>
                            <p class="mb-0">{{ $inquiry->project?->name ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Inquiry Date') }}:</strong>
                            <p class="mb-0">{{ $inquiry->inquiry_date?->format('Y-m-d') ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Required Submission Date') }}:</strong>
                            <p class="mb-0">{{ $inquiry->req_submittal_date?->format('Y-m-d') ?? __('Not Specified') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Project Start Date') }}:</strong>
                            <p class="mb-0">{{ $inquiry->project_start_date?->format('Y-m-d') ?? __('Not Specified') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Status') }}:</strong>
                            <p class="mb-0">
                                @if ($inquiry->status)
                                    <span
                                        class="badge bg-{{ $inquiry->status->color() }}">{{ $inquiry->status->label() }}</span>
                                @else
                                    {{ __('Not Specified') }}
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Status For KON') }}:</strong>
                            <p class="mb-0">
                                @if ($inquiry->status_for_kon)
                                    <span
                                        class="badge bg-{{ $inquiry->status_for_kon->color() }}">{{ $inquiry->status_for_kon->label() }}</span>
                                @else
                                    {{ __('Not Specified') }}
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('KON Title') }}:</strong>
                            <p class="mb-0">{{ $inquiry->kon_title?->label() ?? __('Not Specified') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card border-left-info shadow mb-4">
                    <div class="card-header bg-info text-white d-flex align-items-center">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <h5 class="mb-0">{{ __('Location') }}</h5>
                    </div>
                    <div class="card-body">
                        {{-- <div class="mb-3">
                            <strong class="text-gray-700">{{ __('City') }}:</strong>
                            <p class="mb-0">{{ $inquiry->city?->title ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Town') }}:</strong>
                            <p class="mb-0">{{ $inquiry->town?->title ?? __('Not Specified') }}</p>
                        </div> --}}
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Distance From HQ') }}:</strong>
                            <p class="mb-0">
                                {{-- @if ($inquiry->town_distance) --}}
                                    <span class="badge bg-info text-white">{{ number_format($inquiry->town_distance, 2) }}
                                        {{ __('KM') }}</span>
                                {{-- @endif --}}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Priority -->
                <div class="card border-left-warning shadow mb-4">
                    <div class="card-header bg-warning text-dark d-flex align-items-center">
                        <i class="fas fa-star me-2"></i>
                        <h5 class="mb-0">{{ __('Priority') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Client Priority') }}:</strong>
                            <p class="mb-0">
                                @if ($inquiry->client_priority)
                                    <span class="badge bg-warning text-dark">{{ $inquiry->client_priority }}</span>
                                @else
                                    {{ __('Not Specified') }}
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('KON Priority') }}:</strong>
                            <p class="mb-0">
                                @if ($inquiry->kon_priority)
                                    <span class="badge bg-danger text-white">{{ $inquiry->kon_priority }}</span>
                                @else
                                    {{ __('Not Specified') }}
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Project Size') }}:</strong>
                            <p class="mb-0">{{ $inquiry->projectSize?->name ?? __('Not Specified') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Estimation -->
                <div class="card border-left-secondary shadow mb-4">
                    <div class="card-header bg-secondary text-white d-flex align-items-center">
                        <i class="fas fa-calculator me-2"></i>
                        <h5 class="mb-0">{{ __('Estimation Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Tender Number') }}:</strong>
                            <p class="mb-0">{{ $inquiry->tender_number ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Estimation Start') }}:</strong>
                            <p class="mb-0">
                                {{ $inquiry->estimation_start_date?->format('Y-m-d') ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Estimation End') }}:</strong>
                            <p class="mb-0">
                                {{ $inquiry->estimation_finished_date?->format('Y-m-d') ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Submission Date') }}:</strong>
                            <p class="mb-0">{{ $inquiry->submitting_date?->format('Y-m-d') ?? __('Not Specified') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Total Value') }}:</strong>
                            <p class="mb-0">
                                @if ($inquiry->total_project_value)
                                    <strong class="text-success">{{ number_format($inquiry->total_project_value, 2) }}
                                        {{ __('SAR') }}</strong>
                                @else
                                    {{ __('Not Specified') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quotation State -->
                <div class="card border-left-danger shadow mb-4">
                    <div class="card-header bg-danger text-white d-flex align-items-center">
                        <i class="fas fa-file-invoice me-2"></i>
                        <h5 class="mb-0">{{ __('Quotation Status') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-gray-700">{{ __('Status') }}:</strong>
                            <p class="mb-0">
                                @if ($inquiry->quotation_state)
                                    <span class="badge bg-{{ $inquiry->quotation_state->color() }} px-3 py-2">
                                        {{ $inquiry->quotation_state->label() }}
                                    </span>
                                @else
                                    {{ __('Not Specified') }}
                                @endif
                            </p>
                        </div>
                        @if ($inquiry->rejection_reason)
                            <div class="alert alert-warning mb-0">
                                <strong>{{ __('Rejection Reason') }}:</strong><br>
                                {{ $inquiry->rejection_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ======== Work Types & Inquiry Source ======== -->
            <div class="col-xl-4 col-lg-6 mb-4">

                {{-- 1. Work Types --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-sitemap me-2"></i>
                        <h5 class="mb-0">{{ __('Selected Work Types') }}</h5>
                    </div>
                    <div class="card-body">

                        @if (!empty($allWorkTypes) && count($allWorkTypes) > 0)
                            @foreach ($allWorkTypes as $item)
                                <div class="border rounded p-3 mb-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-primary mb-0">
                                            <span class="badge bg-primary me-2">#{{ $item['order'] + 1 }}</span>
                                            {{ $item['work_type']->name }}
                                        </h6>
                                    </div>

                                    {{-- Hierarchy Path --}}
                                    @if (!empty($item['hierarchy_path']))
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            @foreach ($item['hierarchy_path'] as $path)
                                                <span class="badge bg-info text-white small">{{ $path }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Description --}}
                                    @if (!empty($item['description']))
                                        <small class="text-muted d-block mb-1">
                                            <i class="fas fa-info-circle me-1"></i>{{ __('Description') }}:
                                        </small>
                                        <p class="mb-0 small text-dark">{{ $item['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Final General Description --}}
                            @if ($inquiry->final_work_type)
                                <div class="alert alert-info small p-2 mt-2 mb-0">
                                    <i class="fas fa-edit me-1"></i>
                                    <strong>{{ __('Final General Description') }}:</strong>
                                    {{ $inquiry->final_work_type }}
                                </div>
                            @endif

                            {{-- Total Submittal Score --}}
                            <div class="text-end mt-2">
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    {{ __('Total Submittal Score') }}: {{ $inquiry->total_submittal_score ?? 0 }}
                                </span>
                            </div>
                        @elseif($inquiry->workType)
                            {{-- Single main work type (old style) --}}
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-primary mb-0">
                                    {{ $inquiry->workType->name }}
                                </h6>

                                @if (!empty($workTypePath))
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach ($workTypePath as $path)
                                            <span class="badge bg-info text-white small">{{ $path }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($inquiry->final_work_type)
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>{{ __('Description') }}:
                                    </small>
                                    <p class="mb-0 small">{{ $inquiry->final_work_type }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-center text-muted py-3">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                                {{ __('No Works Selected') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Inquiry Source Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white d-flex align-items-center">
                        <i class="fas fa-project-diagram me-2"></i>
                        <h5 class="mb-0">{{ __('Inquiry Source') }}</h5>
                    </div>
                    <div class="card-body">

                        {{-- Hierarchy Path (مثل: Marketing → Email → Client X) --}}
                        @if (!empty($inquirySourcePath))
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach ($inquirySourcePath as $item)
                                    <span class="badge bg-info text-white small px-2">
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('No hierarchy path available') }}
                            </p>
                        @endif

                        {{-- Final Description --}}
                        @if ($inquiry->final_inquiry_source)
                            <div class="alert alert-light small p-3 mb-0 rounded">
                                <strong class="text-success">
                                    <i class="fas fa-quote-right me-1"></i>
                                    {{ __('Final Description') }}:
                                </strong>
                                <p class="mb-0 mt-1 text-dark">{{ $inquiry->final_inquiry_source }}</p>
                            </div>
                        @else
                            <p class="text-muted text-center small mb-0">
                                <i class="fas fa-ban me-1"></i>
                                {{ __('No final description') }}
                            </p>
                        @endif

                    </div>
                </div>

            </div>
            <!-- ======== نهاية القسم ======== -->

            <div class="col-xl-4 col-lg-12 mb-4">
                <!-- Contacts -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-users me-2"></i>
                        <h5 class="mb-0">{{ __('Contacts') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach ([
                                    'client' => ['label' => __('Client'), 'icon' => 'fa-user-tie', 'color' => 'primary'],
                                    'main_contractor' => ['label' => __('Main Contractor'), 'icon' => 'fa-hard-hat', 'color' => 'success'],
                                    'consultant' => ['label' => __('Consultant'), 'icon' => 'fa-user-check', 'color' => 'info'],
                                    'owner' => ['label' => __('Owner'), 'icon' => 'fa-crown', 'color' => 'warning'],
                                    'engineer' => ['label' => __('Engineer'), 'icon' => 'fa-user-cog', 'color' => 'danger'],
                                ] as $roleKey => $roleData)
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <i class="fas {{ $roleData['icon'] }} fa-lg text-{{ $roleData['color'] }}"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small text-gray-800">{{ $roleData['label'] }}</strong>
                                        <span class="badge bg-{{ $roleData['color'] }} text-white small">
                                            {{ $roleData['label'] }}
                                        </span>
                                    </div>

                                    @if (isset($contactsByRole[$roleKey]) && $contactsByRole[$roleKey])
                                        @php $contact = $contactsByRole[$roleKey]; @endphp
                                        <div class="small">
                                            <div class="fw-bold">{{ $contact->name }}</div>
                                            @if ($contact->email)
                                                <div class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i> {{ $contact->email }}
                                                </div>
                                            @endif
                                            @if ($contact->phone_1)
                                                <div class="text-muted">
                                                    <i class="fas fa-phone me-1"></i> {{ $contact->phone_1 }}
                                                </div>
                                            @endif
                                            @if ($contact->type)
                                                <span
                                                    class="badge bg-light text-dark small mt-1">{{ ucfirst($contact->type) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-muted small">
                                            <i class="fas fa-user-slash me-1"></i> {{ __('Not Assigned') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if ($inquiry->assigned_engineer_date)
                            <div class="alert alert-light small mt-3 mb-0 p-2">
                                <i class="fas fa-calendar-check me-1"></i>
                                <strong>{{ __('Engineer Assignment Date') }}:</strong>
                                {{-- {{ $inquiry->assigned_engineer_date->format('Y-m-d') }} --}}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quotation Types & Units -->
                @if (!empty($quotationData))
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark d-flex align-items-center">
                            <i class="fas fa-list-ul me-2"></i>
                            <h5 class="mb-0">{{ __('Quotation Types & Units') }}</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($quotationData as $data)
                                <div class="mb-3">
                                    <h6 class="text-primary small fw-bold mb-2">
                                        <i class="fas fa-tag me-1"></i> {{ $data['type']->name }}
                                    </h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($data['units'] as $unit)
                                            <span class="badge bg-white text-primary border small">
                                                {{ $unit->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Type Note -->
                @if ($inquiry->type_note)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white d-flex align-items-center">
                            <i class="fas fa-sticky-note me-2"></i>
                            <h5 class="mb-0">{{ __('Type Note') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light small p-3 mb-0">{{ $inquiry->type_note }}</div>
                        </div>
                    </div>
                @endif

                <!-- Total Score -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center p-4 bg-success text-white">
                        <i class="fas fa-trophy fa-2x mb-3"></i>
                        <h3 class="mb-0 fw-bold">{{ $inquiry->total_check_list_score ?? 0 }}</h3>
                        <p class="mb-0 small">{{ __('Total Score') }}</p>
                    </div>
                </div>

                <!-- Project Difficulty -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white d-flex align-items-center">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        <h5 class="mb-0">{{ __('Project Difficulty') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex rounded overflow-hidden mb-3" style="height: 10px;">
                            <div class="flex-fill {{ $inquiry->project_difficulty >= 1 ? 'bg-success' : 'bg-light' }}">
                            </div>
                            <div class="flex-fill {{ $inquiry->project_difficulty >= 2 ? 'bg-warning' : 'bg-light' }}">
                            </div>
                            <div class="flex-fill {{ $inquiry->project_difficulty >= 3 ? 'bg-danger' : 'bg-light' }}">
                            </div>
                            <div class="flex-fill {{ $inquiry->project_difficulty >= 4 ? 'bg-secondary' : 'bg-light' }}">
                            </div>
                        </div>
                        <div class="text-center">
                            <span
                                class="badge bg-{{ $inquiry->project_difficulty == 1
                                    ? 'success'
                                    : ($inquiry->project_difficulty == 2
                                        ? 'warning'
                                        : ($inquiry->project_difficulty == 3
                                            ? 'danger'
                                            : 'secondary')) }} text-white">
                                @switch($inquiry->project_difficulty)
                                    @case(1)
                                        {{ __('Easy') }}
                                    @break

                                    @case(2)
                                        {{ __('Medium') }}
                                    @break

                                    @case(3)
                                        {{ __('Hard') }}
                                    @break

                                    @default
                                        {{ __('Very Hard') }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Width Sections -->
        <div class="row">
            <!-- Work Conditions -->
            @if ($inquiry->workConditions->isNotEmpty())
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <h5 class="mb-0">{{ __('Working Conditions') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-md-3 g-3">
                                @foreach ($inquiry->workConditions as $condition)
                                    <div class="col">
                                        <div class="border rounded p-3 bg-white text-center h-100">
                                            <i class="fas fa-tools text-warning fa-2x mb-2"></i>
                                            <h6 class="mb-1">{{ $condition->name }}</h6>
                                            <span class="badge bg-warning text-dark">{{ __('Score') }}:
                                                {{ $condition->score }}</span>
                                            @if (isset($condition->pivot->selected_option))
                                                <small class="text-muted d-block mt-1">
                                                    {{ __('Selected') }}: {{ $condition->pivot->selected_option }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-end mt-3">
                                <span class="badge bg-warning text-dark fs-6 px-4 py-2">
                                    {{ __('Total Conditions Score') }}: {{ $inquiry->total_check_list_score ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Project Documents -->
            @if ($inquiry->projectDocuments->isNotEmpty())
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white d-flex align-items-center">
                            <i class="fas fa-file-alt me-2"></i>
                            <h5 class="mb-0">{{ __('Project Documents') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-md-4 g-3">
                                @foreach ($inquiry->projectDocuments as $document)
                                    <div class="col">
                                        <div class="border rounded p-3 bg-white text-center">
                                            <i class="fas fa-file-pdf text-primary fa-3x mb-2"></i>
                                            <h6 class="mb-1">{{ $document->name }}</h6>
                                            @if ($document->name === 'other' && $document->pivot->description)
                                                <small
                                                    class="text-muted d-block">{{ $document->pivot->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Uploaded Documents -->
            @if ($inquiry->getMedia('inquiry-documents')->isNotEmpty())
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white d-flex align-items-center">
                            <i class="fas fa-cloud-upload-alt me-2"></i>
                            <h5 class="mb-0">{{ __('Uploaded Documents') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-md-4 g-3">
                                @foreach ($inquiry->getMedia('inquiry-documents') as $media)
                                    <div class="col">
                                        <div class="border rounded p-3 bg-white text-center">
                                            <i class="fas fa-file-download text-info fa-3x mb-2"></i>
                                            <h6 class="mb-1">{{ $media->file_name }}</h6>
                                            <small class="text-muted d-block">{{ number_format($media->size / 1024, 2) }}
                                                KB</small>
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="btn btn-sm btn-primary mt-2">
                                                <i class="fas fa-eye me-1"></i> {{ __('View') }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Project Image -->
            @if ($inquiry->getFirstMedia('project-image'))
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white d-flex align-items-center">
                            <i class="fas fa-image me-2"></i>
                            <h5 class="mb-0">{{ __('Project Image') }}</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ $inquiry->getFirstMedia('project-image')->getUrl() }}" alt="Project Image"
                                class="img-fluid rounded shadow" style="max-height: 500px; object-fit: contain;">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Comments -->
            @if ($inquiry->comments->isNotEmpty())
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-gradient-info text-white d-flex align-items-center">
                            <i class="fas fa-comments me-2"></i>
                            <h5 class="mb-0">{{ __('Comments') }} ({{ $inquiry->comments->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @foreach ($inquiry->comments as $comment)
                                <div class="border-start border-info border-4 ps-3 py-2 mb-3 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-primary">
                                            <i class="fas fa-user-circle me-1"></i>
                                            {{ $comment->user?->name ?? __('Unknown User') }}
                                        </strong>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $comment->created_at->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                    <p class="mb-0">{{ $comment->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Timeline Footer -->
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <i class="fas fa-calendar-plus fa-2x text-primary mb-2"></i>
                                <h6 class="text-muted">{{ __('Created At') }}</h6>
                                <strong>{{ $inquiry->created_at->format('Y-m-d H:i') }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                                <h6 class="text-muted">{{ __('Updated At') }}</h6>
                                <strong>{{ $inquiry->updated_at->format('Y-m-d H:i') }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                                <h6 class="text-muted">{{ __('Created By') }}</h6>
                                <strong>{{ $inquiry->creator?->name ?? __('System') }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <i class="fas fa-hashtag fa-2x text-warning mb-2"></i>
                                <h6 class="text-muted">{{ __('Inquiry ID') }}</h6>
                                <strong>#{{ $inquiry->id }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scoreElement = document.querySelector('.score-display h2');
            if (scoreElement) {
                const finalScore = parseInt(scoreElement.textContent);
                let current = 0;
                const increment = Math.max(1, Math.ceil(finalScore / 50));
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= finalScore) {
                        current = finalScore;
                        clearInterval(timer);
                    }
                    scoreElement.textContent = current;
                }, 20);
            }
        });
    </script>
@endpush
