@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('vouchers'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('vouchers')]
        ],
    ])

    <div class="card">
        <div class="card-header">
            <h2>قائمة السندات</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th x-show="columns[0]">م</th>
                            <th x-show="columns[1]">التاريخ</th>
                            <th x-show="columns[2]">رقم العملية</th>
                            <th x-show="columns[3]">نوع العملية</th>
                            <th x-show="columns[4]">البيان</th>
                            <th x-show="columns[5]">المبلغ</th>
                            <th x-show="columns[6]">الحساب</th>
                            <th x-show="columns[8]">الحساب المقابل</th>
                            <th x-show="columns[9]">الموظف</th>
                            <th x-show="columns[10]">الموظف - 2</th>
                            <th x-show="columns[11]">user</th>
                            <th x-show="columns[12]">created at</th>
                            <th x-show="columns[13]">modified at</th>
                            <th x-show="columns[14]">ملاحظات</th>
                            <th x-show="columns[15]">تم المراجعه</th>
                            @canany(['حذف السندات', 'تعديل السندات'])
                                <th x-show="columns[16]">العمليات</th>
                            @endcan


                            <!-- أكمل باقي الأعمدة بنفس الطريقة -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $index => $voucher)
                            <tr>
                                <td x-show="columns[0]">{{ $x++ }}</td>
                                <td x-show="columns[1]">{{ $voucher->pro_date }}</td>
                                <td x-show="columns[2]">{{ $voucher->pro_id }}</td>
                                <td x-show="columns[3]"
                                    class="
                                    @if ($voucher->pro_type == 1) {echo 'badge badge-primary'} @endif
                                    ">
                                    {{ $voucher->type->ptext ?? '' }}</td>
                                <td x-show="columns[4]">{{ $voucher->details }}</td>
                                <td x-show="columns[5]" class="h2">{{ number_format($voucher->pro_value, 2) }}</td>
                                <td x-show="columns[6]">{{ $voucher->account1->aname ?? '' }}</td>
                                <td x-show="columns[7]">{{ $voucher->account2->aname ?? '' }}</td>
                                <td x-show="columns[9]">{{ $voucher->emp1->aname ?? '' }}</td>
                                <td x-show="columns[10]">{{ $voucher->emp2->aname ?? '' }}</td>
                                <td x-show="columns[11]">{{ $voucher->user->name ?? '' }}</td>
                                <td x-show="columns[12]">{{ $voucher->created_at }}</td>
                                <td x-show="columns[13]">{{ $voucher->updated_at }}</td>
                                <td x-show="columns[14]">{{ $voucher->notes ?? '' }}</td>
                                <td x-show="columns[15]">{{ $voucher->is_approved ? 'نعم' : 'لا' }}</td>
                                @canany(['حذف السندات', 'تعديل السندات'])
                                    <td x-show="columns[16]">
                                        @can('تعديل السندات')
                                            <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-warning"><i
                                                    class="fa fa-eye"></i></a>
                                        @endcan
                                        @can('حذف السندات')
                                            <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger" onclick="return confirm('هل أنت متأكد؟')">X</button>
                                            </form>
                                        @endcan

                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center">
                                    <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
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
@endsection
