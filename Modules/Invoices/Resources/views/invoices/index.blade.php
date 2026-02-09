@extends('admin.dashboard')

@section('sidebar')
    @if (in_array($invoiceType, [10, 12, 14, 16, 22, 26]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($invoiceType, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($invoiceType, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __($invoiceTitle),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __($currentSection)],
            ['label' => __($invoiceTitle)],
        ],
    ])

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">{{ __($invoiceTitle) }}</h5>

                        @can('create ' . $invoiceTitle)
                            <a href="{{ url('/invoices/create?type=' . $invoiceType . '&q=' . md5($invoiceType)) }}"
                                class="btn btn-main">
                                <i class="las la-plus me-1"></i>
                                {{ __('Add') }} {{ __($invoiceTitle) }}
                            </a>
                        @endcan
                    </div>
                    <form method="GET" action="{{ route('invoices.index') }}" class="row g-3 align-items-end">
                        <input type="hidden" name="type" value="{{ $invoiceType }}">


                        <div class="col-md-3">
                            <label for="start_date" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="las la-filter me-1"></i>
                                {{ __('Filter') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    <x-table-export-actions table-id="invoices-table" filename="{{ Str::slug($invoiceTitle) }}"
                        excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                        print-label="{{ __('Print') }}" />

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="invoices-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-hold fw-bold font-14 text-center">#</th>
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Date') }}</th>
                                    @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                        <th class="font-hold fw-bold font-14 text-center">
                                            {{ __('Due Date') }}</th>
                                    @endif
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Operation Name') }}
                                    </th>
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Account') }}</th>
                                    <th class="font-hold fw-bold font-14 text-center">
                                        {{ $invoiceType == 21 ? __('Counter Store') : __('Counter Account') }}
                                    </th>
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Employee') }}</th>
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Financial Value') }}
                                    </th>
                                    @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                        <th class="font-hold fw-bold font-14 text-center">
                                            {{ in_array($invoiceType, [11, 13, 15, 17]) ? __('Paid to Supplier') : __('Paid by Customer') }}
                                        </th>
                                    @endif
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Net Operation') }}
                                    </th>
                                    @if (!in_array($invoiceType, [11, 13, 18, 19, 20, 21]))
                                        <th class="font-hold fw-bold font-14 text-center">
                                            {{ in_array($invoiceType, [11, 13, 15, 17]) ? __('Cost') : __('Profit') }}
                                        </th>
                                    @endif
                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Payment Status') }}
                                    </th>


                                    <th class="font-hold fw-bold font-14 text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ \Carbon\Carbon::parse($invoice->pro_date)->format('Y-m-d') }}
                                            </span>
                                        </td>
                                        @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ \Carbon\Carbon::parse($invoice->accural_date)->format('Y-m-d') }}
                                                </span>
                                            </td>
                                        @endif
                                        <td>{{ $invoice->type->ptext }}</td>
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->acc1Head->aname ?? '' }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->acc2Head->aname ?? '' }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->employee->aname ?? '' }}</span>
                                        </td>
                                        <td>{{ $invoice->pro_value }}</td>
                                        @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                            <td>{{ $invoice->paid_from_client }}</td>
                                        @endif
                                        <td>{{ $invoice->fat_net }}</td>
                                        @if (!in_array($invoiceType, [11, 13, 18, 19, 20, 21]))
                                            <td>{{ $invoice->profit }}</td>
                                        @endif
                                        <td class="text-center">
                                            @php
                                                $totalAmount = $invoice->pro_value;
                                                $paidAmount = $invoice->paid_from_client;
                                            @endphp

                                            @if ($paidAmount == 0)
                                                <span class="badge bg-danger">{{ __('Unpaid') }}</span>
                                            @elseif ($paidAmount >= $totalAmount)
                                                <span class="badge bg-success">{{ __('Paid') }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ __('Partial') }}</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">

                                                @if ($invoice->pro_type == 11)
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('edit.purchase.price.invoice.report', $invoice->id) }}"
                                                        title="{{ __('Edit Selling Price') }}">
                                                        <i class="las la-dollar-sign"></i>
                                                    </a>
                                                    <a class="btn btn-primary btn-icon-square-sm"
                                                        href="{{ route('invoices.barcode-report', $invoice->id) }}"
                                                        title="{{ __('Print Barcode') }}">
                                                        <i class="las la-barcode"></i>
                                                    </a>
                                                @endif

                                                @can('view ' . $titles[$invoice->pro_type])
                                                    <a class="btn btn-info btn-icon-square-sm"
                                                        href="{{ route('invoice.view', $invoice->id) }}"
                                                        title="{{ __('View') }}">
                                                        <i class="las la-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('edit ' . $titles[$invoice->pro_type])
                                                    <a class="btn btn-warning btn-icon-square-sm"
                                                        href="{{ route('invoices.edit', $invoice->id) }}"
                                                        title="{{ __('Edit') }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @if ($invoice->pro_type == 25)
                                                    <button type="button" class="btn btn-info btn-icon-square-sm"
                                                        title="{{ __('Manufacturing Details') }}"
                                                        onclick='Livewire.dispatch("openManufacturingModal", { items: {{ json_encode($invoice->operationItems->map(fn($item) => ['id' => $item->item_id, 'name' => $item->item->name ?? 'Unknown', 'qty' => $item->qty_in ?? $item->qty])->values()) }} })'>
                                                        <i class="fas fa-industry"></i>
                                                    </button>
                                                @endif

                                                @can('delete ' . $titles[$invoice->pro_type])
                                                    <form action="{{ route('invoices.destroy', $invoice->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this invoice?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No') }} {{ __($invoiceTitle) }}
                                                {{ __('found for this date range') }}
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
    <livewire:manufacturing::manufacturing-cost-modal />
@endsection
