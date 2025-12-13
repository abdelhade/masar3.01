@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.items')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Item Details'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Items'), 'url' => route('items.index')],
            ['label' => __('Item Details')],
        ],
    ])

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('Item Details') }}: {{ $item->name ?? '#' . $item->id }}</h4>
                        <div class="d-flex gap-2">
                            @can('edit items')
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                </a>
                            @endcan
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> {{ __('Print') }}
                            </button>
                            <a href="{{ route('items.index') }}" class="btn btn-secondary">
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
                        <h5 class="mb-0"><i class="fas fa-box"></i> {{ __('Item Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($item->getAttributes() as $key => $value)
                                @if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at', 'isdeleted']))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}:</label>
                                    <div class="form-control-static">
                                        @if($value)
                                            @if($key == 'is_active')
                                                {{ $value ? __('Active') : __('Inactive') }}
                                            @elseif($key == 'type' && is_object($value))
                                                {{ $value->value ?? $value }}
                                            @else
                                                {{ is_array($value) ? json_encode($value) : $value }}
                                            @endif
                                        @else
                                            {{ __('N/A') }}
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        @if($item->units->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">{{ __('Units') }}:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Unit') }}</th>
                                                <th>{{ __('Conversion Value') }}</th>
                                                <th>{{ __('Cost') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->units as $unit)
                                            <tr>
                                                <td>{{ $unit->name ?? __('N/A') }}</td>
                                                <td>{{ $unit->pivot->u_val ?? __('N/A') }}</td>
                                                <td>{{ number_format($unit->pivot->cost ?? 0, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($item->prices->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">{{ __('Prices') }}:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Price List') }}</th>
                                                <th>{{ __('Unit') }}</th>
                                                <th>{{ __('Price') }}</th>
                                                <th>{{ __('Discount') }}</th>
                                                <th>{{ __('Tax Rate') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->prices as $price)
                                            <tr>
                                                <td>{{ $price->name ?? __('N/A') }}</td>
                                                <td>{{ $price->pivot->unit_id ?? __('N/A') }}</td>
                                                <td>{{ number_format($price->pivot->price ?? 0, 2) }}</td>
                                                <td>{{ number_format($price->pivot->discount ?? 0, 2) }}</td>
                                                <td>{{ number_format($price->pivot->tax_rate ?? 0, 2) }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
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

