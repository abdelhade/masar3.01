@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('انشاء حساب'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('العملاء'), 'url' => route('clients.index')],
            ['label' => __('انشاء')],
        ],
    ])
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                @php
                    $parent = request()->get('parent');
                @endphp

                <section class="content">

                    @if (in_array($parent, ['122', '211', '121', '124', '44', '32', '212', '125', '221', '11', '213', '112', '123', '224']))


                        <form id="myForm" action="{{ route('accounts.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="q" value="{{ $parent }}">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3>اضافة حساب</h3>
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
                                                <label for="code">الكود</label><span class="text-danger">*</span>
                                                <input required class="form-control font-bold" type="text" name="code"
                                                    value="{{ $last_id }}" id="code">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="aname">الاسم</label><span class="text-danger">*</span>
                                                <input required class="form-control font-bold" type="text" name="aname"
                                                    id="frst">
                                                <div id="resaname"></div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="is_basic">نوع الحساب</label><span class="text-danger">*</span>
                                                <select class="form-control font-bold" name="is_basic" id="is_basic">
                                                    <option value="1">اساسي</option>
                                                    <option selected value="0">حساب عادي</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="parent_id">يتبع ل</label><span class="text-danger">*</span>
                                                <select class="form-control font-bold" name="parent_id" id="parent_id">
                                                    @foreach ($resacs as $rowacs)
                                                        <option value="{{ $rowacs->id }}">
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
                                                    id="phone" placeholder="التليفون او تليفون المسؤول">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address">العنوان</label>
                                                <input class="form-control font-bold" type="text" name="address"
                                                    id="address" placeholder="اكتب العنوان او عنوان المسؤول">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="is_stock">مخزون</label>
                                                <input type="checkbox" name="is_stock" value="0" hidden>
                                                <input type="checkbox" name="is_stock" id="is_stock"
                                                    {{ $parent == '123' ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="secret">حساب سري</label>
                                                <input type="checkbox" name="secret" id="secret">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="is_fund">حساب صندوق</label>
                                                <input type="checkbox" name="is_fund" id="is_fund"
                                                    {{ $parent == '121' ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="rentable">أصل قابل للتأجير</label>
                                                <input type="checkbox" name="rentable" id="rentable"
                                                    {{ $parent == '112' ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        @if ($parent == 44)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="employees_expensses">حساب رواتب للموظفين</label>
                                                    <input type="checkbox" name="employees_expensses"
                                                        id="employees_expensses">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <div class="d-flex justify-content-start">

                                        <button class="btn btn-primary btn-block m-1" type="submit">تأكيد <i
                                                class="las la-save"></i></button>


                                        <button class="btn btn-danger btn-block m-1" type="reset">مسح<i
                                                class="las la-times"></i> </button>

                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            <p>خطأ في تحديد نوع الحساب</p>
                        </div>
                    @endif
            </div>
        </section>
    </div>
@endsection
