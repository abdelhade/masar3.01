@extends('pos::layouts.master')

@section('content')
<div class="pos-reports-container">
    <div class="reports-header">
        <div class="header-content">
            <div class="reports-title">
                <h1><i class="fas fa-chart-bar"></i> تقارير نقاط البيع</h1>
                <p>تحليل شامل لأداء المبيعات والمعاملات</p>
            </div>
            
            <div class="header-actions">
                <button class="btn-pos btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    طباعة التقرير
                </button>
                
                <a href="{{ route('pos.index') }}" class="btn-pos btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    العودة لنظام نقاط البيع
                </a>
            </div>
        </div>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="quick-stats">
        <div class="stat-card sales-today">
            <div class="stat-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <div class="stat-content">
                <h3>مبيعات اليوم</h3>
                <div class="stat-value">{{ number_format($todayStats['total_sales']) }}</div>
                <div class="stat-unit">ريال</div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-arrow-up text-success"></i>
                <span>نشط</span>
            </div>
        </div>

        <div class="stat-card transactions-today">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <h3>معاملات اليوم</h3>
                <div class="stat-value">{{ $todayStats['transactions_count'] }}</div>
                <div class="stat-unit">معاملة</div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-chart-line text-info"></i>
                <span>إجمالي</span>
            </div>
        </div>

        <div class="stat-card items-sold">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <h3>أصناف مباعة</h3>
                <div class="stat-value">{{ number_format($todayStats['items_sold']) }}</div>
                <div class="stat-unit">قطعة</div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-cubes text-warning"></i>
                <span>اليوم</span>
            </div>
        </div>

        <div class="stat-card avg-transaction">
            <div class="stat-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-content">
                <h3>متوسط المعاملة</h3>
                <div class="stat-value">
                    {{ number_format($additionalStats['avg_transaction_value'] ?? 0, 2) }}
                </div>
                <div class="stat-unit">ريال</div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-clock text-info"></i>
                <span>
                    @if($additionalStats['last_transaction_time'] ?? null)
                        {{ \Carbon\Carbon::parse($additionalStats['last_transaction_time'])->diffForHumans() }}
                    @else
                        لا توجد معاملات
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- تفاصيل التقارير -->
    <div class="reports-content">
        <!-- تقرير المبيعات اليومية -->
        <div class="report-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-day"></i> تقرير المبيعات اليومية</h2>
                <span class="report-date">{{ today()->format('Y-m-d') }}</span>
            </div>
            
            <div class="report-grid">
                <div class="report-card">
                    <div class="card-header">
                        <h4>ملخص المبيعات</h4>
                    </div>
                    <div class="card-content">
                        <div class="summary-row">
                            <span>إجمالي المبيعات:</span>
                            <span class="amount">{{ number_format($todayStats['total_sales'], 2) }} ريال</span>
                        </div>
                        <div class="summary-row">
                            <span>عدد المعاملات:</span>
                            <span class="count">{{ $todayStats['transactions_count'] }} معاملة</span>
                        </div>
                        <div class="summary-row">
                            <span>أصناف مباعة:</span>
                            <span class="count">{{ number_format($todayStats['items_sold']) }} قطعة</span>
                        </div>
                        <div class="summary-row total">
                            <span>متوسط قيمة المعاملة:</span>
                            <span class="amount">
                                {{ $todayStats['transactions_count'] > 0 ? number_format($todayStats['total_sales'] / $todayStats['transactions_count'], 2) : '0.00' }} ريال
                            </span>
                        </div>
                    </div>
                </div>

                <div class="report-card">
                    <div class="card-header">
                        <h4>أداء الساعات</h4>
                    </div>
                    <div class="card-content">
                        <div class="hours-chart">
                            <div class="chart-placeholder">
                                <i class="fas fa-clock fa-3x text-muted"></i>
                                <p>رسم بياني لأداء المبيعات خلال ساعات اليوم</p>
                                <small class="text-muted">سيتم تطوير هذه الميزة قريباً</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- أكثر الأصناف مبيعاً -->
        <div class="report-section">
            <div class="section-header">
                <h2><i class="fas fa-star"></i> أكثر الأصناف مبيعاً</h2>
                <span class="report-period">اليوم</span>
            </div>
            
            <div class="top-items-table">
                <table>
                    <thead>
                        <tr>
                            <th>الترتيب</th>
                            <th>الصنف</th>
                            <th>الكمية المباعة</th>
                            <th>إجمالي المبيعات</th>
                            <th>متوسط السعر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- سيتم ملء البيانات هنا من قاعدة البيانات -->
                        <tr>
                            <td><span class="rank gold">1</span></td>
                            <td>
                                <div class="item-info">
                                    <span class="item-name">صنف تجريبي</span>
                                    <small class="item-code">كود: DEMO001</small>
                                </div>
                            </td>
                            <td><span class="quantity">25 قطعة</span></td>
                            <td><span class="sales">1,250.00 ريال</span></td>
                            <td><span class="avg-price">50.00 ريال</span></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="fas fa-info-circle"></i>
                                لا توجد بيانات كافية لعرض التقرير. ابدأ بإجراء بعض المعاملات.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- طرق الدفع -->
        <div class="report-section">
            <div class="section-header">
                <h2><i class="fas fa-credit-card"></i> تحليل طرق الدفع</h2>
                <span class="report-period">اليوم</span>
            </div>
            
            <div class="payment-methods-grid">
                <div class="payment-card cash">
                    <div class="payment-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="payment-info">
                        <h4>نقدي كامل</h4>
                        <div class="payment-amount">{{ number_format($paymentAnalysis['cash_total'] ?? 0, 2) }} ريال</div>
                        <div class="payment-percentage">
                            {{ $todayStats['total_sales'] > 0 ? round(($paymentAnalysis['cash_total'] ?? 0) / $todayStats['total_sales'] * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>

                <div class="payment-card card">
                    <div class="payment-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="payment-info">
                        <h4>دفع جزئي</h4>
                        <div class="payment-amount">{{ number_format($paymentAnalysis['partial_payment'] ?? 0, 2) }} ريال</div>
                        <div class="payment-percentage">
                            {{ $todayStats['total_sales'] > 0 ? round(($paymentAnalysis['partial_payment'] ?? 0) / $todayStats['total_sales'] * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>

                <div class="payment-card mixed">
                    <div class="payment-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="payment-info">
                        <h4>متوسط المعاملة</h4>
                        <div class="payment-amount">{{ number_format($additionalStats['avg_transaction_value'] ?? 0, 2) }} ريال</div>
                        <div class="payment-percentage">
                            {{ $todayStats['transactions_count'] }} معاملة
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- معلومات إضافية -->
    <div class="report-footer">
        <div class="footer-info">
            <div class="info-item">
                <i class="fas fa-calendar"></i>
                <span>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</span>
            </div>
            <div class="info-item">
                <i class="fas fa-user"></i>
                <span>المستخدم: {{ auth()->user()->name }}</span>
            </div>
            <div class="info-item">
                <i class="fas fa-store"></i>
                <span>نقطة البيع: {{ config('app.name') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    .pos-reports-container {
        font-family: 'Cairo', sans-serif;
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        direction: rtl;
    }

    .reports-header {
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
        align-items: center;
    }

    .reports-title h1 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 2.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .reports-title p {
        margin: 0;
        color: #7f8c8d;
        font-size: 1.1rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
    }

    .sales-today .stat-icon {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .transactions-today .stat-icon {
        background: linear-gradient(135deg, #3498db 0%, #74b9ff 100%);
    }

    .items-sold .stat-icon {
        background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    }

    .avg-transaction .stat-icon {
        background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    }

    .stat-content {
        flex: 1;
    }

    .stat-content h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1rem;
        font-weight: 600;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }

    .stat-unit {
        font-size: 0.9rem;
        color: #7f8c8d;
        font-weight: 500;
    }

    .stat-trend {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .reports-content {
        display: flex;
        flex-direction: column;
        gap: 3rem;
    }

    .report-section {
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

    .section-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.6rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .report-date, .report-period {
        background: #3498db;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
    }

    .report-card {
        background: #f8f9fa;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 1.5rem;
    }

    .card-header h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .card-content {
        padding: 2rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row.total {
        background: white;
        margin: 1rem -2rem -2rem;
        padding: 1.5rem 2rem;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .amount {
        color: #27ae60;
        font-weight: 700;
    }

    .count {
        color: #3498db;
        font-weight: 600;
    }

    .chart-placeholder {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
    }

    .chart-placeholder i {
        margin-bottom: 1rem;
    }

    .top-items-table {
        overflow-x: auto;
    }

    .top-items-table table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .top-items-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: right;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #e9ecef;
    }

    .top-items-table td {
        padding: 1rem;
        border-bottom: 1px solid #f8f9fa;
    }

    .top-items-table tr:hover {
        background: #f8f9fa;
    }

    .rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        color: white;
        font-weight: 700;
    }

    .rank.gold {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
        color: #3498db;
        font-weight: 600;
    }

    .sales {
        color: #27ae60;
        font-weight: 700;
    }

    .avg-price {
        color: #8e44ad;
        font-weight: 600;
    }

    .no-data {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
        font-style: italic;
    }

    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }

    .payment-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }

    .payment-card:hover {
        transform: translateY(-5px);
    }

    .payment-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin: 0 auto 1rem;
    }

    .payment-card.cash .payment-icon {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .payment-card.card .payment-icon {
        background: linear-gradient(135deg, #3498db 0%, #74b9ff 100%);
    }

    .payment-card.mixed .payment-icon {
        background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    }

    .payment-info h4 {
        margin: 0 0 1rem 0;
        color: #2c3e50;
        font-weight: 600;
    }

    .payment-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .payment-percentage {
        color: #7f8c8d;
        font-weight: 600;
    }

    .report-footer {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-top: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .footer-info {
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .info-item i {
        color: #3498db;
    }

    @media (max-width: 768px) {
        .pos-reports-container {
            padding: 1rem;
        }
        
        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .reports-title h1 {
            font-size: 1.8rem;
        }
        
        .quick-stats {
            grid-template-columns: 1fr;
        }
        
        .report-grid {
            grid-template-columns: 1fr;
        }
        
        .payment-methods-grid {
            grid-template-columns: 1fr;
        }
        
        .footer-info {
            flex-direction: column;
            text-align: center;
        }
        
        .stat-card {
            padding: 1.5rem;
        }
        
        .report-section {
            padding: 1.5rem;
        }
    }

    @media print {
        .pos-reports-container {
            background: white !important;
            padding: 1rem;
        }
        
        .no-print,
        .header-actions {
            display: none !important;
        }
        
        .stat-card,
        .report-section,
        .report-footer {
            background: white !important;
            box-shadow: none !important;
            border: 1px solid #ddd;
            break-inside: avoid;
        }
    }
</style>

<script>
    // دعم اختصارات لوحة المفاتيح
    document.addEventListener('keydown', function(e) {
        // Ctrl+P للطباعة
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
        
        // ESC للعودة
        if (e.key === 'Escape') {
            e.preventDefault();
            window.location.href = '{{ route("pos.index") }}';
        }
    });

    // تحديث التقرير كل 5 دقائق
    setInterval(function() {
        location.reload();
    }, 300000);

    // تأثيرات حركية
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card');
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
