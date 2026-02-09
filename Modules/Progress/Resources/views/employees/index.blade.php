@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
@endsection
@section('title', __('employees.management'))

@section('content')
    <div class="  m-2 text-center d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('employees.list') }}</h5>
        @can('create progress-employees')
            <a href="{{ route('progress.employees.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> {{ __('employees.new') }}
            </a>
        @endcan
    </div>
    <div class="card ">

        <div class="card border-0 rounded-0">

            <div class="table-responsive" style="overflow-x: auto;">
                <table id="myTable" class="table table-striped mb-0 w-100" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('employees.name') }}</th>
                            <th>{{ __('employees.position') }}</th>
                            <th>{{ __('employees.phone') }}</th>
                            <th>{{ __('employees.email') }}</th>
                            @canany(['edit progress-employees', 'delete progress-employees'])
                                <th>{{ __('employees.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->phone }}</td>
                                <td>{{ $employee->email }}</td>
                                @canany(['edit progress-employees', 'delete progress-employees'])
                                    <td>
                                        @can('edit progress-employees')
                                            <a href="{{ route('progress.employees.edit', $employee) }}" style="10px"
                                                class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('edit progress-employees')
                                            @if ($employee->user)
                                                <a href="{{ route('progress.employees.permissions', $employee->user->id) }}"
                                                    class="btn btn-sm btn-dark">
                                                    <i class="fas fa-lock"></i>
                                                </a>
                                            @endif
                                        @endcan
                                        @can('delete progress-employees')
                                            <form action="{{ route('progress.employees.destroy', $employee) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('{{ __('employees.confirm_delete') }}')">
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
@endsection
