@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>محلل العمل اليومي</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <label for="user_id">المستخدم:</label>
                    <select id="user_id" class="form-control" wire:model="userId">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="operation_type">نوع العملية:</label>
                    <select id="operation_type" class="form-control" wire:model="operationType">
                        <option value="">الكل</option>
                        <option value="10">فاتورة مبيعات</option>
                        <option value="11">فاتورة مشتريات</option>
                        <option value="12">مردود مبيعات</option>
                        <option value="13">مردود مشتريات</option>
                        <option value="7">قيد يومية</option>
                        <option value="8">قيد يومية حسابات</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>الوقت</th>
                            <th>المستخدم</th>
                            <th>نوع العملية</th>
                            <th>الرقم</th>
                            <th>القيمة</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operations as $operation)
                        <tr>
                            <td>{{ $operation->pro_date ? \Carbon\Carbon::parse($operation->pro_date)->format('Y-m-d') : '---' }}</td>
                            <td>{{ $operation->created_at ? $operation->created_at->format('H:i') : '---' }}</td>
                            <td>{{ $operation->user->name ?? '---' }}</td>
                            <td>{{ $operation->getOperationTypeText() }}</td>
                            <td>{{ $operation->pro_num ?? '---' }}</td>
                            <td>{{ number_format($operation->pro_value ?? 0, 2) }}</td>
                            <td>{{ $operation->details ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($operations->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $operations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 