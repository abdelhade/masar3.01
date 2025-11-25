@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Pricing Statuses'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Pricing Statuses')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Pricing Statuses')
                <a href="{{ route('pricing-statuses.create') }}" type="button" class="btn btn-main font-family-cairo fw-bold">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add New') }}
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="pricing-statuses-table" filename="pricing-statuses-table"
                            excel-label="Export Excel" pdf-label="Export PDF" print-label="Print" />

                        <table id="pricing-statuses-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Color') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @canany(['edit Pricing Statuses', 'delete Pricing Statuses'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pricingStatuses as $status)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ __($status->name) }}</td>
                                        <td>{{ $status->description ?: '-' }}</td>
                                        <td>
                                            <span class="badge"
                                                style="background-color: {{ $status->color }}; color: #fff;">
                                                {{ $status->color }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($status->is_active)
                                                <span class="badge bg-success">
                                                    <i class="las la-check-circle"></i> {{ __('Active') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="las la-times-circle"></i> {{ __('Inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        @canany(['edit Pricing Statuses', 'delete Pricing Statuses'])
                                            <td>
                                                @can('edit Pricing Statuses')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('pricing-statuses.edit', $status->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Pricing Statuses')
                                                    <form action="{{ route('pricing-statuses.destroy', $status->id) }}"
                                                        method="POST" style="display:inline-block;"
                                                        onsubmit="return confirm('Are you sure you want to delete this pricing status?');">
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
                                        <td colspan="7" class="text-center">
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
