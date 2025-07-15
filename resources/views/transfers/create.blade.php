@extends('admin.dashboard')

@section('content')

<style>
    .form-group {
        margin-bottom: 1rem;
    }

    label {
        font-weight: 600;
        margin-bottom: 0.4rem;
        display: inline-block;
    }

    .form-control {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
        border-radius: 0.4rem;
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 700;
    }


    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .row + .row {
        margin-top: 1rem;
    }
</style>

<div class="content-wrapper">
    <section class="content">
        <form action="{{ route('transfers.store') }}" method="POST">
            @csrf

            <div class="card bg-white mt-3 col-md-10 container">
                <div class="card-header">
                    <h2 class="card-title">تحويل</h2>
                </div>

                <div class="card-body">

                    {{-- بيانات الفاتورة --}}
                    <div class="row">
                        <div class="col-md-4">
                            <label>رقم العملية</label>
                            <input type="text" name="pro_id" class="form-control" value="{{ $pro_type }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>الرقم الدفتري</label>
                            <input type="text" name="pro_serial" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>رقم الإيصال</label>
                            <input type="text" name="pro_num" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label>التاريخ</label>
                            <input type="date" name="pro_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label>المبلغ</label>
                            <input type="number" step="0.01" name="pro_value" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>البيان</label>
                            <input type="text" name="details" class="form-control">
                        </div>
                    </div>

                    {{-- الحسابات --}}
                    <div class="row">
                        <div class="col-md-6">
                            <label>من حساب</label>
                            <select name="acc2" class="form-control">
                                <option value="">اختر الحساب</option>
                                @foreach ($cashAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>إلى حساب</label>
                            <select name="acc1" class="form-control">
                                <option value="">اختر الحساب</option>
                                @foreach ($bankAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- الموظفين --}}
                    <div class="row">
                        <div class="col-md-6">
                            <label>الموظف</label>
                            <select name="emp_id" class="form-control">
                                <option value="">اختر موظف</option>
                                @foreach ($employeeAccounts as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>مندوب التحصيل</label>
                            <select name="emp2_id" class="form-control">
                                <option value="">اختر مندوب</option>
                                @foreach ($employeeAccounts as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- التكلفة والملاحظات --}}
                    <div class="row">
                        <div class="col-md-6">
                            <label>مركز التكلفة</label>
                            <select name="cost_center" class="form-control">
                                <option value="">بدون مركز تكلفة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>ملاحظات</label>
                            <input type="text" name="info" class="form-control">
                        </div>
                    </div>

                </div>

                <div class="card-footer d-flex justify-content-start">
                    <button type="submit" class="btn btn-primary m-1">تأكيد</button>
                    <button type="reset" class="btn btn-danger m-1">مسح</button>
                </div>
            </div>

        </form>
    </section>
</div>

@endsection
