@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Inquiries Roles'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Roles')]],
    ])

    <div class="row">
        <div class="col-lg-12">

            {{-- Add Button --}}
            @can('create Inquiries Roles')
                <a href="{{ route('inquiries-roles.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="inquiries-roles-table" filename="inquiries-roles-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="inquiries-roles-table" class="table table-striped mb-0" style="min-width: 1000px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>

                                    @canany(['Edit Inquiries Roles', 'Delete Inquiries Roles'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->description ?? '-' }}</td>

                                        @canany(['edit Inquiries Roles', 'delete Inquiries Roles'])
                                            <td>
                                                @can('edit Inquiries Roles')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('inquiries-roles.edit', $role->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Inquiries Roles')
                                                    <form action="{{ route('inquiries-roles.destroy', $role->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this role?') }}');">
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
                                        <td colspan="4" class="text-center">
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
