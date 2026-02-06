<!DOCTYPE html>
<html lang="{{ $currentLocale ?? session('locale', app()->getLocale()) }}" dir="{{ $direction ?? (session('locale', app()->getLocale()) == 'ar' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ __('general.system_name') }}</title>
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap{{ ($currentLocale ?? session('locale', app()->getLocale())) == 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
    
    @stack('styles')
    <style>
    
    .sidebar {
        min-height: 100vh;
        padding: 20px 0;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 5px 10px;
        text-decoration: none;
    }

    .sidebar a i {
        margin-right: 12px;
        font-size: 18px;
    }

    [dir="rtl"] .sidebar a i {
        margin-right: 0;
        margin-left: 12px;
    }

        
        [dir="rtl"] .navbar-nav .dropdown-menu {
            right: auto;
            left: 0;
        }

        [dir="rtl"] .dropdown-menu-end {
            right: 0;
            left: auto;
        }

        [dir="rtl"] .me-auto {
            margin-right: 0 !important;
            margin-left: auto !important;
        }

        [dir="rtl"] .me-2 {
            margin-right: 0 !important;
            margin-left: 0.5rem !important;
        }

        
        .navbar-nav .dropdown-menu {
            min-width: 120px;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        

        
        [dir="rtl"] .dropdown-item .me-2 {
            margin-right: 0 !important;
            margin-left: 0.5rem !important;
        }

        
        .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        
        .sidebar-toggle-btn {
            position: fixed;
            top: 80px;
            left: 20px;
            z-index: 1040;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        [dir="rtl"] .sidebar-toggle-btn {
            left: auto;
            right: 20px;
        }

        .sidebar-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        
        .sidebar-close-btn {
            transition: all 0.3s ease;
        }

        .sidebar-close-btn:hover {
            background-color: #dc3545 !important;
            color: white !important;
            border-color: #dc3545 !important;
            transform: rotate(90deg);
        }

        .sidebar-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        
        .sidebar {
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 60px !important;
            min-width: 60px !important;
        }

        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .sidebar-header h6,
        .sidebar.collapsed .sidebar-close-btn {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-expand-btn {
            display: block !important;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px 0;
            margin: 5px;
            border-radius: 8px;
            position: relative;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 20px;
        }

        
        .sidebar.collapsed .nav-link:hover::after {
            content: attr(data-title);
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0.9;
        }

        [dir="rtl"] .sidebar.collapsed .nav-link:hover::after {
            left: auto;
            right: 70px;
        }
        
        .sidebar-expand-btn {
            border-bottom: 1px solid #dee2e6;
        }

        
        .main-content {
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            flex: 0 0 calc(100% - 60px) !important;
            max-width: calc(100% - 60px) !important;
        }
        
        [dir="rtl"] .main-content.expanded {
            flex: 0 0 calc(100% - 60px) !important;
            max-width: calc(100% - 60px) !important;
        }
        
        
        .container-fluid.sidebar-closed .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        .container-fluid.sidebar-closed .main-content {
            padding-left: 2rem;
            padding-right: 2rem;
        }
        
        @media (max-width: 768px) {
            .container-fluid.sidebar-closed .main-content {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                z-index: 1050;
                transition: left 0.3s ease;
                height: 100vh;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .sidebar.collapsed {
                left: -100% !important;
                width: 280px !important;
            }
            
            #mainContent {
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .main-content.expanded {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        
        @media print {
            .navbar,
            .sidebar,
            nav,
            .breadcrumb-wrapper {
                display: none !important;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
        }

    </style>
</head>
<body >
    @include('progress::layouts.navbar')



    <div class="container-fluid" id="mainContainer">
        <div class="row">
            @include('progress::layouts.sidebar')

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content" id="mainContent">
                @include('progress::partials.alerts')
                    @include('progress::layouts.breadcrumb')
                @yield('content')
            </main>
        </div>
    </div>

    @include('progress::layouts.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('scripts')

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const languageLinks = document.querySelectorAll('a[href*="lang/"]');
            languageLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // إضافة مؤشر تحميل
                    document.body.style.cursor = 'wait';

                    // إعادة تحميل الصفحة بعد تغيير اللغة
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                });
            });
        });


        window.currentLocale = '{{ session("locale", app()->getLocale()) }}';

        // ===================================
        // Dark Mode Toggle Function
        // ===================================
        (function() {
            'use strict';
            
            const STORAGE_KEY = 'darkMode';
            const CLASS_NAME = 'dark-mode';
            
            // تحميل الحالة المحفوظة أو استخدام تفضيلات النظام
            function getInitialMode() {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved) return saved === 'enabled';
                
                // استخدام تفضيلات النظام كافتراضي
                return window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            
            // إصلاح العناصر الديناميكية - إزالة الألوان الثابتة
            function fixDynamicElements() {
                const isDark = document.body.classList.contains(CLASS_NAME);
                if (!isDark) return;
                
                // إزالة text-dark من العناصر الديناميكية
                document.querySelectorAll('.text-dark').forEach(el => {
                    if (!el.closest('.table-dark')) {
                        el.classList.remove('text-dark');
                    }
                });
                
                // إزالة bg-light من العناصر الديناميكية
                document.querySelectorAll('.bg-light').forEach(el => {
                    if (!el.closest('.table-dark')) {
                        el.classList.remove('bg-light');
                        el.classList.add('bg-dark-mode');
                    }
                });
                
                // إزالة bg-white من العناصر الديناميكية
                document.querySelectorAll('.bg-white').forEach(el => {
                    if (!el.closest('.table-dark')) {
                        el.classList.remove('bg-white');
                    }
                });
            }
            
            // تطبيق Dark Mode
            function applyDarkMode(enabled) {
                document.body.classList.toggle(CLASS_NAME, enabled);
                localStorage.setItem(STORAGE_KEY, enabled ? 'enabled' : 'disabled');
                
                // تحديث الأيقونة
                const icon = document.getElementById('darkModeIcon');
                if (icon) {
                    icon.className = enabled ? 'fas fa-sun' : 'fas fa-moon';
                }
                
                // إصلاح العناصر الديناميكية
                setTimeout(fixDynamicElements, 100);
            }
            
            // مراقبة التغييرات في DOM للعناصر الديناميكية
            function observeDOMChanges() {
                const observer = new MutationObserver(function(mutations) {
                    let needsFix = false;
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length > 0) {
                            needsFix = true;
                        }
                    });
                    if (needsFix) {
                        fixDynamicElements();
                    }
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
            
            // تهيئة عند تحميل الصفحة
            document.addEventListener('DOMContentLoaded', function() {
                const isDark = getInitialMode();
                applyDarkMode(isDark);
                
                // بدء مراقبة التغييرات
                observeDOMChanges();
                
                // إضافة مستمع للزر
                const toggle = document.getElementById('darkModeToggle');
                if (toggle) {
                    toggle.addEventListener('click', function() {
                        const newState = !document.body.classList.contains(CLASS_NAME);
                        applyDarkMode(newState);
                    });
                }
            });
            
            // الاستماع لتغييرات تفضيلات النظام (اختياري)
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            systemPrefersDark.addEventListener('change', (e) => {
                // فقط إذا لم يكن المستخدم قد اختار يدوياً
                if (!localStorage.getItem(STORAGE_KEY)) {
                    applyDarkMode(e.matches);
                }
            });
        })();

        // Toggle Sidebar Function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            const mainContent = document.getElementById('mainContent');
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Mobile behavior
                if (sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                } else {
                    sidebar.classList.add('show');
                }
            } else {
                // Desktop behavior
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                    localStorage.setItem('sidebarState', 'open');
                } else {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    localStorage.setItem('sidebarState', 'closed');
                }
            }
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebarMenu');
            const mainContent = document.getElementById('mainContent');
            const savedState = localStorage.getItem('sidebarState');
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Mobile: sidebar hidden by default
                sidebar.classList.remove('show');
            } else {
                // Desktop: restore saved state
                if (savedState === 'closed') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            }
        });
    </script>
</body>
</html>
