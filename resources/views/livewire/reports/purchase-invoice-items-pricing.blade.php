<div class="invoice-items-pricing">
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            color: #2c3e50;
        }

        .opacity-50 {
            opacity: 0.5;
            pointer-events: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 4px;
            background: #17a2b8;
            color: white;
        }

        .invoice-items-pricing {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
        }

        .pricing-header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            margin-bottom: 0;
        }

        .pricing-header h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .invoice-info {
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label {
            font-weight: 600;
            opacity: 0.9;
        }

        .info-value {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 5px;
            font-weight: 700;
        }

        .controls-section {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .control-group label {
            font-weight: 600;
            color: #2c3e50;
            white-space: nowrap;
            min-width: fit-content;
        }

        .form-control {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        /* .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        } */

        .table-container {
            background: white;
            border-radius: 0 0 15px 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .items-table th {
            background: linear-gradient(45deg, #34495e, #2c3e50);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            border: 1px solid #2c3e50;
        }

        .items-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background: white;
            vertical-align: middle;
        }

        .items-table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .items-table tr:hover td {
            background: #e3f2fd !important;
            transform: scale(1.002);
            transition: all 0.2s ease;
        }

        .price-input {
            width: 80px;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
            background: #fff3cd;
            transition: all 0.3s ease;
        }

        .price-input:focus {
            border-color: #f39c12;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(243, 156, 18, 0.2);
            outline: none;
        }

        /* .item-checkbox {
            transform: scale(1.2);
            margin: 0;
        } */

        .item-code {
            font-weight: 600;
            color: #e67e22;
            font-size: 11px;
        }

        .item-name {
            color: #2c3e50;
            font-weight: 500;
            font-size: 11px;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: right;
        }

        .purchase-price {
            color: #c0392b;
            font-weight: 600;
        }

        .sale-price {
            color: #27ae60;
            font-weight: 600;
        }

        .profit {
            color: #8e44ad;
            font-weight: 600;
        }

        .stats-section {
            background: #ecf0f1;
            padding: 20px;
            display: flex;
            gap: 30px;
            align-items: center;
            border-top: 2px solid #bdc3c7;
            border-radius: 0 0 15px 15px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 120px;
        }

        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .pagination {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
    </style>

    <div class="table-container">
        <!-- Header Section -->
        <div class="pricing-header">
            <h2>🛍️ نظام تسعير أصناف الفاتورة</h2>
            <div class="invoice-info">
                <div class="info-item">
                    <span class="info-label">رقم العملية:</span>
                    <span class="info-value"># {{ $operation->pro_num }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">التاريخ:</span>
                    <span class="info-value">{{ $operation->pro_date }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">النوع:</span>
                    <span class="info-value">فاتورة مشتريات</span>
                </div>
                <div class="info-item">
                    <span class="info-label">المخزن:</span>
                    <span class="info-value">{{ $operation->store->aname ?? 'غير محدد' }}</span>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Controls Section -->
        <div class="controls-section">
            <div class="control-group">
                <label>قيمة الزيادة:</label>
                <input type="number" wire:model="bulkIncrease" class="form-control" placeholder="مثال: 5"
                    step="0.01" style="width: 100px;">

                <select wire:model="increaseType" class="form-control">
                    <option value="fixed">مبلغ ثابت</option>
                    <option value="percent">نسبة مئوية</option>
                </select>
            </div>

            <div class="control-group">
                <label>نوع سعر الشراء:</label>
                <select wire:model="purchasePriceType" class="form-control">
                    <option value="last">آخر سعر شراء</option>
                    <option value="average">متوسط سعر الشراء</option>
                </select>
            </div>

            <div class="control-group">
                <label>تطبيق الزيادة على:</label>
                <select wire:model="targetPriceTypes" class="form-control" multiple>
                    @foreach ($priceTypes as $priceType)
                        <option value="{{ $priceType->id }}">{{ $priceType->name }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="applyBulkIncrease" class="btn btn-main" wire:loading.attr="disabled">
                <span wire:loading.remove>تطبيق الزيادة</span>
                <span wire:loading>جاري التطبيق...</span>
            </button>

            <button wire:click="saveAllPrices" class="btn btn-success" wire:loading.attr="disabled">
                💾 <span wire:loading.remove>حفظ جميع الأسعار</span>
                <span wire:loading>جاري الحفظ...</span>
            </button>
        </div>

        <!-- Table Section -->
        <div class="table-responsive" wire:loading.class="opacity-50">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="40">م</th>
                        <th width="120">كود الصنف</th>
                        <th width="200">اسم الصنف</th>
                        <th width="60">الوحدة</th>
                        <th width="70">الكمية</th>
                        {{-- <th width="90">سعر التكلفة</th>
                        <th width="90">إجمالي التكلفة</th> --}}
                        @foreach ($priceTypes as $priceType)
                            <th width="90">{{ $priceType->name }}</th>
                        @endforeach
                        {{-- <th width="90">قيمة الزيادة</th> --}}
                        <th width="90">إجمالي البيع</th>
                        {{-- <th width="80">الربح</th> --}}
                        <th width="80">المخزون الحالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td class="item-code">{{ $item->item->code ?? 'غير محدد' }}</td>
                            <td class="item-name" title="{{ $item->item->name ?? 'غير محدد' }}">
                                {{ $item->item->name ?? 'غير محدد' }}
                            </td>
                            <td>{{ $item->unit->name ?? 'قطعة' }}</td>
                            <td>{{ number_format($item->qty_in) }}</td>
                            {{-- <td class="purchase-price">{{ number_format($item->cost_price) }} ج</td>
                            <td class="purchase-price">{{ number_format($item->cost_price * $item->qty_in) }} ج</td> --}}
                            @foreach ($priceTypes as $priceType)
                                <td class="sale-price">
                                    {{ number_format($item->item->prices()->where('prices.id', $priceType->id)->wherePivot('unit_id', $item->unit_id)->first()?->pivot?->price ?? 0) }}
                                    ج
                                </td>
                            @endforeach
                            <td class="sale-price">{{ number_format($item->detail_value) }} ج</td>
                            {{-- <td class="profit">
                                @php
                                    $salePrice =
                                        $item->item->prices()->wherePivot('unit_id', $item->unit_id)->first()?->pivot
                                            ?->price ?? $item->fat_price;
                                    $profit = ($salePrice - $item->cost_price) * $item->fat_quantity;
                                @endphp
                                {{ number_format($profit) }} ج
                            </td> --}}
                            <td>{{ number_format($item->current_stock_value) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 10 + count($priceTypes) }}" class="loading">
                                @if ($searchTerm)
                                    لا توجد أصناف تطابق البحث "{{ $searchTerm }}"
                                @else
                                    لا توجد أصناف في هذه الفاتورة
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($items->hasPages())
            <div class="pagination">
                {{ $items->links() }}
            </div>
        @endif

        <!-- Statistics Section -->
        {{-- <div class="stats-section">
            <div class="stat-item">
                <div class="stat-label">إجمالي الأصناف</div>
                <div class="stat-value">{{ $totalItems }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">إجمالي قيمة التكلفة</div>
                <div class="stat-value">{{ number_format($totalPurchaseValue, 2) }} ج</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">إجمالي قيمة البيع</div>
                <div class="stat-value">{{ number_format($totalSaleValue, 2) }} ج</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">إجمالي الربح المتوقع</div>
                <div class="stat-value">{{ number_format($totalProfit, 2) }} ج</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">متوسط نسبة الربح</div>
                <div class="stat-value">{{ number_format($avgProfitMargin, 2) }}%</div>
            </div>
        </div> --}}
    </div>

    <!-- Loading Overlay -->
    <div wire:loading.flex class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <span>جاري المعالجة...</span>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('focusin', function(e) {
                    if (e.target.classList.contains('price-input')) {
                        e.target.select();
                    }
                });
            });
        </script>
    @endpush
</div>
