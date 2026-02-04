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
                        <i class="fas fa-clipboard-check me-2"></i>
                        {{ __("Quality Inspections") }}
                    </h2>
                </div>
                @can('create inspections')
                <div>
                    <a href="{{ route('quality.inspections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>{{ __("New Inspection") }}
                    </a>
                </div>
                @endcan

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __("Inspection Number") }}</th>
                            <th>{{ __("Item") }}</th>
                            <th>{{ __("Type") }}</th>
                            <th>{{ __("Quantity") }}</th>
                            <th>{{ __("Result") }}</th>
                            <th>{{ __("Pass Percentage") }}</th>
                            <th>{{ __("Date") }}</th>
                            @canany(['edit inspections', 'delete inspections' , 'view inspections'])
                            <th>{{ __("Actions") }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inspections as $inspection)
                        <tr>
                            <td>
                                <a href="{{ route('quality.inspections.show', $inspection) }}">
                                    {{ $inspection->inspection_number }}
                                </a>
                            </td>
                            <td>{{ $inspection->item->name ?? '---' }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ match($inspection->inspection_type) {
                                        'receiving' => __('Receiving'),
                                        'in_process' => __('In Process'),
                                        'final' => __('Final'),
                                        'random' => __('Random'),
                                        'customer_complaint' => __('Customer Complaint'),
                                        default => $inspection->inspection_type
                                    } }}
                                </span>
                            </td>
                            <td>{{ number_format($inspection->quantity_inspected, 2) }}</td>
                            <td>
                                @if($inspection->result == 'pass')
                                    <span class="badge bg-success">{{ __('Pass') }}</span>
                                @elseif($inspection->result == 'fail')
                                    <span class="badge bg-danger">{{ __('Fail') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('Conditional') }}</span>
                                @endif
                            </td>
                            <td>
                                <strong class="{{ $inspection->pass_percentage >= 95 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($inspection->pass_percentage, 1) }}%
                                </strong>
                            </td>
                            <td>{{ $inspection->inspection_date->format('Y-m-d') }}</td>
                            @canany(['edit inspections', 'delete inspections' , 'view inspections'])
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view inspections')
                                    <a href="{{ route('quality.inspections.show', $inspection) }}"
                                       class="btn btn-sm btn-info" title="{{ __("View") }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit inspections')
                                    <a href="{{ route('quality.inspections.edit', $inspection) }}"
                                       class="btn btn-sm btn-warning" title="{{ __("Edit") }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete inspections')
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $inspection->id }}"
                                            title="{{ __("Delete") }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $inspection->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                {{ __('Are you sure you want to delete inspection number') }} {{ $inspection->inspection_number }}?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                                                <form action="{{ route('quality.inspections.destroy', $inspection) }}" method="POST" class="d-inline">
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
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">{{ __('No inspections') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inspections->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $inspections->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

