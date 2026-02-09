<div>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ __('Asset Depreciation Schedule') }}
                </h2>
                <div class="text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('View expected depreciation schedule for assets') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('General Statistics') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $assets->total() }}</h4>
                                <small class="text-muted">{{ __('Total Assets') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h4 class="text-success">{{ number_format($assets->sum('purchase_cost'), 0) }}</h4>
                                <small class="text-muted">{{ __('Total Cost') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h4 class="text-warning">{{ number_format($assets->sum('accumulated_depreciation'), 0) }}</h4>
                                <small class="text-muted">{{ __('Total Depreciation') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                @php
                                    $totalCost = $assets->sum('purchase_cost');
                                    $totalDepreciation = $assets->sum('accumulated_depreciation');
                                    $netBookValue = $totalCost - $totalDepreciation;
                                @endphp
                                <h4 class="text-info">{{ number_format($netBookValue, 0) }}</h4>
                                <small class="text-muted">{{ __('Total Book Value') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                @php
                                    $fullyDepreciated = $assets->filter(function($asset) {
                                        return $asset->isFullyDepreciated();
                                    })->count();
                                @endphp
                                <h4 class="text-danger">{{ $fullyDepreciated }}</h4>
                                <small class="text-muted">{{ __('Fully Depreciated') }}</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                @php
                                    $overallPercentage = $totalCost > 0 ? ($totalDepreciation / $totalCost) * 100 : 0;
                                @endphp
                                <h4 class="text-secondary">{{ number_format($overallPercentage, 1) }}%</h4>
                                <small class="text-muted">{{ __('Overall Depreciation Rate') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overall Progress Bar -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-warning" style="width: {{ $overallPercentage }}%">
                                    {{ number_format($overallPercentage, 1) }}% {{ __('Depreciated') }}
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">{{ __('Total Cost') }}: {{ number_format($totalCost, 2) }}</small>
                                <small class="text-muted">{{ __('Remaining Value') }}: {{ number_format($netBookValue, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- First Row -->
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input wire:model.live.debounce.500ms="search" type="text" class="form-control" 
                           placeholder="{{ __('Search by asset name...') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Branch') }}</label>
                    <select wire:model.live="selectedBranch" class="form-select">
                        <option value="">{{ __('All Branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Depreciation Status') }}</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="not_depreciated">{{ __('Not Depreciated') }}</option>
                        <option value="partially_depreciated">{{ __('Partially Depreciated') }}</option>
                        <option value="fully_depreciated">{{ __('Fully Depreciated') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Depreciation Method') }}</label>
                    <select wire:model.live="filterMethod" class="form-select">
                        <option value="">{{ __('All Methods') }}</option>
                        <option value="straight_line">{{ __('Straight Line') }}</option>
                        <option value="declining_balance">{{ __('Declining Balance') }}</option>
                        <option value="double_declining">{{ __('Double Declining Balance') }}</option>
                        <option value="sum_of_years">{{ __('Sum of Years Digits') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Journal Entry Status') }}</label>
                    <select wire:model.live="filterJournalStatus" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="has_journal">{{ __('Has Journal Entry') }}</option>
                        <option value="no_journal">{{ __('No Journal Entry') }}</option>
                        <option value="current_month">{{ __('Current Month Entry') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Useful Life') }}</label>
                    <select wire:model.live="filterUsefulLife" class="form-select">
                        <option value="">{{ __('All Ages') }}</option>
                        <option value="1-5">1-5 {{ __('Years') }}</option>
                        <option value="6-10">6-10 {{ __('Years') }}</option>
                        <option value="11-20">11-20 {{ __('Years') }}</option>
                        <option value="21+">21+ {{ __('Years') }}</option>
                    </select>
                </div>
            </div>
            
            <!-- Second Row - Date Range Filter -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar me-1"></i>
                        {{ __('Search Period - From Date') }}
                    </label>
                    <input wire:model.live="filterDateFrom" type="date" class="form-control">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('Searches in: Depreciation start date only') }}
                    </small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar me-1"></i>
                        {{ __('To Date') }}
                    </label>
                    <input wire:model.live="filterDateTo" type="date" class="form-control">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1"></i>
                        {{ __('Filter by depreciation start date') }}
                    </small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Quick Periods') }}</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button wire:click="setDateRange('this_month')" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-day me-1"></i>
                            {{ __('This Month') }}
                        </button>
                        <button wire:click="setDateRange('last_month')" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-calendar-week me-1"></i>
                            {{ __('Last Month') }}
                        </button>
                        <button wire:click="setDateRange('last_3_months')" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ __('Last 3 Months') }}
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <button wire:click="setDateRange('this_year')" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-calendar me-1"></i>
                            {{ __('This Year') }}
                        </button>
                        <button wire:click="setDateRange('next_month')" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            {{ __('Next Month') }}
                        </button>
                        <button wire:click="setDateRange('next_3_months')" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-arrow-circle-right me-1"></i>
                            {{ __('Next 3 Months') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Reset') }}</label>
                    <div class="d-flex gap-2">
                        <button wire:click="clearDateFilters" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-eraser me-1"></i>
                            {{ __('Clear Dates') }}
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Active Filters Display -->
            @if($filterDateFrom || $filterDateTo || $search || $selectedBranch || $filterStatus || $filterMethod || $filterJournalStatus || $filterUsefulLife)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-filter me-2"></i>
                                <strong>{{ __('Active Filters') }}:</strong>
                                @if($filterDateFrom)
                                    <span class="badge bg-primary me-1">{{ __('From') }}: {{ $filterDateFrom }}</span>
                                @endif
                                @if($filterDateTo)
                                    <span class="badge bg-primary me-1">{{ __('To') }}: {{ $filterDateTo }}</span>
                                @endif
                                @if($search)
                                    <span class="badge bg-secondary me-1">{{ __('Search') }}: {{ $search }}</span>
                                @endif
                                @if($selectedBranch)
                                    <span class="badge bg-info me-1">{{ __('Branch') }}: {{ $branches->where('id', $selectedBranch)->first()->name ?? '' }}</span>
                                @endif
                                @if($filterStatus)
                                    <span class="badge bg-warning me-1">{{ __('Depreciation Status') }}: {{ $filterStatus }}</span>
                                @endif
                                @if($filterMethod)
                                    <span class="badge bg-success me-1">{{ __('Method') }}: {{ $filterMethod }}</span>
                                @endif
                                @if($filterJournalStatus)
                                    <span class="badge bg-danger me-1">{{ __('Entry') }}: {{ $filterJournalStatus }}</span>
                                @endif
                            </div>
                            <button wire:click="$set('search', ''); $set('selectedBranch', ''); $set('filterStatus', ''); $set('filterMethod', ''); $set('filterJournalStatus', ''); $set('filterUsefulLife', ''); clearDateFilters()" 
                                    class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Clear All Filters') }}
                                </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assets Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive table-container">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>{{ __('Asset Name') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Depreciation Method') }}</th>
                            <th>{{ __('Purchase Date') }}</th>
                            <th>{{ __('Depreciation Start Date') }}</th>
                            <th>{{ __('Useful Life') }}</th>
                            <th>{{ __('Purchase Cost') }}</th>
                            <th>{{ __('Salvage Value') }}</th>
                            <th>{{ __('Annual Depreciation') }}</th>
                            <th>{{ __('Accumulated Depreciation') }}</th>
                            <th>{{ __('Book Value') }}</th>
                            <th>{{ __('Entry Status') }}</th>
                            <th>{{ __('Depreciation Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr class="table-row">
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $asset->asset_name ?: $asset->accHead->aname }}</strong>
                                        <small class="text-muted">{{ $asset->accHead->code }}</small>
                                    </div>
                                </td>
                                <td>{{ $asset->accHead->branch->name ?? '-' }}</td>
                                <td>
                                    @switch($asset->depreciation_method)
                                    @case('straight_line')
                                    <div class="">{{ __('Straight Line') }}</div>        
                                      <span class="badge bg-info">{{ __('Straight Line') }}</span>
                                            @break
                                        @case('double_declining')
                                            <span class="badge bg-warning">{{ __('Double Declining Balance') }}</span>
                                            @break
                                        @case('declining_balance')
                                            <span class="badge bg-info">{{ __('Declining Balance') }}</span>
                                            @break
                                        @case('sum_of_years')
                                            <span class="badge bg-success">{{ __('Sum of Years Digits') }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ __('Not Specified') }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : __('Not Specified') }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $asset->depreciation_start_date ? $asset->depreciation_start_date->format('Y-m-d') : __('Not Specified') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $asset->useful_life_years }} {{ __('Years') }}</strong>
                                        <small class="text-muted">{{ __('Remaining') }}: {{ $asset->getRemainingLife() }} {{ __('Years') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-primary">{{ number_format($asset->purchase_cost, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="text-success">{{ number_format($asset->salvage_value ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-warning">{{ number_format($asset->annual_depreciation ?? 0, 2) }}</strong>
                                        <small class="text-muted">{{ __('Monthly') }}: {{ number_format(($asset->annual_depreciation ?? 0) / 12, 2) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-warning">{{ number_format($asset->accumulated_depreciation, 2) }}</strong>
                                        <small class="text-muted">{{ number_format($asset->getDepreciationPercentage(), 1) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ number_format($asset->getNetBookValue(), 2) }}</strong>
                                        <div class="progress mt-1" style="height: 8px;">
                                            <div class="progress-bar bg-primary" style="width: {{ 100 - $asset->getDepreciationPercentage() }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $currentMonth = now()->format('Y-m');
                                        $lastDepreciation = $asset->last_depreciation_date ? 
                                            \Carbon\Carbon::parse($asset->last_depreciation_date)->format('Y-m') : null;
                                        $hasCurrentMonthEntry = $lastDepreciation === $currentMonth;
                                    @endphp
                                    @if($hasCurrentMonthEntry)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> {{ __('Entry Created') }}
                                        </span>
                                        <br><small class="text-muted">{{ $asset->last_depreciation_date->format('Y-m-d') }}</small>
                                    @elseif($asset->last_depreciation_date)
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> {{ __('Previous Entry') }}
                                        </span>
                                        <br><small class="text-muted">{{ $asset->last_depreciation_date->format('Y-m-d') }}</small>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times"></i> {{ __('No Entry Created') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($asset->isFullyDepreciated())
                                        <span class="badge bg-success">{{ __('Fully Depreciated') }}</span>
                                    @elseif($asset->accumulated_depreciation > 0)
                                        <span class="badge bg-warning">{{ __('Partially Depreciated') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Not Depreciated') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2" dir="ltr">
                                        <button wire:click="viewSchedule({{ $asset->id }})" 
                                                class="btn btn-primary rounded-pill px-4 py-2" 
                                                title="{{ __('View Schedule') }}">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            {{ __('Schedule') }}
                                        </button>
                                        @if(!$hasCurrentMonthEntry && !$asset->isFullyDepreciated())
                                            <button wire:click="createDepreciationEntry({{ $asset->id }})" 
                                                    class="btn btn-success rounded-pill px-4 py-2" 
                                                    title="{{ __('Create Depreciation Entry') }}">
                                                <i class="fas fa-plus me-2"></i>
                                                {{ __('Create Entry') }}
                                        </button>
                                        @endif
                                        <button wire:click="openFreeJournalModalForAsset({{ $asset->id }})" 
                                                class="btn btn-warning rounded-pill px-4 py-2" 
                                                title="{{ __('Free Journal Entry') }}">
                                            <i class="fas fa-file-invoice me-2"></i>
                                            {{ __('Free Entry') }}
                                        </button>
                                        <button wire:click="exportSchedule({{ $asset->id }})" 
                                                class="btn btn-info rounded-pill px-4 py-2" 
                                                title="{{ __('Export') }}">
                                            <i class="fas fa-download me-2"></i>
                                            {{ __('Export') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar fa-3x mb-3"></i>
                                        <p>{{ __('No assets available for scheduling') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $assets->links() }}
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    @if($showModal && $selectedAsset)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Asset Depreciation Schedule') }}: {{ $selectedAsset->asset_name ?: $selectedAsset->accHead->aname }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Asset Summary -->
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card bg-mint-green">
                                    <div class="card-body text-center">
                                        <h6 class="text-dark">{{ __('Purchase Cost') }}</h6>
                                        <h4 class="text-dark">{{ number_format($selectedAsset->purchase_cost, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-mint-green">
                                    <div class="card-body text-center">
                                        <h6 class="text-dark">{{ __('Salvage Value') }}</h6>
                                        <h4 class="text-dark">{{ number_format($selectedAsset->salvage_value ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-mint-green">
                                    <div class="card-body text-center">
                                        <h6 class="text-dark">{{ __('Depreciable Amount') }}</h6>
                                        <h4 class="text-dark">{{ number_format($selectedAsset->purchase_cost - ($selectedAsset->salvage_value ?? 0), 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-mint-green">
                                    <div class="card-body text-center">
                                        <h6 class="text-dark">{{ __('Useful Life') }}</h6>
                                        <h4 class="text-dark">{{ $selectedAsset->useful_life_years }} {{ __('Years') }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-mint-green">
                                    <div class="card-body text-center">
                                        <h6 class="text-dark">{{ __('Accumulated Depreciation') }}</h6>
                                        <h4 class="text-dark">{{ number_format($selectedAsset->accumulated_depreciation ?? 0, 2) }}</h4>
                                        <small class="text-dark">{{ number_format($selectedAsset->getDepreciationPercentage(), 1) }}%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-mint-green">
                                    <div class="card-body text-center">
                                        <h6 class="text-dark">{{ __('Book Value') }}</h6>
                                        <h4 class="text-dark">{{ number_format($selectedAsset->getNetBookValue(), 2) }}</h4>
                                        <small class="text-dark">{{ __('Remaining') }}: {{ $selectedAsset->getRemainingLife() }} {{ __('Years') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Asset Details -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card bg-mint-green">
                                    <div class="card-header bg-mint-green">
                                        <h6 class="mb-0 text-dark">{{ __('Asset Details') }}</h6>
                                    </div>
                                    <div class="card-body text-dark">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>{{ __('Purchase Date') }}:</strong> {{ $selectedAsset->purchase_date ? $selectedAsset->purchase_date->format('Y-m-d') : __('Not Specified') }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>{{ __('Depreciation Start Date') }}:</strong> {{ $selectedAsset->depreciation_start_date ? $selectedAsset->depreciation_start_date->format('Y-m-d') : __('Not Specified') }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>{{ __('Last Depreciation') }}:</strong> {{ $selectedAsset->last_depreciation_date ? $selectedAsset->last_depreciation_date->format('Y-m-d') : __('Not Yet') }}
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <strong>{{ __('Depreciation Method') }}:</strong> 
                                                @switch($selectedAsset->depreciation_method)
                                                    @case('straight_line')
                                                        {{ __('Straight Line') }}
                                                        @break
                                                    @case('declining_balance')
                                                        {{ __('Declining Balance') }}
                                                        @break
                                                    @case('double_declining')
                                                        {{ __('Double Declining Balance') }}
                                                        @break
                                                    @case('sum_of_years')
                                                        {{ __('Sum of Years Digits') }}
                                                        @break
                                                    @default
                                                        {{ __('Not Specified') }}
                                                @endswitch
                                            </div>
                                            <div class="col-md-4">
                                                <strong>{{ __('Annual Depreciation') }}:</strong> {{ number_format($selectedAsset->annual_depreciation ?? 0, 2) }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>{{ __('Monthly Depreciation') }}:</strong> {{ number_format(($selectedAsset->annual_depreciation ?? 0) / 12, 2) }}
                                            </div>
                                        </div>
                                        @if($selectedAsset->notes)
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <strong>{{ __('Notes') }}:</strong> {{ $selectedAsset->notes }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('Year') }}</th>
                                        <th>{{ __('From Date') }}</th>
                                        <th>{{ __('To Date') }}</th>
                                        <th>{{ __('Beginning Book Value') }}</th>
                                        <th>{{ __('Year Depreciation') }}</th>
                                        <th>{{ __('Accumulated Depreciation') }}</th>
                                        <th>{{ __('Ending Book Value') }}</th>
                                        <th>{{ __('Percentage %') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($scheduleData as $row)
                                        <tr>
                                            <td><strong>{{ $row['year'] }}</strong></td>
                                            <td>{{ $row['start_date'] }}</td>
                                            <td>{{ $row['end_date'] }}</td>
                                            <td>{{ number_format($row['beginning_book_value'], 2) }}</td>
                                            <td class="text-danger">{{ number_format($row['annual_depreciation'], 2) }}</td>
                                            <td class="text-warning">{{ number_format($row['accumulated_depreciation'], 2) }}</td>
                                            <td class="text-success">{{ number_format($row['ending_book_value'], 2) }}</td>
                                            <td>
                                                <div class="progress" style="height: 15px; min-width: 60px;">
                                                    <div class="progress-bar" style="width: {{ $row['percentage'] }}%">
                                                        {{ number_format($row['percentage'], 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                {{ __('Cannot calculate schedule - ensure required data is entered') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(!empty($scheduleData))
                                    <tfoot class="table-secondary">
                                        <tr>
                                            <th colspan="4">{{ __('Total') }}</th>
                                            <th>{{ number_format(collect($scheduleData)->sum('annual_depreciation'), 2) }}</th>
                                            <th>{{ number_format(collect($scheduleData)->last()['accumulated_depreciation'] ?? 0, 2) }}</th>
                                            <th>{{ number_format(collect($scheduleData)->last()['ending_book_value'] ?? 0, 2) }}</th>
                                            <th>100%</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        <!-- Schedule Summary -->
                        @if(!empty($scheduleData))
                            <div class="row mt-4">
                                    <div class="col-12">
                                    <div class="card bg-mint-green">
                                        <div class="card-header bg-mint-green">
                                            <h6 class="mb-0 text-dark">{{ __('Depreciation Schedule Summary') }}</h6>
                                        </div>
                                        <div class="card-body text-dark">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h5 class="text-dark">{{ count($scheduleData) }}</h5>
                                                        <small class="text-dark">{{ __('Total Years') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h5 class="text-dark">{{ number_format(collect($scheduleData)->sum('annual_depreciation'), 2) }}</h5>
                                                        <small class="text-dark">{{ __('Total Depreciation') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h5 class="text-dark">{{ number_format(collect($scheduleData)->sum('annual_depreciation') / count($scheduleData), 2) }}</h5>
                                                        <small class="text-dark">{{ __('Average Annual Depreciation') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h5 class="text-dark">{{ number_format((collect($scheduleData)->sum('annual_depreciation') / count($scheduleData)) / 12, 2) }}</h5>
                                                        <small class="text-dark">{{ __('Average Monthly Depreciation') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Depreciation Progress Chart -->
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <h6>{{ __('Depreciation Progress Over Years') }}</h6>
                                                    <div class="progress mb-2" style="height: 25px;">
                                                        @php
                                                            $totalDepreciable = $selectedAsset->purchase_cost - ($selectedAsset->salvage_value ?? 0);
                                                            $currentProgress = ($selectedAsset->accumulated_depreciation / $totalDepreciable) * 100;
                                                        @endphp
                                                        <div class="progress-bar bg-success" style="width: {{ $currentProgress }}%">
                                                            {{ number_format($currentProgress, 1) }}% {{ __('Depreciated') }}
                                                        </div>
                                                        <div class="progress-bar bg-light text-dark" style="width: {{ 100 - $currentProgress }}%">
                                                            {{ number_format(100 - $currentProgress, 1) }}% {{ __('Remaining') }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">0%</small>
                                                        <small class="text-success">{{ __('Currently Depreciated') }}: {{ number_format($selectedAsset->accumulated_depreciation, 2) }}</small>
                                                        <small class="text-muted">100%</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            {{ __('Close') }}
                        </button>
                        <button type="button" class="btn btn-main" wire:click="exportSchedule({{ $selectedAsset->id }})">
                            <i class="fas fa-download me-2"></i>
                            {{ __('Export to CSV') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Journal Entry Confirmation Modal -->
    @if($showJournalModal && $selectedAssetForJournal && !empty($journalPreview))
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ __('Confirm Depreciation Entry Creation') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeJournalModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Asset Info -->
                        <div class="card bg-mint-green mb-4">
                            <div class="card-header bg-mint-green">
                                <h6 class="mb-0 text-dark">
                                    <i class="fas fa-cube me-2"></i>
                                    {{ __('Asset Data') }}
                                </h6>
                            </div>
                            <div class="card-body text-dark">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>{{ __('Asset Name') }}:</strong> {{ $journalPreview['asset_name'] }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>{{ __('Entry Date') }}:</strong> {{ $journalPreview['date'] }}
                                    </div>
                                </div>
                                @if(isset($journalPreview['months']))
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <strong>{{ __('Number of Months') }}:</strong> {{ $journalPreview['months'] }} {{ $journalPreview['months'] == 1 ? __('Month') : __('Months') }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>{{ __('From Date') }}:</strong> {{ $journalPreview['from_date'] ?? '' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>{{ __('To Date') }}:</strong> {{ $journalPreview['to_date'] ?? '' }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>{{ __('Monthly Depreciation') }}:</strong> {{ number_format($journalPreview['amount'] / $journalPreview['months'], 2) }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('Total Depreciation Amount') }}:</strong> <span class="text-primary fw-bold">{{ number_format($journalPreview['amount'], 2) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Journal Entry Preview -->
                        <div class="card bg-mint-green">
                            <div class="card-header bg-mint-green">
                                <h6 class="mb-0 text-dark">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    {{ __('Journal Entry Preview') }}
                                </h6>
                            </div>
                            <div class="card-body text-dark">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>{{ __('Account') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Debit') }}</th>
                                                <th>{{ __('Credit') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-danger">{{ $journalPreview['debit_account'] }}</td>
                                                <td>{{ $journalPreview['description'] }}</td>
                                                <td class="text-danger fw-bold">{{ number_format($journalPreview['amount'], 2) }}</td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-success">{{ $journalPreview['credit_account'] }}</td>
                                                <td>{{ $journalPreview['description'] }}</td>
                                                <td>-</td>
                                                <td class="text-success fw-bold">{{ number_format($journalPreview['amount'], 2) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-secondary">
                                            <tr>
                                                <th colspan="2">{{ __('Total') }}</th>
                                                <th class="text-danger">{{ number_format($journalPreview['amount'], 2) }}</th>
                                                <th class="text-success">{{ number_format($journalPreview['amount'], 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>{{ __('Note') }}:</strong> {{ $journalPreview['description'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeJournalModal">
                            <i class="fas fa-times me-2"></i>
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" class="btn btn-main rounded-pill px-4" wire:click="confirmJournalEntry">
                            <i class="fas fa-check me-2"></i>
                            {{ __('Confirm and Create Entry') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Free Depreciation Entry Modal -->
    @if($showFreeJournalModal && $freeJournalAssetId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-file-invoice me-2"></i>
                            {{ __('Manual Depreciation Entry') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeFreeJournalModal"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            $asset = \Modules\Depreciation\Models\AccountAsset::with(['accHead', 'depreciationAccount', 'expenseAccount'])->find($freeJournalAssetId);
                        @endphp
                        
                        @if($asset)
                            <!-- Asset Info -->
                            <div class="card bg-mint-green mb-3">
                                <div class="card-header bg-mint-green">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-cube me-2"></i>
                                        {{ __('Asset Data') }}
                                    </h6>
                                </div>
                                <div class="card-body text-dark">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>{{ __('Asset Name') }}:</strong> {{ $asset->asset_name ?: $asset->accHead->aname }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('Purchase Cost') }}:</strong> {{ number_format($asset->purchase_cost, 2) }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>{{ __('Accumulated Depreciation') }}:</strong> {{ number_format($asset->accumulated_depreciation, 2) }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('Book Value') }}:</strong> {{ number_format($asset->getNetBookValue(), 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form wire:submit.prevent="createFreeJournalEntry">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Date') }}</label>
                                        <input type="date" wire:model="freeJournalDate" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Depreciation Amount') }}</label>
                                        <input type="number" wire:model="freeJournalDebitAmount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                                        <small class="text-muted">
                                            {{ __('Suggested Monthly Depreciation') }}: {{ number_format(($asset->annual_depreciation ?? 0) / 12, 2) }}
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Depreciation Expense Account') }}</label>
                                        <select wire:model="freeJournalDebitAccount" class="form-select" required>
                                            <option value="">{{ __('Choose Account') }}</option>
                                            @if($asset->expenseAccount)
                                                <option value="{{ $asset->expenseAccount->id }}" selected>
                                                    {{ $asset->expenseAccount->code }} - {{ $asset->expenseAccount->aname }}
                                                </option>
                                            @endif
                                            @foreach($accounts as $account)
                                                @if(!$asset->expenseAccount || $account->id != $asset->expenseAccount->id)
                                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Accumulated Depreciation Account') }}</label>
                                        <select wire:model="freeJournalCreditAccount" class="form-select" required>
                                            <option value="">{{ __('Choose Account') }}</option>
                                            @if($asset->depreciationAccount)
                                                <option value="{{ $asset->depreciationAccount->id }}" selected>
                                                    {{ $asset->depreciationAccount->code }} - {{ $asset->depreciationAccount->aname }}
                                                </option>
                                            @endif
                                            @foreach($accounts as $account)
                                                @if(!$asset->depreciationAccount || $account->id != $asset->depreciationAccount->id)
                                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Description') }}</label>
                                        <input type="text" wire:model="freeJournalDescription" class="form-control" placeholder="{{ __('Depreciation Entry Description') }}" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Notes') }}</label>
                                        <textarea wire:model="freeJournalNotes" class="form-control" rows="2" placeholder="{{ __('Additional Notes (Optional)') }}"></textarea>
                                    </div>
                                </div>

                                @if($freeJournalDebitAmount > 0)
                                    @php
                                        $remainingDepreciable = ($asset->purchase_cost - ($asset->salvage_value ?? 0)) - $asset->accumulated_depreciation;
                                    @endphp
                                    @if($freeJournalDebitAmount > $remainingDepreciable)
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            {{ __('Warning: Entered amount is greater than remaining depreciable amount') }} ({{ number_format($remainingDepreciable, 2) }})
                                        </div>
                                    @endif
                                @endif

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" wire:click="closeFreeJournalModal">
                                        <i class="fas fa-times me-2"></i>
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-warning" 
                                            @if($freeJournalDebitAmount <= 0 || !$freeJournalDebitAccount || !$freeJournalCreditAccount) disabled @endif>
                                        <i class="fas fa-check me-2"></i>
                                        {{ __('Create Depreciation Entry') }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    /* Dark Brown Text Color - Apply to all text */
    div,
    h1, h2, h3, h4, h5, h6,
    p, span, a, label, small, strong,
    th, td, li,
    .text-primary, .text-success, .text-warning, .text-info, 
    .text-danger, .text-muted, .text-secondary, .text-dark, .text-white,
    .card-body, .card-header, .card-title, .modal-title, .modal-body,
    .form-label, .btn, .badge,
    input, select, textarea {
        color: #5D4037 !important; /* Dark brown color */
    }
    
    /* Keep button text readable but maintain dark brown */
    .btn.btn-primary, .btn.btn-success, .btn.btn-warning, 
    .btn.btn-info, .btn.btn-danger, .btn.btn-secondary {
        color: #5D4037 !important;
    }
    
    /* Keep badges readable */
    .badge {
        color: #5D4037 !important;
    }
    
    /* Links should also be dark brown */
    a {
        color: #5D4037 !important;
    }
    
    a:hover {
        color: #3E2723 !important; /* Darker brown on hover */
    }
    
    /* Mint Green Color */
    .bg-mint-green {
        background-color: #a7f3d0 !important;
        color: #5D4037 !important;
    }
    
    .bg-mint-green .text-dark {
        color: #5D4037 !important;
    }
    
    .progress {
        background-color: #e9ecef;
    }
    
    .progress-bar {
        background-color: #007bff;
        color: #5D4037 !important;
        font-size: 11px;
        line-height: 15px;
    }
    
    .modal-xl {
        max-width: 98%;
    }
    
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
        font-size: 0.9em;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.15s ease-in-out;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    /* Custom progress bars for depreciation status */
    .progress-depreciation {
        height: 8px;
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-depreciation .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #28a745 0%, #ffc107 50%, #dc3545 100%);
        transition: width 0.3s ease;
    }
    
    /* Responsive design */
    @media (max-width: 1200px) {
        .table th, .table td {
            font-size: 0.8em;
            padding: 0.5rem 0.25rem;
        }
        
        .btn-sm {
            font-size: 0.7em;
            padding: 0.25rem 0.4rem;
        }
    }
    
    @media (max-width: 992px) {
        .modal-xl {
            max-width: 95%;
        }
        
        .card-body h4 {
            font-size: 1.1rem;
        }
        
        .card-body h6 {
            font-size: 0.9rem;
        }
    }
    
    /* Animation for progress bars */
    @keyframes progressAnimation {
        0% { width: 0%; }
        100% { width: var(--progress-width); }
    }
    
    .progress-bar-animated {
        animation: progressAnimation 1s ease-in-out;
    }
    
    /* Custom styling for different depreciation methods */
    .badge.bg-info { background-color: #0dcaf0 !important; }
    .badge.bg-warning { background-color: #ffc107 !important; color: #000; }
    .badge.bg-success { background-color: #198754 !important; }
    .badge.bg-secondary { background-color: #6c757d !important; }
    
    /* Large circular buttons */
    .btn.rounded-pill {
        border-radius: 50px !important;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 14px;
        min-width: 120px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .btn.rounded-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .btn.rounded-pill i {
        font-size: 16px;
    }
    
    /* Journal status badges */
    .badge {
        font-size: 0.8em;
        padding: 6px 12px;
        border-radius: 15px;
    }
    
    .badge i {
        margin-right: 4px;
    }
    
    /* Performance optimizations */
    .table-container {
        contain: layout style;
        will-change: scroll-position;
    }
    
    .table-row {
        will-change: auto;
    }
    
    /* Virtualization hints */
    .table tbody {
        contain: strict;
    }
    
    /* Reduce repaints */
    .progress-bar {
        transform: translateZ(0);
    }
    
    /* Lazy loading indicators */
    .asset-image {
        content-visibility: auto;
        contain-intrinsic-size: 50px 50px;
    }
    
    /* Summary cards styling */
    .summary-card {
        border-left: 4px solid;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    }
    
    .summary-card.primary { border-left-color: #0d6efd; }
    .summary-card.success { border-left-color: #198754; }
    .summary-card.warning { border-left-color: #ffc107; }
    .summary-card.info { border-left-color: #0dcaf0; }
    .summary-card.danger { border-left-color: #dc3545; }
    .summary-card.secondary { border-left-color: #6c757d; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Debounced alert handling for better performance
        let alertTimeout;
        Livewire.on('alert', (event) => {
            clearTimeout(alertTimeout);
            alertTimeout = setTimeout(() => {
            if (event.type === 'success') {
                // You can replace this with your preferred notification system
                alert(event.message);
            } else if (event.type === 'error') {
                alert('Error: ' + event.message);
            } else if (event.type === 'warning') {
                alert('Warning: ' + event.message);
            }
            }, 100);
        });
        
        // Performance optimization: Virtual scrolling for large tables
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) {
            // Add intersection observer for lazy loading
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });
            
            // Observe table rows
            document.querySelectorAll('.table-row').forEach(row => {
                observer.observe(row);
            });
        }
        
        // Performance: Optimize progress bar animations
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            bar.style.willChange = 'width';
        });
    });
    
    // Optimize bulk selection performance
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[type="checkbox"]')) {
            // Use requestAnimationFrame for smoother UI updates
            requestAnimationFrame(() => {
                // Update UI state
            });
        }
    });
</script>
@endpush