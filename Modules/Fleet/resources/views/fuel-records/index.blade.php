@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.fleet')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('fleet::Fuel Records'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('fleet::Fuel Records')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Fuel Records')
                <a href="{{ route('fleet.fuel-records.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                    {{ __('fleet::Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="fuel-records-table" filename="fuel-records"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />
                        <table id="fuel-records-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('fleet::Vehicle') }}</th>
                                    <th>{{ __('fleet::Fuel Date') }}</th>
                                    <th>{{ __('fleet::Fuel Type') }}</th>
                                    <th>{{ __('fleet::Quantity') }}</th>
                                    <th>{{ __('fleet::Cost') }}</th>
                                    <th>{{ __('fleet::Mileage at Fueling') }}</th>
                                    <th>{{ __('fleet::Station Name') }}</th>
                                    @canany(['edit Fuel Records', 'delete Fuel Records'])
                                        <th>{{ __('fleet::Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fuelRecords as $record)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $record->vehicle->name ?? '-' }} ({{ $record->vehicle->plate_number ?? '-' }})</td>
                                        <td>{{ $record->fuel_date->format('Y-m-d') }}</td>
                                        <td>{{ $record->fuel_type->label() }}</td>
                                        <td>{{ number_format($record->quantity, 2) }} L</td>
                                        <td>{{ number_format($record->cost, 2) }} {{ __('SAR') }}</td>
                                        <td>{{ number_format($record->mileage_at_fueling, 2) }} km</td>
                                        <td>{{ $record->station_name ?? '-' }}</td>
                                        @canany(['edit Fuel Records', 'delete Fuel Records'])
                                            <td>
                                                @can('edit Fuel Records')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('fleet.fuel-records.edit', $record->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete Fuel Records')
                                                    <form action="{{ route('fleet.fuel-records.destroy', $record->id) }}" method="POST"
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
                                        <td colspan="9" class="text-center">
                                            <div class="alert alert-info py-3 mb-0">{{ __('fleet::No data available') }}</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $fuelRecords->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

