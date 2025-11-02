@extends('admin.dashboard')

@section('sidebar')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('مقارنة عروض أسعار الموردين'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('مقارنة عروض الأسعار')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <br>

            {{-- إحصائيات سريعة --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ count($itemsComparison) }}</h3>
                            <p class="mb-0">إجمالي الأصناف</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ collect($itemsComparison)->sum(fn($item) => count($item['quotations'])) }}</h3>
                            <p class="mb-0">إجمالي العروض</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ collect($itemsComparison)->filter(fn($item) => count($item['quotations']) > 1)->count() }}
                            </h3>
                            <p class="mb-0">أصناف بها منافسة</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ collect($itemsComparison)->filter(fn($item) => count($item['quotations']) == 1)->count() }}
                            </h3>
                            <p class="mb-0">أصناف بعرض واحد</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="las la-balance-scale me-2"></i>
                        مقارنة تفصيلية لعروض أسعار الموردين
                    </h5>
                    <div>
                        <button class="btn btn-light btn-sm" onclick="expandAll()">
                            <i class="las la-expand"></i> توسيع الكل
                        </button>
                        <button class="btn btn-light btn-sm" onclick="collapseAll()">
                            <i class="las la-compress"></i> طي الكل
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse ($itemsComparison as $itemId => $itemData)
                        <div class="item-comparison-section border-bottom">
                            <div class="item-header p-3 bg-light cursor-pointer" onclick="toggleItem({{ $itemId }})"
                                style="cursor: pointer;">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">
                                            <i class="las la-chevron-down toggle-icon" id="icon-{{ $itemId }}"></i>
                                            <i class="las la-box me-2 text-primary"></i>
                                            <strong>{{ $itemData['item_name'] }}</strong>
                                        </h5>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="badge bg-info me-2">
                                            {{ count($itemData['quotations']) }} عرض سعر
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
                                            أفضل سعر: {{ number_format($bestPrice, 2) }}
                                        </span>
                                        @if (count($itemData['quotations']) > 1)
                                            <span class="badge bg-danger">
                                                فرق: {{ number_format($priceDiff, 2) }} ({{ $percentDiff }}%)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="item-quotations collapse show" id="item-{{ $itemId }}">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 60px;">الترتيب</th>
                                                <th>رقم العرض</th>
                                                <th>المورد</th>
                                                <th class="text-center">السعر</th>
                                                <th class="text-center">الكمية</th>
                                                <th class="text-center">الإجمالي</th>
                                                <th class="text-center">الفرق عن الأفضل</th>
                                                <th class="text-center">النسبة</th>
                                                <th>التاريخ</th>
                                                <th>الموظف</th>
                                                <th class="text-center">الحالة</th>
                                                <th class="text-center">العمليات</th>
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
                                                            '<span class="badge bg-success"><i class="las la-trophy"></i> أفضل سعر</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-success" style="font-size: 1.2rem;"><i class="las la-medal"></i> 1</span>';
                                                    } elseif ($percentDiff <= 5) {
                                                        $rowClass = 'table-info';
                                                        $statusBadge =
                                                            '<span class="badge bg-info"><i class="las la-star"></i> سعر ممتاز</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-info" style="font-size: 1.2rem;">' .
                                                            ($index + 1) .
                                                            '</span>';
                                                    } elseif ($percentDiff <= 10) {
                                                        $rowClass = 'table-warning';
                                                        $statusBadge =
                                                            '<span class="badge bg-warning"><i class="las la-exclamation-triangle"></i> سعر مقبول</span>';
                                                        $rankBadge =
                                                            '<span class="badge bg-warning" style="font-size: 1.2rem;">' .
                                                            ($index + 1) .
                                                            '</span>';
                                                    } else {
                                                        $rowClass = 'table-danger';
                                                        $statusBadge =
                                                            '<span class="badge bg-danger"><i class="las la-times-circle"></i> سعر مرتفع</span>';
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
                                                            <i class="las la-star text-warning"></i>
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
                                                                class="btn btn-primary btn-sm" title="عرض">
                                                                <i class="las la-eye"></i>
                                                            </a>
                                                            @if ($isBest)
                                                                <a href="{{ route('invoices.convert-to-purchase', $quotation['invoice_id']) }}"
                                                                    class="btn btn-success btn-sm"
                                                                    title="تحويل لفاتورة مشتريات">
                                                                    <i class="las la-shopping-cart"></i>
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
                                <i class="las la-info-circle me-2"></i>
                                لا توجد عروض أسعار مسجلة حتى الآن
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- @push('styles')
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
@endpush --}}

@push('scripts')
    <script>
        function toggleItem(itemId) {
            const element = $(`#item-${itemId}`);
            const icon = $(`#icon-${itemId}`);

            element.collapse('toggle');
            icon.toggleClass('rotated');
        }

        function expandAll() {
            $('.item-quotations').collapse('show');
            $('.toggle-icon').removeClass('rotated');
        }

        function collapseAll() {
            $('.item-quotations').collapse('hide');
            $('.toggle-icon').addClass('rotated');
        }

        // Auto collapse items with only one quotation
        $(document).ready(function() {
            $('.item-quotations').each(function() {
                const itemId = $(this).attr('id').replace('item-', '');
                const quotationsCount = $(this).find('tbody tr').length;

                if (quotationsCount === 1) {
                    $(this).collapse('hide');
                    $(`#icon-${itemId}`).addClass('rotated');
                }
            });
        });
    </script>
@endpush
