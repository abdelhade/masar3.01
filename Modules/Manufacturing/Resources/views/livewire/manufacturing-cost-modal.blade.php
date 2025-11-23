<div>
    @if ($isOpen)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Manufacturing Cost Details') }}</h5>
                        <button type="button" class="btn-close" wire:click="close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs mb-3" id="manufacturingTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'single' ? 'active' : '' }}"
                                    wire:click="setTab('single')" type="button" role="tab">
                                    {{ __('Single Item Details') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeTab === 'total' ? 'active' : '' }}"
                                    wire:click="setTab('total')" type="button" role="tab">
                                    {{ __('Total Invoice Cost') }}
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="manufacturingTabsContent">

                            <!-- Single Item Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'single' ? 'show active' : '' }}"
                                role="tabpanel">
                                <div class="row">
                                    <!-- Left Side: Item List -->
                                    <div class="col-md-4 border-end">
                                        <h5 class="mb-3">{{ __('Request Items') }}</h5>
                                        <div class="list-group">
                                            @foreach ($items as $item)
                                                <button type="button"
                                                    class="list-group-item list-group-item-action {{ $selectedItemId == $item['id'] ? 'active' : '' }}"
                                                    wire:click="$set('selectedItemId', {{ $item['id'] }})">
                                                    {{ $item['name'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Right Side: Cost Breakdown -->
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-3">
                                            <label class="me-2 fw-bold">{{ __('Quantity to Manufacture') }}:</label>
                                            <input type="number" class="form-control w-25"
                                                wire:model.live.debounce.500ms="manufacturingQuantity" min="1">
                                        </div>

                                        @if ($isLoading)
                                            <div class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        @elseif($costData)
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $costData['name'] }}</h5>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>{{ __('Unit Cost') }}:
                                                            <strong>{{ number_format($costData['unit_cost'], 2) }}</strong></span>
                                                        <span>{{ __('Total Cost') }}:
                                                            <strong>{{ number_format($costData['total_cost'], 2) }}</strong></span>
                                                    </div>
                                                    @if (!$costData['has_recipe'])
                                                        <div class="alert alert-warning py-1 px-2 mb-0">
                                                            <small>{{ __('No manufacturing recipe found (using average cost).') }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <h6 class="fw-bold mt-4">{{ __('Components Breakdown') }}</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>{{ __('Item') }}</th>
                                                            <th>{{ __('Qty') }}</th>
                                                            <th>{{ __('Unit Cost') }}</th>
                                                            <th>{{ __('Total') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if (!empty($costData['components']))
                                                            @foreach ($costData['components'] as $component)
                                                                @include(
                                                                    'manufacturing::livewire.partials.manufacturing-cost-row',
                                                                    ['component' => $component, 'level' => 0]
                                                                )
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">
                                                                    {{ __('No components found') }}</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-5 text-muted">
                                                {{ __('Select an item to view details') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Total Cost Tab -->
                            <div class="tab-pane fade {{ $activeTab === 'total' ? 'show active' : '' }}"
                                role="tabpanel">
                                @if ($isLoading)
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="mb-3">{{ __('Invoice Items Summary') }}</h5>
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Item') }}</th>
                                                            <th>{{ __('Quantity') }}</th>
                                                            <th>{{ __('Est. Unit Cost') }}</th>
                                                            <th>{{ __('Total Cost') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($totalCostData as $data)
                                                            <tr>
                                                                <td>{{ $data['name'] }}</td>
                                                                <td>{{ number_format($data['quantity_needed'], 2) }}
                                                                </td>
                                                                <td>{{ number_format($data['unit_cost'], 2) }}</td>
                                                                <td>{{ number_format($data['total_cost'], 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <td colspan="3" class="text-end fw-bold">
                                                                {{ __('Grand Total') }}</td>
                                                            <td class="fw-bold">{{ number_format($grandTotal, 2) }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <h5 class="mb-3 text-primary">{{ __('Total Raw Materials Needed') }}</h5>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover border">
                                                    <thead class="bg-primary text-white">
                                                        <tr>
                                                            <th>{{ __('Raw Material') }}</th>
                                                            <th>{{ __('Total Quantity Needed') }}</th>
                                                            <th>{{ __('Est. Total Cost') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($totalRawMaterials as $material)
                                                            <tr>
                                                                <td>{{ $material['name'] }}</td>
                                                                <td>{{ number_format($material['quantity'], 2) }}</td>
                                                                <td>{{ number_format($material['total_cost'], 2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="button" class="btn btn-success" wire:click="confirmCreatePurchaseOrder" wire:loading.attr="disabled">
                                                    <i class="fas fa-file-invoice me-2"></i> {{ __('Convert to Purchase Order') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="close">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('confirm-create-po', () => {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("A new purchase order will be created with these items.") }}',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, create it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.proceedCreatePurchaseOrder();
                    }
                });
            });
        });
    </script>
</div>
