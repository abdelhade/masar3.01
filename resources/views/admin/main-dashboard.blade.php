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

<!-- Animated Doodles Background - Geometric Shapes, Currency & ERP Icons -->
<div class="doodles-container">
    <!-- Dollar Sign Icon -->
    <svg class="doodle doodle-1" width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
        <circle cx="40" cy="40" r="30" stroke="#239d77" stroke-width="2.5" fill="none" opacity="0.25"/>
        <text x="40" y="50" font-family="Arial, sans-serif" font-size="40" font-weight="bold" fill="#239d77" opacity="0.3" text-anchor="middle">$</text>
    </svg>
    
    <!-- Chart/Graph Icon -->
    <svg class="doodle doodle-2" width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="60" width="15" height="30" fill="#34d3a3" opacity="0.25"/>
        <rect x="40" y="40" width="15" height="50" fill="#239d77" opacity="0.25"/>
        <rect x="60" y="30" width="15" height="60" fill="#2ba88a" opacity="0.25"/>
        <line x1="15" y1="80" x2="85" y2="80" stroke="#239d77" stroke-width="2" opacity="0.2"/>
        <line x1="15" y1="80" x2="15" y2="15" stroke="#239d77" stroke-width="2" opacity="0.2"/>
    </svg>
    
    <!-- Box/Inventory Icon -->
    <svg class="doodle doodle-3" width="90" height="90" viewBox="0 0 90 90" xmlns="http://www.w3.org/2000/svg">
        <rect x="25" y="25" width="40" height="40" stroke="#34d3a3" stroke-width="2.5" fill="none" opacity="0.25"/>
        <rect x="30" y="30" width="30" height="30" stroke="#239d77" stroke-width="2" fill="none" opacity="0.2"/>
        <line x1="25" y1="25" x2="35" y2="15" stroke="#239d77" stroke-width="2" opacity="0.2"/>
        <line x1="65" y1="25" x2="75" y2="15" stroke="#239d77" stroke-width="2" opacity="0.2"/>
        <line x1="35" y1="15" x2="75" y2="15" stroke="#239d77" stroke-width="2" opacity="0.2"/>
    </svg>
    
    <!-- Euro Sign -->
    <svg class="doodle doodle-4" width="70" height="70" viewBox="0 0 70 70" xmlns="http://www.w3.org/2000/svg">
        <circle cx="35" cy="35" r="25" stroke="#2ba88a" stroke-width="2.5" fill="none" opacity="0.25"/>
        <text x="35" y="45" font-family="Arial, sans-serif" font-size="35" font-weight="bold" fill="#2ba88a" opacity="0.3" text-anchor="middle">€</text>
    </svg>
    
    <!-- Database Icon -->
    <svg class="doodle doodle-5" width="110" height="110" viewBox="0 0 110 110" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="55" cy="30" rx="35" ry="12" stroke="#34d3a3" stroke-width="2.5" fill="none" opacity="0.25"/>
        <ellipse cx="55" cy="55" rx="35" ry="12" stroke="#239d77" stroke-width="2.5" fill="none" opacity="0.25"/>
        <ellipse cx="55" cy="80" rx="35" ry="12" stroke="#2ba88a" stroke-width="2.5" fill="none" opacity="0.25"/>
        <line x1="20" y1="30" x2="20" y2="80" stroke="#239d77" stroke-width="2.5" opacity="0.2"/>
        <line x1="90" y1="30" x2="90" y2="80" stroke="#239d77" stroke-width="2.5" opacity="0.2"/>
    </svg>
    
    <!-- File/Document Icon -->
    <svg class="doodle doodle-6" width="85" height="85" viewBox="0 0 85 85" xmlns="http://www.w3.org/2000/svg">
        <path d="M25 20 L25 65 L60 65 L60 35 L45 35 L45 20 Z" stroke="#239d77" stroke-width="2.5" fill="none" opacity="0.25"/>
        <line x1="45" y1="20" x2="60" y2="35" stroke="#239d77" stroke-width="2.5" opacity="0.25"/>
        <line x1="30" y1="40" x2="55" y2="40" stroke="#34d3a3" stroke-width="2" opacity="0.2"/>
        <line x1="30" y1="50" x2="55" y2="50" stroke="#34d3a3" stroke-width="2" opacity="0.2"/>
    </svg>
    
    <!-- Geometric Shapes: Triangle -->
    <svg class="doodle doodle-7" width="75" height="75" viewBox="0 0 75 75" xmlns="http://www.w3.org/2000/svg">
        <polygon points="37.5,15 60,55 15,55" stroke="#34d3a3" stroke-width="2.5" fill="none" opacity="0.25"/>
        <circle cx="37.5" cy="40" r="8" fill="#239d77" opacity="0.2"/>
    </svg>
    
    <!-- Geometric Shapes: Hexagon -->
    <svg class="doodle doodle-8" width="95" height="95" viewBox="0 0 95 95" xmlns="http://www.w3.org/2000/svg">
        <polygon points="47.5,15 70,30 70,55 47.5,70 25,55 25,30" stroke="#2ba88a" stroke-width="2.5" fill="none" opacity="0.25"/>
        <circle cx="47.5" cy="42.5" r="12" stroke="#239d77" stroke-width="2" fill="none" opacity="0.2"/>
    </svg>
    
    <!-- Coin/Circle with $ -->
    <svg class="doodle doodle-9" width="65" height="65" viewBox="0 0 65 65" xmlns="http://www.w3.org/2000/svg">
        <circle cx="32.5" cy="32.5" r="25" stroke="#239d77" stroke-width="2.5" fill="#239d77" opacity="0.15"/>
        <text x="32.5" y="42" font-family="Arial, sans-serif" font-size="30" font-weight="bold" fill="#239d77" opacity="0.4" text-anchor="middle">$</text>
    </svg>
    
    <!-- Square Grid -->
    <svg class="doodle doodle-10" width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="20" width="20" height="20" stroke="#34d3a3" stroke-width="2" fill="none" opacity="0.25"/>
        <rect x="50" y="20" width="20" height="20" stroke="#239d77" stroke-width="2" fill="none" opacity="0.25"/>
        <rect x="20" y="50" width="20" height="20" stroke="#2ba88a" stroke-width="2" fill="none" opacity="0.25"/>
        <rect x="50" y="50" width="20" height="20" stroke="#34d3a3" stroke-width="2" fill="none" opacity="0.25"/>
    </svg>
</div>

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
        background: linear-gradient(135deg, rgba(52, 211, 163, 0.15) 0%, rgba(35, 157, 119, 0.12) 100%);
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        position: relative;
        overflow: hidden;
    }

    .header-top-row::before {
        display: none;
    }

    .title {
        margin: 0 !important;
        font-size: 1.75rem !important;
        color: #ffffff !important;
        font-family: 'IBM Plex Sans Arabic', 'Inter', ui-sans-serif, system-ui, sans-serif;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .user-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .header-icon-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .header-icon-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .header-icon-btn i {
        width: 20px;
        height: 20px;
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
            padding: 0.875rem 1.25rem !important;
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

        .header-top-row {
            padding: 0.75rem 1rem !important;
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
            <!-- الإعدادات الأساسية -->
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
            ])
            <div class="group-apps-grid">
                {{-- البيانات الاساسيه --}}
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
                ])
                    <a href="{{ route('accounts.index') }}" class="app-card">
                        <div class="app-icon" style="background-color: white;">
                            <i data-lucide="chart-bar-increasing"
                                style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
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
                    'view varibalsValues',
                ])
                    <a href="{{ route('items.index') }}" class="app-card">
                        <div class="app-icon" style="background-color: white;">
                            <i data-lucide="boxes"
                                style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                        </div>
                        <p class="app-name">الاصناف</p>
                    </a>
                @endcanany

                {{-- الصلاحيات --}}
                @canany(['view roles', 'view branches', 'view settings', 'view login-history', 'view active-sessions',
                    'view activity-logs'])
                    <a href="{{ route('users.index') }}" class="app-card">
                        <div class="app-icon" style="background-color: white;">
                            <i data-lucide="key" style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                        </div>
                        <p class="app-name">الصلاحيات</p>
                    </a>
                @endcanany

                {{-- الاعدادات --}}
                @can('view settings')
                    <a href="{{ route('export-settings') }}" class="app-card">
                        <div class="app-icon" style="background-color: white;">
                            <i data-lucide="settings"
                                style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                        </div>
                        <p class="app-name">الاعدادات</p>
                    </a>
                @endcan

                {{-- التقارير --}}
                <a href="{{ route('reports.overall') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-bar-chart"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">التقارير</p>
                </a>
            </div>
            @endcanany

            <!-- إدارة المبيعات -->
            <div class="group-apps-grid">
                {{-- crm --}}
                <a href="{{ route('statistics.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="user-cog"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">CRM</p>
                </a>
                {{-- المبيعات --}}
                <a href="{{ route('invoices.index', ['type' => 10]) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="trending-up"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">المبيعات</p>
                </a>
                {{-- pos --}}
                <a href="{{ route('pos.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="shopping-cart"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">نقطة البيع</p>
                </a>
                {{-- ادارة المستأجرات --}}
                <a href="{{ route('rentals.buildings.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="building"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">ادارة المستأجرات</p>
                </a>
            </div>

            <!-- المحاسبة والمالية -->
            <div class="group-apps-grid">
                {{-- ادارة الحسابات --}}
                <a href="{{ route('journals.index', ['type' => 'basic_journal']) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-text"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">أدارة الحسابات</p>
                </a>
                {{-- ادارة المصروفات --}}
                <a href="{{ route('expenses.dashboard') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="credit-card"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">ادارة المصروفات</p>
                </a>
                {{--   السندات الماليه --}}
                <a href="{{ route('vouchers.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="receipt"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">السندات الماليه</p>
                </a>
                {{-- التحويلات  النقديه --}}
                <a href="{{ route('transfers.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="arrow-left-right"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">التحويلات النقديه</p>
                </a>
                {{-- ادارة الدفعات -?user --}}
                <a href="{{ route('installments.plans.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="tag"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">ادارة الدفعات</p>
                </a>
                {{-- ادارة الشيكات --}}
                <a href="{{ route('checks.incoming') }}" class="app-card">
                    <span class="new-badge">جديد 🎉</span>
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="file-check-2"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">إدارة الشيكات</p>
                </a>
            </div>

            <!-- ادارة المخزون و التصنيع -->
            <div class="group-apps-grid">
                {{-- ادارة المخزون --}}
                <a href="{{ route('invoices.index', ['type' => 18]) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="package"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">ادارة المخزون</p>
                </a>
                {{-- التصنيع --}}
                <a href="{{ route('manufacturing.create') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="factory"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">التصنيع</p>
                </a>
                {{-- إدارة الجودة --}}
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
                    <a href="{{ route('quality.dashboard') }}" class="app-card">
                        <div class="app-icon" style="background-color: white;">
                            <i data-lucide="award"
                                style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                        </div>
                        <p class="app-name">إدارة الجودة</p>
                    </a>
                @endcanany
                {{-- المشتريات --}}
                <a href="{{ route('invoices.index', ['type' => 11]) }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="shopping-bag"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">المشتريات</p>
                </a>
                {{-- الصيانه --}}
                <a href="{{ route('service.types.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="package"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">الصيانه</p>
                </a>
                {{-- إدارة الأسطول --}}
                @can('view Fleet Dashboard')
                <a href="{{ route('fleet.dashboard.index') }}?sidebar=fleet" class="app-card">
                    <span class="new-badge">جديد 🎉</span>
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="truck"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">إدارة الأسطول</p>
                </a>
                @endcan
            </div>

            <!-- المشاريع والإنتاج -->
            <div class="group-apps-grid">
                {{-- المشاريع  --}}
                <a href="{{ route('projects.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="kanban"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">المشاريع</p>
                </a>
                {{-- التقدم اليومي --}}
                <a href="{{ route('progress.projcet.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="bar-chart-3"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">التقدم اليومي</p>
                </a>
                {{-- عمليات الاصول  --}}
                <a href="{{ route('depreciation.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="building"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">عمليات الاصول</p>
                </a>
                {{-- ادارة الموارد  --}}
                <a href="{{ route('myresources.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="cog"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">إدارة الموارد</p>
                </a>
            </div>

            <!-- الموارد البشرية -->
            <div class="group-apps-grid">
                {{-- الموارد البشريه --}}
                <a href="{{ route('employees.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="users"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">الموارد البشريه</p>
                </a>
                {{-- بصمة الموبايل  --}}
                <a href="{{ route('mobile.employee-login') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="fingerprint"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">بصمه الموبايل</p>
                </a>
            </div>

            <!-- الخدمات والعمليات -->
            <div class="group-apps-grid">
                {{-- ادارة المستأجرات  --}}
                <a href="{{ route('rentals.buildings.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="building"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">ادارة المستأجرات</p>
                </a>
                {{-- أدارة الشحن --}}
                <a href="{{ route('orders.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="truck"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">أدارة الشحن</p>
                </a>
                {{-- Inquiries --}}
                <a href="{{ route('inquiries.index') }}" class="app-card">
                    <div class="app-icon" style="background-color: white;">
                        <i data-lucide="layers"
                            style="color: #00695C; width: 24px; height: 24px; stroke-width: 2;"></i>
                    </div>
                    <p class="app-name">Inquiries</p>
                </a>
            </div>
        </div>
    @endcanany
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

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;

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
