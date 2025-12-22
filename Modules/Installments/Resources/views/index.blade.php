@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.installments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Installment Plans'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Installment Plans')],
        ],
    ])
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            @can('create Installment Plans')
                <a href="{{ route('installments.plans.create') }}" type="button" class="btn btn-primary fw-bold">
                    {{ __('Add New Installment Plan') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
                <br>
                <br>
            @endcan
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Number of Installments') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($installmentPlans as $plan)
                                    <tr class="text-center">
                                        <td> {{ $plan->id }} </td>
                                        <td>{{ $plan->account->aname ?? 'N/A' }} ({{ $plan->account->code ?? '' }})</td>
                                        <td>{{ number_format($plan->total_amount, 2) }}</td>
                                        <td>{{ $plan->number_of_installments }}</td>
                                        <td>{{ $plan->start_date->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-success">{{ $plan->status }}</span></td>
                                        <td>
                                            @can('view Installment Plans')
                                                <a class="btn btn-info btn-icon-square-sm"
                                                    href="{{ route('installments.plans.show', $plan->id) }}">
                                                    <i class="las la-eye"></i>
                                                </a>
                                            @endcan

                                            @can('edit Installment Plans')
                                                <a href="{{ route('installments.plans.edit', $plan->id) }}"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('delete Installment Plans')
                                                <form action="{{ route('installments.plans.destroy', $plan->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذه الخطة؟ سيتم حذف جميع الأقساط والقيود المحاسبية المرتبطة بها.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="alert alert-info py-3 mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No data available') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
