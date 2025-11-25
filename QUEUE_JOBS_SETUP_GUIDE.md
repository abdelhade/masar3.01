# دليل تفعيل واستخدام Queue Jobs للمعالجة

## ✅ ما تم إنجازه

تم إنشاء:
1. ✅ `ProcessAttendanceJob.php` - لمعالجة الأقسام
2. ✅ `ProcessSingleEmployeeJob.php` - لمعالجة موظف واحد
3. ✅ Methods في `AttendanceProcessingService`:
   - `processDepartmentAsync()` - معالجة قسم في الخلفية
   - `processSingleEmployeeAsync()` - معالجة موظف في الخلفية

---

## 🚀 خطوات التفعيل

### الخطوة 1: إنشاء جدول Jobs

```bash
php artisan queue:table
php artisan migrate
```

هذا سينشئ جدول `jobs` في قاعدة البيانات.

### الخطوة 2: تشغيل Queue Worker

#### في Development:
```bash
php artisan queue:work
```

#### في Production (مع Supervisor):
يجب إعداد Supervisor لضمان استمرار Worker:

```ini
# /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

ثم:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 📝 كيفية الاستخدام

### الطريقة 1: معالجة متزامنة (الحالية) ✅

**للمعالجات الصغيرة** - النتائج تظهر فوراً:

```php
// في AttendanceProcessingManager
$results = $this->attendanceProcessingService->processSingleEmployee(
    $employee,
    $startDate,
    $endDate,
    $this->notes
);
```

### الطريقة 2: معالجة غير متزامنة (Queue Jobs) 🆕

**للمعالجات الكبيرة** - المعالجة في الخلفية:

```php
// في AttendanceProcessingManager
// للمعالجات الكبيرة (أقسام)
$this->attendanceProcessingService->processDepartmentAsync(
    $department,
    $startDate,
    $endDate,
    $this->notes
);

// أو لموظف واحد (إذا كانت المعالجة قد تستغرق وقتاً)
$this->attendanceProcessingService->processSingleEmployeeAsync(
    $employee,
    $startDate,
    $endDate,
    $this->notes
);
```

---

## 🔍 مراقبة الـ Jobs

### 1. عرض Jobs في Queue

```bash
# عرض Jobs المعلقة
php artisan queue:work --once

# عرض Jobs الفاشلة
php artisan queue:failed

# إعادة محاولة Jobs الفاشلة
php artisan queue:retry all
```

### 2. مراقبة Logs

```bash
tail -f storage/logs/laravel.log
```

ستجد logs مثل:
```
[2025-11-24 10:00:00] Starting attendance processing job
[2025-11-24 10:05:00] Attendance processing job completed successfully
```

### 3. Database Monitoring

يمكنك مراقبة جدول `jobs`:

```sql
SELECT * FROM jobs WHERE queue = 'default';
SELECT * FROM failed_jobs;
```

---

## ⚙️ الإعدادات

### في `.env`:

```env
QUEUE_CONNECTION=database
```

### خيارات أخرى:

- `sync` - معالجة متزامنة (لا queue)
- `database` - استخدام قاعدة البيانات (موصى به)
- `redis` - استخدام Redis (أسرع، يحتاج Redis)
- `sqs` - Amazon SQS (للـ cloud)

---

## 🎯 متى تستخدم Queue Jobs؟

### ✅ استخدم Queue Jobs عندما:
- معالجة قسم كبير (50+ موظف)
- فترة معالجة طويلة (شهر كامل أو أكثر)
- تريد تجربة مستخدم أفضل (لا انتظار)

### ❌ لا تستخدم Queue Jobs عندما:
- معالجة موظف واحد فقط
- فترة قصيرة (أسبوع أو أقل)
- تريد النتائج فوراً

---

## 🔧 Troubleshooting

### المشكلة: Jobs لا تعمل

**الحل**:
1. تأكد من تشغيل `php artisan queue:work`
2. تحقق من `QUEUE_CONNECTION` في `.env`
3. تأكد من وجود جدول `jobs`

### المشكلة: Jobs تعلق

**الحل**:
```bash
# إعادة تشغيل Worker
php artisan queue:restart
```

### المشكلة: Jobs تفشل

**الحل**:
```bash
# عرض Jobs الفاشلة
php artisan queue:failed

# إعادة محاولة
php artisan queue:retry {job-id}
```

---

## 📊 مثال على الاستخدام في Livewire

```php
// في AttendanceProcessingManager.php

public function processAttendanceAsync(): void
{
    $this->validate();
    
    $startDate = Carbon::parse($this->startDate);
    $endDate = Carbon::parse($this->endDate);
    
    if ($this->processingType === 'department') {
        $department = Department::findOrFail($this->selectedDepartment);
        
        // استخدام Queue
        $this->attendanceProcessingService->processDepartmentAsync(
            $department,
            $startDate,
            $endDate,
            $this->notes
        );
        
        session()->flash('success', 'تم بدء المعالجة في الخلفية. سيتم إشعارك عند الانتهاء.');
    }
}
```

---

## ✅ الخلاصة

- ✅ **Queue Jobs جاهزة للاستخدام**
- ✅ **تم إنشاء Jobs للمعالجة**
- ⚠️ **تحتاج تفعيل**: `php artisan queue:work`
- 📝 **استخدمها للمعالجات الكبيرة فقط**

**الخطوة التالية**: تشغيل `php artisan queue:work` لبدء معالجة Jobs!

