@extends('pos::layouts.master')

@section('content')
<div class="pos-show-container">
    <div class="transaction-header">
        <div class="header-content">
            <div class="transaction-info">
                <h1>عرض معاملة POS</h1>
                <div class="transaction-meta">
                    <span class="invoice-number">فاتورة رقم: {{ $transaction->pro_id }}</span>
                    <span class="transaction-date">{{ \Carbon\Carbon::parse($transaction->pro_date)->format('Y-m-d H:i') }}</span>
                    <span class="transaction-status">
                        <i class="fas fa-check-circle text-success"></i>
                        مكتملة
                    </span>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="{{ route('pos.print', $transaction->id) }}" 
                   class="btn-pos btn-success" 
                   target="_blank">
                    <i class="fas fa-print"></i>
                    طباعة
                </a>
                
                <a href="{{ route('pos.index') }}" 
                   class="btn-pos btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    عودة
                </a>
            </div>
        </div>
    </div>

    <div class="transaction-details">
        <div class="details-grid">
            <!-- معلومات أساسية -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> معلومات أساسية</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="label">رقم الفاتورة:</span>
                        <span class="value">{{ $transaction->pro_id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">تاريخ المعاملة:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($transaction->pro_date)->format('Y-m-d') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">وقت المعاملة:</span>
                        <span class="value">{{ $transaction->created_at->format('H:i:s') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">الكاشير:</span>
                        <span class="value">{{ $transaction->employee->aname ?? 'غير محدد' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">المخزن:</span>
                        <span class="value">{{ $transaction->acc2Head->aname ?? 'غير محدد' }}</span>
                    </div>
                </div>
            </div>

            <!-- معلومات العميل -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> معلومات العميل</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="label">اسم العميل:</span>
                        <span class="value">{{ $transaction->acc1Head->aname ?? 'عميل نقدي' }}</span>
                    </div>
                    @if($transaction->acc1Head && $transaction->acc1Head->phone)
                    <div class="detail-row">
                        <span class="label">الهاتف:</span>
                        <span class="value">{{ $transaction->acc1Head->phone }}</span>
                    </div>
                    @endif
                    @if($transaction->acc1Head && $transaction->acc1Head->address)
                    <div class="detail-row">
                        <span class="label">العنوان:</span>
                        <span class="value">{{ $transaction->acc1Head->address }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- معلومات مالية -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-calculator"></i> الملخص المالي</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="label">المجموع الفرعي:</span>
                        <span class="value">{{ number_format($transaction->fat_total, 2) }} ريال</span>
                    </div>
                    @if($transaction->fat_disc > 0)
                    <div class="detail-row">
                        <span class="label">الخصم:</span>
                        <span class="value text-danger">-{{ number_format($transaction->fat_disc, 2) }} ريال</span>
                    </div>
                    @endif
                    @if($transaction->fat_plus > 0)
                    <div class="detail-row">
                        <span class="label">الإضافي:</span>
                        <span class="value text-success">+{{ number_format($transaction->fat_plus, 2) }} ريال</span>
                    </div>
                    @endif
                    <div class="detail-row total">
                        <span class="label">الإجمالي:</span>
                        <span class="value">{{ number_format($transaction->fat_net, 2) }} ريال</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">المدفوع:</span>
                        <span class="value">{{ number_format($transaction->paid_from_client ?? $transaction->fat_net, 2) }} ريال</span>
                    </div>
                    @if(($transaction->paid_from_client ?? $transaction->fat_net) > $transaction->fat_net)
                    <div class="detail-row">
                        <span class="label">المتبقي:</span>
                        <span class="value text-info">{{ number_format(($transaction->paid_from_client ?? $transaction->fat_net) - $transaction->fat_net, 2) }} ريال</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- تفاصيل الأصناف -->
        <div class="items-section">
            <div class="section-header">
                <h3><i class="fas fa-shopping-cart"></i> تفاصيل الأصناف</h3>
                <span class="items-count">{{ $transaction->operationItems->count() }} صنف</span>
            </div>
            
            <div class="items-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الكمية</th>
                            <th>الوحدة</th>
                            <th>السعر</th>
                            <th>الخصم</th>
                            <th>المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->operationItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="item-info">
                                    <span class="item-name">{{ $item->item->name ?? 'غير محدد' }}</span>
                                    @if($item->item && $item->item->code)
                                    <small class="item-code">كود: {{ $item->item->code }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="quantity">{{ number_format($item->qty_out, 0) }}</span>
                            </td>
                            <td>
                                <span class="unit">{{ $item->unit->name ?? 'قطعة' }}</span>
                            </td>
                            <td>
                                <span class="price">{{ number_format($item->item_price, 2) }}</span>
                            </td>
                            <td>
                                <span class="discount">{{ number_format($item->item_discount ?? 0, 2) }}</span>
                            </td>
                            <td>
                                <span class="total">{{ number_format($item->detail_value, 2) }} ريال</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ملاحظات -->
        @if($transaction->info)
        <div class="notes-section">
            <div class="section-header">
                <h3><i class="fas fa-sticky-note"></i> ملاحظات</h3>
            </div>
            <div class="notes-content">
                <p>{{ $transaction->info }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .pos-show-container {
        font-family: 'Cairo', sans-serif;
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        direction: rtl;
    }

    .transaction-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
    }

    .transaction-info h1 {
        margin: 0 0 1rem 0;
        color: #2c3e50;
        font-size: 2rem;
        font-weight: 700;
    }

    .transaction-meta {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .invoice-number {
        background: linear-gradient(135deg, #3498db 0%, #74b9ff 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 1rem;
    }

    .transaction-date {
        color: #7f8c8d;
        font-weight: 500;
    }

    .transaction-status {
        color: #27ae60;
        font-weight: 600;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .transaction-details {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .detail-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .detail-card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 1.5rem;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-body {
        padding: 2rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-row.total {
        background: #f8f9fa;
        margin: 1rem -2rem -2rem;
        padding: 1.5rem 2rem;
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .label {
        font-weight: 600;
        color: #7f8c8d;
    }

    .value {
        font-weight: 500;
        color: #2c3e50;
    }

    .items-section, .notes-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .section-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .items-count {
        background: #3498db;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .items-table {
        overflow-x: auto;
    }

    .items-table table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .items-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: right;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #e9ecef;
        font-size: 0.9rem;
    }

    .items-table td {
        padding: 1rem;
        border-bottom: 1px solid #f8f9fa;
        vertical-align: middle;
    }

    .items-table tr:hover {
        background: #f8f9fa;
    }

    .items-table tr:last-child td {
        border-bottom: none;
    }

    .item-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .item-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .item-code {
        color: #7f8c8d;
        font-size: 0.8rem;
    }

    .quantity {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-weight: 600;
        display: inline-block;
        min-width: 40px;
        text-align: center;
    }

    .unit {
        background: #f3e5f5;
        color: #7b1fa2;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .price, .discount, .total {
        font-weight: 600;
        text-align: left;
    }

    .price {
        color: #2196f3;
    }

    .discount {
        color: #f44336;
    }

    .total {
        color: #4caf50;
        font-size: 1.1rem;
    }

    .notes-content {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 10px;
        padding: 1.5rem;
        color: #856404;
        line-height: 1.6;
    }

    .text-success {
        color: #27ae60 !important;
    }

    .text-danger {
        color: #e74c3c !important;
    }

    .text-info {
        color: #3498db !important;
    }

    @media (max-width: 768px) {
        .pos-show-container {
            padding: 1rem;
        }
        
        .header-content {
            flex-direction: column;
            gap: 1rem;
        }
        
        .transaction-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .details-grid {
            grid-template-columns: 1fr;
        }
        
        .items-table {
            font-size: 0.9rem;
        }
        
        .items-table th,
        .items-table td {
            padding: 0.75rem 0.5rem;
        }
        
        .detail-card,
        .items-section,
        .notes-section {
            padding: 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .detail-row.total {
            margin: 1rem -1.5rem -1.5rem;
            padding: 1.5rem;
        }
    }
</style>

<script>
    // دعم اختصارات لوحة المفاتيح
    document.addEventListener('keydown', function(e) {
        // P للطباعة
        if (e.key === 'p' || e.key === 'P') {
            e.preventDefault();
            window.open('{{ route("pos.print", $transaction->id) }}', '_blank');
        }
        
        // ESC للعودة
        if (e.key === 'Escape') {
            e.preventDefault();
            window.location.href = '{{ route("pos.index") }}';
        }
    });

    // تحسين تجربة المستخدم
    document.addEventListener('DOMContentLoaded', function() {
        // إضافة تأثيرات حركية
        const cards = document.querySelectorAll('.detail-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('animate-fade-in');
        });
    });
</script>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
</style>
@endsection
