<style>
    /* ==========================================
       Alpine.js - Hide elements until ready
       ========================================== */
    [x-cloak] {
        display: none !important;
    }

    /* ==========================================
       Form Layout & Structure
       ========================================== */
    .employee-form-container {
        font-family: 'Cairo', sans-serif;
        direction: rtl;
    }

    .employee-form-tabs .nav-link {
        transition: all 0.2s ease;
    }

    .employee-form-tabs .nav-link:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .employee-form-tabs .nav-link.active {
        font-weight: 700;
        background-color: var(--color-primary-100, #b3f0e0) !important;
        color: var(--color-primary-800, #15674b) !important;
        border-color: var(--color-primary-200, #80e6cb) !important;
    }

    .employee-form-tabs .nav-link.text-danger {
        border-bottom: 2px solid #dc3545;
    }
    
    /* Error badge styling on tabs */
    .nav-tabs .nav-link .badge.bg-danger {
        animation: pulse-error 2s infinite;
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    
    @keyframes pulse-error {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }
    
    /* Tab with errors - red border indicator */
    .nav-tabs .nav-link.text-danger:not(.active) {
        border-bottom: 2px solid #dc3545 !important;
        position: relative;
    }
    
    .nav-tabs .nav-link.text-danger:not(.active)::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: 0;
        left: 0;
        height: 2px;
        background-color: #dc3545;
    }
    
    /* Active tabs in view mode */
    .nav-tabs .nav-link.active {
        background-color: var(--color-primary-100, #b3f0e0) !important;
        color: var(--color-primary-800, #15674b) !important;
        border-color: var(--color-primary-200, #80e6cb) !important;
    }
    
    /* Hover effect for active tabs */
    .nav-tabs .nav-link.active:hover {
        background-color: var(--color-primary-200, #80e6cb) !important;
        color: var(--color-primary-900, #0e4c35) !important;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            font-size: 0.875rem;
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            white-space: nowrap;
        }
    }
    
    @media (max-width: 576px) {
        .card-header h6 {
            font-size: 0.9rem;
        }
    }
    
    /* Accessibility */
    .btn:focus,
    .form-control:focus,
    .form-select:focus {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }
    
    /* Loading states */
    .btn[wire\:loading] {
        position: relative;
        pointer-events: none;
    }
    
    .btn[wire\:loading]::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.5);
        border-radius: inherit;
    }
    
    /* Dropdown items hover effect */
    .kpi-dropdown-item:hover,
    .leave-type-dropdown-item:hover {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .kpi-dropdown-item:hover small,
    .leave-type-dropdown-item:hover small {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .hover-bg-light:hover {
        background-color: #f8f9fa !important;
    }
    
    /* ==========================================
       Dropdown Z-Index Fix - Simple & Effective
       ========================================== */
    
    /* Ensure all parent containers allow overflow */
    .card,
    .card-body,
    .row,
    [class*="col-"],
    .container-fluid,
    .tab-content {
        overflow: visible !important;
    }
    
    /* Dropdown Container - Create new stacking context with highest priority */
    .kpi-dropdown-container,
    .leave-type-dropdown-container {
        position: relative !important;
        overflow: visible !important;
        z-index: 99999 !important;
        isolation: isolate;
    }
    
    /* Employee Dropdown - Always on top - Highest z-index */
    .employee-dropdown {
        z-index: 999999 !important;
        position: absolute !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
        border-radius: 0.375rem !important;
        background-color: white !important;
    }
    
    /* Ensure dropdowns work even in hidden tabs */
    .tab-pane .kpi-dropdown-container,
    .tab-pane .leave-type-dropdown-container {
        position: relative !important;
    }
    
    /* Ensure dropdowns are properly styled */
    .tab-pane.show.active .employee-dropdown {
        position: absolute !important;
    }
    
    /* Hide dropdowns when not shown */
    .employee-dropdown[x-cloak] {
        display: none !important;
    }
    
    /* Force ALL cards to lower z-index - Must be below dropdown */
    .card,
    .card-body,
    .card.border-success,
    .card.border-primary,
    .card.shadow-sm,
    .card.border-0,
    .card.h-100 {
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Force rows and columns to not interfere */
    .row,
    .row.g-3,
    .row.g-3.mb-3,
    [class*="col-"],
    .col-md-4,
    .col-sm-6,
    .col-md-8 {
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Force ALL elements in the same card-body as dropdown to lower z-index */
    /* Target the outer card-body that contains both dropdown and template */
    .card-body:has(.kpi-dropdown-container) > *:not(.kpi-dropdown-container):not(.leave-type-dropdown-container),
    .card-body:has(.leave-type-dropdown-container) > *:not(.kpi-dropdown-container):not(.leave-type-dropdown-container),
    .card-body:has(.kpi-dropdown-container) > *:not(.kpi-dropdown-container):not(.leave-type-dropdown-container) *,
    .card-body:has(.leave-type-dropdown-container) > *:not(.kpi-dropdown-container):not(.leave-type-dropdown-container) * {
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Force template content (cards that appear after selection) to lower z-index */
    /* These are the cards that appear when kpiIds.length > 0 */
    template[x-if] ~ div,
    template[x-if] ~ div *,
    template[x-if] ~ div .card,
    template[x-if] ~ div .row .card,
    template[x-if] ~ div .row.g-3 .card,
    template[x-if] ~ div .row.g-3.mb-3 .card,
    template[x-if] ~ div .col-md-4 .card,
    template[x-if] ~ div .col-sm-6 .card {
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Ensure parent card-body doesn't create stacking context that interferes */
    .card-body:has(.kpi-dropdown-container),
    .card-body:has(.leave-type-dropdown-container) {
        position: relative !important;
        z-index: auto !important;
        isolation: auto !important;
    }
    
    /* Force nested card-body (inside border-primary card) to not interfere */
    .card.border-primary .card-body {
        position: relative !important;
        z-index: auto !important;
        isolation: auto !important;
    }
    
    /* Table responsive */
    .table-responsive {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    /* Cursor pointer utility */
    .cursor-pointer {
        cursor: pointer !important;
    }
    
    /* ==========================================
       Fix Empty Space in Tabs
       ========================================== */
    
    /* Remove unnecessary padding/margin from tab content */
    #employeeFormTabsContent .tab-pane {
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
    }
    
    /* Ensure tab content doesn't have extra space */
    #employeeFormTabsContent .tab-pane > div {
        margin-bottom: 0;
    }
    
    /* Remove extra padding from card-body in tabs */
    #employeeFormTabsContent .tab-pane .card-body {
        padding: 1rem !important;
    }
    
    /* Ensure tab content container doesn't add extra space */
    .tab-content {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Fix for empty space in form layout */
    .card-body form {
        margin-bottom: 0;
    }
    
    /* Remove extra margin from last element in tabs */
    #employeeFormTabsContent .tab-pane > div:last-child {
        margin-bottom: 0;
    }
</style>
