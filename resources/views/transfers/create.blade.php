@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.transfers')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => $pageTitle ?? 'إضافة تحويل نقدي',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'التحويلات النقدية', 'url' => route('transfers.index')],
            ['label' => 'إضافة جديد'],
        ],
    ])

    <div class="content-wrapper">
        <section class="content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- الـ Form الأساسي --}}
            <form action="{{ route('transfers.store') }}" method="POST">
                @csrf
                <input type="text" name="pro_type" value="{{ $pro_type }}" hidden>

                <div class="row">
                    {{-- عمود الفورم الرئيسي --}}
                    <div class="col-lg-9">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h2 class="card-title">{{ $pageTitle ?? 'إضافة تحويل نقدي' }}</h2>
                            </div>

                            <div class="card-body">
                                {{-- بيانات الفاتورة --}}
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">رقم العملية</label>
                                        <input type="text" name="pro_id" class="form-control"
                                            value="{{ $newProId }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">الرقم الدفتري</label>
                                        <input type="text" name="pro_serial" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">رقم الإيصال</label>
                                        <input type="text" name="pro_num" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">التاريخ</label>
                                        <input type="date" name="pro_date" class="form-control"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">المبلغ</label>
                                        <input type="number" step="0.01" name="pro_value" id="pro_value"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">البيان</label>
                                        <input type="text" name="details" class="form-control">
                                    </div>
                                </div>

                                {{-- الحسابات --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">من حساب</label>
                                        <select name="acc2" class="form-control">
                                            <option value="">اختر الحساب</option>
                                            @foreach ($fromAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">إلى حساب</label>
                                        <select name="acc1" class="form-control">
                                            <option value="">اختر الحساب</option>
                                            @foreach ($toAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- الموظفين --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">الموظف</label>
                                        <select name="emp_id" class="form-control">
                                            <option value="">اختر موظف</option>
                                            @foreach ($employeeAccounts as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">مندوب التحصيل</label>
                                        <select name="emp2_id" class="form-control">
                                            <option value="">اختر مندوب</option>
                                            @foreach ($employeeAccounts as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->aname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- التكلفة والملاحظات --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">مركز التكلفة</label>
                                        <select name="cost_center" class="form-control">
                                            <option value="">بدون مركز تكلفة</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">ملاحظات</label>
                                        <input type="text" name="info" class="form-control">
                                    </div>
                                </div>

                                {{-- Branch Select Component --}}
                                <x-branches::branch-select :branches="$branches" />
                            </div>

                            <div class="card-footer d-flex justify-content-start">
                                <button type="submit" class="btn btn-main m-1">
                                    <i class="ki-outline ki-check fs-3"></i>
                                    تأكيد
                                </button>
                                <button type="reset" class="btn btn-danger m-1">
                                    <i class="ki-outline ki-cross fs-3"></i>
                                    مسح
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- عمود الـ Currency Converter --}}
                    {{-- <div class="col-lg-3">
                        <x-settings::currency-converter-mini :inline="false" sourceField="#pro_value" :showAmount="true"
                            :showResult="true" />
                    </div> --}}
                </div>
            </form>
        </section>
    </div>
@endsection

{{-- Debug Scripts --}}
@push('scripts')
    <script>
        // Test 2: Check if currency-converter.js is loaded
        setTimeout(() => {
            // Test the API endpoint directly
            fetch('/currencies/active')
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.currencies) {} else {
                        console.error('❌ API returned invalid format');
                    }
                })
                .catch(error => {
                    console.error('❌ API Test failed:', error);
                });
        }, 1000);
    </script>
@endpush
