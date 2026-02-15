# دليل إعداد طابعات المطبخ

## المتطلبات

### 1. وكيل الطباعة (Print Agent)
- يجب تشغيل وكيل طباعة Windows على `http://localhost:5000/print`
- الوكيل متوفر في مجلد `print-agent/`
- **تطبيق C# بسيط بدون dependencies خارجية**

#### خطوات التثبيت:
1. انتقل إلى مجلد `print-agent/`
2. شغل `compile.bat` لتجميع البرنامج
3. شغل `start-admin.bat` لتشغيل الوكيل كمسؤول
4. افتح المتصفح على `http://localhost:5000` للتحقق

#### API Format:
```json
{
  "printer": "اسم_الطابعة",
  "content": "محتوى_الطباعة"
}
```

راجع `print-agent/README.md` للتفاصيل الكاملة

### 2. Queue Worker
- يجب تشغيل queue worker لمعالجة مهام الطباعة:
```bash
php artisan queue:work
```

### 3. الأذونات
قم بتشغيل seeder الأذونات:
```bash
php artisan db:seed --class=Modules\\POS\\database\\seeders\\KitchenPrinterPermissionsSeeder
```

## خطوات الإعداد

### 1. تشغيل Migrations
```bash
php artisan migrate
```

### 2. إضافة محطات الطابعة
- انتقل إلى: الإعدادات > طابعات المطبخ
- أضف محطة جديدة لكل طابعة (مثال: المطبخ، البار، الحلويات)
- حدد اسم الطابعة كما هو في Windows
- فعّل المحطة وحدد إذا كانت افتراضية

### 3. ربط الفئات بالطابعات
- يتم ربط كل فئة منتج بمحطة طابعة أو أكثر
- الأصناف التابعة للفئة ستُطبع تلقائياً على الطابعات المحددة
- إذا لم يتم تحديد طابعة للفئة، سيتم استخدام الطابعة الافتراضية

### 4. اختبار النظام
- قم بإنشاء فاتورة كاشير جديدة
- تحقق من سجل مهام الطباعة في: الإعدادات > سجل مهام الطباعة
- في حالة الفشل، يمكن إعادة المحاولة يدوياً

## استكشاف الأخطاء

### الطباعة لا تعمل
1. تأكد من تشغيل وكيل الطباعة على localhost:5000
2. تأكد من تشغيل queue worker
3. تحقق من سجل الأخطاء في Laravel logs

### الطابعة غير موجودة
- تأكد من أن اسم الطابعة في النظام يطابق اسمها في Windows تماماً


### الطباعة لا تحدث تلقائياً
- تأكد من تسجيل Event Listener في EventServiceProvider
- تحقق من أن الحدث يتم إطلاقه بعد حفظ المعاملة

### رسائل الخطأ في السجل
- راجع `storage/logs/laravel.log` لمعرفة تفاصيل الأخطاء
- تحقق من صحة تنسيق JSON المرسل للوكيل

## الملفات الرئيسية

### Models
- `Modules/POS/app/Models/KitchenPrinterStation.php`
- `Modules/POS/app/Models/PrintJob.php`
- `Modules/Settings/Models/Category.php`

### Services
- `Modules/POS/app/Services/KitchenPrinterService.php`
- `Modules/POS/app/Services/PrintContentFormatter.php`

### Events & Listeners
- `Modules/POS/app/Events/TransactionSaved.php`
- `Modules/POS/app/Listeners/PrintOrderListener.php`

### Jobs
- `Modules/POS/app/Jobs/PrintKitchenOrderJob.php`

### Controllers
- `Modules/POS/app/Http/Controllers/KitchenPrinterStationController.php`
- `Modules/POS/app/Http/Controllers/PrintJobController.php`

### Views
- `Modules/POS/resources/views/kitchen-printers/`
- `Modules/POS/resources/views/print-jobs/`

## الميزات

### الطباعة التلقائية
- يتم إطلاق الطباعة تلقائياً عند حفظ فاتورة كاشير
- يتم تحديد الطابعات بناءً على فئات الأصناف
- الطباعة غير متزامنة (لا تؤثر على سرعة حفظ الفاتورة)

### إعادة المحاولة التلقائية
- 3 محاولات تلقائية عند الفشل
- فترة انتظار 5 ثوانٍ بين المحاولات
- مهلة 10 ثوانٍ لكل محاولة

### سجل مهام الطباعة
- عرض جميع مهام الطباعة مع حالتها
- تصفية حسب التاريخ، المحطة، والحالة
- إعادة محاولة يدوية للمهام الفاشلة
- عرض تفاصيل الأخطاء

### تنسيق الطباعة
- عرض 32 حرف للسطر (مناسب للطابعات الحرارية)
- عرض رقم الطاولة (إن وجد)
- عرض الأصناف مع الكميات
- عرض الملاحظات الخاصة
- عرض التاريخ والوقت
