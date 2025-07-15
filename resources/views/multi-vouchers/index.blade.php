@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('vouchers'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('vouchers')]],
    ])


    <div class="card">
        @if (session('success'))
            <div class="alert alert-success cake cake-pulse">
                {{ session('success') }}
            </div>
        @endif
        <div class="card-header">

            <a href="{{ route('multi-vouchers.create') }}" type="button" class="btn btn-primary">{{ __('Add New') }}
                <i class="fas fa-plus me-2"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">

                        <tr>
                            <th class="font-family-cairo fw-bold font-14 text-center">#</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">التاريخ</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">رقم العمليه</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">نوع العمليه</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">البيان</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">المبلغ</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">من حساب</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">الي حساب</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">الموظف</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">الموظف 2 </th>
                            <th class="font-family-cairo fw-bold font-14 text-center">المستخدم</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">تم الانشاء في </th>
                            <th class="font-family-cairo fw-bold font-14 text-center">ملاحظات</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">تم المراجعه</th>
                            <th class="font-family-cairo fw-bold font-14 text-center">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($multis as $multi)
                            <tr>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $loop->iteration }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->pro_date }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->pro_id }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->type->ptext ?? '—' }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->details }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->pro_value }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">
                                    {{ $multi->account1->aname ?? 'مذكروين' }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">
                                    {{ $multi->account2->aname ?? 'مذكروين' }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->emp1->aname ?? '' }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->emp2->aname ?? '' }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->user }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->created_at }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $multi->info }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">
                                    {{ $multi->confirmed ? 'نعم' : 'لا' }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center" x-show="columns[16]">
                                    <button>
                                        <a href="{{ route('multi-vouchers.edit', $multi) }}"
                                            class="text-primary font-16"><i class="las la-eye"></i></a>
                                    </button>
                                    <form action="{{ route('multi-vouchers.destroy', $multi->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-icon-square-sm"
                                            onclick="return confirm(' أنت متأكد انك عايز تمسح العملية و القيد المصاحب لها؟')">
                                            <i class="las la-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">
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

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
@endsection
