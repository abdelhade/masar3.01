@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('الخصومات'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الخصومات')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                @if (is_null($type))
                    <div class="alert alert-warning text-center">
                        يرجى اختيار نوع الخصم من القائمة.
                    </div>
                @else
                    <h4>
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
                            <table class="table table-striped mb-0 text-center" style="min-width: 1000px;">
                                <thead class="table-light">
                                    <tr>
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
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
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
                                                    class="btn btn-success btn-sm">
                                                    <i class="las la-edit"></i>
                                                </a>
                                                 @endcan
                                                @can('حذف - قائمة الخصومات المسموح بها')
                                                <form action="{{ route('discounts.destroy', $discount->id) }}"
                                                    method="POST" style="display:inline-block;"
                                                    onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="las la-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13">
                                                <div class="alert alert-info text-center mb-0">
                                                لا توجد بيانات مضافة حتى الآن
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
