@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير النقدية والبنوك</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>الحساب</th>
                            <th>الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- سيتم عرض بيانات النقدية والبنوك هنا --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 