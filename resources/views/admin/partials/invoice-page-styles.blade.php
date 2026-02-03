{{-- صفحة الفاتورة: بدون topbar، sidebar مقفول، ارتفاع كامل، scroll داخل الجدول فقط --}}
<style>
    /* ========== Sidebar مقفول ========== */
    body.invoice-page.sidebar-collapsed .left-sidenav {
        width: 80px !important;
        min-width: 80px !important;
        max-width: 80px !important;
        flex: 0 0 80px !important;
    }
    body.invoice-page.sidebar-collapsed .page-wrapper {
        width: calc(100% - 80px) !important;
        max-width: none !important;
        margin-left: 80px !important;  /* يطابق عرض الـ sidebar المقفول - بدون هذا يبقى 230px من القالب الأساسي */
    }
    
    /* إخفاء النصوص في الـ sidebar المقفول وإظهار الأيقونات فقط */
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link {
        justify-content: center !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        text-align: center !important;
        position: relative !important;
        overflow: hidden !important;
    }
    /* إخفاء النصوص في الـ sidebar المقفول */
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link {
        font-size: 0 !important;
        line-height: 0 !important;
        color: transparent !important;
    }
    /* إظهار الأيقونات فقط */
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link i,
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link .menu-icon,
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link [data-feather],
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link svg {
        font-size: 1.5rem !important;
        display: inline-block !important;
        margin: 0 !important;
        width: 24px !important;
        height: 24px !important;
        line-height: 1 !important;
        color: #7081b9 !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: relative !important;
        z-index: 1 !important;
    }
    /* إخفاء النصوص فقط وليس الأيقونات - استخدام text-indent */
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link {
        text-indent: -9999px !important;
    }
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link i,
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link .menu-icon,
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link [data-feather],
    body.invoice-page.sidebar-collapsed .left-sidenav-menu .nav-link svg {
        text-indent: 0 !important;
    }

    /* ========== ارتفاع كامل + توزيع عمودي (لا scroll للصفحة) ========== */
    html, body.invoice-page { height: 100%; }
    body.invoice-page .page-wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding-top: 0 !important;
    }
    body.invoice-page .page-content {
        flex: 1;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    body.invoice-page .page-content .container-fluid {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    body.invoice-page .page-content .container-fluid > .row {
        flex: 1;
        min-height: 0;
        display: flex;
    }
    body.invoice-page .page-content .container-fluid > .row > [class*="col"] {
        min-height: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    body.invoice-page .content-wrapper,
    body.invoice-page section.content {
        height: 100% !important;
        min-height: 0;
        display: flex;
        flex-direction: column;
        padding: 0 !important;
    }
    body.invoice-page section.content > div {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }
    body.invoice-page form.d-flex.flex-column.invoice-form-fullheight,
    body.invoice-page form.d-flex.flex-column.g-0 {
        flex: 1 !important;
        min-height: 0 !important;
        height: 100% !important;
        overflow: hidden;
    }
    /* منطقة جدول الأصناف: تأخذ الباقي والـ scroll داخلها فقط */
    body.invoice-page .flex-grow-1.min-height-0.overflow-hidden {
        flex: 1;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    body.invoice-page .invoice-scroll-container {
        flex: 1;
        min-height: 0;
        overflow-y: auto !important;
        overflow-x: auto;
    }

    /* تكبير الخط في صفحة الفاتورة بالكامل (18px) */
    body.invoice-page,
    body.invoice-page .page-content,
    body.invoice-page .invoice-container,
    body.invoice-page .content-wrapper,
    body.invoice-page .content {
        font-size: 18px;
    }
    body.invoice-page .page-content {
        padding: 0.5rem;
    }
    body.invoice-page .container-fluid {
        max-width: 100%;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    body.invoice-page .content-wrapper,
    body.invoice-page .content {
        padding: 0;
    }
    body.invoice-page .invoice-container {
        width: 100%;
        max-width: 100%;
    }
    /* إزالة كل الـ padding الرأسي حول مساحة جدول الأصناف */
    body.invoice-page .invoice-scroll-container {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    /* تكبير خط الجدول ورأس الفاتورة (18px) */
    body.invoice-page .invoice-data-grid th,
    body.invoice-page .invoice-data-grid td,
    body.invoice-page .invoice-data-grid .form-control,
    body.invoice-page .invoice-data-grid input,
    body.invoice-page .invoice-data-grid select,
    body.invoice-page .invoice-data-grid .static-text {
        font-size: 18px !important;
    }
    body.invoice-page .card .form-label,
    body.invoice-page .invoice-scroll-container .form-control {
        font-size: 18px !important;
    }
    /* تصغير طول الفوتر (قسم الإجماليات والمدفوعات) */
    /* الفوتر: تصغير كل العناصر */
    body.invoice-page #invoice-fixed-footer.invoice-footer-compact,
    body.invoice-page #invoice-fixed-footer {
        padding: 0.35rem 0.5rem !important;
        margin-top: 0;
        font-size: 0.8125rem;
    }
    body.invoice-page #invoice-fixed-footer .row {
        padding: 0 !important;
        margin-bottom: 0.15rem !important;
    }
    body.invoice-page #invoice-fixed-footer .card-body {
        padding: 0.25rem 0.4rem !important;
    }
    body.invoice-page #invoice-fixed-footer .card-header {
        padding: 0.25rem 0.4rem !important;
        font-size: 0.8125rem;
    }
    body.invoice-page #invoice-fixed-footer .form-control-sm-footer,
    body.invoice-page #invoice-fixed-footer .form-control.form-control-sm {
        font-size: 0.8125rem !important;
        height: 1.65rem !important;
        min-height: 1.65rem !important;
        padding: 0.15rem 0.35rem !important;
    }
    body.invoice-page #invoice-fixed-footer .input-group-sm .input-group-text {
        font-size: 0.8125rem;
        padding: 0.15rem 0.35rem;
    }
    body.invoice-page #invoice-fixed-footer .form-group {
        margin-bottom: 0.25rem !important;
    }
    body.invoice-page #invoice-fixed-footer .btn-sm {
        font-size: 0.8125rem;
        padding: 0.25rem 0.5rem;
    }
    body.invoice-page #invoice-fixed-footer .badge {
        font-size: 0.75rem;
    }
    body.invoice-page #invoice-fixed-footer hr {
        margin: 0.25rem 0 !important;
    }
    /* إزالة أي مسافة بالطول في مقابل مساحة جدول الأصناف */
    body.invoice-page .row.flex-grow-1.overflow-hidden {
        margin-bottom: 0 !important;
    }
    body.invoice-page .flex-grow-1.min-height-0.overflow-hidden {
        padding: 0 !important;
        margin: 0 !important;
        min-height: 0;
    }
    body.invoice-page .invoice-scroll-container.card {
        margin: 0 !important;
        border-radius: 0;
    }
    /* تقليل المسافة الرأسية فوق جدول الأصناف */
    body.invoice-page .row.border.border-secondary.rounded.p-3.mb-3 {
        margin-bottom: 0.4rem !important;
        padding: 0.4rem 0.5rem !important;
    }

    /* Dark / Monokai: هيدر وجداول صفحة الفواتير */
    body.invoice-page.theme-dark .table thead th,
    body.invoice-page.theme-dark #invoices-table thead th,
    body.invoice-page.theme-monokai .table thead th,
    body.invoice-page.theme-monokai #invoices-table thead th {
        background-color: var(--masar-sidebar) !important;
        border-color: var(--masar-border) !important;
        color: var(--masar-text) !important;
    }
    body.invoice-page.theme-dark #invoices-table,
    body.invoice-page.theme-dark #invoices-table tbody td,
    body.invoice-page.theme-monokai #invoices-table,
    body.invoice-page.theme-monokai #invoices-table tbody td {
        color: var(--masar-text) !important;
        border-color: var(--masar-border) !important;
    }
    body.invoice-page.theme-dark #invoices-table tbody tr:nth-of-type(odd),
    body.invoice-page.theme-monokai #invoices-table tbody tr:nth-of-type(odd) {
        background-color: var(--masar-card) !important;
    }
    body.invoice-page.theme-dark #invoices-table tbody tr:nth-of-type(even),
    body.invoice-page.theme-monokai #invoices-table tbody tr:nth-of-type(even) {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }
    body.invoice-page.theme-dark #invoices-table .badge.bg-light,
    body.invoice-page.theme-monokai #invoices-table .badge.bg-light {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: var(--masar-text) !important;
    }

    /* Dark / Monokai: جدول أصناف الفاتورة (invoice-data-grid) */
    body.invoice-page.theme-dark .invoice-scroll-container.card,
    body.invoice-page.theme-monokai .invoice-scroll-container.card {
        background-color: var(--masar-card) !important;
        border-color: var(--masar-border) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid th,
    body.invoice-page.theme-monokai .invoice-data-grid th {
        background-color: var(--masar-sidebar) !important;
        border-color: var(--masar-border) !important;
        color: var(--masar-text) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid .invoice-grid-search-row td,
    body.invoice-page.theme-monokai .invoice-data-grid .invoice-grid-search-row td {
        background-color: rgba(0, 0, 0, 0.25) !important;
        border-color: var(--masar-border) !important;
        color: var(--masar-text) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid td,
    body.invoice-page.theme-monokai .invoice-data-grid td {
        border-color: var(--masar-border) !important;
        color: var(--masar-text) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid tbody tr:nth-of-type(odd),
    body.invoice-page.theme-monokai .invoice-data-grid tbody tr:nth-of-type(odd) {
        background-color: var(--masar-card) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid tbody tr:nth-of-type(even),
    body.invoice-page.theme-monokai .invoice-data-grid tbody tr:nth-of-type(even) {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid .form-control,
    body.invoice-page.theme-dark .invoice-data-grid input,
    body.invoice-page.theme-dark .invoice-data-grid select,
    body.invoice-page.theme-monokai .invoice-data-grid .form-control,
    body.invoice-page.theme-monokai .invoice-data-grid input,
    body.invoice-page.theme-monokai .invoice-data-grid select {
        background-color: transparent !important;
        color: var(--masar-text) !important;
        border-color: transparent !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid input:focus,
    body.invoice-page.theme-dark .invoice-data-grid select:focus,
    body.invoice-page.theme-dark .invoice-data-grid .form-control:focus,
    body.invoice-page.theme-monokai .invoice-data-grid input:focus,
    body.invoice-page.theme-monokai .invoice-data-grid select:focus,
    body.invoice-page.theme-monokai .invoice-data-grid .form-control:focus {
        background-color: rgba(96, 165, 250, 0.15) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid input[readonly],
    body.invoice-page.theme-dark .invoice-data-grid input:disabled,
    body.invoice-page.theme-monokai .invoice-data-grid input[readonly],
    body.invoice-page.theme-monokai .invoice-data-grid input:disabled {
        background-color: rgba(255, 255, 255, 0.06) !important;
        color: var(--masar-text-muted) !important;
    }
    body.invoice-page.theme-dark .invoice-data-grid .static-text,
    body.invoice-page.theme-monokai .invoice-data-grid .static-text {
        color: var(--masar-text) !important;
    }
</style>