<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الشيكات</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            direction: rtl;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header .info {
            font-size: 11px;
            color: #666;
        }
        
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        
        .filters p {
            margin: 3px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: right;
            font-weight: 600;
            border: 1px solid #555;
        }
        
        table td {
            padding: 6px;
            border: 1px solid #ddd;
            text-align: right;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .summary p {
            margin: 5px 0;
            font-size: 13px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-cleared { background-color: #28a745; color: #fff; }
        .badge-bounced { background-color: #dc3545; color: #fff; }
        .badge-cancelled { background-color: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير الشيكات</h1>
        <div class="info">
            <p>تاريخ التوليد: {{ $generated_at }}</p>
        </div>
    </div>

    @if(!empty($filters['status']) || !empty($filters['type']) || !empty($filters['start_date']))
    <div class="filters">
        <strong>الفلاتر المطبقة:</strong>
        @if(!empty($filters['status']))
            <p>الحالة: {{ \Modules\Checks\Models\Check::getStatuses()[$filters['status']] ?? $filters['status'] }}</p>
        @endif
        @if(!empty($filters['type']))
            <p>النوع: {{ \Modules\Checks\Models\Check::getTypes()[$filters['type']] ?? $filters['type'] }}</p>
        @endif
        @if(!empty($filters['start_date']) && !empty($filters['end_date']))
            <p>الفترة: من {{ $filters['start_date'] }} إلى {{ $filters['end_date'] }}</p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>رقم الشيك</th>
                <th>البنك</th>
                <th>المبلغ</th>
                <th>تاريخ الاستحقاق</th>
                <th>الحالة</th>
                <th>النوع</th>
                <th>صاحب الحساب</th>
            </tr>
        </thead>
        <tbody>
            @forelse($checks as $check)
                <tr>
                    <td>{{ $check->check_number }}</td>
                    <td>{{ $check->bank_name }}</td>
                    <td>{{ number_format($check->amount, 2) }} ر.س</td>
                    <td>{{ $check->due_date->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge badge-{{ $check->status }}">
                            {{ \Modules\Checks\Models\Check::getStatuses()[$check->status] ?? $check->status }}
                        </span>
                    </td>
                    <td>{{ \Modules\Checks\Models\Check::getTypes()[$check->type] ?? $check->type }}</td>
                    <td>{{ $check->account_holder_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        لا توجد شيكات مطابقة للبحث
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h3>ملخص التقرير</h3>
        <p><strong>عدد الشيكات:</strong> {{ count($checks) }}</p>
        <p><strong>إجمالي المبلغ:</strong> {{ number_format($total_amount, 2) }} ر.س</p>
    </div>

    <div class="footer">
        <p>تم إنشاء هذا التقرير تلقائياً من نظام إدارة الشيكات</p>
    </div>
</body>
</html>

