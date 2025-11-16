@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير جرد المخزون</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>الرصيد الدفتري</th>
                            <th>الرصيد الفعلي</th>
                            <th>الفرق</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- سيتم عرض بيانات الجرد هنا --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 