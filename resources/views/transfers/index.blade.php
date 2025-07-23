@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Transfers'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Transfers')]],
    ])


    <div class="card">
        <div class="card-header">
            @can('إضافة التحويلات النقدية')
                <a href="{{ route('transfers.create') }}" type="button" class="btn btn-primary">{{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>رقم العمليه</th>
                            <th>نوع العمليه</th>
                            <th>البيان</th>
                            <th>المبلغ</th>
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>الموظف</th>
                            <th>الموظف 2 </th>
                            <th>المستخدم</th>
                            <th>تم الانشاء في </th>
                            <th>ملاحظات</th>
                            <th>تم المراجعه</th>
                            @canany(['تعديل التحويلات النقدية', 'حذف التحويلات النقدية'])
                                <th class="text-end">العمليات</th>
                            @endcanany

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td> {{ $loop->iteration }}</td>
                                <td class="nowrap">{{ $transfer->pro_date }}</td>
                                <td>{{ $transfer->pro_id }}</td>
                                <td>{{ $transfer->type->ptext ?? '—' }}</td>
                                <td>{{ $transfer->details ?? '' }}</td>
                                <td>
                                    <h4>{{ $transfer->pro_value }}</h4>
                                </td>
                                <td>{{ $transfer->account1->aname ?? '' }}</td>
                                <td>{{ $transfer->account2->aname ?? '' }}</td>
                                <td>{{ $transfer->emp1->aname ?? '' }}</td>
                                <td>{{ $transfer->emp2->aname ?? '' }}</td>
                                <td>{{ $transfer->user_name->name }}</td>
                                <td>{{ $transfer->created_at }}</td>
                                <td>{{ $transfer->info }}</td>
                                <td>{{ $transfer->confirmed ? 'نعم' : 'لا' }}</td>
                                @canany(['تعديل التحويلات النقدية', 'حذف التحويلات النقدية'])
                                    <td x-show="columns[16]">
                                        @can('تعديل التحويلات النقديه')
                                            <button>
                                                <a href="{{ route('transfers.edit', $transfer) }}" class="text-primary font-16"><i
                                                        class="las la-eye"></i></a>
                                            </button>
                                        @endcan
                                        @can('حذف التحويلات النقدية')
                                            <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger font-16" onclick="return confirm('هل أنت متأكد؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                @endcanany
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection
