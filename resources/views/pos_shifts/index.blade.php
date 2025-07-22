@extends('admin.dashboard')

@section('content')
    <div class="container">
        <h2>قائمة الشيفتات</h2>
        <a href="{{ route('pos-shifts.create') }}" class="btn btn-primary mb-3">فتح شيفت جديد</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>الكاشير</th>
                    <th>بداية الشيفت</th>
                    <th>الرصيد الافتتاحي</th>
                    <th>الحالة</th>
                    <th>الرصيد الختامي</th>
                    <th>الملاحظات</th>
                    <th>تاريخ الإنشاء</th>
                    <th>تاريخ التحديث</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shifts as $shift)
                    <tr>
                        <td>{{ $shift->user->name ?? 'غير معروف' }}</td>
                        <td>{{ $shift->opened_at }}</td>
                        <td>{{ $shift->opening_balance }}</td>
                        <td>{{ $shift->status }}    </td>
                        <td>{{ $shift->closing_balance }}</td>
                        <td>{{ $shift->notes }}</td>
                        <td>{{ $shift->created_at }}</td>
                        <td>{{ $shift->updated_at }}</td>
                        <td>
                            <a href="{{ route('pos-shifts.show', $shift->id) }}" class="btn btn-sm btn-info">عرض</a>
                            @if($shift->closed_at == null)
                                <a href="{{ route('pos-shifts.close', $shift->id) }}" class="btn btn-sm btn-danger">إغلاق</a>
                            @endif
                        </td>
                        
                        

                   
                   

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection