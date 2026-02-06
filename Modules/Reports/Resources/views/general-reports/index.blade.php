@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @push('styles')
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
    @endpush


    <!-- Particles JS Container -->
    <div id="particles-js"></div>

    <div class="reports-container">
        <div class="reports-header">
            <p class="display-4 fw-bold mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                {{ __('Integrated Reports System') }}
            </p>
        </div>

        <div class="search-box">
            <div class="input-group mb-3">
                <input type="text" class="form-control frst form-control-lg" placeholder="{{ __('Search Report') }}"
                    aria-label="{{ __('Search Reports') }}" id="report-filter" onkeyup="filterReports()">
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
                        <span class="card-title">{{ __('General Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Daily Activity Analyzer')
                            <a href="{{ route('reports.overall') }}" class="report-link">
                                <i class="fas fa-file-alt"></i>
                                <span>{{ __('Daily Activity Analyzer') }}</span>
                            </a>
                        @endcan

                        @can('view General Journal')
                            <a href="{{ route('reports.journal-summery') }}" class="report-link">
                                <i class="fas fa-book"></i>
                                <span>{{ __('General Journal') }}</span>
                            </a>
                        @endcan

                        @can('view General Account Statement')
                            <a href="{{ route('reports.general-journal-details') }}" class="report-link">
                                <i class="fas fa-file-invoice"></i>
                                <span>{{ __('General Account Statement') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير الحسابات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-book"></i>
                        <span class="card-title">{{ __('Accounts Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Accounts Tree')
                            <a href="{{ route('reports.accounts-tree') }}" class="report-link">
                                <i class="fas fa-tree"></i>
                                <span>{{ __('Accounts Tree') }}</span>
                            </a>
                        @endcan

                        @can('view Balance Sheet')
                            <a href="{{ route('reports.general-balance-sheet') }}" class="report-link">
                                <i class="fas fa-balance-scale"></i>
                                <span>{{ __('Balance Sheet') }}</span>
                            </a>
                        @endcan

                        @can('view Profit Loss Report')
                            <a href="{{ route('reports.general-profit-loss-report') }}" class="report-link">
                                <i class="fas fa-calculator"></i>
                                <span>{{ __('Profit Loss Report') }}</span>
                            </a>
                        @endcan

                        @can('view Income Statement Total')
                            <a href="{{ route('reports.general-profit-loss-report-total') }}" class="report-link">
                                <i class="fas fa-chart-line"></i>
                                <span>{{ __('Income Statement for Total Period') }}</span>
                            </a>
                        @endcan

                        @can('view Accounts Balance')
                            <a href="{{ route('reports.general-account-balances') }}" class="report-link">
                                <i class="fas fa-calculator"></i>
                                <span>{{ __('Accounts Balance') }}</span>
                            </a>
                        @endcan

                        @can('view Account Movement Report')
                            <a href="{{ route('account-movement') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Account Movement Report') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير المخزون -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-boxes"></i>
                        <span class="card-title">{{ __('Inventory Items Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Items Report')
                            <a href="{{ route('items.index') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Items List With Balances') }}</span>
                            </a>

                            <a href="{{ route('item-movement') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Item Movement') }}</span>
                            </a>

                            <a href="{{ route('reports.get-items-max-min-quantity') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Items List With Min Max') }}</span>
                            </a>

                            <a href="{{ route('reports.inactive-items') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Inactive Items Report') }}</span>
                            </a>

                            <a href="{{ route('reports.items.idle') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Idle Items Report') }}</span>
                            </a>

                            <a href="{{ route('reports.items.with-stores') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Items By Store Report') }}</span>
                            </a>

                            <a href="{{ route('reports.inventory-discrepancy-report') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Inventory Monitoring') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير المبيعات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="card-title">{{ __('Sales Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Sales Report')
                            <a href="{{ route('reports.sales.representative') }}" class="report-link">
                                <i class="fas fa-user-tie"></i>
                                <span>{{ __('Sales By Representative') }}</span>
                            </a>

                            <a href="{{ route('reports.general-sales-total-report') }}" class="report-link">
                                <i class="fas fa-chart-line"></i>
                                <span>{{ __('Sales Total Report') }}</span>
                            </a>

                            <a href="{{ route('reports.sales.items') }}" class="report-link">
                                <i class="fas fa-box-open"></i>
                                <span>{{ __('Sales Items Report') }}</span>
                            </a>

                            <a href="{{ route('sales.invoice-report') }}" class="report-link">
                                <i class="fas fa-file-invoice"></i>
                                <span>{{ __('Sales Invoices Report') }}</span>
                            </a>

                            <a href="{{ route('sales-orders-report') }}" class="report-link">
                                <i class="fas fa-shopping-bag"></i>
                                <span>{{ __('Sales Orders Report') }}</span>
                            </a>

                            <a href="{{ route('purchase-quotations-reports') }}" class="report-link">
                                <i class="fas fa-file-contract"></i>
                                <span>{{ __('Customer Quotation Report') }}</span>
                            </a>

                            <a href="{{ route('reports.general-sales-report-by-address') }}" class="report-link">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ __('Sales By Address') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير المشتريات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-shopping-basket"></i>
                        <span class="card-title">{{ __('Purchase Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Purchases Report')
                            <a href="{{ route('reports.general-purchases-daily-report') }}" class="report-link">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ __('Purchases Daily Report') }}</span>
                            </a>

                            <a href="{{ route('reports.general-purchases-total') }}" class="report-link">
                                <i class="fas fa-chart-pie"></i>
                                <span>{{ __('Purchases Total Report') }}</span>
                            </a>

                            <a href="{{ route('reports.general-purchases-items-report') }}" class="report-link">
                                <i class="fas fa-boxes"></i>
                                <span>{{ __('Purchases Items Report') }}</span>
                            </a>

                            <a href="{{ route('billing.invoice-report') }}" class="report-link">
                                <i class="fas fa-boxes"></i>
                                <span>{{ __('Purchases Invoices Report') }}</span>
                            </a>

                            <a href="{{ route('supplier-rfqs-report') }}" class="report-link">
                                <i class="fas fa-boxes"></i>
                                <span>{{ __('Supplier Quotation Report') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير المصروفات -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="card-title">{{ __('Expenses Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Expenses Report')
                            <a href="{{ route('reports.expenses-balance-report') }}" class="report-link">
                                <i class="fas fa-balance-scale-right"></i>
                                <span>{{ __('Expenses Balance Report') }}</span>
                            </a>

                            <a href="{{ route('reports.general-expenses-report') }}" class="report-link">
                                <i class="fas fa-file-invoice"></i>
                                <span>{{ __('General Expenses Report') }}</span>
                            </a>

                            <a href="{{ route('reports.general-expenses-daily-report') }}" class="report-link">
                                <i class="fas fa-calendar-day"></i>
                                <span>{{ __('Expenses Daily Report') }}</span>
                            </a>

                            <a href="{{ route('reports.general-cost-centers-report') }}" class="report-link">
                                <i class="fas fa-project-diagram"></i>
                                <span>{{ __('Cost Centers Report') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير النقدية والبنوك -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="card-title">{{ __('Cash Bank Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view General Cashbox Movement Report')
                            <a href="{{ route('reports.general-cashbox-movement-report') }}" class="report-link">
                                <i class="fas fa-cash-register"></i>
                                <span>{{ __('General Cashbox Movement Report') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- تقارير العملاء -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i>
                        <span class="card-title">{{ __('Customers Reports') }}</span>
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
                        <span class="card-title">{{ __('Manufacturing Reports') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Manufacturing Invoices Report')
                            <a href="{{ route('manufacturing.invoice.report') }}" class="report-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>{{ __('Manufacturing Invoices Report') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- نظام إدارة الجودة -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card">
                    <div class="card-header">
                        <i class="fas fa-award"></i>
                        <span class="card-title">{{ __('Quality Management System (Qms)') }}</span>
                    </div>
                    <div class="card-body">
                        @can('view Quality Report')
                            <a href="{{ route('quality.dashboard') }}" class="report-link">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>{{ __('Quality Dashboard') }}</span>
                            </a>

                            <a href="{{ route('quality.inspections.index') }}" class="report-link">
                                <i class="fas fa-clipboard-check"></i>
                                <span>{{ __('Quality Inspections') }}</span>
                            </a>

                            <a href="{{ url('/quality/ncr') }}" class="report-link">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>{{ __('Non-Conformance Reports (Ncr)') }}</span>
                            </a>

                            <a href="{{ url('/quality/capa') }}" class="report-link">
                                <i class="fas fa-tools"></i>
                                <span>{{ __('Corrective Actions (Capa)') }}</span>
                            </a>

                            <a href="{{ url('/quality/batches') }}" class="report-link">
                                <i class="fas fa-barcode"></i>
                                <span>{{ __('Batch Tracking') }}</span>
                            </a>

                            <a href="{{ url('/quality/supplier-ratings') }}" class="report-link">
                                <i class="fas fa-star"></i>
                                <span>{{ __('Supplier Ratings') }}</span>
                            </a>

                            <a href="{{ url('/quality/certificates') }}" class="report-link">
                                <i class="fas fa-certificate"></i>
                                <span>{{ __('Certificates & Compliance') }}</span>
                            </a>

                            <a href="{{ url('/quality/audits') }}" class="report-link">
                                <i class="fas fa-search"></i>
                                <span>{{ __('Internal Audit') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
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
    @endpush
@endsection
