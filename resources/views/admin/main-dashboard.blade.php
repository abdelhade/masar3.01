<title>Massar | Dashboard</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ auth()->id() }}">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<!-- External Stylesheets -->
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-main.css') }}">

<!-- Lucide Icons CDN -->
<script src="{{ asset('assets/js/lucide.js') }}"></script>
<div class="dashboard-container">
    <div class="header-section">
        <h1 class="main-title"> Massar ERP</h1>
        <h1 class="main-title">عمّلك بالكامل في منصة واحدة</h1>
        <p class="subtitle">
            إدارة شاملة وذكية لجميع عملياتك - <span class="highlight-text">سريع</span>،
            <span class="highlight-text">مرن</span>، و <span class="highlight-text">سهل الاستخدام</span>
        </p>
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
            route: "{{ route('depreciation.index') }}"
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
        {
            name: "إدارة الشيكات",
            icon: "file-check-2",
            iconBg: "#E8F5E9",
            iconColor: "#2E7D32",
            route: "{{ route('checks.incoming') }}",
            isNew: true
        },
        {
            name: "بصمه الموبايل",
            icon: "fingerprint",
            iconBg: "#E8F5E8",
            iconColor: "#2E7D32",
            route: "{{ route('mobile.employee-login') }}",
            isNew: true
        },
    ];

    // Function to create app card HTML - Dynamic Sidebar Version
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
        const appCards = document.querySelectorAll('.app-card');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            appCards.forEach(card => {
                const appName = card.querySelector('.app-name').textContent.toLowerCase();
                
                if (appName.includes(searchTerm)) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.3s ease';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
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
</script>
