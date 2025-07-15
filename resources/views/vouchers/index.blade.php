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
                            <th class="font-family-cairo fw-bold font-14">م</th>
                            <th class="font-family-cairo fw-bold font-14">التاريخ</th>
                            <th class="font-family-cairo fw-bold font-14">رقم العملية</th>
                            <th class="font-family-cairo fw-bold font-14">نوع العملية</th>
                            <th class="font-family-cairo fw-bold font-14">البيان</th>
                            <th class="font-family-cairo fw-bold font-14">المبلغ</th>
                            <th class="font-family-cairo fw-bold font-14">الحساب</th>
                            <th class="font-family-cairo fw-bold font-14">الحساب المقابل</th>
                            <th class="font-family-cairo fw-bold font-14">الموظف</th>
                            <th class="font-family-cairo fw-bold font-14">الموظف - 2</th>
                            <th class="font-family-cairo fw-bold font-14">المستخدم</th>
                            <th class="font-family-cairo fw-bold font-14">تاريخ الإدخال</th>
                            <th class="font-family-cairo fw-bold font-14">تاريخ التعديل</th>
                            <th class="font-family-cairo fw-bold font-14">ملاحظات</th>
                            <th class="font-family-cairo fw-bold font-14">تم المراجعة</th>
                            <th class="font-family-cairo fw-bold font-14">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $index => $voucher)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $voucher->pro_date }}</td>
                                <td class="text-center">{{ $voucher->pro_id }}</td>
                                <td class="text-center {{ $voucher->pro_type == 1 ? 'badge badge-primary' : '' }}">
                                    {{ $voucher->type->ptext ?? '' }}
                                </td>
                                <td class="text-center">{{ $voucher->details }}</td>
                                <td class="text-center h2">{{ number_format($voucher->pro_value, 2) }}</td>
                                <td class="text-center">{{ $voucher->account1->aname ?? '' }}</td>
                                <td class="text-center">{{ $voucher->account2->aname ?? '' }}</td>
                                <td class="text-center">{{ $voucher->emp1->aname ?? '' }}</td>
                                <td class="text-center">{{ $voucher->emp2->aname ?? '' }}</td>
                                <td class="text-center">{{ $voucher->user->name ?? '' }}</td>
                                <td class="text-center">{{ $voucher->created_at }}</td>
                                <td class="text-center">{{ $voucher->updated_at }}</td>
                                <td class="text-center">{{ $voucher->notes ?? '' }}</td>
                                <td class="text-center">{{ $voucher->is_approved ? 'نعم' : 'لا' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-warning">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" onclick="return confirm('هل أنت متأكد؟')">X</button>
                                    </form>
                                </td>
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
