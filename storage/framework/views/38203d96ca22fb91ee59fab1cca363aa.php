<title>Massar | Dashboard</title>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<meta name="user-id" content="<?php echo e(auth()->id()); ?>">

<!-- External Stylesheets -->
<link rel="stylesheet" href="<?php echo e(asset('assets/css/dashboard-main.css')); ?>">

<!-- Lucide Icons CDN -->
<script src="<?php echo e(asset('assets/js/lucide.js')); ?>"></script>
<div class="dashboard-container">
    <div class="header-section">
        <h1 class="main-title"> Massar ERP</h1>
        <h1 class="main-title">عمّلك بالكامل في منصة واحدة</h1>
        <p class="subtitle">
            إدارة شاملة وذكية لجميع عملياتك - <span class="highlight-text">سريع</span>،
            <span class="highlight-text">مرن</span>، و <span class="highlight-text">سهل الاستخدام</span>
        </p>
        <div class="mt-3">

        </div>
    </div>

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
            route: "<?php echo e(route('home')); ?>"
        },
        {
            name: "البيانات الاساسيه",
            icon: "chart-bar-increasing",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "<?php echo e(route('accounts.index')); ?>"
        },
        {
            name: "الاصناف",
            icon: "boxes",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "<?php echo e(route('items.index')); ?>"
        },
        {
            name: "الخصومات",
            icon: "tag",
            iconBg: "#E3F2FD",
            iconColor: "#1565C0",
            route: "<?php echo e(route('discounts.index')); ?>"
        },
        {
            name: "التصنيع",
            icon: "factory",
            iconBg: "#E0F2F1",
            iconColor: "#00695C",
            route: "<?php echo e(route('manufacturing.create')); ?>"
        },
        {
            name: "الصلاحيات",
            icon: "key",
            iconBg: "#FFF8E1",
            iconColor: "#F57F17",
            route: "<?php echo e(route('users.index')); ?>"
        },

        // Row 2
        {
            name: "CRM",
            icon: "user-cog",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "<?php echo e(route('statistics.index')); ?>"
        },
        {
            name: "المبيعات",
            icon: "trending-up",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "<?php echo e(route('invoices.index', ['type' => 10])); ?>"
        },
        {
            name: "المشتريات",
            icon: "shopping-bag",
            iconBg: "#FFF3E0",
            iconColor: "#E65100",
            route: "<?php echo e(route('invoices.index', ['type' => 11])); ?>"
        },
        {
            name: "ادارة المخزون",
            icon: "package",
            iconBg: "#FFF8E1",
            iconColor: "#F57F17",
            route: "<?php echo e(route('invoices.index', ['type' => 18])); ?>"
        },

        {
            name: "السندات الماليه",
            icon: "receipt",
            iconBg: "#E0F2F1",
            iconColor: "#00695C",
            route: "<?php echo e(route('vouchers.index')); ?>"
        },


        {
            name: "التحويلات النقديه",
            icon: "arrow-left-right",
            iconBg: "#E3F2FD",
            iconColor: "#1565C0",
            route: "<?php echo e(route('transfers.index')); ?>"
        },
        {
            name: "رواتب الموظفين",
            icon: "id-card",
            iconBg: "#FFF8E1",
            iconColor: "#F57F17",
            route: "<?php echo e(route('multi-vouchers.index')); ?>"
        },
        {
            name: "الاستحقاقات",
            icon: "wallet",
            iconBg: "#E0F2F1",
            iconColor: "#00695C",
            route: "<?php echo e(route('journals.index')); ?>"
        },
        {
            name: "عمليات الاصول",
            icon: "building",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "<?php echo e(route('depreciation.index')); ?>"
        },
        {
            name: "أدارة الحسابات",
            icon: "file-text",
            iconBg: "#FFF3E0",
            iconColor: "#E65100",
            route: "<?php echo e(route('journals.index', ['type' => 'basic_journal'])); ?>"
        },
        {
            name: "المشاريع",
            icon: "kanban",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "<?php echo e(route('projects.index')); ?>"
        },

        // Row 4
        {
            name: "الموارد البشريه",
            icon: "users",
            iconBg: "#E3F2FD",
            iconColor: "#1565C0",
            route: "<?php echo e(route('employees.index')); ?>"
        },
        {
            name: "الاعدادات",
            icon: "settings",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "<?php echo e(route('export-settings')); ?>"
        },
        {
            name: "ادارة المستأجرات",
            icon: "building",
            iconBg: "#FFF8E1",
            iconColor: "#F57F17",
            route: "<?php echo e(route('rentals.buildings.index')); ?>"
        },
        {
            name: "الصيانه",
            icon: "package",
            iconBg: "#E0F2F1",
            iconColor: "#00695C",
            route: "<?php echo e(route('service.types.index')); ?>"
        },
        {
            name: "أدارة الشحن",
            icon: "truck",
            iconBg: "#FFF3E0",
            iconColor: "#E65100",
            route: "<?php echo e(route('orders.index')); ?>"

        },
        {
            name: "نقطة البيع",
            icon: "shopping-cart",
            iconBg: "#F3E5F5",
            iconColor: "#7B1FA2",
            route: "<?php echo e(route('pos.index')); ?>"
        },
        {
            name: "التقدم اليومي ",
            icon: "bar-chart-3",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "<?php echo e(route('progress.projcet.index')); ?>"

        },
        {
            name: "Inquiries",
            icon: "layers",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "<?php echo e(route('inquiries.index')); ?>"
        },
        {
            name: "إدارة الشيكات",
            icon: "file-check-2",
            iconBg: "#E8F5E9",
            iconColor: "#2E7D32",
            route: "<?php echo e(route('checks.incoming')); ?>",
            isNew: true
        },
    ];

    // Function to create app card HTML - Dynamic Sidebar Version
    function createAppCard(app) {
        const badge = app.isNew ? '<span class="new-badge">جديد</span>' : '';
        return `
        <a href="${app.route}" class="app-card">
            ${badge}
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

    function handleAppClick(route) {
        window.location.href = route;
    }
</script>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/admin/main-dashboard.blade.php ENDPATH**/ ?>