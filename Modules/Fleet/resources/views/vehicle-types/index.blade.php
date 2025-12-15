@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Vehicle Types'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('fleet::Vehicle Types')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Vehicle Types')
                <a href="{{ route('fleet.vehicle-types.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                    {{ __('fleet::Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="vehicle-types-table" filename="vehicle-types"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="vehicle-types-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('fleet::Name') }}</th>
                                    <th>{{ __('fleet::Description') }}</th>
                                    <th>{{ __('fleet::Status') }}</th>
                                    @canany(['edit Vehicle Types', 'delete Vehicle Types'])
                                        <th>{{ __('fleet::Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($types as $type)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td>{{ $type->description ?? '-' }}</td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge bg-success">{{ __('fleet::Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('fleet::Inactive') }}</span>
                                            @endif
                                        </td>

                                        @canany(['edit Vehicle Types', 'delete Vehicle Types'])
                                            <td>
                                                @can('edit Vehicle Types')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('fleet.vehicle-types.edit', $type->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Vehicle Types')
                                                    <form action="{{ route('fleet.vehicle-types.destroy', $type->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('fleet::Are you sure you want to delete this item?') }}');">
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
                                        <td colspan="5" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('fleet::No data available') }}
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

