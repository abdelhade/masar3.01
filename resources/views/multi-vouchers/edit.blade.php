@extends('admin.dashboard')

@section('content')
    <style>
        .form-group { margin-bottom: 1.25rem; }
        .form-control { padding: 0.75rem; font-size: 1rem; border-radius: 0.5rem; }
        label { font-weight: bold; margin-bottom: 0.5rem; display: inline-block; }
        .table th, .table td { vertical-align: middle !important; }
        .btn { padding: 0.5rem 1rem; }
        .card { margin-bottom: 2rem; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); }
        #entriesTable select, #entriesTable input { min-width: 120px; }
        .table th { background-color: #f8f9fa; }
    </style>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="cake cake-flash cake-delay-2s">تعديل عملية: {{ $ptext }}</h1>
            </div>
            <div class="card-body">
                <form action="{{ route('multi-vouchers.update', $operHead->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="pro_type" value="{{ $pro_type }}">

                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>رقم الفاتورة</label>
                                <input type="text" name="pro_id" class="form-control" value="{{ $operHead->pro_id }}" readonly>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>SN</label>
                                <input type="text" name="pro_serial" class="form-control" value="{{ $operHead->pro_serial }}">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>التاريخ</label>
                                <input type="date" name="pro_date" class="form-control" value="{{ $operHead->pro_date }}">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>الصندوق</label>
                                @if (in_array($pro_type, ['32', '40', '41', '46', '47', '50', '53', '55']))
                                    <select name="acc1[]" class="form-control" required>
                                        @foreach ($accounts1 as $acc1)
                                            <option value="{{ $acc1->id }}" {{ $mainEntry && $mainEntry->account_id == $acc1->id ? 'selected' : '' }}>
                                                {{ $acc1->code }} _ {{ $acc1->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif (in_array($pro_type, ['33', '42', '43', '44', '45', '48', '49', '51', '52', '54']))
                                    <select name="acc2[]" class="form-control" required>
                                        @foreach ($accounts2 as $acc2)
                                            <option value="{{ $acc2->id }}" {{ $mainEntry && $mainEntry->account_id == $acc2->id ? 'selected' : '' }}>
                                                {{ $acc2->code }} _ {{ $acc2->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>الموظف</label>
                                <select name="emp_id" class="form-control" required>
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
                                <label>البيان</label>
                                <input name="details" required type="text" class="form-control frst" value="{{ $operHead->details }}">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="entriesTable">
                            <thead>
                                <tr>
                                    <th>المبلغ</th>
                                    <th>الحساب</th>
                                    <th>ملاحظات</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subEntries as $index => $entry)
                                    <tr>
                                        <td>
                                            <input type="number" name="sub_value[]" class="form-control debit" step="0.01" value="{{ $entry->debit ?: $entry->credit }}">
                                        </td>
                                        <td>
                                            @if (in_array($pro_type, ['32', '40', '41', '46', '47', '50', '53', '55']))
                                                <select name="acc2[]" class="form-control" required>
                                                    <option value="">اختر حساب</option>
                                                    @foreach ($accounts2 as $acc)
                                                        <option value="{{ $acc->id }}" {{ $entry->account_id == $acc->id ? 'selected' : '' }}>
                                                            {{ $acc->code }} _ {{ $acc->aname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif (in_array($pro_type, ['33', '42', '43', '44', '45', '48', '49', '51', '52', '54']))
                                                <select name="acc1[]" class="form-control" required>
                                                    <option value="">اختر حساب</option>
                                                    @foreach ($accounts1 as $acc)
                                                        <option value="{{ $acc->id }}" {{ $entry->account_id == $acc->id ? 'selected' : '' }}>
                                                            {{ $acc->code }} _ {{ $acc->aname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                        <td><input type="text" name="note[]" class="form-control" value="{{ $entry->info }}"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm removeRow">حذف</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-2 text-right">
                            <strong>إجمالي المبلغ: <span id="debitTotal">0.00</span></strong>
                        </div>

                        <button type="button" class="btn btn-success mt-2" id="addRow">إضافة سطر</button>
                    </div>

                    <div class="row mt-4">
                        <div class="col">
                            <div class="form-group">
                                <label>ملاحظات عامة</label>
                                <input type="text" name="info" class="form-control" value="{{ $operHead->info }}">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">تحديث</button>
                </form>
            </div>
        </div>
    </div>

    {{-- نفس سكريبت create مع الحساب --}}
    <script>
        function calculateTotals() {
            let total = 0;
            document.querySelectorAll('.debit').forEach(el => total += parseFloat(el.value || 0));
            document.getElementById('debitTotal').innerText = total.toFixed(2);
        }

        document.addEventListener('input', e => {
            if (e.target.classList.contains('debit')) calculateTotals();
        });

        document.addEventListener('click', e => {
            if (e.target.classList.contains('removeRow')) {
                const row = e.target.closest('tr');
                const rows = document.querySelectorAll('#entriesTable tbody tr');
                if (rows.length > 1) {
                    row.remove();
                    calculateTotals();
                } else {
                    alert('لا يمكن حذف الصف الوحيد.');
                }
            }
        });

        calculateTotals();
    </script>
@endsection
