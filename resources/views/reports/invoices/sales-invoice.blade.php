@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تقرير فواتير المشتريات'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('فواتير المشتريات')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered table-striped text-center" style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>رقم الفاتورة</th>
                                    <th>النوع</th>
                                    <th>القيمة</th>
                                    <th>الربح</th>
                                    <th>تاريخ الفاتورة</th>
                                    <th>الحساب الدائن</th>
                                    <th>الحساب المدين</th>
                                    <th>الموظف</th>
                                    <th>المستخدم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $invoice->pro_id }}</td>
                                        <td>
                                            @if ($invoice->pro_type == 10)
                                                <span class="badge bg-primary">مبيعات</span>
                                            @elseif($invoice->pro_type == 13)
                                                <span class="badge bg-warning">مرتجع مبيعات</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->pro_value, 2) }}</td>
                                        <td
                                            class="{{ $invoice->profit > 0 ? 'bg-success text-white' : ($invoice->profit < 0 ? 'bg-danger text-white' : '') }}">
                                            {{ $invoice->profit }}
                                        </td>

                                        <td>{{ $invoice->pro_date }}</td>
                                        <td>{{ $invoice->acc1Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->acc2Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->employee->aname ?? '-' }}</td>
                                        <td>{{ $invoice->user->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            لا توجد فواتير مشتريات مسجلة
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
