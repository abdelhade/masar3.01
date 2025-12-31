@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.permissions')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Managers'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Managers')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Users')
                <a href="{{ route('users.create') }}" type="button" class="btn btn-main">
                    {{ __('Add New User') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="users-table" filename="users-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="users-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Permissions') }}</th>
                                    <th>{{ __('Branches') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    @canany(['edit Users', 'delete Users'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="text-center">
                                        <td class="font-hold fw-bold font-14 text-center"> {{ $loop->iteration }}
                                        </td>
                                        <td class="font-hold fw-bold font-14 text-center">{{ $user->name }}</td>
                                        <td class="font-hold fw-bold font-14 text-center">{{ $user->email }}</td>
                                        <td class="font-hold fw-bold font-14 text-center">
                                            <span class="badge bg-primary">{{ $user->permissions->count() }}</span>
                                        </td>
                                        <td class="font-hold fw-bold font-14 text-center">
                                            @foreach ($user->branches as $branch)
                                                <span class="badge bg-info text-dark">{{ $branch->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        @canany(['edit Users', 'delete Users'])
                                            <td>
                                                @can('edit Users')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('users.edit', $user->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete Users')
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No data available') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
