@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Sale Order Report'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Sale Order')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="sale-order-invoice-report-table"
                            filename="sale-order-invoice-report-table" excel-label="{{ __('Export Excel') }}"
                            pdf-label="{{ __('Export PDF') }}" print-label="{{ __('Print') }}" />

                        <table id="sale-order-invoice-report-table" class="table table-bordered table-striped text-center"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Invoice Date') }}</th>
                                    <th>{{ __('Credit Account') }}</th>
                                    <th>{{ __('Debit Account') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $invoice->pro_id }}</td>
                                        <td>
                                            @if ($invoice->pro_type == 14)
                                                <span class="badge bg-primary">{{ __('Sale Order') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->pro_value, 2) }}</td>
                                        <td>{{ $invoice->pro_date }}</td>
                                        <td>{{ $invoice->acc1Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->acc2Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->employee->aname ?? '-' }}</td>
                                        <td>{{ $invoice->user->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                <a class="btn btn-primary btn-sm"
                                                    href="{{ route('invoices.convert-to-sales', $invoice->id) }}"
                                                    title="{{ __('Convert to Sales Invoice') }}">
                                                    <i class="fas fa-shopping-cart me-1"></i>
                                                    {{ __('Convert to Sales Invoice') }}
                                                </a>

                                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                                    style="display:inline-block;"
                                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this sale order?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-icon-square-sm"
                                                        title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="fas fa-info-circle me-2"></i>
                                                {{ __('No data available yet') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
