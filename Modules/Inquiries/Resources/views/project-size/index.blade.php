@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Project Sizes'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Project Sizes')]],
    ])

    <div class="row">
        <div class="col-lg-12">

            {{-- Add Button --}}
            @can('create Project Size')
                <a href="{{ route('project-size.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x:auto;">

                        <x-table-export-actions table-id="project-size-table" filename="project-size-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="project-size-table" class="table table-striped mb-0" style="min-width: 800px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>

                                    @canany(['Edit Project Size', 'Delete Project Size'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projectSizes as $size)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $size->name }}</td>
                                        <td>{{ $size->description ?? '-' }}</td>

                                        @canany(['Edit Project Size', 'Delete Project Size'])
                                            <td>
                                                @can('edit Project Size')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('project-size.edit', $size->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Project Size')
                                                    <form action="{{ route('project-size.destroy', $size->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}');">
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
                                            <div class="alert alert-info py-3 mb-0">
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
