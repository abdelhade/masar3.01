@extends('admin.dashboard')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">قائمة تأجير المعدات</h4>
            <a href="{{ route('rentals.create') }}" class="btn btn-light">
                إضافة تأجير جديد
            </a>
        </div>

        <div class="card-body p-4">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($rentals->isEmpty())
                <div class="alert alert-info text-center">لا توجد عمليات تأجير مسجلة حالياً.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>المعدة</th>
                                <th>العميل</th>
                                <th>الموظف</th>
                                <th>قيمة الإيجار</th>
                                <th>المشروع</th>
                                <th>من تاريخ</th>
                                <th>إلى تاريخ</th>
                                <th>ملاحظات</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rentals as $rental)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $rental->acc3Head->aname ?? '-'}}</td>
                                    <td>{{ $rental->acc1Head->aname ?? '-' }}</td>
                                    <td>{{ $rental->employee->aname ?? '-' }}</td>
                                    <td>{{ number_format($rental->pro_value, 2) }}</td>
                                    <td>{{ $rental->project->name ?? '-' }}</td>
                                    <td>{{ $rental->start_date }}</td>
                                    <td>{{ $rental->end_date }}</td>
                                    <td>{{ $rental->details }}</td>
                                    <td>
                                        <a href="{{ route('rentals.edit', $rental->id) }}" class="btn btn-sm btn-primary">تعديل</a>
                                        <form action="{{ route('rentals.destroy', $rental->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
