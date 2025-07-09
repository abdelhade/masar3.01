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
                    <a href="{{route('reports.overall')}}"><p>محلل العمل اليومي</p></a>
                    <a href="{{ route('journal-summery') }}"><p>اليومية العامة</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير الحسابات</h2>
                    <a href=""><p>شجرة الحسابات</p></a>
                    <a href=""><p>الميزانية العمومية</p></a>
                    <a href=""><p>كشف حساب حساب</p></a>
                    <a href=""><p>ميزان الحسابات</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير المخزون</h2>
                    <a href=""><p>قائمة الاصناف مع الارصدة كل المخازن</p></a>
                    <a href=""><p>قائمة الاصناف مع الارصدة مخزن معين</p></a>
                    <a href=""><p>قائمة الحسابات مع الارصدة</p></a>
                    <a href=""><p>حركة الصنف</p></a>
                    <a href=""><p>ميزان الاصناف</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير المبيعات</h2>
                    <a href=""><p>تقرير المبيعات اليومية</p></a>
                    <a href=""><p>تقرير المبيعات اجماليات</p></a>
                    <a href=""><p>تقرير المبيعات اصناف</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير المشتريات</h2>
                    <a href=""><p>تقرير المشتريات اليومية</p></a>
                    <a href=""><p>تقرير المشتريات اجماليات</p></a>
                    <a href=""><p>تقرير المشتريات اصناف</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير العملاء</h2>
                    <a href=""><p>تقرير العملاء اليومية</p></a>
                    <a href=""><p>تقرير العملاء اجماليات</p></a>
                    <a href=""><p>تقرير العملاء اصناف</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير الموردين</h2>
                    <a href=""><p>تقرير الموردين اليومية</p></a>
                    <a href=""><p>تقرير الموردين اجماليات</p></a>
                    <a href=""><p>تقرير الموردين اصناف</p></a>
                </div>
ذ
                <div class="col-md-4">
                    <h2>تقارير المصروفات</h2>
                    <a href=""><p>قائمة الاصناف مع الارصدة</p></a>
                    <a href=""><p>كشف حساب مصروف</p></a>
                    <a href=""><p>ميزان المصروفات</p></a>
                </div>

                <div class="col-md-4">
                    <h2>تقارير مراكز التكلفة</h2>
                    <a href=""><p>قائمة مراكز التكلفة</p></a>
                    <a href=""><p>كشف حساب مركز التكلفة</p></a>
                    <a href=""><p>كشف حساب عام مع مركز تكلفة</p></a>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
