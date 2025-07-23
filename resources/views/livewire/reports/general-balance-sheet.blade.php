<div class="container">
    <div class="card shadow-lg">
        <div class="card-head p-4 bg-gradient" style="background: linear-gradient(90deg, #4361ee 0%, #4cc9f0 100%); color: white; border-radius: 10px 10px 0 0;">
            <h2 class="mb-1"><i class="fas fa-balance-scale me-2"></i> {{ __('الميزانية العمومية') }}</h2>
            <div class="text-muted">{{ __('حتى تاريخ:') }} {{ $asOfDate }}</div>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="as_of_date">{{ __('حتى تاريخ:') }}</label>
                    <input type="date" id="as_of_date" class="form-control" wire:model.lazy="asOfDate">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">
                        <i class="fas fa-sync-alt me-1"></i> {{ __('توليد التقرير') }}
                    </button>
                </div>
            </div>
            <div class="row g-4">
                <!-- الأصول -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white d-flex align-items-center">
                            <i class="fas fa-university me-2"></i>
                            <h4 class="mb-0">{{ __('الأصول') }}</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('الحساب') }}</th>
                                        <th class="text-end">{{ __('المبلغ') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assets as $asset)
                                    <tr>
                                        <td>{{ $asset->code }} - {{ $asset->aname }}</td>
                                        <td class="text-end">{{ number_format($asset->balance, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">{{ __('لا توجد بيانات للأصول.') }}</td>
                                    </tr>
                                    @endforelse
                                    <tr class="table-primary fw-bold">
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
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-success text-white d-flex align-items-center">
                            <i class="fas fa-piggy-bank me-2"></i>
                            <h4 class="mb-0">{{ __('الخصوم وحقوق الملكية') }}</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('الحساب') }}</th>
                                        <th class="text-end">{{ __('المبلغ') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($liabilities as $liability)
                                    <tr>
                                        <td>{{ $liability->code }} - {{ $liability->aname }}</td>
                                        <td class="text-end">{{ number_format($liability->balance, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">{{ __('لا توجد بيانات للخصوم.') }}</td>
                                    </tr>
                                    @endforelse
                                    @forelse($equity as $eq)
                                    <tr>
                                        <td>{{ $eq->code }} - {{ $eq->aname }}</td>
                                        <td class="text-end">{{ number_format($eq->balance, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">{{ __('لا توجد بيانات لحقوق الملكية.') }}</td>
                                    </tr>
                                    @endforelse
                                    <tr class="table-success fw-bold">
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
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert {{ $totalAssets == $totalLiabilitiesEquity ? 'alert-success' : 'alert-danger' }} d-flex align-items-center">
                        @if($totalAssets == $totalLiabilitiesEquity)
                            <i class="fas fa-check-circle fa-lg me-2"></i>
                            <strong class="me-2">{{ __('الميزانية متوازنة ✓') }}</strong>
                        @else
                            <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
                            <strong class="me-2">{{ __('الميزانية غير متوازنة!') }}</strong>
                            <span>{{ __('الفرق: :diff', ['diff' => number_format(abs($totalAssets - $totalLiabilitiesEquity), 2)]) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 