@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Resource Assignments') }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">{{ __('Assignments List') }}</h4>
                        <div>
                            <a href="{{ route('myresources.assignments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('Add New Assignment') }}
                            </a>
                            <a href="{{ route('myresources.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('Resources Management') }}
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Resource') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Daily Cost') }}</th>
                                    <th>{{ __('Assigned By') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->id }}</td>
                                        <td>
                                            <strong>{{ $assignment->resource->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $assignment->resource->code ?? '' }}</small>
                                        </td>
                                        <td>{{ $assignment->project->name ?? 'N/A' }}</td>
                                        <td>{{ $assignment->start_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                        <td>{{ $assignment->end_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                        <td>
                                            @if($assignment->status instanceof \Modules\MyResources\Enums\ResourceAssignmentStatus)
                                                <span class="badge bg-{{ $assignment->status->color() }}">
                                                    {{ $assignment->status->label() }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ $assignment->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->assignment_type instanceof \Modules\MyResources\Enums\AssignmentType)
                                                {{ $assignment->assignment_type->label() }}
                                            @else
                                                @php
                                                    $typeLabels = [
                                                        'current' => __('Current'),
                                                        'upcoming' => __('Upcoming'),
                                                        'past' => __('Past')
                                                    ];
                                                    $typeValue = $assignment->assignment_type;
                                                    $typeLabel = $typeLabels[$typeValue] ?? $typeValue;
                                                @endphp
                                                {{ $typeLabel }}
                                            @endif
                                        </td>
                                        <td>{{ $assignment->daily_cost ? number_format($assignment->daily_cost, 2) : 'N/A' }}</td>
                                        <td>{{ $assignment->assignedBy->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('myresources.assignments.edit', $assignment) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('myresources.assignments.destroy', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">{{ __('No assignments found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

