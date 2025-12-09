<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Massar | Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-main.css') }}">

    <!-- Lucide Icons CDN -->
    <script src="{{ asset('assets/js/lucide.js') }}"></script>
</head>
<body class="theme-neumorphism-lite">

<style>
    .header-section {
        padding: 1.5rem 2rem !important;
        margin-bottom: 2rem !important;
    }

    .header-top-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .title {
        margin: 0 !important;
        font-size: 1.75rem !important;
        color: #34d3a3 !important;
        font-family: 'IBM Plex Sans Arabic', 'Inter', ui-sans-serif, system-ui, sans-serif;
    }

    .user-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-container {
        margin: 0 auto;
        max-width: 600px;
        width: 100%;
    }

    /* Tablet and below */
    @media (max-width: 1024px) {
        .header-section {
            padding: 1.5rem 1.5rem !important;
        }

        .search-container {
            max-width: 100%;
        }
    }

    /* Mobile landscape and below */
    @media (max-width: 768px) {
        .header-section {
            padding: 1.25rem 1rem !important;
            margin-bottom: 1.5rem !important;
        }

        .header-top-row {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 1.5rem !important;
        }

        .user-section {
            width: 100%;
            justify-content: center;
            flex-direction: column;
        }

        .user-info,
        .logout-btn {
            width: 100%;
            justify-content: center;
        }

        .search-container {
            padding: 0 0.5rem;
        }
    }

    /* Small mobile */
    @media (max-width: 640px) {
        .header-section {
            padding: 1rem 0.75rem !important;
            border-radius: 1rem !important;
        }

        .title {
            font-size: 1.25rem !important;
        }

        .user-section {
            gap: 0.75rem;
        }

        .logout-text {
            font-size: 0.875rem;
        }
    }
</style>

<div class="dashboard-container">
    <div class="header-section">
        <!-- الصف الأول: العنوان ومعلومات المستخدم -->
        <div class="header-top-row">
            <h1 class="title text-white text-page-title">Massar ERP</h1>
            <div class="user-section">
                <i data-lucide="user" class="user-icon"></i>
                <span class="user-name">{{ auth()->user()->name ?? 'المستخدم' }}</span>
                <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" title="تسجيل الخروج">
                        <i data-lucide="log-out" class="logout-icon"></i>
                        <span class="logout-text">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- الصف الثاني: البحث -->
        <div class="search-container">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" id="searchInput" class="search-input frst" placeholder="🔍 ابحث عن القسم...">
            <span class="search-count" id="searchCount"></span>
        </div>
    </div>

    <!-- مجموعة الإعدادات الأساسية -->

    @canany([
        'view Clients',
        'view Suppliers',
        'view Funds',
        'view Banks',
        'view Employees',
        'view warhouses',
        'view Expenses',
        'view Revenues',
        'view various_creditors',
        'view various_debtors',
        'view partners',
        'view current_partners',
        'view assets',
        'view rentables',
        'view check-portfolios-incoming',
        'view basicData-statistics',
        'view items',
        'view units',
        'view prices',
        'view notes-names',
        'view varibals',
        'view varibalsValues',
        'view roles',
        'view branches',
        'view settings',
        'view login-history',
        'view active-sessions',
        'view activity-logs',
        'view settings',
        ])

        <div class="apps-grid">

            <div class="app-group" data-group-index="0">
                <div class="group-header">
                    <div class="group-icon-wrapper" style="background: #34d3a320;">
                        <i data-lucide="settings" style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                    </div>
                    <h2 class="group-title">الإعدادات الأساسية</h2>
                    <div class="group-count">4</div>
                </div>

                <div class="group-apps-grid">
                    {{-- البيانات الاساسيه --}}
                    @canany([
                        'view Clients',
                        'view Suppliers',
                        'view Funds',
                        'view Banks',
                        'view Employees',
                        'view
                        warhouses',
                        'view Expenses',
                        'view Revenues',
                        'view various_creditors',
                        'view various_debtors',
                        'view
                        partners',
                        'view current_partners',
                        'view assets',
                        'view rentables',
                        'view check-portfolios-incoming',
                        'view basicData-statistics',
                        ])
                        <a href="{{ route('accounts.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="chart-bar-increasing"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">البيانات الاساسيه</p>
                        </a>
                    @endcanany

                    {{-- الاصناف --}}
                    @canany([
                        'view items',
                        'view units',
                        'view prices',
                        'view notes-names',
                        'view varibals',
                        'view
                        varibalsValues',
                        ])
                        <a href="{{ route('items.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="boxes"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">الاصناف</p>
                        </a>
                    @endcanany
                    {{-- الصلاحيات --}}
                    @canany(['view roles', 'view branches', 'view settings', 'view login-history', 'view active-sessions',
                        'view activity-logs'])
                        <a href="{{ route('users.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="key" style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">الصلاحيات</p>
                        </a>
                    @endcanany

                    {{-- الاعدادات --}}
                    @can('view settings')
                        <a href="{{ route('export-settings') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="settings"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">الاعدادات</p>
                        </a>
                    @endcan

                </div>
            </div>

        @endcanany


        <!-- مجموعة ادارة المبيعات -->
        <div class="app-group" data-group-index="1">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="shopping-bag"
                        style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title"> ادارة المبيعات</h2>
                <div class="group-count">4</div>
            </div>
            <div class="group-apps-grid">
                {{-- crm --}}
                <a href="{{ route('statistics.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="user-cog"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">CRM</p>
                </a>
                {{-- المبيعات --}}
                <a href="{{ route('invoices.index', ['type' => 10]) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="trending-up"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">المبيعات</p>
                </a>
                {{-- pos --}}
                <a href="{{ route('pos.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="shopping-cart"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">نقطة البيع</p>
                </a>
                {{-- ادارة المستأجرات --}}
                <a href="{{ route('rentals.buildings.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="building"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">ادارة المستأجرات</p>
                </a>
            </div>
        </div>

        <!-- مجموعة المحاسبة والمالية -->
        <div class="app-group" data-group-index="2">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="wallet" style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title">المحاسبة والمالية</h2>
                <div class="group-count">7</div>
            </div>
            <div class="group-apps-grid">
                {{-- ادارة الحسابات --}}
                <a href="{{ route('journals.index', ['type' => 'basic_journal']) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-text"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">أدارة الحسابات</p>
                </a>
                {{-- ادارة المصروفات --}}
                <a href="{{ route('reports.expenses-balance-report') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-text"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">ادارة المصروفات</p>
                </a>
                {{--   السندات الماليه --}}
                <a href="{{ route('vouchers.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="receipt"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">السندات الماليه</p>
                </a>
                {{-- التحويلات  النقديه --}}
                <a href="{{ route('transfers.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="arrow-left-right"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">التحويلات النقديه</p>
                </a>
                {{-- ادارة الدفعات -?user --}}
                <a href="{{ route('installments.plans.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="tag"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">ادارة الدفعات</p>
                </a>
                {{-- ادارة الشيكات --}}
                <a href="{{ route('checks.incoming') }}" class="app-card">
                    <span class="new-badge">جديد 🎉</span>
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-check-2"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">إدارة الشيكات</p>
                </a>
                {{-- ادارة الملفات  --}}
                <a href="{{ route('home') }}" class="app-card">
                    <span class="new-badge">جديد 🎉</span>
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-text"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">ادارة الملفات</p>
                </a>
            </div>
        </div>

        <!-- مجموعة ادارة المخزون و التصنيع -->
        <div class="app-group" data-group-index="3">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="shopping-bag"
                        style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title"> ادارة المخزون و التصنيع</h2>
                <div class="group-count">5</div>
            </div>
            <div class="group-apps-grid">
                {{-- ادارة المخزون --}}
                <a href="{{ route('invoices.index', ['type' => 18]) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="package"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">ادارة المخزون</p>
                </a>
                {{-- التصنيع --}}
                <a href="{{ route('manufacturing.create') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="factory"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">التصنيع</p>
                </a>
                {{-- المشتريات --}}
                <a href="{{ route('invoices.index', ['type' => 11]) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="shopping-bag"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">المشتريات</p>
                </a>
                {{-- الصيانه --}}
                <a href="{{ route('service.types.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="package"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">الصيانه</p>
                </a>


            </div>
        </div>

        <!-- مجموعة المشاريع والإنتاج -->
        <div class="app-group" data-group-index="4">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="kanban" style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title">المشاريع والإنتاج</h2>
                <div class="group-count">4</div>
            </div>
            <div class="group-apps-grid">
                {{-- المشاريع  --}}
                <a href="{{ route('projects.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="kanban"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">المشاريع</p>
                </a>
                {{-- التقدم اليومي --}}
                <a href="{{ route('progress.projcet.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="bar-chart-3"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">التقدم اليومي</p>
                </a>
                {{-- عمليات الاصول  --}}
                <a href="{{ route('depreciation.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="building"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">عمليات الاصول</p>
                </a>
                {{-- ادارة الموارد  --}}
                <a href="{{ route('myresources.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="cog"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">إدارة الموارد</p>
                </a>
            </div>
        </div>

        <!-- مجموعة الموارد البشرية -->
        <div class="app-group" data-group-index="5">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="users" style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title">الموارد البشرية</h2>
                <div class="group-count">2</div>
            </div>
            <div class="group-apps-grid">
                {{-- الموارد البشريه --}}
                <a href="{{ route('employees.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="users"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">الموارد البشريه</p>
                </a>
                {{-- بصمة الموبايل  --}}
                <a href="{{ route('mobile.employee-login') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="fingerprint"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">بصمه الموبايل</p>
                </a>
            </div>
        </div>

        <!-- مجموعة الخدمات والعمليات -->
        <div class="app-group" data-group-index="6">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="truck" style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title">الخدمات والعمليات</h2>
                <div class="group-count">3</div>
            </div>
            <div class="group-apps-grid">
                {{-- ادارة المستأجرات  --}}
                <a href="{{ route('rentals.buildings.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="building"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">ادارة المستأجرات</p>
                </a>
                {{-- أدارة الشحن --}}
                <a href="{{ route('orders.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="truck"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">أدارة الشحن</p>
                </a>
                {{-- Inquiries --}}
                <a href="{{ route('inquiries.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="layers"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">Inquiries</p>
                </a>
            </div>
        </div>

        <!-- مجموعة إدارة الجودة -->
        @canany([
            'view quality',
            'view inspections',
            'view standards',
            'view ncr',
            'view capa',
            'view batches',
            'view rateSuppliers',
            'view certificates',
            'view audits',
            ])
            <div class="app-group" data-group-index="7">
                <div class="group-header">
                    <div class="group-icon-wrapper" style="background: #34d3a320;">
                        <i data-lucide="award" style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                    </div>
                    <h2 class="group-title">إدارة الجودة</h2>
                    <div class="group-count">10</div>
                </div>
                <div class="group-apps-grid">
                    {{-- لوحة تحكم الجودة --}}
                    @can('view quality')
                        <a href="{{ route('quality.dashboard') }}" class="app-card">
                            <span class="new-badge">جديد 🎉</span>
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="chart-line"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">لوحة تحكم الجودة</p>
                        </a>
                    @endcan
                    {{-- فحوصات الجوده  --}}
                    @can('view inspections')
                        <a href="{{ route('quality.inspections.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="clipboard-check"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">فحوصات الجودة</p>
                        </a>
                    @endcan

                    {{-- معايير الجوده --}}
                    @can('view standards')
                        <a href="{{ route('quality.standards.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="ruler"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">معايير الجودة</p>
                        </a>
                    @endcan
                    {{-- عدم المطابقة (NCR) --}}
                    @can('view ncr')
                        <a href="{{ route('quality.ncr.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="alert-triangle"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">عدم المطابقة (NCR)</p>
                        </a>
                    @endcan
                    {{-- الاجراءات التصحيحية --}}
                    @can('view capa')
                        <a href="{{ route('quality.capa.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="wrench"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">الإجراءات التصحيحية</p>
                        </a>
                    @endcan
                    {{-- تتبع الدفعات  --}}
                    @can('view batches')
                        <a href="{{ route('quality.batches.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="barcode"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">تتبع الدفعات</p>
                        </a>
                    @endcan
                    {{-- تقييم الموردين  --}}
                    @can('view rateSuppliers')
                        <a href="{{ route('quality.suppliers.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="star"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">تقييم الموردين</p>
                        </a>
                    @endcan
                    {{-- الشهادات والامتثال  --}}
                    @can('view certificates')
                        <a href="{{ route('quality.certificates.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="award"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">الشهادات والامتثال</p>
                        </a>
                    @endcan
                    {{-- التدقيق الداخلي  --}}
                    @can('view audits')
                        <a href="{{ route('quality.audits.index') }}" class="app-card">
                            <div class="app-icon" style="background-color: white;">
                                <i data-lucide="search"
                                    style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                            </div>
                            <p class="app-name">التدقيق الداخلي</p>
                        </a>
                    @endcan
                    {{-- تقارير الجوده  --}}
                    <a href="{{ route('quality.reports') }}" class="app-card">
                        <div class="app-icon" style="background-color: white;">
                            <i data-lucide="chart-pie"
                                style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                        </div>
                        <p class="app-name">تقارير الجودة</p>
                    </a>
                </div>
            </div>
        @endcanany

        <!-- مجموعة التقارير -->
        <div class="app-group" data-group-index="8">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: #34d3a320;">
                    <i data-lucide="file-bar-chart"
                        style="color: #34d3a3; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title">التقارير</h2>
                <div class="group-count">8</div>
            </div>
            <div class="group-apps-grid">
                {{-- محلل العمل اليومي --}}
                <a href="{{ route('reports.overall') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="bar-chart-3"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">محلل العمل اليومي</p>
                </a>
                {{-- شجره الحسابات --}}
                <a href="{{ route('reports.accounts-tree') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="git-branch"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">شجرة الحسابات</p>
                </a>
                {{-- الميزانيه العموميه --}}
                <a href="{{ route('reports.general-balance-sheet') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="scale"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">الميزانية العمومية</p>
                </a>
                {{-- ارباح وخسائر --}}
                <a href="{{ route('reports.general-profit-loss-report') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="trending-up"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">أرباح وخسائر</p>
                </a>
                {{-- تقارير المبيعات  --}}
                <a href="{{ route('reports.sales.total') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="shopping-cart"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">تقارير المبيعات</p>
                </a>
                {{-- تقارير المشتريات  --}}
                <a href="{{ route('reports.purchases.total') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="shopping-bag"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">تقارير المشتريات</p>
                </a>
                {{-- تقارير المخزون --}}
                <a href="{{ route('reports.general-inventory-balances') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="package"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">تقارير المخزون</p>
                </a>
                {{-- تقارير المصروفات  --}}
                <a href="{{ route('reports.expenses-balance-report') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-text"
                            style="color: #00695C; width: 30px; height: 25px; stroke-width: 2.5;"></i>
                    </div>
                    <p class="app-name">تقارير المصروفات</p>
                </a>

            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide icons when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const searchCount = document.getElementById('searchCount');
        const appCards = document.querySelectorAll('.app-card');
        const appGroups = document.querySelectorAll('.app-group');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;
                let visibleGroups = 0;

                // Search in app cards
                appCards.forEach(function(card) {
                    const appName = card.querySelector('.app-name');
                    if (appName) {
                        const text = appName.textContent.toLowerCase();
                        if (text.includes(searchTerm) || searchTerm === '') {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });

                // Hide/show groups based on visible cards
                appGroups.forEach(function(group) {
                    const groupCards = group.querySelectorAll('.app-card');
                    const visibleCards = Array.from(groupCards).filter(function(card) {
                        return card.style.display !== 'none';
                    });

                    if (visibleCards.length > 0 || searchTerm === '') {
                        group.style.display = '';
                        if (visibleCards.length > 0) {
                            visibleGroups++;
                        }
                    } else {
                        group.style.display = 'none';
                    }
                });

                // Update search count
                if (searchTerm !== '') {
                    searchCount.textContent = visibleCount + ' نتيجة';
                    searchCount.style.display = 'block';
                } else {
                    searchCount.style.display = 'none';
                }
            });
        }
    });

    // Reinitialize icons if Lucide loads after DOM
    window.addEventListener('load', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
</body>
</html>
