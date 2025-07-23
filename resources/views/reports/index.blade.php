@extends('admin.dashboard')
@section('content')

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --light-bg: #f8f9fa;
            --card-border: #eaeaea;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #333;
        }
        
        .reports-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .reports-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
        }
        
        .report-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            background-color: white;
            overflow: hidden;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .report-card .card-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .report-card .card-header i {
            margin-left: 10px;
            font-size: 1.2rem;
        }
        
        .report-card .card-body {
            padding: 20px;
        }
        
        .report-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #444;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        .report-link:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--primary-color);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .report-link:hover {
            background-color: rgba(67, 97, 238, 0.08);
            transform: translateX(5px);
            color: var(--primary-color);
        }
        
        .report-link:hover:before {
            opacity: 1;
        }
        
        .report-link i {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 6px;
            margin-left: 12px;
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .search-box {
            max-width: 400px;
            margin: 0 auto 30px;
        }
        
        .category-badge {
            position: absolute;
            top: -8px;
            left: -8px;
            background-color: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .stats-badge {
            background-color: rgba(76, 201, 240, 0.15);
            color: #4cc9f0;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .reports-header {
                padding: 15px;
            }
            
            .report-card .card-header {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="reports-container">
        <div class="reports-header text-center">
            <p class="display-4 fw-bold mb-3"><i class="fas fa-chart-bar me-2"></i> نظام التقارير المتكامل</p>
        </div>
        
        <div class="search-box">
            <div class="input-group mb-3">
                <input type="text" class="form-control frst form-control-lg" placeholder="{{ __('ابحث عن تقرير...') }}" aria-label="{{ __('بحث التقارير') }}" id="report-filter" onkeyup="filterReports()">

                <script>
                    function filterReports() {
                        var input = document.getElementById('report-filter');
                        var filter = input.value.toLowerCase();
                        var rows = document.querySelectorAll('.reports-row .col-lg-4, .reports-row .col-md-6');
                        rows.forEach(function(row) {
                            var text = row.textContent || row.innerText;
                            if (text.toLowerCase().indexOf(filter) > -1) {
                                row.style.display = "";
                            } else {
                                row.style.display = "none";
                            }
                        });
                    }
                </script>
                
                <button class="btn btn-primary" type="button"><i class="fas fa-search"></i></button>
            </div>
        </div>
        


        <div class="row g-4 reports-row">
            <!-- التقارير العامة -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i>
                        <span class="card-title">التقارير العامة</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.overall') }}" class="report-link">
                            <i class="fas fa-file-alt"></i>
                            <span>محلل العمل اليومي</span>
                        </a>
                        <a href="{{ route('journal-summery') }}" class="report-link">
                            <i class="fas fa-book"></i>
                            <span>اليومية العامة</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير الحسابات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-book"></i>
                        <span class="card-title">تقارير الحسابات</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('accounts.tree') }}" class="report-link">
                            <i class="fas fa-tree"></i>
                            <span>شجرة الحسابات</span>
                        </a>
                        <a href="{{ route('reports.general-balance-sheet') }}" class="report-link">
                            <i class="fas fa-balance-scale"></i>
                            <span>الميزانية العمومية</span>
                        </a>
                        <a href="{{ route('reports.general-account-statement') }}" class="report-link">
                            <i class="fas fa-file-invoice"></i>
                            <span>كشف حساب حساب</span>
                        </a>
                        <a href="{{ route('reports.general-account-balances') }}" class="report-link">
                            <i class="fas fa-calculator"></i>
                            <span>ميزان الحسابات</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير المخزون -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-boxes"></i>
                        <span class="card-title">تقارير المخزون</span>
                        <span class="stats-badge ms-2">جديدة</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-inventory-report') }}" class="report-link">
                            <i class="fas fa-warehouse"></i>
                            <span>تقرير المخزون العام</span>
                        </a>
                        <a href="{{ route('reports.general-inventory-daily-movement-report') }}" class="report-link">
                            <i class="fas fa-exchange-alt"></i>
                            <span>تقرير حركة المخزون اليومية</span>
                        </a>
                        <a href="{{ route('reports.general-inventory-stocktaking-report') }}" class="report-link">
                            <i class="fas fa-clipboard-check"></i>
                            <span>تقرير جرد المخزون</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير الحسابات (جديدة) -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="card-title">تقارير الحسابات</span>
                        <span class="stats-badge ms-2">جديدة</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-accounts-report') }}" class="report-link">
                            <i class="fas fa-file-contract"></i>
                            <span>تقرير الحسابات العام</span>
                        </a>
                        <a href="{{ route('reports.general-account-statement-report') }}" class="report-link">
                            <i class="fas fa-receipt"></i>
                            <span>تقرير كشف حساب عام</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير النقدية والبنوك -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="card-title">تقارير النقدية والبنوك</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-cash-bank-report') }}" class="report-link">
                            <i class="fas fa-landmark"></i>
                            <span>تقرير النقدية والبنوك</span>
                        </a>
                        <a href="{{ route('reports.general-cashbox-movement-report') }}" class="report-link">
                            <i class="fas fa-cash-register"></i>
                            <span>تقرير حركة الصندوق</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير المبيعات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="card-title">تقارير المبيعات</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-sales-daily-report') }}" class="report-link">
                            <i class="fas fa-calendar-day"></i>
                            <span>تقرير المبيعات اليومية</span>
                        </a>
                        <a href="{{ route('reports.general-sales-total-report') }}" class="report-link">
                            <i class="fas fa-chart-line"></i>
                            <span>تقرير المبيعات اجماليات</span>
                        </a>
                        <a href="{{ route('reports.general-sales-items-report') }}" class="report-link">
                            <i class="fas fa-box-open"></i>
                            <span>تقرير المبيعات اصناف</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير المشتريات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-basket"></i>
                        <span class="card-title">تقارير المشتريات</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-purchases-daily-report') }}" class="report-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>تقرير المشتريات اليومية</span>
                        </a>
                        <a href="{{ route('reports.general-purchases-total-report') }}" class="report-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>تقرير المشتريات اجماليات</span>
                        </a>
                        <a href="{{ route('reports.general-purchases-items-report') }}" class="report-link">
                            <i class="fas fa-boxes"></i>
                            <span>تقرير المشتريات اصناف</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير العملاء -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i>
                        <span class="card-title">تقارير العملاء</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-customers-daily-report') }}" class="report-link">
                            <i class="fas fa-user-clock"></i>
                            <span>تقرير العملاء اليومية</span>
                        </a>
                        <a href="{{ route('reports.general-customers-total-report') }}" class="report-link">
                            <i class="fas fa-user-friends"></i>
                            <span>تقرير العملاء اجماليات</span>
                        </a>
                        <a href="{{ route('reports.general-customers-items-report') }}" class="report-link">
                            <i class="fas fa-user-tag"></i>
                            <span>تقرير العملاء اصناف</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير الموردين -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-truck-loading"></i>
                        <span class="card-title">تقارير الموردين</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-suppliers-daily-report') }}" class="report-link">
                            <i class="fas fa-truck-moving"></i>
                            <span>تقرير الموردين اليومية</span>
                        </a>
                        <a href="{{ route('reports.general-suppliers-total-report') }}" class="report-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>تقرير الموردين اجماليات</span>
                        </a>
                        <a href="{{ route('reports.general-suppliers-items-report') }}" class="report-link">
                            <i class="fas fa-pallet"></i>
                            <span>تقرير الموردين اصناف</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير المصروفات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="card-title">تقارير المصروفات</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-expenses-report') }}" class="report-link">
                            <i class="fas fa-list"></i>
                            <span>قائمة الاصناف مع الارصدة</span>
                        </a>
                        <a href="{{ route('reports.general-expenses-daily-report') }}" class="report-link">
                            <i class="fas fa-file-invoice"></i>
                            <span>كشف حساب مصروف</span>
                        </a>
                        <a href="{{ route('reports.expenses-balance-report') }}" class="report-link">
                            <i class="fas fa-balance-scale-right"></i>
                            <span>ميزان المصروفات</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير مراكز التكلفة -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-calculator"></i>
                        <span class="card-title">تقارير مراكز التكلفة</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-cost-centers-list') }}" class="report-link">
                            <i class="fas fa-list-ol"></i>
                            <span>قائمة مراكز التكلفة</span>
                        </a>
                        <a href="{{ route('reports.general-cost-center-account-statement') }}" class="report-link">
                            <i class="fas fa-file-alt"></i>
                            <span>كشف حساب مركز التكلفة</span>
                        </a>
                        <a href="{{ route('reports.general-account-statement-with-cost-center') }}" class="report-link">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>كشف حساب عام مع مركز تكلفة</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- تقارير الاصناف -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-cubes"></i>
                        <span class="card-title">تقارير الاصناف</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.get-items-max-min-quantity') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>مراقبة كميات الأصناف</span>
                        </a>
                        <a href="#" class="report-link">
                            <i class="fas fa-chart-bar"></i>
                            <span>تقرير مبيعات الأصناف (قريبًا)</span>
                        </a>
                        <a href="#" class="report-link">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>الأصناف منتهية الصلاحية (قريبًا)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
       
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation for cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.report-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });
    </script>
@endsection