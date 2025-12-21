# 📋 دليل Activity Log - ما يجب تسجيله

## 🎯 الأنشطة الحرجة التي يجب تتبعها في نظام ERP

### 1. **العمليات المالية** 💰
- ✅ **الفواتير** (إنشاء/تعديل/حذف/طباعة)
- ✅ **السندات** (إيصالات/مدفوعات)
- ✅ **التحويلات المالية**
- ✅ **الدفاتر اليومية**
- ✅ **الشيكات** (إنشاء/تحصيل/إرجاع)
- ✅ **خطط التقسيط والمدفوعات**

### 2. **إدارة المستخدمين والصلاحيات** 👥
- ✅ **إنشاء/تعديل/حذف المستخدمين**
- ✅ **تغيير الصلاحيات والأدوار**
- ✅ **تغيير كلمات المرور**
- ✅ **ربط المستخدمين بالفروع**

### 3. **إدارة المخزون** 📦
- ✅ **الأصناف** (إنشاء/تعديل/حذف/تغيير الأسعار)
- ✅ **حركات المخزون** (دخول/خروج/تحويل)
- ✅ **تعديلات المخزون**
- ✅ **تتبع الدفعات**

### 4. **العمليات التجارية** 🏢
- ✅ **الاستفسارات/العروض**
- ✅ **الطلبات**
- ✅ **إرجاع الطلبات**
- ✅ **الشحنات**

### 5. **الموارد البشرية** 👨‍💼
- ✅ **الموظفين** (إنشاء/تعديل/إنهاء خدمة)
- ✅ **العقود**
- ✅ **طلبات الإجازات**
- ✅ **الحضور والانصراف**
- ✅ **المرتبات**

### 6. **الإعدادات المهمة** ⚙️
- ✅ **تغيير إعدادات النظام**
- ✅ **تغيير الفروع**
- ✅ **تغيير الحسابات**

---

## 🔧 كيفية إضافة التتبع التلقائي

### الطريقة 1: استخدام Trait `LogsActivity`

```php
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Model
{
    use LogsActivity;
    
    // تحديد الحقول التي يجب تتبعها
    protected static $logAttributes = ['name', 'email', 'is_active'];
    
    // تسجيل فقط الحقول التي تغيرت
    protected static $logOnlyDirty = true;
    
    // تسمية النشاط
    protected static $logName = 'user';
}
```

### الطريقة 2: تسجيل نشاط يدوي

```php
use Spatie\Activitylog\Facades\Activity;

activity()
    ->causedBy(auth()->user())
    ->performedOn($model)
    ->withProperties(['custom' => 'property'])
    ->log('تم إنشاء فاتورة جديدة');
```

### الطريقة 3: في Controller

```php
public function store(Request $request)
{
    $item = Item::create($request->validated());
    
    activity()
        ->causedBy(auth()->user())
        ->performedOn($item)
        ->withProperties(['data' => $request->all()])
        ->log('تم إنشاء صنف جديد');
    
    return redirect()->route('items.index');
}
```

---

## 📝 أمثلة عملية

### مثال 1: تتبع User Model

```php
// app/Models/User.php
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use LogsActivity;
    
    protected static $logAttributes = ['name', 'email', 'is_active'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'user';
}
```

### مثال 2: تتبع Item Model

```php
// app/Models/Item.php
use Spatie\Activitylog\Traits\LogsActivity;

class Item extends Model
{
    use LogsActivity;
    
    protected static $logAttributes = ['name', 'code', 'cost', 'price'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'item';
}
```

### مثال 3: تتبع Permission Changes

```php
// Modules/Authorization/Models/Permission.php
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends SpatiePermission
{
    use LogsActivity;
    
    protected static $logAttributes = ['name', 'category'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'permission';
}
```

---

## ⚠️ ملاحظات مهمة

1. **لا تسجل كل شيء**: ركز على الأنشطة الحرجة فقط
2. **تجنب الحقول الحساسة**: لا تسجل كلمات المرور أو البيانات المالية الحساسة
3. **استخدم `logOnlyDirty`**: لتسجيل فقط التغييرات الفعلية
4. **حدد الحقول المهمة**: لا تسجل كل الحقول، فقط المهمة

---

## 🚀 الخطوات التالية

1. أضف `LogsActivity` trait للـ Models المهمة
2. حدد الحقول التي يجب تتبعها
3. اختبر النظام وراجع Activity Log
4. أضف المزيد من الـ Models حسب الحاجة

