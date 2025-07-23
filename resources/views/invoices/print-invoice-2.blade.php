<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $titles[$type] ?? 'فاتورة' }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
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
            min-width: 100px;
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
    </style>
</head>
<body>
    <div class="page-info">Page 1 of 1</div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">skysoft</div>
            <div class="invoice-title">{{ $titles[$type] ?? 'فاتورة' }}</div>
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info">
            <div class="left-info">
                <div class="info-row">
                    <span class="info-label">الرقم</span>
                    <span class="info-value">{{ $serial_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">التاريخ</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($pro_date)->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">رقم الموظف</span>
                    <span class="info-value">{{ $emp_id }}</span>
                </div>
            </div>

            <div class="right-info">
                <div class="info-row">
                    <span class="info-label">{{ $acc1Role }}</span>
                    <span class="info-value">
                        {{ $acc1List->first()->acc_name ?? 'غير محدد' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">اسم العميل</span>
                    <span class="info-value">
                        {{ $acc1List->first()->acc_name ?? 'غير محدد' }} - {{ $acc1List->first()->acc_name_2 ?? '' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">المستودع</span>
                    <span class="info-value">{{ $acc2List->first()->acc_name ?? 'المخزن الرئيسي' }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>رقم الصنف</th>
                    <th>اسم الصنف</th>
                    <th>الكمية</th>
                    <th>الوحدة</th>
                    <th>السعر</th>
                    <th>القيمة</th>
                    <th>نسبة الخصم</th>
                    <th>قيمة الخصم</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceItems as $item)
                @php
                    $itemData = $items->firstWhere('id', $item['item_id']);
                    $unitData = $item['available_units']->first();
                    $discountValue = ($item['sub_value'] * $item['discount']) / 100;
                @endphp
                <tr>
                    <td>{{ $item['item_id'] }}</td>
                    <td style="text-align: right;">{{ $itemData->item_name ?? 'غير محدد' }}</td>
                    <td>{{ number_format($item['quantity'], 0) }}</td>
                    <td>{{ $unitData->unit_name ?? 'قطعة' }}</td>
                    <td>{{ number_format($item['price'], 2) }}</td>
                    <td>{{ number_format($item['sub_value'], 2) }}</td>
                    <td>{{ $item['discount'] }}%</td>
                    <td>{{ number_format($discountValue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-grid">
                <div class="left-totals">
                    <div class="total-row">
                        <span class="total-label">اجمالي الفاتورة</span>
                        <span class="total-value">{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الكمية</span>
                        <span class="total-value">{{ number_format(collect($invoiceItems)->sum('quantity'), 2) }}</span>
                    </div>
                </div>

                <div class="right-totals">
                    <div class="total-row">
                        <span class="total-label">مستلم من العميل</span>
                        <span class="total-value">{{ number_format($received_from_client, 2) }} نقداً</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الرصيد السابق</span>
                        <span class="total-value">0</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">الرصيد الحالي</span>
                        <span class="total-value">{{ number_format($total_after_additional - $received_from_client, 0) }}</span>
                    </div>
                    <div class="total-row net-total">
                        <span class="total-label">الصافي</span>
                        <span class="total-value">{{ number_format($total_after_additional, 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Amount in Words -->
            {{-- <div class="amount-in-words">
                {{ $this->convertNumberToArabicWords($total_after_additional) }} جنيه
            </div> --}}
        </div>

        <!-- Notes Section -->
        @if($notes)
        <div class="notes-section">
            <div class="notes-label">ملحوظات:</div>
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
