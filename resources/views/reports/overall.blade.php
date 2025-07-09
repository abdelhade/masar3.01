@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>محلل العمل اليومي</h2>
        </div>
        <div class="card-body">
            <div class="table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>الوقت</th>
                            <th>المستخدم</th>
                            <th>العملية</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opers as $log)
                        <tr>
                            <td>{{ $log->created_at->format('Y-m-d') }}</td>
                            <td>{{ $log->created_at->format('H:i') }}</td>
                            <td>{{ $log->user->name ?? '---' }}</td>
                            <td>{{ $log->type->ptext?? '_____' }}</td>
                            <td>{{ $log->details }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

@endsection