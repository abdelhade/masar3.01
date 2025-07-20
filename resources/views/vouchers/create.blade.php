@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Vouchers'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Vouchers'), 'url' => route('vouchers.index')],
            ['label' => __('Create')],
        ],
    ])

    @php
        $type = request()->get('type');
        $proTypeMap = [
            'receipt' => 1,
            'payment' => 2,
        ];
        $pro_type = $proTypeMap[$type] ?? null;
    @endphp

    @if ($type === 'receipt' || $type === 'payment')
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid"></div>
            </section>

            <section class="content">
                <form id="myForm" action="{{ route('vouchers.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="pro_type" value="{{ $pro_type }}">

                    <div class="card bg-white col-md-11 container">
                        <div class="card-header">
                            <h3 class="card-title">سند {{ $type === 'receipt' ? 'قبض' : 'دفع' }}</h3>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="pro_id">رقم العملية</label>
                                        <input type="text" name="pro_id" class="form-control"
                                            value="{{ $newProId }}" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="pro_serial">الرقم الدفتري</label>
                                        <input type="text" name="pro_serial" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="pro_num">رقم الإيصال</label>
                                        <input type="text" name="pro_num" class="form-control" value="">
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="pro_date">التاريخ</label>
                                        <input type="date" name="pro_date" class="form-control"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="pro_value">المبلغ</label>
                                        <input type="number" step="0.01" name="pro_value" id="pro_value"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label for="details">البيان</label>
                                        <input type="text" name="details" class="form-control"
                                            placeholder="اكتب البيان بالتفصيل">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="{{ $type === 'receipt' ? 'acc1' : 'acc2' }}">حساب الصندوق</label>
                                        <select name="{{ $type === 'receipt' ? 'acc1' : 'acc2' }}" class="form-control">
                                            <option value="">اختر حساب الصندوق</option>
                                            @foreach ($cashAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="{{ $type === 'payment' ? 'acc1' : 'acc2' }}">
                                            {{ $type === 'payment' ? 'الي الحساب' : 'من الحساب' }}
                                        </label>
                                        <select name="{{ $type === 'payment' ? 'acc1' : 'acc2' }}" class="form-control"
                                            required>
                                            <option value="">اختر حساب</option>
                                            @foreach ($otherAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="emp_id">الموظف</label>
                                        <select name="emp_id" class="form-control">
                                            <option value="">اختر موظف</option>
                                            @foreach ($employeeAccounts as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="emp2_id">مندوب التحصيل</label>
                                        <select name="emp2_id" class="form-control">
                                            <option value="">اختر مندوب</option>
                                            @foreach ($employeeAccounts as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="cost_center">مركز التكلفة</label>
                                        <select name="cost_center" class="form-control">
                                            <option value="">بدون مركز تكلفة</option>
                                            @foreach ($costCenters as $cost)
                                                <option value="{{ $cost->id }}">{{ $cost->cname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="info">ملاحظات</label>
                                        <input type="text" name="info" class="form-control"
                                            placeholder="أدخل أي ملاحظات">
                                    </div>
                                </div>
                            </div>

                            <div class=" mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">حفظ</button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    @endif
@endsection
