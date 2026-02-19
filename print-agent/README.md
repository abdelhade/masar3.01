# وكيل طباعة المطبخ - Kitchen Print Agent

## نظرة عامة
تطبيق C# بسيط يعمل كخادم HTTP لاستقبال طلبات الطباعة من نظام Laravel وطباعتها على طابعات Windows.

## المميزات
- ✅ بدون dependencies خارجية (يستخدم فقط مكتبات .NET المدمجة)
- ✅ واجهة ويب بسيطة للاختبار
- ✅ دعم جميع طابعات Windows
- ✅ سجل للعمليات في Console
- ✅ API بسيط وسهل الاستخدام

## المتطلبات
- Windows 7 أو أحدث
- .NET Framework 4.0 أو أحدث (مثبت افتراضياً في معظم أنظمة Windows)

## خطوات التثبيت والتشغيل

### 1. التجميع (Compile)
```batch
compile.bat
```
سيقوم بإنشاء ملف `PrintAgent.exe`

### 2. التشغيل
اختر أحد الطرق التالية:

#### الطريقة الأولى: تشغيل عادي
```batch
start.bat
```
أو انقر نقراً مزدوجاً على `PrintAgent.exe`

#### الطريقة الثانية: تشغيل كمسؤول (موصى به)
```batch
start-admin.bat
```
أو انقر بزر الماوس الأيمن على `PrintAgent.exe` واختر "Run as Administrator"

### 3. التحقق من التشغيل
افتح المتصفح وانتقل إلى:
```
http://localhost:5000
```

## API Endpoints

### 1. الصفحة الرئيسية
```
GET http://localhost:5000/
```
واجهة ويب بسيطة مع زر اختبار الطباعة

### 2. قائمة الطابعات
```
GET http://localhost:5000/printers
```
**الاستجابة:**
```json
{
  "success": true,
  "printers": ["Printer1", "Printer2"],
  "default_printer": "Printer1",
  "count": 2
}
```

### 3. الطباعة
```
POST http://localhost:5000/print
Content-Type: application/json

{
  "printer": "اسم_الطابعة",
  "content": "المحتوى_المراد_طباعته"
}
```
**الاستجابة:**
```json
{
  "success": true,
  "message": "تمت الطباعة بنجاح",
  "printer": "اسم_الطابعة",
  "timestamp": "2026-02-15 23:30:00"
}
```

### 4. فحص الصحة
```
GET http://localhost:5000/health
```
**الاستجابة:**
```json
{
  "success": true,
  "status": "running",
  "timestamp": "2026-02-15 23:30:00"
}
```

## اختبار من Laravel

### باستخدام cURL
```bash
curl -X POST http://localhost:5000/print \
  -H "Content-Type: application/json" \
  -d '{"printer":"اسم_الطابعة","content":"اختبار الطباعة"}'
```

### باستخدام Postman
1. Method: POST
2. URL: `http://localhost:5000/print`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "printer": "اسم_الطابعة",
  "content": "اختبار الطباعة\nالسطر الثاني\nالسطر الثالث"
}
```

## استكشاف الأخطاء

### خطأ: "Access Denied" أو "Port already in use"
**الحل:** قم بتشغيل البرنامج كمسؤول باستخدام `start-admin.bat`

### خطأ: "الطابعة غير موجودة"
**الحل:** 
1. تحقق من اسم الطابعة في Windows (Settings > Devices > Printers)
2. استخدم `/printers` للحصول على قائمة الطابعات المتاحة
3. تأكد من كتابة الاسم بالضبط كما يظهر في Windows

### الطباعة لا تعمل
**الحل:**
1. تأكد من أن الطابعة متصلة وتعمل
2. جرب طباعة ملف عادي من Notepad للتأكد من عمل الطابعة
3. تحقق من سجل الأخطاء في Console

### البرنامج لا يعمل
**الحل:**
1. تأكد من تثبيت .NET Framework 4.0 أو أحدث
2. قم بتشغيل `compile.bat` مرة أخرى
3. تحقق من عدم وجود برنامج آخر يستخدم المنفذ 5000

## تشغيل تلقائي عند بدء Windows

### الطريقة 1: Startup Folder
1. اضغط `Win + R`
2. اكتب `shell:startup` واضغط Enter
3. انسخ اختصار `PrintAgent.exe` إلى هذا المجلد

### الطريقة 2: Task Scheduler
1. افتح Task Scheduler
2. Create Basic Task
3. Trigger: At startup
4. Action: Start a program
5. Program: مسار `PrintAgent.exe`
6. ✅ Run with highest privileges

## الملفات

- `PrintAgent.cs` - الكود المصدري
- `PrintAgent.exe` - البرنامج المجمع (يتم إنشاؤه بعد التجميع)
- `compile.bat` - سكريبت التجميع
- `start.bat` - تشغيل عادي
- `start-admin.bat` - تشغيل كمسؤول
- `README.md` - هذا الملف

## ملاحظات مهمة

1. **الصلاحيات:** يفضل تشغيل البرنامج كمسؤول لتجنب مشاكل الصلاحيات
2. **الجدار الناري:** قد تحتاج للسماح للبرنامج في Windows Firewall
3. **المنفذ:** البرنامج يستخدم المنفذ 5000 افتراضياً
4. **الترميز:** يدعم UTF-8 للنصوص العربية

## الدعم الفني

إذا واجهت أي مشاكل:
1. تحقق من سجل الأخطاء في Console
2. تأكد من تشغيل البرنامج كمسؤول
3. تحقق من إعدادات الجدار الناري
4. راجع قسم "استكشاف الأخطاء" أعلاه
