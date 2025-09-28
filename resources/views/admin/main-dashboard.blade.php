<title>Massar | Dashboard</title>
<!-- Lucide Icons CDN -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fafafa;
        direction: rtl;
        min-height: 100vh;
        color: #1a1a1a;
    }

    .dashboard-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .apps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1.5rem;
        max-width: 100%;
    }

    @media (min-width: 768px) {
        .apps-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .apps-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .apps-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    .app-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .app-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .app-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem auto;
        transition: transform 0.2s ease;
    }

    .app-card:hover .app-icon {
        transform: scale(1.1);
    }

    .app-name {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25;
        color: #374151;
    }

    .header-section {
        background: #fafafa;
        backdrop-filter: blur(10px);
        border-radius: 2rem;
        padding: 3rem 2rem;
        margin-bottom: 3rem;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .header-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        /* background: linear-gradient(45deg, transparent, rgba(255, 193, 7, 0.1), transparent); */
        animation: shimmer 3s ease-in-out infinite;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .dashboard-container {
            padding: 1rem;
        }

        .apps-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .app-card {
            padding: 1rem;
        }

        .app-icon {
            width: 40px;
            height: 40px;
        }

        .header-section {
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
        }


    }
</style>
<div class="floating-dots">
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
</div>
<div class="dashboard-container">
    <div class="header-section">
        <h1 class="main-title">Massar </h1>
        <h1 class="main-title">عمّلك باكمله في منصة واحدة.</h1>
        <p class="subtitle">
            بسيط، ذو <span class="highlight-text">كفاءة عالية</span>، و <span class="highlight-text">بأقل
                التكاليف!</span>
        </p>
    </div>
    <br>
    <div class="apps-grid" id="appsGrid">
        <!-- Apps will be populated by JavaScript -->
    </div>
</div>
<script>
    const appsData = [
        // Row 1
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
            name: "الخصومات",
            icon: "tag",
            iconBg: "#E3F2FD",
            iconColor: "#1565C0",
            route: "{{ route('discounts.index') }}"
        },
        {
            name: "التصنيع",
            icon: "factory",
            iconBg: "#E0F2F1",
            iconColor: "#00695C",
            route: "{{ route('manufacturing.create') }}"
        },
        {
            name: "الصلاحيات",
            icon: "key",
            iconBg: "#FFF8E1",
            iconColor: "#F57F17",
            route: "{{ route('users.index') }}"
        },

        // Row 2
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
            name: "عمليات الاصول",
            icon: "building",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "{{ route('multi-vouchers.index', ['type' => 'basic_journal']) }}"
        },
        {
            name: "أدارة الحسابات",
            icon: "file-text",
            iconBg: "#FFF3E0",
            iconColor: "#E65100",
            route: "{{ route('journals.index', ['type' => 'basic_journal']) }}"
        },
        {
            name: "المشاريع",
            icon: "kanban",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "{{ route('projects.index') }}"
        },

        // Row 4
        {
            name: "الموارد البشريه",
            icon: "users",
            iconBg: "#E3F2FD",
            iconColor: "#1565C0",
            route: "{{ route('employees.index') }}"
        },
        {
            name: "الاعدادات",
            icon: "settings",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "{{ route('export-settings') }}"
        },
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
            name: "نقطة البيع",
            icon: "shopping-cart",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "{{ route('pos.index') }}"
        },
        {
            name: "التقدم اليومي ",
            icon: "bar-chart-3",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "{{ route('progress.projcet.index') }}"

        },
        {
            name: "Inquiries",
            icon: "layers",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "{{ route('inquiries.index') }}"
        },
    ];

    // Function to create app card HTML
    function createAppCard(app) {
        return `
        <a href="${app.route}" class="app-card">
            <div class="app-icon" style="background-color: ${app.iconBg};">
                <i data-lucide="${app.icon}" style="color: ${app.iconColor}; width: 24px; height: 24px;"></i>
            </div>
            <p class="app-name">${app.name}</p>
        </a>
    `;
    }

    // Initialize the dashboard
    function initDashboard() {
        const appsGrid = document.getElementById('appsGrid');

        // Generate HTML for all apps
        const appsHTML = appsData.map(app => createAppCard(app)).join('');
        appsGrid.innerHTML = appsHTML;

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initDashboard();
    });

    // Reinitialize icons if Lucide loads after DOM
    window.addEventListener('load', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function handleAppClick(appName, route) {
        // تحديد نوع القسم المطلوب إظهاره في السايدبار
        const sidebarSections = {

            'الرئيسيه': 'all',
            'البيانات الاساسيه': 'accounts',
            'الاصناف': 'items',
            'الخصومات': 'discounts',
            'التصنيع': 'manufacturing',
            'الصلاحيات': 'permissions',
            'CRM': 'crm',
            'المبيعات': 'sales-invoices',
            'المشتريات': 'purshase-invoices',
            'ادارة المخزون': 'invoices',
            'السندات الماليه': 'vouchers',
            'التحويلات النقديه': 'transfers',
            'رواتب الموظفين': 'multi-vouchers',
            'الاستحقاقات': 'contract-journals',
            'عمليات الاصول': 'depreciation-journals',
            'أدارة الحسابات': 'basic_journal-journals',
            'المشاريع': 'projects',
            'الموارد البشريه': 'departments',
            'الاعدادات': 'settings',
            'ادارة المستأجرات': 'rentals',
            'الصيانه': 'service',
            'أدارة الشحن': 'shipping',
            'نقطة البيع': 'POS',
            'التقدم اليومي ': 'daily_progress',
            'Inquiries': 'inquiries',
            // أضف باقي الأقسام...
        };

        const sectionType = sidebarSections[appName] || 'all';

        // إعادة التوجيه مع تمرير نوع القسم
        window.location.href = `${route}?sidebar=${sectionType}`;
    }

    // تحديث إنشاء البطاقات
    function createAppCard(app) {
        return `
        <div class="app-card" onclick="handleAppClick('${app.name}', '${app.route}')">
            <div class="app-icon" style="background-color: ${app.iconBg};">
                <i data-lucide="${app.icon}" style="color: ${app.iconColor}; width: 24px; height: 24px;"></i>
            </div>
            <p class="app-name">${app.name}</p>
        </div>
    `;
    }
</script>
