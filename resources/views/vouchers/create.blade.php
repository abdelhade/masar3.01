@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection

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
            'exp-payment' => 3,
        ];
        $pro_type = $proTypeMap[$type] ?? null;
    @endphp

    @if ($type === 'receipt' || $type === 'payment' || $type === 'exp-payment')
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
                            <h1 class="h1">سند @switch($type)
                                    @case('receipt')
                                        قبض عام
                                    @break

                                    `
                                    @case('exp-payment')
                                        دفع عام
                                    @break

                                    @default
                                        دفع مصروف
                                @endswitch
                            </h1>
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

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="pro_date">التاريخ</label>
                                        <input type="date" name="pro_date" class="form-control"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="pro_value">المبلغ</label>
                                        <input type="number" step="0.01" name="pro_value" id="pro_value"
                                            class="form-control frst">
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="form-group">
                                        <label for="details">البيان</label>
                                        <input type="text" name="details" class="form-control"
                                            placeholder="اكتب البيان بالتفصيل">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="cash_account">حساب الصندوق</label>
                                        <select name="{{ $type === 'receipt' ? 'acc1' : 'acc2' }}"
                                            typess="{{ $type }}" id="cash_account" class="form-control">

                                            @foreach ($cashAccounts as $account)
                                                <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                                    {{ $account->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col">قبل : <span class="text-primary"
                                                id="cash_before">{{ $cashAccounts->first()->balance ?? 0 }}</span></div>
                                        <div class="col">بعد : <span class="text-primary" id="cash_after">00.00</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label
                                            for="other_account">{{ $type === 'receipt' ? 'الحساب الدائن' : 'الحساب المدين' }}</label>
                                        <select name="{{ $type === 'receipt' ? 'acc2' : 'acc1' }}" id="other_account"
                                            class="form-control">
                                            <option value="">اختر الحساب</option>
                                            @if ($type == 'exp-payment')
                                                @foreach ($expensesAccounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->balance }}">
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            @else
                                                @foreach ($otherAccounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->balance }}">
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col">قبل : <span
                                                id="acc_before">{{ $type === 'exp-payment' ? ($expensesAccounts->first()->balance ?? 0) : ($otherAccounts->first()->balance ?? 0) }}</span></div>

                                        <div class="col">بعد : <span id="acc_after">00.00</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
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

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="emp2_id">مندوب التحصيل</label>
                                        <select name="emp2_id" class="form-control">
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
                                        <label for="project_id">المشروع</label>
                                        <select name="project_id" class="form-control">
                                            <option value="">اختر المشروع</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label for="info">ملاحظات</label>
                                        <input type="text" name="info" class="form-control"
                                            placeholder="أدخل أي ملاحظات">
                                    </div>
                                </div>

                                <x-branches::branch-select :branches="$branches" />
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-main btn-lg">حفظ</button>
                            </div>
                        </div>
                </form>
            </section>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Cash account balance calculation
                const cashAccount = document.getElementById("cash_account");
                const cashBefore = document.getElementById("cash_before");
                const cashAfter = document.getElementById("cash_after");
                const proValue = document.getElementById("pro_value");

                // Other account balance calculation
                const otherAccount = document.getElementById("other_account");
                const accBefore = document.getElementById("acc_before");
                const accAfter = document.getElementById("acc_after");

                // Update initial balances
                function updateBalances() {
                    const cashBalance = parseFloat(cashAccount.options[cashAccount.selectedIndex].dataset.balance) || 0;
                    const otherBalance = parseFloat(otherAccount.options[otherAccount.selectedIndex].dataset.balance) ||
                        0;
                    const value = parseFloat(proValue.value) || 0;

                    // For receipt: cash increases, other account decreases
                    // For payment: cash decreases, other account increases
                    if ("{{ $type }}" === "receipt") {
                        cashAfter.textContent = (cashBalance + value).toFixed(2);
                        accAfter.textContent = (otherBalance - value).toFixed(2);
                    } else {
                        cashAfter.textContent = (cashBalance - value).toFixed(2);
                        accAfter.textContent = (otherBalance + value).toFixed(2);
                    }

                    cashBefore.textContent = cashBalance.toFixed(2);
                    accBefore.textContent = otherBalance.toFixed(2);
                }

                // Event listeners
                cashAccount.addEventListener("change", updateBalances);
                otherAccount.addEventListener("change", updateBalances);
                proValue.addEventListener("input", updateBalances);

                // Initial update
                updateBalances();
            });
        </script>
    @endif
@endsection
