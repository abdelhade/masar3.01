# إعداد رفع الصور - Image Upload Setup

## نظرة عامة (Overview)
تم إعادة هيكلة نظام رفع الصور لاستخدام **Spatie Media Library** بأفضل الممارسات، مما يضمن عمل الصور بشكل صحيح في كل من البيئة المحلية (Local) والإنتاج (Production).

---

## التغييرات المنفذة (Changes Made)

### 1. تحديث Employee Model
**الملف:** `app/Models/Employee.php`

#### ما تم تغييره:
- ✅ **إزالة** Override method الخاطئ للـ `getFirstMediaUrl()` 
- ✅ **إضافة** Accessor جديد `image_url` يستخدم built-in functionality من Spatie
- ✅ **تحسين** معالجة الصور الافتراضية (Fallback images)

#### الكود الجديد:
```php
/**
 * Get the employee's image URL or fallback to placeholder
 * Works correctly in both local (Laragon) and production environments
 * Spatie Media Library automatically handles APP_URL from config
 */
public function getImageUrlAttribute(): ?string
{
    $url = $this->getFirstMediaUrl('employee_images');
    
    // If no media exists, return the fallback URL defined in registerMediaCollections
    if (!$url) {
        return asset('assets/images/avatar-placeholder.svg');
    }
    
    return $url;
}
```

### 2. تحديث Livewire Component
**الملف:** `resources/views/livewire/hr-management/employees/manage-employee.blade.php`

#### التغيير:
```php
// ❌ القديم (Wrong)
$this->currentImageUrl = $employee->getFirstMediaUrl('employee_images') ?: null;

// ✅ الجديد (Correct)
$this->currentImageUrl = $employee->image_url;
```

### 3. تحديث Employee View Partial
**الملف:** `resources/views/livewire/hr-management/employees/partials/employee-view.blade.php`

#### التغيير:
```php
// ❌ القديم (Wrong)
$employeeImage = $viewEmployee->getFirstMediaUrl('employee_images');

// ✅ الجديد (Correct)
$employeeImage = $viewEmployee->image_url;
```

---

## إعدادات البيئة المحلية (Local Environment - Laragon)

### 1. ملف `.env`
تأكد من وجود الإعدادات التالية:
```env
APP_URL=http://localhost:8000
FILESYSTEM_DISK=public
MEDIA_DISK=public
```

### 2. إنشاء Symbolic Link
قم بتشغيل الأمر التالي **مرة واحدة فقط**:
```bash
php artisan storage:link
```

هذا الأمر ينشئ symbolic link من `public/storage` إلى `storage/app/public`

### 3. التحقق من الإعداد
بعد رفع صورة، يجب أن يكون الرابط بهذا الشكل:
```
http://localhost:8000/storage/1/image-name.png
```

---

## إعدادات بيئة الإنتاج (Production Environment)

### 1. ملف `.env` للإنتاج
```env
# مثال: إذا كان الموقع على https://example.com
APP_URL=https://example.com

FILESYSTEM_DISK=public
MEDIA_DISK=public

# اختياري: إذا كنت تستخدم CDN
# ASSET_URL=https://cdn.example.com
```

⚠️ **مهم جداً:** تأكد من تغيير `APP_URL` إلى رابط موقعك الحقيقي في الإنتاج!

### 2. إنشاء Symbolic Link على السيرفر
قم بتشغيل الأمر التالي على السيرفر بعد الـ deployment:
```bash
php artisan storage:link
```

### 3. صلاحيات المجلدات (Permissions)
تأكد من أن السيرفر لديه صلاحيات الكتابة على المجلدات التالية:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 4. التحقق من الإعداد
بعد رفع صورة في الإنتاج، يجب أن يكون الرابط بهذا الشكل:
```
https://example.com/storage/1/image-name.png
```

---

## حل المشاكل الشائعة (Troubleshooting)

### المشكلة 1: الصور لا تظهر في الإنتاج
**الحلول:**
1. تأكد من صحة `APP_URL` في `.env`
2. تأكد من وجود symbolic link:
   ```bash
   ls -la public/storage
   ```
   يجب أن ترى: `storage -> /path/to/storage/app/public`
3. تأكد من صلاحيات المجلدات
4. امسح الـ cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### المشكلة 2: خطأ "File not found" عند رفع الصورة
**الحل:**
```bash
# تأكد من وجود المجلد
mkdir -p storage/app/public

# تأكد من الصلاحيات
chmod -R 775 storage
```

### المشكلة 3: الصور تظهر في Local لكن ليس في Production
**السبب المحتمل:** `APP_URL` غير محدد بشكل صحيح في `.env`

**الحل:**
```bash
# في ملف .env للإنتاج
APP_URL=https://your-actual-domain.com

# ثم امسح الـ cache
php artisan config:clear
```

### المشكلة 4: الروابط تظهر بـ `localhost` في Production
**السبب:** الـ cache يحتوي على قيم قديمة

**الحل:**
```bash
php artisan config:clear
php artisan config:cache
php artisan optimize:clear
```

---

## استخدام الصور في الكود (Usage in Code)

### في Blade Templates:
```blade
{{-- عرض صورة الموظف --}}
<img src="{{ $employee->image_url }}" alt="{{ $employee->name }}">

{{-- التحقق من وجود صورة --}}
@if($employee->hasMedia('employee_images'))
    <img src="{{ $employee->image_url }}" alt="{{ $employee->name }}">
@else
    <img src="{{ asset('assets/images/avatar-placeholder.svg') }}" alt="No Image">
@endif
```

### في Livewire Components:
```php
// جلب رابط الصورة
$imageUrl = $employee->image_url;

// رفع صورة جديدة
$employee->addMedia($file->getRealPath())
    ->usingName($file->getClientOriginalName())
    ->toMediaCollection('employee_images');

// حذف الصورة الحالية
$employee->clearMediaCollection('employee_images');
```

### في Controllers:
```php
// جلب الموظف مع الصور
$employee = Employee::with('media')->find($id);

// رابط الصورة
$imageUrl = $employee->image_url;
```

---

## مميزات الحل الجديد (Benefits)

✅ **يعمل في Local والـ Production** بدون تعديلات إضافية  
✅ **يستخدم Built-in functionality** من Spatie Media Library  
✅ **يحترم `APP_URL`** من ملف `.env`  
✅ **معالجة صحيحة للصور الافتراضية** (Fallback images)  
✅ **كود نظيف وقابل للصيانة** (Clean & maintainable)  
✅ **متوافق مع CDN** (يمكن استخدام `ASSET_URL` للـ CDN)  

---

## ملاحظات مهمة (Important Notes)

1. **لا تقم بـ Override** الـ `getFirstMediaUrl()` method من Spatie - استخدم Accessors بدلاً من ذلك
2. **تأكد دائماً** من صحة `APP_URL` في كل بيئة
3. **لا تنسى** تشغيل `php artisan storage:link` بعد الـ deployment
4. **استخدم** `image_url` accessor بدلاً من `getFirstMediaUrl()` مباشرة

---

## المراجع (References)

- [Spatie Media Library Documentation](https://spatie.be/docs/laravel-medialibrary)
- [Laravel File Storage Documentation](https://laravel.com/docs/filesystem)
- [Laravel Configuration Documentation](https://laravel.com/docs/configuration)

