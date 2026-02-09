@extends('admin.dashboard')

{{-- Dynamic Sidebar: نعرض فقط الحسابات --}}
@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    @php
        // تحديد نوع الحساب بدقة - فحص الأطول قبل الأقصر
        $parent = request()->get('parent');
        if (!$parent) {
            if (str_starts_with($account->code, '1202')) {
                $parent = '1202'; // الأصول القابلة للتأجير
            } elseif (str_starts_with($account->code, '12')) {
                $parent = '12'; // الأصول الثابتة
            } else {
                $parent = substr($account->code, 0, -3);
            }
        }
        $isClientOrSupplier = in_array(substr($account->code, 0, 4), ['1103', '2101']);
        // خريطة تربط parent_id بنوع الحساب
        $parentTypeMap = [
            '1103' => '1', // العملاء
            '2101' => '2', // الموردين
            '1101' => '3', // الصناديق
            '1102' => '4', // البنوك
            '2102' => '5', //الموظفين
            '1104' => '6', // المخازن
            '5' => '7', // المصروفات
            '42' => '8', // الإيرادات
            '2104' => '9', // دائنين اخرين
            '1106' => '10', // مدينين آخرين
            '31' => '11', // الشريك الرئيسي
            '32' => '12', // جاري الشريك
            '12' => '13', // الأصول
            '1202' => '14', // الأصول القابلة للتأجير
        ];
        $type = $parentTypeMap[$parent] ?? '0';
    @endphp
    @include('components.breadcrumb', [
        'title' => __('Edit Account'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Edit')]],
    ])
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <section class="content">
                    <form id="updateAccountForm" action="{{ route('accounts.update', $account->id) }}" method="POST"
                        onsubmit="console.log('Form submitting to:', this.action, 'Method: POST'); return true;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $account->id }}">
                        <input type="hidden" name="parent" value="{{ $parent }}">
                        <input type="hidden" name="q" value="{{ $parent }}">
                        
                        <!-- Action Buttons at the top -->
                        <div class="card-footer mb-3">
                            <div class="d-flex justify-content-start">
                                <button class="btn btn-success m-1" type="submit" id="updateBtn">
                                    <i class="las la-save"></i> {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                        
                        <div class="card card-info">
                            <div class="card-header">
                                <h3>{{ __('Edit Account') }}</h3>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <strong>✓</strong> {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>✗</strong> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

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
                                            <label for="code">{{ __('Code') }} <span
                                                    class="text-danger">*</span></label>
                                            <input required readonly class="form-control font-bold" type="text"
                                                name="code" value="{{ $account->code }}" id="code">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aname">{{ __('Name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input required class="form-control font-bold frst" type="text"
                                                name="aname" value="{{ $account->aname }}" id="aname">
                                            <div id="resaname"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="is_basic">{{ __('Account Type') }} <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control font-bold" name="is_basic" id="is_basic">
                                                <option value="1" {{ $account->is_basic == 1 ? 'selected' : '' }}>
                                                    {{ __('Basic') }}</option>
                                                <option value="0" {{ $account->is_basic == 0 ? 'selected' : '' }}>
                                                    {{ __('Regular Account') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="parent_id">{{ __('Parent Account') }} <span
                                                    class="text-danger">*</span></label>
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
                                            <label for="phone">{{ __('Phone') }}</label>
                                            <input class="form-control font-bold" type="text" name="phone"
                                                id="phone" value="{{ $account->phone }}">
                                        </div>
                                    </div>
                                </div>
                                @if ($isClientOrSupplier)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zatca_name">{{ __('Trade Name (ZATCA)') }}</label>
                                                <input class="form-control" type="text" name="zatca_name" id="zatca_name"
                                                    value="{{ $account->zatca_name }}"
                                                    placeholder="{{ __('Trade Name') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="vat_number">{{ __('VAT Number') }}</label>
                                                <input class="form-control" type="text" name="vat_number"
                                                    id="vat_number" value="{{ $account->vat_number }}"
                                                    placeholder="{{ __('VAT Number') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="national_id">{{ __('National ID') }}</label>
                                                <input class="form-control" type="text" name="national_id"
                                                    id="national_id" value="{{ $account->national_id }}"
                                                    placeholder="{{ __('National ID') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zatca_address">{{ __('National Address (ZATCA)') }}</label>
                                                <input class="form-control" type="text" name="zatca_address"
                                                    id="zatca_address" value="{{ $account->zatca_address }}"
                                                    placeholder="{{ __('National Address') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="company_type">{{ __('Client Type') }}</label>
                                                <select class="form-control" name="company_type" id="company_type">
                                                    <option value="">{{ __('Select Type') }}</option>
                                                    <option value="شركة"
                                                        {{ $account->company_type == 'شركة' ? 'selected' : '' }}>
                                                        {{ __('Company') }}</option>
                                                    <option value="فردي"
                                                        {{ $account->company_type == 'فردي' ? 'selected' : '' }}>
                                                        {{ __('Individual') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nationality">{{ __('Nationality') }}</label>
                                                <input class="form-control" type="text" name="nationality"
                                                    id="nationality" value="{{ $account->nationality }}"
                                                    placeholder="{{ __('Nationality') }}">
                                            </div>
                                        </div>
                                    </div>
                                    @if (substr($account->code, 0, 4) === '1103')
                                        {{-- حقل حد الائتمان للعملاء فقط --}}
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="debit_limit">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                        {{ __('Credit Limit Allowed') }}
                                                    </label>
                                                    <input class="form-control" type="number" step="0.001"
                                                        name="debit_limit" id="debit_limit"
                                                        value="{{ $account->debit_limit }}" placeholder="0.000">
                                                    <small
                                                        class="text-muted">{{ __('Leave empty for no limit') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="country_id" :label="__('Country')" column="title"
                                                model="Modules\HR\Models\Country" :placeholder="__('Search for country...')"
                                                :required="false" :class="'form-select'" :selected="$account->country_id" />
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="city_id" :label="__('City')" column="title"
                                                model="Modules\HR\Models\City" :placeholder="__('Search for city...')" :required="false"
                                                :class="'form-select'" :selected="$account->city_id" />
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="state_id" :label="__('State')" column="title"
                                                model="Modules\HR\Models\State" :placeholder="__('Search for state...')"
                                                :required="false" :class="'form-select'" :selected="$account->state_id" />
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <x-dynamic-search name="town_id" :label="__('District')" column="title"
                                                model="Modules\HR\Models\Town" :placeholder="__('Search for district...')" :required="false"
                                                :class="'form-select'" :selected="$account->town_id" />
                                        </div>
                                    </div>
                                @endif
                                {{-- Hidden flags: keep values but do not show editable checkboxes on edit form --}}
                                @php
                                    $edit_is_stock = $account->is_stock ? 1 : 0;
                                    $edit_secret = $account->secret ? 1 : 0;
                                    $edit_is_fund = $account->is_fund ? 1 : 0;
                                    $edit_rentable = $account->rentable ? 1 : 0;
                                    $edit_employees_expensses = $account->employees_expensses ? 1 : 0;
                                @endphp

                                <input type="hidden" name="is_stock" value="{{ $edit_is_stock }}">
                                <input type="hidden" name="secret" value="{{ $edit_secret }}">
                                <input type="hidden" name="is_fund" value="{{ $edit_is_fund }}">
                                <input type="hidden" name="rentable" value="{{ $edit_rentable }}">
                                @if ($parent == 44)
                                    <input type="hidden" name="employees_expensses"
                                        value="{{ $edit_employees_expensses }}">
                                @endif

                                {{-- Summary badges (non-editable) to indicate current flags --}}
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <small class="text-muted">
                                            {{ __('Current Account Settings') }}:
                                            @if ($edit_is_stock)
                                                <span class="badge bg-info me-1">{{ __('Inventory') }}</span>
                                            @endif
                                            @if ($edit_is_fund)
                                                <span class="badge bg-success me-1">{{ __('Fund/Bank') }}</span>
                                            @endif
                                            @if ($edit_rentable)
                                                <span class="badge bg-warning me-1">{{ __('Rentable Asset') }}</span>
                                            @endif
                                            @if ($edit_secret)
                                                <span class="badge bg-secondary me-1">{{ __('Secret Account') }}</span>
                                            @endif
                                            @if ($parent == 44 && $edit_employees_expensses)
                                                <span class="badge bg-primary me-1">{{ __('Salary Account') }}</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                @if ($parent == '12' || $parent == '1202')
                                    <div class="alert alert-warning"
                                        style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                        {{ __('Accumulated depreciation account and depreciation expense account will be added for the asset') }}
                                    </div>
                                    <input hidden type="text" readonly name="reserve" id="reserve" value="1">
                                @endif
                                <x-branches::branch-select :branches="$branches" :selected="$account->branch_id ?? null" />

                                @if (isMultiCurrencyEnabled())
                                    <div class="col-md-6 mb-3">
                                        <label for="currency_id" class="form-label">{{ __('Currency') }}</label>
                                        <select name="currency_id" id="currency_id"
                                            class="form-select @error('currency_id') is-invalid @enderror">
                                            <option value="">{{ __('Default Currency') }}</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{-- في حالة التعديل أو وجود خطأ validation --}}
                                                    @selected(old('currency_id', $account->currency_id ?? null) == $currency->id)>
                                                    {{ $currency->name }} ({{ $currency->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('currency_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                @else
                                    {{-- حقل مخفي للتأكد من إرسال null إذا لم يكن الحقل موجوداً --}}
                                    <input type="hidden" name="currency_id" value="">
                                @endif

                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection
