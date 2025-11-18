@extends('admin.dashboard')
@section('sidebar')
    @include('components.sidebar.manufacturing')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Manufacturing Stages'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Manufacturing Stages')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            @can('create Manufacturing Stages')
                <a href="{{ route('manufacturing.stages.create') }}" type="button"
                    class="btn btn-primary font-family-cairo fw-bold">
                    {{ __('Add New Stage') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="manufacturing-stages-table" filename="manufacturing-stages"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="manufacturing-stages-table" class="table table-striped mb-0 text-center align-middle"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Stage Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Branch') }}</th>

                                    @canany(['view Manufacturing Stages', 'edit Manufacturing Stages', 'delete Manufacturing
                                        Stages'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stages as $stage)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $stage->name }}</td>
                                        <td>{{ Str::limit($stage->description, 50) }}</td>
                                        <td>{{ $stage->branch->name ?? '-' }}</td>

                                        @canany(['view Manufacturing Stages', 'edit Manufacturing Stages', 'delete
                                            Manufacturing Stages'])
                                            <td>
                                                <div role="group">
                                                    @can('edit Manufacturing Stages')
                                                        <a href="{{ route('manufacturing.stages.edit', $stage) }}"
                                                            class="btn btn-success btn-icon-square-sm" title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan

                                                    @can('delete Manufacturing Stages')
                                                        <form action="{{ route('manufacturing.stages.destroy', $stage) }}"
                                                            method="POST" style="display:inline-block;"
                                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this stage?') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-icon-square-sm"
                                                                title="{{ __('Delete') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No manufacturing stages available. Add a new stage.') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $stages->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
