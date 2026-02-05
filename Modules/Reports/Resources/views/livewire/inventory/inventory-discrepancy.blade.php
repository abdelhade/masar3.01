<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show font-hold" role="alert" x-data="{ show: true }"
            x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <i class="fas fa-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show font-hold" role="alert" x-data="{ show: true }"
            x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <i class="fas fa-times me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show font-hold" role="alert" x-data="{ show: true }"
            x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> {{ __('Report Filters') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Warehouse') }}</label>
                    <select wire:model.live="selectedWarehouse" class="form-select">
                        <option value="">{{ __('Select Warehouse') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                        @endforeach
                    </select>
                    @error('selectedWarehouse')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ __('Inventory Adjustment Account') }}</label>
                    <select wire:model="selectedPartner" class="form-select">
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->aname }}</option>
                        @endforeach
                    </select>
                    @error('selectedPartner')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" wire:click="applyInventoryAdjustments"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="applyInventoryAdjustments">
                                <i class="fas fa-sync-alt"></i> {{ __('Update Balances & Apply Adjustment') }}
                            </span>
                            <span wire:loading wire:target="applyInventoryAdjustments">{{ __('Applying...') }}</span>
                        </button>
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Statistics --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">{{ __('Total Items') }}</h6>
                    <h4 class="text-primary">{{ $totalItems }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">{{ __('Items with Overage') }}</h6>
                    <h4 class="text-info">{{ $itemsWithOverage }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">{{ __('Items with Shortage') }}</h6>
                    <h4 class="text-danger">{{ $itemsWithShortage }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">{{ __('Matching Items') }}</h6>
                    <h4 class="text-success">{{ $itemsMatching }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Difference Account Warning --}}
    @if (!$inventoryDifferenceAccount)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>{{ __('Warning') }}:</strong>
            {{ __('Inventory difference account not specified or does not exist.') }}
            @if ($inventoryDifferenceAccountValue)
                <br>
                <small>
                    {{ __('Saved value in settings') }}: <code>{{ $inventoryDifferenceAccountValue }}</code>
                    <br>
                    {{ __('Please verify this code/ID exists in the accounts table (AccHead) and is not deleted.') }}
                </small>
            @else
                <br>
                <small>
                    {{ __('Please set value for key') }} <code>show_inventory_difference_account</code>
                    {{ __('in general settings.') }}
                </small>
            @endif
        </div>
    @endif

    {{-- Data Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th>{{ __('Item Name') }}</th>
                            <th>{{ __('Cost') }}</th>
                            <th>{{ __('Book Balance') }}</th>
                            <th>{{ __('Actual Quantity (Inventory)') }}</th>
                            <th>{{ __('Quantity Difference') }}</th>
                            <th>{{ __('Difference Type') }}</th>
                            <th>{{ __('Difference Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryData as $index => $data)
                            <tr
                                class="{{ $data['discrepancy'] < 0 ? 'table-danger' : ($data['discrepancy'] > 0 ? 'table-info' : '') }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $data['item_name'] }}</td>
                                <td class="text-center">{{ number_format($data['item_cost']) }}</td>
                                <td class="text-center">
                                    {{ number_format($data['system_quantity']) }}
                                    <br><small>{{ $data['main_unit_name'] }}</small>
                                </td>
                                <td style="width: 150px;">
                                    <input type="number"
                                        wire:model.live.debounce.500ms="quantities.{{ $data['item_id'] }}"
                                        class="form-control form-control-sm text-center" step="any">
                                </td>
                                <td class="text-center fw-bold">
                                    <span
                                        class="{{ $data['discrepancy'] < 0 ? 'text-danger' : ($data['discrepancy'] > 0 ? 'text-primary' : 'text-success') }}">
                                        {{ $data['discrepancy'] > 0 ? '+' : '' }}{{ number_format($data['discrepancy']) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge {{ $data['discrepancy'] < 0 ? 'bg-danger' : ($data['discrepancy'] > 0 ? 'bg-info' : 'bg-success') }}">
                                        {{ $data['discrepancy_type'] }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">
                                    <span
                                        class="{{ $data['discrepancy_value'] < 0 ? 'text-danger' : ($data['discrepancy_value'] > 0 ? 'text-primary' : 'text-success') }}">
                                        {{ $data['discrepancy_value'] > 0 ? '+' : '' }}{{ number_format($data['discrepancy_value']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    @if (!$selectedWarehouse)
                                        {{ __('Please select a warehouse to display items') }}
                                    @else
                                        {{ __('No items to display') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Enhanced Financial Summary --}}
            @if (count($inventoryData) > 0)
                @php
                    $summary = collect($inventoryData);
                    $totalIncreaseValue = $summary->where('discrepancy_value', '>', 0)->sum('discrepancy_value');
                    $totalDecreaseValue = abs($summary->where('discrepancy_value', '<', 0)->sum('discrepancy_value'));
                    $netDifference = $summary->sum('discrepancy_value');
                @endphp

                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-up fs-2"></i>
                                <h6 class="mt-2">{{ __('Total Increase Value') }}</h6>
                                <h4>{{ number_format($totalIncreaseValue) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-down fs-2"></i>
                                <h6 class="mt-2">{{ __('Total Decrease Value') }}</h6>
                                <h4>{{ number_format($totalDecreaseValue) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card {{ $netDifference >= 0 ? 'bg-success' : 'bg-warning' }} text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fs-2"></i>
                                <h6 class="mt-2">{{ __('Net Difference') }}</h6>
                                <h4>{{ number_format($netDifference) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fs-2"></i>
                                <h6 class="mt-2">{{ __('Difference Account') }}</h6>
                                <small>{{ $inventoryDifferenceAccount ? __('Set') : __('Not Set') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Unsaved Changes Warning --}}
                @if ($hasUnsavedChanges)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('There are unsaved changes. Click "Update Balances & Apply Adjustment" to save changes.') }}
                    </div>
                @endif

                {{-- Items Needing Adjustment Summary Table --}}
                @php
                    $itemsWithDiscrepancies = collect($inventoryData)->where('discrepancy', '!=', 0);
                @endphp

                @if ($itemsWithDiscrepancies->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                {{ __('Items Needing Adjustment') }} ({{ $itemsWithDiscrepancies->count() }}
                                {{ __('Item(s)') }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr class="text-center">
                                            <th>{{ __('Item') }}</th>
                                            <th>{{ __('Book Quantity') }}</th>
                                            <th>{{ __('Actual Quantity') }}</th>
                                            <th>{{ __('Difference') }}</th>
                                            <th>{{ __('Value') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itemsWithDiscrepancies as $item)
                                            <tr class="text-center">
                                                <td>{{ $item['item_name'] }}</td>
                                                <td>{{ number_format($item['system_quantity']) }}</td>
                                                <td>{{ number_format($item['actual_quantity']) }}</td>
                                                <td
                                                    class="{{ $item['discrepancy'] > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    {{ $item['discrepancy'] > 0 ? '+' : '' }}{{ number_format($item['discrepancy']) }}
                                                </td>
                                                <td
                                                    class="{{ $item['discrepancy_value'] > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    {{ $item['discrepancy_value'] > 0 ? '+' : '' }}{{ number_format($item['discrepancy_value']) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // تحسين تجربة المستخدم عند إدخال الكميات
        document.addEventListener('input', function(e) {
            if (e.target && e.target.type === 'number' && e.target.getAttribute('wire:model.live.debounce.500ms')) {
                e.target.style.backgroundColor = '#fff3cd'; // تمييز الحقول المعدلة
                setTimeout(() => {
                    if (e.target && e.target.style) {
                        e.target.style.backgroundColor = '';
                    }
                }, 1000);
            }
        });

        // دالة لطباعة تقرير مخصص
        function printInventoryReport() {
            const printContent = `
                    <style>
                        body { font-family: Arial, sans-serif; direction: rtl; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        th { background-color: #f5f5f5; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .summary { display: flex; justify-content: space-around; margin: 20px 0; }
                        .summary-item { text-align: center; }
                        @media print { .no-print { display: none; } }
                    </style>
                    <div class="header">
                        <h2>تقرير جرد المخزون</h2>
                        <p>التاريخ: ${new Date().toLocaleDateString('ar-EG')}</p>
                        <p>المخزن: ${document.querySelector('[wire\\:model\\.live="selectedWarehouse"] option:checked')?.textContent || 'غير محدد'}</p>
                    </div>
                    ${document.querySelector('.table-responsive')?.innerHTML || ''}
                `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
@endpush

@push('styles')
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, .8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* تحسين مظهر الجدول */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* تحسين حقول الإدخال */
        input[type="number"]:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* تمييز الصفوف بناءً على نوع الفرق */
        .table-danger {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .table-info {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        /* تحسين البطاقات الإحصائية */
        .card .card-body i {
            opacity: 0.8;
        }

        /* تحسين التنبيهات */
        .alert {
            border-left: 4px solid;
            border-radius: 0.375rem;
        }

        .alert-warning {
            border-left-color: #ffc107;
        }

        .alert-success {
            border-left-color: #198754;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }

        .alert-info {
            border-left-color: #0dcaf0;
        }

        @media print {

            .btn,
            .loading-overlay,
            #alertContainer,
            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table {
                font-size: 12px;
            }
        }

        /* تحسينات للجوال */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 12px;
            }

            .card-body {
                padding: 1rem 0.5rem;
            }

            .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>
@endpush
</div>
