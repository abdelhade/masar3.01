# إصلاح صلاحيات صفحة Backup - Progress Module

## المشكلة
عند محاولة الوصول إلى صفحة Backup في `http://127.0.0.1:8000/progress/backup`، كان يظهر خطأ **"المنطقة محظورة" (403 Forbidden)**.

## السبب
الـ middleware في `BackupController` كان يستخدم أسماء صلاحيات خاطئة لا تطابق الصلاحيات الموجودة في قاعدة البيانات.

### الصلاحيات الخاطئة (قبل الإصلاح):
```php
$this->middleware('can:view backup')->only(['index']);
$this->middleware('can:create backup')->only(['export', 'import']);
$this->middleware('can:delete backup')->only(['destroy']);
```

### الصلاحيات الصحيحة (بعد الإصلاح):
```php
$this->middleware('can:view progress-backup')->only(['index']);
$this->middleware('can:create progress-backup')->only(['export', 'import']);
$this->middleware('can:download progress-backup')->only(['download']);
$this->middleware('can:delete progress-backup')->only(['destroy']);
```

## الإصلاح المطبق

### 1. تحديث BackupController.php
**الملف:** `Modules/Progress/Http/Controllers/BackupController.php`

تم تصحيح أسماء الصلاحيات في الـ `__construct()` method:

```php
public function __construct()
{
    $this->middleware('can:view progress-backup')->only(['index']);
    $this->middleware('can:create progress-backup')->only(['export', 'import']);
    $this->middleware('can:download progress-backup')->only(['download']);
    $this->middleware('can:delete progress-backup')->only(['destroy']);
}
```

## الصلاحيات المطلوبة

لكي يتمكن المستخدم من الوصول إلى صفحة Backup، يجب أن يمتلك الصلاحيات التالية:

| الصلاحية | الوصف | الاستخدام |
|---------|------|----------|
| `view progress-backup` | عرض صفحة Backup | الوصول إلى `/progress/backup` |
| `create progress-backup` | إنشاء نسخة احتياطية | تصدير واستيراد النسخ الاحتياطية |
| `download progress-backup` | تحميل نسخة احتياطية | تحميل ملفات النسخ الاحتياطية |
| `delete progress-backup` | حذف نسخة احتياطية | حذف ملفات النسخ الاحتياطية |

## كيفية منح الصلاحيات للمستخدم

### الطريقة 1: من خلال صفحة صلاحيات الموظفين
1. اذهب إلى: `http://127.0.0.1:8000/progress/employees`
2. اضغط على "Permissions" للموظف المطلوب
3. ابحث عن قسم "Backup" في الجدول
4. حدد الصلاحيات المطلوبة:
   - ✅ View
   - ✅ Create
   - ✅ Download
   - ✅ Delete
5. اضغط على "Save Changes"

### الطريقة 2: من خلال Tinker
```php
php artisan tinker

$user = User::find(YOUR_USER_ID);
$user->givePermissionTo([
    'view progress-backup',
    'create progress-backup',
    'download progress-backup',
    'delete progress-backup',
]);
```

### الطريقة 3: منح جميع الصلاحيات لـ Admin
```php
php artisan tinker

$admin = User::where('email', 'admin@example.com')->first();
$admin->givePermissionTo(Permission::all());
```

## التحقق من الإصلاح

### 1. التحقق من الصلاحيات في قاعدة البيانات
```sql
SELECT * FROM permissions WHERE name LIKE '%backup%';
```

يجب أن تظهر النتائج التالية:
```
| name                      | guard_name |
|---------------------------|------------|
| view progress-backup      | web        |
| create progress-backup    | web        |
| download progress-backup  | web        |
| delete progress-backup    | web        |
```

### 2. التحقق من صلاحيات المستخدم
```sql
SELECT p.name 
FROM permissions p
JOIN model_has_permissions mhp ON p.id = mhp.permission_id
WHERE mhp.model_id = YOUR_USER_ID 
  AND mhp.model_type = 'App\\Models\\User'
  AND p.name LIKE '%backup%';
```

### 3. اختبار الوصول
1. سجل الدخول بحساب المستخدم الذي منحته الصلاحيات
2. اذهب إلى: `http://127.0.0.1:8000/progress/backup`
3. يجب أن تظهر الصفحة بدون خطأ 403

## ملاحظات مهمة

### 1. تطابق أسماء الصلاحيات
تأكد من أن أسماء الصلاحيات في:
- ✅ `BackupController` middleware
- ✅ `permissions.blade.php` (صفحة صلاحيات الموظفين)
- ✅ `sidebar.blade.php` (`@can` directives)
- ✅ قاعدة البيانات (`permissions` table)

جميعها تستخدم نفس الصيغة: `action progress-backup`

### 2. الصلاحيات في Sidebar
في `Modules/Progress/Resources/views/layouts/sidebar.blade.php`:
```blade
@can('view progress-backup')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('progress.backup.*') ? 'active' : '' }}"
            href="{{ route('progress.backup.index') }}">
            <i class="fas fa-database me-2"></i> 
            <span>{{ __('general.backup_restore') }}</span>
        </a>
    </li>
@endcan
```

### 3. Cache الصلاحيات
إذا لم تعمل الصلاحيات بعد التحديث، قم بمسح الـ cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan permission:cache-reset
```

## الملفات المعدلة

| الملف | التغيير |
|------|---------|
| `Modules/Progress/Http/Controllers/BackupController.php` | تصحيح أسماء الصلاحيات في middleware |
| `Modules/Progress/Resources/views/backup/index.blade.php` | تصحيح route الحذف في JavaScript من `/backup/delete/` إلى `route('progress.backup.destroy')` |

## المشاكل الإضافية التي تم إصلاحها

### مشكلة 404 عند حذف النسخة الاحتياطية
**السبب:** الـ JavaScript function `confirmDelete` كان يستخدم route خاطئ `/backup/delete/` بدلاً من `/progress/backup/delete/`

**الحل:** تم تحديث الـ function لاستخدام `route('progress.backup.destroy')` helper:
```javascript
// قبل
form.action = '/backup/delete/' + encodeURIComponent(filename);

// بعد
form.action = '{{ route("progress.backup.destroy", "") }}/' + encodeURIComponent(filename);
```

## الخلاصة

تم إصلاح المشكلة بنجاح عن طريق تصحيح أسماء الصلاحيات في `BackupController` لتطابق الصلاحيات الموجودة في قاعدة البيانات. الآن يمكن للمستخدمين الذين لديهم صلاحية `view progress-backup` الوصول إلى صفحة Backup بدون مشاكل.
