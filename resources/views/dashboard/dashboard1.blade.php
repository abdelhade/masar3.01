@extends('dashboard.layout')

@section('title', 'لوحة تحكم المبيعات والمخزون - Dashboard 1')

@section('content')
<div class="container-fluid">
    <!-- رأس الصفحة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                    لوحة تحكم المبيعات والمخزون
                </h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        طباعة التقرير
                    </button>
                    <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i>
                        تصدير Excel
                    </button>
                    <button class="btn btn-info btn-sm" onclick="refreshAllData()">
                        <i class="fas fa-sync-alt me-1"></i>
                        تحديث البيانات
                    </button>
                </div>
            </div>
            <p class="text-muted mt-2">مراقبة وتحليل أداء المبيعات والمخزون في الوقت الفعلي</p>
        </div>
    </div>

    <!-- ملخص المبيعات -->
    @livewire('dashboard.sales-summary')

    <!-- الرسوم البيانية -->
    <div class="row mb-4">
        <!-- تطور المبيعات -->
        <div class="col-xl-8 col-lg-7">
            @livewire('dashboard.sales-trends-chart')
        </div>

        <!-- أكثر المنتجات مبيعاً -->
        <div class="col-xl-4 col-lg-5">
            @livewire('dashboard.top-selling-items-chart')
        </div>
    </div>

    <!-- مؤشرات الأداء الإضافية -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">مؤشرات الأداء الرئيسية (KPIs)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="kpi-item">
                                <div class="kpi-value" id="conversionRate">0%</div>
                                <div class="kpi-label">معدل التحويل</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="kpi-item">
                                <div class="kpi-value" id="avgOrderValue">0 ريال</div>
                                <div class="kpi-label">متوسط قيمة الطلب</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="kpi-item">
                                <div class="kpi-value" id="customerSatisfaction">0%</div>
                                <div class="kpi-label">رضا العملاء</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="kpi-item">
                                <div class="kpi-value" id="inventoryTurnover">0</div>
                                <div class="kpi-label">دوران المخزون</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- معلومات إضافية -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">أفضل أيام المبيعات</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>اليوم</th>
                                    <th>المبيعات</th>
                                    <th>النسبة</th>
                                </tr>
                            </thead>
                            <tbody id="bestDaysTable">
                                <tr>
                                    <td>الأحد</td>
                                    <td>45,000 ريال</td>
                                    <td>25%</td>
                                </tr>
                                <tr>
                                    <td>الاثنين</td>
                                    <td>38,000 ريال</td>
                                    <td>21%</td>
                                </tr>
                                <tr>
                                    <td>الثلاثاء</td>
                                    <td>32,000 ريال</td>
                                    <td>18%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">تنبيهات المخزون</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>لابتوب HP:</strong> المخزون منخفض (5 قطع متبقية)
                    </div>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>هاتف iPhone:</strong> المخزون متوسط (15 قطعة متبقية)
                    </div>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>سماعات لاسلكية:</strong> المخزون جيد (45 قطعة متبقية)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- نافذة منبثقة للتفاصيل -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">تفاصيل إضافية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- سيتم تحميل المحتوى هنا -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .border-right-primary {
        border-right: 0.25rem solid #4e73df !important;
    }
    
    .border-right-success {
        border-right: 0.25rem solid #1cc88a !important;
    }
    
    .border-right-info {
        border-right: 0.25rem solid #36b9cc !important;
    }
    
    .border-right-warning {
        border-right: 0.25rem solid #f6c23e !important;
    }
    
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    
    .text-gray-300 {
        color: #dddfeb !important;
    }
    
    .kpi-item {
        padding: 1rem;
        border-radius: 0.35rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        margin-bottom: 1rem;
    }
    
    .kpi-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .kpi-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .alert {
        border: none;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    @media print {
        .btn, .modal {
            display: none !important;
        }
        
        .container-fluid {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحميل البيانات التجريبية للمؤشرات
    loadKPIData();
});

function loadKPIData() {
    // تحميل مؤشرات الأداء
    document.getElementById('conversionRate').textContent = '3.2%';
    document.getElementById('avgOrderValue').textContent = '2,150 ريال';
    document.getElementById('customerSatisfaction').textContent = '94%';
    document.getElementById('inventoryTurnover').textContent = '4.5';
}

function refreshAllData() {
    // تحديث جميع مكونات Livewire
    Livewire.dispatch('refresh-data');
    
    // إظهار رسالة نجاح
    showNotification('تم تحديث جميع البيانات بنجاح', 'success');
}

function exportToExcel() {
    // تنفيذ تصدير البيانات إلى Excel
    showNotification('سيتم إضافة ميزة التصدير إلى Excel قريباً', 'info');
}

function showNotification(message, type) {
    // إنشاء إشعار
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // إزالة الإشعار بعد 3 ثواني
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function showDetails(itemId, itemName) {
    // عرض تفاصيل إضافية للمنتج
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    document.getElementById('detailsModalLabel').textContent = `تفاصيل: ${itemName}`;
    document.getElementById('detailsModalBody').innerHTML = `
        <div class="text-center">
            <p>جاري تحميل التفاصيل...</p>
        </div>
    `;
    modal.show();
}
</script>
@endpush
