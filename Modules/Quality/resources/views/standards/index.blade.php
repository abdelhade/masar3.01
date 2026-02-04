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
                        <h2 class="mb-0">
                            <i class="fas fa-ruler-combined me-2"></i>{{ __('Quality Standards') }}
                        </h2>
                    </div>
                    @can('create standards')
                        <div>
                            <a href="{{ route('quality.standards.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>{{ __('New Standard') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Total Standards') }}</h6>
                        <h3>{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Active Standards') }}</h6>
                        <h3 class="text-success">{{ $stats['active'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Inactive Standards') }}</h6>
                        <h3 class="text-danger">{{ $stats['inactive'] }}</h3>
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
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Standard Name') }}</th>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('Test Frequency') }}</th>
                                <th>{{ __('Acceptance Threshold') }}</th>
                                <th>{{ __('Status') }}</th>
                                @canany(['edit standards', 'delete standards', 'view standards'])
                                    <th>{{ __('Actions') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($standards as $standard)
                                <tr>
                                    <td><strong>{{ $standard->standard_code }}</strong></td>
                                    <td>{{ $standard->standard_name }}</td>
                                    <td>{{ $standard->item->name ?? '---' }}</td>
                                    <td>
                                        {{ match ($standard->test_frequency) {
                                            'per_batch' => __('Per Batch'),
                                            'daily' => __('Daily'),
                                            'weekly' => __('Weekly'),
                                            'monthly' => __('Monthly'),
                                            default => $standard->test_frequency,
                                        } }}
                                    </td>
                                    <td><strong>{{ $standard->acceptance_threshold }}%</strong></td>
                                    <td>
                                        @if ($standard->is_active)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Suspended') }}</span>
                                        @endif
                                    </td>
                                    @canany(['edit standards', 'delete standards', 'view standards'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view standards')
                                                    <a href="{{ route('quality.standards.show', $standard) }}"
                                                        class="btn btn-sm btn-info" title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('edit standards')
                                                    <a href="{{ route('quality.standards.edit', $standard) }}"
                                                        class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete standards')
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $standard->id }}"
                                                        title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $standard->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ __('Are you sure you want to delete Quality Standard') }}
                                                            "{{ $standard->standard_name }}"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                                {{ __('Cancel') }}
                                                            </button>
                                                            <form action="{{ route('quality.standards.destroy', $standard) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-danger">{{ __('Delete') }}</button>
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
                                    <td colspan="@canany(['edit standards', 'delete standards', 'view standards']) 7 @else 6 @endcanany"
                                        class="text-center py-4">
                                        {{ __('No Quality Standards Found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($standards->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $standards->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
