<div class="table-responsive invoice-scroll-container card border border-top-0 border-secondary border-3 h-100"
    style="overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6; position: relative; z-index: 1;">

    <style>
        .invoice-data-grid {
            border-collapse: separate !important;
            border-spacing: 0;
            width: 100%;
            border: none;
        }

        .invoice-data-grid th {
            padding: 6px !important;
            background-color: #bbc8d6ff;
            border: 1px solid #dee2e6;
            border-top: none;
            vertical-align: middle;
            font-weight: bold;
            font-size: 0.8rem;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 1px 0 #dee2e6;
        }

        .invoice-data-grid td {
            padding: 0 !important;
            border: 1px solid #5f5f5fff;
            vertical-align: middle;
            height: 32px;
            font-size: 0.75rem;
        }

        .invoice-data-grid .search-row {
            background-color: #f8f9fa;
            position: sticky;
            top: 40px;
            z-index: 9;
        }

        .invoice-data-grid .search-row td {
            padding: 4px !important;
            border: 1px solid #dee2e6;
        }

        .invoice-data-grid tbody tr:nth-of-type(odd):not(.search-row) {
            background-color: #ffffffff;
        }

        .invoice-data-grid tbody tr:nth-of-type(even):not(.search-row) {
            background-color: #cfcfcf8e;
        }

        .invoice-data-grid .form-control,
        .invoice-data-grid .form-select,
        .invoice-data-grid input,
        .invoice-data-grid select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 32px;
            padding: 4px 6px !important;
            background-color: transparent;
            margin: 0 !important;
            font-size: 0.75rem;
        }

        .invoice-data-grid .form-control:focus,
        .invoice-data-grid .form-select:focus,
        .invoice-data-grid input:focus,
        .invoice-data-grid select:focus {
            background-color: #e8f0fe !important;
            outline: none !important;
            box-shadow: inset 0 0 0 2px #0d6efd !important;
            z-index: 1;
            position: relative;
        }

        .invoice-data-grid input[type=number]::-webkit-inner-spin-button,
        .invoice-data-grid input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .invoice-data-grid input[readonly],
        .invoice-data-grid input:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
            color: #6c757d;
        }

        .invoice-data-grid input.text-center {
            text-align: center;
        }

        .invoice-data-grid td.action-cell {
            padding: 2px !important;
            text-align: center;
        }

        .invoice-data-grid .static-text {
            display: flex;
            align-items: center;
            padding: 0 6px;
            height: 100%;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.75rem;
        }

        .search-results-dropdown {
            position: fixed !important;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border: 2px solid #0d6efd;
            border-top: none;
            z-index: 999999 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 400px;
        }

        .search-results-dropdown .list-group-item {
            border-radius: 0;
            border-left: none;
            border-right: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .search-results-dropdown .list-group-item:hover {
            background-color: #e8f0fe;
            transform: translateX(4px);
        }

        .search-results-dropdown .list-group-item.active {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }

        .search-results-dropdown .list-group-item.active * {
            color: white !important;
        }

        .search-results-dropdown .list-group-item.active .badge {
            background-color: white !important;
            color: #0d6efd !important;
        }
    </style>

    <table class="table invoice-data-grid mb-0" style="min-width: 1200px;">
        <thead class="table-light text-center align-middle">
            <tr>
                @php
                    $columnNames = [
                        'item_name' => __('Item Name'),
                        'code' => __('Code'),
                        'unit' => __('Unit'),
                        'quantity' => __('Quantity'),
                        'batch_number' => __('Batch Number'),
                        'expiry_date' => __('Expiry Date'),
                        'price' => __('Price'),
                        'discount' => __('Discount'),
                        'sub_value' => __('Value'),
                    ];

                    $visibleColumns = ['item_name', 'code', 'unit', 'quantity'];

                    if (in_array($type, [10, 11, 12, 13, 19, 20])) {
                        $visibleColumns[] = 'batch_number';
                        $visibleColumns[] = 'expiry_date';
                    }

                    $visibleColumns = array_merge($visibleColumns, ['price', 'discount', 'sub_value']);
                @endphp

                @foreach ($visibleColumns as $columnKey)
                    <th class="font-bold fw-bold text-center" style="font-size: 0.8rem;">
                        {{ $columnNames[$columnKey] ?? $columnKey }}
                    </th>
                @endforeach
                <th class="font-bold fw-bold text-center" style="font-size: 0.8rem;">{{ __('Action') }}</th>
            </tr>
        </thead>

        <tbody id="invoice-items-tbody">
            {{-- Invoice Items (rendered by JavaScript) - will appear here ABOVE search row --}}

            {{-- Search Row - Always at bottom --}}
            <tr class="search-row">
                <td colspan="2" style="position: relative;">
                    <input type="text" id="search-input" class="form-control"
                        placeholder="{{ __('ابحث عن صنف بالاسم أو الكود...') }}"
                        style="min-height: 36px; font-size: 0.85rem;" autocomplete="off">

                    {{-- Search Results Dropdown --}}
                    <div id="search-results-dropdown" class="search-results-dropdown hidden" style="display: none;">
                        {{-- Results rendered by JavaScript --}}
                    </div>
                </td>

                <td colspan="2">
                    <input type="text" id="barcode-input" class="form-control"
                        placeholder="{{ __('امسح الباركود...') }}" style="min-height: 36px; font-size: 0.85rem;">
                </td>

                <td colspan="20">
                    <div class="d-flex align-items-center justify-content-between px-2" style="min-height: 36px;">
                        <small id="search-status" class="text-muted">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                            جاري تحميل الأصناف...
                        </small>
                        <button type="button" id="reload-items-btn"
                            onclick="window.reloadSearchItems && window.reloadSearchItems()"
                            class="btn btn-sm btn-outline-primary" style="font-size: 0.7rem; padding: 2px 8px;">
                            <i class="fas fa-sync-alt"></i>
                            تحديث
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
