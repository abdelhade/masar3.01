# نظام تسجيل البصمة للموظفين - دليل الاستخدام

## 📱 نظرة عامة

نظام تسجيل البصمة للموظفين هو نظام متكامل يسمح للموظفين بتسجيل دخولهم وخروجهم باستخدام بيانات البصمة المخزنة في جدول الموظفين.

---

## 🔐 نظام المصادقة

### **1. تسجيل دخول الموظف**
- **البيانات المطلوبة:**
  - `finger_print_id`: رقم البصمة
  - `finger_print_name`: اسم البصمة  
  - `password`: كلمة المرور

### **2. التحقق من البيانات**
- يتم البحث في جدول `employees`
- التحقق من حالة الموظف (مفعل/معطل)
- التحقق من كلمة المرور باستخدام Hash

### **3. إدارة الجلسة**
- حفظ بيانات الموظف في Session
- التحقق من تسجيل الدخول في كل طلب
- تسجيل خروج آمن

---

## 🚀 المميزات

### **1. تسجيل دخول آمن**
- ✅ **مصادقة مزدوجة**: رقم البصمة + اسم البصمة + كلمة المرور
- ✅ **حماية الجلسة**: Session management
- ✅ **التحقق من الحالة**: موظف مفعل فقط
- ✅ **رسائل خطأ واضحة**: للمستخدم

### **2. تسجيل البصمة التلقائي**
- ✅ **ملء البيانات تلقائياً**: من بيانات الموظف المسجل دخول
- ✅ **وقت السيرفر**: دقة في التوقيت
- ✅ **تحديد الموقع**: GPS + عنوان
- ✅ **حفظ آمن**: في قاعدة البيانات

### **3. واجهة مستخدم متقدمة**
- ✅ **تصميم متجاوب**: يعمل على جميع الأجهزة
- ✅ **واجهة عربية**: دعم كامل للغة العربية
- ✅ **رسائل تفاعلية**: SweetAlert2
- ✅ **تحميل سلس**: Loading states

---

## 🛠️ التثبيت والإعداد

### **1. الملفات المطلوبة**
```
resources/views/mobile/employee-login.blade.php  - صفحة تسجيل الدخول
app/Http/Controllers/EmployeeAuthController.php  - Controller للمصادقة
app/Http/Middleware/EmployeeAuth.php            - Middleware للتحقق
resources/views/mobile/attendance.blade.php     - صفحة تسجيل البصمة (محدثة)
app/Http/Controllers/MobileAttendanceController.php - Controller للبصمة (محدث)
```

### **2. Routes المطلوبة**
```php
// Employee Login Routes
Route::get('/mobile/employee-login', function () {
    return view('mobile.employee-login');
})->name('mobile.employee-login');

// Employee Auth API Routes
Route::post('/api/employee/login', [EmployeeAuthController::class, 'login']);
Route::post('/api/employee/logout', [EmployeeAuthController::class, 'logout']);
Route::get('/api/employee/check-auth', [EmployeeAuthController::class, 'checkAuth']);
Route::get('/api/employee/current', [EmployeeAuthController::class, 'getCurrentEmployee']);

// Mobile Attendance Routes (مع middleware)
Route::get('/mobile/attendance', function () {
    return view('mobile.attendance');
})->middleware(['employee.auth']);

Route::post('/api/attendance/record', [MobileAttendanceController::class, 'recordAttendance'])
    ->middleware(['employee.auth']);
```

### **3. Middleware Registration**
```php
// في bootstrap/app.php
$middleware->alias([
    'employee.auth' => \App\Http\Middleware\EmployeeAuth::class,
]);
```

---

## 📱 كيفية الاستخدام

### **1. تسجيل دخول الموظف**
```
https://your-domain.com/mobile/employee-login
```

**الخطوات:**
1. **أدخل رقم البصمة**: من جدول الموظفين
2. **أدخل اسم البصمة**: من جدول الموظفين
3. **أدخل كلمة المرور**: كلمة مرور الموظف
4. **اضغط تسجيل الدخول**: سيتم التحقق من البيانات

### **2. تسجيل البصمة**
```
https://your-domain.com/mobile/attendance
```

**الخطوات:**
1. **اختر نوع البصمة**: دخول أو خروج
2. **اضغط زر التسجيل**: سيتم تسجيل البصمة تلقائياً
3. **انتظر التأكيد**: ستظهر رسالة نجاح

---

## 🗄️ قاعدة البيانات

### **جدول employees**
```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    finger_print_id INT UNIQUE NULL,
    finger_print_name VARCHAR(255) UNIQUE NULL,
    password VARCHAR(255) NULL,
    status ENUM('مفعل', 'معطل') DEFAULT 'مفعل',
    -- باقي الحقول...
);
```

### **جدول attendances**
```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    employee_attendance_finger_print_id INT NOT NULL,
    employee_attendance_finger_print_name VARCHAR(255) NOT NULL,
    type ENUM('check_in', 'check_out') NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location JSON NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 🔒 الأمان

### **1. مصادقة الموظف**
- **Hash كلمات المرور**: باستخدام Laravel Hash
- **Session Management**: إدارة آمنة للجلسات
- **Middleware Protection**: حماية جميع الـ routes
- **Validation**: التحقق من صحة البيانات

### **2. حماية البيانات**
- **CSRF Protection**: حماية من هجمات CSRF
- **Rate Limiting**: منع spam requests
- **Input Validation**: تنظيف البيانات المدخلة
- **SQL Injection Protection**: حماية من حقن SQL

### **3. التحقق من الصلاحيات**
- **Employee Status Check**: التحقق من حالة الموظف
- **Session Validation**: التحقق من صحة الجلسة
- **Route Protection**: حماية الـ routes بالـ middleware

---

## 🧪 الاختبار

### **1. اختبار تسجيل الدخول**
```bash
# اختبار API
curl -X POST http://localhost/api/employee/login \
  -H "Content-Type: application/json" \
  -d '{
    "finger_print_id": 123,
    "finger_print_name": "أحمد محمد",
    "password": "password123"
  }'
```

### **2. اختبار تسجيل البصمة**
```bash
# بعد تسجيل الدخول
curl -X POST http://localhost/api/attendance/record \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=..." \
  -d '{
    "type": "check_in",
    "location": "{\"latitude\":30.0444,\"longitude\":31.2357}",
    "notes": "اختبار"
  }'
```

### **3. اختبار الصفحات**
- **صفحة تسجيل الدخول**: `/mobile/employee-login`
- **صفحة تسجيل البصمة**: `/mobile/attendance`

---

## 🐛 استكشاف الأخطاء

### **1. مشاكل تسجيل الدخول**
```javascript
// خطأ: "رقم البصمة أو اسم البصمة غير صحيح"
// الحل: تأكد من صحة البيانات في جدول employees

// خطأ: "كلمة المرور غير صحيحة"
// الحل: تأكد من كلمة المرور أو قم بتحديثها
```

### **2. مشاكل الجلسة**
```javascript
// خطأ: "غير مسجل دخول"
// الحل: تأكد من تسجيل الدخول أولاً

// خطأ: "الموظف غير موجود"
// الحل: تأكد من وجود الموظف في قاعدة البيانات
```

### **3. مشاكل الـ Middleware**
```php
// خطأ: "Class 'App\Http\Middleware\EmployeeAuth' not found"
// الحل: تأكد من تسجيل الـ middleware في bootstrap/app.php
```

---

## 🔧 التخصيص

### **1. تغيير رسائل الخطأ**
```php
// في EmployeeAuthController.php
return response()->json([
    'success' => false,
    'message' => 'رسالة خطأ مخصصة'
], 401);
```

### **2. تغيير مدة الجلسة**
```php
// في config/session.php
'lifetime' => 120, // دقائق
```

### **3. إضافة حقول إضافية**
```php
// في EmployeeAuthController.php
Session::put('employee_department', $employee->department->name);
```

---

## 📊 التقارير

### **1. إحصائيات البصمات**
- **إجمالي البصمات**: عدد البصمات في الفترة
- **دخول/خروج**: عدد مرات كل نوع
- **حالة البصمات**: معلقة/معتمدة/مرفوضة

### **2. آخر بصمة**
- **النوع**: دخول أو خروج
- **التاريخ والوقت**: آخر بصمة مسجلة
- **الموقع**: مكان آخر بصمة

---

## 🚀 التحسينات المستقبلية

### **1. ميزات إضافية**
- **تذكير البصمة**: إشعارات تذكير
- **تقرير شهري**: تقرير شامل للبصمات
- **تصدير البيانات**: Excel/PDF
- **API للمديرين**: إدارة البصمات

### **2. تحسينات الأمان**
- **2FA**: مصادقة ثنائية
- **Biometric**: بصمة حقيقية
- **Audit Log**: سجل المراجعة
- **Encryption**: تشفير البيانات

### **3. تحسينات الأداء**
- **Caching**: تخزين مؤقت
- **Queue**: معالجة في الخلفية
- **Optimization**: تحسين الاستعلامات

---

## 📞 الدعم الفني

### **للمساعدة:**
1. **تحقق من Console**: فحص أخطاء JavaScript
2. **تحقق من Logs**: فحص سجلات Laravel
3. **تحقق من Database**: فحص البيانات
4. **تحقق من Session**: فحص الجلسة

### **ملفات مهمة:**
- `resources/views/mobile/employee-login.blade.php`: صفحة تسجيل الدخول
- `app/Http/Controllers/EmployeeAuthController.php`: Controller المصادقة
- `app/Http/Middleware/EmployeeAuth.php`: Middleware التحقق
- `resources/views/mobile/attendance.blade.php`: صفحة تسجيل البصمة

---

**تاريخ الإنشاء**: 16 يناير 2025  
**الإصدار**: 2.0  
**المطور**: فريق تطوير Massar ERP  
**الحالة**: جاهز للاستخدام مع نظام مصادقة الموظفين
