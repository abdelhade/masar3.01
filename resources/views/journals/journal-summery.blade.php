@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Journals'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Journals')]],
    ])


    <div class="card">

        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-hover table-sm  mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">

                        <tr class="journal_tr text-center">
                            <th>م</th>
                            <th>رقم القيد</th>
                            <th>مدين</th>
                            <th>دائن</th>
                            <th>اسم الحساب</th>
                            <th>بيان</th>
                            <th>نوع العملية</th>
                            <th>التاريخ</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($journalHeads as $i => $head)
                            @foreach ($head->dets as $j => $detail)
                                <tr class="">
                                    @if ($j == 0)
                                        <td  class="font-family-cairo fw-bold font-14 text-center" rowspan="{{ $head->dets->count() }}">{{ $i + 1 }}</td>
                                        <td  class="font-family-cairo fw-bold font-14 text-center" rowspan="{{ $head->dets->count() }}">{{ $head->journal_id }}</td>
                                    @endif

                                    <td  class="font-family-cairo fw-bold font-14 text-center">{{ $detail->debit }}</td>
                                    <td  class="font-family-cairo fw-bold font-14 text-center">{{ $detail->credit }}</td>
                                    <td  class="font-family-cairo fw-bold font-14 text-center">{{ $detail->accHead->aname ?? '-' }}</td>
an
                                    @if ($j == 0)
                                        <td  class="font-family-cairo fw-bold font-14 text-center" rowspan="{{ $head->dets->count() }}">{{ $head->details }}</td>
                                        <td  class="font-family-cairo fw-bold font-14 text-center" rowspan="{{ $head->dets->count() }}">{{ $head->oper->type->ptext }}</td>
                                        <td  class="font-family-cairo fw-bold font-14 text-center" rowspan="{{ $head->dets->count() }}">{{ $head->date }}</td>
                                    @endif
                                </tr>

                            @endforeach

                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
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
