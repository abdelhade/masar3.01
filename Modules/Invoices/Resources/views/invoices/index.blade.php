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

    <div class="row" x-data="invoiceFilter()">
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
                                <tr>
                                    <th><input type="text" x-model="filters.index" class="form-control form-control-sm"
                                            placeholder="#"></th>
                                    <th><input type="text" x-model="filters.date" class="form-control form-control-sm"
                                            placeholder="{{ __('Date') }}"></th>
                                    @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                        <th><input type="text" x-model="filters.dueDate"
                                                class="form-control form-control-sm" placeholder="{{ __('Due Date') }}">
                                        </th>
                                    @endif
                                    <th><input type="text" x-model="filters.operationName"
                                            class="form-control form-control-sm" placeholder="{{ __('Operation Name') }}">
                                    </th>
                                    <th><input type="text" x-model="filters.account" class="form-control form-control-sm"
                                            placeholder="{{ __('Account') }}"></th>
                                    <th><input type="text" x-model="filters.counterAccount"
                                            class="form-control form-control-sm"
                                            placeholder="{{ $invoiceType == 21 ? __('Counter Store') : __('Counter Account') }}">
                                    </th>
                                    <th><input type="text" x-model="filters.employee"
                                            class="form-control form-control-sm" placeholder="{{ __('Employee') }}"></th>
                                    <th><input type="text" x-model="filters.financialValue"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('Financial Value') }}"></th>
                                    @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                        <th><input type="text" x-model="filters.paidAmount"
                                                class="form-control form-control-sm"
                                                placeholder="{{ in_array($invoiceType, [11, 13, 15, 17]) ? __('Paid to Supplier') : __('Paid by Customer') }}">
                                        </th>
                                    @endif
                                    <th><input type="text" x-model="filters.netOperation"
                                            class="form-control form-control-sm" placeholder="{{ __('Net Operation') }}">
                                    </th>
                                    @if (!in_array($invoiceType, [11, 13, 18, 19, 20, 21]))
                                        <th><input type="text" x-model="filters.profit"
                                                class="form-control form-control-sm"
                                                placeholder="{{ in_array($invoiceType, [11, 13, 15, 17]) ? __('Cost') : __('Profit') }}">
                                        </th>
                                    @endif
                                    <th>
                                        <select x-model="filters.paymentStatus" class="form-select form-select-sm">
                                            <option value="">{{ __('All') }}</option>
                                            <option value="unpaid">{{ __('Unpaid') }}</option>
                                            <option value="paid">{{ __('Paid') }}</option>
                                            <option value="partial">{{ __('Partial') }}</option>
                                        </select>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $invoicesData = $invoices
                                        ->map(function ($invoice, $index) use ($invoiceType) {
                                            $totalAmount = $invoice->pro_value;
                                            $paidAmount = $invoice->paid_from_client;

                                            if ($paidAmount == 0) {
                                                $paymentStatus = 'unpaid';
                                                $paymentBadge =
                                                    '<span class="badge bg-danger">' . __('Unpaid') . '</span>';
                                            } elseif ($paidAmount >= $totalAmount) {
                                                $paymentStatus = 'paid';
                                                $paymentBadge =
                                                    '<span class="badge bg-success">' . __('Paid') . '</span>';
                                            } else {
                                                $paymentStatus = 'partial';
                                                $paymentBadge =
                                                    '<span class="badge bg-warning text-dark">' .
                                                    __('Partial') .
                                                    '</span>';
                                            }

                                            return [
                                                'index' => $index + 1,
                                                'id' => $invoice->id,
                                                'date' => \Carbon\Carbon::parse($invoice->pro_date)->format('Y-m-d'),
                                                'dueDate' => \Carbon\Carbon::parse($invoice->accural_date)->format(
                                                    'Y-m-d',
                                                ),
                                                'operationName' => $invoice->type->ptext ?? '',
                                                'account' => $invoice->acc1Head->aname ?? '',
                                                'counterAccount' => $invoice->acc2Head->aname ?? '',
                                                'employee' => $invoice->employee->aname ?? '',
                                                'financialValue' => $invoice->pro_value,
                                                'paidAmount' => $invoice->paid_from_client,
                                                'netOperation' => $invoice->fat_net,
                                                'profit' => $invoice->profit,
                                                'paymentStatus' => $paymentStatus,
                                                'paymentBadge' => $paymentBadge,
                                                'proType' => $invoice->pro_type,
                                                'invoice' => $invoice,
                                            ];
                                        })
                                        ->toArray();
                                @endphp

                                <template x-for="(invoice, idx) in filteredInvoices" :key="invoice.id">
                                    <tr>
                                        <td x-text="invoice.index"></td>
                                        <td>
                                            <span class="badge bg-light text-dark" x-text="invoice.date"></span>
                                        </td>
                                        @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                            <td>
                                                <span class="badge bg-light text-dark" x-text="invoice.dueDate"></span>
                                            </td>
                                        @endif
                                        <td x-text="invoice.operationName"></td>
                                        <td><span class="badge bg-light text-dark" x-text="invoice.account"></span></td>
                                        <td><span class="badge bg-light text-dark" x-text="invoice.counterAccount"></span>
                                        </td>
                                        <td><span class="badge bg-light text-dark" x-text="invoice.employee"></span></td>
                                        <td x-text="invoice.financialValue"></td>
                                        @if (!in_array($invoiceType, [18, 19, 20, 21]))
                                            <td x-text="invoice.paidAmount"></td>
                                        @endif
                                        <td x-text="invoice.netOperation"></td>
                                        @if (!in_array($invoiceType, [11, 13, 18, 19, 20, 21]))
                                            <td x-text="invoice.profit"></td>
                                        @endif
                                        <td class="text-center" x-html="invoice.paymentBadge"></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                                <template x-if="invoice.proType == 11">
                                                    <div class="d-flex gap-2">
                                                        <a class="btn btn-success btn-icon-square-sm"
                                                            :href="`{{ url('edit/purchase/price/invoice/report') }}/${invoice.id}`"
                                                            :title="'{{ __('Edit Selling Price') }}'">
                                                            <i class="las la-dollar-sign"></i>
                                                        </a>
                                                        <a class="btn btn-primary btn-icon-square-sm"
                                                            :href="`{{ url('invoices/barcode-report') }}/${invoice.id}`"
                                                            :title="'{{ __('Print Barcode') }}'">
                                                            <i class="las la-barcode"></i>
                                                        </a>
                                                    </div>
                                                </template>

                                                <a class="btn btn-info btn-icon-square-sm"
                                                    :href="`{{ url('invoice/view') }}/${invoice.id}`"
                                                    :title="'{{ __('View') }}'">
                                                    <i class="las la-eye"></i>
                                                </a>

                                                <a class="btn btn-warning btn-icon-square-sm"
                                                    :href="`{{ url('invoices') }}/${invoice.id}/edit`"
                                                    :title="'{{ __('Edit') }}'">
                                                    <i class="las la-edit"></i>
                                                </a>

                                                <form :action="`{{ url('invoices') }}/${invoice.id}`"
                                                    method="POST"
                                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this invoice?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                        <i class="las la-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="filteredInvoices.length === 0">
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
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoiceFilter() {
            return {
                invoices: @json($invoicesData ?? []),
                filters: {
                    index: '',
                    date: '',
                    dueDate: '',
                    operationName: '',
                    account: '',
                    counterAccount: '',
                    employee: '',
                    financialValue: '',
                    paidAmount: '',
                    netOperation: '',
                    profit: '',
                    paymentStatus: ''
                },
                get filteredInvoices() {
                    return this.invoices.filter(invoice => {
                        const matchIndex = !this.filters.index ||
                            invoice.index.toString().includes(this.filters.index);

                        const matchDate = !this.filters.date ||
                            invoice.date.toLowerCase().includes(this.filters.date.toLowerCase());

                        const matchDueDate = !this.filters.dueDate ||
                            invoice.dueDate.toLowerCase().includes(this.filters.dueDate.toLowerCase());

                        const matchOperationName = !this.filters.operationName ||
                            invoice.operationName.toLowerCase().includes(this.filters.operationName
                            .toLowerCase());

                        const matchAccount = !this.filters.account ||
                            invoice.account.toLowerCase().includes(this.filters.account.toLowerCase());

                        const matchCounterAccount = !this.filters.counterAccount ||
                            invoice.counterAccount.toLowerCase().includes(this.filters.counterAccount
                                .toLowerCase());

                        const matchEmployee = !this.filters.employee ||
                            invoice.employee.toLowerCase().includes(this.filters.employee.toLowerCase());

                        const matchFinancialValue = !this.filters.financialValue ||
                            invoice.financialValue.toString().includes(this.filters.financialValue);

                        const matchPaidAmount = !this.filters.paidAmount ||
                            invoice.paidAmount.toString().includes(this.filters.paidAmount);

                        const matchNetOperation = !this.filters.netOperation ||
                            invoice.netOperation.toString().includes(this.filters.netOperation);

                        const matchProfit = !this.filters.profit ||
                            invoice.profit.toString().includes(this.filters.profit);

                        const matchPaymentStatus = !this.filters.paymentStatus ||
                            invoice.paymentStatus === this.filters.paymentStatus;

                        return matchIndex && matchDate && matchDueDate && matchOperationName &&
                            matchAccount && matchCounterAccount && matchEmployee &&
                            matchFinancialValue && matchPaidAmount && matchNetOperation &&
                            matchProfit && matchPaymentStatus;
                    });
                }
            }
        }
    </script>
    <livewire:manufacturing::manufacturing-cost-modal />
@endsection
