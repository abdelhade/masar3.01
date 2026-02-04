@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .report-container {
                margin: 20px;
                direction: rtl;
            }

            .report-header {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
            }

            .report-title {
                color: #2c3e50;
                margin-bottom: 10px;
                font-size: 24px;
            }

            .report-summary {
                display: flex;
                justify-content: space-around;
                margin: 20px 0;
                flex-wrap: wrap;
            }

            .summary-card {
                background: white;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                text-align: center;
                min-width: 150px;
                margin: 5px;
            }

            .summary-card h4 {
                margin: 0;
                color: #3498db;
                font-size: 24px;
            }

            .summary-card p {
                margin: 5px 0 0 0;
                color: #7f8c8d;
                font-size: 14px;
            }

            .comparison-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .comparison-table th {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 8px;
                text-align: center;
                font-weight: bold;
                border: none;
            }

            .comparison-table td {
                border: 1px solid #e1e5e9;
                padding: 12px 8px;
                text-align: center;
                vertical-align: middle;
            }

            .comparison-table tr:nth-child(even) {
                background-color: #f8f9fa;
            }

            .comparison-table tr:hover {
                background-color: #e3f2fd;
            }

            .item-name {
                font-weight: bold;
                color: #2c3e50;
                text-align: right;
                padding-right: 15px;
            }

            .best-price {
                font-weight: bold;
                color: #27ae60;
                font-size: 16px;
                background: #d5f4e6;
                border-radius: 4px;
                padding: 5px;
            }

            .best-supplier {
                font-weight: bold;
                color: #3498db;
                background: #ebf3fd;
                border-radius: 4px;
                padding: 5px;
            }

            .price-cell {
                font-weight: 500;
            }

            .no-price {
                color: #95a5a6;
                font-style: italic;
            }

            .empty-message {
                text-align: center;
                padding: 40px;
                color: #7f8c8d;
                font-size: 18px;
                background: #f8f9fa;
                border-radius: 8px;
            }

            @media (max-width: 768px) {
                .comparison-table {
                    font-size: 12px;
                }

                .comparison-table th,
                .comparison-table td {
                    padding: 8px 4px;
                }

                .report-summary {
                    justify-content: center;
                }

                .summary-card {
                    margin: 5px 10px;
                }
            }
        </style>
    @endpush
    <title>{{ __('Price Comparison Report') }}</title>

    <div class="report-container">
        <div class="report-header">
            <h1 class="report-title">{{ __('Price Comparison Report for Items Between Suppliers') }}</h1>

            @if (!empty($items) && count($items) > 0)
                <div class="report-summary">
                    <div class="summary-card">
                        <h4>{{ count($items) }}</h4>
                        <p>{{ __('Total Items') }}</p>
                    </div>
                    <div class="summary-card">
                        <h4>{{ count($suppliers) }}</h4>
                        <p>{{ __('Total Suppliers') }}</p>
                    </div>
                    <div class="summary-card">
                        <h4>{{ collect($items)->sum('offers_count') }}</h4>
                        <p>{{ __('Total Offers') }}</p>
                    </div>
                </div>
            @endif
        </div>

        @if (isset($message))
            <div class="empty-message">
                <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                <p>{{ $message }}</p>
            </div>
        @elseif(!empty($items) && count($items) > 0)
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">{{ __('Item Name') }}</th>
                        @foreach ($suppliers as $supplierName)
                            <th style="width: {{ 60 / count($suppliers) }}%;">{{ $supplierName }}</th>
                        @endforeach
                        <th style="width: 10%;">{{ __('Best Price') }}</th>
                        <th style="width: 10%;">{{ __('Best Supplier') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td class="item-name">{{ $item['item_name'] }}</td>
                            @foreach ($suppliers as $supplierId => $supplierName)
                                <td class="price-cell">
                                    @if (isset($item['suppliers'][$supplierId]))
                                        <span
                                            class="price-value">{{ number_format($item['suppliers'][$supplierId], 2) }}</span>
                                        @if ($item['suppliers'][$supplierId] == $item['best_price'])
                                            <i class="fas fa-star text-warning" title="{{ __('Best Price') }}"
                                                style="margin-left: 5px;"></i>
                                        @endif
                                    @else
                                        <span class="no-price">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if ($item['best_price'] !== null)
                                    <span
                                        class="best-price badge bg-success">{{ number_format($item['best_price'], 2) }}</span>
                                @else
                                    <span class="no-price">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($item['best_supplier_name'] !== __('Undefined Supplier'))
                                    <span class="best-supplier badge bg-info">{{ $item['best_supplier_name'] }}</span>
                                @else
                                    <span class="no-price">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-message">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; color: #bdc3c7;"></i>
                <h3>{{ __('No Data Available') }}</h3>
                <p>{{ __('No price offers registered in the system currently') }}</p>
            </div>
        @endif
    </div>
@endsection
