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
                                    <th>تاريخ الفاتورة</th>
                                    <th>الحساب الدائن</th>
                                    <th>الحساب المدين</th>
                                    <th>الموظف</th>
                                    <th>المستخدم</th>
                                    <th>العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $invoice->pro_id }}</td>
                                        <td>
                                            @if ($invoice->pro_type == 11)
                                                <span class="badge bg-primary">مشتريات</span>
                                            @elseif($invoice->pro_type == 13)
                                                <span class="badge bg-warning">مرتجع مشتريات</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->pro_value, 2) }}</td>
                                        <td>{{ $invoice->pro_date }}</td>
                                        <td>{{ $invoice->acc1Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->acc2Head->aname ?? '-' }}</td>
                                        <td>{{ $invoice->employee->aname ?? '-' }}</td>
                                        <td>{{ $invoice->user->name ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <a class="btn btn-blue btn-icon-square-sm"
                                                    href="{{ route('invoices.edit', $invoice->id) }}">
                                                    <i class="las la-eye"></i>
                                                </a>

                                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                                    style="display:inline-block;"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا التخصص؟');">
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
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد بيانات مضافة حتى الآن
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
