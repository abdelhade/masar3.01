@extends('admin.dashboard')

@section('title', __('projects.list'))

@section('content')
    <div class="m-2 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('projects.list') }}</h5>
        {{-- @can('projects-create') --}}
        <a href="{{ route('progress.projcet.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> {{ __('projects.new') }}
        </a>
        {{-- @endcan --}}
    </div>

    <div class="card border-0 rounded-0">
        <div class="card-header border-0">
            <div class="table-responsive" style="overflow-x: auto;">
                <table id="myTable" class="table table-striped mb-0 w-100" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('projects.name') }}</th>
                            <th>{{ __('projects.client') }}</th>
                            <th>{{ __('general.status') }}</th>
                            <th>{{ __('general.type_of_project') }}</th>
                            <th>{{ __('projects.start_date') }}</th>
                            <th>{{ __('projects.end_date') }}</th>
                            {{-- @canany(['projects-view', 'projects-edit', 'projects-progress', 'projects-delete']) --}}
                            <th>{{ __('projects.actions') }}</th>
                            {{-- @endcanany --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->client->name }}</td>
                                <td>
                                    @switch($project->status)
                                        @case('in_progress')
                                            <span class="badge bg-success">{{ __('general.active') }}</span>
                                        @break

                                        @case('completed')
                                            <span class="badge bg-secondary">{{ __('general.completed') }}</span>
                                        @break

                                        @case('pending')
                                            <span class="badge bg-warning">{{ __('general.pending') }}</span>
                                        @break

                                        @case('cancelled')
                                            <span class="badge bg-danger">{{ __('general.cancelled') }}</span>
                                        @break
                                    @endswitch
                                </td>

                                <td>
                                    {{ $project->type ? $project->type->name : __('general.not_specified') }}
                                </td>
                                <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '' }}
                                </td>
                                <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '' }}
                                </td>
                                {{-- @canany(abilities: ['projects-view', 'projects-edit', 'projects-progress', 'projects-delete']) --}}
                                <td>
                                    {{-- @can('projects-view') --}}
                                    <a href="{{ route('progress.projcet.show', $project) }}"
                                        class="btn btn-icon-square-sm btn-primary" style='font-size:10px;'
                                        title="{{ __('general.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- @endcan
                                        @can('projects-edit') --}}
                                    <a href="{{ route('progress.projcet.edit', $project) }}"
                                        class="btn btn-icon-square-sm btn-success" style='font-size:10px;'
                                        title="{{ __('general.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- @endcan
                                        @can('projects-progress') --}}
                                    <a href="{{ route('projects.progress/state', $project) }}"
                                        class="btn btn-icon-square-sm btn-info" style='font-size:10px;'
                                        title="{{ __('general.progress_report') }}">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    {{-- @endcan
                                        @can('projects-delete') --}}
                                    <form action="{{ route('progress.projcet.destroy', $project) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon-square-sm "
                                            style='font-size:10px;'
                                            onclick="return confirm('{{ __('projects.confirm_delete') }}')"
                                            title="{{ __('general.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    {{-- @endcan --}}
                                </td>
                                {{-- @endcanany --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
