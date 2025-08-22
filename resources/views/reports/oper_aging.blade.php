@extends('admin.dashboard')
@section('content')
<div class="container">

    <h2 class="mb-4">تقرير الديون المستحقة حتى {{ $today->format('d-m-Y') }}</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>رقم العملية</th>
                <th>تاريخ العملية</th>
                <th>تاريخ الاستحقاق</th>
                <th>قيمة الفاتورة</th>
                <th>الرصيد المستحق</th>
                <th>الفترة</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total = 0;
                $bucketSummary = [];
            @endphp

            @foreach($data as $i => $row)
                @php 
                    $total += $row->balance;
                    $bucketSummary[$row->aging_bucket] = ($bucketSummary[$row->aging_bucket] ?? 0) + $row->balance;
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $row->pro_num }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->pro_date)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->due_date)->format('d-m-Y') }}</td>
                    <td class="text-end">{{ number_format($row->invoice_value, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($row->balance, 2) }}</td>
                    <td class="text-center">{{ $row->aging_bucket }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-secondary">
            <tr>
                <th colspan="5" class="text-center">الإجمالي الكلي</th>
                <th class="text-end">{{ number_format($total, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <h4 class="mt-4">ملخص حسب الفترات</h4>
    <table class="table table-sm table-bordered w-50">
        <thead class="table-light">
            <tr>
                <th>الفترة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bucketSummary as $bucket => $amount)
                <tr>
                    <td>{{ $bucket }}</td>
                    <td class="text-end">{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
