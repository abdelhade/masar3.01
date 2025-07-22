@extends('admin.dashboard')

@section('content')
<div class="container">
    <h2>فتح شيفت جديد</h2>

    <form action="{{ route('pos-shifts.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>الرصيد الافتتاحي</label>
            <input type="number" step="0.01" name="opening_balance" class="form-control" required>
        </div>

        <div class="form-group mt-3">
            <button class="btn btn-success">فتح الشيفت</button>
        </div>
    </form>
</div>
@endsection
