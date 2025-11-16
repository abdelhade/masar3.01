@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.installments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Overdue Installments'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Overdue Installments')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('List of all overdue installments in the system') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Plan Number') }}</th>
                                    <th>{{ __('Installment Number') }}</th>
                                    <th>{{ __('Amount Due') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Days Overdue') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overduePayments as $payment)
                                    <tr class="text-center">
                                        <td>{{ $payment->plan->client->name ?? 'N/A' }}</td>
                                        <td>{{ $payment->plan->id }}</td>
                                        <td>{{ $payment->installment_number }}</td>
                                        <td>{{ number_format($payment->amount_due, 2) }}</td>
                                        <td>{{ $payment->due_date->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $payment->due_date->diffInDays(today()) }}
                                                {{ __('Day(s)') }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('view Overdue Installments')
                                                <a class="btn btn-info btn-sm"
                                                    href="{{ route('installments.plans.show', $payment->plan->id) }}">
                                                    {{ __('View Plan') }}
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="alert alert-success py-3 mb-0">
                                                <i class="las la-check-circle me-2"></i>
                                                {{ __('There are currently no overdue installments.') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $overduePayments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
