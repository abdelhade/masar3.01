@extends('pos::layouts.master')

@section('content')
<div class="pos-dashboard">
    <div class="dashboard-header">
        <div class="header-navigation">
            <a href="{{ route('dashboard') }}" class="back-to-main-btn">
                <i class="fas fa-home"></i>
                <span>العودة للصفحة الرئيسية</span>
            </a>
        </div>
        
        <div class="welcome-section">
            <h1>مرحباً بك في نظام نقاط البيع</h1>
            <p>إدارة المبيعات بسهولة وسرعة</p>
        </div>
        
        <div class="quick-stats">
            <div class="stat-card sales">
                <div class="stat-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="stat-info">
                    <h3>مبيعات اليوم</h3>
                    <span class="stat-value">{{ number_format($todayStats['total_sales'] ?? 0) }} ريال</span>
                </div>
            </div>
            
            <div class="stat-card transactions">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3>المعاملات</h3>
                    <span class="stat-value">{{ $todayStats['transactions_count'] ?? 0 }}</span>
                </div>
            </div>
            
            <div class="stat-card items">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>أصناف مباعة</h3>
                    <span class="stat-value">{{ $todayStats['items_sold'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-actions">
        <div class="action-cards">
            <a href="{{ route('pos.create') }}" class="action-card primary">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-info">
                    <h3>معاملة جديدة</h3>
                    <p>ابدأ معاملة بيع جديدة</p>
                </div>
                <div class="action-shortcut">F1</div>
            </a>

            <a href="{{ route('pos.reports') }}" class="action-card secondary">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-info">
                    <h3>التقارير</h3>
                    <p>عرض تقارير المبيعات</p>
                </div>
                <div class="action-shortcut">F2</div>
            </a>

            <a href="{{ route('invoices.index') }}" class="action-card info">
                <div class="action-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="action-info">
                    <h3>الفواتير</h3>
                    <p>إدارة جميع الفواتير</p>
                </div>
                <div class="action-shortcut">F3</div>
            </a>
        </div>
    </div>

    @if(count($recentTransactions) > 0)
    <div class="recent-transactions">
        <div class="section-header">
            <h2>المعاملات الأخيرة</h2>
            <span class="section-subtitle">آخر {{ count($recentTransactions) }} معاملات اليوم</span>
        </div>
        
        <div class="transactions-table">
            <table>
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>المبلغ</th>
                        <th>الوقت</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $transaction)
                    <tr>
                        <td>
                            <span class="invoice-number">{{ $transaction->pro_id }}</span>
                        </td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name">{{ $transaction->acc1Head->aname ?? 'عميل نقدي' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="amount">{{ number_format($transaction->fat_net) }} ريال</span>
                        </td>
                        <td>
                            <span class="time">{{ $transaction->created_at->format('H:i') }}</span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('pos.show', $transaction->id) }}" class="btn-action view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('pos.print', $transaction->id) }}" class="btn-action print" title="طباعة" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<style>
    .pos-dashboard {
        font-family: 'Cairo', sans-serif;
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        direction: rtl;
    }

    .dashboard-header {
        margin-bottom: 3rem;
    }

    .welcome-section {
        text-align: center;
        margin-bottom: 2rem;
        color: white;
    }

    .welcome-section h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .welcome-section p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
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
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-card.sales .stat-icon {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .stat-card.transactions .stat-icon {
        background: linear-gradient(135deg, #3498db 0%, #74b9ff 100%);
    }

    .stat-card.items .stat-icon {
        background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    }

    .stat-info h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1rem;
        font-weight: 600;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .dashboard-actions {
        margin-bottom: 3rem;
    }

    .action-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .action-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        text-decoration: none;
        color: inherit;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(135deg, #3498db 0%, #74b9ff 100%);
    }

    .action-card.primary::before {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .action-card.secondary::before {
        background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    }

    .action-card.info::before {
        background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    }

    .action-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        background: linear-gradient(135deg, #3498db 0%, #74b9ff 100%);
    }

    .action-card.primary .action-icon {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .action-card.secondary .action-icon {
        background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    }

    .action-card.info .action-icon {
        background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    }

    .action-info h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1.3rem;
        font-weight: 700;
    }

    .action-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 1rem;
    }

    .action-shortcut {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(0, 0, 0, 0.1);
        color: #7f8c8d;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .recent-transactions {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .section-header h2 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .section-subtitle {
        color: #7f8c8d;
        font-size: 1rem;
    }

    .transactions-table {
        overflow-x: auto;
    }

    .transactions-table table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .transactions-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: right;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #e9ecef;
    }

    .transactions-table td {
        padding: 1rem;
        border-bottom: 1px solid #f8f9fa;
    }

    .transactions-table tr:hover {
        background: #f8f9fa;
    }

    .invoice-number {
        background: #3498db;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .customer-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .amount {
        font-weight: 700;
        color: #27ae60;
        font-size: 1.1rem;
    }

    .time {
        color: #7f8c8d;
        font-weight: 600;
    }

    .actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-action.view {
        background: #3498db;
        color: white;
    }

    .btn-action.print {
        background: #27ae60;
        color: white;
    }

    .btn-action:hover {
        transform: scale(1.1);
        text-decoration: none;
        color: white;
    }

    @media (max-width: 768px) {
        .pos-dashboard {
            padding: 1rem;
        }
        
        .welcome-section h1 {
            font-size: 2rem;
        }
        
        .quick-stats {
            grid-template-columns: 1fr;
        }
        
        .action-cards {
            grid-template-columns: 1fr;
        }
        
        .transactions-table {
            font-size: 0.9rem;
        }
        
        .stat-card, .action-card {
            padding: 1.5rem;
        }
    }
</style>

<script>
    // دعم اختصارات لوحة المفاتيح
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault();
            window.location.href = '{{ route("pos.create") }}';
        }
        
        if (e.key === 'F2') {
            e.preventDefault();
            window.location.href = '{{ route("pos.reports") }}';
        }
        
        if (e.key === 'F3') {
            e.preventDefault();
            window.location.href = '{{ route("invoices.index") }}';
        }
    });

    // تحديث الإحصائيات كل 5 دقائق
    setInterval(function() {
        location.reload();
    }, 300000);
</script>
@endsection