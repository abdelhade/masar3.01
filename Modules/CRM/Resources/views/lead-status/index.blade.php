@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Lead Statuses'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Lead Statuses')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Lead Statuses')
                <a href="{{ route('lead-status.create') }}" type="button" class="btn btn-main font-family-cairo fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="lead-status-table" filename="lead-status-table" :excel-label="__('Export Excel')"
                            :pdf-label="__('Export PDF')" :print-label="__('Print')" />

                        <table id="lead-status-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Color') }}</th>
                                    <th>{{ __('Order') }}</th>
                                    @canany(['delete Lead Statuses', 'edit Lead Statuses'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leadStatus as $chance)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $chance->name }}</td>

                                        <td>
                                            <span
                                                style="display:inline-block; width: 20px; height: 20px;
                                                background-color: {{ $chance->color }};
                                                 border: 1px solid #ccc; border-radius: 4px;"></span>
                                            <span>{{ $chance->color }}</span>
                                        </td>
                                        <td>{{ $chance->order_column }}</td>
                                        @canany(['edit Lead Statuses', 'delete Lead Statuses'])
                                            <td>
                                                @can('edit Lead Statuses')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('lead-status.edit', $chance->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete Lead Statuses')
                                                    <form action="{{ route('lead-status.destroy', $chance->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this status?') }}');">
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
                                                {{ __('No data added yet') }}
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
