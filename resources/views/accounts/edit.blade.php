@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل حساب'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('العملاء'), 'url' => route('clients.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                @php 
                    $parent = request()->get('parent');
                    // تحديد ما إذا كان حساب عميل أو مورد
                    $isClientOrSupplier = in_array(substr($account->code, 0, 4), ['1103', '2101']);
                @endphp

                <section class="content">
                    <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $account->id }}">
                        <input type="hidden" name="q" value="{{ $parent }}">

                        <div class="card card-info">
                            <div class="card-header">
                                <h3>تعديل حساب</h3>
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

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="code">الكود <span class="text-danger">*</span></label>
                                            <input required readonly class="form-control font-bold" type="text"
                                                name="code" value="{{ $account->code }}" id="code">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aname">الاسم <span class="text-danger">*</span></label>
                                            <input required class="form-control font-bold" type="text" name="aname"
                                                value="{{ $account->aname }}" id="frst">
                                            <div id="resaname"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="is_basic">نوع الحساب <span class="text-danger">*</span></label>
                                            <select class="form-control font-bold" name="is_basic" id="is_basic">
                                                <option value="1" {{ $account->is_basic == 1 ? 'selected' : '' }}>اساسي
                                                </option>
                                                <option value="0" {{ $account->is_basic == 0 ? 'selected' : '' }}>حساب
                                                    عادي</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="parent_id">يتبع ل <span class="text-danger">*</span></label>
                                            <select class="form-control font-bold" name="parent_id" id="parent_id">
                                                @foreach ($resacs as $rowacs)
                                                    <option value="{{ $rowacs->id }}"
                                                        {{ $account->parent_id == $rowacs->id ? 'selected' : '' }}>
                                                        {{ $rowacs->code }} - {{ $rowacs->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="phone">تليفون</label>
                                            <input class="form-control font-bold" type="text" name="phone"
                                                id="phone" value="{{ $account->phone }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="address">العنوان</label>
                                            <input class="form-control font-bold" type="text" name="address"
                                                id="address" value="{{ $account->address }}">
                                        </div>
                                    </div>
                                </div>
                                
                                @if($isClientOrSupplier)
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="zatca_name">الاسم التجاري (ZATCA)</label>
                                            <input class="form-control" type="text" name="zatca_name" id="zatca_name" value="{{ $account->zatca_name }}" placeholder="الاسم التجاري">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vat_number">الرقم الضريبي (VAT)</label>
                                            <input class="form-control" type="text" name="vat_number" id="vat_number" value="{{ $account->vat_number }}" placeholder="الرقم الضريبي">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="national_id">رقم الهوية</label>
                                            <input class="form-control" type="text" name="national_id" id="national_id" value="{{ $account->national_id }}" placeholder="رقم الهوية">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="zatca_address">العنوان الوطني (ZATCA)</label>
                                            <input class="form-control" type="text" name="zatca_address" id="zatca_address" value="{{ $account->zatca_address }}" placeholder="العنوان الوطني">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_type">نوع العميل</label>
                                            <select class="form-control" name="company_type" id="company_type">
                                                <option value="شركة" {{ $account->company_type == 'شركة' ? 'selected' : '' }}>شركة</option>
                                                <option value="فردي" {{ $account->company_type == 'فردي' ? 'selected' : '' }}>فردي</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nationality">الجنسية</label>
                                            <input class="form-control" type="text" name="nationality" id="nationality" value="{{ $account->nationality }}" placeholder="الجنسية">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="is_stock">مخزون</label><br>
                                            <input type="checkbox" name="is_stock" id="is_stock"
                                                {{ $account->is_stock ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="secret">حساب سري</label><br>
                                            <input type="checkbox" name="secret" id="secret"
                                                {{ $account->secret ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="is_fund">حساب صندوق</label><br>
                                            <input type="checkbox" name="is_fund" id="is_fund"
                                                {{ $account->is_fund ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="rentable">أصل قابل للتأجير</label><br>
                                            <input type="checkbox" name="rentable" id="rentable"
                                                {{ $account->rentable ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    @if ($parent == 44)
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="employees_expensses">حساب رواتب للموظفين</label><br>
                                                <input type="checkbox" name="employees_expensses" id="employees_expensses"
                                                    {{ $account->employees_expensses ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-start">
                                    <button class="btn btn-success btn-block m-1" type="submit">تحديث</button>
                                    <a href="{{ route('accounts.index') }}"
                                        class="btn btn-secondary btn-block m-1">رجوع</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            $('#frst').on('keyup', function() {
                var itemId = $(this).val();
                $.ajax({
                    url: '{{ url('get/get_accinfo') }}',
                    method: 'GET',
                    data: {
                        id: itemId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#resaname').text(response.message);
                    },
                    error: function() {
                        $('#resaname').html(
                        "<p class='text-danger'>خطأ في التحقق من الاسم</p>");
                    }
                });
            });
        });
    </script>
@endsection