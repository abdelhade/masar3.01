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
                                <label class="form-label fw-bold">{{ __('Subtotal') }}:</label>
                                <div class="form-control-static">{{ number_format($invoice->fat_val ?? 0, 2) }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Discount') }}:</label>
                                <div class="form-control-static">{{ number_format($invoice->fat_dis ?? 0, 2) }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Additional') }}:</label>
                                <div class="form-control-static">{{ number_format($invoice->fat_add ?? 0, 2) }}</div>
                            </div>

                            @if(setting('enable_vat_fields') == '1' && setting('vat_level') != 'disabled')
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('VAT') }} ({{ setting('default_vat_percentage', 0) }}%):</label>
                                <div class="form-control-static">{{ number_format($invoice->vat_value ?? 0, 2) }}</div>
                            </div>
                            @endif
                        </div>

                        @if(setting('enable_vat_fields') == '1' && setting('withholding_tax_level') != 'disabled')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Withholding Tax') }} ({{ setting('default_withholding_tax_percentage', 0) }}%):</label>
                                <div class="form-control-static">{{ number_format($invoice->withholding_tax_value ?? 0, 2) }}</div>
                            </div>
                        </div>
                        @endif

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
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Item') }}</th>
                                                <th>{{ __('Code') }}</th>
                                                <th>{{ __('Unit') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Price') }}</th>
                                                <th>{{ __('Discount %') }}</th>
                                                <th>{{ __('Discount Value') }}</th>
                                                <th>{{ __('Total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $subtotal = 0;
                                            @endphp
                                            @foreach($invoice->operationItems as $index => $item)
                                            @php
                                                $quantity = $item->qty_in ?: $item->qty_out;
                                                $price = $item->item_price ?? 0;
                                                $discountPre = $item->item_discount_pre ?? 0;
                                                $discountValue = $item->item_discount ?? 0;
                                                $itemTotal = $item->detail_value ?? 0;
                                                $subtotal += $itemTotal;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->item->name ?? __('N/A') }}</td>
                                                <td>{{ $item->item->code ?? '-' }}</td>
                                                <td>{{ $item->unit->name ?? '-' }}</td>
                                                <td>{{ number_format($quantity, 2) }}</td>
                                                <td>{{ number_format($price, 2) }}</td>
                                                <td>{{ number_format($discountPre, 2) }}%</td>
                                                <td>{{ number_format($discountValue, 2) }}</td>
                                                <td>{{ number_format($itemTotal, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="8" class="text-end fw-bold">{{ __('Subtotal') }}:</td>
                                                <td class="fw-bold">{{ number_format($subtotal, 2) }}</td>
                                            </tr>
                                            @if($invoice->discount_percentage > 0 || $invoice->discount_value > 0)
                                            <tr>
                                                <td colspan="8" class="text-end">
                                                    {{ __('Discount') }}
                                                    @if($invoice->discount_percentage > 0)
                                                        ({{ number_format($invoice->discount_percentage, 2) }}%)
                                                    @endif:
                                                </td>
                                                <td class="text-danger">- {{ number_format($invoice->discount_value ?? 0, 2) }}</td>
                                            </tr>
                                            @endif
                                            @if($invoice->additional_percentage > 0 || $invoice->additional_value > 0)
                                            <tr>
                                                <td colspan="8" class="text-end">
                                                    {{ __('Additional') }}
                                                    @if($invoice->additional_percentage > 0)
                                                        ({{ number_format($invoice->additional_percentage, 2) }}%)
                                                    @endif:
                                                </td>
                                                <td class="text-success">+ {{ number_format($invoice->additional_value ?? 0, 2) }}</td>
                                            </tr>
                                            @endif
                                            @php
                                                $afterDiscount = $subtotal - ($invoice->discount_value ?? 0);
                                                $afterAdditional = $afterDiscount + ($invoice->additional_value ?? 0);
                                            @endphp
                                            @if(isset($invoice->vat_percentage) && $invoice->vat_percentage > 0)
                                            <tr>
                                                <td colspan="8" class="text-end">
                                                    {{ __('VAT') }} ({{ number_format($invoice->vat_percentage, 2) }}%):
                                                </td>
                                                <td>+ {{ number_format(($afterAdditional * $invoice->vat_percentage) / 100, 2) }}</td>
                                            </tr>
                                            @php
                                                $afterAdditional += ($afterAdditional * $invoice->vat_percentage) / 100;
                                            @endphp
                                            @endif
                                            @if(isset($invoice->withholding_tax_percentage) && $invoice->withholding_tax_percentage > 0)
                                            <tr>
                                                <td colspan="8" class="text-end">
                                                    {{ __('Withholding Tax') }} ({{ number_format($invoice->withholding_tax_percentage, 2) }}%):
                                                </td>
                                                <td class="text-danger">- {{ number_format(($afterAdditional * $invoice->withholding_tax_percentage) / 100, 2) }}</td>
                                            </tr>
                                            @php
                                                $afterAdditional -= ($afterAdditional * $invoice->withholding_tax_percentage) / 100;
                                            @endphp
                                            @endif
                                            <tr class="table-primary">
                                                <td colspan="8" class="text-end fw-bold fs-5">{{ __('Total') }}:</td>
                                                <td class="fw-bold fs-5">{{ number_format($afterAdditional, 2) }}</td>
                                            </tr>
                                            @if(isset($invoice->paid_from_client) && $invoice->paid_from_client > 0)
                                            <tr>
                                                <td colspan="8" class="text-end">{{ __('Paid') }}:</td>
                                                <td>{{ number_format($invoice->paid_from_client, 2) }}</td>
                                            </tr>
                                            <tr class="table-warning">
                                                <td colspan="8" class="text-end fw-bold">{{ __('Remaining') }}:</td>
                                                <td class="fw-bold">{{ number_format(max($afterAdditional - $invoice->paid_from_client, 0), 2) }}</td>
                                            </tr>
                                            @endif
                                        </tfoot>
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

