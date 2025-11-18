@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();

    // Define all app groups with permissions
    $appsGroupsData = [
        [
            'groupName' => 'الإعدادات الأساسية',
            'groupIcon' => 'settings',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'الرئيسيه',
                    'icon' => 'home',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('home'),
                    'permission' => null, // Always visible
                ],
                [
                    'name' => 'البيانات الاساسيه',
                    'icon' => 'chart-bar-increasing',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('accounts.index'),
                    'permission' => 'view basicData-statistics',
                ],
                [
                    'name' => 'الاصناف',
                    'icon' => 'boxes',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('items.index'),
                    'permission' => 'view items',
                ],
                [
                    'name' => 'الصلاحيات',
                    'icon' => 'key',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('users.index'),
                    'permission' => 'view Users',
                ],
                [
                    'name' => 'الاعدادات',
                    'icon' => 'settings',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('export-settings'),
                    'permission' => 'view Settings',
                ],
            ],
        ],
        [
            'groupName' => 'المبيعات والمشتريات',
            'groupIcon' => 'shopping-bag',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'CRM',
                    'icon' => 'user-cog',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('statistics.index'),
                    'permission' => 'view CRM Statistics',
                ],
                [
                    'name' => 'المبيعات',
                    'icon' => 'trending-up',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('invoices.index', ['type' => 10]),
                    'permission' => 'view Sales',
                ],
                [
                    'name' => 'المشتريات',
                    'icon' => 'shopping-bag',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('invoices.index', ['type' => 11]),
                    'permission' => 'view Purchases',
                ],
                [
                    'name' => 'ادارة المخزون',
                    'icon' => 'package',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('invoices.index', ['type' => 18]),
                    'permission' => 'view Inventory',
                ],
                [
                    'name' => 'نقطة البيع',
                    'icon' => 'shopping-cart',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
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
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('vouchers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التحويلات النقديه',
                    'icon' => 'arrow-left-right',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('transfers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'رواتب الموظفين',
                    'icon' => 'id-card',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('multi-vouchers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'الاستحقاقات',
                    'icon' => 'wallet',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('journals.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'أدارة الحسابات',
                    'icon' => 'file-text',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('journals.index', ['type' => 'basic_journal']),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'إدارة الشيكات',
                    'icon' => 'file-check-2',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('checks.incoming'),
                    'permission' => 'view check-portfolios-incoming',
                    'isNew' => true,
                ],
            ],
        ],
        [
            'groupName' => 'المشاريع والإنتاج',
            'groupIcon' => 'kanban',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'المشاريع',
                    'icon' => 'kanban',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('projects.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التصنيع',
                    'icon' => 'factory',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('manufacturing.create'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التقدم اليومي',
                    'icon' => 'bar-chart-3',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('progress.projcet.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'عمليات الاصول',
                    'icon' => 'building',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('depreciation.index'),
                    'permission' => 'view assets',
                ],
            ],
        ],
        [
            'groupName' => 'الموارد البشرية',
            'groupIcon' => 'users',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'الموارد البشريه',
                    'icon' => 'users',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('employees.index'),
                    'permission' => 'view Employees',
                ],
                [
                    'name' => 'بصمه الموبايل',
                    'icon' => 'fingerprint',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('mobile.employee-login'),
                    'permission' => null,
                ],
            ],
        ],
        [
            'groupName' => 'الخدمات والعمليات',
            'groupIcon' => 'truck',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'ادارة المستأجرات',
                    'icon' => 'building',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('rentals.buildings.index'),
                    'permission' => 'view rentables',
                ],
                [
                    'name' => 'الصيانه',
                    'icon' => 'package',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('service.types.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'أدارة الشحن',
                    'icon' => 'truck',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('orders.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'Inquiries',
                    'icon' => 'layers',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('inquiries.index'),
                    'permission' => 'view Inquiries',
                ],
            ],
        ],
        [
            'groupName' => 'إدارة الجودة',
            'groupIcon' => 'award',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'لوحة تحكم الجودة',
                    'icon' => 'chart-line',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.dashboard'),
                    'permission' => 'view Dashboard',
                    'isNew' => true,
                ],
                [
                    'name' => 'فحوصات الجودة',
                    'icon' => 'clipboard-check',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.inspections.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'معايير الجودة',
                    'icon' => 'ruler',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.standards.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'عدم المطابقة (NCR)',
                    'icon' => 'alert-triangle',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.ncr.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'الإجراءات التصحيحية',
                    'icon' => 'wrench',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.capa.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'تتبع الدفعات',
                    'icon' => 'barcode',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.batches.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'تقييم الموردين',
                    'icon' => 'star',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.suppliers.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'الشهادات والامتثال',
                    'icon' => 'award',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.certificates.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'التدقيق الداخلي',
                    'icon' => 'search',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.audits.index'),
                    'permission' => 'view Dashboard',
                ],
                [
                    'name' => 'تقارير الجودة',
                    'icon' => 'chart-pie',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('quality.reports'),
                    'permission' => 'view Dashboard',
                ],
            ],
        ],
        [
            'groupName' => 'التقارير',
            'groupIcon' => 'file-bar-chart',
            'groupColor' => '#00695C',
            'apps' => [
                [
                    'name' => 'محلل العمل اليومي',
                    'icon' => 'bar-chart-3',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.overall'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'شجرة الحسابات',
                    'icon' => 'git-branch',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.accounts-tree'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'الميزانية العمومية',
                    'icon' => 'scale',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.general-balance-sheet'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'أرباح وخسائر',
                    'icon' => 'trending-up',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.general-profit-loss-report'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المبيعات',
                    'icon' => 'shopping-cart',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.sales.total'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المشتريات',
                    'icon' => 'shopping-bag',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.purchases.total'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المخزون',
                    'icon' => 'package',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
                    'route' => route('reports.general-inventory-report'),
                    'permission' => 'view Reports',
                ],
                [
                    'name' => 'تقارير المصروفات',
                    'icon' => 'file-text',
                    'iconBg' => 'white',
                    'iconColor' => '#00695C',
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

    .title {
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
            <h1 class="title text-white">Massar ERP</h1>
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

    <div class="apps-grid">
        @php
            $groupsData = [
                [
                    'groupName' => 'الإعدادات الأساسية',
                    'groupIcon' => 'settings',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'الرئيسيه', 'icon' => 'home', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('home'), 'permission' => null],
                        ['name' => 'البيانات الاساسيه', 'icon' => 'chart-bar-increasing', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('accounts.index'), 'permission' => null],
                        ['name' => 'الاصناف', 'icon' => 'boxes', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('items.index'), 'permission' => null],
                        
                        ['name' => 'الصلاحيات', 'icon' => 'key', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('users.index'), 'permission' => null],
                        ['name' => 'الاعدادات', 'icon' => 'settings', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('export-settings'), 'permission' => null],
                    ]
                ],
                [
                    'groupName' => ' ادارة المبيعات',
                    'groupIcon' => 'shopping-bag',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'CRM', 'icon' => 'user-cog', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('statistics.index'), 'permission' => null],
                        
                        ['name' => 'المبيعات', 'icon' => 'trending-up', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('invoices.index', ['type' => 10]), 'permission' => null],
                       
                        ['name' => 'نقطة البيع', 'icon' => 'shopping-cart', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('pos.index'), 'permission' => null],
                        ['name' => 'ادارة المستأجرات', 'icon' => 'building', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('rentals.buildings.index'), 'permission' => null],
                    ]
                ],

                [
                    'groupName' => 'المحاسبة والمالية',
                    'groupIcon' => 'wallet',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'أدارة الحسابات', 'icon' => 'file-text', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('journals.index', ['type' => 'basic_journal']), 'permission' => null],

                        ['name' => 'ادارة المصروفات', 'icon' => 'file-text', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.expenses-balance-report'), 'permission' => null],

                        ['name' => 'السندات الماليه', 'icon' => 'receipt', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('vouchers.index'), 'permission' => null],
                        
                        ['name' => 'التحويلات النقديه', 'icon' => 'arrow-left-right', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('transfers.index'), 'permission' => null],

                     
                        ['name' => 'ادارة الدفعات', 'icon' => 'tag', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('installments.plans.index'), 'permission' => null],

                        ['name' => 'إدارة الشيكات', 'icon' => 'file-check-2', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('checks.incoming'), 'isNew' => true, 'permission' => null],
                        
                        ['name' => 'ادارة الملفات', 'icon' => 'file-text', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('home'), 'isNew' => true ,'permission' => null],
                    ]
                ],
              
              
              
                [
                    'groupName' => ' ادارة المخزون و التصنيع',
                    'groupIcon' => 'shopping-bag',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'ادارة المخزون', 'icon' => 'package', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('invoices.index', ['type' => 18]), 'permission' => null],

                        ['name' => 'التصنيع', 'icon' => 'factory', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('manufacturing.create'), 'permission' => null],

                        ['name' => 'المشتريات', 'icon' => 'shopping-bag', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('invoices.index', ['type' => 11]), 'permission' => null],

                        ['name' => 'الصيانه', 'icon' => 'package', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('service.types.index'), 'permission' => null],

                        // ادراة الجودة
                        ['name' => 'لوحة تحكم الجودة', 'icon' => 'chart-line', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.dashboard'), 'isNew' => true, 'permission' => null],

                    ]
                ],
                
              
                [
                    'groupName' => 'المشاريع والإنتاج',
                    'groupIcon' => 'kanban',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'المشاريع', 'icon' => 'kanban', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('projects.index'), 'permission' => null],
                     
                        ['name' => 'التقدم اليومي', 'icon' => 'bar-chart-3', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('progress.projcet.index'), 'permission' => null],
                        ['name' => 'عمليات الاصول', 'icon' => 'building', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('depreciation.index'), 'permission' => null],
                    ]
                ],
                [
                    'groupName' => 'الموارد البشرية',
                    'groupIcon' => 'users',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'الموارد البشريه', 'icon' => 'users', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('employees.index'), 'permission' => null],
                        ['name' => 'بصمه الموبايل', 'icon' => 'fingerprint', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('mobile.employee-login'), 'permission' => null],
                    ]
                ],
                [
                    'groupName' => 'الخدمات والعمليات',
                    'groupIcon' => 'truck',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'ادارة المستأجرات', 'icon' => 'building', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('rentals.buildings.index'), 'permission' => null],
                       
                        ['name' => 'أدارة الشحن', 'icon' => 'truck', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('orders.index'), 'permission' => null],
                        ['name' => 'Inquiries', 'icon' => 'layers', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('inquiries.index'), 'permission' => null],
                    ]
                ],
                [
                    'groupName' => 'إدارة الجودة',
                    'groupIcon' => 'award',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'لوحة تحكم الجودة', 'icon' => 'chart-line', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.dashboard'), 'isNew' => true, 'permission' => null],
                        ['name' => 'فحوصات الجودة', 'icon' => 'clipboard-check', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.inspections.index'), 'permission' => null],
                        ['name' => 'معايير الجودة', 'icon' => 'ruler', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.standards.index'), 'permission' => null],
                        ['name' => 'عدم المطابقة (NCR)', 'icon' => 'alert-triangle', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.ncr.index'), 'permission' => null],
                        ['name' => 'الإجراءات التصحيحية', 'icon' => 'wrench', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.capa.index'), 'permission' => null],
                        ['name' => 'تتبع الدفعات', 'icon' => 'barcode', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.batches.index'), 'permission' => null],
                        ['name' => 'تقييم الموردين', 'icon' => 'star', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.suppliers.index'), 'permission' => null],
                        ['name' => 'الشهادات والامتثال', 'icon' => 'award', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.certificates.index'), 'permission' => null],
                        ['name' => 'التدقيق الداخلي', 'icon' => 'search', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.audits.index'), 'permission' => null],
                        ['name' => 'تقارير الجودة', 'icon' => 'chart-pie', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('quality.reports'), 'permission' => null],
                    ]
                ],
                [
                    'groupName' => 'التقارير',
                    'groupIcon' => 'file-bar-chart',
                    'groupColor' => '#00695C',
                    'apps' => [
                        ['name' => 'محلل العمل اليومي', 'icon' => 'bar-chart-3', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.overall'), 'permission' => null],
                        ['name' => 'شجرة الحسابات', 'icon' => 'git-branch', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.accounts-tree'), 'permission' => null],
                        ['name' => 'الميزانية العمومية', 'icon' => 'scale', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.general-balance-sheet'), 'permission' => null],
                        ['name' => 'أرباح وخسائر', 'icon' => 'trending-up', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.general-profit-loss-report'), 'permission' => null],
                        ['name' => 'تقارير المبيعات', 'icon' => 'shopping-cart', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.sales.total'), 'permission' => null],
                        ['name' => 'تقارير المشتريات', 'icon' => 'shopping-bag', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.purchases.total'), 'permission' => null],
                        ['name' => 'تقارير المخزون', 'icon' => 'package', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.general-inventory-report'), 'permission' => null],
                        ['name' => 'تقارير المصروفات', 'icon' => 'file-text', 'iconBg' => 'white', 'iconColor' => '#00695C', 'route' => route('reports.expenses-balance-report'), 'permission' => null],
                    ]
                ],
            ];
        @endphp

        @foreach($groupsData as $index => $group)
            @php
                // Filter apps based on permissions
                $visibleApps = array_filter($group['apps'], function($app) use ($user) {
                    // If no permission is set, show the app
                    if (!isset($app['permission']) || $app['permission'] === null) {
                        return true;
                    }
                    // Check if user has the permission
                    return $user && $user->can($app['permission']);
                });
            @endphp

            @if(count($visibleApps) > 0)
                <div class="app-group" data-group-index="{{ $index }}">
            <div class="group-header">
                        <div class="group-icon-wrapper" style="background: {{ $group['groupColor'] }}20;">
                            <i data-lucide="{{ $group['groupIcon'] }}" style="color: {{ $group['groupColor'] }}; width: 24px; height: 24px; stroke-width: 2.5;"></i>
                </div>
                        <h2 class="group-title">{{ $group['groupName'] }}</h2>
                        <div class="group-count">{{ count($visibleApps) }}</div>
            </div>
            <div class="group-apps-grid">
                        @foreach($visibleApps as $app)
                            <a href="{{ $app['route'] }}" class="app-card">
                                @if(isset($app['isNew']) && $app['isNew'])
                                    <span class="new-badge">جديد 🎉</span>
                                @endif
                                <div class="app-icon" style="background-color: {{ $app['iconBg'] }};">
                                    <i data-lucide="{{ $app['icon'] }}" style="color: {{ $app['iconColor'] }}; width: 30px; height: 25px; stroke-width: 2.5;font-size: 60px !important; "></i>
            </div>
                                <p class="app-name">{{ $app['name'] }}</p>
                            </a>
                        @endforeach
        </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<script>
    // Initialize Lucide icons when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Reinitialize icons if Lucide loads after DOM
    window.addEventListener('load', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
