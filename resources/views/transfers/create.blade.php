@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.transfers')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => $pageTitle ?? 'إضافة تحويل نقدي',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'التحويلات النقدية', 'url' => route('transfers.index')],
            ['label' => 'إضافة جديد'],
        ],
    ])

    <div class="content-wrapper">
        <section class="content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- الـ Form الأساسي --}}
            <form id="myForm" action="{{ route('transfers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pro_type" value="{{ $pro_type }}">
                <input type="hidden" name="currency_id" id="currency_id" value="1">
                <input type="hidden" name="currency_rate" id="currency_rate" value="1">

                <div class="row">
                    {{-- عمود الفورم الرئيسي --}}
                    <div class="col-lg-9">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h2 class="card-title">{{ $pageTitle ?? 'إضافة تحويل نقدي' }}</h2>
                            </div>

                            <div class="card-body">
                                {{-- بيانات الفاتورة --}}
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">رقم العملية</label>
                                        <input type="text" name="pro_id" class="form-control"
                                            value="{{ $newProId }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">الرقم الدفتري</label>
                                        <input type="text" name="pro_serial" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">رقم الإيصال</label>
                                        <input type="text" name="pro_num" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">التاريخ</label>
                                        <input type="date" name="pro_date" class="form-control"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-{{ isMultiCurrencyEnabled() ? '2' : '4' }}">
                                        <label class="form-label">المبلغ</label>
                                        <input type="number" step="0.01" name="pro_value" id="pro_value"
                                            class="form-control">
                                    </div>

                                    @if(isMultiCurrencyEnabled())
                                        <div class="col-md-2">
                                            <label class="form-label">العملة</label>
                                            <select id="currency_selector" class="form-control">
                                                <option value="">اختر العملة</option>
                                                @foreach($allCurrencies as $currency)
                                                    <option value="{{ $currency->id }}" 
                                                            data-rate="{{ $currency->latestRate->rate ?? 1 }}"
                                                            data-name="{{ $currency->name }}">
                                                        {{ $currency->name }} ({{ number_format($currency->latestRate->rate ?? 1, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">القيمة المحولة</label>
                                            <input type="text" id="converted_amount" readonly 
                                                class="form-control bg-light" placeholder="0.00">
                                        </div>
                                    @endif

                                    <div class="col-md-{{ isMultiCurrencyEnabled() ? '2' : '4' }}">
                                        <label class="form-label">البيان</label>
                                        <input type="text" name="details" class="form-control">
                                    </div>
                                </div>

                                {{-- الحسابات --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">من حساب</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <select name="acc2" id="from_account" class="form-control js-tom-select" style="flex: 1;">
                                                <option value="">اختر الحساب</option>
                                                @foreach ($fromAccounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->balance }}"
                                                        data-currency-id="{{ $account->currency_id }}"
                                                        data-currency-name="{{ $account->currency?->name ?? '' }}">
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(isMultiCurrencyEnabled())
                                                <span id="from_account_currency" class="badge bg-info text-white px-3 py-2" style="min-width: 60px; font-size: 14px;">—</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">إلى حساب</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <select name="acc1" id="to_account" class="form-control js-tom-select" style="flex: 1;">
                                                <option value="">اختر الحساب</option>
                                                @foreach ($toAccounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->balance }}"
                                                        data-currency-id="{{ $account->currency_id }}"
                                                        data-currency-name="{{ $account->currency?->name ?? '' }}">
                                                        {{ $account->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(isMultiCurrencyEnabled())
                                                <span id="to_account_currency" class="badge bg-info text-white px-3 py-2" style="min-width: 60px; font-size: 14px;">—</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- الموظفين --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">الموظف</label>
                                        <select name="emp_id" class="form-control">
                                            <option value="">اختر موظف</option>
                                            @foreach ($employeeAccounts as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">مندوب التحصيل</label>
                                        <select name="emp2_id" class="form-control">
                                            <option value="">اختر مندوب</option>
                                            @foreach ($employeeAccounts as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- التكلفة والملاحظات --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">مركز التكلفة</label>
                                        <select name="cost_center" class="form-control">
                                            <option value="">بدون مركز تكلفة</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">ملاحظات</label>
                                        <input type="text" name="info" class="form-control">
                                    </div>
                                </div>

                                {{-- Branch Select Component --}}
                                <x-branches::branch-select :branches="$branches" />
                            </div>

                            <div class="card-footer d-flex justify-content-start">
                                <button type="submit" class="btn btn-main m-1">
                                    <i class="ki-outline ki-check fs-3"></i>
                                    تأكيد
                                </button>
                                <button type="reset" class="btn btn-danger m-1">
                                    <i class="ki-outline ki-cross fs-3"></i>
                                    مسح
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize Tom Select for searchable selects
            function initTomSelect() {
                if (window.TomSelect) {
                    // Initialize from_account
                    const fromAccountSelect = document.getElementById('from_account');
                    if (fromAccountSelect && !fromAccountSelect.tomselect) {
                        const fromTomSelect = new TomSelect(fromAccountSelect, {
                            create: false,
                            searchField: ['text'],
                            sortField: {
                                field: 'text',
                                direction: 'asc'
                            },
                            dropdownInput: true,
                            placeholder: 'ابحث...',
                            onItemAdd: function() {
                                checkAndUpdateCurrency();
                            },
                            onItemRemove: function() {
                                checkAndUpdateCurrency();
                            }
                        });

                        // Set z-index for dropdown
                        fromTomSelect.on('dropdown_open', function() {
                            const dropdown = fromAccountSelect.parentElement.querySelector('.ts-dropdown');
                            if (dropdown) {
                                dropdown.style.zIndex = '99999';
                            }
                        });
                    }

                    // Initialize to_account
                    const toAccountSelect = document.getElementById('to_account');
                    if (toAccountSelect && !toAccountSelect.tomselect) {
                        const toTomSelect = new TomSelect(toAccountSelect, {
                            create: false,
                            searchField: ['text'],
                            sortField: {
                                field: 'text',
                                direction: 'asc'
                            },
                            dropdownInput: true,
                            placeholder: 'ابحث عن الحساب...',
                            onItemAdd: function() {
                                checkAndUpdateCurrency();
                            },
                            onItemRemove: function() {
                                checkAndUpdateCurrency();
                            }
                        });

                        // Set z-index for dropdown
                        toTomSelect.on('dropdown_open', function() {
                            const dropdown = toAccountSelect.parentElement.querySelector('.ts-dropdown');
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

            // Function to get currency ID from account
            function getAccountCurrencyId(accountElement) {
                if (!accountElement) {
                    return null;
                }

                let selectedOption = null;

                if (accountElement.tomselect) {
                    // Using Tom Select
                    const selectedValue = accountElement.tomselect.getValue();
                    if (selectedValue) {
                        selectedOption = accountElement.querySelector(`option[value="${selectedValue}"]`);
                    }
                } else {
                    // Using native select
                    const selectedIndex = accountElement.selectedIndex;
                    if (selectedIndex >= 0) {
                        selectedOption = accountElement.options[selectedIndex];
                    }
                }

                if (selectedOption) {
                    // Try dataset first, then getAttribute as fallback
                    const currencyId = selectedOption.dataset.currencyId || selectedOption.getAttribute('data-currency-id');
                    return currencyId ? String(currencyId) : null;
                }

                return null;
            }

            // Function to get currency symbol from account
            function getAccountCurrencySymbol(accountElement) {
                if (!accountElement) {
                    return null;
                }

                let selectedOption = null;

                if (accountElement.tomselect) {
                    const selectedValue = accountElement.tomselect.getValue();
                    if (selectedValue) {
                        selectedOption = accountElement.querySelector(`option[value="${selectedValue}"]`);
                    }
                } else {
                    const selectedIndex = accountElement.selectedIndex;
                    if (selectedIndex >= 0) {
                        selectedOption = accountElement.options[selectedIndex];
                    }
                }

                if (selectedOption) {
                    const currencyName = selectedOption.dataset.currencyName || selectedOption.getAttribute('data-currency-name');
                    return currencyName || '—';
                }

                return '—';
            }

            // Function to update currency badge display
            function updateCurrencyBadges() {
                const multiCurrencyEnabled = {{ isMultiCurrencyEnabled() ? 'true' : 'false' }};
                
                if (!multiCurrencyEnabled) {
                    return;
                }

                const fromAccountEl = document.getElementById('from_account');
                const toAccountEl = document.getElementById('to_account');
                const fromBadge = document.getElementById('from_account_currency');
                const toBadge = document.getElementById('to_account_currency');

                if (fromAccountEl && fromBadge) {
                    const fromSymbol = getAccountCurrencySymbol(fromAccountEl);
                    fromBadge.textContent = fromSymbol;
                }

                if (toAccountEl && toBadge) {
                    const toSymbol = getAccountCurrencySymbol(toAccountEl);
                    toBadge.textContent = toSymbol;
                }
            }

            // Function to check currency match and update hidden fields
            function checkAndUpdateCurrency() {
                // التحقق من تفعيل تعدد العملات أولاً
                const multiCurrencyEnabled = {{ isMultiCurrencyEnabled() ? 'true' : 'false' }};
                
                if (!multiCurrencyEnabled) {
                    // إذا كان تعدد العملات غير مفعل، استخدم القيم الافتراضية
                    document.getElementById('currency_id').value = '1';
                    document.getElementById('currency_rate').value = '1';
                    return true;
                }

                // الحصول على عناصر الحسابين
                const fromAccountEl = document.getElementById('from_account');
                const toAccountEl = document.getElementById('to_account');

                if (!fromAccountEl || !toAccountEl) {
                    return true; // Allow submission if elements not found
                }

                // الحصول على عملة الحسابين
                const fromCurrencyId = getAccountCurrencyId(fromAccountEl);
                const toCurrencyId = getAccountCurrencyId(toAccountEl);

                // تحديث عرض العملات
                updateCurrencyBadges();

                // التحقق من أن الحسابين محددين
                if (!fromCurrencyId || !toCurrencyId) {
                    // إذا لم يتم اختيار الحسابين، استخدم القيم الافتراضية
                    document.getElementById('currency_id').value = '1';
                    document.getElementById('currency_rate').value = '1';
                    return true;
                }

                // التحقق من تطابق العملات
                if (String(fromCurrencyId) !== String(toCurrencyId)) {
                    alert('عذراً، يجب أن يكون للحسابين نفس العملة لإتمام التحويل.');
                    return false;
                }

                // إذا كانت العملات متطابقة، تعيين currency_id و currency_rate
                const currencyRates = @json($allCurrencies->mapWithKeys(fn($c) => [$c->id => $c->latestRate->rate ?? 1]));
                const currencyRate = currencyRates[fromCurrencyId] || 1;

                document.getElementById('currency_id').value = fromCurrencyId;
                document.getElementById('currency_rate').value = currencyRate;

                return true;
            }

            // Currency conversion calculation
            const proValue = document.getElementById('pro_value');
            const currencySelector = document.getElementById('currency_selector');
            
            if (proValue) {
                proValue.addEventListener('input', calculateConvertedAmount);
            }
            
            if (currencySelector) {
                currencySelector.addEventListener('change', calculateConvertedAmount);
            }

            function calculateConvertedAmount() {
                const multiCurrencyEnabled = {{ isMultiCurrencyEnabled() ? 'true' : 'false' }};
                
                if (!multiCurrencyEnabled) {
                    return;
                }

                const amount = parseFloat(proValue.value) || 0;
                const currencySelector = document.getElementById('currency_selector');
                const convertedAmountField = document.getElementById('converted_amount');
                
                if (!currencySelector || !convertedAmountField) {
                    return;
                }

                const selectedOption = currencySelector.options[currencySelector.selectedIndex];
                const rate = parseFloat(selectedOption.dataset.rate) || 1;
                const currencyId = currencySelector.value || '1';
                
                const convertedAmount = amount * rate;
                convertedAmountField.value = convertedAmount.toFixed(2);
                
                // Update hidden fields
                document.getElementById('currency_id').value = currencyId;
                document.getElementById('currency_rate').value = rate;
            }

            // إضافة event listener على submit
            const form = document.getElementById('myForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!checkAndUpdateCurrency()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });
            }
        });
    </script>
@endpush
