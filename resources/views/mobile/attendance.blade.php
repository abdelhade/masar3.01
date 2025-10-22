<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>تسجيل البصمة - Massar ERP</title>
    
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .attendance-container {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 10px;
            padding-top: 15px;
            padding-bottom: 15px;
        }
        
        .attendance-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 20px 18px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            margin: 0 auto;
        }
        
        .attendance-header {
            margin-bottom: 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            gap: 15px;
        }
        
        .attendance-title {
            color: #333;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
            line-height: 1.3;
            flex: 1;
        }
        
        .attendance-subtitle {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: white;
            font-size: 28px;
        }
        
        .user-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }
        
        .user-id {
            color: #666;
            font-size: 13px;
        }
        
        .user-details {
            margin-top: 8px;
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }
        
        .user-details div {
            margin-bottom: 2px;
        }
        
        .attendance-type {
            margin-bottom: 20px;
        }
        
        .type-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .type-btn {
            flex: 1;
            padding: 14px 12px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            min-height: 70px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .type-btn.active {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
        
        .type-btn:hover:not(.active) {
            border-color: #28a745;
            transform: translateY(-2px);
        }
        
        .type-icon {
            font-size: 22px;
            margin-bottom: 6px;
            display: block;
        }
        
        .type-label {
            font-size: 13px;
            font-weight: bold;
        }
        
        .attendance-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-checkin {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
        }
        
        .attendance-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .attendance-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .logout-icon-btn {
            width: 38px;
            height: 38px;
            border: 2px solid #dc3545;
            border-radius: 50%;
            background: transparent;
            color: #dc3545;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .logout-icon-btn:hover:not(:disabled) {
            background: #dc3545;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
        }
        
        .logout-icon-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .location-info {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: right;
        }
        
        .location-icon {
            color: #1976d2;
            margin-left: 6px;
        }
        
        .location-text {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            line-height: 1.3;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 18px 0;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #28a745;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status-message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .last-attendance {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 12px;
            margin-top: 15px;
            margin-bottom: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .last-attendance-title {
            font-size: 14px;
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .last-attendance-title::before {
            content: "📅";
            font-size: 16px;
            margin-left: 5px;
        }
        
        .last-attendance-info {
            font-size: 12px;
            color: #856404;
            line-height: 1.5;
        }
        
        /* تحسينات للشاشات الصغيرة جداً */
        @media (max-width: 360px) {
            .attendance-container {
                padding: 8px;
                padding-top: 12px;
                padding-bottom: 12px;
            }
            
            .attendance-card {
                padding: 18px 15px;
                border-radius: 15px;
                margin-bottom: 8px;
            }
            
            .attendance-title {
                font-size: 20px;
            }
            
            .attendance-subtitle {
                font-size: 12px;
            }
            
            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .user-name {
                font-size: 15px;
            }
            
            .user-id {
                font-size: 12px;
            }
            
            .user-details {
                font-size: 10px;
            }
            
            .type-btn {
                padding: 12px 10px;
                min-height: 65px;
            }
            
            .type-icon {
                font-size: 20px;
            }
            
            .type-label {
                font-size: 12px;
            }
            
            .attendance-btn {
                padding: 14px;
                font-size: 15px;
                min-height: 45px;
            }
            
            .logout-icon-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .header-content {
                gap: 10px;
            }
        }
        
        /* تحسينات للشاشات المتوسطة */
        @media (min-width: 361px) and (max-width: 480px) {
            .attendance-container {
                padding-top: 15px;
                padding-bottom: 15px;
            }
            
            .attendance-card {
                padding: 20px 18px;
                margin-bottom: 12px;
            }
            
            .attendance-title {
                font-size: 21px;
            }
            
            .user-avatar {
                width: 65px;
                height: 65px;
                font-size: 26px;
            }
            
            .type-btn {
                padding: 13px 11px;
                min-height: 68px;
            }
            
            .attendance-btn {
                padding: 15px;
                min-height: 48px;
            }
        }
        
        /* تحسينات للشاشات الكبيرة */
        @media (min-width: 481px) {
            .attendance-container {
                padding-top: 20px;
                padding-bottom: 20px;
            }
            
            .attendance-card {
                padding: 25px 22px;
                margin-bottom: 15px;
            }
            
            .attendance-title {
                font-size: 24px;
            }
            
            .attendance-subtitle {
                font-size: 14px;
            }
            
            .user-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            
            .user-name {
                font-size: 18px;
            }
            
            .user-id {
                font-size: 14px;
            }
            
            .user-details {
                font-size: 12px;
            }
            
            .type-btn {
                padding: 15px;
                min-height: 75px;
            }
            
            .type-icon {
                font-size: 24px;
            }
            
            .type-label {
                font-size: 14px;
            }
            
            .attendance-btn {
                padding: 18px;
                font-size: 18px;
                min-height: 55px;
            }
        }
        
        /* تحسينات للوضع الأفقي */
        @media (max-height: 600px) and (orientation: landscape) {
            .attendance-container {
                padding: 8px;
                padding-top: 12px;
                padding-bottom: 12px;
                align-items: flex-start;
            }
            
            .attendance-card {
                padding: 12px;
                margin-bottom: 8px;
            }
            
            .attendance-header {
                margin-bottom: 15px;
            }
            
            .attendance-title {
                font-size: 18px;
                margin-bottom: 5px;
            }
            
            .attendance-subtitle {
                font-size: 12px;
            }
            
            .user-info {
                padding: 12px;
                margin-bottom: 15px;
            }
            
            .user-avatar {
                width: 50px;
                height: 50px;
                font-size: 20px;
                margin-bottom: 8px;
            }
            
            .user-name {
                font-size: 14px;
            }
            
            .user-id {
                font-size: 11px;
            }
            
            .user-details {
                font-size: 10px;
                margin-top: 5px;
            }
            
            .attendance-type {
                margin-bottom: 15px;
            }
            
            .type-buttons {
                gap: 8px;
                margin-bottom: 12px;
            }
            
            .type-btn {
                padding: 10px 8px;
                min-height: 55px;
            }
            
            .type-icon {
                font-size: 18px;
                margin-bottom: 4px;
            }
            
            .type-label {
                font-size: 11px;
            }
            
            .attendance-btn {
                padding: 12px;
                font-size: 14px;
                margin-bottom: 10px;
                min-height: 40px;
            }
            
            .location-info {
                padding: 8px;
                margin-bottom: 12px;
            }
            
            .last-attendance {
                padding: 12px;
                margin-top: 15px;
                margin-bottom: 10px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .last-attendance-title {
                font-size: 12px;
            }
            
            .last-attendance-title::before {
                font-size: 14px;
            }
            
            .last-attendance-info {
                font-size: 10px;
            }
        }
        
        /* تحسينات للتفاعل */
        .type-btn:focus,
        .attendance-btn:focus,
        .logout-icon-btn:focus {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
        
        /* تحسينات للخط */
        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
        }
        
        /* تحسينات إضافية للاستجابة */
        @media (max-width: 320px) {
            .attendance-container {
                padding: 6px;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            
            .attendance-card {
                padding: 15px 12px;
                margin-bottom: 6px;
            }
            
            .type-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .type-btn {
                min-height: 60px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 8px;
                align-items: center;
            }
            
            .attendance-title {
                text-align: center;
            }
            
            .last-attendance {
                padding: 12px;
                margin-top: 15px;
                margin-bottom: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .last-attendance-title {
                font-size: 13px;
            }
            
            .last-attendance-title::before {
                font-size: 15px;
            }
            
            .last-attendance-info {
                font-size: 11px;
            }
        }
        
        /* تحسينات للتفاعل مع اللمس */
        .type-btn,
        .attendance-btn,
        .logout-icon-btn {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .type-btn:active {
            transform: translateY(0) scale(0.98);
        }
        
        .attendance-btn:active {
            transform: translateY(0) scale(0.98);
        }
        
        .logout-icon-btn:active {
            transform: scale(0.95);
        }
        
        /* تحسينات للوضع المظلم */
        @media (prefers-color-scheme: dark) {
            .attendance-card {
                background: #1a1a1a;
                color: #ffffff;
            }
            
            .attendance-title {
                color: #ffffff;
            }
            
            .attendance-subtitle {
                color: #cccccc;
            }
            
            .user-info {
                background: #2a2a2a;
                border: 1px solid #444444;
            }
            
            .user-name {
                color: #ffffff;
            }
            
            .user-id {
                color: #cccccc;
            }
            
            .user-details {
                color: #cccccc;
            }
            
            .type-btn {
                background: #2a2a2a;
                border-color: #444444;
                color: #ffffff;
            }
            
            .type-btn:hover:not(.active) {
                border-color: #28a745;
                background: #2a2a2a;
            }
            
            .location-info {
                background: #2a2a2a;
                border: 1px solid #444444;
            }
            
            .last-attendance {
                background: #2a2a2a;
                border-color: #444444;
            }
            
            .last-attendance-title {
                color: #ffc107;
            }
            
            .last-attendance-info {
                color: #cccccc;
            }
        }
        
        /* تحسينات للشاشات عالية الدقة */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .user-avatar,
            .type-icon {
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        }
        
        /* تحسينات للوصولية */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* تحسينات للشاشات الصغيرة جداً مع دعم اللمس */
        @media (max-width: 320px) and (pointer: coarse) {
            .type-btn {
                min-height: 48px;
            }
            
            .attendance-btn {
                min-height: 48px;
                font-size: 16px;
            }
            
            .logout-icon-btn {
                width: 44px;
                height: 44px;
            }
        }
        
        /* تحسينات للشاشات الكبيرة جداً */
        @media (min-width: 768px) {
            .attendance-container {
                padding: 25px;
                padding-top: 30px;
                padding-bottom: 30px;
            }
            
            .attendance-card {
                max-width: 500px;
                padding: 35px 30px;
                margin-bottom: 15px;
            }
            
            .attendance-title {
                font-size: 28px;
            }
            
            .attendance-subtitle {
                font-size: 16px;
            }
            
            .user-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }
            
            .user-name {
                font-size: 20px;
            }
            
            .user-id {
                font-size: 16px;
            }
            
            .user-details {
                font-size: 14px;
            }
            
            .type-btn {
                padding: 20px;
                min-height: 90px;
            }
            
            .type-icon {
                font-size: 28px;
            }
            
            .type-label {
                font-size: 16px;
            }
            
            .attendance-btn {
                padding: 20px;
                font-size: 20px;
                min-height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="attendance-container">
        <div class="attendance-card">
            <div class="attendance-header">
                <div class="header-content">
                    <h1 class="attendance-title">تسجيل البصمة</h1>
                    <button class="logout-icon-btn" id="logout-btn" onclick="logoutEmployee()" title="خروج">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
                <p class="attendance-subtitle">اختر نوع البصمة وسجل حضورك</p>
            </div>
            
            <!-- معلومات المستخدم -->
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name" id="employeeName">المستخدم</div>
                <div class="user-id">رقم الموظف: <span id="employeeId">-</span></div>
                <div class="user-details">
                    <div>رقم البصمة: <span id="fingerPrintId">-</span></div>
                    <div>اسم البصمة: <span id="fingerPrintName">-</span></div>
                    <div>المنصب: <span id="employeePosition">-</span></div>
                    <div>القسم: <span id="employeeDepartment">-</span></div>
                </div>
            </div>
            
            <!-- معلومات الموقع -->
            <div class="location-info" id="location-info" style="display: none;">
                <i class="fas fa-map-marker-alt location-icon"></i>
                <span id="location-address">جاري تحديد الموقع...</span>
                <div class="location-text" id="location-coordinates"></div>
            </div>
            
            <!-- رسالة توضيحية للموقع -->
            <div class="location-help" style="
                background: #fff3cd; 
                border: 1px solid #ffeaa7; 
                border-radius: 8px; 
                padding: 10px; 
                margin-bottom: 15px; 
                font-size: 12px; 
                color: #856404;
                text-align: right;
                display: none;
            " id="location-help">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>تنبيه مهم:</strong> تحديد الموقع إجباري لتسجيل البصمة. يرجى السماح بالوصول للموقع في إعدادات المتصفح. 
                إذا كنت تستخدم HTTP، قد تحتاج إلى استخدام HTTPS لتحديد الموقع.
            </div>
            
            <!-- نوع البصمة -->
            <div class="attendance-type">
                <div class="type-buttons">
                    <div class="type-btn active" data-type="check_in">
                        <i class="fas fa-sign-in-alt type-icon"></i>
                        <div class="type-label">دخول</div>
                    </div>
                    <div class="type-btn" data-type="check_out">
                        <i class="fas fa-sign-out-alt type-icon"></i>
                        <div class="type-label">خروج</div>
                    </div>
                </div>
            </div>
            
            <!-- زر تسجيل البصمة -->
            <button class="attendance-btn btn-checkin" id="attendance-btn" onclick="recordAttendance()" disabled>
                <i class="fas fa-fingerprint"></i>
                <span>انتظار تحديد الموقع...</span>
            </button>
            
            <!-- Loading -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <div>جاري تسجيل البصمة...</div>
            </div>
            
            <!-- رسالة الحالة -->
            <div id="status-message" class="status-message" style="display: none;"></div>
            <div id="errorMessage" class="status-message status-error" style="display: none;"></div>
            
            <!-- آخر بصمة -->
            <div class="last-attendance" id="last-attendance">
                <div class="last-attendance-title">آخر بصمة</div>
                <div class="last-attendance-info" id="last-attendance-info">
                    <i class="fas fa-spinner fa-spin"></i> جاري تحميل آخر بصمة...
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedType = 'check_in';
        let currentLocation = null;
        let locationTracker = null;
        
        // تهيئة الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            checkEmployeeAuth();
        });
        
        // التحقق من تسجيل دخول الموظف
        async function checkEmployeeAuth() {
            try {
                const response = await fetch('/api/employee/check-auth', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success && result.data.logged_in) {
                    // الموظف مسجل دخول
                    currentEmployee = result.data.employee;
                    updateEmployeeInfo();
                    initializePage();
                    setupEventListeners();
                    getCurrentLocation();
                    loadLastAttendance();
                } else {
                    // الموظف غير مسجل دخول
                    showError('يرجى تسجيل الدخول أولاً');
                    setTimeout(() => {
                        window.location.href = '/mobile/employee-login';
                    }, 2000);
                }
                
            } catch (error) {
                console.error('خطأ في التحقق من تسجيل الدخول:', error);
                showError('خطأ في الاتصال. يرجى المحاولة مرة أخرى');
                setTimeout(() => {
                    window.location.href = '/mobile/employee-login';
                }, 2000);
            }
        }
        
        function initializePage() {
            // إخفاء شريط التنقل في المتصفح
            if (window.navigator.standalone === true) {
                document.body.classList.add('standalone');
            }
            
            // منع التمرير
            document.body.style.overflow = 'hidden';
        }
        
        function setupEventListeners() {
            // تغيير نوع البصمة
            document.querySelectorAll('.type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // إزالة active من جميع الأزرار
                    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
                    
                    // إضافة active للزر المحدد
                    this.classList.add('active');
                    
                    // تحديث النوع المحدد
                    selectedType = this.dataset.type;
                    
                    // تحديث زر التسجيل
                    updateAttendanceButton();
                });
            });
        }
        
        function updateAttendanceButton() {
            const btn = document.getElementById('attendance-btn');
            
            if (selectedType === 'check_in') {
                btn.className = 'attendance-btn btn-checkin';
                btn.innerHTML = '<i class="fas fa-fingerprint"></i><span>تسجيل دخول</span>';
            } else {
                btn.className = 'attendance-btn btn-checkout';
                btn.innerHTML = '<i class="fas fa-fingerprint"></i><span>تسجيل خروج</span>';
            }
        }
        
        // تحديث معلومات الموظف
        function updateEmployeeInfo() {
            if (currentEmployee) {
                document.getElementById('employeeName').textContent = currentEmployee.name;
                document.getElementById('employeeId').textContent = currentEmployee.id;
                document.getElementById('fingerPrintId').textContent = currentEmployee.finger_print_id;
                document.getElementById('fingerPrintName').textContent = currentEmployee.finger_print_name;
                document.getElementById('employeePosition').textContent = currentEmployee.position || 'غير محدد';
                document.getElementById('employeeDepartment').textContent = currentEmployee.department?.name || 'غير محدد';
            }
        }
        
        // دالة للحصول على موقع أكثر دقة
        async function getAccurateLocation() {
            return new Promise((resolve, reject) => {
                let attempts = 0;
                const maxAttempts = 3;
                const minAccuracy = 200; // دقة مقبولة بالمتر (أكثر واقعية)
                
                function tryGetLocation() {
                    attempts++;
                    
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            // التحقق من دقة الموقع
                            if (position.coords.accuracy <= minAccuracy || attempts >= maxAttempts) {
                                console.log(`الموقع محدد بدقة: ${position.coords.accuracy}m (محاولة ${attempts})`);
                                resolve(position);
                            } else {
                                console.log(`دقة الموقع ضعيفة: ${position.coords.accuracy}m (محاولة ${attempts})`);
                                if (attempts < maxAttempts) {
                                    // محاولة أخرى مع إعدادات مختلفة
                                    setTimeout(tryGetLocation, 2000);
                                } else {
                                    // قبول الموقع حتى لو كانت الدقة ضعيفة
                                    console.log('قبول الموقع بالدقة المتاحة');
                                    resolve(position);
                                }
                            }
                        },
                        (error) => {
                            if (attempts < maxAttempts) {
                                console.log(`فشل محاولة ${attempts}، إعادة المحاولة...`);
                                setTimeout(tryGetLocation, 2000);
                            } else {
                                reject(error);
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 20000,
                            maximumAge: 0 // عدم استخدام الموقع المخزن
                        }
                    );
                }
                
                tryGetLocation();
            });
        }

        async function getCurrentLocation() {
            try {
                if (!navigator.geolocation) {
                    throw new Error('المتصفح لا يدعم تحديد الموقع');
                }
                
                // فحص إذا كان الموقع يستخدم HTTPS
                if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                    console.warn('تحديد الموقع يتطلب HTTPS في الإنتاج');
                }
                
                // إظهار رسالة تحميل
                showLocationLoading();
                
                // محاولة الحصول على موقع أكثر دقة
                const position = await getAccurateLocation();
                
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                
                // إخفاء رسالة المساعدة عند نجاح تحديد الموقع
                document.getElementById('location-help').style.display = 'none';
                
                // الحصول على العنوان من Google Maps
                await getAddressFromCoordinates(currentLocation.latitude, currentLocation.longitude);
                
                // إظهار معلومات الموقع
                document.getElementById('location-info').style.display = 'block';
                
                // تفعيل زر تسجيل البصمة
                enableAttendanceButton();
                
            } catch (error) {
                console.error('خطأ في تحديد الموقع:', error);
                showLocationError(error);
                disableAttendanceButton();
            }
        }
        
        async function getAddressFromCoordinates(lat, lng) {
            try {
                // فحص إذا كان مفتاح Google Maps متوفر
                const apiKey = '{{ config("services.google_maps.api_key") }}';
                if (!apiKey || apiKey === '') {
                    console.warn('مفتاح Google Maps API غير متوفر');
                    document.getElementById('location-address').textContent = 'الموقع محدد بنجاح';
                    document.getElementById('location-coordinates').textContent = 
                        `إحداثيات: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    return;
                }
                
                // تحسين الإحداثيات لتقليل التباين
                const roundedLat = Math.round(lat * 1000000) / 1000000; // 6 خانات عشرية
                const roundedLng = Math.round(lng * 1000000) / 1000000;
                
                const response = await fetch(
                    `https://maps.googleapis.com/maps/api/geocode/json?latlng=${roundedLat},${roundedLng}&key=${apiKey}&language=ar&result_type=street_address|route|locality|administrative_area_level_1|country`
                );
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status === 'OK' && data.results.length > 0) {
                    // اختيار العنوان الأكثر تفصيلاً
                    let address = data.results[0].formatted_address;
                    
                    // إذا كان العنوان طويل جداً، اختصار العنوان
                    if (address.length > 100) {
                        // البحث عن عنوان أقصر
                        for (let i = 1; i < data.results.length && i < 3; i++) {
                            if (data.results[i].formatted_address.length <= 100) {
                                address = data.results[i].formatted_address;
                                break;
                            }
                        }
                    }
                    
                    document.getElementById('location-address').textContent = address;
                    
                    // إضافة تحذير إذا كانت الدقة ضعيفة
                    let accuracyText = `إحداثيات: ${roundedLat.toFixed(6)}, ${roundedLng.toFixed(6)} (دقة: ${currentLocation.accuracy.toFixed(1)}m)`;
                    if (currentLocation.accuracy > 100) {
                        accuracyText += ' ⚠️';
                    }
                    document.getElementById('location-coordinates').textContent = accuracyText;
                    
                    currentLocation.address = address;
                    currentLocation.latitude = roundedLat;
                    currentLocation.longitude = roundedLng;
                } else {
                    console.warn('Google Maps API error:', data.status, data.error_message);
                    document.getElementById('location-address').textContent = 'الموقع محدد بنجاح';
                    
                    let accuracyText = `إحداثيات: ${roundedLat.toFixed(6)}, ${roundedLng.toFixed(6)} (دقة: ${currentLocation.accuracy.toFixed(1)}m)`;
                    if (currentLocation.accuracy > 100) {
                        accuracyText += ' ⚠️';
                    }
                    document.getElementById('location-coordinates').textContent = accuracyText;
                }
            } catch (error) {
                console.error('خطأ في الحصول على العنوان:', error);
                document.getElementById('location-address').textContent = 'الموقع محدد بنجاح';
                
                let accuracyText = `إحداثيات: ${lat.toFixed(6)}, ${lng.toFixed(6)} (دقة: ${currentLocation.accuracy.toFixed(1)}m)`;
                if (currentLocation.accuracy > 100) {
                    accuracyText += ' ⚠️';
                }
                document.getElementById('location-coordinates').textContent = accuracyText;
            }
        }
        
        function showLocationLoading() {
            document.getElementById('location-info').style.display = 'block';
            document.getElementById('location-address').innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري تحديد الموقع...';
            document.getElementById('location-coordinates').textContent = 'يرجى الانتظار...';
            
            // تعطيل زر تسجيل البصمة أثناء تحديد الموقع
            disableAttendanceButton();
        }
        
        function showLocationError(error) {
            document.getElementById('location-info').style.display = 'block';
            document.getElementById('location-help').style.display = 'block';
            
            // تعطيل زر تسجيل البصمة عند فشل تحديد الموقع
            disableAttendanceButton();
            
            let errorMessage = 'خطأ في تحديد الموقع';
            let errorDetails = 'تحديد الموقع إجباري لتسجيل البصمة';
            
            if (error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'تم رفض الوصول للموقع';
                        errorDetails = 'يرجى السماح بالوصول للموقع في إعدادات المتصفح - الموقع إجباري';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'الموقع غير متاح';
                        errorDetails = 'تأكد من تشغيل GPS أو خدمة الموقع - الموقع إجباري';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'انتهت مهلة تحديد الموقع';
                        errorDetails = 'يرجى المحاولة مرة أخرى - الموقع إجباري';
                        break;
                    default:
                        errorMessage = 'خطأ في تحديد الموقع';
                        errorDetails = (error.message || 'تحديد الموقع إجباري لتسجيل البصمة');
                        break;
                }
            }
            
            document.getElementById('location-address').innerHTML = `
                <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> ${errorMessage}
                <br><button onclick="retryLocation()" style="
                    background: #dc3545; 
                    color: white; 
                    border: none; 
                    padding: 5px 10px; 
                    border-radius: 5px; 
                    margin-top: 5px; 
                    font-size: 12px;
                    cursor: pointer;
                ">إعادة المحاولة</button>
            `;
            document.getElementById('location-coordinates').textContent = errorDetails;
        }
        
        function retryLocation() {
            getCurrentLocation();
        }
        
        function enableAttendanceButton() {
            const btn = document.getElementById('attendance-btn');
            btn.disabled = false;
            btn.style.opacity = '1';
            updateAttendanceButton();
        }
        
        function disableAttendanceButton() {
            const btn = document.getElementById('attendance-btn');
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.innerHTML = '<i class="fas fa-fingerprint"></i><span>انتظار تحديد الموقع...</span>';
        }
        
        // دالة للتحقق من صحة الموقع
        function validateLocation(location) {
            if (!location || !location.latitude || !location.longitude) {
                return false;
            }
            
            // التحقق من صحة الإحداثيات
            if (location.latitude < -90 || location.latitude > 90) {
                return false;
            }
            
            if (location.longitude < -180 || location.longitude > 180) {
                return false;
            }
            
            // التحقق من دقة الموقع (يجب أن تكون أقل من 500 متر - أكثر واقعية)
            if (location.accuracy > 500) {
                console.warn(`دقة الموقع ضعيفة جداً: ${location.accuracy}m`);
                return false;
            }
            
            // تحذير إذا كانت الدقة ضعيفة ولكن مقبولة
            if (location.accuracy > 100) {
                console.warn(`دقة الموقع ضعيفة ولكن مقبولة: ${location.accuracy}m`);
            }
            
            return true;
        }
        
        async function recordAttendance() {
            if (!currentLocation) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'تحديد الموقع إجباري',
                    text: 'لا يمكن تسجيل البصمة بدون تحديد الموقع. هل تريد المحاولة مرة أخرى؟',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، حدد الموقع',
                    cancelButtonText: 'إلغاء',
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d'
                });
                
                if (result.isConfirmed) {
                    // محاولة تحديد الموقع
                    await getCurrentLocation();
                    if (!currentLocation) {
                        // إذا فشل تحديد الموقع مرة أخرى، منع التسجيل
                        Swal.fire({
                            icon: 'error',
                            title: 'لا يمكن التسجيل',
                            text: 'تحديد الموقع إجباري لتسجيل البصمة. يرجى المحاولة لاحقاً أو التحقق من إعدادات الموقع.',
                            confirmButtonText: 'حسناً',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                } else {
                    return; // المستخدم اختار الإلغاء
                }
            }
            
            // إظهار Loading
            showLoading(true);
            
            try {
                // التحقق من صحة الموقع
                if (!validateLocation(currentLocation)) {
                    throw new Error('الموقع غير صحيح أو غير دقيق بما فيه الكفاية (دقة الموقع: ' + currentLocation.accuracy.toFixed(1) + ' متر)');
                }
                
                // تحذير المستخدم إذا كانت الدقة ضعيفة
                if (currentLocation.accuracy > 100) {
                    console.warn(`تحذير: دقة الموقع ضعيفة (${currentLocation.accuracy.toFixed(1)}m) ولكن مقبولة للتسجيل`);
                }
                
                // إعداد البيانات - الموقع إجباري
                const attendanceData = {
                    type: selectedType,
                    location: JSON.stringify({
                        latitude: currentLocation.latitude,
                        longitude: currentLocation.longitude,
                        accuracy: currentLocation.accuracy,
                        address: currentLocation.address || null,
                        timestamp: new Date().toISOString()
                    }),
                    notes: 'تم التسجيل من الموبايل مع تحديد الموقع'
                };
                
                // إرسال البيانات للخادم
                const response = await fetch('/api/attendance/record', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(attendanceData)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // نجح التسجيل
                    showSuccessMessage(result.message);
                    loadLastAttendance();
                    
                    // إظهار رسالة نجاح
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح!',
                        text: result.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                } else {
                    // فشل التسجيل
                    throw new Error(result.message || 'حدث خطأ في تسجيل البصمة');
                }
                
            } catch (error) {
                console.error('خطأ في تسجيل البصمة:', error);
                
                showErrorMessage(error.message);
                
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: error.message,
                    confirmButtonText: 'حسناً'
                });
            } finally {
                showLoading(false);
            }
        }
        
        function showLoading(show) {
            const loading = document.getElementById('loading');
            const btn = document.getElementById('attendance-btn');
            const logoutBtn = document.getElementById('logout-btn');
            
            if (show) {
                loading.style.display = 'block';
                btn.disabled = true;
                btn.style.opacity = '0.6';
                if (logoutBtn) {
                    logoutBtn.disabled = true;
                    logoutBtn.style.opacity = '0.6';
                }
            } else {
                loading.style.display = 'none';
                btn.disabled = false;
                btn.style.opacity = '1';
                if (logoutBtn) {
                    logoutBtn.disabled = false;
                    logoutBtn.style.opacity = '1';
                }
            }
        }
        
        function showSuccessMessage(message) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.className = 'status-message status-success';
            statusDiv.textContent = message;
            statusDiv.style.display = 'block';
            
            // إخفاء الرسالة بعد 5 ثواني
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 5000);
        }
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                
                // إخفاء الرسالة بعد 5 ثواني
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 5000);
            }
        }
        
        function showErrorMessage(message) {
            showError(message);
        }
        
        async function loadLastAttendance() {
            try {
                const response = await fetch('/api/attendance/last', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                const lastAttendanceInfo = document.getElementById('last-attendance-info');
                
                if (response.ok && result.success && result.data) {
                    const attendance = result.data;
                    
                    const typeText = attendance.type === 'check_in' ? 'دخول' : 'خروج';
                    const date = new Date(attendance.date).toLocaleDateString('ar-SA');
                    const time = attendance.time;
                    
                    lastAttendanceInfo.innerHTML = `
                        <strong>${typeText}</strong> - ${date} في ${time}<br>
                        <small>الحالة: ${getStatusText(attendance.status)}</small>
                    `;
                } else {
                    // لا توجد بصمة سابقة
                    lastAttendanceInfo.innerHTML = `
                        <i class="fas fa-info-circle" style="color: #6c757d;"></i>
                        لا توجد بصمة سابقة
                    `;
                }
            } catch (error) {
                console.error('خطأ في تحميل آخر بصمة:', error);
                const lastAttendanceInfo = document.getElementById('last-attendance-info');
                lastAttendanceInfo.innerHTML = `
                    <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                    خطأ في تحميل آخر بصمة
                `;
            }
        }
        
        function getStatusText(status) {
            switch (status) {
                case 'pending': return 'قيد المراجعة';
                case 'approved': return 'معتمد';
                case 'rejected': return 'مرفوض';
                default: return status;
            }
        }
        
        // منع إغلاق الصفحة أثناء التحميل
        window.addEventListener('beforeunload', function(e) {
            const loading = document.getElementById('loading');
            if (loading.style.display === 'block') {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // تحديث الموقع كل 5 دقائق
        setInterval(getCurrentLocation, 5 * 60 * 1000);
        
        // دالة تسجيل خروج الموظف
        async function logoutEmployee() {
            try {
                // تأكيد من المستخدم
                const result = await Swal.fire({
                    title: 'تأكيد الخروج',
                    text: 'هل أنت متأكد من تسجيل الخروج؟',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، خروج',
                    cancelButtonText: 'إلغاء'
                });
                
                if (result.isConfirmed) {
                    // إظهار Loading
                    showLoading(true);
                    const logoutBtn = document.getElementById('logout-btn');
                    logoutBtn.disabled = true;
                    
                    // إرسال طلب تسجيل الخروج
                    const response = await fetch('/api/employee/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // نجح تسجيل الخروج
                        Swal.fire({
                            icon: 'success',
                            title: 'تم تسجيل الخروج',
                            text: 'تم تسجيل خروجك بنجاح',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // الانتقال لصفحة تسجيل الدخول
                            window.location.href = '/mobile/employee-login';
                        });
                    } else {
                        throw new Error(data.message || 'حدث خطأ في تسجيل الخروج');
                    }
                }
                
            } catch (error) {
                showError('حدث خطأ في تسجيل الخروج. يرجى المحاولة مرة أخرى');
                
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ في تسجيل الخروج',
                    confirmButtonText: 'حسناً'
                });
            } finally {
                showLoading(false);
                const logoutBtn = document.getElementById('logout-btn');
                if (logoutBtn) {
                    logoutBtn.disabled = false;
                }
            }
        }
    </script>
</body>
</html>
