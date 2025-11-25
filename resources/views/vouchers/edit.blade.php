@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection

@section('content')
    <div class="content-wrapper">
        <!-- Header -->
        <section class="content-header">
            <div class="container-fluid">

            </div>
        </section>

        <!-- Main content -->
        <section class="content">

            <form id="myForm" action="{{ route('vouchers.update', $voucher->id) }}" method="post">
                @csrf
                @method('PUT')

                <input type="hidden" name="pro_type" value="{{ $voucher->pro_type }}">
                <div class="card card-warning col-md-8 container">
                    <div class="card-header">
                        <h3 class="card-title cake cake-pulse">تعديل سند {{ $type == '1' ? 'قبض' : 'دفع' }}</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li class="bg-red-100 text-zinc-50">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif


                        <div class="row">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pro_id">رقم العملية</label>
                                    <input type="text" name="pro_id" class="form-control"
                                        value="{{ old('pro_id', $voucher->pro_id) }}" readonly>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pro_serial">الرقم الدفتري</label>
                                    <input type="text" name="pro_serial" class="form-control"
                                        value="{{ old('pro_serial', $voucher->pro_serial) }}">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pro_num">رقم الإيصال</label>
                                    <input type="text" name="pro_num" class="form-control"
                                        value="{{ old('pro_num', $voucher->pro_num) }}">
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="pro_date">التاريخ</label>
                                    <input type="date" name="pro_date" class="form-control"
                                        value="{{ $voucher->pro_date }}">
                                </div>
                            </div>
                        </div>
                        <br>
                        <hr>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="pro_value" class="text-lg">المبلغ</label>
                                    <input type="number" step="0.01" name="pro_value" id="pro_value"
                                        class="form-control" value="{{ old('pro_value', $voucher->pro_value) }}">
                                </div>
                            </div>

                            <div class="col-lg-9">
                                <div class="form-group">
                                    <label for="details" class="text-lg">البيان</label>
                                    <input type="text" name="details" class="form-control form-control-lg"
                                        placeholder="اكتب البيان بالتفصيل" value="{{ old('details', $voucher->details) }}">
                                </div>
                            </div>

                        </div>
                        <br>
                        <hr>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="{{ $type == '1' ? 'acc1' : 'acc2' }}">حساب الصندوق</label>
                                    <select name="{{ $type == '1' ? 'acc1' : 'acc2' }}" class="form-control">
                                        <option value="">اختر حساب الصندوق</option>
                                        @foreach ($cashAccounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ ($voucher->pro_type == 1 ? $voucher->acc1 : $voucher->acc2) == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="{{ $type == '1' ? 'acc2' : 'acc1' }}">
                                        {{ $type === '1' ? 'الي الحساب' : 'من الحساب' }} </label>
                                    <select required name="{{ $type == '1' ? 'acc2' : 'acc1' }}" class="form-control">
                                        <option value="">اختر حساب</option>
                                        @foreach ($otherAccounts as $other)
                                            <option value="{{ $other->id }}"
                                                {{ ($type == '1' ? $voucher->acc2 : $voucher->acc1) == $other->id ? 'selected' :   $voucher->acc2 }}>
                                                {{ $other->code }} - {{ $other->aname }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="emp_id">الموظف</label>
                                    <select name="emp_id" class="form-control">
                                        <option value="">اختر موظف</option>
                                        @foreach ($employeeAccounts as $emp)
                                            <option value="{{ $emp->id }}"
                                                {{ old('emp_id', $voucher->emp_id) == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="emp2_id">مندوب التحصيل</label>
                                    <select name="emp2_id" class="form-control">
                                        <option value="">اختر مندوب</option>
                                        @foreach ($employeeAccounts as $emp)
                                            <option value="{{ $emp->id }}"
                                                {{ old('emp2_id', $voucher->emp2_id) == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br>
                        <hr>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="cost_center">مركز التكلفة</label>
                                    <select name="cost_center" class="form-control">
                                        <option value="">بدون مركز تكلفة</option>
                                        @foreach ($costCenters as $center)
                                            <option value="{{ $center->id }}"
                                                {{ old('cost_center', $voucher->cost_center) == $center->id ? 'selected' : '' }}>
                                                {{ $center->cname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-8">
                                <input placeholder="ملاحظات" type="text" name="info" class="form-control"
                                    value="{{ old('info', $voucher->info) }}">
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-main" type="submit">تحديث</button>
                            </div>
                            <div class="col">
                                <button class="btn btn-default" type="button" onclick="window.history.back()">تراجع</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>


        </section>
    </div>
@endsection
