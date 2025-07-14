@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Journals'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Journals')]],
    ])

    
            <div class="card">
                @if (session('success'))
    <div class="alert alert-success cake cake-pulse">
        {{ session('success') }}
    </div>
@endif
                <div class="card-header">
                   @can('انشاء قيود اليوميه عمليات')
                    <a href="{{ route('multi-journals.create') }}" type="button" class="btn btn-primary">{{ __('Add New') }}
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
                                    <th>من حساب</th>
                                    <th>الي حساب</th>
                                    <th>الموظف</th>
                                    <th>الموظف 2 </th>
                                    <th>المستخدم</th>
                                    <th>تم الانشاء في </th>
                                    <th>ملاحظات</th>
                                    <th>تم المراجعه</th>
                                    @can('اجراء العمليات علي قيود اليوميه عمليات')
                                    <th class="text-end">العمليات</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($multis as $multi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $multi->pro_date }}</td>
                                        <td>{{ $multi->pro_id }}</td>
                                        <td>{{ $multi->type->ptext ?? '—' }}</td>
                                        <td>{{ $multi->details}}</td>
                                        <td>{{ $multi->pro_value }}</td>
                                        <td>{{ $multi->account1->aname ?? 'مذكروين' }}</td>
                                        <td>{{ $multi->account2->aname ?? 'مذكروين' }}</td>
                                        <td>{{ $multi->emp1->aname ?? ''  }}</td>
                                        <td>{{ $multi->emp2->aname ?? '' }}</td>
                                        <td>{{ $multi->user }}</td>
                                        <td>{{ $multi->created_at }}</td>
                                        <td>{{ $multi->info }}</td>
                                        <td>{{ $multi->confirmed ? 'نعم' : 'لا' }}</td>
                                    @can('اجراء العمليات علي قيود اليوميه عمليات')
                                        <td x-show="columns[16]">
                                            @can('تعديل قيود اليوميه عمليات')
                                        <button>
                                        <a href="{{ route('multi-journals.edit', $multi) }}" class="text-primary font-16"><i class="las la-eye"></i></a>
                                        </button>
                                        @endcan
                                        @can('حذف قيود اليوميه عمليات')
                                            <form action="{{ route('multi-journals.destroy', $multi->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-danger font-16" onclick="return confirm(' أنت متأكد انك عايز تمسح العملية و القيد المصاحب لها؟')">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                    @endcan
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
@endsection
