<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل دخول الموظف - Massar ERP</title>
    
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-title {
            color: #333;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: right;
        }
        
        .form-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            text-align: right;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        
        .input-group .form-control {
            border-right: none;
            border-radius: 12px 0 0 12px;
        }
        
        .login-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .login-btn:hover {
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
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            display: none;
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
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 5px;
        }
        
        .employee-details {
            font-size: 14px;
            color: #666;
        }
        
        .fingerprint-icon {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
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
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
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
            document.getElementById('fingerPrintId').focus();
        }
        
        function setupEventListeners() {
            // تسجيل الدخول
            document.getElementById('employeeLoginForm').addEventListener('submit', handleLogin);
            
            // Enter key navigation
            document.getElementById('fingerPrintId').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('fingerPrintName').focus();
                }
            });
            
            document.getElementById('fingerPrintName').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('password').focus();
                }
            });
            
            document.getElementById('password').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleLogin(e);
                }
            });
        }
        
        async function handleLogin(e) {
            e.preventDefault();
            
            const fingerPrintId = document.getElementById('fingerPrintId').value.trim();
            const fingerPrintName = document.getElementById('fingerPrintName').value.trim();
            const password = document.getElementById('password').value;
            
            // التحقق من البيانات المطلوبة
            if (!fingerPrintId || !fingerPrintName || !password) {
                showError('يرجى ملء جميع الحقول المطلوبة');
                return;
            }
            
            // إظهار Loading
            showLoading(true);
            hideMessages();
            
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
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // نجح تسجيل الدخول
                    currentEmployee = result.data.employee;
                    showSuccess('تم تسجيل الدخول بنجاح');
                    showEmployeeInfo(currentEmployee);
                    
                    // الانتقال لصفحة تسجيل البصمة بعد ثانيتين
                    setTimeout(() => {
                        window.location.href = `/mobile/attendance?employee_id=${currentEmployee.id}`;
                    }, 2000);
                    
                } else {
                    // فشل تسجيل الدخول
                    showError(result.message || 'بيانات الدخول غير صحيحة');
                }
                
            } catch (error) {
                showError('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى');
                return; // إيقاف التنفيذ عند حدوث خطأ
            } finally {
                showLoading(false);
            }
        }
        
        function showLoading(show) {
            const loading = document.getElementById('loading');
            const loginBtn = document.getElementById('loginBtn');
            
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
        
        function showError(message) {
            hideMessages(); // إخفاء جميع الرسائل أولاً
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // إخفاء الرسالة بعد 5 ثواني
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function showSuccess(message) {
            hideMessages(); // إخفاء جميع الرسائل أولاً
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            
            // إخفاء الرسالة بعد 3 ثواني
            setTimeout(() => {
                successDiv.style.display = 'none';
            }, 3000);
        }
        
        function hideMessages() {
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';
        }
        
        function showEmployeeInfo(employee) {
            const employeeInfo = document.getElementById('employeeInfo');
            const employeeName = document.getElementById('employeeName');
            const employeeDetails = document.getElementById('employeeDetails');
            
            employeeName.textContent = employee.name;
            employeeDetails.innerHTML = `
                <strong>رقم الموظف:</strong> ${employee.id}<br>
                <strong>المنصب:</strong> ${employee.position || 'غير محدد'}<br>
                <strong>القسم:</strong> ${employee.department?.name || 'غير محدد'}<br>
                <strong>الحالة:</strong> ${employee.status}
            `;
            
            employeeInfo.style.display = 'block';
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
