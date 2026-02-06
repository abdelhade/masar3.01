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
            <h3 class="fw-bold text-primary mb-0">
                <i class="fa-solid fa-diagram-project me-2"></i> {{ __('general.project_types') }}
            </h3>
            @can('create progress-project-types')
                <a href="{{ route('progress.project_types.create') }}" class="btn btn-success rounded-pill shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> {{ __('general.add_project_type') }}
                </a>
            @endcan
        </div>

        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="{{ __('general.close') }}"></button>
            </div>
        @endif

        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">#</th>
                            <th>{{ __('general.name') }}</th>
                            @canany(['edit progress-project-types', 'delete progress-project-types'])
                                <th class="text-center" style="width: 180px">{{ __('general.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $type->name }}</td>
                                @canany(['edit progress-project-types', 'delete progress-project-types'])
                                    <td class="text-center">
                                        @can('edit progress-project-types')
                                        <a href="{{ route('progress.project_types.edit', $type->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                            <i class="fa-solid fa-pen-to-square"></i> {{ __('general.edit') }}
                                        </a>
                                        @endcan
                                        @can('delete progress-project-types')
                                        <form action="{{ route('progress.project_types.destroy', $type->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('{{ __('general.confirm_delete') }}')"
                                                class="btn btn-sm btn-outline-danger rounded-pill">
                                                <i class="fa-solid fa-trash"></i> {{ __('general.delete') }}
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                    {{ __('general.no_project_types') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
