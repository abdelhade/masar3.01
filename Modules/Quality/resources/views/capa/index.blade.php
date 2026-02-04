@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0"><i class="fas fa-tools me-2"></i>{{ __("CAPA") }}</h2>
                    </div>
                    <div>
                        @can('create capa')
                            <a href="{{ route('quality.capa.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>{{ __("New CAPA") }}
                            </a>
                        @endcan

                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Total Actions") }}</h6>
                        <h3>{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("In Progress") }}</h6>
                        <h3 class="text-warning">{{ $stats['in_progress'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Completed") }}</h6>
                        <h3 class="text-success">{{ $stats['completed'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __("Overdue") }}</h6>
                        <h3 class="text-danger">{{ $stats['overdue'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __("CAPA Number") }}</th>
                                <th>{{ __("Type") }}</th>
                                <th>{{ __("Assigned To") }}</th>
                                <th>{{ __("Planned Start Date") }}</th>
                                <th>{{ __("Planned Completion Date") }}</th>
                                <th>{{ __("Completion Percentage") }}</th>
                                <th>{{ __("Status") }}</th>
                                @canany(['edit capa', 'delete capa', 'view capa'])
                                    <th>{{ __("Actions") }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($capas as $capa)
                                <tr class="{{ $capa->isOverdue() ? 'table-danger' : '' }}">
                                    <td><a href="{{ route('quality.capa.show', $capa) }}">{{ $capa->capa_number }}</a></td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $capa->action_type == 'corrective' ? 'warning' : 'info' }}">
                                            {{ $capa->action_type == 'corrective' ? __("Corrective") : __("Preventive") }}
                                        </span>
                                    </td>
                                    <td>{{ $capa->responsiblePerson->name ?? '---' }}</td>
                                    <td>{{ $capa->planned_start_date->format('Y-m-d') }}</td>
                                    <td>{{ $capa->planned_completion_date->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $capa->completion_percentage >= 100 ? 'success' : 'primary' }}"
                                                style="width: {{ $capa->completion_percentage }}%">
                                                {{ $capa->completion_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ match ($capa->status) {
                                                'completed' => 'success',
                                                'in_progress' => 'warning',
                                                'verified' => 'info',
                                                default => 'secondary',
                                            } }}">
                                            {{ $capa->status }}
                                        </span>
                                    </td>
                                    @canany(['edit capa', 'delete capa', 'view capa'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view capa')
                                                    <a href="{{ route('quality.capa.show', $capa) }}" class="btn btn-sm btn-info"
                                                        title="{{ __("View") }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('edit capa')
                                                    <a href="{{ route('quality.capa.edit', $capa) }}"
                                                        class="btn btn-sm btn-warning" title="{{ __("Edit") }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete capa')
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $capa->id }}" title="{{ __("Delete") }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $capa->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ __("Are you sure you want to delete CAPA") }} "{{ $capa->capa_number }}"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                                                            <form action="{{ route('quality.capa.destroy', $capa) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">{{ __("Delete") }}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">{{ __("No CAPAs") }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($capas->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $capas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
