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
    <link rel="stylesheet" href="{{ asset('css/themes/masar-themes.css') }}">

    <!-- Lucide Icons CDN -->
    <script src="{{ asset('assets/js/lucide.js') }}"></script>
</head>

<body class="theme-neumorphism-lite">
    <script>
        (function() {
            var k = 'masar_theme';
            var v;
            try {
                v = localStorage.getItem(k);
            } catch (e) {
                v = null;
            }
            var t = (v && ['classic', 'mint-green', 'dark', 'monokai'].indexOf(v) !== -1) ? v : 'classic';
            document.body.classList.add('theme-' + t);
        })();
    </script>
    <!-- Animated Doodles Background - Geometric Shapes, Currency & ERP Icons -->
    <div class="doodles-container">
        <!-- Dollar Sign Icon -->
        <svg class="doodle doodle-1" width="80" height="80" viewBox="0 0 80 80"
            xmlns="http://www.w3.org/2000/svg">
            <circle cx="40" cy="40" r="30" stroke="#239d77" stroke-width="2.5" fill="none"
                opacity="0.25" />
            <text x="40" y="50" font-family="Arial, sans-serif" font-size="40" font-weight="bold" fill="#239d77"
                opacity="0.3" text-anchor="middle">$</text>
        </svg>

        <!-- Chart/Graph Icon -->
        <svg class="doodle doodle-2" width="100" height="100" viewBox="0 0 100 100"
            xmlns="http://www.w3.org/2000/svg">
            <rect x="20" y="60" width="15" height="30" fill="#34d3a3" opacity="0.25" />
            <rect x="40" y="40" width="15" height="50" fill="#239d77" opacity="0.25" />
            <rect x="60" y="30" width="15" height="60" fill="#2ba88a" opacity="0.25" />
            <line x1="15" y1="80" x2="85" y2="80" stroke="#239d77" stroke-width="2"
                opacity="0.2" />
            <line x1="15" y1="80" x2="15" y2="15" stroke="#239d77" stroke-width="2"
                opacity="0.2" />
        </svg>

        <!-- Box/Inventory Icon -->
        <svg class="doodle doodle-3" width="90" height="90" viewBox="0 0 90 90"
            xmlns="http://www.w3.org/2000/svg">
            <rect x="25" y="25" width="40" height="40" stroke="#34d3a3" stroke-width="2.5" fill="none"
                opacity="0.25" />
            <rect x="30" y="30" width="30" height="30" stroke="#239d77" stroke-width="2" fill="none"
                opacity="0.2" />
            <line x1="25" y1="25" x2="35" y2="15" stroke="#239d77" stroke-width="2"
                opacity="0.2" />
            <line x1="65" y1="25" x2="75" y2="15" stroke="#239d77" stroke-width="2"
                opacity="0.2" />
            <line x1="35" y1="15" x2="75" y2="15" stroke="#239d77" stroke-width="2"
                opacity="0.2" />
        </svg>

        <!-- Euro Sign -->
        <svg class="doodle doodle-4" width="70" height="70" viewBox="0 0 70 70"
            xmlns="http://www.w3.org/2000/svg">
            <circle cx="35" cy="35" r="25" stroke="#2ba88a" stroke-width="2.5" fill="none"
                opacity="0.25" />
            <text x="35" y="45" font-family="Arial, sans-serif" font-size="35" font-weight="bold" fill="#2ba88a"
                opacity="0.3" text-anchor="middle">€</text>
        </svg>

        <!-- Database Icon -->
        <svg class="doodle doodle-5" width="110" height="110" viewBox="0 0 110 110"
            xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="55" cy="30" rx="35" ry="12" stroke="#34d3a3"
                stroke-width="2.5" fill="none" opacity="0.25" />
            <ellipse cx="55" cy="55" rx="35" ry="12" stroke="#239d77"
                stroke-width="2.5" fill="none" opacity="0.25" />
            <ellipse cx="55" cy="80" rx="35" ry="12" stroke="#2ba88a"
                stroke-width="2.5" fill="none" opacity="0.25" />
            <line x1="20" y1="30" x2="20" y2="80" stroke="#239d77" stroke-width="2.5"
                opacity="0.2" />
            <line x1="90" y1="30" x2="90" y2="80" stroke="#239d77" stroke-width="2.5"
                opacity="0.2" />
        </svg>

        <!-- File/Document Icon -->
        <svg class="doodle doodle-6" width="85" height="85" viewBox="0 0 85 85"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M25 20 L25 65 L60 65 L60 35 L45 35 L45 20 Z" stroke="#239d77" stroke-width="2.5" fill="none"
                opacity="0.25" />
            <line x1="45" y1="20" x2="60" y2="35" stroke="#239d77" stroke-width="2.5"
                opacity="0.25" />
            <line x1="30" y1="40" x2="55" y2="40" stroke="#34d3a3" stroke-width="2"
                opacity="0.2" />
            <line x1="30" y1="50" x2="55" y2="50" stroke="#34d3a3" stroke-width="2"
                opacity="0.2" />
        </svg>

        <!-- Geometric Shapes: Triangle -->
        <svg class="doodle doodle-7" width="75" height="75" viewBox="0 0 75 75"
            xmlns="http://www.w3.org/2000/svg">
            <polygon points="37.5,15 60,55 15,55" stroke="#34d3a3" stroke-width="2.5" fill="none"
                opacity="0.25" />
            <circle cx="37.5" cy="40" r="8" fill="#239d77" opacity="0.2" />
        </svg>

        <!-- Geometric Shapes: Hexagon -->
        <svg class="doodle doodle-8" width="95" height="95" viewBox="0 0 95 95"
            xmlns="http://www.w3.org/2000/svg">
            <polygon points="47.5,15 70,30 70,55 47.5,70 25,55 25,30" stroke="#2ba88a" stroke-width="2.5"
                fill="none" opacity="0.25" />
            <circle cx="47.5" cy="42.5" r="12" stroke="#239d77" stroke-width="2" fill="none"
                opacity="0.2" />
        </svg>

        <!-- Coin/Circle with $ -->
        <svg class="doodle doodle-9" width="65" height="65" viewBox="0 0 65 65"
            xmlns="http://www.w3.org/2000/svg">
            <circle cx="32.5" cy="32.5" r="25" stroke="#239d77" stroke-width="2.5" fill="#239d77"
                opacity="0.15" />
            <text x="32.5" y="42" font-family="Arial, sans-serif" font-size="30" font-weight="bold" fill="#239d77"
                opacity="0.4" text-anchor="middle">$</text>
        </svg>

        <!-- Square Grid -->
        <svg class="doodle doodle-10" width="100" height="100" viewBox="0 0 100 100"
            xmlns="http://www.w3.org/2000/svg">
            <rect x="20" y="20" width="20" height="20" stroke="#34d3a3" stroke-width="2" fill="none"
                opacity="0.25" />
            <rect x="50" y="20" width="20" height="20" stroke="#239d77" stroke-width="2" fill="none"
                opacity="0.25" />
            <rect x="20" y="50" width="20" height="20" stroke="#2ba88a" stroke-width="2" fill="none"
                opacity="0.25" />
            <rect x="50" y="50" width="20" height="20" stroke="#34d3a3" stroke-width="2" fill="none"
                opacity="0.25" />
        </svg>
    </div>

    <!-- Header Section - أول شيء في الصفحة -->
    <div class="header-section">
        <div class="header-container">
            <div class="header-top-row">
                <h1 class="title text-white text-page-title">Massar ERP</h1>
                <div class="user-section">
                    <i data-lucide="user" class="user-icon"></i>
                    <span class="user-name">{{ auth()->user()->name ?? __('User') }}</span>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn" :title="__('Logout')">
                            <i data-lucide="log-out" class="logout-icon"></i>
                            <span class="logout-text">{{ __('Logout') }}</span>
                        </button>
                    </form>
                </div>
                <div class="search-container-inline">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="text" id="searchInput" class="search-input frst"
                        placeholder="🔍 {{ __('Search for section...') }}">
                    <span class="search-count" id="searchCount"></span>
                </div>
                {{-- Theme switcher --}}
                <div class="ms-3 d-flex align-items-center gap-2">
                    <label for="masar-theme-select" class="mb-0 small fw-bold text-white">{{ __('Theme') }}:</label>
                    <select id="masar-theme-select" class="form-select form-select-sm shadow-sm" :title="__('Change Theme')"
                        style="min-width: 140px; border-radius: 8px; font-weight: 600;">
                        <option value="classic">{{ __('Classic (Blue)') }}</option>
                        <option value="mint-green">{{ __('Mint Green') }}</option>
                        <option value="dark">{{ __('Dark Mode') }}</option>
                        <option value="monokai">{{ __('Monokai') }}</option>
                    </select>
                </div>
                {{-- Language switcher --}}
                <div class="ms-3 d-flex align-items-center gap-2">
                    <label for="language-select" class="mb-0 small fw-bold text-white">{{ __('Language') }}:</label>
                    <select id="language-select" class="form-select form-select-sm shadow-sm" :title="__('Change Language')"
                        style="min-width: 120px; border-radius: 8px; font-weight: 600;">
                        <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
                        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-container">

        <!-- كروت الإحصائيات -->
        <div class="stats-cards-section">
            <div class="row g-3 stats-cards-row">
                <!-- كرت العملاء -->
                <div class="col-lg-4 col-md-4 stats-card-col">
                    <div class="card border-0 shadow-lg h-100 stats-card stats-card-clients">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="stats-card-content">
                                    <p class="stats-card-label">{{ __('Clients') }}</p>
                                    <h2 class="stats-card-value">{{ number_format($totalClients ?? 0) }}</h2>
                                    <p class="stats-card-subtitle">
                                        <i data-lucide="trending-up"></i>
                                        {{ __('Total Clients') }}
                                    </p>
                                </div>
                                <div class="stat-icon-wrapper">
                                    <i data-lucide="users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-decoration card-decoration-top"></div>
                        <div class="card-decoration card-decoration-bottom"></div>
                    </div>
                </div>

                <!-- كرت مرات الدخول -->
                <div class="col-lg-4 col-md-4 stats-card-col">
                    <div class="card border-0 shadow-lg h-100 stats-card stats-card-logins">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="stats-card-content">
                                    <p class="stats-card-label">{{ __('Login Count') }}</p>
                                    <h2 class="stats-card-value">{{ number_format($totalLogins ?? 0) }}</h2>
                                    <p class="stats-card-subtitle">
                                        <i data-lucide="activity"></i>
                                        {{ __('Total Sessions') }}
                                    </p>
                                </div>
                                <div class="stat-icon-wrapper">
                                    <i data-lucide="log-in"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-decoration card-decoration-top"></div>
                        <div class="card-decoration card-decoration-bottom"></div>
                    </div>
                </div>

                <!-- كرت المبيعات اليوم -->
                <div class="col-lg-4 col-md-4 stats-card-col">
                    <div class="card border-0 shadow-lg h-100 stats-card stats-card-sales">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="stats-card-content">
                                    <p class="stats-card-label">{{ __('Today Sales') }}</p>
                                    <h2 class="stats-card-value">{{ number_format($todaySales ?? 0, 2) }}</h2>
                                    <p class="stats-card-subtitle">
                                        <i data-lucide="dollar-sign"></i>
                                        {{ __('SAR') }}
                                    </p>
                                </div>
                                <div class="stat-icon-wrapper">
                                    <i data-lucide="trending-up"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-decoration card-decoration-top"></div>
                        <div class="card-decoration card-decoration-bottom"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- أيقونات البرنامج - سطر واحد -->
        @canany([
            'view Clients',
            'view Suppliers',
            'view Funds',
            'view Banks',
            'view Employees',
            'view warhouses',
            'view
            Expenses',
            'view Revenues',
            'view various_creditors',
            'view various_debtors',
            'view partners',
            'view
            current_partners',
            'view assets',
            'view rentables',
            'view check-portfolios-incoming',
            'view
            basicData-statistics',
            'view items',
            'view units',
            'view prices',
            'view notes-names',
            'view varibals',
            'view
            varibalsValues',
            'view roles',
            'view branches',
            'view settings',
            'view login-history',
            'view active-sessions',
            'view activity-logs',
            'view Inquiries',
            'view Orders',
            'view Rental-Management',
            'view progress-recyclebin',
            'view progress-project-types',
            'view progress-project-templates',
            'view progress-item-statuses',
            'view
            progress-work-items',
            'view progress-work-item-categories',
            'view daily-progress',
            'view progress-issues',
            'view
            progress-projects',
            'view progress-dashboard',
            ])
            <div class="apps-icons-row">
                <div class="d-flex">
                    {{-- البيانات الاساسيه --}}
                    <div class="app-icon-group">
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
                            'view partners',
                            'view current_partners',
                            'view assets',
                            'view rentables',
                            'view
                            check-portfolios-incoming',
                            'view basicData-statistics',
                            ])
                            <a href="{{ route('accounts.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="chart-bar-increasing"></i>
                                </div>
                                <p>{{ __('Basic Data') }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- الاصناف --}}
                    <div class="app-icon-group">
                        @canany([
                            'view items',
                            'view units',
                            'view prices',
                            'view notes-names',
                            'view varibals',
                            'view
                            varibalsValues',
                            ])
                            <a href="{{ route('items.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="boxes"></i>
                                </div>
                                <p>{{ __('Items') }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- الصلاحيات --}}
                    <div class="app-icon-group">
                        @canany([
                            'view roles',
                            'view branches',
                            'view settings',
                            'view login-history',
                            'view
                            active-sessions',
                            'view activity-logs',
                            ])
                            <a href="{{ route('users.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="key"></i>
                                </div>
                                <p>{{ __('Permissions') }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- الاعدادات --}}
                    <div class="app-icon-group">
                        @can('view settings')
                            <a href="{{ route('mysettings.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="settings"></i>
                                </div>
                                <p>{{ __('Settings') }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- التقارير --}}
                    <div class="app-icon-group">
                        @canany([
                            'view DailyWorkAnalysis',
                            'view Chart-of-Accounts',
                            'view balance-sheet',
                            'view
                            Profit-Loss',
                            'view Sales-Reports',
                            'view Purchasing-Reports',
                            'view Inventory-Reports',
                            'view
                            Expenses-Reports',
                            ])
                            <a href="{{ route('reports.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="file-bar-chart"></i>
                                </div>
                                <p>{{ __('Reports') }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- crm --}}
                    <div class="app-icon-group">
                        @canany(['view CRM', 'view CRM Statistics'])
                            <a href="{{ route('statistics.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="user-cog"></i>
                                </div>
                                <p>CRM</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- المبيعات --}}
                    <div class="app-icon-group">
                        @can('view Sales Invoice')
                            <a href="{{ route('invoices.index', ['type' => 10]) }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="trending-up"></i>
                                </div>
                                <p>{{ __("Sales") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- pos --}}
                    <div class="app-icon-group">
                        @can('view POS System')
                            <a href="{{ route('pos.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="shopping-cart"></i>
                                </div>
                                <p>{{ __("POS") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- ادارة المستأجرات --}}
                    <div class="app-icon-group">
                        @can('view Buildings')
                            <a href="{{ route('rentals.buildings.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="building"></i>
                                </div>
                                <p>{{ __("Rental Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- ادارة الحسابات --}}
                    <div class="app-icon-group">
                        @can('view journals')
                            <a href="{{ route('journals.index', ['type' => 'basic_journal']) }}"
                                target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="file-text"></i>
                                </div>
                                <p>{{ __("Accounts Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- ادارة المصروفات --}}
                    <div class="app-icon-group">
                        @can('view Expenses-Management')
                            <a href="{{ route('expenses.dashboard') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="credit-card"></i>
                                </div>
                                <p>{{ __("Expenses Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- السندات الماليه --}}
                    <div class="app-icon-group">
                        @canany(['view receipt vouchers', 'view payment vouchers', 'view exp-payment'])
                            <a href="{{ route('vouchers.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="receipt"></i>
                                </div>
                                <p>{{ __("Financial Vouchers") }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- التحويلات النقديه --}}
                    <div class="app-icon-group">
                        @can('view transfers')
                            <a href="{{ route('transfers.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="arrow-left-right"></i>
                                </div>
                                <p>{{ __("Cash Transfers") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- ادارة الدفعات --}}
                    <div class="app-icon-group">
                        @can('view Installment Plans')
                            <a href="{{ route('installments.plans.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="tag"></i>
                                </div>
                                <p>{{ __("Installments Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- ادارة الشيكات --}}
                    <div class="app-icon-group">
                        @can('view Checks')
                            <a href="{{ route('checks.incoming') }}" target="_blank" class="app-icon-large icon-bg-green"
                                style="position: relative;">
                                <span
                                    style="position: absolute; top: 5px; left: 5px; background: #ff4757; color: white; padding: 2px 6px; border-radius: 8px; font-size: 0.65rem; font-weight: 600; z-index: 10;">{{ __("New") }}</span>
                                <div class="icon-wrapper">
                                    <i data-lucide="file-check-2"></i>
                                </div>
                                <p>{{ __("Checks Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- ادارة المخزون --}}
                    <div class="app-icon-group">
                        @canany([
                            'view Inventory-Management',
                            'view Damaged Goods Invoice',
                            'view Dispatch Order',
                            'view
                            Addition Order',
                            'view Store-to-Store Transfer',
                            ])
                            <a href="{{ route('invoices.index', ['type' => 18]) }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="package"></i>
                                </div>
                                <p>{{ __("Inventory Management") }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- التصنيع --}}
                    <div class="app-icon-group">
                        @can('view Manufacturing Invoices')
                            <a href="{{ route('manufacturing.create') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="factory"></i>
                                </div>
                                <p>{{ __("Manufacturing") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- إدارة الجودة --}}
                    <div class="app-icon-group">
                        @canany([
                            'view quality',
                            'view inspections',
                            'view standards',
                            'view ncr',
                            'view capa',
                            'view
                            batches',
                            'view rateSuppliers',
                            'view certificates',
                            'view audits',
                            ])
                            <a href="{{ route('quality.dashboard') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="award"></i>
                                </div>
                                <p>{{ __("Quality Management") }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- المشتريات --}}
                    <div class="app-icon-group">
                        @can('view Purchase Invoice')
                            <a href="{{ route('invoices.index', ['type' => 11]) }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="shopping-bag"></i>
                                </div>
                                <p>{{ __("Purchases") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- الصيانه --}}
                    <div class="app-icon-group">
                        @canany([
                            'view Service Types',
                            'view Maintenances',
                            'view Periodic Maintenance',
                            'view
                            Maintenance',
                            ])
                            <a href="{{ route('service.types.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="package"></i>
                                </div>
                                <p>{{ __("Maintenance") }}</p>
                            </a>
                        @endcanany
                    </div>

                    {{-- إدارة الأسطول --}}
                    <div class="app-icon-group">
                        @can('view Fleet Dashboard')
                            <a href="{{ route('fleet.dashboard.index') }}?sidebar=fleet" target="_blank" class="app-icon-large icon-bg-green"
                                style="position: relative;">
                                <span
                                    style="position: absolute; top: 5px; left: 5px; background: #ff4757; color: white; padding: 2px 6px; border-radius: 8px; font-size: 0.65rem; font-weight: 600; z-index: 10;">{{ __("New") }}</span>
                                <div class="icon-wrapper">
                                    <i data-lucide="truck"></i>
                                </div>
                                <p>{{ __("Fleet Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- المشاريع --}}
                    <div class="app-icon-group">
                        @can('view Projects')
                            @if (Route::has('progress.projects.index'))
                                <a href="{{ route('progress.projects.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                    <div class="icon-wrapper">
                                        <i data-lucide="kanban"></i>
                                    </div>
                                    <p>{{ __("Projects") }}</p>
                                </a>
                            @endif
                        @endcan
                    </div>

                    {{-- التقدم اليومي --}}
                    <div class="app-icon-group">
                        @canany([
                            'view progress-recyclebin',
                            'view progress-project-types',
                            'view
                            progress-project-templates',
                            'view progress-item-statuses',
                            'view progress-work-items',
                            'view
                            progress-work-item-categories',
                            'view daily-progress',
                            'view progress-issues',
                            'view
                            progress-projects',
                            'view progress-dashboard',
                            ])
                            @if (Route::has('progress.dashboard'))
                                <a href="{{ route('progress.dashboard') }}" target="_blank" class="app-icon-large icon-bg-green">
                                    <div class="icon-wrapper">
                                        <i data-lucide="bar-chart-3"></i>
                                    </div>
                                    <p>{{ __("Daily Progress") }}</p>
                                </a>
                            @endif
                        @endcanany
                    </div>

                    {{-- عمليات الاصول --}}
                    <div class="app-icon-group">
                        <a href="{{ route('depreciation.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                            <div class="icon-wrapper">
                                <i data-lucide="building"></i>
                            </div>
                            <p>{{ __("Assets Operations") }}</p>
                        </a>
                    </div>

                    {{-- ادارة الموارد --}}
                    <div class="app-icon-group">
                        @can('view MyResources')
                            <a href="{{ route('myresources.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="cog"></i>
                                </div>
                                <p>{{ __("Resources Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- الموارد البشريه --}}
                    <div class="app-icon-group">
                        @can('view Employees')
                            <a href="{{ route('employees.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="users"></i>
                                </div>
                                <p>{{ __("Human Resources") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- بصمة الموبايل --}}
                    <div class="app-icon-group">
                        @can('view Mobile-fingerprint')
                            <a href="{{ route('mobile.employee-login') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="fingerprint"></i>
                                </div>
                                <p>{{ __("Mobile Fingerprint") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- أدارة الشحن --}}
                    <div class="app-icon-group">
                        @can('view Orders')
                            <a href="{{ route('orders.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="truck"></i>
                                </div>
                                <p>{{ __("Shipping Management") }}</p>
                            </a>
                        @endcan
                    </div>

                    {{-- Inquiries --}}
                    <div class="app-icon-group">
                        @can('view Inquiries')
                            <a href="{{ route('inquiries.index') }}" target="_blank" class="app-icon-large icon-bg-green">
                                <div class="icon-wrapper">
                                    <i data-lucide="layers"></i>
                                </div>
                                <p>Inquiries</p>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany

        <!-- الجداول (3 في الصف) -->
        <div class="tables-section" style="margin-top: 3rem;">
            <div class="row g-4">
                <!-- {{ __("Last 5 Accounts") }} -->
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom" style="padding: 1rem 1.25rem;">
                            <h5 class="mb-0 fw-bold tables-section-title">
                                <i data-lucide="wallet"
                                    style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;"></i>
                                {{ __("Last 5 Accounts") }}
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __("Code") }}</th>
                                            <th>{{ __("Name") }}</th>
                                            <th>{{ __("Number") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentAccounts ?? [] as $account)
                                            <tr>
                                                <td><strong>{{ $account->code ?? '-' }}</strong></td>
                                                <td>{{ $account->aname ?? '-' }}</td>
                                                <td>#{{ $account->id }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">{{ __("No data available") }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- {{ __("Last 5 Login Operations") }} -->
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom" style="padding: 1rem 1.25rem;">
                            <h5 class="mb-0 fw-bold tables-section-title">
                                <i data-lucide="log-in"
                                    style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;"></i>
                                {{ __("Last 5 Login Operations") }}
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __("User") }}</th>
                                            <th>IP</th>
                                            <th>{{ __("Date") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentLogins ?? [] as $login)
                                            <tr>
                                                <td><strong>{{ $login->user->name ?? '-' }}</strong></td>
                                                <td style="font-size: 0.875rem;">{{ $login->ip_address ?? '-' }}</td>
                                                <td style="font-size: 0.875rem;">
                                                    {{ $login->login_at ? $login->login_at->format('Y-m-d H:i') : '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">{{ __("No data available") }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- {{ __("Sales Statistics") }} -->
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom" style="padding: 1rem 1.25rem;">
                            <h5 class="mb-0 fw-bold tables-section-title">
                                <i data-lucide="trending-up"
                                    style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;"></i>
                                {{ __("Sales Statistics") }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-3">
                                <div class="sales-stats-item d-flex justify-content-between align-items-center">
                                    <span class="sales-stats-label">{{ __("Last Invoice") }}</span>
                                    <span class="sales-stats-value">
                                        {{ $salesStats['last_invoice'] ? '#' . $salesStats['last_invoice']->pro_id . ' - ' . number_format($salesStats['last_invoice']->fat_net ?? 0, 2) . ' ر.س' : '-' }}
                                    </span>
                                </div>
                                <div class="sales-stats-item d-flex justify-content-between align-items-center">
                                    <span class="sales-stats-label">{{ __("Today") }}</span>
                                    <span class="sales-stats-value">
                                        {{ number_format($salesStats['today'] ?? 0, 2) }} ر.س
                                    </span>
                                </div>
                                <div class="sales-stats-item d-flex justify-content-between align-items-center">
                                    <span class="sales-stats-label">{{ __("Last Week") }}</span>
                                    <span class="sales-stats-value">
                                        {{ number_format($salesStats['last_week'] ?? 0, 2) }} ر.س
                                    </span>
                                </div>
                                <div class="sales-stats-item d-flex justify-content-between align-items-center">
                                    <span class="sales-stats-label">{{ __("Last Month") }}</span>
                                    <span class="sales-stats-value">
                                        {{ number_format($salesStats['last_month'] ?? 0, 2) }} ر.س
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- {{ __("Last 5 Items") }} -->
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom" style="padding: 1rem 1.25rem;">
                            <h5 class="mb-0 fw-bold tables-section-title">
                                <i data-lucide="package"
                                    style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;"></i>
                                {{ __("Last 5 Items") }}
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __("Code") }}</th>
                                            <th>{{ __("Name") }}</th>
                                            <th>{{ __("Date") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentItems ?? [] as $item)
                                            <tr>
                                                <td><strong>{{ $item->code ?? '-' }}</strong></td>
                                                <td>{{ $item->name ?? '-' }}</td>
                                                <td style="font-size: 0.875rem;">
                                                    {{ $item->created_at ? $item->created_at->format('Y-m-d') : '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">{{ __("No data available") }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- {{ __("Last 5 Operations") }} -->
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom" style="padding: 1rem 1.25rem;">
                            <h5 class="mb-0 fw-bold tables-section-title">
                                <i data-lucide="file-text"
                                    style="width: 20px; height: 20px; margin-left: 8px; vertical-align: middle;"></i>
                                {{ __("Last 5 Operations") }}
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __("Number") }}</th>
                                            <th>{{ __("Client") }}</th>
                                            <th>{{ __("Amount") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentOperations ?? [] as $operation)
                                            <tr>
                                                <td><strong>#{{ $operation->pro_id ?? '-' }}</strong></td>
                                                <td>{{ $operation->acc1Head->aname ?? '-' }}</td>
                                                <td>
                                                    {{ number_format($operation->fat_net ?? 0, 2) }} ر.س
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">{{ __("No data available") }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Masar theme switcher --}}
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
    <script>
        (function() {
            if (typeof MasarThemeSwitcher !== 'undefined') MasarThemeSwitcher.bindSelect('#masar-theme-select');
        })();
    </script>

    {{-- Language switcher --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const languageSelect = document.getElementById('language-select');
            
            if (languageSelect) {
                languageSelect.addEventListener('change', function() {
                    const selectedLang = this.value;
                    
                    // Redirect to language switch route
                    window.location.href = '{{ url("/locale") }}/' + selectedLang;
                });
            }
        });
    </script>

    <script>
        // Initialize Lucide icons when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const searchCount = document.getElementById('searchCount');

            function performSearch() {
                if (!searchInput) {
                    console.error('Search input not found');
                    return;
                }

                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                // Get the apps-icons-row container
                const appsRow = document.querySelector('.apps-icons-row');
                if (!appsRow) {
                    console.error('Apps icons row (.apps-icons-row) not found');
                    return;
                }

                // Get all app icons inside apps-icons-row
                const appIcons = appsRow.querySelectorAll('.app-icon-large');

                console.log('Found', appIcons.length, 'app icons in .apps-icons-row');

                if (appIcons.length === 0) {
                    console.warn('No app icons found in .apps-icons-row');
                    return;
                }

                // Search in app icons
                appIcons.forEach(function(icon) {
                    const appText = icon.querySelector('p');
                    if (appText) {
                        const text = appText.textContent.toLowerCase().trim();
                        const matches = text.includes(searchTerm);

                        if (matches || searchTerm === '') {
                            // Show icon - remove all hiding styles
                            icon.classList.remove('hidden');
                            icon.style.cssText = '';
                            visibleCount++;
                        } else {
                            // Hide icon - use multiple methods to ensure it works
                            icon.classList.add('hidden');
                            icon.style.setProperty('display', 'none', 'important');
                            icon.style.setProperty('visibility', 'hidden', 'important');
                            icon.style.setProperty('opacity', '0', 'important');
                            icon.style.setProperty('height', '0', 'important');
                            icon.style.setProperty('width', '0', 'important');
                            icon.style.setProperty('margin', '0', 'important');
                            icon.style.setProperty('padding', '0', 'important');
                        }
                    } else {
                        // If no p tag, show the icon anyway
                        icon.classList.remove('hidden');
                        icon.style.cssText = '';
                        visibleCount++;
                    }
                });

                console.log('Search term:', searchTerm, 'Visible count:', visibleCount);

                // Update search count
                if (searchCount) {
                    if (searchTerm !== '') {
                        searchCount.textContent = visibleCount + ' {{ __("result") }}';
                        searchCount.style.display = 'block';
                    } else {
                        searchCount.style.display = 'none';
                    }
                }
            }

            if (searchInput) {
                // Initial search to show all icons
                performSearch();

                // Add event listeners
                searchInput.addEventListener('input', performSearch);
                searchInput.addEventListener('keyup', performSearch);
                searchInput.addEventListener('paste', function() {
                    setTimeout(performSearch, 10);
                });

                // Also listen for change event
                searchInput.addEventListener('change', performSearch);
            } else {
                console.error('Search input element not found');
            }
        });

        // Reinitialize icons if Lucide loads after DOM
        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Re-run search after page fully loads
            const searchInput = document.getElementById('searchInput');
            if (searchInput && searchInput.value) {
                const event = new Event('input');
                searchInput.dispatchEvent(event);
            }
        });
    </script>
</body>

</html>
