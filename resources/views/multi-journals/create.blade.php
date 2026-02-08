@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.journals')
@endsection

@section('content')

    @include('components.breadcrumb', [
        'title' => __('Multi Journal'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Journals'), 'url' => route('multi-journals.index')],
            ['label' => __('Create Multi Journal')]
        ],
    ])
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

        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .table input,
        .table select {
            min-width: 100px;
        }

        .summary-box {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 0.4rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .summary-box.balanced {
            background: #d4edda;
            color: #155724;
        }

        .summary-box.unbalanced {
            background: #f8d7da;
            color: #721c24;
        }

        /* Tom Select dropdown z-index */
        .ts-dropdown,
        .tom-select-dropdown,
        .ts-dropdown-content {
            z-index: 99999 !important;
        }

        /* Remove overflow from table-responsive */
        .table-responsive {
            overflow: visible !important;
        }
    </style>

    <div>
        <div class="card mt-3">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8"><h1 class="card-title">{{ __('Multi Journal Entry') }}</h1></div>
                    <div class="col-md-4">
                    <div class="d-flex justify-content-start mb-3">
                    <button type="submit" form="myForm" class="btn btn-main" id="submitBtn">
                        <span id="submitText">{{ __('Save') }}</span>
                        <span id="submitLoading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> {{ __('Saving...') }}
                        </span>
                    </button>
                    <a href="{{ route('multi-journals.index') }}" class="btn btn-danger ms-2">{{ __('Cancel') }}</a>
                    </div>
                </div>  </div>
            </div>
            <div class="card-body">

              

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form id="myForm" action="{{ route('multi-journals.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pro_type" value="8">

                    <div class="row">
                        <div class="col-md-3">
                            <label>{{ __('Date') }}</label>
                            <input type="date" name="pro_date" class="form-control" 
                                value="{{ old('pro_date', now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-3">
                            <label>{{ __('Serial Number') }}</label>
                            <input type="text" name="pro_num" class="form-control" 
                                value="{{ old('pro_num') }}" placeholder="EX:7645">
                        </div>

                        <div class="col-md-3">
                            <label>{{ __('Employee') }}</label>
                            <select name="emp_id" class="form-control js-tom-select" required>
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" 
                                        {{ old('emp_id', $employees->first()?->id) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->code }} - {{ $emp->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>{{ __('Cost Center') }}</label>
                            <select name="cost_center" class="form-control js-tom-select">
                                <option value="">{{ __('Select Cost Center') }}</option>
                                @foreach ($cost_centers as $cost)
                                    <option value="{{ $cost->id }}" 
                                        {{ old('cost_center', $cost_centers->first()?->id) == $cost->id ? 'selected' : '' }}>
                                        {{ $cost->cname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            <label>{{ __('Statement') }}</label>
                            <input type="text" name="details" class="form-control frst" 
                                value="{{ old('details') }}" required>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered" id="entriesTable">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">{{ __('Debit') }}</th>
                                    <th style="width: 12%;">{{ __('Credit') }}</th>
                                    <th style="width: 40%;">{{ __('Account') }}</th>
                                    <th style="width: 26%;">{{ __('Notes') }}</th>
                                    <th style="width: 10%;">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $oldAccounts = old('account_id', []);
                                    $oldDebits = old('debit', [0]);
                                    $oldCredits = old('credit', [0]);
                                    $oldNotes = old('note', ['']);
                                @endphp
                                @if (count($oldAccounts) > 0)
                                    @foreach ($oldAccounts as $i => $accId)
                                <tr>
                                            <td>
                                                <input type="number" name="debit[]" class="form-control debit" 
                                                    step="0.01" min="0" 
                                                    value="{{ $oldDebits[$i] ?? 0 }}" required>
                                            </td>
                                            <td>
                                                <input type="number" name="credit[]" class="form-control credit" 
                                                    step="0.01" min="0" 
                                                    value="{{ $oldCredits[$i] ?? 0 }}" required>
                                            </td>
                                            <td>
                                                <select name="account_id[]" class="form-control js-tom-select" required>
                                                    <option value="">{{ __('Select Account') }}</option>
                                                    @foreach ($accounts as $acc)
                                                        <option value="{{ $acc->id }}" 
                                                            {{ $accId == $acc->id ? 'selected' : '' }}>
                                                            {{ $acc->code }} - {{ $acc->aname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="note[]" class="form-control" 
                                                    value="{{ $oldNotes[$i] ?? '' }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('Remove') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>
                                            <input type="number" name="debit[]" class="form-control debit" 
                                                step="0.01" min="0" value="0" required>
                                        </td>
                                        <td>
                                            <input type="number" name="credit[]" class="form-control credit" 
                                                step="0.01" min="0" value="0" required>
                                        </td>
                                    <td>
                                            <select name="account_id[]" class="form-control js-tom-select" required>
                                            <option value="">{{ __('Select Account') }}</option>
                                            @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}">
                                                        {{ $acc->code }} - {{ $acc->aname }}
                                                    </option>
                                            @endforeach
                                        </select>
                                    </td>
                                        <td>
                                            <input type="text" name="note[]" class="form-control">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('Remove') }}</button>
                                        </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-secondary mt-2" id="addRow">+ {{ __('Add Row') }}</button>
                    </div>

                    <div class="row mt-4">
                        <div class="col">
                            <label>{{ __('General Notes') }}</label>
                            <input type="text" name="info" class="form-control" value="{{ old('info') }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="summary-box" id="debitSummaryBox">
                                {{ __('Total Debit') }}: <span id="debitTotal">0.00</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-box" id="creditSummaryBox">
                                {{ __('Total Credit') }}: <span id="creditTotal">0.00</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-box" id="diffSummaryBox">
                                {{ __('Difference') }}: <span id="diffTotal">0.00</span>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize Tom Select for all searchable selects
        (function(){
            function initSelect(elem){
                if (window.TomSelect && !elem.tomselect) {
                    const tomSelect = new TomSelect(elem, {
                        create: false,
                        searchField: ['text'],
                        sortField: {field: 'text', direction: 'asc'},
                        dropdownInput: true,
                        plugins: { remove_button: {title: '{{ __('Remove') }}'} },
                        placeholder: elem.getAttribute('placeholder') || '{{ __('Search...') }}'
                    });
                    
                    // Set z-index for dropdown
                    tomSelect.on('dropdown_open', function() {
                        const dropdown = elem.parentElement.querySelector('.ts-dropdown');
                        if (dropdown) {
                            dropdown.style.zIndex = '99999';
                        }
                    });
                }
            }
            
            function initAll(){
                document.querySelectorAll('select.js-tom-select').forEach(initSelect);
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAll);
            } else {
                initAll();
            }
            
            // Re-initialize Tom Select after adding new rows
            window.initTomSelectForNewRows = function() {
                document.querySelectorAll('select.js-tom-select').forEach(select => {
                    if (!select.tomselect) {
                        initSelect(select);
                    }
                });
            };
        })();

        // Table body reference
        const tableBody = document.querySelector('#entriesTable tbody');

        // حساب المجاميع
        function calculateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;

            document.querySelectorAll('.debit').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });

            document.querySelectorAll('.credit').forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });

            const diff = totalDebit - totalCredit;
            const absDiff = Math.abs(diff);
            const isBalanced = absDiff < 0.01;

            // Update totals display
            document.getElementById('debitTotal').textContent = totalDebit.toFixed(2);
            document.getElementById('creditTotal').textContent = totalCredit.toFixed(2);
            document.getElementById('diffTotal').textContent = diff >= 0 ? `+${diff.toFixed(2)}` : diff.toFixed(2);

            // Update visual state
            const diffBox = document.getElementById('diffSummaryBox');
            const debitBox = document.getElementById('debitSummaryBox');
            const creditBox = document.getElementById('creditSummaryBox');

            if (isBalanced) {
                diffBox.className = 'summary-box balanced';
            } else {
                diffBox.className = 'summary-box unbalanced';
            }
        }

        // إضافة صف جديد
        document.getElementById('addRow').addEventListener('click', function() {
            const lastRow = tableBody.querySelector('tr:last-child');
            const debitValue = lastRow.querySelector('.debit').value;
            const creditValue = lastRow.querySelector('.credit').value;
            const accountValue = lastRow.querySelector('select[name="account_id[]"]').value;

            // التحقق من تعبئة الصف الحالي
            if ((!debitValue || parseFloat(debitValue) === 0) && 
                (!creditValue || parseFloat(creditValue) === 0) || !accountValue) {
                alert("{{ __('Please fill the current row before adding a new one.') }}");
                return;
            }

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <input type="number" name="debit[]" class="form-control debit" 
                        step="0.01" min="0" value="0" required>
                </td>
                <td>
                    <input type="number" name="credit[]" class="form-control credit" 
                        step="0.01" min="0" value="0" required>
                </td>
                <td>
                    <select name="account_id[]" class="form-control js-tom-select" required>
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->code }} - {{ $acc->aname }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="note[]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm removeRow">{{ __('Remove') }}</button>
                </td>
            `;

            tableBody.appendChild(newRow);

            // Initialize Tom Select for new row
            if (window.initTomSelectForNewRows) {
                window.initTomSelectForNewRows();
            }

            // Focus on first input of new row
            const newDebitInput = newRow.querySelector('.debit');
            if (newDebitInput) {
                newDebitInput.focus();
                newDebitInput.select();
            }
        });

        // حذف صف
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('removeRow')) {
                const row = e.target.closest('tr');
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                
                if (rows.length <= 1) {
                    alert("{{ __('Cannot delete the first row. At least one row must exist.') }}");
                    return;
                }

                if (confirm('{{ __('Are you sure you want to delete this row?') }}')) {
                    row.remove();
                    calculateTotals();
                }
            }
        });

        // تحديث المجاميع عند تغيير القيم
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('debit') || e.target.classList.contains('credit')) {
                calculateTotals();
            }
        });

        // عند إدخال قيمة في المدين، يصبح الدائن صفر والعكس
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('debit')) {
                const row = e.target.closest('tr');
                const creditInput = row.querySelector('.credit');
                if (creditInput && parseFloat(e.target.value) > 0) {
                    creditInput.value = '0';
                }
            }
            
            if (e.target.classList.contains('credit')) {
                const row = e.target.closest('tr');
                const debitInput = row.querySelector('.debit');
                if (debitInput && parseFloat(e.target.value) > 0) {
                    debitInput.value = '0';
                }
            }
        });

        // Select all text on focus for text inputs
        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="text"]');
            textInputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.select();
                });
            });
        });

        // Form validation before submit
        document.getElementById('myForm').addEventListener('submit', function(e) {
            const totalDebit = parseFloat(document.getElementById('debitTotal').textContent) || 0;
            const totalCredit = parseFloat(document.getElementById('creditTotal').textContent) || 0;
            const diff = totalDebit - totalCredit;
            const absDiff = Math.abs(diff);

            // Check if balanced
            if (absDiff >= 0.01) {
                e.preventDefault();
                const diffText = diff >= 0 ? `+${diff.toFixed(2)}` : diff.toFixed(2);
                alert('{{ __('Debit value must equal credit value.') }} ' + '{{ __('Current difference') }}: ' + diffText);
                return false;
            }

            // Check if at least one entry has value
            let hasValue = false;
            document.querySelectorAll('.debit, .credit').forEach(input => {
                if (parseFloat(input.value) > 0) {
                    hasValue = true;
                }
            });

            if (!hasValue) {
                e.preventDefault();
                alert('{{ __('At least one amount must be entered.') }}');
                return false;
            }

            // Check if all rows have accounts
            let allHaveAccounts = true;
            document.querySelectorAll('select[name="account_id[]"]').forEach(select => {
                if (!select.value) {
                    allHaveAccounts = false;
                }
            });

            if (!allHaveAccounts) {
                e.preventDefault();
                alert('{{ __('An account must be selected for each row.') }}');
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline';
        });

        // Initialize totals on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotals();
        });
    </script>

@endsection
