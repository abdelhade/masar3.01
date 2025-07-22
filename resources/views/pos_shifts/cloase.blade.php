@extends('admin.dashboard')

@section('content')
<div class="container">
    <h2>إغلاق الشيفت</h2>
    <p><strong>الكاشير:</strong> {{ $shift->user->name ?? 'غير معروف' }}</p>
    <p><strong>الرصيد الافتتاحي:</strong> {{ $shift->opening_balance }}</p>
    <p><strong>بداية الشيفت:</strong> {{ $shift->opened_at }}</p>

    <form method="POST" action="{{ route('pos-shifts.close.confirm', $shift->id) }}">
        @csrf

        <div class="form-group">
            <label>الرصيد الختامي</label>
            <input type="number" step="0.01" name="closing_balance" class="form-control" required>
        </div>

        <div class="form-group mt-3">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>

        <button class="btn btn-danger mt-3">تأكيد إغلاق الشيفت</button>
    </form>
</div>
@endsection
