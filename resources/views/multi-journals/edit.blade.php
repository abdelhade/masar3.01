@extends('admin.dashboard')

@section('content')
    <div class="container">
        <h4>{{ isset($oper) ? 'تعديل قيد متعدد' : 'إنشاء قيد متعدد' }}</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ isset($oper) ? route('multi-journals.update', $oper->id) : route('multi-journals.store') }}"
            method="POST">
            @csrf
            @if (isset($oper))
                @method('PUT')
            @endif

            <div class="row mb-3">
                <div class="col-md-3">
                    <label>التاريخ</label>
                    <input type="date" name="pro_date" class="form-control"
                        value="{{ old('pro_date', $oper->pro_date ?? date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-3">
                    <label>نوع الحركة</label>
                    <input type="number" name="pro_type" class="form-control"
                        value="{{ old('pro_type', $oper->pro_type ?? 1) }}" required>
                </div>

                <div class="col-md-3">
                    <label>الموظف</label>
                    <select name="emp_id" class="form-control">
                        <option value="">-- اختر --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}"
                                {{ old('emp_id', $oper->emp_id ?? '') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->code }}--{{ $emp->aname }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>تفاصيل القيد</label>
                <input type="text" name="details" class="form-control"
                    value="{{ old('details', $oper->details ?? '') }}">
            </div>

            <hr>
            <h5>تفاصيل اليومية</h5>

            <table class="table table-striped mb-0" style="min-width: 1200px;">
                <thead class="table-light text-center align-middle">

                    <tr>
                        <th  class="font-family-cairo fw-bold font-14 text-center">الحساب</th>
                        <th  class="font-family-cairo fw-bold font-14 text-center">مدين</th>
                        <th  class="font-family-cairo fw-bold font-14 text-center">دائن</th>
                        <th  class="font-family-cairo fw-bold font-14 text-center">ملاحظة</th>
                        <th  class="font-family-cairo fw-bold font-14 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = old('account_id')
                            ? collect(old('account_id'))->map(function ($val, $i) use ($request) {
                                return [
                                    'account_id' => old("account_id.$i"),
                                    'debit' => old("debit.$i"),
                                    'credit' => old("credit.$i"),
                                    'note' => old("note.$i"),
                                ];
                            })
                            : (isset($details)
                                ? $details
                                : collect([['account_id' => '', 'debit' => '', 'credit' => '', 'note' => '']]));
                    @endphp

                    @foreach ($lines as $i => $line)
                        <tr>
                            <td class="text-center">
                                <select name="account_id[]" class="form-control" required>
                                    <option value="">-- اختر --</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}"
                                            {{ $line['account_id'] == $account->id ? 'selected' : '' }}>
                                            {{ $account->aname }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-center"><input type="number" step="0.01" name="debit[]" class="form-control"
                                    value="{{ $line['debit'] }}"></td>
                            <td class="text-center"><input type="number" step="0.01" name="credit[]" class="form-control"
                                    value="{{ $line['credit'] }}"></td>
                            <td class="text-center"><input type="text" name="note[]" class="form-control" value="{{ $line['note'] }}"></td>
                            <td class="text-center"><button type="button"  class="btn btn-danger btn-icon-square-sm remove-row">✖</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="button" id="addRow" class="btn btn-secondary btn-sm mb-3">+ صف جديد</button>

            <div>
                <button type="submit" class="btn btn-primary">حفظ</button>
                <a href="{{ route('multi-journals.index') }}" class="btn btn-light">إلغاء</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('addRow').addEventListener('click', function() {
            const table = document.querySelector('#entries tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="text-center">
                <select name="account_id[]" class="form-control" required>
                    <option value="">-- اختر --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center"><input type="number" step="0.01" name="debit[]" class="form-control" value="0"></td>
            <td class="text-center"><input type="number" step="0.01" name="credit[]" class="form-control" value="0"></td>
            <td class="text-center"><input type="text" name="note[]" class="form-control"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row">✖</button></td>
        `;
            table.appendChild(row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endpush
