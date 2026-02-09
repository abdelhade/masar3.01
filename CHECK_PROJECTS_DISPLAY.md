# تشخيص مشكلة عدم ظهور المشاريع

## الخطوات للتشخيص:

### 1. افتح الصفحة
```
http://127.0.0.1:8000/progress/projects
```

### 2. تحقق من الـ Logs
```powershell
Get-Content "storage/logs/laravel.log" -Tail 50
```

ابحث عن:
- `Projects Index - Admin/Manager` أو `Projects Index - Regular User`
- `getAllActive Query`
- `getAllActive Result`

### 3. تحقق من البيانات في قاعدة البيانات
```powershell
php artisan tinker
```

ثم اكتب:
```php
// عدد المشاريع الكلي
\App\Models\Project::count()

// المشاريع غير المسودات
\App\Models\Project::where('is_draft', false)->count()

// المشاريع مع is_progress = 1
\App\Models\Project::where('is_progress', 1)->count()

// المشاريع التي تطابق الشرطين
\App\Models\Project::where('is_draft', false)->where('is_progress', 1)->count()

// عرض أول 5 مشاريع
\App\Models\Project::where('is_draft', false)->where('is_progress', 1)->take(5)->get(['id', 'name', 'is_draft', 'is_progress'])
```

### 4. تحقق من الصلاحيات
```php
// في tinker
$user = \Auth::user();
$user->name
$user->roles->pluck('name')
```

### 5. إذا كانت المشكلة في is_progress
قد تكون القيمة `null` بدلاً من `1`. جرب:
```php
// في tinker
\App\Models\Project::whereNull('is_progress')->count()

// تحديث جميع المشاريع لتكون is_progress = 1
\App\Models\Project::whereNull('is_progress')->update(['is_progress' => 1]);
```

### 6. حل مؤقت - إزالة الفلتر
إذا أردت رؤية جميع المشاريع مؤقتاً، يمكنك تعليق السطر:
```php
// في ProjectRepository.php
->where('is_progress', 1)  // علق هذا السطر
```

---

## النتائج المتوقعة:

### إذا كان العدد 21:
✅ البيانات موجودة، المشكلة في العرض أو الصلاحيات

### إذا كان العدد 0:
⚠️ المشكلة في البيانات - جميع المشاريع إما:
- `is_draft = true`
- `is_progress = null` أو `0`

---

## الحل السريع:

إذا كانت المشكلة أن `is_progress` = `null`:

```sql
UPDATE projects SET is_progress = 1 WHERE is_progress IS NULL;
```

أو في Laravel:
```php
php artisan tinker --execute="\App\Models\Project::whereNull('is_progress')->update(['is_progress' => 1]);"
```
