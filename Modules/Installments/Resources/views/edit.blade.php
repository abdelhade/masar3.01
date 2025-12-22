@extends('admin.dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @livewire('installments::edit-installment-plan', ['plan' => $plan])
            </div>
        </div>
    </div>
@endsection
