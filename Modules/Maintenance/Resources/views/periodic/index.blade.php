{{-- resources/views/maintenance/periodic/index.blade.php --}}
@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.service')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Periodic Maintenance'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Periodic Maintenance')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Periodic Maintenance')
                <a href="{{ route('periodic.maintenances.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                    {{ __('Add Periodic Maintenance Schedule') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="periodic-table" filename="periodic-maintenance"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="periodic-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Service Type') }}</th>
                                    <th>{{ __('Frequency') }}</th>
                                    <th>{{ __('Next Maintenance') }}</th>
                                    <th>{{ __('Last Maintenance') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @canany(['edit Periodic Maintenance', 'delete Periodic Maintenance'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schedules as $schedule)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $schedule->client_name }}
                                            <br>
                                            <small class="text-muted">{{ $schedule->client_phone }}</small>
                                        </td>
                                        <td>
                                            {{ $schedule->item_name }}
                                            <br>
                                            <small class="text-muted">{{ __('Item Number') }}:
                                                {{ $schedule->item_number }}</small>
                                        </td>
                                        <td>{{ $schedule->serviceType->name }}</td>
                                        <td>{{ $schedule->getFrequencyLabel() }}</td>
                                        <td>
                                            {{ $schedule->next_maintenance_date->format('Y-m-d') }}
                                            @if ($schedule->isOverdue())
                                                <br><span class="badge bg-danger">{{ __('Overdue') }}</span>
                                            @elseif($schedule->isMaintenanceDueSoon())
                                                <br><span class="badge bg-warning">{{ __('Due Soon') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->last_maintenance_date?->format('Y-m-d') ?? __('Not Done Yet') }}
                                        </td>
                                        <td>
                                            @if ($schedule->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        @canany(['edit Periodic Maintenance', 'delete Periodic Maintenance'])
                                            <td>
                                                @can('edit Periodic Maintenance')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('periodic.maintenances.edit', $schedule->id) }}"
                                                        title="{{ __('Edit') }}">
                                                        <i class="las la-edit"></i>
                                                    </a>

                                                    {{-- Toggle Active - الاسم الصحيح والـ method الصحيح --}}
                                                    <form action="{{ route('periodic.maintenances.toggle', $schedule->id) }}"
                                                        method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-warning btn-icon-square-sm"
                                                            title="{{ __('Toggle Status') }}">
                                                            <i class="las la-toggle-{{ $schedule->is_active ? 'on' : 'off' }}"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Create Maintenance - الاسم الصحيح --}}
                                                    <a class="btn btn-info btn-icon-square-sm"
                                                        href="{{ route('periodic.maintenances.create-maintenance', $schedule->id) }}"
                                                        title="{{ __('Create From Schedule') }}">
                                                        <i class="las la-plus"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Periodic Maintenance')
                                                    <form action="{{ route('periodic.maintenances.destroy', $schedule->id) }}"
                                                        method="POST" style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm"
                                                            title="{{ __('Delete') }}">
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
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No periodic maintenance schedules found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
