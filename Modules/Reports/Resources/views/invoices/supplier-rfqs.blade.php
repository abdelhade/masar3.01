@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Supplier Price Quotes Comparison'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Price Quotes Comparison')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <br>

            {{-- Quick Statistics --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ count($itemsComparison) }}</h3>
                            <p class="mb-0">{{ __('Total Items') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ collect($itemsComparison)->sum(fn($item) => count($item['quotations'])) }}</h3>
                            <p class="mb-0">{{ __('Total Quotes') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ collect($itemsComparison)->filter(fn($item) => count($item['quotations']) > 1)->count() }}
                            </h3>
                            <p class="mb-0">{{ __('Items with Competition') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ collect($itemsComparison)->filter(fn($item) => count($item['quotations']) == 1)->count() }}
                            </h3>
                            <p class="mb-0">{{ __('Items with Single Quote') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header text-white d-flex justify-content-between align-items-center bg-primary">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale me-2"></i>
                        {{ __('Detailed Supplier Price Quotes Comparison') }}
                    </h5>
                    <div>
                        <button class="btn btn-light btn-sm" onclick="expandAll()">
                            <i class="fas fa-expand"></i> {{ __('Expand All') }}
                        </button>
                        <button class="btn btn-light btn-sm" onclick="collapseAll()">
                            <i class="fas fa-compress"></i> {{ __('Collapse All') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse ($itemsComparison as $itemId => $itemData)
                        <div class="item-comparison-section border-bottom">
                            <div class="item-header p-3 bg-light cursor-pointer custom-toggle-trigger"
                                data-target="#item-{{ $itemId }}" style="cursor: pointer;">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chevron-down toggle-icon {{ count($itemData['quotations']) > 1 ? '' : 'rotated' }}"
                                                id="icon-{{ $itemId }}"></i>
                                            <i class="fas fa-box me-2 text-primary"></i>
                                            <strong>{{ $itemData['item_name'] }}</strong>
                                        </h5>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="badge bg-info me-2">
                                            {{ count($itemData['quotations']) }} {{ __('Quote(s)') }}
                                        </span>
                                        @php
                                            $prices = collect($itemData['quotations'])->pluck('price');
                                            $bestPrice = $prices->min();
                                            $worstPrice = $prices->max();
                                            $priceDiff = $worstPrice - $bestPrice;
                                            $percentDiff =
                                                $bestPrice > 0 ? round(($priceDiff / $bestPrice) * 100, 1) : 0;
                                        @endphp
                                        <span class="badge bg-success">
                                            {{ __('Best Price') }}: {{ number_format($bestPrice, 2) }}
                                        </span>
                                        @if (count($itemData['quotations']) > 1)
                                            <span class="badge bg-danger">
                                                {{ __('Difference') }}: {{ number_format($priceDiff, 2) }}
                                                ({{ $percentDiff }}%)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="item-quotations" id="item-{{ $itemId }}"
                                style="{{ count($itemData['quotations']) > 1 ? 'display: block;' : 'display: none;' }}">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 60px;">{{ __('Rank') }}</th>
                                                <th>{{ __('Quote Number') }}</th>
                                                <th>{{ __('Supplier') }}</th>
                                                <th class="text-center">{{ __('Price') }}</th>
                                                <th class="text-center">{{ __('Quantity') }}</th>
                                                <th class="text-center">{{ __('Total') }}</th>
                                                <th class="text-center">{{ __('Diff from Best') }}</th>
                                                <th class="text-center">{{ __('Percentage') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Employee') }}</th>
                                                <th class="text-center">{{ __('Status') }}</th>
                                                <th class="text-center">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $bestPrice = collect($itemData['quotations'])->min('price');
                                            @endphp
                                            @foreach ($itemData['quotations'] as $index => $quotation)
                                                @php
                                                    $isBest = $quotation['price'] == $bestPrice;
                                                    $priceDiff = $quotation['price'] - $bestPrice;
                                                    $percentDiff =
                                                        $bestPrice > 0 ? round(($priceDiff / $bestPrice) * 100, 1) : 0;

                                                    $rowClass = '';
                                                    $statusBadge = '';
                                                    $rankBadge = '';

                                                    if ($isBest) {
                                                        $rowClass = 'table-success';
                                                        $statusBadge =
                                                            '<span class="badge bg-success"><i class="fas fa-trophy"></i> ' .
                                                            __('Best Price') .
                                                            '</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-success" style="font-size: 1.2rem;"><i class="fas fa-medal"></i> 1</span>';
                                                    } elseif ($percentDiff <= 5) {
                                                        $rowClass = 'table-info';
                                                        $statusBadge =
                                                            '<span class="badge bg-info"><i class="fas fa-star"></i> ' .
                                                            __('Excellent Price') .
                                                            '</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-info" style="font-size: 1.2rem;">' .
                                                            ($index + 1) .
                                                            '</span>';
                                                    } elseif ($percentDiff <= 10) {
                                                        $rowClass = 'table-warning';
                                                        $statusBadge =
                                                            '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> ' .
                                                            __('Acceptable Price') .
                                                            '</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-warning" style="font-size: 1.2rem;">' .
                                                            ($index + 1) .
                                                            '</span>';
                                                    } else {
                                                        $rowClass = 'table-danger';
                                                        $statusBadge =
                                                            '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> ' .
                                                            __('High Price') .
                                                            '</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-danger" style="font-size: 1.2rem;">' .
                                                            ($index + 1) .
                                                            '</span>';
                                                    }
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    <td class="text-center">{!! $rankBadge !!}</td>
                                                    <td><strong>{{ $quotation['invoice_number'] }}</strong></td>
                                                    <td>
                                                        @if ($isBest)
                                                            <i class="fas fa-star text-warning"></i>
                                                        @endif
                                                        <strong>{{ $quotation['supplier_name'] }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong class="{{ $isBest ? 'text-success' : 'text-danger' }}"
                                                            style="font-size: 1.1rem;">
                                                            {{ number_format($quotation['price'], 2) }}
                                                        </strong>
                                                    </td>
                                                    <td class="text-center">{{ number_format($quotation['quantity'], 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <strong>{{ number_format($quotation['total'], 2) }}</strong>
                                                    </td>
                                                    <td
                                                        class="text-center {{ $priceDiff > 0 ? 'text-danger' : 'text-success' }}">
                                                        @if ($isBest)
                                                            <span class="badge bg-success">-</span>
                                                        @else
                                                            <strong>+{{ number_format($priceDiff, 2) }}</strong>
                                                        @endif
                                                    </td>
                                                    <td
                                                        class="text-center {{ $percentDiff > 0 ? 'text-danger' : 'text-success' }}">
                                                        @if ($isBest)
                                                            <span class="badge bg-success">0%</span>
                                                        @else
                                                            <strong>+{{ $percentDiff }}%</strong>
                                                        @endif
                                                    </td>
                                                    <td><small>{{ $quotation['invoice_date'] }}</small></td>
                                                    <td><small>{{ $quotation['employee'] }}</small></td>
                                                    <td class="text-center">{!! $statusBadge !!}</td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('invoices.edit', $quotation['invoice_id']) }}"
                                                                class="btn btn-primary btn-sm" title="{{ __('View') }}">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if ($isBest)
                                                                <a href="{{ route('invoices.convert-to-purchase', $quotation['invoice_id']) }}"
                                                                    class="btn btn-success btn-sm"
                                                                    title="{{ __('Convert to Purchase') }}">
                                                                    <i class="fas fa-shopping-cart"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('No price quotes registered yet') }}
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .item-comparison-section:hover {
            background-color: #f8f9fa;
        }

        .item-header:hover {
            background-color: #e9ecef !important;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(-90deg);
        }

        .table-success {
            background-color: rgba(25, 135, 84, 0.15) !important;
        }

        .table-danger {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }

        .table-warning {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }

        .table-info {
            background-color: rgba(13, 202, 240, 0.15) !important;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        function expandAll() {
            $('.item-quotations').slideDown();
            $('.toggle-icon').removeClass('rotated');
        }

        function collapseAll() {
            $('.item-quotations').slideUp();
            $('.toggle-icon').addClass('rotated');
        }

        $(document).ready(function() {
            // Unbind any previous handlers to be safe
            $('.custom-toggle-trigger').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const targetId = $(this).data('target');
                const targetElement = $(targetId);
                const itemId = targetId.replace('#item-', '');
                const icon = $(`#icon-${itemId}`);

                // Toggle the content
                targetElement.slideToggle(300);

                // Toggle the icon rotation
                icon.toggleClass('rotated');
            });
        });

        // Re-initialize MetisMenu to ensure sidebar works correctly after dynamic changes
        $(document).ready(function() {
            if ($(".metismenu").length > 0) {
                try {
                    $(".metismenu").metisMenu('dispose');
                } catch (e) {
                    console.log('MetisMenu dispose failed', e);
                }
                $(".metismenu").metisMenu();
            }
        });
    </script>
@endpush
