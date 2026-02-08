@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.vouchers')
@endsection

@section('content')
    <style>
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-control {
            padding: 0.75rem;
            font-size: 1rem;
            border-radius: 0.5rem;
        }

        label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .btn {
            padding: 0.5rem 1rem;
        }

        .card {
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        #entriesTable select,
        #entriesTable input {
            min-width: 120px;
        }

        .table th {
            background-color: #f8f9fa;
        }
    </style>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="h4 font-weight-bold mb-0">{{ __("Operation Type") }}: {{ $ptext }}</h3>
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

            <form id="myForm" action="{{ route('multi-vouchers.update', $operHead->id) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="pro_type" value="{{ $pro_type }}">

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>{{ __("Invoice Number") }}</label>
                            <input type="text" name="pro_id" class="form-control" value="{{ $operHead->pro_id }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>SN</label>
                            <input type="text" name="pro_serial" class="form-control" value="{{ $operHead->pro_serial }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __("Date") }}</label>
                            <input type="date" name="pro_date" class="form-control" value="{{ $operHead->pro_date }}">
                        </div>
                    </div>
                </div>

                @php
                    $account1_types = ['32', '40', '41', '46', '47', '50', '53', '55'];
                    $account2_types = ['33', '42', '43', '44', '45', '48', '49', '51', '52', '54'];
                @endphp

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __("From Account") }}</label>
                            @if (in_array($pro_type, $account1_types))
                                <select name="acc1[]" class="form-control js-tom-select js-balance-source" required>
                                    @foreach ($accounts1 as $acc1)
                                        <option value="{{ $acc1->id }}" data-balance="{{ $acc1->balance ?? 0 }}"
                                            {{ $mainEntry && $mainEntry->account_id == $acc1->id ? 'selected' : '' }}>
                                            {{ $acc1->code }} _ {{ $acc1->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif (in_array($pro_type, $account2_types))
                                <select name="acc2[]" class="form-control js-tom-select js-balance-source" required>
                                    @foreach ($accounts2 as $acc2)
                                        <option value="{{ $acc2->id }}" data-balance="{{ $acc2->balance ?? 0 }}"
                                            {{ $mainEntry && $mainEntry->account_id == $acc2->id ? 'selected' : '' }}>
                                            {{ $acc2->code }} _ {{ $acc2->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small class="text-muted d-block mt-1">
                                {{ __('Balance before') }}: <span id="topBalanceBefore">0.00</span>
                                &nbsp;|&nbsp;
                                {{ __('After') }}: <span id="topBalanceAfter">0.00</span>
                            </small>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __("Employee") }}</label>
                            <select name="emp_id" class="form-control js-tom-select" required>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $emp->id == $operHead->emp_id ? 'selected' : '' }}>
                                        {{ $emp->code }} _ {{ $emp->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label>{{ __("Statement") }}</label>
                            <input name="details" required type="text" class="form-control frst" value="{{ $operHead->details }}">
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="entriesTable" class="table table-striped table-bordered mb-0" style="min-width: 1200px;">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th>{{ __("Amount") }}</th>
                                <th>{{ __("Account") }}</th>
                                <th>{{ __("Notes") }}</th>
                                <th>{{ __("Action") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subEntries as $index => $entry)
                                <tr>
                                    <td><input type="number" name="sub_value[]" class="form-control debit" step="0.01"
                                            value="{{ $entry->debit ?: $entry->credit }}"></td>
                                    <td>
                                        @if (in_array($pro_type, $account2_types))
                                            <select name="acc1[]" class="form-control js-tom-select js-balance-dest" required>
                                                <option value="">__ اختر حساب __</option>
                                                @foreach ($accounts1 as $acc1)
                                                    <option value="{{ $acc1->id }}" data-balance="{{ $acc1->balance ?? 0 }}"
                                                        {{ $entry->account_id == $acc1->id ? 'selected' : '' }}>
                                                        {{ $acc1->code }} _ {{ $acc1->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif (in_array($pro_type, $account1_types))
                                            <select name="acc2[]" class="form-control js-tom-select js-balance-dest" required>
                                                <option value="">__ اختر حساب __</option>
                                                @foreach ($accounts2 as $acc2)
                                                    <option value="{{ $acc2->id }}" data-balance="{{ $acc2->balance ?? 0 }}"
                                                        {{ $entry->account_id == $acc2->id ? 'selected' : '' }}>
                                                        {{ $acc2->code }} _ {{ $acc2->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                        <small class="text-muted d-block mt-1">
                                            {{ __('Balance before') }}: <span class="rowBalanceBefore">0.00</span>
                                            &nbsp;|&nbsp;
                                            {{ __('After') }}: <span class="rowBalanceAfter">0.00</span>
                                        </small>
                                    </td>
                                    <td><input type="text" name="note[]" class="form-control" value="{{ $entry->info }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-2 text-right">
                        <strong>{{ __("Total Amount") }}: <span id="debitTotal">0.00</span></strong>
                    </div>
                    <button type="button" class="btn btn-success mt-2" id="addRow">{{ __("Add Row") }}</button>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ __("General Notes") }}</label>
                            <input type="text" name="info" class="form-control" value="{{ $operHead->info }}">
                        </div>
                    </div>
                </div>

                <x-branches::branch-select :branches="$branches" />

                <button type="submit" class="btn btn-main btn-lg btn-block mt-3">تحديث</button>
            </form>
        </div>
    </div>

    <script>
        // Lightweight Tom Select initializer (fallback if global manager isn't present)
        (function(){
            function initSelect(elem){
                if (window.TomSelect && !elem.tomselect) {
                    new TomSelect(elem, {
                        create: false,
                        searchField: ['text'],
                        sortField: {field: 'text', direction: 'asc'},
                        dropdownInput: true,
                        plugins: { remove_button: {title: 'إزالة'} },
                        placeholder: elem.getAttribute('placeholder') || 'ابحث...'
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
        })();
        document.getElementById('myForm').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });


        const tableBody = document.querySelector('#entriesTable tbody');

        // زر {{ __("Add Row") }} جديد
        document.getElementById('addRow').onclick = () => {
            const lastRow = tableBody.querySelector('tr:last-child');
            const amount = lastRow.querySelector('input[name="sub_value[]"]').value;
            const account = lastRow.querySelector('select').value;


            if (!amount || parseFloat(amount) === 0 || !account) {
                alert("يرجى تعبئة الصف الحالي أولاً قبل إضافة صف جديد.");
                return;
            }

            const row = tableBody.insertRow();
            row.innerHTML = `
                    <td><input type="number" name="sub_value[]" class="form-control debit" step="0.01" value="0"></td>
                    <td>
                        @if (in_array($pro_type, $account2_types))
                            <select name="acc1[]" class="form-control js-tom-select js-balance-dest" required>
                                @foreach ($accounts1 as $acc1)
                                    <option value="{{ $acc1->id }}" data-balance="{{ $acc1->balance ?? 0 }}">{{ $acc1->code }} _ {{ $acc1->aname }}</option>
                                @endforeach
                            </select>
                        @elseif (in_array($pro_type, $account1_types))
                            <select name="acc2[]" class="form-control js-tom-select js-balance-dest" required>
                                @foreach ($accounts2 as $acc2)
                                    <option value="{{ $acc2->id }}" data-balance="{{ $acc2->balance ?? 0 }}">{{ $acc2->code }} _ {{ $acc2->aname }}</option>
                                @endforeach
                            </select>
                        @endif
                        <small class="text-muted d-block mt-1">
                            {{ __('Balance before') }}: <span class="rowBalanceBefore">0.00</span>
                            &nbsp;|&nbsp;
                            {{ __('After') }}: <span class="rowBalanceAfter">0.00</span>
                        </small>
                    </td>
                    <td><input type="text" name="note[]" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
                `;

            // بعد إضافة الصف الجديد، ضعه في متغير
            const newRow = tableBody.querySelector('tr:last-child');

            // ركّز على أول حقل فيه
            const newInput = newRow.querySelector('input[name="sub_value[]"]');
            newInput.focus();
            newInput.select();

            // Initialize Tom Select on newly added row select
            const newSelect = newRow.querySelector('select.js-tom-select');
            if (newSelect) {
                if (window.tomSelectManager) {
                    window.tomSelectManager.initializeElement(newSelect);
                } else if (window.TomSelect && !newSelect.tomselect) {
                    new TomSelect(newSelect, {
                        create: false,
                        dropdownInput: true,
                        plugins: { remove_button: {title: 'إزالة'} },
                    });
                }
            }
            // init balance UI for new row
            attachRowBalanceHandlers(row);

        };


        // زر الحذف
        document.addEventListener('click', e => {
            if (e.target.classList.contains('removeRow')) {
                const row = e.target.closest('tr');
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                if (row !== rows[0]) {
                    row.remove();
                    calculateTotals();
                } else {
                    alert("لا يمكن حذف الصف الأول.");
                }
            }
        });

        // تحقق عند الإرسال
        document.getElementById('myForm').onsubmit = e => {
            const total = [...document.querySelectorAll('.debit')]
                .reduce((sum, input) => sum + parseFloat(input.value || 0), 0);

            if (total <= 0) {
                e.preventDefault();
                alert("يجب إدخال مبلغ واحد على الأقل.");
            }
        };

        // حساب المجاميع
        function calculateTotals() {
            let totalDebit = 0;
            document.querySelectorAll('.debit').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });
            const display = document.getElementById('debitTotal');
            if (display) {
                display.textContent = totalDebit.toFixed(2);
            }
            // update top account after-balance using total as outgoing amount
            const topSelect = document.querySelector('.js-balance-source');
            if (topSelect) {
                const before = parseFloat(topSelect.selectedOptions[0]?.getAttribute('data-balance') || '0');
                const after = before - totalDebit;
                const bEl = document.getElementById('topBalanceBefore');
                const aEl = document.getElementById('topBalanceAfter');
                if (bEl) bEl.textContent = before.toFixed(2);
                if (aEl) aEl.textContent = after.toFixed(2);
            }
        }

        // إعادة حساب المجموع عند إدخال بيانات
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('debit')) {
                calculateTotals();
                // update the row after-balance for this row
                const row = e.target.closest('tr');
                if (row) updateRowBalance(row);
            }
        });

        function updateRowBalance(row){
            const select = row.querySelector('.js-balance-dest');
            const amountInput = row.querySelector('input[name="sub_value[]"]');
            const beforeSpan = row.querySelector('.rowBalanceBefore');
            const afterSpan = row.querySelector('.rowBalanceAfter');
            const before = parseFloat(select?.selectedOptions[0]?.getAttribute('data-balance') || '0');
            const amount = parseFloat(amountInput?.value || '0');
            if (beforeSpan) beforeSpan.textContent = before.toFixed(2);
            if (afterSpan) afterSpan.textContent = (before + amount).toFixed(2);
        }

        function attachTopBalanceHandlers(){
            const topSelect = document.querySelector('.js-balance-source');
            if (!topSelect) return;
            topSelect.addEventListener('change', calculateTotals);
            // init display
            calculateTotals();
        }

        function attachRowBalanceHandlers(ctx){
            const row = ctx || document.querySelector('#entriesTable tbody tr');
            if (!row) return;
            const select = row.querySelector('.js-balance-dest');
            if (select){
                select.addEventListener('change', () => updateRowBalance(row));
            }
            updateRowBalance(row);
        }

        attachTopBalanceHandlers();
        // Attach handlers to all existing rows
        document.querySelectorAll('#entriesTable tbody tr').forEach(attachRowBalanceHandlers);
    </script>
@endsection
