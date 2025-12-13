@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.transfers')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Transfer Details'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Transfers'), 'url' => route('transfers.index')],
            ['label' => __('Transfer Details')],
        ],
    ])

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title d-flex align-items-center gap-2">
                            {{ __('Transfer Details') }}: #{{ $transfer->pro_id }}
                            @switch($type)
                                @case('cash-to-cash')
                                    - {{ __('Cash to Cash') }}
                                @break
                                @case('cash-to-bank')
                                    - {{ __('Cash to Bank') }}
                                @break
                                @case('bank-to-cash')
                                    - {{ __('Bank to Cash') }}
                                @break
                                @case('bank-to-bank')
                                    - {{ __('Bank to Bank') }}
                                @break
                            @endswitch
                            @if($transfer->is_journal)
                                @livewire('operation-constraints-button', ['operheadId' => $transfer->id])
                            @endif
                        </h4>
                        <div class="d-flex gap-2">
                            @php
                                $typeSlugs = [3 => 'cash-to-cash', 4 => 'cash-to-bank', 5 => 'bank-to-cash', 6 => 'bank-to-bank'];
                                $slug = $typeSlugs[$transfer->pro_type] ?? null;
                            @endphp
                            @if(($slug && \Illuminate\Support\Facades\Gate::allows("edit {$slug}")) || \Illuminate\Support\Facades\Gate::allows('edit transfers'))
                                <a href="{{ route('transfers.edit', $transfer) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                </a>
                            @endif
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> {{ __('Print') }}
                            </button>
                            <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
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
                        <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> {{ __('Transfer Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Operation Number') }}:</label>
                                <div class="form-control-static">{{ $transfer->pro_id }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Operation Type') }}:</label>
                                <div class="form-control-static">
                                    {{ $transfer->type->ptext ?? __('N/A') }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Date') }}:</label>
                                <div class="form-control-static">{{ $transfer->pro_date ? \Carbon\Carbon::parse($transfer->pro_date)->format('Y-m-d') : __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Serial Number') }}:</label>
                                <div class="form-control-static">{{ $transfer->pro_serial ?? __('N/A') }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('From Account') }} ({{ __('Debit') }}):</label>
                                <div class="form-control-static">{{ $transfer->acc1Head->aname ?? __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('To Account') }} ({{ __('Credit') }}):</label>
                                <div class="form-control-static">{{ $transfer->acc2Head->aname ?? __('N/A') }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Amount') }}:</label>
                                <div class="form-control-static">
                                    <h4 class="mb-0">{{ number_format($transfer->pro_value ?? 0, 2) }}</h4>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Employee') }}:</label>
                                <div class="form-control-static">{{ $transfer->employee->aname ?? __('N/A') }}</div>
                            </div>
                        </div>

                        @if($transfer->details)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Details') }}:</label>
                                <div class="form-control-static">{{ $transfer->details }}</div>
                            </div>
                        </div>
                        @endif

                        @if($transfer->info)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Notes') }}:</label>
                                <div class="form-control-static">{{ $transfer->info }}</div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('User') }}:</label>
                                <div class="form-control-static">{{ $transfer->user->name ?? __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Created At') }}:</label>
                                <div class="form-control-static">{{ $transfer->created_at ? \Carbon\Carbon::parse($transfer->created_at)->format('Y-m-d H:i') : __('N/A') }}</div>
                            </div>
                        </div>

                        @if($transfer->journalHead && $transfer->journalHead->dets->count() > 0)
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
                                            @foreach($transfer->journalHead->dets as $detail)
                                            <tr>
                                                <td>{{ $detail->accHead->aname ?? __('N/A') }}</td>
                                                <td>{{ number_format($detail->debit ?? 0, 2) }}</td>
                                                <td>{{ number_format($detail->credit ?? 0, 2) }}</td>
                                                <td>{{ $detail->info ?? __('N/A') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-end">{{ __('Total') }}:</th>
                                                <th>{{ number_format($transfer->journalHead->dets->sum('debit'), 2) }}</th>
                                                <th>{{ number_format($transfer->journalHead->dets->sum('credit'), 2) }}</th>
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

