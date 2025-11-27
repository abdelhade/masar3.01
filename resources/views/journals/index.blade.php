@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.journals')
@endsection
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
            @can('create journals')
                <a href="{{ route('journals.create') }}" type="button" class="btn btn-main">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add New') }}
                </a>
            @endcan

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
                            @canany(['edit journals', 'delete journals'])
                                <th class="font-family-cairo fw-bold font-14 text-center">العمليات</th>
                            @endcanany
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
                                @canany(['edit journals', 'delete journals'])
                                    <td class="font-family-cairo fw-bold font-14 text-center" x-show="columns[16]">
                                        @can('edit journals')
                                            <button>
                                                <a href="{{ route('journals.edit', $journal) }}" class="text-primary font-16"><i
                                                        class="las la-pen"></i></a>
                                            </button>
                                        @endcan
                                        @can('delete journals')
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
                                @endcanany
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
