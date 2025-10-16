<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .attendance-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .attendance-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .attendance-header {
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .attendance-title {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .attendance-subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 32px;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .user-id {
            color: #666;
            font-size: 14px;
        }
        
        .user-details {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        
        .user-details div {
            margin-bottom: 3px;
        }
        
        .attendance-type {
            margin-bottom: 30px;
        }
        
        .type-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .type-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .type-btn.active {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
        
        .type-btn:hover {
            border-color: #28a745;
            transform: translateY(-2px);
        }
        
        .type-icon {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }
        
        .type-label {
            font-size: 14px;
            font-weight: bold;
        }
        
        .attendance-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .btn-checkin {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
        }
        
        .attendance-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .attendance-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .logout-icon-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #dc3545;
            border-radius: 50%;
            background: transparent;
            color: #dc3545;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-icon-btn:hover {
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
            padding: 15px;
            margin-bottom: 20px;
            text-align: right;
        }
        
        .location-icon {
            color: #1976d2;
            margin-left: 8px;
        }
        
        .location-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
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
            font-size: 14px;
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
            padding: 15px;
            margin-top: 20px;
        }
        
        .last-attendance-title {
            font-size: 14px;
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
        }
        
        .last-attendance-info {
            font-size: 12px;
            color: #856404;
        }
        
        @media (max-width: 480px) {
            .attendance-card {
                margin: 10px;
                padding: 20px;
            }
            
            .type-buttons {
                flex-direction: column;
            }
            
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .logout-icon-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
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
            <button class="attendance-btn btn-checkin" id="attendance-btn" onclick="recordAttendance()">
                <i class="fas fa-fingerprint"></i> تسجيل دخول
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
            <div class="last-attendance" id="last-attendance" style="display: none;">
                <div class="last-attendance-title">آخر بصمة</div>
                <div class="last-attendance-info" id="last-attendance-info"></div>
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
            const icon = btn.querySelector('i');
            const text = btn.querySelector('span') || btn;
            
            if (selectedType === 'check_in') {
                btn.className = 'attendance-btn btn-checkin';
                icon.className = 'fas fa-fingerprint';
                btn.innerHTML = '<i class="fas fa-fingerprint"></i> تسجيل دخول';
            } else {
                btn.className = 'attendance-btn btn-checkout';
                icon.className = 'fas fa-fingerprint';
                btn.innerHTML = '<i class="fas fa-fingerprint"></i> تسجيل خروج';
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
        
        async function getCurrentLocation() {
            try {
                if (!navigator.geolocation) {
                    throw new Error('المتصفح لا يدعم تحديد الموقع');
                }
                
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });
                
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                
                // الحصول على العنوان من Google Maps
                await getAddressFromCoordinates(currentLocation.latitude, currentLocation.longitude);
                
                // إظهار معلومات الموقع
                document.getElementById('location-info').style.display = 'block';
                
            } catch (error) {
                console.error('خطأ في تحديد الموقع:', error);
                showLocationError();
            }
        }
        
        async function getAddressFromCoordinates(lat, lng) {
            try {
                const response = await fetch(
                    `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key={{ config('services.google.maps_api_key') }}&language=ar`
                );
                
                const data = await response.json();
                
                if (data.status === 'OK' && data.results.length > 0) {
                    const address = data.results[0].formatted_address;
                    document.getElementById('location-address').textContent = address;
                    document.getElementById('location-coordinates').textContent = 
                        `إحداثيات: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    
                    currentLocation.address = address;
                } else {
                    document.getElementById('location-address').textContent = 'لم يتم العثور على العنوان';
                    document.getElementById('location-coordinates').textContent = 
                        `إحداثيات: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                }
            } catch (error) {
                console.error('خطأ في الحصول على العنوان:', error);
                document.getElementById('location-address').textContent = 'خطأ في الحصول على العنوان';
                document.getElementById('location-coordinates').textContent = 
                    `إحداثيات: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }
        }
        
        function showLocationError() {
            document.getElementById('location-info').style.display = 'block';
            document.getElementById('location-address').textContent = 'خطأ في تحديد الموقع';
            document.getElementById('location-coordinates').textContent = 'تأكد من السماح بالوصول للموقع';
        }
        
        async function recordAttendance() {
            if (!currentLocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'لا يمكن تسجيل البصمة بدون تحديد الموقع',
                    confirmButtonText: 'حسناً'
                });
                return;
            }
            
            // إظهار Loading
            showLoading(true);
            
            try {
                // إعداد البيانات
                const attendanceData = {
                    type: selectedType,
                    location: JSON.stringify({
                        latitude: currentLocation.latitude,
                        longitude: currentLocation.longitude,
                        accuracy: currentLocation.accuracy,
                        address: currentLocation.address || null
                    }),
                    notes: 'تم التسجيل من الموبايل'
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
                
                if (response.ok && result.success && result.data) {
                    const attendance = result.data;
                    const lastAttendanceDiv = document.getElementById('last-attendance');
                    const lastAttendanceInfo = document.getElementById('last-attendance-info');
                    
                    const typeText = attendance.type === 'check_in' ? 'دخول' : 'خروج';
                    const date = new Date(attendance.date).toLocaleDateString('ar-SA');
                    const time = attendance.time;
                    
                    lastAttendanceInfo.innerHTML = `
                        <strong>${typeText}</strong> - ${date} في ${time}<br>
                        <small>الحالة: ${getStatusText(attendance.status)}</small>
                    `;
                    
                    lastAttendanceDiv.style.display = 'block';
                }
            } catch (error) {
                console.error('خطأ في تحميل آخر بصمة:', error);
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
