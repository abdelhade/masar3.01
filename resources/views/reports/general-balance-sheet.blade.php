@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('الميزانية العمومية') }}</h2>
            <div class="text-muted">{{ __('حتى تاريخ:') }} {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="as_of_date">{{ __('حتى تاريخ:') }}</label>
                    <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">{{ __('توليد التقرير') }}</button>
                </div>
            </div>

            <div class="row">
                <!-- الأصول -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4>{{ __('الأصول') }}</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('الحساب') }}</th>
                                        <th class="text-end">{{ __('المبلغ') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assets as $asset)
                                    <tr>
                                        <td>{{ $asset->code }} - {{ $asset->aname }}</td>
                                        <td class="text-end">{{ number_format($asset->balance, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-primary">
                                        <th>{{ __('إجمالي الأصول') }}</th>
                                        <th class="text-end">{{ number_format($totalAssets, 2) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- الخصوم وحقوق الملكية -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4>{{ __('الخصوم وحقوق الملكية') }}</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('الحساب') }}</th>
                                        <th class="text-end">{{ __('المبلغ') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($liabilities as $liability)
                                    <tr>
                                        <td>{{ $liability->code }} - {{ $liability->aname }}</td>
                                        <td class="text-end">{{ number_format($liability->balance, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    @foreach($equity as $eq)
                                    <tr>
                                        <td>{{ $eq->code }} - {{ $eq->aname }}</td>
                                        <td class="text-end">{{ number_format($eq->balance, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-success">
                                        <th>{{ __('إجمالي الخصوم وحقوق الملكية') }}</th>
                                        <th class="text-end">{{ number_format($totalLiabilitiesEquity, 2) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert {{ $totalAssets == $totalLiabilitiesEquity ? 'alert-success' : 'alert-warning' }}">
                        <strong>{{ __('النتيجة:') }}</strong> 
                        @if($totalAssets == $totalLiabilitiesEquity)
                            {{ __('الميزانية متوازنة ✓') }}
                        @else
                            {{ __('الميزانية غير متوازنة - الفرق: :diff', ['diff' => number_format(abs($totalAssets - $totalLiabilitiesEquity), 2)]) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 