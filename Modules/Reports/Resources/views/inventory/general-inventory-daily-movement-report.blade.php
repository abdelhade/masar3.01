@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Daily Inventory Movement Report') }}</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('Inbound Quantity') }}</th>
                                <th>{{ __('Outbound Quantity') }}</th>
                                <th>{{ __('Balance') }}</th>
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
