<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $titles[$type] ?? 'فاتورة' }}</title>
    <style>
        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 10px;
            color: #000;
            background: white;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 0;
        }

        .header {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: center;
            border-bottom: 2px solid #000;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid #000;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 3px 0;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
        }

        .info-value {
            flex: 1;
            text-align: left;
            padding-left: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            font-size: 11px;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .items-table td:first-child {
            width: 40px;
        }

        .items-table td:nth-child(2) {
            width: 60px;
        }

        .items-table td:nth-child(3) {
            text-align: right;
            width: 200px;
        }

        .totals-section {
            padding: 15px;
            border-top: 1px solid #000;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 2px 0;
        }

        .total-label {
            font-weight: bold;
        }

        .total-value {
            min-width: 100px;
            text-align: left;
        }

        .net-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .amount-in-words {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ccc;
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            padding: 15px;
            border-top: 1px solid #000;
            font-size: 11px;
        }

        .footer-section {
            text-align: center;
        }

        .footer-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notes-section {
            padding: 10px 15px;
            border-top: 1px solid #000;
        }

        .notes-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .page-info {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 10px;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-primary {
            color: #007bff;
        }

        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .badge-info {
            color: #fff;
            background-color: #17a2b8;
        }

        .badge-primary {
            color: #fff;
            background-color: #007bff;
        }
    </style>
</head>

<body>
    <div class="page-info">Page 1 of 1</div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">Massar</div>
            <div class="invoice-title">{{ $titles[$type] ?? 'فاتورة' }}</div>
            @php
                $nationalAddress = \Modules\Settings\Models\PublicSetting::where('key', 'national_address')->value('value');
                $taxNumber = \Modules\Settings\Models\PublicSetting::where('key', 'tax_number')->value('value');
            @endphp
            @if($nationalAddress || $taxNumber)
            <div style="font-size: 10px; margin-top: 5px;">
                @if($nationalAddress)
                <div>العنوان الوطني: {{ $nationalAddress }}</div>
                @endif
                @if($taxNumber)
                <div>الرقم الضريبي: {{ $taxNumber }}</div>
                @endif
            </div>
            @endif
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info">
            <div class="left-info">
                <div class="info-row">
                    <span class="info-label">رقم الفاتورة:</span>
                    <span class="info-value">
                        <span>{{ $pro_id ?? 'غير محدد' }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الفاتورة:</span>
                    <span class="info-value">
                        {{ $pro_date ? \Carbon\Carbon::parse($pro_date)->format('Y-m-d') : 'غير محدد' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الاستحقاق:</span>
                    <span class="info-value">
                        {{ $accural_date ? \Carbon\Carbon::parse($accural_date)->format('Y-m-d') : 'غير محدد' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">الموظف:</span>
                    <span class="info-value">
                        {{ $employees->first()->aname ?? 'غير محدد' }}
                    </span>
                </div>
            </div>

            <div class="right-info">
                <div class="info-row">
                    <span class="info-label">
                        @if (in_array($type, [10, 12, 14, 16, 18, 21, 22, 26]))
                            مدين:
                        @elseif(in_array($type, [11, 13, 15, 17, 20]))
                            دائن:
                        @else
                            الحساب الأول:
                        @endif
                    </span>
                    <span class="info-value">
                        <span>{{ $acc1List->first()->aname ?? 'غير محدد' }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        @if (in_array($type, [10, 12, 14, 16, 18, 21, 22, 26]))
                            دائن:
                        @elseif(in_array($type, [11, 13, 15, 17, 20]))
                            مدين:
                        @else
                            الحساب الثاني:
                        @endif
                    </span>
                    <span class="info-value">
                        <span>{{ $acc2List->first()->aname ?? 'غير محدد' }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        @if (in_array($type, [10, 12, 14, 16, 18, 21, 22, 26]))
                            المدفوع من العميل:
                        @elseif(in_array($type, [11, 13, 15, 17, 20]))
                            المدفوع للمورد:
                        @else
                            المدفوع:
                        @endif
                    </span>
                    <span class="info-value">
                        <span>{{ number_format($received_from_client ?? 0, 2) }} جنيه</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الباركود</th>
                    <th>الوحدة</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الخصم</th>
                    <th>القيمة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoiceItems as $index => $item)
                    @php
                        $itemData = $items->firstWhere('id', $item['item_id']);
                        $unitData = isset($item['available_units']) ? $item['available_units']->first() : null;
                        // Get barcode for this item and unit
                        $barcode = null;
                        if ($itemData) {
                            $barcode = \App\Models\Barcode::where('item_id', $item['item_id'])
                                ->where('unit_id', $item['unit_id'])
                                ->first();
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: right;">
                            <strong>{{ $itemData->name ?? 'غير محدد' }}</strong>
                            @if ($itemData && $itemData->code)
                                <br><small>كود: {{ $itemData->code }}</small>
                            @endif
                        </td>
                        <td>
                            <code>{{ $barcode->barcode ?? 'غير محدد' }}</code>
                        </td>
                        <td>
                            <span>{{ $unitData->name ?? 'غير محدد' }}</span>
                        </td>
                        <td>
                            <span>{{ number_format($item['quantity']) }}</span>
                        </td>
                        <td>
                            <span>
                                <strong>{{ number_format($item['price'], 2) }}</strong> جنيه
                            </span>
                        </td>
                        <td>
                            <span>
                                {{ number_format($item['discount'], 2) }} جنيه
                            </span>
                        </td>
                        <td>
                            <strong>
                                {{ number_format($item['sub_value'] ?? (($item['quantity'] * $item['price']) - ($item['discount'] ?? 0)), 2) }} جنيه
                            </strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">لا توجد أصناف في هذه الفاتورة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-grid">
                <div class="left-totals">
                    <div class="total-row">
                        <span class="total-label">المجموع الفرعي:</span>
                        <span class="total-value">{{ number_format($subtotal ?? 0, 2) }} جنيه</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الخصم:</span>
                        <span class="total-value">
                            - {{ number_format($discount_value ?? 0, 2) }} جنيه
                        </span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الإضافي:</span>
                        <span class="total-value">
                            + {{ number_format($additional_value ?? 0, 2) }} جنيه
                        </span>
                    </div>
                </div>

                <div class="right-totals">
                    <div class="total-row net-total">
                        <span class="total-label">الإجمالي النهائي:</span>
                        <span class="total-value">{{ number_format($total_after_additional ?? 0, 2) }} جنيه</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">المدفوع:</span>
                        <span class="total-value">{{ number_format($received_from_client ?? 0, 2) }} جنيه</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الباقي:</span>
                        <span class="total-value">
                            {{ number_format(max(($total_after_additional ?? 0) - ($received_from_client ?? 0), 0), 2) }}
                            جنيه
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        @if (isset($notes) && $notes)
            <div class="notes-section">
                <div class="notes-label">الملاحظات:</div>
                <div>{{ $notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-section">
                <div class="footer-label">العميل</div>
                <div>{{ now()->format('d-M-y') }}</div>
            </div>
            <div class="footer-section">
                <div class="footer-label">المستخدم</div>
                <div>{{ now()->format('H:i:s A') }}</div>
            </div>
            <div class="footer-section">
                <div class="footer-label">الإدارة</div>
                <div>مدير النظام</div>
            </div>
        </div>
    </div>

    <script>
        // طباعة تلقائية عند تحميل الصفحة
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
