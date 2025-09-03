<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة نقاط البيع - {{ $pro_id }}</title>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
            direction: rtl;
        }
        
        .receipt {
            width: 80mm;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
            background: white;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .company-logo {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
        }
        
        .company-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 14px;
            font-weight: 600;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .receipt-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
        }
        
        .info-value {
            color: #666;
        }
        
        .receipt-items {
            margin-bottom: 10px;
        }
        
        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            font-size: 10px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #ccc;
        }
        
        .item-name {
            flex: 1;
            font-weight: 500;
            padding-left: 5px;
        }
        
        .item-qty {
            width: 30px;
            text-align: center;
        }
        
        .item-price {
            width: 40px;
            text-align: left;
        }
        
        .item-total {
            width: 50px;
            text-align: left;
            font-weight: 600;
        }
        
        .receipt-totals {
            border-top: 2px solid #333;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .total-row.grand-total {
            font-size: 14px;
            font-weight: 700;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .receipt-payment {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #333;
            font-size: 10px;
            color: #666;
        }
        
        .thank-you {
            font-weight: 600;
            font-size: 12px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .footer-info {
            margin-bottom: 3px;
        }
        
        .barcode {
            text-align: center;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 600;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .receipt {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 5px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* تحسينات للطابعات الحرارية */
        @media print and (max-width: 80mm) {
            body {
                font-size: 10px;
            }
            
            .receipt-title {
                font-size: 12px;
            }
            
            .total-row.grand-total {
                font-size: 12px;
            }
        }
    </style>
</head>
<body onload="window.print(); window.close();">
    <div class="receipt">
        <!-- رأس الفاتورة -->
        <div class="receipt-header">
            <div class="company-logo">{{ config('app.name') }}</div>
            <div class="company-info">نظام نقاط البيع المتقدم</div>
            <div class="company-info">{{ config('company.address', 'عنوان الشركة') }}</div>
            <div class="company-info">هاتف: {{ config('company.phone', '000-000-0000') }}</div>
            <div class="receipt-title">فاتورة مبيعات</div>
        </div>

        <!-- معلومات الفاتورة -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">رقم الفاتورة:</span>
                <span class="info-value">{{ $pro_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">التاريخ:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($pro_date)->format('Y-m-d') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">الوقت:</span>
                <span class="info-value">{{ \Carbon\Carbon::now()->format('H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">الكاشير:</span>
                <span class="info-value">{{ auth()->user()->name ?? 'غير محدد' }}</span>
            </div>
            @if($acc1List->count() > 0 && $acc1List->first()->aname !== 'عميل نقدي')
            <div class="info-row">
                <span class="info-label">العميل:</span>
                <span class="info-value">{{ $acc1List->first()->aname ?? 'عميل نقدي' }}</span>
            </div>
            @endif
        </div>

        <!-- عناصر الفاتورة -->
        <div class="receipt-items">
            <div class="items-header">
                <span>الصنف</span>
                <span>الكمية</span>
                <span>السعر</span>
                <span>المجموع</span>
            </div>
            
            @foreach($invoiceItems as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $items->where('id', $item['item_id'])->first()->name ?? 'غير محدد' }}
                </div>
                <div class="item-qty">{{ number_format($item['quantity'], 0) }}</div>
                <div class="item-price">{{ number_format($item['price'], 2) }}</div>
                <div class="item-total">{{ number_format($item['sub_value'], 2) }}</div>
            </div>
            @endforeach
        </div>

        <!-- المجاميع -->
        <div class="receipt-totals">
            <div class="total-row">
                <span>المجموع الفرعي:</span>
                <span>{{ number_format($subtotal, 2) }} ريال</span>
            </div>
            
            @if($discount_value > 0)
            <div class="total-row">
                <span>الخصم ({{ number_format($discount_percentage, 1) }}%):</span>
                <span>-{{ number_format($discount_value, 2) }} ريال</span>
            </div>
            @endif
            
            @if($additional_value > 0)
            <div class="total-row">
                <span>الإضافي ({{ number_format($additional_percentage, 1) }}%):</span>
                <span>+{{ number_format($additional_value, 2) }} ريال</span>
            </div>
            @endif
            
            <div class="total-row grand-total">
                <span>الإجمالي:</span>
                <span>{{ number_format($total_after_additional, 2) }} ريال</span>
            </div>
        </div>

        <!-- معلومات الدفع -->
        @if($received_from_client > 0)
        <div class="receipt-payment">
            <div class="payment-row">
                <span>المدفوع:</span>
                <span>{{ number_format($received_from_client, 2) }} ريال</span>
            </div>
            @if($received_from_client > $total_after_additional)
            <div class="payment-row">
                <span>المتبقي:</span>
                <span>{{ number_format($received_from_client - $total_after_additional, 2) }} ريال</span>
            </div>
            @endif
        </div>
        @endif

        <!-- الباركود -->
        <div class="barcode">
            *{{ $pro_id }}*
        </div>

        <!-- ملاحظات -->
        @if($notes)
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">ملاحظات:</span>
            </div>
            <div style="margin-top: 5px; font-size: 10px; color: #666;">
                {{ $notes }}
            </div>
        </div>
        @endif

        <!-- تذييل الفاتورة -->
        <div class="receipt-footer">
            <div class="thank-you">شكراً لك على التسوق معنا!</div>
            <div class="footer-info">للاستفسارات: {{ config('company.phone', '000-000-0000') }}</div>
            <div class="footer-info">{{ config('company.website', 'www.company.com') }}</div>
            <div class="footer-info">تم الإنشاء بواسطة نظام نقاط البيع</div>
            <div class="footer-info">{{ now()->format('Y-m-d H:i:s') }}</div>
        </div>
    </div>

    <script>
        // طباعة تلقائية وإغلاق النافذة
        window.onload = function() {
            setTimeout(function() {
                window.print();
                setTimeout(function() {
                    window.close();
                }, 1000);
            }, 500);
        };

        // التعامل مع إلغاء الطباعة
        window.onafterprint = function() {
            window.close();
        };

        // للطابعات الحرارية - تحسين التنسيق
        if (window.matchMedia && window.matchMedia('print').matches) {
            document.body.style.fontSize = '10px';
        }
    </script>
</body>
</html>
