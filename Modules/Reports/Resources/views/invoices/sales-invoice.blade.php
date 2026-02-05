@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Sales Invoices Report'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Sales Invoices')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="sales-invoice-report-table" filename="sales-invoice-report-table"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="sales-invoice-report-table" class="table table-bordered table-striped text-center"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Profit') }}</th>
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
                                            @if ($invoice->pro_type == 10)
                                                <span class="badge bg-primary">{{ __('Sales') }}</span>
                                            @elseif($invoice->pro_type == 13)
                                                <span class="badge bg-warning">{{ __('Sales Return') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->pro_value, 2) }}</td>
                                        <td
                                            class="fw-bold {{ $invoice->profit > 0 ? 'bg-success text-white' : ($invoice->profit < 0 ? 'bg-danger text-white' : 'bg-secondary text-white') }}">
                                            {{ number_format($invoice->profit, 2) }}
                                        </td>
                                        <td>{{ $invoice->pro_date }}</td>
                                        <td>{{ $invoice->acc1Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->acc2Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->employee->aname ?? '-' }}</td>
                                        <td>{{ $invoice->user->name ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <a class="btn btn-primary btn-icon-square-sm"
                                                    href="{{ route('invoices.edit', $invoice->id) }}"
                                                    title="{{ __('View/Edit') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                                    style="display:inline-block;"
                                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this record?') }}');">
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
                                        <td colspan="11" class="text-center">
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
