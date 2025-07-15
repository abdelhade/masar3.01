@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الخصومات'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الخصومات')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card ">

                @if (is_null($type))
                    <div class="alert alert-warning text-center">
                        يرجى اختيار نوع الخصم من القائمة.
                    </div>
                @else
                    <h4 class="mx-4">
                        @if ($type == 30)
                            {{ __('قائمة الخصومات المسموح بها') }}
                        @elseif ($type == 31)
                            {{ __('قائمة الخصومات المكتسبة') }}
                        @else
                            {{ __('جميع الخصومات') }}
                        @endif
                    </h4>

                    <div class="card-body">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-striped mb-0" style="min-width: 1200px;">
                                <thead class="table-light text-center align-middle">

                                    <tr>
[]                                        <th class="font-family-cairo fw-bold font-14 text-center">#</th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('نوع الخصم') }}</th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('قيمة الخصم') }}
                                        </th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('تاريخ السند') }}
                                        </th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('رقم السند') }}</th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الحساب المدين') }}
                                        </th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الحساب الدائن') }}
                                        </th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('ملاحظات') }}</th>
                                        <th class="font-family-cairo fw-bold font-14 text-center">{{ __('العمليات') }}</th>

                                        <th>#</th>
                                        <th>{{ __('نوع الخصم') }}</th>
                                        <th>{{ __('قيمة الخصم') }}</th>
                                        <th>{{ __('تاريخ السند') }}</th>
                                        <th>{{ __('رقم السند') }}</th>
                                        <th>{{ __('الحساب المدين') }}</th>
                                        <th>{{ __('الحساب الدائن') }}</th>
                                        <th>{{ __('ملاحظات') }}</th>
                                        @can('عرض - تفاصيل خصم مسموح')
                                        <th>{{ __('العمليات') }}</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($discounts as $discount)
                                        <tr>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ $loop->iteration }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">
                                                <span
                                                    class="badge
                                                @if ($discount->acc1 == 91 || $discount->acc2 == 91) bg-success text-dark
                                                @elseif($discount->acc1 == 97 || $discount->acc2 == 97)
                                                    bg-warning text-dark
                                                @else
                                                    bg-secondary @endif
                                                text-uppercase">
                                                    @if ($discount->acc1 == 91 || $discount->acc2 == 91)
                                                        خصم مسموح به
                                                    @elseif($discount->acc1 == 97 || $discount->acc2 == 97)
                                                        خصم مكتسب
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            </td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ $discount->pro_value }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ \Carbon\Carbon::parse($discount->pro_date)->format('Y-m-d') }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ $discount->pro_id }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ $discount->acc1Head->aname ?? '-' }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ $discount->acc2Head->aname ?? '-' }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">{{ $discount->info }}</td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">
                                            <td>{{ $discount->pro_value }}</td>
                                            <td>{{ \Carbon\Carbon::parse($discount->pro_date)->format('Y-m-d') }}</td>
                                            <td>{{ $discount->pro_id }}</td>
                                            <td>{{ $discount->acc1Head->aname ?? '-' }}</td>
                                            <td>{{ $discount->acc2Head->aname ?? '-' }}</td>
                                            <td>{{ $discount->info }}</td>
                                            @can('عرض - تفاصيل خصم مسموح')
                                            <td>
                                                @can('تعديل - قائمة الخصومات المسموح بها')
                                                <a href="{{ route('discounts.edit', ['discount' => $discount->id, 'type' => $discount->acc1 == 97 ? 31 : 30]) }}"
                                                    class="btn btn-success btn-icon-square-sm">
                                                    <i class="las la-edit"></i>
                                                </a>
                                                 @endcan
                                                @can('حذف - قائمة الخصومات المسموح بها')
                                                <form action="{{ route('discounts.destroy', $discount->id) }}"
                                                    method="POST" style="display:inline-block;"
                                                    onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                        <i class="las la-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                            @endcan
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
                @endif
            </div>
        </div>
    </div>
@endsection
