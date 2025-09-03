@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => $invoiceTitle,
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => $currentSection],
            ['label' => $invoiceTitle],
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
                        <h5 class="card-title mb-0">{{ $invoiceTitle }}</h5>
                        <a href="{{ url('/invoices/create?type=' . $invoiceType . '&q=' . md5($invoiceType)) }}"
                            class="btn btn-primary">
                            <i class="las la-plus me-1"></i>
                            إضافة {{ $invoiceTitle }}
                        </a>
                    </div>

                    <form method="GET" action="{{ route('invoices.index') }}" class="row g-3 align-items-end">
                        <input type="hidden" name="type" value="{{ $invoiceType }}">

                        <div class="col-md-4">
                            <label for="start_date" class="form-label">{{ __('من تاريخ') }}</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">{{ __('إلى تاريخ') }}</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('فلتر') }}</button>
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
                        excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="invoices-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th class="font-family-cairo fw-bold font-14 text-center">#</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('تاريخ') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('تاريخ الاستحقاق') }}
                                    </th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('اسم العمليه') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الحساب') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الحساب المقابل') }}
                                    </th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الموظف') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('قيمة الماليه') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">
                                        {{ __('المدفوع من العميل ') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('صافي العمليه') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الربح') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('العمليات') }}</th>
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
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ \Carbon\Carbon::parse($invoice->accural_date)->format('Y-m-d') }}
                                            </span>
                                        </td>

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
                                        <td>{{ $invoice->paid_from_client }}</td>
                                        <td>{{ $invoice->fat_net }}</td>
                                        <td>{{ $invoice->profit }}</td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                                @if ($invoice->pro_type == 11)
                                                    <a class="btn btn-success d-inline-flex align-items-center"
                                                        href="{{ route('edit.purchase.price.invoice.report', $invoice->id) }}">
                                                        <i class="las la-eye me-1"></i>
                                                        تعديل سعر البيع
                                                    </a>

                                                    <a class="btn btn-primary d-inline-flex align-items-center"
                                                        href="{{ route('invoices.barcode-report', $invoice->id) }}">
                                                        <i class="las la-barcode me-1"></i>
                                                        طباعة باركود
                                                    </a>
                                                @endif

                                                <a class="btn btn-blue btn-icon-square-sm"
                                                    href="{{ route('invoices.edit', $invoice->id) }}">
                                                    <i class="las la-eye"></i>
                                                </a>

                                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                        <i class="las la-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد {{ $invoiceTitle }} في هذا التاريخ
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
