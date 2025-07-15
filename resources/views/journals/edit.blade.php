@extends('admin.dashboard')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="cake cake-flash">تعديل قيد يومية</h1>
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

                <form action="{{ route('journals.update', ['journal' => $journal->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="pro_type" value="7">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="pro_date">التاريخ</label>
                                <input type="date" class="form-control" name="pro_date"
                                    value="{{ old('pro_date', $journal->pro_date) }}">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="pro_num">الرقم الدفتري</label>
                                <input type="text" class="form-control" name="pro_num"
                                    value="{{ old('pro_num', $journal->pro_num) }}" placeholder="EX:7645">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="emp_id">الموظف</label>
                                <select class="form-control" name="emp_id" required>
                                    <option value="">اختر حساب</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ old('emp_id', $journal->emp_id) == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->code }} _ {{ $emp->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cost_center">مركز التكلفة</label>
                                <select class="form-control" name="cost_center" required>
                                    <option value="">اختر مركز تكلفة</option>
                                    @foreach ($cost_centers as $cost)
                                        <option value="{{ $cost->id }}"
                                            {{ old('cost_center', $journal->cost_center) == $cost->id ? 'selected' : '' }}>
                                            {{ $cost->cname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="details">بيان</label>
                                <input name="details" type="text" class="form-control frst"
                                    value="{{ old('details', $journal->details) }}">
                            </div>
                        </div>
                    </div>

                    <!-- جدول الحسابات -->
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">

                                <tr>
                                    <th width="15%">مدين</th>
                                    <th width="15%">دائن</th>
                                    <th width="30%">الحساب</th>
                                    <th width="40%">ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" required name="debit"
                                            value="{{ old('debit', $journal->pro_value) }}" class="form-control debit"
                                            step="0.01"></td>
                                    <td></td>
                                    <td>
                                        <select class="form-control" name="acc1" required>
                                            <option value="">اختر حساب</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}"
                                                    {{ old('acc1', $journal->acc1) == $acc->id ? 'selected' : '' }}>
                                                    {{ $acc->code }} _ {{ $acc->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="info2" class="form-control"
                                            value="{{ old('info2', $journal->info2) }}"></td>
                                </tr>

                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="number" name="credit"
                                            value="{{ old('debit', $journal->pro_value) }}" class="form-control credit"
                                            step="0.01">
                                    </td>
                                    <td>
                                        <select class="form-control" name="acc2" required>
                                            <option value="">اختر حساب</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}"
                                                    {{ old('acc2', $journal->acc2) == $acc->id ? 'selected' : '' }}>
                                                    {{ $acc->code }} _ {{ $acc->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="info3" class="form-control"
                                            value="{{ old('info3', $journal->info3) }}">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- إجماليات -->
                    <div class="row">
                        <div class="col col-md-4">
                            <div class="form-group">
                                <label for="">مدين</label>
                                <input type="text" class="form-control" id="debit_total" readonly>
                            </div>
                        </div>

                        <div class="col col-md-4">
                            <div class="form-group">
                                <label for="">دائن</label>
                                <input type="text" class="form-control" id="credit_total" readonly>
                            </div>
                        </div>

                        <div class="col col-md-4">
                            <div class="form-group">
                                <label for="">الفرق</label>
                                <input type="text" class="form-control" id="difference" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- ملاحظات إضافية -->
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <input type="text" name="info" class="form-control"
                                    value="{{ old('info', $journal->info) }}">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">حفظ</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // جمع المدين والدائن وعرض الفرق
            function calculateTotals() {
                let debitTotal = 0,
                    creditTotal = 0;
                $('.debit').each(function() {
                    debitTotal += parseFloat($(this).val()) || 0;
                });
                $('.credit').each(function() {
                    creditTotal += parseFloat($(this).val()) || 0;
                });
                $('#debit_total').val(debitTotal.toFixed(2));
                $('#credit_total').val(creditTotal.toFixed(2));
                $('#difference').val((debitTotal - creditTotal).toFixed(2));
            }
            $('.debit, .credit').on('focusout', calculateTotals);

            // منع الحفظ إذا لم يتوازن القيد
            $('form').on('submit', function(e) {
                if ($('#difference').val() != 0) {
                    alert('يجب أن يتوازن القيد (مدين = دائن)');
                    e.preventDefault();
                }
            });

            // منع تعبئة المدين والدائن في نفس الصف
            function preventBoth(row) {
                row.find('.debit').on('input', function() {
                    if (+this.value > 0) row.find('.credit').val('0.00');
                    calculateTotals();
                });
                row.find('.credit').on('input', function() {
                    if (+this.value > 0) row.find('.debit').val('0.00');
                    calculateTotals();
                });
            }

            // تطبيق preventBoth على كل صف
            $('table tbody tr').each(function() {
                preventBoth($(this));
            });

            // حساب الإجماليات عند تحميل الصفحة
            calculateTotals();
        });
    </script>
@endsection
