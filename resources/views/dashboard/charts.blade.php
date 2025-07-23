@extends('dashboard.layout')
@section('content')
    @include('dashboard.components.summary-cards')
    @include('dashboard.components.summary-tables')
    <div class="row">
        @for($i = 1; $i <= 20; $i++)
            <div class="col-md-6 col-lg-4 mb-4">
                @include('dashboard.components.chart' . $i)
            </div>
        @endfor
    </div>
@endsection 