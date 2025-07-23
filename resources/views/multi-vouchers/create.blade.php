@extends('admin.dashboard')

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
            <h3 class="h4 font-weight-bold mb-0">نوع العملية: {{ $ptext }}</h3>
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

            <form id="myForm" action="{{ route('multi-vouchers.store') }}" method="POST">
                @csrf

                <input type="hidden" name="pro_type" value="{{ $pro_type }}">

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>رقم الفاتورة</label>
                            <input type="text" name="pro_id" class="form-control" value="{{ $newProId }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>SN</label>
                            <input type="text" name="pro_serial" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>التاريخ</label>
                            <input type="date" name="pro_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
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
                            <label>الصندوق</label>
                            @if (in_array($pro_type, $account1_types))
                                <select name="acc1[]" class="form-control" required>
                                    @foreach ($accounts1 as $acc1)
                                        <option value="{{ $acc1->id }}">{{ $acc1->code }} _ {{ $acc1->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif (in_array($pro_type, $account2_types))
                                <select name="acc2[]" class="form-control" required>
                                    @foreach ($accounts2 as $acc2)
                                        <option value="{{ $acc2->id }}">{{ $acc2->code }} _ {{ $acc2->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>الموظف</label>
                            <select name="emp_id" class="form-control" required>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->code }} _ {{ $emp->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label>البيان</label>
                            <input name="details" required type="text" class="form-control frst">
                        </div>
                    </div>
                </div>



                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="entriesTable" class="table table-striped table-bordered mb-0" style="min-width: 1200px;">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th>المبلغ</th>
                                <th>الحساب</th>
                                <th>ملاحظات</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="number" name="sub_value[]" class="form-control debit" step="0.01"
                                        value="0"></td>
                                <td>
                                    @if (in_array($pro_type, $account2_types))
                                        <select name="acc1[]" class="form-control" required>
                                            <option value="">__ اختر حساب __</option>
                                            @foreach ($accounts1 as $acc1)
                                                <option value="{{ $acc1->id }}">{{ $acc1->code }} _
                                                    {{ $acc1->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif (in_array($pro_type, $account1_types))
                                        <select name="acc2[]" class="form-control" required>
                                            <option value="">__ اختر حساب __</option>
                                            @foreach ($accounts2 as $acc2)
                                                <option value="{{ $acc2->id }}">{{ $acc2->code }} _
                                                    {{ $acc2->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td><input type="text" name="note[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-2 text-right">
                        <strong>إجمالي المبلغ: <span id="debitTotal">0.00</span></strong>
                    </div>
                    <button type="button" class="btn btn-success mt-2" id="addRow">إضافة سطر</button>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>ملاحظات عامة</label>
                            <input type="text" name="info" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">حفظ</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('myForm').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });


        const tableBody = document.querySelector('#entriesTable tbody');

        // زر إضافة سطر جديد
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
                            <select name="acc1[]" class="form-control" required>
                                @foreach ($accounts1 as $acc1)
                                    <option value="{{ $acc1->id }}">{{ $acc1->code }} _ {{ $acc1->aname }}</option>
                                @endforeach
                            </select>
                        @elseif (in_array($pro_type, $account1_types))
                            <select name="acc2[]" class="form-control" required>
                                @foreach ($accounts2 as $acc2)
                                    <option value="{{ $acc2->id }}">{{ $acc2->code }} _ {{ $acc2->aname }}</option>
                                @endforeach
                            </select>
                        @endif
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
        }

        // إعادة حساب المجموع عند إدخال بيانات
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('debit')) {
                calculateTotals();
            }
        });

        // الحساب المبدئي
        calculateTotals();
    </script>
@endsection