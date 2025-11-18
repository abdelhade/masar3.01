@extends('admin.dashboard')

{{-- Dynamic Sidebar: نعرض فقط الحسابات --}}
@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('انشاء حساب'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('انشاء')]],
    ])
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                @php
                    $parent = request()->get('parent');
                    $isClientOrSupplier = in_array($parent, ['1103', '2101']); // تحديد ما إذا كان حساب عميل أو مورد
                @endphp
                @php
                    // خريطة تربط parent_id بنوع الحساب
                    $parentTypeMap = [
                        '1103' => '1', // العملاء
                        '2101' => '2', // الموردين
                        '1101' => '3', // الصناديق
                        '1102' => '4', // البنوك
                        '2102' => '5', //الموظفين
                        '1104' => '6', // المخازن
                        '5'    => '7', // المصروفات
                        '42'   => '8', // الإيرادات
                        '2104' => '9', // دائنين اخرين
                        '1106' => '10', // مدينين آخرين
                        '31'   => '11', // الشريك الرئيسي
                        '32'   => '12', // جاري الشريك
                        '12'   => '13', // الأصول
                        '1202' => '14', // الأصول القابلة للتأجير
                        '1105' => '17', // حافظات أوراق القبض
                        '2103' => '18', // حافظات أوراق الدفع
                    ];
                    $type = $parentTypeMap[$parent] ?? '0';
                @endphp

                <section class="content">
                    @if (in_array($parent, [
                            '1103',
                            '2101',
                            '1105',
                            '2103',
                            '1101',
                            '1102',
                            '5',
                            '42',
                            '2104',
                            '1106',
                            '31',
                            '32',
                            '12',
                            '2102',
                            '1202',
                            '1104',
                        ]))
                        <form id="myForm" action="{{ route('accounts.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="q" value="{{ $parent }}">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3>{{ __('اضافة حساب') }}</h3>
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

                                                <input
                                                    type="text"
                                                    class="form-control font-bold"
                                                    id="type"
                                                    name="acc_type"
                                                    value="{{ $type }}"
                                                    readonly hidden
                                                >

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="code">{{ __('الكود') }}</label><span
                                                    class="text-danger">*</span>
                                                <input readonly required class="form-control font-bold" type="text"
                                                    name="code" value="{{ $last_id }}" id="code">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="aname">{{ __('الاسم') }}</label><span
                                                    class="text-danger">*</span>
                                                <input required class="form-control font-bold frst" type="text"
                                                    name="aname" id="aname">
                                                <div id="resaname"></div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="is_basic">{{ __('نوع الحساب') }}</label><span
                                                    class="text-danger">*</span>
                                                <select class="form-control font-bold" name="is_basic" id="is_basic">
                                                    <option value="1">{{ __('اساسي') }}</option>
                                                    <option selected value="0">{{ __('حساب عادي') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="parent_id">{{ __('يتبع ل') }}</label><span
                                                    class="text-danger">*</span>
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
                                                <label for="branch_id">{{ __('الفرع') }}</label>
                                                <select required class="form-control font-bold" name="branch_id" id="branch_id">
                                                    <option value="">{{ __('اختر الفرع') }}</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phone">{{ __('تليفون') }}</label>
                                                <input class="form-control font-bold" type="text" name="phone"
                                                    id="phone" placeholder="{{ __('التليفون او تليفون المسؤول') }}">
                                            </div>
                                        </div>
                                    </div>

                                    @if ($isClientOrSupplier)
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="zatca_name">{{ __('الاسم التجاري (ZATCA)') }}</label>
                                                    <input class="form-control" type="text" name="zatca_name"
                                                        id="zatca_name" placeholder="{{ __('الاسم التجاري') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="vat_number">الرقم الضريبي (VAT)</label>
                                                    <input class="form-control" type="text" name="vat_number"
                                                        id="vat_number" placeholder="{{ __('الرقم الضريبي') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="national_id">رقم الهوية</label>
                                                    <input class="form-control" type="text" name="national_id"
                                                        id="national_id" placeholder="{{ __('رقم الهوية') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="zatca_address">العنوان الوطني (ZATCA)</label>
                                                    <input class="form-control" type="text" name="zatca_address"
                                                        id="zatca_address" placeholder="{{ __('العنوان الوطني') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="company_type">نوع العميل</label>
                                                    <select class="form-control" name="company_type" id="company_type">
                                                        <option value="">{{ __('اختر النوع') }}</option>
                                                        <option value="شركة">شركة</option>
                                                        <option value="فردي">فردي</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="nationality">الجنسية</label>
                                                    <input class="form-control" type="text" name="nationality"
                                                        id="nationality" placeholder="{{ __('الجنسية') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <x-dynamic-search name="country_id" label="الدولة" column="title"
                                                    model="App\Models\Country" placeholder="ابحث عن الدولة..."
                                                    :required="false" :class="'form-select'" />
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <x-dynamic-search name="city_id" label="المدينة" column="title"
                                                    model="App\Models\City" placeholder="ابحث عن المدينة..."
                                                    :required="false" :class="'form-select'" />
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <x-dynamic-search name="state_id" label="المنطقة" column="title"
                                                    model="App\Models\State" placeholder="ابحث عن المنطقة..."
                                                    :required="false" :class="'form-select'" />
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <x-dynamic-search name="town_id" label="الحي" column="title"
                                                    model="App\Models\Town" placeholder="ابحث عن الحي..."
                                                    :required="false" :class="'form-select'" />
                                            </div>

                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="is_stock" class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="is_stock" value="0">
                                                    <input type="checkbox" name="is_stock" id="is_stock" value="1" 
                                                        class="form-check-input mt-0"
                                                        {{ $parent == '123' ? 'checked' : '' }}>
                                                    <span>مخزون</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="secret" class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="secret" value="0">
                                                    <input type="checkbox" name="secret" id="secret" value="1" class="form-check-input mt-0">
                                                    <span>حساب سري</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="is_fund" class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="is_fund" value="0">
                                                    <input type="checkbox" name="is_fund" id="is_fund" value="1" 
                                                        class="form-check-input mt-0"
                                                        {{ $parent == '121' ? 'checked' : '' }}>
                                                    <span>حساب صندوق</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="rentable" class="d-flex align-items-center gap-2">
                                                    <input type="hidden" name="rentable" value="0">
                                                    <input type="checkbox" name="rentable" id="rentable" value="1" 
                                                        class="form-check-input mt-0"
                                                        {{ $parent == '112' ? 'checked' : '' }}>
                                                    <span>أصل قابل للتأجير</span>
                                                </label>
                                            </div>
                                        </div>

                                        @if ($parent == 44)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="employees_expensses" class="d-flex align-items-center gap-2">
                                                        <input type="hidden" name="employees_expensses" value="0">
                                                        <input type="checkbox" name="employees_expensses" 
                                                            id="employees_expensses" value="1" 
                                                            class="form-check-input mt-0">
                                                        <span>حساب رواتب للموظفين</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                    @if ($parent == '12')
                                        <div class="alert alert-warning"
                                            style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                            {{ __('سيتم اضافة حساب مجمع اهلاك و حساب مصروف اهلاك للأصل') }}
                                        </div>
                                                   <input hidden type="text" readonly name="reserve" id="reserve" value="1">
                                        </div>
                                    @endif


                                </div>

                                <div class="card-footer">
                                    <div class="d-flex justify-content-start">
                                        <button class="btn btn-success m-1" type="submit">
                                            <i class="las la-save"></i> تأكيد
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            <p>خطأ في تحديد نوع الحساب</p>
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </div>

@endsection
