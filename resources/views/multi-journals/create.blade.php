@extends('admin.dashboard')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="cake cake-flash">قيد يومية متعدد</h1>
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

                <form id="myForm" action="{{ route('multi-journals.store') }}" method="POST">
                    @csrf

                    <input type="hidden" name="pro_type" value="8">
                    <div class="row ">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="">التاريخ</label>
                                <input type="date" name="pro_date" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="">الموظف</label>
                                <select name="emp_id" class="form-control" required>
                                    <option value="">اختر حساب</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->code }} _
                                            {{ $emp->aname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-control">
                                <label for="details">بيان</label>
                                <input name="details" type="text" required class="form-control frst">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="entriesTable">
                            <thead>
                                <tr>
                                    <th>مدين</th>
                                    <th>دائن</th>
                                    <th>الحساب</th>
                                    <th>ملاحظات</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" name="debit[]" class="form-control debit" step="0.01"
                                            value="0"></td>
                                    <td><input type="number" name="credit[]" class="form-control credit" step="0.01"
                                            value="0"></td>
                                    <td>
                                        <select name="account_id[]" class="form-control" required>
                                            <option value="">اختر حساب</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} _
                                                    {{ $acc->aname }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="note[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-secondary" id="addRow">إضافة سطر</button>
                    </div>

                    <!-- ملاحظات عامة -->
                    <div class="row mt-3">
                        <div class="col">
                            <div class="form-control">
                                <label>ملاحظات عامة</label>
                                <input type="text" name="info" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">اجمالي مدين</label>
                                <p class="text-blue" id="debitTotal">00.00</p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">اجمالي دائن</label>
                                <p class="text-blue" id="creditTotal">00.00</p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">الفرق</label>
                                <p class="text-blue" id="diffTotal"></p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">حفظ</button>
                </form>
            </div>
        </div>
    </div>


    <script>
        const tableBody = document.querySelector('#entriesTable tbody');

        document.getElementById('addRow').onclick = () => {
            const lastRow = tableBody.querySelector('tr:last-child');

            // تحقق من تعبئة البيانات في آخر صف
            const debit = lastRow.querySelector('.debit').value;
            const credit = lastRow.querySelector('.credit').value;
            const account = lastRow.querySelector('select').value;

            // إذا لم يتم إدخال أي قيمة في الحقول المطلوبة، لا تضف صف جديد
            if ((!debit || debit == 0) && (!credit || credit == 0) || !account) {
                alert("يرجى تعبئة الصف الحالي أولاً قبل إضافة صف جديد.");
                return;
            }

            const row = tableBody.insertRow();
            row.innerHTML = `
            <td><input type="number" name="debit[]" class="form-control debit" step="0.01" value="0"></td>
            <td><input type="number" name="credit[]" class="form-control credit" step="0.01" value="0"></td>
            <td>
                <select name="account_id[]" class="form-control" required>
                    <option value="">اختر حساب</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} _ {{ $acc->aname }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="note[]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
        `;
        };

        // منع حذف الصف الأول
        document.addEventListener('click', e => {
            if (e.target.classList.contains('removeRow')) {
                const row = e.target.closest('tr');
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                if (row !== rows[0]) {
                    row.remove();
                } else {
                    alert("لا يمكن حذف الصف الأول.");
                }
            }
        });

        // التحقق من توازن القيد عند الإرسال
        document.getElementById('myForm').onsubmit = e => {
            const sum = sel => [...document.querySelectorAll(sel)].reduce((a, el) => a + parseFloat(el.value || 0), 0);
            if (sum('.debit').toFixed(2) !== sum('.credit').toFixed(2)) {
                e.preventDefault();
                alert("يجب أن تتساوى المجاميع المدينة والدائنة.");
            }
        };
    </script>

    <script>
        // عند الخروج من المدين، يصفر الدائن
        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('debit')) {
                const creditInput = e.target.closest('tr').querySelector('.credit');
                if (parseFloat(e.target.value || 0) > 0) {
                    creditInput.value = 0;
                }
            }

            if (e.target.classList.contains('credit')) {
                const debitInput = e.target.closest('tr').querySelector('.debit');
                if (parseFloat(e.target.value || 0) > 0) {
                    debitInput.value = 0;
                }
            }
        }, true); // نستخدم capture mode لضمان التقاط الحدث
    </script>
    <script>
        function calculateTotals() {
            let debitInputs = document.querySelectorAll('.debit');
            let creditInputs = document.querySelectorAll('.credit');

            let totalDebit = 0;
            let totalCredit = 0;

            debitInputs.forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });

            creditInputs.forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });

            // عرض النتائج
            document.getElementById('debitTotal').textContent = totalDebit.toFixed(2);
            document.getElementById('creditTotal').textContent = totalCredit.toFixed(2);
            document.getElementById('diffTotal').textContent = (totalDebit - totalCredit).toFixed(2);
        }

        // ربط التحديث مع أي تغيير في الحقول
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('debit') || e.target.classList.contains('credit')) {
                calculateTotals();
            }
        });

        // استدعاء للحساب عند تحميل الصفحة (لو في قيم موجودة مسبقًا)
        calculateTotals();
    </script>


@endsection
