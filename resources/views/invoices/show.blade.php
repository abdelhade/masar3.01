@extends('admin.dashboard')

@section('sidebar')
    @if (in_array($invoice->pro_type, [10, 12, 14, 16, 22]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($invoice->pro_type, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($invoice->pro_type, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Invoice Details'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Invoices'), 'url' => route('invoices.index', ['type' => $invoice->pro_type])],
            ['label' => __('Invoice Details')],
        ],
    ])

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">
                            {{ __('Invoice Details') }}: #{{ $invoice->pro_id }}
                            - {{ $invoice->type->ptext ?? __('N/A') }}
                        </h4>
                        <div class="d-flex gap-2">
                            @php
                                $titles = [
                                    10 => 'Sales Invoice',
                                    11 => 'Purchase Invoice',
                                    12 => 'Sales Return',
                                    13 => 'Purchase Return',
                                    14 => 'Sales Order',
                                    15 => 'Purchase Order',
                                    16 => 'Quotation to Customer',
                                    17 => 'Quotation from Supplier',
                                    18 => 'Damaged Goods Invoice',
                                    19 => 'Dispatch Order',
                                    20 => 'Addition Order',
                                    21 => 'Store-to-Store Transfer',
                                    22 => 'Booking Order',
                                    24 => 'Service Invoice',
                                    25 => 'Requisition',
                                    26 => 'Pricing Agreement',
                                ];
                                $permissionName = 'edit '.($titles[$invoice->pro_type] ?? '');
                            @endphp
                            @can($permissionName)
                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                </a>
                            @endcan
                            <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-success" target="_blank">
                                <i class="fas fa-print"></i> {{ __('Print Invoice') }}
                            </a>
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> {{ __('Print Page') }}
                            </button>
                            <a href="{{ route('invoices.index', ['type' => $invoice->pro_type]) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card printable-content">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> {{ __('Invoice Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Invoice Number') }}:</label>
                                <div class="form-control-static">{{ $invoice->pro_id }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Invoice Type') }}:</label>
                                <div class="form-control-static">
                                    {{ $invoice->type->ptext ?? __('N/A') }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Date') }}:</label>
                                <div class="form-control-static">{{ $invoice->pro_date ? \Carbon\Carbon::parse($invoice->pro_date)->format('Y-m-d') : __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Serial Number') }}:</label>
                                <div class="form-control-static">{{ $invoice->pro_serial ?? __('N/A') }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Customer/Supplier') }}:</label>
                                <div class="form-control-static">{{ $invoice->acc1Head->aname ?? __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Store/Warehouse') }}:</label>
                                <div class="form-control-static">{{ $invoice->acc2Head->aname ?? __('N/A') }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Total Amount') }}:</label>
                                <div class="form-control-static">
                                    <h4 class="mb-0">{{ number_format($invoice->fat_net ?? $invoice->pro_value ?? 0, 2) }}</h4>
                                </div>
                            </div>

                            @if($invoice->employee)
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Employee') }}:</label>
                                <div class="form-control-static">{{ $invoice->employee->aname ?? __('N/A') }}</div>
                            </div>
                            @endif
                        </div>

                        @if($invoice->operationItems->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">{{ __('Invoice Items') }}:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Item') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Price') }}</th>
                                                <th>{{ __('Discount') }}</th>
                                                <th>{{ __('Total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoice->operationItems as $item)
                                            <tr>
                                                <td>{{ $item->item->name ?? __('N/A') }}</td>
                                                <td>{{ number_format($item->qty_in ?? $item->qty_out ?? 0, 2) }}</td>
                                                <td>{{ number_format($item->item_price ?? 0, 2) }}</td>
                                                <td>{{ number_format($item->item_discount ?? 0, 2) }}</td>
                                                <td>{{ number_format($item->detail_value ?? 0, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($invoice->info)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Notes') }}:</label>
                                <div class="form-control-static">{{ $invoice->info }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .form-control-static {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            min-height: 2.5rem;
            display: flex;
            align-items: center;
        }

        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; }
            .card-header { background: #f1f1f1 !important; color: #000 !important; }
            body { font-size: 12px; }
            .form-control-static { background: #fff !important; border: 1px solid #000 !important; }
            .table { font-size: 10px; }
        }
    </style>
    @endpush
@endsection

