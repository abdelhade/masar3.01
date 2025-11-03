@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('مقارنة عروض أسعار العملاء'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('مقارنة عروض الأسعار للعملاء')],
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
                            <h3>{{ is_array($itemsComparison) ? count($itemsComparison) : 0 }}</h3>
                            <p class="mb-0">إجمالي الأصناف</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ is_array($itemsComparison) ? collect($itemsComparison)->sum(fn($item) => count($item['quotations'] ?? [])) : 0 }}
                            </h3>
                            <p class="mb-0">إجمالي العروض</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ is_array($itemsComparison) ? collect($itemsComparison)->filter(fn($item) => count($item['quotations'] ?? []) > 1)->count() : 0 }}
                            </h3>
                            <p class="mb-0">أصناف بها عروض متعددة</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ is_array($itemsComparison) ? collect($itemsComparison)->filter(fn($item) => count($item['quotations'] ?? []) == 1)->count() : 0 }}
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
                        مقارنة تفصيلية لعروض أسعار العملاء
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
                    @if (is_array($itemsComparison) && count($itemsComparison) > 0)
                        @foreach ($itemsComparison as $itemId => $itemData)
                            <div class="item-comparison-section border-bottom">
                                <div class="item-header p-3 bg-light cursor-pointer"
                                    onclick="toggleItem({{ $itemId }})" style="cursor: pointer;">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="mb-0">
                                                <i class="las la-chevron-down toggle-icon"
                                                    id="icon-{{ $itemId }}"></i>
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
                                                $highestPrice = $prices->max();
                                                $lowestPrice = $prices->min();
                                                $priceDiff = $highestPrice - $lowestPrice;
                                                $percentDiff =
                                                    $lowestPrice > 0 ? round(($priceDiff / $lowestPrice) * 100, 1) : 0;
                                            @endphp
                                            <span class="badge bg-success">
                                                أعلى سعر: {{ number_format($highestPrice, 2) }}
                                            </span>
                                            @if (count($itemData['quotations']) > 1)
                                                <span class="badge bg-warning">
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
                                                    {{-- <th>العميل</th> --}}
                                                    <th class="text-center">السعر</th>
                                                    <th class="text-center">الكمية</th>
                                                    <th class="text-center">الإجمالي</th>
                                                    <th class="text-center">الفرق عن الأعلى</th>
                                                    <th class="text-center">النسبة</th>
                                                    <th>التاريخ</th>
                                                    <th>الموظف</th>
                                                    <th class="text-center">الحالة</th>
                                                    <th class="text-center">العمليات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $highestPrice = collect($itemData['quotations'])->max('price');
                                                @endphp
                                                @foreach ($itemData['quotations'] as $index => $quotation)
                                                    @php
                                                        $isHighest = $quotation['price'] == $highestPrice;
                                                        $priceDiff = $highestPrice - $quotation['price'];
                                                        $percentDiff =
                                                            $highestPrice > 0
                                                                ? round(($priceDiff / $highestPrice) * 100, 1)
                                                                : 0;

                                                        $rowClass = '';
                                                        $statusBadge = '';
                                                        $rankBadge = '';

                                                        if ($isHighest) {
                                                            $rowClass = 'table-success';
                                                            $statusBadge =
                                                                '<span class="badge bg-success"><i class="las la-trophy"></i> أعلى سعر</span>';
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
                                                                '<span class="badge bg-danger"><i class="las la-times-circle"></i> سعر منخفض</span>';
                                                            $rankBadge =
                                                                '<span class="badge bg-danger" style="font-size: 1.2rem;">' .
                                                                ($index + 1) .
                                                                '</span>';
                                                        }
                                                    @endphp
                                                    <tr class="{{ $rowClass }}">
                                                        <td class="text-center">{!! $rankBadge !!}</td>
                                                        <td><strong>{{ $quotation['invoice_number'] }}</strong></td>
                                                        {{-- <td>
                                                            @if ($isHighest)
                                                                <i class="las la-star text-warning"></i>
                                                            @endif
                                                            <strong>{{ $quotation['partner_name'] }}</strong>
                                                        </td> --}}
                                                        <td class="text-center">
                                                            <strong
                                                                class="{{ $isHighest ? 'text-success' : 'text-warning' }}"
                                                                style="font-size: 1.1rem;">
                                                                {{ number_format($quotation['price'], 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-center">
                                                            {{ number_format($quotation['quantity'], 2) }}</td>
                                                        <td class="text-center">
                                                            <strong>{{ number_format($quotation['total'], 2) }}</strong>
                                                        </td>
                                                        <td
                                                            class="text-center {{ $priceDiff > 0 ? 'text-danger' : 'text-success' }}">
                                                            @if ($isHighest)
                                                                <span class="badge bg-success">-</span>
                                                            @else
                                                                <strong>-{{ number_format($priceDiff, 2) }}</strong>
                                                            @endif
                                                        </td>
                                                        <td
                                                            class="text-center {{ $percentDiff > 0 ? 'text-danger' : 'text-success' }}">
                                                            @if ($isHighest)
                                                                <span class="badge bg-success">0%</span>
                                                            @else
                                                                <strong>-{{ $percentDiff }}%</strong>
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
                                                                @if ($isHighest)
                                                                    <a href="{{ route('invoices.convert-to-sales', $quotation['invoice_id']) }}"
                                                                        class="btn btn-success btn-sm"
                                                                        title="تحويل لفاتورة مبيعات">
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
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="alert alert-info">
                                <i class="las la-info-circle me-2"></i>
                                لا توجد عروض أسعار مسجلة للعملاء حتى الآن
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

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

    <style>
        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(-90deg);
        }

        .item-header:hover {
            background-color: #e9ecef !important;
        }
    </style>
@endpush
