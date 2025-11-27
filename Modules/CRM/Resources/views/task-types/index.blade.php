@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Task Types'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Task Types')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Task Types')
                <a href="{{ route('tasks.types.create') }}" class="btn btn-main font-hold fw-bold">
                    {{ __('Add New') }} <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">

                        <x-table-export-actions table-id="work-item-types-table" filename="work-item-types" :excel-label="__('Export Excel')"
                            :pdf-label="__('Export PDF')" :print-label="__('Print')" />

                        <table id="work-item-types-table" class="table table-striped text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Title') }}</th>
                                    @canany(['edit Task Types', 'delete Task Types'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($taskType as $type)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->title }}</td>
                                        @canany(['edit Task Types', 'delete Task Types'])
                                            <td>
                                                @can('edit Task Types')
                                                    <a href="{{ route('tasks.types.edit', $type->id) }}"
                                                        class="btn btn-success btn-icon-square-sm">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Task Types')
                                                    <form action="{{ route('tasks.types.destroy', $type->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this task type?') }}');">
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
                                        <td colspan="3" class="text-center">
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
