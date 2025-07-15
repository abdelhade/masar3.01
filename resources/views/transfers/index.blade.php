@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Transfers'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Transfers')]],
    ])


    <div class="card">
        <div class="card-header">
            <a href="{{ route('transfers.create') }}" type="button" class="btn btn-primary">{{ __('Add New') }}
                <i class="fas fa-plus me-2"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">

                        <tr>
                            <th class="font-family-cairo fw-bold font-14 text-center" >#</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >التاريخ</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >رقم العمليه</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >نوع العمليه</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >البيان</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >المبلغ</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >مدين</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >دائن</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >الموظف</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >الموظف 2 </th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >المستخدم</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >تم الانشاء في </th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >ملاحظات</th>
                            <th class="font-family-cairo fw-bold font-14 text-center" >تم المراجعه</th>
                            <th class="font-family-cairo fw-bold font-14 text-center"  class="text-end">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td  class="font-family-cairo fw-bold font-14 text-center"> {{ $loop->iteration }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center" class="nowrap">{{ $transfer->pro_date }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->pro_id }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->type->ptext ?? '—' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->details ?? '' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center"><h4>{{ $transfer->pro_value }}</h4></td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->account1->aname ?? '' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->account2->aname ?? '' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->emp1->aname ?? '' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->emp2->aname ?? '' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->user_name->name }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->created_at }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->info }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center">{{ $transfer->confirmed ? 'نعم' : 'لا' }}</td>
                                <td  class="font-family-cairo fw-bold font-14 text-center" x-show="columns[16]">
                                    <button>
                                        <a href="{{ route('transfers.edit', $transfer) }}" class="text-primary font-16"><i
                                                class="las la-eye"></i></a>
                                    </button>
                                    <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger font-16" onclick="return confirm('هل أنت متأكد؟')">
                                            <i class="las la-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection
