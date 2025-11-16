@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();

    // Define all app groups with permissions
    $appsGroupsData = [
        [
            'groupName' => 'الإعدادات الأساسية',
            'groupIcon' => 'settings',
            'groupColor' => '#7B1FA2',
            'apps' => [
                [
                    'name' => 'الرئيسيه',
                    'icon' => 'home',
                    'iconBg' => '#FFF3E0',
                    'iconColor' => '#E65100',
                    'route' => route('home'),
                    'permission' => null, // Always visible
                ],
                [
                    'name' => 'البيانات الاساسيه',
                    'icon' => 'chart-bar-increasing',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#7B1FA2',
                    'route' => route('accounts.index'),
                    'permission' => 'view basicData-statistics',
                ],
                [
                    'name' => 'الاصناف',
                    'icon' => 'boxes',
                    'iconBg' => '#E8F5E8',
                    'iconColor' => '#2E7D32',
                    'route' => route('items.index'),
                    'permission' => 'view items',
                ],
                [
                    'name' => 'الصلاحيات',
                    'icon' => 'key',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('users.index'),
                    'permission' => 'view Users',
                ],
                [
                    'name' => 'الاعدادات',
                    'icon' => 'settings',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#7B1FA2',
                    'route' => route('export-settings'),
                    'permission' => 'view Settings',
                ],
            ],
        ],
        [
            'groupName' => 'المبيعات والمشتريات',
            'groupIcon' => 'shopping-bag',
            'groupColor' => '#E65100',
            'apps' => [
                [
                    'name' => 'CRM',
                    'icon' => 'user-cog',
                    'iconBg' => '#E8F5E8',
                    'iconColor' => '#2E7D32',
                    'route' => route('statistics.index'),
                    'permission' => 'view CRM Statistics',
                ],
                [
                    'name' => 'المبيعات',
                    'icon' => 'trending-up',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#7B1FA2',
                    'route' => route('invoices.index', ['type' => 10]),
                    'permission' => 'view Sales',
                ],
                [
                    'name' => 'المشتريات',
                    'icon' => 'shopping-bag',
                    'iconBg' => '#FFF3E0',
                    'iconColor' => '#E65100',
                    'route' => route('invoices.index', ['type' => 11]),
                    'permission' => 'view Purchases',
                ],
                [
                    'name' => 'ادارة المخزون',
                    'icon' => 'package',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('invoices.index', ['type' => 18]),
                    'permission' => 'view Inventory',
                ],
                [
                    'name' => 'نقطة البيع',
                    'icon' => 'shopping-cart',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#7B1FA2',
                    'route' => route('pos.index'),
                    'permission' => 'view POS',
                ],
            ],
        ],
        [
            'groupName' => 'المحاسبة والمالية',
            'groupIcon' => 'wallet',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'السندات الماليه',
                    'icon' => 'receipt',
                    'iconBg' => '#E0F2F1',
                    'iconColor' => '#00695C',
                    'route' => route('vouchers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التحويلات النقديه',
                    'icon' => 'arrow-left-right',
                    'iconBg' => '#E3F2FD',
                    'iconColor' => '#1565C0',
                    'route' => route('transfers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'رواتب الموظفين',
                    'icon' => 'id-card',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('multi-vouchers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'الاستحقاقات',
                    'icon' => 'wallet',
                    'iconBg' => '#E0F2F1',
                    'iconColor' => '#00695C',
                    'route' => route('journals.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'أدارة الحسابات',
                    'icon' => 'file-text',
                    'iconBg' => '#FFF3E0',
                    'iconColor' => '#E65100',
                    'route' => route('journals.index', ['type' => 'basic_journal']),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'إدارة الشيكات',
                    'icon' => 'file-check-2',
                    'iconBg' => '#E8F5E9',
                    'iconColor' => '#2E7D32',
                    'route' => route('checks.incoming'),
                    'permission' => 'view check-portfolios-incoming',
                    'isNew' => true,
                ],
            ],
        ],
        [
            'groupName' => 'المشاريع والإنتاج',
            'groupIcon' => 'kanban',
            'groupColor' => '#2E7D32',
            'apps' => [
                [
                    'name' => 'المشاريع',
                    'icon' => 'kanban',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#7B1FA2',
                    'route' => route('projects.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التصنيع',
                    'icon' => 'factory',
                    'iconBg' => '#E0F2F1',
                    'iconColor' => '#00695C',
                    'route' => route('manufacturing.create'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التقدم اليومي',
                    'icon' => 'bar-chart-3',
                    'iconBg' => '#E8F5E8',
                    'iconColor' => '#2E7D32',
                    'route' => route('progress.projcet.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'عمليات الاصول',
                    'icon' => 'building',
                    'iconBg' => '#E8F5E8',
                    'iconColor' => '#2E7D32',
                    'route' => route('depreciation.index'),
                    'permission' => 'view assets',
                ],
            ],
        ],
        [
            'groupName' => 'الموارد البشرية',
            'groupIcon' => 'users',
            'groupColor' => '#1565C0',
            'apps' => [
                [
                    'name' => 'الموارد البشريه',
                    'icon' => 'users',
                    'iconBg' => '#E3F2FD',
                    'iconColor' => '#1565C0',
                    'route' => route('employees.index'),
                    'permission' => 'view Employees',
                ],
                [
                    'name' => 'بصمه الموبايل',
                    'icon' => 'fingerprint',
                    'iconBg' => '#E8F5E8',
                    'iconColor' => '#2E7D32',
                    'route' => route('mobile.employee-login'),
                    'permission' => null,
                ],
            ],
        ],
        [
            'groupName' => 'الخدمات والعمليات',
            'groupIcon' => 'truck',
            'groupColor' => '#F57F17',
            'apps' => [
                [
                    'name' => 'ادارة المستأجرات',
                    'icon' => 'building',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('rentals.buildings.index'),
                    'permission' => 'view rentables',
                ],
                [
                    'name' => 'الصيانه',
                    'icon' => 'package',
                    'iconBg' => '#E0F2F1',
                    'iconColor' => '#00695C',
                    'route' => route('service.types.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'أدارة الشحن',
                    'icon' => 'truck',
                    'iconBg' => '#FFF3E0',
                    'iconColor' => '#E65100',
                    'route' => route('orders.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'Inquiries',
                    'icon' => 'layers',
                    'iconBg' => '#E8F5E8',
                    'iconColor' => '#2E7D32',
                    'route' => route('inquiries.index'),
                    'permission' => 'view Inquiries',
                ],
            ],
        ],
        [
            'groupName' => 'إدارة الجودة',
            'groupIcon' => 'award',
            'groupColor' => '#C62828',
            'apps' => [
                [
                    'name' => 'لوحة تحكم الجودة',
                    'icon' => 'chart-line',
                    'iconBg' => '#FFEBEE',
                    'iconColor' => '#C62828',
                    'route' => route('quality.dashboard'),
                    'permission' => 'view Dashboard',
                    'isNew' => true,
                ],
                [
                    'name' => 'فحوصات الجودة',
                    'icon' => 'clipboard-check',
                    'iconBg' => '#E8F5E9',
                    'iconColor' => '#2E7D32',
                    'route' => route('quality.inspections.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'معايير الجودة',
                    'icon' => 'ruler',
                    'iconBg' => '#E3F2FD',
                    'iconColor' => '#1565C0',
                    'route' => route('quality.standards.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'عدم المطابقة (NCR)',
                    'icon' => 'alert-triangle',
                    'iconBg' => '#FFF3E0',
                    'iconColor' => '#E65100',
                    'route' => route('quality.ncr.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'الإجراءات التصحيحية',
                    'icon' => 'wrench',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#7B1FA2',
                    'route' => route('quality.capa.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'تتبع الدفعات',
                    'icon' => 'barcode',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('quality.batches.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'تقييم الموردين',
                    'icon' => 'star',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('quality.suppliers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'الشهادات والامتثال',
                    'icon' => 'award',
                    'iconBg' => '#E0F2F1',
                    'iconColor' => '#00695C',
                    'route' => route('quality.certificates.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التدقيق الداخلي',
                    'icon' => 'search',
                    'iconBg' => '#E3F2FD',
                    'iconColor' => '#1565C0',
                    'route' => route('quality.audits.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'تقارير الجودة',
                    'icon' => 'chart-pie',
                    'iconBg' => '#FFEBEE',
                    'iconColor' => '#C62828',
                    'route' => route('quality.reports'),
                    'permission' => 'view Dashboard',
                ],
            ],
        ],
        [
            'groupName' => 'التقارير',
            'groupIcon' => 'file-bar-chart',
            'groupColor' => '#6A1B9A',
            'apps' => [
                [
                    'name' => 'محلل العمل اليومي',
                    'icon' => 'bar-chart-3',
                    'iconBg' => '#F3E5F5',
                    'iconColor' => '#6A1B9A',
                    'route' => route('reports.overall'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'شجرة الحسابات',
                    'icon' => 'git-branch',
                    'iconBg' => '#E8F5E9',
                    'iconColor' => '#2E7D32',
                    'route' => route('reports.accounts-tree'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'الميزانية العمومية',
                    'icon' => 'scale',
                    'iconBg' => '#E3F2FD',
                    'iconColor' => '#1565C0',
                    'route' => route('reports.general-balance-sheet'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'أرباح وخسائر',
                    'icon' => 'trending-up',
                    'iconBg' => '#E8F5E9',
                    'iconColor' => '#2E7D32',
                    'route' => route('reports.general-profit-loss-report'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المبيعات',
                    'icon' => 'shopping-cart',
                    'iconBg' => '#FFF3E0',
                    'iconColor' => '#E65100',
                    'route' => route('reports.sales.total'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المشتريات',
                    'icon' => 'shopping-bag',
                    'iconBg' => '#FFF8E1',
                    'iconColor' => '#F57F17',
                    'route' => route('reports.purchases.total'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المخزون',
                    'icon' => 'package',
                    'iconBg' => '#E0F2F1',
                    'iconColor' => '#00695C',
                    'route' => route('reports.general-inventory-report'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المصروفات',
                    'icon' => 'file-text',
                    'iconBg' => '#FFEBEE',
                    'iconColor' => '#C62828',
                    'route' => route('reports.expenses-balance-report'),
                    'permission' => 'view Reports',
                ],
            ],
        ],
    ];
@endphp

<title>Massar | Dashboard</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ auth()->id() }}">


<link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-main.css') }}">

<!-- Lucide Icons CDN -->
<script src="{{ asset('assets/js/lucide.js') }}"></script>

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
    }

    .main-title {
        margin: 0 !important;
        font-size: 1.75rem !important;
    }

    .user-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .search-container {
        margin: 0 auto;
        max-width: 600px;
    }

    @media (max-width: 768px) {
        .header-top-row {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .user-section {
            width: 100%;
            justify-content: center;
        }
    }
</style>
<div class="dashboard-container">
    <div class="header-section">
        <!-- الصف الأول: العنوان ومعلومات المستخدم -->
        <div class="header-top-row">
            <h1 class="main-title">Massar ERP</h1>
            <div class="user-section">
                <div class="user-info">
                    <i data-lucide="user" class="user-icon"></i>
                    <span class="user-name">{{ auth()->user()->name ?? 'المستخدم' }}</span>
                </div>
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

    <div class="apps-grid" id="appsGrid">
        <!-- Apps will be populated by JavaScript -->
    </div>
</div>
<script>
    const appsGroups = [
        {
            groupName: "الإعدادات الأساسية",
            groupIcon: "settings",
            groupColor: "#7B1FA2",
            apps: [
                {
                    name: "الرئيسيه",
                    icon: "home",
                    iconBg: "#FFF3E0",
                    iconColor: "#E65100",
                    route: "{{ route('home') }}"
                },
                {
                    name: "البيانات الاساسيه",
                    icon: "chart-bar-increasing",
                    iconBg: "#F3E5F5",
                    iconColor: "#7B1FA2",
                    route: "{{ route('accounts.index') }}"
                },
                {
                    name: "الاصناف",
                    icon: "boxes",
                    iconBg: "#E8F5E8",
                    iconColor: "#2E7D32",
                    route: "{{ route('items.index') }}"
                },
                {
                    name: "ادارة التقسيط",
                    icon: "tag",
                    iconBg: "#E3F2FD",
                    iconColor: "#1565C0",
                    route: "{{ route('installments.plans.index') }}"
                },
                {
                    name: "الصلاحيات",
                    icon: "key",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('users.index') }}"
                },
                {
                    name: "الاعدادات",
                    icon: "settings",
                    iconBg: "#F3E5F5",
                    iconColor: "#7B1FA2",
                    route: "{{ route('export-settings') }}"
                }
            ]
        },
        {
            groupName: "المبيعات والمشتريات",
            groupIcon: "shopping-bag",
            groupColor: "#E65100",
            apps: [
                {
                    name: "CRM",
                    icon: "user-cog",
                    iconBg: "#E8F5E8",
                    iconColor: "#2E7D32",
                    route: "{{ route('statistics.index') }}"
                },
                {
                    name: "المبيعات",
                    icon: "trending-up",
                    iconBg: "#F3E5F5",
                    iconColor: "#7B1FA2",
                    route: "{{ route('invoices.index', ['type' => 10]) }}"
                },
                {
                    name: "المشتريات",
                    icon: "shopping-bag",
                    iconBg: "#FFF3E0",
                    iconColor: "#E65100",
                    route: "{{ route('invoices.index', ['type' => 11]) }}"
                },
                {
                    name: "ادارة المخزون",
                    icon: "package",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('invoices.index', ['type' => 18]) }}"
                },
                {
                    name: "نقطة البيع",
                    icon: "shopping-cart",
                    iconBg: "#F3E5F5",
                    iconColor: "#7B1FA2",
                    route: "{{ route('pos.index') }}"
                }
            ]
        },
        {
            groupName: "المحاسبة والمالية",
            groupIcon: "wallet",
            groupColor: "#00695C",
            apps: [
                {
                    name: "السندات الماليه",
                    icon: "receipt",
                    iconBg: "#E0F2F1",
                    iconColor: "#00695C",
                    route: "{{ route('vouchers.index') }}"
                },
                {
                    name: "التحويلات النقديه",
                    icon: "arrow-left-right",
                    iconBg: "#E3F2FD",
                    iconColor: "#1565C0",
                    route: "{{ route('transfers.index') }}"
                },
                {
                    name: "رواتب الموظفين",
                    icon: "id-card",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('multi-vouchers.index') }}"
                },
                {
                    name: "الاستحقاقات",
                    icon: "wallet",
                    iconBg: "#E0F2F1",
                    iconColor: "#00695C",
                    route: "{{ route('journals.index') }}"
                },
                {
                    name: "أدارة الحسابات",
                    icon: "file-text",
                    iconBg: "#FFF3E0",
                    iconColor: "#E65100",
                    route: "{{ route('journals.index', ['type' => 'basic_journal']) }}"
                },
                {
                    name: "إدارة الشيكات",
                    icon: "file-check-2",
                    iconBg: "#E8F5E9",
                    iconColor: "#2E7D32",
                    route: "{{ route('checks.incoming') }}",
                    isNew: true
                }
            ]
        },
        {
            groupName: "المشاريع والإنتاج",
            groupIcon: "kanban",
            groupColor: "#2E7D32",
            apps: [
                {
                    name: "المشاريع",
                    icon: "kanban",
                    iconBg: "#F3E5F5",
                    iconColor: "#7B1FA2",
                    route: "{{ route('projects.index') }}"
                },
                {
                    name: "التصنيع",
                    icon: "factory",
                    iconBg: "#E0F2F1",
                    iconColor: "#00695C",
                    route: "{{ route('manufacturing.create') }}"
                },
                {
                    name: "التقدم اليومي",
                    icon: "bar-chart-3",
                    iconBg: "#E8F5E8",
                    iconColor: "#2E7D32",
                    route: "{{ route('progress.projcet.index') }}"
                },
                {
                    name: "عمليات الاصول",
                    icon: "building",
                    iconBg: "#E8F5E8",
                    iconColor: "#2E7D32",
                    route: "{{ route('depreciation.index') }}"
                }
            ]
        },
        {
            groupName: "الموارد البشرية",
            groupIcon: "users",
            groupColor: "#1565C0",
            apps: [
                {
                    name: "الموارد البشريه",
                    icon: "users",
                    iconBg: "#E3F2FD",
                    iconColor: "#1565C0",
                    route: "{{ route('employees.index') }}"
                },
                {
                    name: "بصمه الموبايل",
                    icon: "fingerprint",
                    iconBg: "#E8F5E8",
                    iconColor: "#2E7D32",
                    route: "{{ route('mobile.employee-login') }}"
                }
            ]
        },
        {
            groupName: "الخدمات والعمليات",
            groupIcon: "truck",
            groupColor: "#F57F17",
            apps: [
                {
                    name: "ادارة المستأجرات",
                    icon: "building",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('rentals.buildings.index') }}"
                },
                {
                    name: "الصيانه",
                    icon: "package",
                    iconBg: "#E0F2F1",
                    iconColor: "#00695C",
                    route: "{{ route('service.types.index') }}"
                },
                {
                    name: "أدارة الشحن",
                    icon: "truck",
                    iconBg: "#FFF3E0",
                    iconColor: "#E65100",
                    route: "{{ route('orders.index') }}"
                },
                {
                    name: "Inquiries",
                    icon: "layers",
                    iconBg: "#E8F5E8",
                    iconColor: "#2E7D32",
                    route: "{{ route('inquiries.index') }}"
                }
            ]
        },
        {
            groupName: "إدارة الجودة",
            groupIcon: "award",
            groupColor: "#C62828",
            apps: [
                {
                    name: "لوحة تحكم الجودة",
                    icon: "chart-line",
                    iconBg: "#FFEBEE",
                    iconColor: "#C62828",
                    route: "{{ route('quality.dashboard') }}",
                    isNew: true
                },
                {
                    name: "فحوصات الجودة",
                    icon: "clipboard-check",
                    iconBg: "#E8F5E9",
                    iconColor: "#2E7D32",
                    route: "{{ route('quality.inspections.index') }}"
                },
                {
                    name: "معايير الجودة",
                    icon: "ruler",
                    iconBg: "#E3F2FD",
                    iconColor: "#1565C0",
                    route: "{{ route('quality.standards.index') }}"
                },
                {
                    name: "عدم المطابقة (NCR)",
                    icon: "alert-triangle",
                    iconBg: "#FFF3E0",
                    iconColor: "#E65100",
                    route: "{{ route('quality.ncr.index') }}"
                },
                {
                    name: "الإجراءات التصحيحية",
                    icon: "wrench",
                    iconBg: "#F3E5F5",
                    iconColor: "#7B1FA2",
                    route: "{{ route('quality.capa.index') }}"
                },
                {
                    name: "تتبع الدفعات",
                    icon: "barcode",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('quality.batches.index') }}"
                },
                {
                    name: "تقييم الموردين",
                    icon: "star",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('quality.suppliers.index') }}"
                },
                {
                    name: "الشهادات والامتثال",
                    icon: "award",
                    iconBg: "#E0F2F1",
                    iconColor: "#00695C",
                    route: "{{ route('quality.certificates.index') }}"
                },
                {
                    name: "التدقيق الداخلي",
                    icon: "search",
                    iconBg: "#E3F2FD",
                    iconColor: "#1565C0",
                    route: "{{ route('quality.audits.index') }}"
                },
                {
                    name: "تقارير الجودة",
                    icon: "chart-pie",
                    iconBg: "#FFEBEE",
                    iconColor: "#C62828",
                    route: "{{ route('quality.reports') }}"
                }
            ]
        },
        {
            groupName: "التقارير",
            groupIcon: "file-bar-chart",
            groupColor: "#6A1B9A",
            apps: [
                {
                    name: "محلل العمل اليومي",
                    icon: "bar-chart-3",
                    iconBg: "#F3E5F5",
                    iconColor: "#6A1B9A",
                    route: "{{ route('reports.overall') }}"
                },
                {
                    name: "شجرة الحسابات",
                    icon: "git-branch",
                    iconBg: "#E8F5E9",
                    iconColor: "#2E7D32",
                    route: "{{ route('reports.accounts-tree') }}"
                },
                {
                    name: "الميزانية العمومية",
                    icon: "scale",
                    iconBg: "#E3F2FD",
                    iconColor: "#1565C0",
                    route: "{{ route('reports.general-balance-sheet') }}"
                },
                {
                    name: "أرباح وخسائر",
                    icon: "trending-up",
                    iconBg: "#E8F5E9",
                    iconColor: "#2E7D32",
                    route: "{{ route('reports.general-profit-loss-report') }}"
                },
                {
                    name: "تقارير المبيعات",
                    icon: "shopping-cart",
                    iconBg: "#FFF3E0",
                    iconColor: "#E65100",
                    route: "{{ route('reports.sales.total') }}"
                },
                {
                    name: "تقارير المشتريات",
                    icon: "shopping-bag",
                    iconBg: "#FFF8E1",
                    iconColor: "#F57F17",
                    route: "{{ route('reports.purchases.total') }}"
                },
                {
                    name: "تقارير المخزون",
                    icon: "package",
                    iconBg: "#E0F2F1",
                    iconColor: "#00695C",
                    route: "{{ route('reports.general-inventory-report') }}"
                },
                {
                    name: "تقارير المصروفات",
                    icon: "file-text",
                    iconBg: "#FFEBEE",
                    iconColor: "#C62828",
                    route: "{{ route('reports.expenses-balance-report') }}"
                }
            ]
        }
    ];

    // Function to create app card HTML
    function createAppCard(app) {
        const badge = app.isNew ? '<span class="new-badge">جديد 🎉</span>' : '';
        return `
        <a href="${app.route}" class="app-card">
            ${badge}
            <div class="app-icon" style="background-color: ${app.iconBg};">
                <i data-lucide="${app.icon}" style="color: ${app.iconColor}; width: 20px; height: 20px; stroke-width: 2.5;"></i>
            </div>
            <p class="app-name">${app.name}</p>
        </a>
    `;
    }

    // Function to create group section HTML
    function createGroupSection(group, index) {
        const appsHTML = group.apps.map(app => createAppCard(app)).join('');
        return `
        <div class="app-group" style="animation-delay: ${index * 0.1}s;" data-group-index="${index}">
            <div class="group-header">
                <div class="group-icon-wrapper" style="background: ${group.groupColor}20;">
                    <i data-lucide="${group.groupIcon}" style="color: ${group.groupColor}; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                <h2 class="group-title">${group.groupName}</h2>
                <div class="group-count">${group.apps.length}</div>
                <div class="group-toggle" title="طي/فتح المجموعة">
                    <i data-lucide="chevron-up" class="toggle-icon" style="color: ${group.groupColor}; width: 20px; height: 20px; stroke-width: 2.5;"></i>
                </div>
            </div>
            <div class="group-apps-grid">
                ${appsHTML}
            </div>
        </div>
    `;
    }

    // Initialize the dashboard
    function initDashboard() {
        const appsGrid = document.getElementById('appsGrid');

        // Generate HTML for all groups with staggered animation
        const groupsHTML = appsGroups.map((group, index) => createGroupSection(group, index)).join('');
        appsGrid.innerHTML = groupsHTML;

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Add interactive effects to groups
        addGroupInteractivity();
    }

    // Add interactive effects to groups
    function addGroupInteractivity() {
        const groups = document.querySelectorAll('.app-group');

        groups.forEach(group => {
            const toggleBtn = group.querySelector('.group-toggle');
            const toggleIcon = group.querySelector('.toggle-icon');
            const appsGrid = group.querySelector('.group-apps-grid');

            if (toggleBtn && appsGrid) {
                // Toggle functionality
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleGroup(group, appsGrid, toggleIcon);
                });

                // Make the whole header clickable for better UX
                const header = group.querySelector('.group-header');
                header.style.cursor = 'pointer';

                header.addEventListener('click', function(e) {
                    // Only toggle if not clicking on an interactive element
                    if (!e.target.closest('.group-toggle') &&
                        !e.target.closest('.app-card')) {
                        toggleGroup(group, appsGrid, toggleIcon);
                    }
                });
            }
        });
    }

    // Toggle group visibility
    function toggleGroup(group, appsGrid, toggleIcon) {
        const isCollapsed = group.classList.contains('collapsed');

        if (isCollapsed) {
            // Expand
            group.classList.remove('collapsed');
            appsGrid.style.maxHeight = appsGrid.scrollHeight + 'px';
            appsGrid.style.opacity = '1';
            appsGrid.style.marginTop = '0';

            // Rotate icon
            if (toggleIcon) {
                toggleIcon.style.transform = 'rotate(0deg)';
            }

            setTimeout(() => {
                appsGrid.style.maxHeight = 'none';
                appsGrid.style.overflow = 'visible';
            }, 300);
        } else {
            // Collapse
            group.classList.add('collapsed');
            appsGrid.style.maxHeight = appsGrid.scrollHeight + 'px';
            appsGrid.style.overflow = 'hidden';

            // Force reflow
            appsGrid.offsetHeight;

            appsGrid.style.maxHeight = '0px';
            appsGrid.style.opacity = '0';
            appsGrid.style.marginTop = '-1rem';

            // Rotate icon
            if (toggleIcon) {
                toggleIcon.style.transform = 'rotate(180deg)';
            }
        }
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initDashboard();
        initSearch();
    });

    // Reinitialize icons if Lucide loads after DOM
    window.addEventListener('load', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function handleAppClick(route) {
        window.location.href = route;
    }

    // Search functionality
    function initSearch() {
        const searchInput = document.getElementById('searchInput');
        const searchCount = document.getElementById('searchCount');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            // Get all groups
            const groups = document.querySelectorAll('.app-group');

            groups.forEach(group => {
                const appCards = group.querySelectorAll('.app-card');
                let groupHasVisibleCards = false;

                appCards.forEach(card => {
                    const appName = card.querySelector('.app-name').textContent.toLowerCase();
                    const groupTitle = group.querySelector('.group-title').textContent.toLowerCase();

                    if (appName.includes(searchTerm) || groupTitle.includes(searchTerm)) {
                        card.style.display = '';
                        card.style.animation = 'fadeIn 0.3s ease';
                        visibleCount++;
                        groupHasVisibleCards = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Hide/show group based on visible cards
                if (groupHasVisibleCards || !searchTerm) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });

            // Update count
            if (searchTerm) {
                searchCount.textContent = `${visibleCount} نتيجة`;
                searchCount.style.display = 'inline-block';
            } else {
                searchCount.style.display = 'none';
            }
        });

        // Clear search on Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
                this.blur();
            }
        });
    }

    // Fade in animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    `;
    document.head.appendChild(style);

    // Logout confirmation with animation
    const logoutForm = document.getElementById('logoutForm');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Create particles effect
            const btn = this.querySelector('.logout-btn');
            const rect = btn.getBoundingClientRect();
            createLogoutParticles(rect.left + rect.width / 2, rect.top + rect.height / 2);

            // Show loading state
            btn.innerHTML = `
                <i data-lucide="loader-2" class="logout-icon" style="animation: spin 1s linear infinite;"></i>
                <span class="logout-text">جارِ تسجيل الخروج...</span>
            `;
            lucide.createIcons();

            // Submit after animation
            setTimeout(() => {
                this.submit();
            }, 600);
        });
    }

    // Create logout particles
    function createLogoutParticles(x, y) {
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.style.position = 'fixed';
            particle.style.width = '6px';
            particle.style.height = '6px';
            particle.style.borderRadius = '50%';
            particle.style.background = '#ff3b30';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '9999';
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';

            const angle = (Math.PI * 2 * i) / 15;
            const velocity = 50 + Math.random() * 50;
            const tx = Math.cos(angle) * velocity;
            const ty = Math.sin(angle) * velocity;

            particle.animate([
                { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                { transform: `translate(${tx}px, ${ty}px) scale(0)`, opacity: 0 }
            ], {
                duration: 800,
                easing: 'cubic-bezier(0, .9, .57, 1)'
            });

            document.body.appendChild(particle);
            setTimeout(() => particle.remove(), 800);
        }
    }

    // Add spin animation for loader
    const spinStyle = document.createElement('style');
    spinStyle.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(spinStyle);
</script>
