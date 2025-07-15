@extends('admin.dashboard')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <form id="myForm" action="{{ route('transfers.update', $transfer->id) }}" method="POST">
            @csrf
            @method('PUT')
       
            <input type="hidden" name="pro_type" value="{{$pro_type}}">

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
                        <div class="col-lg-3">
                            <label>المبلغ</label>
                            <input type="number" step="0.01" name="pro_value" class="form-control" value="{{ old('pro_value', $transfer->pro_value ?? '') }}" onblur="validateRequired(this)">
                        </div>
                        <div class="col-lg-9">
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
                           
                            <select name="acc2" required id="acc2" class="form-control" onblur="validateRequired(this); checkSameAccounts();">
                                <option value="">اختر الحساب</option>
                                @php $fromAccounts = ($type === 'cash_to_cash' || $type === 'cash_to_bank') ? $cashAccounts : $bankAccounts; 
                                @endphp
                                @foreach ($fromAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('acc2', $transfer->acc2 ?? '') == $account->id ? 'selected' : '' }}>
                                        {{ $account->aname }}
                                    </option>
                                @endforeach
                            </select>


                        </div>
                        <div class="col-lg-6">
                            <label>إلى حساب: {{ $acc2_text }} <span class="badge badge-outline-info">مدين</span></label>
                            <select name="acc1" id="acc2" required class="form-control" onblur="validateRequired(this); checkSameAccounts();">
                                <option value="">اختر الحساب</option>
                                @php $toAccounts = ($type === 'cash_to_cash' || $type === 'bank_to_cash') ? $cashAccounts : $bankAccounts; @endphp
                                @foreach ($toAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('acc1', $transfer->acc1 ?? '') == $account->id ? ' selected ' : '' }}>
                                        {{ $account->aname }}
                                    </option>
                                @endforeach
                            </select>
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
                                {{-- أضف مراكز التكلفة هنا --}}
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
                            <button class="btn btn-primary" type="submit">تأكيد</button>
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
</script>
@endsection
