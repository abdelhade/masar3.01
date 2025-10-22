@extends('admin.dashboard')

{{-- Dynamic Sidebar: نعرض فقط الحسابات --}}
@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    @php
        $parent = request()->get('parent');
        $isClientOrSupplier = in_array($parent, ['1103', '2101']);
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
        ];
        $type = $parentTypeMap[$parent] ?? '0';
    @endphp
    @include('components.breadcrumb', [
        'title' => __('تعديل حساب'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('تعديل')],
        ],
    ])
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <section class="content">
                    <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $account->id }}">
                        <input type="hidden" name="q" value="{{ $parent }}">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3>{{ __('تعديل حساب') }}</h3>
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
                                <!-- Account type input - use actual database value -->
                                <input type="hidden" name="acc_type" value="{{ $account->acc_type ?? '' }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="code">{{ __('الكود') }} <span class="text-danger">*</span></label>
                                            <input required readonly class="form-control font-bold" type="text" name="code" value="{{ $account->code }}" id="code">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aname">{{ __('الاسم') }} <span class="text-danger">*</span></label>
                                            <input required class="form-control font-bold frst" type="text" name="aname" value="{{ $account->aname }}" id="aname">
                                            <div id="resaname"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="is_basic">{{ __('نوع الحساب') }} <span class="text-danger">*</span></label>
                                            <select class="form-control font-bold" name="is_basic" id="is_basic">
                                                <option value="1" {{ $account->is_basic == 1 ? 'selected' : '' }}>{{ __('اساسي') }}</option>
                                                <option value="0" {{ $account->is_basic == 0 ? 'selected' : '' }}>{{ __('حساب عادي') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="parent_id">{{ __('يتبع ل') }} <span class="text-danger">*</span></label>
                                            <select class="form-control font-bold" name="parent_id" id="parent_id">
                                                @foreach ($resacs as $rowacs)
                                                    <option value="{{ $rowacs->id }}" {{ $account->parent_id == $rowacs->id ? 'selected' : '' }}>
                                                        {{ $rowacs->code }} - {{ $rowacs->aname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="branch_id">{{ __('الفرع') }}</label>
                                            <select class="form-control font-bold" name="branch_id" id="branch_id">
                                                <option value="">{{ __('اختر الفرع') }}</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ $account->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="phone">{{ __('تليفون') }}</label>
                                            <input class="form-control font-bold" type="text" name="phone" id="phone" value="{{ $account->phone }}">
                                        </div>
                                    </div>
                                </div>
                                @if ($isClientOrSupplier)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zatca_name">{{ __('الاسم التجاري (ZATCA)') }}</label>
                                                <input class="form-control" type="text" name="zatca_name" id="zatca_name" value="{{ $account->zatca_name }}" placeholder="{{ __('الاسم التجاري') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="vat_number">{{ __('الرقم الضريبي (VAT)') }}</label>
                                                <input class="form-control" type="text" name="vat_number" id="vat_number" value="{{ $account->vat_number }}" placeholder="{{ __('الرقم الضريبي') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="national_id">{{ __('رقم الهوية') }}</label>
                                                <input class="form-control" type="text" name="national_id" id="national_id" value="{{ $account->national_id }}" placeholder="{{ __('رقم الهوية') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zatca_address">{{ __('العنوان الوطني (ZATCA)') }}</label>
                                                <input class="form-control" type="text" name="zatca_address" id="zatca_address" value="{{ $account->zatca_address }}" placeholder="{{ __('العنوان الوطني') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="company_type">{{ __('نوع العميل') }}</label>
                                                <select class="form-control" name="company_type" id="company_type">
                                                    <option value="شركة" {{ $account->company_type == 'شركة' ? 'selected' : '' }}>{{ __('شركة') }}</option>
                                                    <option value="فردي" {{ $account->company_type == 'فردي' ? 'selected' : '' }}>{{ __('فردي') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nationality">{{ __('الجنسية') }}</label>
                                                <input class="form-control" type="text" name="nationality" id="nationality" value="{{ $account->nationality }}" placeholder="{{ __('الجنسية') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="country_id" label="الدولة" column="title" model="App\Models\Country" placeholder="ابحث عن الدولة..." :required="false" :class="'form-select'" :selected="$account->country_id" />
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="city_id" label="المدينة" column="title" model="App\Models\City" placeholder="ابحث عن المدينة..." :required="false" :class="'form-select'" :selected="$account->city_id" />
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="state_id" label="المنطقة" column="title" model="App\Models\State" placeholder="ابحث عن المنطقة..." :required="false" :class="'form-select'" :selected="$account->state_id" />
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="town_id" label="الحي" column="title" model="App\Models\Town" placeholder="ابحث عن الحي..." :required="false" :class="'form-select'" :selected="$account->town_id" />
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="is_stock">{{ __('مخزون') }}</label><br>
                                            <input type="hidden" name="is_stock" value="0">
                                            <input type="checkbox" name="is_stock" id="is_stock" value="1" {{ $account->is_stock ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="secret">{{ __('حساب سري') }}</label><br>
                                            <input type="hidden" name="secret" value="0">
                                            <input type="checkbox" name="secret" id="secret" value="1" {{ $account->secret ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="is_fund">{{ __('حساب صندوق') }}</label><br>
                                            <input type="hidden" name="is_fund" value="0">
                                            <input type="checkbox" name="is_fund" id="is_fund" value="1" {{ $account->is_fund ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="rentable">{{ __('أصل قابل للتأجير') }}</label><br>
                                            <input type="hidden" name="rentable" value="0">
                                            <input type="checkbox" name="rentable" id="rentable" value="1" {{ $account->rentable ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    @if ($parent == 44)
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="employees_expensses">{{ __('حساب رواتب للموظفين') }}</label><br>
                                                <input type="hidden" name="employees_expensses" value="0">
                                                <input type="checkbox" name="employees_expensses" id="employees_expensses" value="1" {{ $account->employees_expensses ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if ($parent == '12')
                                    <div class="alert alert-warning" style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                        {{ __('سيتم اضافة حساب مجمع اهلاك و حساب مصروف اهلاك للأصل') }}
                                    </div>
                                    <input hidden type="text" readonly name="reserve" id="reserve" value="1">
                                @endif
                                <x-branches::branch-select :branches="$branches" :selected="$account->branch_id ?? null" />
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-start">
                                    <button class="btn btn-success m-1" type="submit">
                                        <i class="las la-save"></i> {{ __('تحديث') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection
