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
            'payment' => 101,
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
                                        <label for="cash_account">{{ $type === 'receipt' ? 'الصندوق / البنك' : 'حساب الصندوق' }}</label>
                                        <select name="{{ $type === 'receipt' ? 'acc1' : 'acc2' }}"
                                            typess="{{ $type }}" id="cash_account" class="form-control js-tom-select">
                                            @if ($type === 'receipt')
                                                <optgroup label="الصناديق">
                                                    @foreach ($cashAccounts as $account)
                                                        <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                                            {{ $account->aname }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                                @if ($bankAccounts->isNotEmpty())
                                                    <optgroup label="البنوك">
                                                        @foreach ($bankAccounts as $account)
                                                            <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                                                {{ $account->aname }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            @else
                                                @foreach ($cashAccounts as $account)
                                                    <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col">قبل : <span class="text-primary"
                                                id="cash_before">{{ ($type === 'receipt' && ($cashAccounts->isNotEmpty() || $bankAccounts->isNotEmpty())) ? ($cashAccounts->first()->balance ?? $bankAccounts->first()->balance ?? 0) : ($cashAccounts->first()->balance ?? 0) }}</span></div>
                                        <div class="col">بعد : <span class="text-primary" id="cash_after">00.00</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label
                                            for="other_account">{{ $type === 'receipt' ? 'الحساب الدائن' : 'الحساب المدين' }}</label>
                                        <select name="{{ $type === 'receipt' ? 'acc2' : 'acc1' }}" id="other_account"
                                            class="form-control js-tom-select">
                                            <option value="">اختر الحساب</option>
                                            @if ($type == 'exp-payment')
                                                @foreach ($expensesAccounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->balance }}">
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            @elseif ($type == 'payment')
                                                @foreach ($paymentExpensesAccounts as $account)
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
                                                id="acc_before">
                                                @if ($type === 'exp-payment')
                                                    {{ $expensesAccounts->first()->balance ?? 0 }}
                                                @elseif ($type === 'payment')
                                                    {{ $paymentExpensesAccounts->first()->balance ?? 0 }}
                                                @else
                                                    {{ $otherAccounts->first()->balance ?? 0 }}
                                                @endif
                                            </span></div>

                                        <div class="col">بعد : <span id="acc_after">00.00</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="emp_id">الموظف</label>
                                        <select name="emp_id" class="form-control">
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
                    // Get cash balance
                    let cashBalance = 0;
                    if (cashAccount.tomselect) {
                        // Using Tom Select
                        const selectedValue = cashAccount.tomselect.getValue();
                        if (selectedValue) {
                            const selectedOption = cashAccount.querySelector(`option[value="${selectedValue}"]`);
                            if (selectedOption && selectedOption.dataset.balance) {
                                cashBalance = parseFloat(selectedOption.dataset.balance);
                            }
                        }
                    } else {
                        // Using native select
                        const selectedCashOption = cashAccount.options[cashAccount.selectedIndex];
                        if (selectedCashOption && selectedCashOption.dataset.balance) {
                            cashBalance = parseFloat(selectedCashOption.dataset.balance);
                        }
                    }
                    
                    // Get other account balance
                    let otherBalance = 0;
                    if (otherAccount.tomselect) {
                        // Using Tom Select
                        const selectedValue = otherAccount.tomselect.getValue();
                        if (selectedValue) {
                            const selectedOption = otherAccount.querySelector(`option[value="${selectedValue}"]`);
                            if (selectedOption && selectedOption.dataset.balance) {
                                otherBalance = parseFloat(selectedOption.dataset.balance);
                            }
                        }
                    } else {
                        // Using native select
                        const selectedOtherOption = otherAccount.options[otherAccount.selectedIndex];
                        if (selectedOtherOption && selectedOtherOption.dataset.balance) {
                            otherBalance = parseFloat(selectedOtherOption.dataset.balance);
                        }
                    }
                    
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

                // Initialize Tom Select for searchable selects
                function initTomSelect() {
                    if (window.TomSelect) {
                        // Initialize cash_account
                        const cashAccountSelect = document.getElementById('cash_account');
                        if (cashAccountSelect && !cashAccountSelect.tomselect) {
                            const cashTomSelect = new TomSelect(cashAccountSelect, {
                                create: false,
                                searchField: ['text'],
                                sortField: {field: 'text', direction: 'asc'},
                                dropdownInput: true,
                                placeholder: 'ابحث...',
                                onItemAdd: function() {
                                    updateBalances();
                                },
                                onItemRemove: function() {
                                    updateBalances();
                                }
                            });
                            
                            // Set z-index for dropdown
                            cashTomSelect.on('dropdown_open', function() {
                                const dropdown = cashAccountSelect.parentElement.querySelector('.ts-dropdown');
                                if (dropdown) {
                                    dropdown.style.zIndex = '99999';
                                }
                            });
                        }

                        // Initialize other_account
                        const otherAccountSelect = document.getElementById('other_account');
                        if (otherAccountSelect && !otherAccountSelect.tomselect) {
                            const otherTomSelect = new TomSelect(otherAccountSelect, {
                                create: false,
                                searchField: ['text'],
                                sortField: {field: 'text', direction: 'asc'},
                                dropdownInput: true,
                                placeholder: 'ابحث عن الحساب...',
                                onItemAdd: function() {
                                    updateBalances();
                                },
                                onItemRemove: function() {
                                    updateBalances();
                                }
                            });
                            
                            // Set z-index for dropdown
                            otherTomSelect.on('dropdown_open', function() {
                                const dropdown = otherAccountSelect.parentElement.querySelector('.ts-dropdown');
                                if (dropdown) {
                                    dropdown.style.zIndex = '99999';
                                }
                            });
                        }
                    } else {
                        // Retry if Tom Select not loaded yet
                        setTimeout(initTomSelect, 100);
                    }
                }
                
                // Initialize Tom Select
                initTomSelect();

                // Event listeners for native selects (if not using Tom Select)
                if (!cashAccount.tomselect) {
                    cashAccount.addEventListener("change", updateBalances);
                }
                if (!otherAccount.tomselect) {
                    otherAccount.addEventListener("change", updateBalances);
                }
                proValue.addEventListener("input", updateBalances);

                // Initial update
                updateBalances();
            });
        </script>
    @endif
@endsection
