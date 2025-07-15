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
                            <a href="{{ route('journals.create') }}" type="button" class="btn btn-primary">{{ __('Add New') }}
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
                                    @foreach ($journals as $journal)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $journal->pro_date }}</td>
                                            <td>{{ $journal->pro_id }}</td>
                                            <td>{{ $journal->type->ptext ?? '—' }}</td>
                                            <td>{{ $journal->details }}</td>
                                            <td>{{ $journal->pro_value }}</td>
                                            <td>{{ $journal->account1->aname }}</td>
                                            <td>{{ $journal->account2->aname ?? '' }}</td>
                                            <td>{{ $journal->emp1->aname ?? '' }}</td>
                                            <td>{{ $journal->emp2->aname ?? '' }}</td>
                                            <td>{{ $journal->user }}</td>
                                            <td>{{ $journal->created_at }}</td>
                                            <td>{{ $journal->info }}</td>
                                            <td>{{ $journal->confirmed ? 'نعم' : 'لا' }}</td>
                                            @can('اجراء العمليات علي قيود اليوميه عمليات')
                                                <td x-show="columns[16]">
                                                    @can('تعديل قيود اليوميه عمليات')
                                                        <button>
                                                            <a href="{{ route('journals.edit', $journal) }}"
                                                                class="text-primary font-16"><i class="las la-eye"></i></a>
                                                        </button>
                                                    @endcan
                                                    @can('حذف قيود اليوميه عمليات')
                                                        <form action="{{ route('journals.destroy', $journal->id) }}" method="POST"
                                                            style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-danger font-16"
                                                                onclick="return confirm(' أنت متأكد انك عايز تمسح العملية و القيد المصاحب لها؟')">
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
        @endif
        <div class="card-header">

            <a href="{{ route('journals.create') }}" type="button" class="btn btn-primary">{{ __('Add New') }}
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
                        @forelse ($journals as $journal)
                            <tr>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $loop->iteration }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->pro_date }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->pro_id }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">
                                    {{ $journal->type->ptext ?? '—' }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->details }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->pro_value }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->account1->aname }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">
                                    {{ $journal->account2->aname ?? '' }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->emp1->aname ?? '' }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->emp2->aname ?? '' }}
                                </td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->user }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->created_at }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">{{ $journal->info }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center">
                                    {{ $journal->confirmed ? 'نعم' : 'لا' }}</td>
                                <td class="font-family-cairo fw-bold font-14 text-center" x-show="columns[16]">
                                    <button>
                                        <a href="{{ route('journals.edit', $journal) }}" class="text-primary font-16"><i
                                                class="las la-eye"></i></a>
                                    </button>
                                    <form action="{{ route('journals.destroy', $journal->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-danger font-16"
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
@endsection
