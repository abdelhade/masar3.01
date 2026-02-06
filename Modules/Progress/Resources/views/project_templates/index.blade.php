@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
@endsection
@section('content')
    <div class="container py-4">

        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary mb-0">{{ __('general.project_templates') }}</h4>
            @can('create progress-project-templates')
                <a href="{{ route('progress.project-templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> {{ __('general.new_template') }}
                </a>
            @endcan
        </div>

        
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('general.template_type') }}</th>
                                <th>{{ __('general.items_count') }}</th>
                                <th>{{ __('general.created_at') }}</th>
                                @canany(['edit progress-project-templates', 'delete progress-project-templates', 'project-templates-view'])
                                    <th class="text-end">{{ __('general.actions') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $tpl)
                                <tr>
                                    <td>{{ $tpl->id }}</td>
                                    <td class="fw-semibold">{{ $tpl->name }}</td>
                                    <td><span class="badge bg-info">{{ $tpl->items_count }}</span></td>
                                    <td>{{ $tpl->created_at->format('Y-m-d') }}</td>
                                    @canany(['edit progress-project-templates', 'delete progress-project-templates',
                                        'project-templates-view'])
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                @can('view progress-project-templates')
                                                    <a href="{{ route('progress.project-templates.show', $tpl) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="{{ __('general.view') }}">
                                                        <i class="fa-solid fa-eye"></i>


                                                    </a>
                                                @endcan
                                                @can('edit progress-project-templates')
                                                    <a href="{{ route('progress.project-templates.edit', $tpl) }}"
                                                        class="btn btn-sm btn-outline-success" title="{{ __('general.edit') }}">
                                                        <i class="fa-solid fa-pen-to-square"></i>

                                                    </a>
                                                @endcan
                                                @can('delete progress-project-templates')
                                                    <form action="{{ route('progress.project-templates.destroy', $tpl) }}" method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('{{ __('general.confirm_delete') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            title="{{ __('general.delete') }}">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-folder-x me-2"></i>{{ __('general.no_templates') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="mt-3">
            {{ $templates->links() }}
        </div>
    </div>
@endsection
