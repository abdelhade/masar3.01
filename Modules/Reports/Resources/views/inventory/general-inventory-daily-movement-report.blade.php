@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير حركة المخزون اليومية</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>الصنف</th>
                            <th>الكمية الداخلة</th>
                            <th>الكمية الخارجة</th>
                            <th>الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- سيتم عرض بيانات الحركة هنا --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 