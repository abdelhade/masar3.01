<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>تسجيل دخول الموظف - Massar ERP</title>
    
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #000000 0%, #1d1b1d 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px 25px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            margin: 15px;
            position: relative;
        }
        
        .login-header {
            margin-bottom: 25px;
        }
        
        .login-title {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .form-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
            display: block;
            font-size: 14px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            text-align: right;
            width: 100%;
            min-height: 50px;
        }
        
        .form-control:focus {
            border-color: #2d50ec;
            box-shadow: 0 0 0 0.2rem rgba(10, 14, 250, 0.25);
            outline: none;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: stretch;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 12px 12px 0;
            display: flex;
            align-items: center;
            padding: 0 15px;
            min-width: 50px;
            justify-content: center;
        }
        
        .input-group .form-control {
            border-right: none;
            border-radius: 12px 0 0 12px;
            flex: 1;
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, #395dfc 0%, #1c32fa 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .login-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
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
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            display: none;
            font-size: 14px;
            line-height: 1.4;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .field-error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            animation: pulse 1s ease-in-out;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            display: none;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .employee-info {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: right;
            display: none;
        }
        
        .employee-name {
            font-size: 16px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 5px;
        }
        
        .employee-details {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }
        
        .fingerprint-icon {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        /* تحسينات للشاشات الصغيرة جداً */
        @media (max-width: 360px) {
            .login-container {
                margin: 10px;
                padding: 20px 15px;
                border-radius: 15px;
            }
            
            .login-title {
                font-size: 20px;
            }
            
            .login-subtitle {
                font-size: 13px;
            }
            
            .form-control {
                padding: 12px 14px;
                font-size: 15px;
                min-height: 45px;
            }
            
            .login-btn {
                padding: 14px;
                font-size: 15px;
                min-height: 45px;
            }
            
            .fingerprint-icon {
                font-size: 24px;
            }
        }
        
        /* تحسينات للشاشات المتوسطة */
        @media (min-width: 361px) and (max-width: 480px) {
            .login-container {
                margin: 15px;
                padding: 25px 20px;
            }
            
            .login-title {
                font-size: 22px;
            }
            
            .form-control {
                padding: 13px 15px;
                min-height: 48px;
            }
            
            .login-btn {
                padding: 15px;
                min-height: 48px;
            }
        }
        
        /* تحسينات للشاشات الكبيرة */
        @media (min-width: 481px) {
            .login-container {
                padding: 35px 30px;
            }
            
            .login-title {
                font-size: 26px;
            }
            
            .login-subtitle {
                font-size: 15px;
            }
        }
        
        /* تحسينات للوضع الأفقي */
        @media (max-height: 600px) and (orientation: landscape) {
            .login-container {
                margin: 10px;
                padding: 20px;
            }
            
            .login-header {
                margin-bottom: 15px;
            }
            
            .login-title {
                font-size: 20px;
                margin-bottom: 5px;
            }
            
            .login-subtitle {
                font-size: 13px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .form-control {
                padding: 10px 14px;
                min-height: 40px;
            }
            
            .login-btn {
                padding: 12px;
                margin-top: 10px;
                min-height: 40px;
            }
        }
        
        /* تحسينات للتفاعل */
        .form-control:focus,
        .login-btn:focus {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
        
        /* تحسينات للخط */
        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
        }
        
        /* تحسينات للتفاعل مع اللمس */
        .form-control,
        .login-btn {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .form-control:active {
            transform: scale(0.98);
        }
        
        .login-btn:active {
            transform: translateY(0) scale(0.98);
        }
        
        /* تحسينات للوضع المظلم */
        @media (prefers-color-scheme: dark) {
            .login-container {
                background: #1a1a1a;
                color: #ffffff;
            }
            
            .login-title {
                color: #ffffff;
            }
            
            .login-subtitle {
                color: #cccccc;
            }
            
            .form-label {
                color: #ffffff;
            }
            
            .form-control {
                background: #2a2a2a;
                border-color: #444444;
                color: #ffffff;
            }
            
            .form-control:focus {
                background: #2a2a2a;
                border-color: #667eea;
                color: #ffffff;
            }
            
            .input-group-text {
                background: #2a2a2a;
                border-color: #444444;
                color: #cccccc;
            }
            
            .employee-info {
                background: #2a2a2a;
                border: 1px solid #444444;
            }
            
            .employee-name {
                color: #667eea;
            }
            
            .employee-details {
                color: #cccccc;
            }
        }
        
        /* تحسينات للشاشات عالية الدقة */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .fingerprint-icon {
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
            .form-control {
                min-height: 48px;
                font-size: 16px;
            }
            
            .login-btn {
                min-height: 48px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="fingerprint-icon">
                <i class="fas fa-fingerprint"></i>
            </div>
            <h1 class="login-title">تسجيل دخول الموظف</h1>
            <p class="login-subtitle">أدخل بيانات البصمة للدخول</p>
        </div>
        
        <form id="employeeLoginForm">
            <div class="form-group">
                <label class="form-label">رقم البصمة</label>
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           id="fingerPrintId" 
                           name="finger_print_id"
                           placeholder="أدخل رقم البصمة"
                           required>
                    <span class="input-group-text">
                        <i class="fas fa-id-card"></i>
                    </span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">اسم البصمة</label>
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           id="fingerPrintName" 
                           name="finger_print_name"
                           placeholder="أدخل اسم البصمة"
                           required>
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password"
                           placeholder="أدخل كلمة المرور"
                           required>
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span> تسجيل الدخول للبصمه</span>
            </button>
        </form>
        
        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <div>جاري التحقق من البيانات...</div>
        </div>
        
        <!-- Error Message -->
        <div class="error-message" id="errorMessage"></div>
        
        <!-- Success Message -->
        <div class="success-message" id="successMessage"></div>
        
        <!-- Employee Info -->
        <div class="employee-info" id="employeeInfo" style="display: none;">
            <div class="employee-name" id="employeeName"></div>
            <div class="employee-details" id="employeeDetails"></div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // إخفاء رسائل console.log غير المرغوب فيها
        (function() {
            const originalLog = console.log;
            const originalDebug = console.debug;
            const originalWarn = console.warn;
            
            console.log = function(...args) {
                const message = args.join(' ');
                // تجاهل الرسائل التي تحتوي على هذه الكلمات
                if (message.includes('false disabled installed not_installed cannot_run ready_to_run running')) {
                    return;
                }
                originalLog.apply(console, args);
            };
            
            console.debug = function(...args) {
                const message = args.join(' ');
                // تجاهل رسائل debug غير المرغوب فيها
                if (message.includes('false disabled installed not_installed cannot_run ready_to_run running')) {
                    return;
                }
                originalDebug.apply(console, args);
            };
            
            console.warn = function(...args) {
                const message = args.join(' ');
                // تجاهل رسائل warning غير المرغوب فيها
                if (message.includes('false disabled installed not_installed cannot_run ready_to_run running')) {
                    return;
                }
                originalWarn.apply(console, args);
            };
        })();
        
        let currentEmployee = null;
        
        // تهيئة الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
            setupEventListeners();
        });
        
        function initializePage() {
            // إخفاء شريط التنقل في المتصفح
            if (window.navigator.standalone === true) {
                document.body.classList.add('standalone');
            }
            
            // منع التمرير
            document.body.style.overflow = 'hidden';
            
            // التركيز على أول حقل
            const fingerPrintIdField = document.getElementById('fingerPrintId');
            if (fingerPrintIdField) {
                fingerPrintIdField.focus();
            }
            
            // تحسين الأداء - إخفاء رسائل console غير المرغوب فيها
            console.clear();
        }
        
        function setupEventListeners() {
            // تسجيل الدخول
            const loginForm = document.getElementById('employeeLoginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', handleLogin);
            }
            
            // Enter key navigation
            const fingerPrintIdField = document.getElementById('fingerPrintId');
            const fingerPrintNameField = document.getElementById('fingerPrintName');
            const passwordField = document.getElementById('password');
            
            if (fingerPrintIdField) {
                fingerPrintIdField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (fingerPrintNameField) {
                            fingerPrintNameField.focus();
                        }
                    }
                });
            }
            
            if (fingerPrintNameField) {
                fingerPrintNameField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (passwordField) {
                            passwordField.focus();
                        }
                    }
                });
            }
            
            if (passwordField) {
                passwordField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleLogin(e);
                    }
                });
            }
        }
        
        async function handleLogin(e) {
            e.preventDefault();
            
            const fingerPrintId = document.getElementById('fingerPrintId').value.trim();
            const fingerPrintName = document.getElementById('fingerPrintName').value.trim();
            const password = document.getElementById('password').value;
            
            // التحقق من البيانات المطلوبة
            if (!fingerPrintId || !fingerPrintName || !password) {
                let missingFields = [];
                if (!fingerPrintId) missingFields.push('رقم البصمة');
                if (!fingerPrintName) missingFields.push('اسم البصمة');
                if (!password) missingFields.push('كلمة المرور');
                
                showError(`يرجى ملء الحقول التالية: ${missingFields.join('، ')}`);
                
                // التركيز على أول حقل فارغ
                if (!fingerPrintId) {
                    document.getElementById('fingerPrintId').focus();
                } else if (!fingerPrintName) {
                    document.getElementById('fingerPrintName').focus();
                } else if (!password) {
                    document.getElementById('password').focus();
                }
                return;
            }
            
            // إظهار Loading
            showLoading(true);
            hideMessages();
            
            // إضافة timeout للطلب
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 ثانية
            
            try {
                // إرسال بيانات تسجيل الدخول
                const response = await fetch('/api/employee/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        finger_print_id: parseInt(fingerPrintId),
                        finger_print_name: fingerPrintName,
                        password: password
                    }),
                    signal: controller.signal
                });
                
                // إلغاء timeout
                clearTimeout(timeoutId);
                
                let result;
                try {
                    result = await response.json();
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    showError('حدث خطأ في استقبال البيانات من الخادم');
                    return;
                }
                
                if (response.ok && result.success) {
                    // نجح تسجيل الدخول
                    currentEmployee = result.data.employee;
                    showSuccess('تم تسجيل الدخول بنجاح');
                    showEmployeeInfo(currentEmployee);
                    
                    // الانتقال لصفحة تسجيل البصمة بعد ثانيتين
                    setTimeout(() => {
                        window.location.href = `/mobile/attendance?employee_id=${currentEmployee.id}`;
                    }, 2000);
                    
                } else {
                    // فشل تسجيل الدخول - عرض الرسالة الواضحة
                    if (result && result.message) {
                        handleLoginError(result);
                    } else {
                        showError('حدث خطأ في تسجيل الدخول. يرجى المحاولة مرة أخرى');
                    }
                }
                
            } catch (error) {
                console.error('Login error:', error);
                
                // إلغاء timeout
                clearTimeout(timeoutId);
                
                // التحقق من نوع الخطأ
                if (error.name === 'AbortError') {
                    showError('انتهت مهلة الاتصال. يرجى المحاولة مرة أخرى');
                } else if (error.message.includes('HTTP error! status: 401')) {
                    showError('بيانات الدخول غير صحيحة. يرجى التحقق من رقم البصمة واسم البصمة وكلمة المرور');
                } else if (error.message.includes('HTTP error! status: 403')) {
                    showError('حساب الموظف معطل. يرجى التواصل مع الإدارة لتفعيل الحساب');
                } else if (error.message.includes('HTTP error! status: 422')) {
                    showError('بيانات غير صحيحة. يرجى التحقق من جميع الحقول المطلوبة');
                } else if (error.message.includes('HTTP error! status: 500')) {
                    showError('حدث خطأ في الخادم. يرجى المحاولة لاحقاً أو التواصل مع الإدارة');
                } else if (error.message.includes('Failed to fetch')) {
                    showError('لا يمكن الاتصال بالخادم. يرجى التحقق من الاتصال بالإنترنت');
                } else if (error.message.includes('NetworkError')) {
                    showError('خطأ في الشبكة. يرجى التحقق من الاتصال بالإنترنت');
                } else {
                    showError('حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى');
                }
                return; // إيقاف التنفيذ عند حدوث خطأ
            } finally {
                showLoading(false);
            }
        }
        
        function showLoading(show) {
            const loading = document.getElementById('loading');
            const loginBtn = document.getElementById('loginBtn');
            
            if (loading && loginBtn) {
                if (show) {
                    loading.style.display = 'block';
                    loginBtn.disabled = true;
                    loginBtn.style.opacity = '0.6';
                } else {
                    loading.style.display = 'none';
                    loginBtn.disabled = false;
                    loginBtn.style.opacity = '1';
                }
            }
        }
        
        function showError(message) {
            hideMessages(); // إخفاء جميع الرسائل أولاً
            const errorDiv = document.getElementById('errorMessage');
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                
                // إخفاء الرسالة بعد 5 ثواني
                setTimeout(() => {
                    if (errorDiv) {
                        errorDiv.style.display = 'none';
                    }
                }, 5000);
            }
        }
        
        function handleLoginError(result) {
            // التحقق من وجود result
            if (!result) {
                showError('حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى');
                return;
            }
            
            const errorType = result.error_type;
            let message = result.message || 'بيانات الدخول غير صحيحة';
            
            // تحسين الرسائل حسب نوع الخطأ
            if (errorType === 'finger_print_id') {
                message = 'رقم البصمة غير صحيح. يرجى التحقق من رقم البصمة المدخل';
            } else if (errorType === 'finger_print_name') {
                message = 'اسم البصمة غير صحيح. يرجى التحقق من اسم البصمة المدخل';
            } else if (errorType === 'wrong_password') {
                message = 'كلمة المرور غير صحيحة. يرجى التحقق من كلمة المرور المدخلة';
            } else if (errorType === 'no_password') {
                message = 'لم يتم تعيين كلمة مرور لهذا الموظف. يرجى التواصل مع الإدارة';
            } else if (errorType === 'account_disabled') {
                message = 'حساب الموظف معطل. يرجى التواصل مع الإدارة لتفعيل الحساب';
            }
            
            // إظهار رسالة الخطأ
            showError(message);
            
            // التركيز على الحقل المناسب حسب نوع الخطأ
            switch (errorType) {
                case 'finger_print_id':
                    // التركيز على حقل رقم البصمة
                    const fingerPrintIdField = document.getElementById('fingerPrintId');
                    if (fingerPrintIdField) {
                        fingerPrintIdField.focus();
                        fingerPrintIdField.select();
                        // إضافة تأثير بصري
                        fingerPrintIdField.classList.add('field-error');
                        setTimeout(() => {
                            fingerPrintIdField.classList.remove('field-error');
                        }, 3000);
                    }
                    break;
                    
                case 'finger_print_name':
                    // التركيز على حقل اسم البصمة
                    const fingerPrintNameField = document.getElementById('fingerPrintName');
                    if (fingerPrintNameField) {
                        fingerPrintNameField.focus();
                        fingerPrintNameField.select();
                        // إضافة تأثير بصري
                        fingerPrintNameField.classList.add('field-error');
                        setTimeout(() => {
                            fingerPrintNameField.classList.remove('field-error');
                        }, 3000);
                    }
                    break;
                    
                case 'wrong_password':
                case 'no_password':
                    // التركيز على حقل كلمة المرور
                    const passwordField = document.getElementById('password');
                    if (passwordField) {
                        passwordField.focus();
                        passwordField.select();
                        // إضافة تأثير بصري
                        passwordField.classList.add('field-error');
                        setTimeout(() => {
                            passwordField.classList.remove('field-error');
                        }, 3000);
                    }
                    break;
                    
                case 'account_disabled':
                    // لا نركز على أي حقل، فقط نعرض الرسالة
                    console.log('Employee status:', result.employee_status);
                    break;
                    
                default:
                    // التركيز على أول حقل
                    const firstField = document.getElementById('fingerPrintId');
                    if (firstField) {
                        firstField.focus();
                    }
                    break;
            }
        }
        
        function showSuccess(message) {
            hideMessages(); // إخفاء جميع الرسائل أولاً
            const successDiv = document.getElementById('successMessage');
            if (successDiv) {
                successDiv.textContent = message;
                successDiv.style.display = 'block';
                
                // إخفاء الرسالة بعد 3 ثواني
                setTimeout(() => {
                    if (successDiv) {
                        successDiv.style.display = 'none';
                    }
                }, 3000);
            }
        }
        
        function hideMessages() {
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
            if (successDiv) {
                successDiv.style.display = 'none';
            }
        }
        
        function showEmployeeInfo(employee) {
            const employeeInfo = document.getElementById('employeeInfo');
            const employeeName = document.getElementById('employeeName');
            const employeeDetails = document.getElementById('employeeDetails');
            
            if (employeeInfo && employeeName && employeeDetails) {
                employeeName.textContent = employee.name;
                employeeDetails.innerHTML = `
                    <strong>رقم الموظف:</strong> ${employee.id}<br>
                    <strong>المنصب:</strong> ${employee.position || 'غير محدد'}<br>
                    <strong>القسم:</strong> ${employee.department?.name || 'غير محدد'}<br>
                    <strong>الحالة:</strong> ${employee.status}
                `;
                
                employeeInfo.style.display = 'block';
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
    </script>
</body>
</html>
<?php /**PATH D:\laragon\www\massar1.02\resources\views/mobile/employee-login.blade.php ENDPATH**/ ?>