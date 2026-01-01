@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    <div class="container-fluid px-4 py-5">
        <h1 class="mb-4">{{ __('Invoice Track Search') }}</h1>

        <form action="" method="get"
            onsubmit="event.preventDefault(); var id = document.getElementById('operation_id').value; if(id) window.location.href = '/invoices/track/' + encodeURIComponent(id);">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="operation_id">{{ __('Operation Number / pro_id') }}</label>
                        <input id="operation_id" name="operation_id" class="form-control"
                            placeholder="{{ __('Enter the operation number or pro_id') }}">
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-2"></i>
                            {{ __('View Track') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
