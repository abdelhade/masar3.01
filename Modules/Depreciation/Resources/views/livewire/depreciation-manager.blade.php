<div>
<div>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-calculator me-2"></i>
                    {{ __('Asset Depreciation Management') }}
                </h2>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('depreciation.schedule') }}" class="btn btn-primary">
                        <i class="fas fa-calendar-alt me-2"></i>
                        {{ __('Depreciation Schedule') }}
                    </a>
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Select an asset account from the list to apply depreciation') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control" 
                           placeholder="{{ __('Search by account name...') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Branch') }}</label>
                    <select wire:model.live="filterBranch" class="form-select">
                        <option value="">{{ __('All Branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            @if(!empty($selectedAssets))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <span>{{ __('Selected') }} {{ count($selectedAssets) }} {{ __('asset(s)') }}</span>
                            <div>
                                <button wire:click="bulkDepreciation" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-calculator me-1"></i>
                                    {{ __('Bulk Process') }}
                                </button>
                                <button wire:click="$set('selectedAssets', []); $set('selectAll', false)" class="btn btn-outline-secondary btn-sm">
                                    {{ __('Clear Selection') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th width="50">
                                <input type="checkbox" wire:model="selectAll" class="form-check-input">
                            </th>
                            <th>{{ __('Asset Name') }}</th>
                            <th>{{ __('Asset Account') }}</th>
                            <th>{{ __('Purchase Date') }}</th>
                            <th>{{ __('Cost') }}</th>
                            <th>{{ __('Annual Depreciation') }}</th>
                            <th>{{ __('Accumulated Depreciation') }}</th>
                            <th>{{ __('Book Value') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountAssets as $asset)
                            <tr>
                                <td>
                                    <input type="checkbox" wire:model="selectedAssets" value="{{ $asset->id }}" class="form-check-input">
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $asset->asset_name ?: $asset->accHead->aname }}</strong>
                                        <small class="text-muted">{{ $asset->accHead->code }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $asset->accHead->aname }}</span>
                                </td>
                                <td>
                                    {{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}
                                </td>
                                <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                                <td>{{ number_format($asset->annual_depreciation, 2) }}</td>
                                <td>{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                                <td>
                                    <strong class="text-primary">{{ number_format($asset->getNetBookValue(), 2) }}</strong>
                                </td>
                                <td>
                                    @if($asset->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" dir="ltr">
                                        <button wire:click="editAsset({{ $asset->id }})" 
                                                class="btn btn-sm btn-outline-primary" 
                                                title="{{ __('Edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="selectAccountForDepreciation({{ $asset->accHead->id }})" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="{{ __('Additional Depreciation') }}">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <p>{{ __('No assets registered') }}</p>
                                        @if($assetAccounts->count() > 0)
                                            <p class="small">{{ __('You can add a new asset from the accounts below') }}</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Available Asset Accounts Section -->
            @if($assetAccounts->count() > 0)
                <div class="mt-4">
                    <h5 class="text-muted mb-3">
                        <i class="fas fa-plus-circle me-2"></i>
                        {{ __('Available Asset Accounts to Add') }}
                    </h5>
                    <div class="row">
                        @foreach($assetAccounts as $account)
                            <div class="col-md-4 mb-2">
                                <div class="card border-dashed border-primary">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $account->aname }}</strong>
                                                <br><small class="text-muted">{{ $account->code }}</small>
                                            </div>
                                            <button wire:click="createAssetRecord({{ $account->id }})" 
                                                    class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-plus"></i> {{ __('Add') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $accountAssets->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content position-relative">
                    <!-- Loading Overlay during save (hidden by default to avoid flash) -->
                    <div wire:loading.delay wire:target="processDepreciation"
                         wire:loading.class.remove="d-none" wire:loading.class="d-flex"
                         class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center"
                         style="background: rgba(255,255,255,0.6); z-index: 10;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border text-primary me-2" role="status" aria-hidden="true"></div>
                            <strong>{{ __('Saving...') }}</strong>
                        </div>
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Asset Depreciation') }}: {{ $selectedAccount?->aname }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>{{ __('Please correct the following errors:') }}</strong>
                                </div>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @error('general')
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <form wire:submit.prevent="processDepreciation">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2">{{ __('Depreciation Data') }}</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Purchase Date') }}</label>
                                            <input wire:model="purchase_date" type="date" 
                                                   class="form-control @error('purchase_date') is-invalid @enderror">
                                            @error('purchase_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('First Depreciation Date') }}</label>
                                            <input wire:model="depreciation_date" type="date" 
                                                   class="form-control @error('depreciation_date') is-invalid @enderror">
                                            @error('depreciation_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    </div>
                                </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Purchase Cost (from asset account)') }}</label>
                                            <input wire:model="purchase_cost" type="number" step="0.01" 
                                                   class="form-control @error('purchase_cost') is-invalid @enderror" readonly
                                                   placeholder="{{ __('Automatically fetched from account balance') }}">
                                            @error('purchase_cost')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">{{ __('Value taken from current asset account balance') }}</small>
                                        </div>
                                        </div>
                                    </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Salvage Value') }}</label>
                                            <input wire:model="salvage_value" type="number" step="0.01" 
                                                   class="form-control @error('salvage_value') is-invalid @enderror">
                                            @error('salvage_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                    <div class="mb-3">
                                            <label class="form-label">{{ __('Depreciable Value') }}</label>
                                            <input type="text" class="form-control" value="{{ number_format(max(($purchase_cost ?: 0) - ($salvage_value ?: 0), 0), 2) }}" readonly>
                                    </div>
                                </div>
                                    <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Depreciation Method') }}</label>
                                        <select wire:model="depreciation_method" class="form-select @error('depreciation_method') is-invalid @enderror">
                                            <option value="straight_line">{{ __('Straight Line') }}</option>
                                                <option value="declining_balance">{{ __('Declining Balance') }}</option>
                                            <option value="double_declining">{{ __('Double Declining Balance') }}</option>
                                            <option value="sum_of_years">{{ __('Sum of Years Digits') }}</option>
                                        </select>
                                        @error('depreciation_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>
                                </div>
                            </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Depreciation Years') }}</label>
                                            <input wire:model="useful_life_years" type="number" min="1" 
                                                   class="form-control @error('useful_life_years') is-invalid @enderror">
                                            @error('useful_life_years')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary border-bottom pb-2">{{ __('Expected Depreciation Schedule') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>{{ __('Year') }}</th>
                                                <th>{{ __('From Date') }}</th>
                                                <th>{{ __('To Date') }}</th>
                                                <th>{{ __('Beginning Book Value') }}</th>
                                                <th>{{ __('Year Depreciation') }}</th>
                                                <th>{{ __('Accumulated Depreciation') }}</th>
                                                <th>{{ __('Ending Book Value') }}</th>
                                                <th>{{ __('Depreciation Formula') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($schedulePreview as $row)
                                                <tr>
                                                    <td><strong>{{ $row['year'] }}</strong></td>
                                                    <td>{{ $row['start_date'] }}</td>
                                                    <td>{{ $row['end_date'] }}</td>
                                                    <td>{{ number_format($row['beginning_book_value'], 2) }}</td>
                                                    <td class="text-danger">{{ number_format($row['annual_depreciation'], 2) }}</td>
                                                    <td class="text-warning">{{ number_format($row['accumulated_depreciation'], 2) }}</td>
                                                    <td class="text-success">{{ number_format($row['ending_book_value'], 2) }}</td>
                                                    <td>
                                                        @switch($depreciation_method)
                                                            @case('straight_line')
                                                                {{ __('Depreciable Value') }} / {{ $useful_life_years ?: '-' }}
                                                                @break
                                                            @case('declining_balance')
                                                                {{ __('Book Value') }} × (1/{{ $useful_life_years ?: '-' }})
                                                                @break
                                                            @case('double_declining')
                                                                {{ __('Book Value') }} × (2/{{ $useful_life_years ?: '-' }})
                                                                @break
                                                            @case('sum_of_years')
                                                                {{ __('Depreciable Value') }} × {{ __('Remaining Years') }} / {{ __('Sum of Years') }}
                                                                @break
                                                            @default
                                                                -
                                                        @endswitch
                                                    </td>
                                        </tr>
                                            @empty
                                        <tr>
                                                    <td colspan="8" class="text-center text-muted">{{ __('Not enough data to calculate schedule') }}</td>
                                        </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('This is a scheduling phase only - no journal entries are created from this screen.') }}
                                </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('depreciation.schedule') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>
                            {{ __('Go to Schedule Screen') }}
                        </a>
                        <button type="button" class="btn btn-outline-secondary" wire:click="generatePreview" wire:loading.attr="disabled" wire:target="generatePreview">
                            <span wire:loading.delay.inline wire:target="generatePreview" class="me-2">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                            <i class="fas fa-sync-alt me-2"></i>
                            {{ __('Update Schedule') }}
                        </button>
                        <button type="button" class="btn btn-secondary" wire:click="closeModal" wire:loading.attr="disabled" wire:target="processDepreciation">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" class="btn btn-success" wire:click="processDepreciation" wire:loading.attr="disabled" wire:target="processDepreciation">
                            <span wire:loading.delay.inline wire:target="processDepreciation" class="me-2">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                            <i class="fas fa-save me-2"></i>
                            {{ __('Save') }}
                        </button>
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('alert', (event) => {
            if (event.type === 'success') {
                // You can replace this with your preferred notification system
                alert(event.message);
            } else if (event.type === 'error') {
                alert('{{ __('Error') }}: ' + event.message);
            }
        });
    });
</script>
@endpush