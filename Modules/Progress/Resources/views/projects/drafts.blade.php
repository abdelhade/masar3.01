@extends('progress::layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('general.drafts') }}</li>
@endsection

@section('title', __('general.project_drafts'))

@section('content')
    <div class="m-2 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-alt me-2"></i>
            {{ __('general.project_drafts') }}
        </h5>
        <div>
            <a href="{{ route('progress.projects.index') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-project-diagram me-1"></i> {{ __('general.all_projects') }}
            </a>
            @can('create progress-projects')
                <a href="{{ route('progress.projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> {{ __('projects.new') }}
                </a>
            @endcan
        </div>
    </div>

    @if($drafts->isEmpty())
        <div class="card border-0 rounded-3">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">{{ __('general.no_drafts_found') }}</h5>
                <p class="text-muted">{{ __('general.no_drafts_description') }}</p>
                @can('create progress-projects')
                    <a href="{{ route('progress.projects.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> {{ __('general.create_new_project') }}
                    </a>
                @endcan
            </div>
        </div>
    @else
        <div class="card border-0 rounded-0">
            <div class="card-header border-0">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="draftsTable" class="table table-striped mb-0 w-100" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('projects.name') }}</th>
                                <th>{{ __('projects.client') }}</th>
                                <th>{{ __('general.status') }}</th>
                                <th>{{ __('general.type_of_project') }}</th>
                                <th>{{ __('general.created_at') }}</th>
                                <th>{{ __('general.completion') }}</th>
                                @canany(['edit progress-projects', 'delete progress-projects'])
                                    <th>{{ __('projects.actions') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($drafts as $draft)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-warning me-2"></i>
                                            <strong>{{ $draft->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if($draft->client)
                                            {{ $draft->client->cname }}
                                        @else
                                            <span class="text-muted">{{ __('general.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-pencil-alt me-1"></i>
                                            {{ __('general.draft') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($draft->projectType)
                                            {{ $draft->projectType->name }}
                                        @else
                                            <span class="text-muted">{{ __('general.not_set') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $draft->created_at->format('d-m-Y H:i') }}
                                    </td>
                                    <td>
                                        @php
                                            $completion = 0;
                                            $total = 8; // عدد الحقول المطلوبة
                                            $filled = 0;

                                            if($draft->name) $filled++;
                                            if($draft->client_id) $filled++;
                                            if($draft->start_date) $filled++;
                                            if($draft->end_date) $filled++;
                                            if($draft->project_type_id) $filled++;
                                            if($draft->working_zone) $filled++;
                                            if($draft->items->count() > 0) $filled++;
                                            if($draft->employees->count() > 0) $filled++;

                                            $completion = round(($filled / $total) * 100);
                                        @endphp

                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <div class="progress-bar {{ $completion >= 100 ? 'bg-success' : 'bg-warning' }}"
                                                     role="progressbar"
                                                     style="width: {{ $completion }}%;">
                                                    <strong>{{ $completion }}%</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @canany(['edit progress-projects', 'delete progress-projects'])
                                        <td>
                                            @can('edit progress-projects')
                                                
                                                <a href="{{ route('progress.projects.edit', $draft) }}"
                                                   class="btn btn-icon-square-sm btn-warning"
                                                   style='font-size:10px;'
                                                   title="{{ __('general.continue_editing') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                
                                                @if($completion >= 100)
                                                    <form action="{{ route('progress.projects.publish', $draft) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ __('general.confirm_publish_draft') }}')">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-icon-square-sm btn-success"
                                                                style='font-size:10px;'
                                                                title="{{ __('general.publish_project') }}">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-icon-square-sm btn-secondary"
                                                            style='font-size:10px;'
                                                            disabled
                                                            title="{{ __('general.complete_all_fields_to_publish') }}">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                @endif
                                            @endcan

                                            @can('delete progress-projects')
                                                <form action="{{ route('progress.projects.destroy', $draft) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger btn-icon-square-sm"
                                                            style='font-size:10px;'
                                                            onclick="return confirm('{{ __('general.confirm_delete_draft') }}')"
                                                            title="{{ __('general.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <style>
        .progress {
            background-color: #e9ecef;
        }

        .progress-bar {
            font-size: 0.75rem;
        }

        .btn-icon-square-sm {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#draftsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                },
                order: [[5, 'desc']], // ترتيب حسب تاريخ الإنشاء
                pageLength: 25
            });
        });
    </script>
    @endpush
@endsection
