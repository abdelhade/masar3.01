@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.transfers')
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content">
        <form id="myForm" action="{{ route('transfers.update', $transfer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="pro_type" value="{{$pro_type}}">
            <input type="hidden" name="currency_id" id="currency_id" value="{{ $transfer->currency_id ?? 1 }}">
            <input type="hidden" name="currency_rate" id="currency_rate" value="{{ $transfer->currency_rate ?? 1 }}">

            <div class="card col-md-8 container">
                <div class="card-header bg-warning">
                    <h2 class="card-title ">
                        تعديل
                        @switch($type)
                            @case('cash_to_cash') تحويل من صندوق إلى صندوق @break
                            @case('cash_to_bank') تحويل من صندوق إلى بنك @break
                            @case('bank_to_cash') تحويل من بنك إلى صندوق @break
                            @case('bank_to_bank') تحويل من بنك إلى بنك @break
                        @endswitch
                    </h2>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li class="text-danger">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-lg-2">
                            <label>رقم العملية</label>
                            <input type="text" name="pro_id" class="form-control" value="{{ $transfer->pro_id}}" readonly>
                        </div>
                        <div class="col-lg-2">
                            <label>الرقم الدفتري</label>
                            <input type="text" name="pro_serial" class="form-control" value="{{ $transfer->pro_serial}}">
                        </div>
                        <div class="col-lg-2">
                            <label>رقم الإيصال</label>
                            <input type="text" name="pro_num" class="form-control" value="{{ old('pro_num', $transfer->pro_num ?? '') }}" onblur="validateRequired(this)">
                        </div>
                        <div class="col-lg-4">
                            <label>التاريخ</label>
                            <input type="date" name="pro_date" class="form-control"
                                value="{{ old('pro_date', isset($transfer->pro_date) ? date('Y-m-d', strtotime($transfer->pro_date)) : date('Y-m-d')) }}"
                                onblur="validateRequired(this)">
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-lg-{{ isMultiCurrencyEnabled() ? '2' : '3' }}">
                            <label>المبلغ</label>
                            @php
                                // عرض القيمة الأصلية (قبل الضرب في سعر الصرف)
                                $displayValue = $transfer->pro_value;
                                if ($transfer->currency_rate && $transfer->currency_rate > 0) {
                                    $displayValue = $transfer->pro_value / $transfer->currency_rate;
                                }
                            @endphp
                            <input type="number" step="0.01" name="pro_value" id="pro_value" class="form-control" value="{{ old('pro_value', number_format($displayValue, 2, '.', '')) }}" onblur="validateRequired(this)">
                        </div>

                        @if(isMultiCurrencyEnabled())
                            <div class="col-lg-2">
                                <label>العملة</label>
                                <select id="currency_selector" class="form-control">
                                    <option value="">اختر العملة</option>
                                    @foreach($allCurrencies as $currency)
                                        <option value="{{ $currency->id }}" 
                                                data-rate="{{ $currency->latestRate->rate ?? 1 }}"
                                                data-name="{{ $currency->name }}"
                                                {{ $transfer->currency_id == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->name }} ({{ number_format($currency->latestRate->rate ?? 1, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <label>القيمة المحولة</label>
                                <input type="text" id="converted_amount" readonly 
                                    class="form-control bg-light" 
                                    value="{{ number_format($transfer->pro_value, 2) }}" 
                                    placeholder="0.00">
                            </div>
                        @endif

                        <div class="col-lg-{{ isMultiCurrencyEnabled() ? '6' : '9' }}">
                            <label>البيان</label>
                            <input type="text" name="details" class="form-control" value="{{ old('details', $transfer->details ?? '') }}" onblur="validateRequired(this)">
                        </div>
                    </div>

                    <hr><br>

                    @php
                        $types = [
                            'cash_to_cash' => ['الصندوق', 'الصندوق'],
                            'cash_to_bank' => ['الصندوق', 'البنك'],
                            'bank_to_cash' => ['البنك', 'الصندوق'],
                            'bank_to_bank' => ['البنك', 'البنك'],
                        ];
                        [$acc1_text, $acc2_text] = $types[$type] ?? ['حساب 1', 'حساب 2'];
                    @endphp

                    <div class="row">
                        <div class="col-lg-6">
                            <label>من حساب: {{ $acc1_text }} <span class="badge badge-outline-info">دائن</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <select name="acc1" required id="acc1" class="form-control js-tom-select" style="flex: 1;" onblur="validateRequired(this); checkSameAccounts();">
                                    <option value="">اختر الحساب</option>
                                    @foreach ($fromAccounts as $account)
                                        <option value="{{ $account->id }}"
                                            data-balance="{{ $account->balance }}"
                                            data-currency-id="{{ $account->currency_id }}"
                                            data-currency-name="{{ $account->currency?->name ?? '' }}"
                                            {{ old('acc1', $transfer->acc1 ?? '') == $account->id ? 'selected' : '' }}>
                                            {{ $account->aname }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(isMultiCurrencyEnabled())
                                    <span id="acc1_currency" class="badge bg-info text-white px-3 py-2" style="min-width: 60px; font-size: 14px;">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label>إلى حساب: {{ $acc2_text }} <span class="badge badge-outline-info">مدين</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <select name="acc2" id="acc2" required class="form-control js-tom-select" style="flex: 1;" onblur="validateRequired(this); ">
                                    <option value="">اختر الحساب</option>
                                    @foreach ($toAccounts as $account)
                                        <option value="{{ $account->id }}"
                                            data-balance="{{ $account->balance }}"
                                            data-currency-id="{{ $account->currency_id }}"
                                            data-currency-name="{{ $account->currency?->name ?? '' }}"
                                            {{ old('acc2', $transfer->acc2 ?? '') == $account->id ? ' selected ' : '' }}>
                                            {{ $account->aname }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(isMultiCurrencyEnabled())
                                    <span id="acc2_currency" class="badge bg-info text-white px-3 py-2" style="min-width: 60px; font-size: 14px;">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-lg-6">
                            <label>الموظف</label>
                            <select name="emp_id" class="form-control">
                                <option value="">اختر موظف</option>
                                @foreach ($employeeAccounts as $emp)
                                    <option value="{{ $emp->id }}" {{ old('emp_id', $transfer->emp_id ?? '') == $emp->id ? ' selected ' : '' }}>
                                        {{ $emp->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label>مندوب التحصيل</label>
                            <select name="emp2_id" class="form-control">
                                <option value="">اختر مندوب</option>
                                @foreach ($employeeAccounts as $emp)
                                    <option value="{{ $emp->id }}" {{ old('emp2_id', $transfer->emp2_id ?? '') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-lg-6">
                            <label>مركز التكلفة</label>
                            <select name="cost_center" class="form-control">
                                <option value="">بدون مركز تكلفة</option>
                                @if(!empty($costCenters) && count($costCenters))
                                    @foreach($costCenters as $cc)
                                        <option value="{{ $cc->id }}" {{ old('cost_center', $transfer->cost_center ?? '') == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label>ملاحظات</label>
                            <input type="text" name="info" class="form-control" value="{{ old('info', $transfer->info ?? '') }}">
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-main" type="submit">تأكيد</button>
                        </div>
                        <div class="col">
                            <button class="btn btn-danger" type="reset">مسح</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
function validateRequired(input) {
    if (!input.value.trim()) {
        input.classList.add('is-invalid');
        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'invalid-feedback';
            errorMsg.innerText = 'هذا الحقل مطلوب';
            input.parentNode.appendChild(errorMsg);
        }
    } else {
        input.classList.remove('is-invalid');
        const next = input.nextElementSibling;
        if (next && next.classList.contains('invalid-feedback')) {
            next.remove();
        }
    }
}

function checkSameAccounts() {
    let acc1 = document.getElementById('acc1').value;
    let acc2 = document.getElementById('acc2').value;
    if (acc1 && acc2 && acc1 === acc2) {
        alert("لا يمكن اختيار نفس الحساب في الحقلين");
        document.getElementById('acc1').value = '';
        document.getElementById('acc2').value = '';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Initialize Tom Select for searchable selects
    function initTomSelect() {
        if (window.TomSelect) {
            // Initialize acc1
            const acc1Select = document.getElementById('acc1');
            if (acc1Select && !acc1Select.tomselect) {
                const acc1TomSelect = new TomSelect(acc1Select, {
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
                acc1TomSelect.on('dropdown_open', function() {
                    const dropdown = acc1Select.parentElement.querySelector('.ts-dropdown');
                    if (dropdown) {
                        dropdown.style.zIndex = '99999';
                    }
                });
            }

            // Initialize acc2
            const acc2Select = document.getElementById('acc2');
            if (acc2Select && !acc2Select.tomselect) {
                const acc2TomSelect = new TomSelect(acc2Select, {
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
                acc2TomSelect.on('dropdown_open', function() {
                    const dropdown = acc2Select.parentElement.querySelector('.ts-dropdown');
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

        const acc1El = document.getElementById('acc1');
        const acc2El = document.getElementById('acc2');
        const acc1Badge = document.getElementById('acc1_currency');
        const acc2Badge = document.getElementById('acc2_currency');

        if (acc1El && acc1Badge) {
            const acc1Symbol = getAccountCurrencySymbol(acc1El);
            acc1Badge.textContent = acc1Symbol;
        }

        if (acc2El && acc2Badge) {
            const acc2Symbol = getAccountCurrencySymbol(acc2El);
            acc2Badge.textContent = acc2Symbol;
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
        const acc1El = document.getElementById('acc1');
        const acc2El = document.getElementById('acc2');

        if (!acc1El || !acc2El) {
            return true; // Allow submission if elements not found
        }

        // الحصول على عملة الحسابين
        const acc1CurrencyId = getAccountCurrencyId(acc1El);
        const acc2CurrencyId = getAccountCurrencyId(acc2El);

        // تحديث عرض العملات
        updateCurrencyBadges();

        // التحقق من أن الحسابين محددين
        if (!acc1CurrencyId || !acc2CurrencyId) {
            // إذا لم يتم اختيار الحسابين، استخدم القيم الافتراضية
            document.getElementById('currency_id').value = '1';
            document.getElementById('currency_rate').value = '1';
            return true;
        }

        // التحقق من تطابق العملات
        if (String(acc1CurrencyId) !== String(acc2CurrencyId)) {
            alert('عذراً، يجب أن يكون للحسابين نفس العملة لإتمام التحويل.');
            return false;
        }

        // إذا كانت العملات متطابقة، تعيين currency_id و currency_rate
        const currencyRates = @json($allCurrencies->mapWithKeys(fn($c) => [$c->id => $c->latestRate->rate ?? 1]));
        const currencyRate = currencyRates[acc1CurrencyId] || 1;

        document.getElementById('currency_id').value = acc1CurrencyId;
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

    // Initial check on page load
    checkAndUpdateCurrency();
    
    // Update currency badges on page load
    updateCurrencyBadges();
    
    // Calculate converted amount on page load
    calculateConvertedAmount();
});
</script>
@endpush
@endsection
