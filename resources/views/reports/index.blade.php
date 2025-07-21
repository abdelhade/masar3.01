@extends('admin.dashboard')

@section('content')
    <style>
        h2 {
            font-size: 18px;
            border: 1px solid rgb(222, 222, 222);
            padding: 5px;
        }
    </style>

    <div class="container">
        <div class="card">
            <div class="card-head">
                <h1>التقارير</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h2>التقارير العامة</h2>
                        <a href="{{ route('reports.overall') }}">
                            <p>محلل العمل اليومي</p>
                        </a>
                        <a href="{{ route('journal-summery') }}">
                            <p>اليومية العامة</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير الحسابات</h2>
                        <a href="{{ route('accounts.tree') }}">
                            <p>شجرة الحسابات</p>
                        </a>
                        <a href="{{ route('reports.general-balance-sheet') }}">
                            <p>الميزانية العمومية</p>
                        </a>
                        <a href="{{ route('reports.general-account-statement') }}">
                            <p>كشف حساب حساب</p>
                        </a>
                        <a href="{{ route('reports.general-account-balances') }}">
                            <p>ميزان الحسابات</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير المخزون</h2>
                        <a href="{{ route('reports.general-inventory-balances') }}">
                            <p>قائمة الاصناف مع الارصدة كل المخازن</p>
                        </a>
                        <a href="{{ route('reports.general-inventory-balances-by-store') }}">
                            <p>قائمة الاصناف مع الارصدة مخزن معين</p>
                        </a>
                        <a href="{{ route('reports.general-account-balances-by-store') }}">
                            <p>قائمة الحسابات مع الارصدة</p>
                        </a>
                        <a href="{{ route('reports.general-inventory-movements') }}">
                            <p>حركة الصنف</p>
                        </a>
                        <a href="{{ route('reports.general-inventory-balances') }}">
                            <p>ميزان الاصناف</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير المبيعات</h2>
                        <a href="{{ route('reports.general-sales-daily-report') }}">
                            <p>تقرير المبيعات اليومية</p>
                        </a>
                        <a href="{{ route('reports.general-sales-total-report') }}">
                            <p>تقرير المبيعات اجماليات</p>
                        </a>
                        <a href="{{ route('reports.general-sales-items-report') }}">
                            <p>تقرير المبيعات اصناف</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير المشتريات</h2>
                        <a href="{{ route('reports.general-purchases-daily-report') }}">
                            <p>تقرير المشتريات اليومية</p>
                        </a>
                        <a href="{{ route('reports.general-purchases-total-report') }}">
                            <p>تقرير المشتريات اجماليات</p>
                        </a>
                        <a href="{{ route('reports.general-purchases-items-report') }}">
                            <p>تقرير المشتريات اصناف</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير العملاء</h2>
                        <a href="{{ route('reports.general-customers-daily-report') }}">
                            <p>تقرير العملاء اليومية</p>
                        </a>
                        <a href="{{ route('reports.general-customers-total-report') }}">
                            <p>تقرير العملاء اجماليات</p>
                        </a>
                        <a href="{{ route('reports.general-customers-items-report') }}">
                            <p>تقرير العملاء اصناف</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير الموردين</h2>
                        <a href="{{ route('reports.general-suppliers-daily-report') }}">
                            <p>تقرير الموردين اليومية</p>
                        </a>
                        <a href="{{ route('reports.general-suppliers-total-report') }}">
                            <p>تقرير الموردين اجماليات</p>
                        </a>
                        <a href="{{ route('reports.general-suppliers-items-report') }}">
                            <p>تقرير الموردين اصناف</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير المصروفات</h2>
                        <a href="{{ route('reports.general-expenses-report') }}">
                            <p>قائمة الاصناف مع الارصدة</p>
                        </a>
                        <a href="{{ route('reports.general-expenses-daily-report') }}">
                            <p>كشف حساب مصروف</p>
                        </a>
                        <a href="{{ route('reports.expenses-balance-report') }}">
                            <p>ميزان المصروفات</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير مراكز التكلفة</h2>
                        <a href="{{ route('reports.general-cost-centers-list') }}">
                            <p>قائمة مراكز التكلفة</p>
                        </a>
                        <a href="{{ route('reports.general-cost-center-account-statement') }}">
                            <p>كشف حساب مركز التكلفة</p>
                        </a>
                        <a href="{{ route('reports.general-account-statement-with-cost-center') }}">
                            <p>كشف حساب عام مع مركز تكلفة</p>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <h2>تقارير الاصناف </h2>
                        <a href="{{ route('reports.get-items-max-min-quantity') }}">
                            <p>مراقبة كميات الأصناف </p>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
