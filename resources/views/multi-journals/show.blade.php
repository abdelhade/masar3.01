@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.journals')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Multi Journal Details'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Multi Journals'), 'url' => route('multi-journals.index')],
            ['label' => __('Multi Journal Details')],
        ],
    ])

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('Multi Journal Details') }}: #{{ $oper->pro_id }}</h4>
                        <div class="d-flex gap-2">
                            @can('edit multi-journals')
                                <a href="{{ route('multi-journals.edit', $oper) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                </a>
                            @endcan
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> {{ __('Print') }}
                            </button>
                            <a href="{{ route('multi-journals.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card printable-content">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-book"></i> {{ __('Multi Journal Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Operation Number') }}:</label>
                                <div class="form-control-static">{{ $oper->pro_id }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Operation Type') }}:</label>
                                <div class="form-control-static">
                                    {{ $oper->type->ptext ?? __('N/A') }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Date') }}:</label>
                                <div class="form-control-static">{{ $oper->pro_date ? \Carbon\Carbon::parse($oper->pro_date)->format('Y-m-d') : __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Serial Number') }}:</label>
                                <div class="form-control-static">{{ $oper->pro_serial ?? __('N/A') }}</div>
                            </div>
                        </div>

                        @if($oper->employee)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Employee') }}:</label>
                                <div class="form-control-static">{{ $oper->employee->aname ?? __('N/A') }}</div>
                            </div>
                        </div>
                        @endif

                        @if($oper->details)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Details') }}:</label>
                                <div class="form-control-static">{{ $oper->details }}</div>
                            </div>
                        </div>
                        @endif

                        @if($oper->info)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Notes') }}:</label>
                                <div class="form-control-static">{{ $oper->info }}</div>
                            </div>
                        </div>
                        @endif

                        @if($details->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">{{ __('Journal Details') }}:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Account') }}</th>
                                                <th>{{ __('Debit') }}</th>
                                                <th>{{ __('Credit') }}</th>
                                                <th>{{ __('Info') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($details as $detail)
                                            <tr>
                                                <td>{{ $detail->accountHead->aname ?? __('N/A') }}</td>
                                                <td>{{ number_format($detail->debit ?? 0, 2) }}</td>
                                                <td>{{ number_format($detail->credit ?? 0, 2) }}</td>
                                                <td>{{ $detail->info ?? __('N/A') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-end">{{ __('Total') }}:</th>
                                                <th>{{ number_format($details->sum('debit'), 2) }}</th>
                                                <th>{{ number_format($details->sum('credit'), 2) }}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .form-control-static {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            min-height: 2.5rem;
            display: flex;
            align-items: center;
        }

        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; }
            .card-header { background: #f1f1f1 !important; color: #000 !important; }
            body { font-size: 12px; }
            .form-control-static { background: #fff !important; border: 1px solid #000 !important; }
            .table { font-size: 10px; }
        }
    </style>
    @endpush
@endsection

