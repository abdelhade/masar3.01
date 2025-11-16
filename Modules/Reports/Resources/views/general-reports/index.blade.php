@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

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
            position: relative;
        }

        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .reports-container {
            position: relative;
            z-index: 2;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .reports-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: var(--primary-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .reports-header .display-4 {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0;
        }

        .search-box {
            max-width: 500px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 2;
        }

        .search-box .input-group {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .search-box input {
            border: none;
            padding: 15px 20px;
            font-size: 1.1rem;
            background: transparent;
        }

        .search-box input:focus {
            outline: none;
            box-shadow: none;
        }

        .search-box .btn {
            border: none;
            background: var(--primary-color);
            padding: 0 25px;
        }

        .report-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .report-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .report-card .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            padding: 18px 20px;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .report-card .card-header i {
            margin-left: 12px;
            font-size: 1.3rem;
        }

        .report-card .card-body {
            padding: 20px;
        }

        .report-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            color: #444;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.5);
        }

        .report-link:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .report-link:hover {
            background: rgba(67, 97, 238, 0.12);
            transform: translateX(8px);
            color: var(--primary-color);
        }

        .report-link:hover:before {
            opacity: 1;
        }

        .report-link i {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.15), rgba(63, 55, 201, 0.15));
            border-radius: 8px;
            margin-left: 12px;
            color: var(--primary-color);
            font-size: 1rem;
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 0;
        }

        .reports-row {
            position: relative;
            z-index: 2;
        }

        @media (max-width: 768px) {
            .reports-header {
                padding: 20px 15px;
            }

            .reports-header .display-4 {
                font-size: 1.5rem;
            }

            .report-card .card-header {
                padding: 15px;
            }

            .search-box {
                margin-bottom: 20px;
            }
        }
    </style>

    <!-- Particles JS Container -->
    <div id="particles-js"></div>

    <div class="reports-container">
        <div class="reports-header">
            <p class="display-4 fw-bold mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                {{ __('reports.integrated_reports_system') }}
            </p>
        </div>

        <div class="search-box">
            <div class="input-group mb-3">
                <input type="text" class="form-control frst form-control-lg"
                    placeholder="{{ __('reports.search_report') }}"
                    aria-label="{{ __('reports.search_reports') }}"
                    id="report-filter"
                    onkeyup="filterReports()">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="row g-4 reports-row">
            <!-- التقارير العامة -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i>
                        <span class="card-title">{{ __('reports.general_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.overall') }}" class="report-link">
                            <i class="fas fa-file-alt"></i>
                            <span>{{ __('reports.daily_activity_analyzer') }}</span>
                        </a>
                        <a href="{{ route('reports.journal-summery') }}" class="report-link">
                            <i class="fas fa-book"></i>
                            <span>{{ __('reports.general_journal') }}</span>
                        </a>
                        <a href="{{ route('reports.general-journal-details') }}" class="report-link">
                            <i class="fas fa-file-invoice"></i>
                            <span>{{ __('reports.general_account_statement') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير الحسابات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-book"></i>
                        <span class="card-title">{{ __('reports.accounts_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.accounts-tree') }}" class="report-link">
                            <i class="fas fa-tree"></i>
                            <span>{{ __('reports.accounts_tree') }}</span>
                        </a>
                        <a href="{{ route('reports.general-balance-sheet') }}" class="report-link">
                            <i class="fas fa-balance-scale"></i>
                            <span>{{ __('reports.balance_sheet') }}</span>
                        </a>
                        <a href="{{ route('reports.general-profit-loss-report') }}" class="report-link">
                            <i class="fas fa-calculator"></i>
                            <span>{{ __('reports.profit_loss_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-account-balances') }}" class="report-link">
                            <i class="fas fa-calculator"></i>
                            <span>{{ __('reports.accounts_balance') }}</span>
                        </a>
                        <a href="{{ route('account-movement') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.account_movement_report') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير المخزون -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-boxes"></i>
                        <span class="card-title">{{ __('reports.inventory_items_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('items.index') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.items_list_with_balances') }}</span>
                        </a>
                        <a href="{{ route('item-movement') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.item_movement') }}</span>
                        </a>
                        <a href="{{ route('reports.get-items-max-min-quantity') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.items_list_with_min_max') }}</span>
                        </a>
                        <a href="{{ route('reports.items.inactive') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.inactive_items_report') }}</span>
                        </a>
                        <a href="{{ route('reports.items.with-stores') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.items_by_store_report') }}</span>
                        </a>
                        <a href="{{ route('reports.inventory-discrepancy-report') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>{{ __('reports.inventory_monitoring') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير المبيعات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="card-title">{{ __('reports.sales_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.sales.representative') }}" class="report-link">
                            <i class="fas fa-user-tie"></i>
                            <span>{{ __('reports.sales_by_representative') }}</span>
                        </a>
                        <a href="{{ route('reports.sales.daily') }}" class="report-link">
                            <i class="fas fa-calendar-day"></i>
                            <span>{{ __('reports.sales_daily_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-sales-total-report') }}" class="report-link">
                            <i class="fas fa-chart-line"></i>
                            <span>{{ __('reports.sales_total_report') }}</span>
                        </a>
                        <a href="{{ route('reports.sales.items') }}" class="report-link">
                            <i class="fas fa-box-open"></i>
                            <span>{{ __('reports.sales_items_report') }}</span>
                        </a>
                        <a href="{{ route('sales.invoice-report') }}" class="report-link">
                            <i class="fas fa-file-invoice"></i>
                            <span>{{ __('reports.sales_invoices_report') }}</span>
                        </a>
                        <a href="{{ route('sales-orders-report') }}" class="report-link">
                            <i class="fas fa-shopping-bag"></i>
                            <span>{{ __('reports.sales_orders_report') }}</span>
                        </a>
                        <a href="{{ route('purchase-quotations-reports') }}" class="report-link">
                            <i class="fas fa-file-contract"></i>
                            <span>{{ __('reports.customer_quotation_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-sales-report-by-address') }}" class="report-link">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ __('reports.sales_by_address') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير المشتريات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-basket"></i>
                        <span class="card-title">{{ __('reports.purchase_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.general-purchases-daily-report') }}" class="report-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>{{ __('reports.purchases_daily_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-purchases-total') }}" class="report-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>{{ __('reports.purchases_total_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-purchases-items-report') }}" class="report-link">
                            <i class="fas fa-boxes"></i>
                            <span>{{ __('reports.purchases_items_report') }}</span>
                        </a>
                        <a href="{{ route('billing.invoice-report') }}" class="report-link">
                            <i class="fas fa-boxes"></i>
                            <span>{{ __('reports.purchases_invoices_report') }}</span>
                        </a>
                        <a href="{{ route('supplier-rfqs-report') }}" class="report-link">
                            <i class="fas fa-boxes"></i>
                            <span>{{ __('reports.supplier_quotation_report') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير المصروفات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="card-title">{{ __('reports.expenses_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reports.expenses-balance-report') }}" class="report-link">
                            <i class="fas fa-balance-scale-right"></i>
                            <span>{{ __('reports.expenses_balance_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-expenses-report') }}" class="report-link">
                            <i class="fas fa-file-invoice"></i>
                            <span>{{ __('reports.general_expenses_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-expenses-daily-report') }}" class="report-link">
                            <i class="fas fa-calendar-day"></i>
                            <span>{{ __('reports.expenses_daily_report') }}</span>
                        </a>
                        <a href="{{ route('reports.general-cost-centers-report') }}" class="report-link">
                            <i class="fas fa-project-diagram"></i>
                            <span>{{ __('reports.cost_centers_report') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير النقدية والبنوك -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="card-title">{{ __('reports.cash_bank_reports') }}</span>
                    </div>
                    <div class="card-body">
                        {{-- <a href="{{ route('reports.general-cash-bank-report') }}" class="report-link">
                            <i class="fas fa-landmark"></i>
                            <span>{{ __('reports.cash_bank_report') }}</span>
                        </a> --}}
                        <a href="{{ route('reports.general-cashbox-movement-report') }}" class="report-link">
                            <i class="fas fa-cash-register"></i>
                            <span>{{ __('reports.general_cashbox_movement_report') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- تقارير العملاء -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i>
                        <span class="card-title">{{ __('reports.customers_reports') }}</span>
                    </div>
                    <div class="card-body">
                        <!-- يمكن إضافة تقارير العملاء هنا -->
                    </div>
                </div>
            </div>

            <!-- تقارير التصنيع -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-industry"></i>
                        <span class="card-title">تقارير التصنيع</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('manufacturing.invoice.report') }}" class="report-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>تقارير فواتير التصنيع</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- نظام إدارة الجودة -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-award"></i>
                        <span class="card-title">إدارة الجودة (QMS)</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('quality.dashboard') }}" class="report-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>لوحة تحكم الجودة</span>
                        </a>
                        <a href="{{ route('quality.inspections.index') }}" class="report-link">
                            <i class="fas fa-clipboard-check"></i>
                            <span>فحوصات الجودة</span>
                        </a>
                        <a href="{{ url('/quality/ncr') }}" class="report-link">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>تقارير عدم المطابقة (NCR)</span>
                        </a>
                        <a href="{{ url('/quality/capa') }}" class="report-link">
                            <i class="fas fa-tools"></i>
                            <span>إجراءات تصحيحية (CAPA)</span>
                        </a>
                        <a href="{{ url('/quality/batches') }}" class="report-link">
                            <i class="fas fa-barcode"></i>
                            <span>تتبع الدفعات</span>
                        </a>
                        <a href="{{ url('/quality/supplier-ratings') }}" class="report-link">
                            <i class="fas fa-star"></i>
                            <span>تقييم الموردين</span>
                        </a>
                        <a href="{{ url('/quality/certificates') }}" class="report-link">
                            <i class="fas fa-certificate"></i>
                            <span>الشهادات والامتثال</span>
                        </a>
                        <a href="{{ url('/quality/audits') }}" class="report-link">
                            <i class="fas fa-search"></i>
                            <span>التدقيق الداخلي</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Particles JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>

    <script>
        // Particles JS Configuration
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: '#ffffff'
                },
                shape: {
                    type: 'circle'
                },
                opacity: {
                    value: 0.5,
                    random: false
                },
                size: {
                    value: 3,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#ffffff',
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'repulse'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                }
            },
            retina_detect: true
        });

        // Search Filter Function
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

        // Card Animation on Page Load
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
