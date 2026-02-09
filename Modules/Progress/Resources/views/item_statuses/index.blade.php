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
                <i class="fa-solid fa-tags me-2"></i> {{ __('general.item_statuses') }}
            </h3>
            @can('create progress-item-statuses')
                <a href="{{ route('progress.item-statuses.create') }}" class="btn btn-success rounded-pill shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> {{ __('general.add_item_status') }}
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

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
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
                            <th>{{ __('general.color') }}</th>
                            <th>{{ __('general.icon') }}</th>
                            <th class="text-center">{{ __('general.order') }}</th>
                            <th class="text-center">{{ __('general.status') }}</th>
                            @canany(['edit progress-item-statuses', 'delete progress-item-statuses'])
                                <th class="text-center" style="width: 180px">{{ __('general.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statuses as $status)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $status->name }}</strong>
                                    @if($status->description)
                                        <br><small class="text-muted">{{ Str::limit($status->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($status->color)
                                        <span class="badge" style="background-color: {{ $status->color }}">
                                            {{ $status->color }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($status->icon)
                                        <i class="{{ $status->icon }}"></i> {{ $status->icon }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $status->order }}</td>
                                <td class="text-center">
                                    @if($status->is_active)
                                        <span class="badge bg-success">{{ __('general.active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('general.inactive') }}</span>
                                    @endif
                                </td>
                                @canany(['edit progress-item-statuses', 'delete progress-item-statuses'])
                                    <td class="text-center">
                                        @can('edit progress-item-statuses')
                                        <a href="{{ route('progress.item-statuses.edit', $status->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                            <i class="fa-solid fa-pen-to-square"></i> {{ __('general.edit') }}
                                        </a>
                                        @endcan
                                        @can('delete progress-item-statuses')
                                        <form action="{{ route('progress.item-statuses.destroy', $status->id) }}" method="POST"
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                    {{ __('general.no_item_statuses') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

