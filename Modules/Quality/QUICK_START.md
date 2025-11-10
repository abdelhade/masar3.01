# ⚡ دليل البدء السريع - Quality Module

## 🚀 التثبيت في 5 خطوات

### الخطوة 1: تشغيل Migrations

```bash
cd d:\laravel\massar1.02
php artisan migrate
```

### الخطوة 2: تسجيل Service Provider

أضف في `config/app.php`:

```php
'providers' => [
    // ...
    Modules\Quality\Providers\QualityServiceProvider::class,
],
```

أو أضف في `bootstrap/providers.php`:

```php
return [
    // ...
    Modules\Quality\Providers\QualityServiceProvider::class,
];
```

### الخطوة 3: مسح Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
composer dump-autoload
```

### الخطوة 4: تسجيل Routes في Main Routes

أضف في `routes/web.php` أو أنشئ `routes/modules/quality.php`:

```php
// تم بالفعل في Modules/Quality/routes/web.php
// فقط تأكد من تسجيل RouteServiceProvider
```

### الخطوة 5: الوصول للنظام

افتح المتصفح واذهب إلى:

```
http://127.0.0.1:8000/quality/dashboard
```

---

## 📱 الروابط السريعة

| الرابط | الوصف |
|-------|-------|
| `/quality/dashboard` | لوحة التحكم |
| `/quality/inspections` | إدارة الفحوصات |
| `/quality/inspections/create` | فحص جديد |
| `/quality/reports` | التقارير |

---

## 🎯 أول استخدام

### 1. إنشاء معيار جودة (Quality Standard)
```php
// سيتم إضافة الواجهة لاحقاً
// حالياً يمكن إنشاؤه من Tinker:
php artisan tinker

>>> $standard = new \Modules\Quality\Models\QualityStandard();
>>> $standard->item_id = 1;
>>> $standard->branch_id = 1;
>>> $standard->standard_code = 'STD-001';
>>> $standard->standard_name = 'معيار جودة الاختبار';
>>> $standard->save();
```

### 2. إنشاء فحص (Inspection)
اذهب إلى: `/quality/inspections/create`

---

## 🔧 استكشاف الأخطاء

### خطأ: "Route [quality.dashboard] not defined"
**الحل**: 
```bash
php artisan route:clear
php artisan cache:clear
```

### خطأ: "Table doesn't exist"
**الحل**: 
```bash
php artisan migrate
```

### خطأ: "Class QualityServiceProvider not found"
**الحل**: 
```bash
composer dump-autoload
php artisan cache:clear
```

---

## ✅ التحقق من التثبيت

### اختبار Routes:
```bash
php artisan route:list | grep quality
```

يجب أن ترى:
```
GET|HEAD  quality/dashboard ........ quality.dashboard
GET|HEAD  quality/inspections ...... quality.inspections.index
POST      quality/inspections ...... quality.inspections.store
...
```

### اختبار Migrations:
```bash
php artisan migrate:status | grep quality
```

يجب أن ترى 8 migrations لـ Quality.

---

## 🎓 الخطوات التالية

1. أضف بيانات تجريبية
2. جرّب إنشاء فحص
3. استكشف Dashboard
4. راجع الوثائق الكاملة في `README.md`

---

## 📞 المساعدة

إذا واجهت أي مشكلة، راجع:
1. `README.md` - الوثائق الكاملة
2. `IMPLEMENTATION_SUMMARY.md` - ملخص التنفيذ

---

**استمتع باستخدام Quality Module! 🎉**

