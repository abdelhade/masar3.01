# نظام إدارة الشيكات - Checks Module

## نظرة عامة

نظام إدارة الشيكات هو موديول متكامل لإدارة الشيكات الواردة والصادرة (أوراق القبض والدفع) مع متابعة حالاتها وإصدار التقارير. تم تحديث الموديول بالكامل لاستخدام Laravel 12 و Livewire 3 و Volt و Flux UI.

## المميزات الرئيسية

### ✅ لوحة تحكم شاملة
- عرض إحصائيات مفصلة للشيكات
- رسوم بيانية للاتجاهات الشهرية
- تنبيهات الشيكات المتأخرة
- تقارير الشيكات حسب البنوك

### ✅ إدارة كاملة للشيكات
- إضافة شيكات جديدة مع تكامل محاسبي تلقائي
- تعديل وحذف الشيكات
- البحث والتصفية المتقدمة
- إرفاق مستندات مع كل شيك

### ✅ تتبع حالات الشيك
- **معلق (Pending)**: شيك في انتظار التصفية
- **مصفى (Cleared)**: شيك تم تصفيته بنجاح
- **مرتد (Bounced)**: شيك تم رفضه من البنك
- **ملغى (Cancelled)**: شيك تم إلغاؤه

### ✅ أنواع الشيكات
- **أوراق قبض (Incoming)**: شيكات واردة من العملاء
- **أوراق دفع (Outgoing)**: شيكات صادرة للموردين

### ✅ التكامل المحاسبي
- إنشاء قيود محاسبية تلقائية عند إضافة شيك
- دعم قيود عكسية عند الإلغاء
- تكامل كامل مع نظام الحسابات

## البنية التقنية

### Services
- `CheckService`: إدارة الشيكات الأساسية
- `CheckAccountingService`: العمليات المحاسبية
- `CheckPortfolioService`: إدارة حافظات الأوراق المالية

### Form Requests
- `StoreCheckRequest`: التحقق من بيانات الشيك الجديد
- `UpdateCheckRequest`: التحقق من بيانات التحديث
- `ClearCheckRequest`: التحقق من بيانات التصفية
- `BatchCollectRequest`: التحقق من بيانات التحصيل المجمع
- `BatchCancelRequest`: التحقق من بيانات الإلغاء المجمع

### Policies
- `CheckPolicy`: إدارة الصلاحيات

### Volt Components
- `checks-management`: إدارة الشيكات
- `checks-dashboard`: لوحة التحكم

## التثبيت

1. تأكد من تفعيل الموديول:
```bash
php artisan module:enable Checks
```

2. تشغيل Migrations:
```bash
php artisan migrate
```

3. تشغيل Seeders:
```bash
php artisan module:seed Checks
```

## الاستخدام

### إضافة شيك جديد

1. اذهب إلى صفحة إدارة الشيكات
2. اضغط على زر "إضافة شيك جديد"
3. املأ البيانات المطلوبة
4. سيتم إنشاء القيد المحاسبي تلقائياً

### تصفية شيك

1. ابحث عن الشيك المراد تصفيته
2. اضغط على زر "تصفية"
3. أدخل حساب البنك وتاريخ التحصيل
4. سيتم إنشاء قيد محاسبي للتحصيل

### إلغاء شيك مع قيد عكسي

1. ابحث عن الشيك المراد إلغاؤه
2. اضغط على زر "إلغاء"
3. سيتم إنشاء قيد عكسي تلقائياً

## الصلاحيات

النظام يستخدم صلاحيات محددة:
- `viewAny`: عرض الشيكات
- `view`: عرض شيك محدد
- `create`: إضافة شيكات جديدة
- `update`: تعديل الشيكات
- `delete`: حذف الشيكات
- `clear`: تصفية الشيكات
- `bounce`: تمييز الشيكات كمرتدة
- `cancel`: إلغاء الشيكات
- `approve`: اعتماد الشيكات
- `export`: تصدير البيانات

## API Endpoints

### Statistics
```
GET /checks/statistics?period=month
```

### Export
```
GET /checks/export?status=pending&type=incoming
GET /checks/export/pdf?status=pending&type=incoming
GET /checks/export/excel?status=pending&type=incoming
```

### Clear Check
```
POST /checks/{check}/clear
```

### Batch Collect
```
POST /checks/batch-collect
```

### Batch Cancel
```
POST /checks/batch-cancel-reversal
```

## التكامل المحاسبي

### أنواع العمليات (pro_types)
- **65**: ورقة قبض
- **66**: ورقة دفع
- **67**: تحصيل شيك
- **69**: شيك مرتد
- **71**: قيد عكسي لشيك

### الحسابات المحاسبية
- **110501**: حافظة أوراق القبض
- **210301**: حافظة أوراق الدفع

## Helpers

تم إضافة `CheckHelper` class مع الـ helper methods التالية:
- `formatCheckNumber()`: تنسيق رقم الشيك
- `calculateTotal()`: حساب إجمالي المبلغ
- `getStatusBadge()`: الحصول على badge للحالة
- `getTypeBadge()`: الحصول على badge للنوع
- `validateCheckNumber()`: التحقق من صحة رقم الشيك
- `getOverdueCount()`: عدد الشيكات المتأخرة
- `getPendingCount()`: عدد الشيكات المعلقة

## Scopes

تم إضافة Scopes في Check Model:
- `overdue()`: الشيكات المتأخرة
- `createdBetween()`: الشيكات في نطاق تاريخ الإنشاء
- `dueBetween()`: الشيكات في نطاق تاريخ الاستحقاق

## Helper Methods

تم إضافة Helper Methods في Check Model:
- `daysUntilDue()`: عدد الأيام حتى تاريخ الاستحقاق
- `daysOverdue()`: عدد الأيام المتأخرة

## التطوير

### إضافة Feature جديدة

1. إنشاء Service method في `CheckService` أو `CheckAccountingService`
2. إنشاء Form Request إذا لزم الأمر
3. إضافة Route في `routes/web.php`
4. إضافة Policy method في `CheckPolicy`
5. تحديث Volt component إذا لزم الأمر

### Tests

```bash
php artisan test Modules/Checks
```

### تشغيل Command للشيكات المتأخرة

```bash
php artisan checks:check-overdue
```

يمكن إضافته إلى `routes/console.php`:
```php
Schedule::command('checks:check-overdue')->daily();
```

## التحديثات

### v2.0.0 (2025)
- ✅ تحويل إلى Livewire Volt
- ✅ استخدام Flux UI components
- ✅ إنشاء Service Layer (3 Services)
- ✅ إضافة Form Requests (5 Requests)
- ✅ إضافة Policies
- ✅ إكمال Reversal Logic
- ✅ إصلاح مشاكل Encoding
- ✅ تحديث Check Model لاستخدام Laravel 12 patterns
- ✅ إضافة Events & Listeners
- ✅ إضافة Tests (Unit & Feature)
- ✅ إضافة Caching للإحصائيات
- ✅ إضافة Console Command للشيكات المتأخرة
- ✅ إنشاء CheckFactory

## Events & Listeners

### Events
- `CheckCreated`: عند إنشاء شيك جديد
- `CheckCleared`: عند تصفية شيك
- `CheckBounced`: عند ارتداد شيك
- `CheckOverdue`: عند تأخر شيك

### Listeners
- `SendCheckCreatedNotification`: إرسال إشعار عند إنشاء شيك
- `SendCheckOverdueNotification`: إرسال إشعار عند تأخر شيك

## Console Commands

### Check Overdue Reminder
```bash
php artisan checks:check-overdue
```

يمكن إضافته إلى `app/Console/Kernel.php` أو `routes/console.php` لتشغيله تلقائياً:
```php
$schedule->command('checks:check-overdue')->daily();
```

## الدعم الفني

للمساعدة أو الاستفسارات، يرجى التواصل مع فريق الدعم الفني.

---

**تاريخ التحديث**: 2025  
**الإصدار**: 2.0.0

