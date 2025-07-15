@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الفواتير'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('فاتورة مبيعات')],
        ],
    ])
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">#</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('تاريخ') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('تاريخ الاستحقاق') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('اسم العمليه') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('الحساب') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('الحساب المقابل') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('المخزن') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('الموظف') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('قيمة المليه') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('صافي العمليه') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('الربح') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('المستخدم') }}</th>
                                    <th  class="font-family-cairo fw-bold font-14 text-center">{{ __('العمليات') }}</th>
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

                                        {{-- <td>{{ $invoice->type->ptext }}</td> --}}
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->acc1Head->aname ?? '' }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->acc2Head->aname ?? '' }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->store->aname ?? '' }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark">{{ $invoice->employee->aname ?? '' }}</span>
                                        </td>
                                        <td>{{ $invoice->pro_value }}</td>
                                        <td>{{ $invoice->fat_net }}</td>
                                        <td>{{ $invoice->profit }}</td>
                                        <td><span class="badge bg-dark">{{ $invoice->acc1Headuser->aname }}</span>
                                        </td>
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
                                               لا توجد بيانات 
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
