@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Client Types'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Client Types')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Client Types')
                <a href="{{ route('client-types.create') }}" type="button" class="btn btn-main font-hold fw-bold">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add New') }}
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="customer-type-table" filename="customer-type-table"
                            :excel-label="__('Export Excel')" :pdf-label="__('Export PDF')" :print-label="__('Print')" />

                        <table id="customer-type-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    @canany(['edit Client Types', 'delete Client Types'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customerTypes as $type)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->title }}</td>
                                        <td>{{ $type->branch->name ?? '-' }}</td>

                                        @canany(['edit Client Types', 'delete Client Types'])
                                            <td>
                                                @can('edit Client Types')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('client-types.edit', $type->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Client Types')
                                                    <form action="{{ route('client-types.destroy', $type->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this type?') }}');">
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
