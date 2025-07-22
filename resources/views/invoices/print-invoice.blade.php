<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الفاتورة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-gray: #f5f5f5;
            --dark-gray: #333;
            --border-color: #ddd;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            color: var(--dark-gray);
            background-color: #f9f9f9;
            line-height: 1.6;
            padding: 20px;
        }

        .print-controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-btn {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .print-btn:active {
            transform: translateY(1px);
        }

        .invoice-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        /* باقي أنماط الفاتورة كما هي في الكود السابق */
        /* ... */

        @media print {
            .print-controls {
                display: none;
            }

            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 14px;
            color: #2c3e50;
            line-height: 1.6;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        /* Header Section */
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            position: relative;
            overflow: hidden;
        }

        .invoice-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .company-info h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .company-info p {
            font-size: 16px;
            opacity: 0.9;
        }

        .invoice-title {
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px 30px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .invoice-title h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .invoice-details-header {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            font-size: 14px;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }

        /* Main Content */
        .invoice-body {
            padding: 40px;
        }

        .client-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .section-title::before {
            content: '';
            width: 20px;
            height: 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            margin-left: 10px;
        }

        .client-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .detail-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
            min-width: 80px;
            margin-left: 10px;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 30px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .items-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .items-table th {
            padding: 18px 12px;
            font-weight: 600;
            text-align: center;
            font-size: 15px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .items-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #e9ecef;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .items-table tbody tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            transform: scale(1.01);
        }

        .items-table td {
            padding: 15px 12px;
            text-align: center;
            font-weight: 500;
            color: #495057;
        }

        .empty-row td {
            padding: 40px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
        }

        /* Totals Section */
        .totals-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .totals-grid {
            display: grid;
            gap: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .total-row:hover {
            transform: translateX(-5px);
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .total-row.final-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 700;
            font-size: 18px;
            border: none;
        }

        .total-row.remaining {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            font-weight: 600;
        }

        .total-label {
            font-weight: 600;
            color: #495057;
        }

        .total-value {
            font-weight: 700;
            color: #2c3e50;
            font-family: 'Courier New', monospace;
        }

        .final-total .total-label,
        .final-total .total-value,
        .remaining .total-label,
        .remaining .total-value {
            color: white;
        }

        /* Notes Section */
        .notes-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #ffc107;
        }

        .notes-content {
            background: white;
            padding: 15px;
            border-radius: 8px;
            color: #856404;
            line-height: 1.8;
        }

        /* Footer */
        .invoice-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            text-align: center;
            padding: 25px;
            position: relative;
            overflow: hidden;
        }

        .invoice-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }

        .footer-content {
            position: relative;
            z-index: 2;
        }

        .footer-content p {
            margin-bottom: 8px;
            font-size: 16px;
        }

        .footer-content p:last-child {
            opacity: 0.7;
            font-size: 14px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                font-size: 12px;
            }

            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }

            .invoice-header {
                padding: 15px 20px;
            }

            .company-info h1 {
                font-size: 24px;
                margin-bottom: 5px;
            }

            .company-info p {
                font-size: 12px;
            }

            .invoice-title h2 {
                font-size: 20px;
                margin-bottom: 8px;
            }

            .invoice-title {
                padding: 12px 20px;
            }

            .invoice-body {
                padding: 20px;
            }

            .client-section {
                padding: 15px;
                margin-bottom: 15px;
            }

            .section-title {
                font-size: 14px;
                margin-bottom: 8px;
            }

            .detail-row {
                padding: 6px 10px;
                margin-bottom: 3px;
            }

            .items-table th,
            .items-table td {
                padding: 6px 8px;
                font-size: 11px;
            }

            .items-table th {
                font-size: 12px;
            }

            .totals-section {
                padding: 15px;
                margin-bottom: 15px;
            }

            .total-row {
                padding: 6px 12px;
                margin-bottom: 3px;
            }

            .total-row.final-total {
                font-size: 14px;
            }

            .notes-section {
                padding: 15px;
                margin-bottom: 15px;
            }

            .notes-content {
                padding: 8px;
                font-size: 11px;
            }

            .invoice-footer {
                padding: 12px;
            }

            .footer-content p {
                font-size: 12px;
                margin-bottom: 3px;
            }

            @page {
                size: A4;
                margin: 8mm;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .invoice-body {
                padding: 20px;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .client-details {
                grid-template-columns: 1fr;
            }

            .items-table {
                font-size: 12px;
            }

            .items-table th,
            .items-table td {
                padding: 10px 8px;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .invoice-container>* {
            animation: fadeInUp 0.6s ease forwards;
        }

        .invoice-body>* {
            animation: fadeInUp 0.8s ease forwards;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="company-info">
                    <h1>شركة التقنية المتقدمة</h1>
                    <p>للحلول التقنية والبرمجية</p>
                    <p>📧 info@company.com | 📱 +966 12 345 6789</p>
                </div>

                <div class="invoice-title">
                    <h2> {{ $titles[$type] }}</h2>
                    <div class="invoice-details-header">
                        <div class="detail-item">
                            <span>رقم الفاتورة:</span>
                            <strong>{{ $pro_id }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>التاريخ:</span>
                            <strong>{{ $pro_date }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>تاريخ الاستحقاق:</span>
                            <strong>{{ $accural_date }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>رقم السيريال:</span>
                            <strong>{{ $serial_number }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="invoice-body">
            <!-- Client Details Section -->
            <div class="client-section">
                <div class="section-title">تفاصيل الفاتورة</div>
                <div class="client-details">

                    <div class="detail-row">
                        <span class="detail-label">العميل:</span>
                        <span
                            class="detail-value">{{ $acc1List->firstWhere('id', $acc1_id)->aname ?? 'غير محدد' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">المخزن:</span>
                        <span
                            class="detail-value">{{ $acc2List->firstWhere('id', $acc2_id)->aname ?? 'غير محدد' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">الموظف:</span>
                        <span
                            class="detail-value">{{ $employees->firstWhere('id', $emp_id)->aname ?? 'غير محدد' }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="items-section">
                <div class="section-title">تفاصيل الأصناف</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>الوحدة</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>الخصم</th>
                            <th>القيمة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoiceItems as $row)
                            <tr>
                                <td>{{ $items->firstWhere('id', $row['item_id'])->name ?? 'غير محدد' }}</td>
                                <td>{{ $row['available_units']->firstWhere('id', $row['unit_id'])->name ?? 'غير محدد' }}
                                </td>
                                <td>{{ number_format($row['quantity']) }}</td>
                                <td>{{ number_format($row['price'], 2) }} ج.م</td>
                                <td>{{ number_format($row['discount'], 2) }} ج.م</td>
                                <td>{{ number_format($row['sub_value'], 2) }} ج.م</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center;">لا توجد أصناف مضافة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="client-section">
                <div class="section-title">ملخص الفاتورة</div>
                <div class="client-details">

                    <div class="detail-row">
                        <span class="total-label">الإجمالي الفرعي:</span>
                        <span class="total-value">{{ number_format($subtotal) }}ج.م</span>
                    </div>

                    <div class="detail-row">
                        <span class="total-label">الخصم:</span>
                        <span class="total-value">{{ number_format($discount_percentage) }}ج.م</span>
                    </div>

                    <div class="detail-row">
                        <span class="total-label">قيمة الخصم: </span>
                        <span class="total-value">{{ number_format($discount_value) }}ج.م</span>
                    </div>

                    <div class="detail-row">
                        <span class="total-label">الإضافي:</span>
                        <span class="total-value">{{ number_format($additional_value) }}ج.م</span>
                    </div>

                    <div class="detail-row">
                        <span class="total-label">قيمة الإضافي:</span>
                        <span class="total-value">{{ number_format($additional_value) }}%</span>
                    </div>

                    <div class="detail-row">
                        <span class="total-label">المدفوع:</span>
                        <span class="total-value">{{ number_format($received_from_client) }}ج.م</span>
                    </div>

                    <div class="detail-row total-row final-total">
                        <span class="total-label">الإجمالي النهائي:</span>
                        <span class="total-value">{{ number_format($total_after_additional) }}ج.م</span>
                    </div>

                    <div class="detail-row total-row remaining">
                        <span class="total-value">الباقي:</span>
                        <span
                            class="total-value">{{ number_format(max($total_after_additional - $received_from_client, 0)) }}ج.م</span>
                    </div>
                </div>
            </div>
            <!-- Notes Section -->
            <div class="notes-section">
                <div class="section-title">ملاحظات</div>
                <div class="notes-content">
                    يرجى سداد المبلغ المتبقي خلال 30 يوماً من تاريخ الفاتورة. جميع الأجهزة تشمل ضمان لمدة سنة واحدة. في
                    حالة وجود أي استفسارات، يرجى التواصل معنا على الرقم المذكور أعلاه.
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-content">
                <p>شكراً لتعاملكم معنا</p>
                <p>تم إنشاء هذه الفاتورة بواسطة النظام الآلي</p>
            </div>
        </div>
    </div>
    <br>
    <div class="print-controls">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i>
            طباعة الفاتورة
        </button>

    </div>
</body>

</html>
