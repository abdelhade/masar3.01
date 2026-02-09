# Quick Start Guide - Translation Files

## 🚀 البدء السريع (Quick Start)

### ما الذي تغير؟
تم إعادة تنظيم ملفات الترجمة من ملف واحد ضخم (`general.php`) إلى ملفات متعددة منظمة.

### الملفات الجديدة
1. **`common.php`** - مصطلحات مشتركة (save, delete, edit, etc.)
2. **`projects.php`** - مصطلحات المشاريع
3. **`daily_progress.php`** - مصطلحات التقدم اليومي

---

## 📖 كيفية الاستخدام (How to Use)

### قبل (Old Way)
```php
__('progress::general.save')           // ❌
__('progress::general.project_name')   // ❌
__('progress::general.add_progress')   // ❌
```

### بعد (New Way)
```php
__('progress::common.save')                    // ✅
__('progress::projects.project_name')          // ✅
__('progress::daily_progress.add_progress')    // ✅
```

---

## 🔍 كيف أعرف أي ملف أستخدم؟ (Which File to Use?)

### استخدم `common.php` لـ:
- الإجراءات: save, edit, delete, create, update, cancel, back
- التأكيدات: yes, no, confirm, are_you_sure
- الحالات: active, pending, completed, in_progress
- الرسائل: loading, success, error, saved_successfully
- الحقول المشتركة: name, description, date, email, phone
- التنقل: dashboard, home, settings, logout

### استخدم `projects.php` لـ:
- معلومات المشروع: project_name, project_type, working_zone
- معلومات العميل: client, client_name, contact_person
- التواريخ: start_date, end_date, project_duration
- جدول العمل: working_days_per_week, daily_work_hours, holidays
- عناصر العمل: work_items, total_quantity, unit
- التقدم: progress, overall_progress, completion_percentage
- الفريق: employee, team_members, manage_employees
- التقارير: progress_report, gantt_chart, export_report

### استخدم `daily_progress.php` لـ:
- إدخال التقدم: add_progress, save_progress
- الكميات: planned_qty, actual_qty, remaining_quantity
- النسب: planned_percentage, actual_percentage
- المقارنات: planned_vs_actual_progress
- الفلاتر: filter_by_date, apply_filter
- التصدير: export_to_excel, export_to_pdf

---

## 🔄 جدول التحويل السريع (Quick Conversion Table)

| القديم (Old) | الجديد (New) |
|-------------|-------------|
| `progress::general.save` | `progress::common.save` |
| `progress::general.cancel` | `progress::common.cancel` |
| `progress::general.delete` | `progress::common.delete` |
| `progress::general.loading` | `progress::common.loading` |
| `progress::general.project_name` | `progress::projects.project_name` |
| `progress::general.start_date` | `progress::projects.start_date` |
| `progress::general.work_items` | `progress::projects.work_items` |
| `progress::general.add_progress` | `progress::daily_progress.add_progress` |
| `progress::general.planned_qty` | `progress::daily_progress.planned_qty` |
| `progress::general.actual_qty` | `progress::daily_progress.actual_qty` |

---

## 🛠️ أدوات المساعدة (Helper Tools)

### 1. البحث في الملف المناسب
```bash
# ابحث في common.php
grep "save" Modules/Progress/Resources/lang/en/common.php

# ابحث في projects.php
grep "project_name" Modules/Progress/Resources/lang/en/projects_new.php

# ابحث في daily_progress.php
grep "add_progress" Modules/Progress/Resources/lang/en/daily_progress.php
```

### 2. استخدم translation_mapper.json
افتح الملف `translation_mapper.json` للحصول على قائمة كاملة بالتطابقات.

---

## 📝 أمثلة عملية (Practical Examples)

### مثال 1: نموذج حفظ بيانات
```php
<!-- Old -->
<button>{{ __('progress::general.save') }}</button>
<button>{{ __('progress::general.cancel') }}</button>

<!-- New -->
<button>{{ __('progress::common.save') }}</button>
<button>{{ __('progress::common.cancel') }}</button>
```

### مثال 2: صفحة المشروع
```php
<!-- Old -->
<label>{{ __('progress::general.project_name') }}</label>
<label>{{ __('progress::general.start_date') }}</label>
<label>{{ __('progress::general.client') }}</label>

<!-- New -->
<label>{{ __('progress::projects.project_name') }}</label>
<label>{{ __('progress::projects.start_date') }}</label>
<label>{{ __('progress::projects.client') }}</label>
```

### مثال 3: صفحة التقدم اليومي
```php
<!-- Old -->
<button>{{ __('progress::general.add_progress') }}</button>
<label>{{ __('progress::general.planned_qty') }}</label>
<label>{{ __('progress::general.actual_qty') }}</label>

<!-- New -->
<button>{{ __('progress::daily_progress.add_progress') }}</button>
<label>{{ __('progress::daily_progress.planned_qty') }}</label>
<label>{{ __('progress::daily_progress.actual_qty') }}</label>
```

---

## ⚠️ ملاحظات مهمة (Important Notes)

1. **لا تحذف الملفات القديمة** حتى اكتمال الترحيل
2. **اختبر بعد كل تغيير** للتأكد من عمل الترجمات
3. **استخدم البحث والاستبدال** في محرر النصوص لتسريع العملية
4. **راجع الوثائق الكاملة** في `README.md` للمزيد من التفاصيل

---

## 🎯 خطوات سريعة للترحيل (Quick Migration Steps)

### الخطوة 1: افتح الملف
```bash
# مثال: افتح ملف create.blade.php
code Modules/Progress/Resources/views/projects/create.blade.php
```

### الخطوة 2: ابحث عن الترجمات القديمة
```
Find: __('progress::general.
```

### الخطوة 3: استبدل بالترجمات الجديدة
استخدم جدول التحويل أعلاه أو `translation_mapper.json`

### الخطوة 4: اختبر الصفحة
افتح الصفحة في المتصفح وتأكد من ظهور الترجمات بشكل صحيح

### الخطوة 5: كرر للملفات الأخرى
كرر الخطوات 1-4 لكل ملف

---

## 📚 وثائق إضافية (Additional Documentation)

- **`README.md`** - دليل شامل للبنية الجديدة
- **`MIGRATION_PLAN.md`** - خطة الترحيل التفصيلية
- **`STATUS.md`** - الحالة الحالية والتقدم
- **`COMPLETION_REPORT.md`** - تقرير الإنجاز الكامل
- **`translation_mapper.json`** - قائمة كاملة بالتطابقات

---

## 💡 نصائح (Tips)

1. **ابدأ بملف صغير** لتتعلم العملية
2. **استخدم Find & Replace** لتسريع العملية
3. **اختبر باللغتين** (العربية والإنجليزية)
4. **وثق أي مشاكل** تواجهها
5. **اطلب المساعدة** إذا احتجت

---

## ❓ أسئلة شائعة (FAQ)

### س: هل يجب تحديث جميع الملفات دفعة واحدة؟
**ج:** لا، يمكنك الترحيل تدريجياً ملف تلو الآخر.

### س: ماذا لو لم أجد المفتاح في الملفات الجديدة؟
**ج:** راجع `translation_mapper.json` أو اسأل فريق التطوير.

### س: هل الملفات القديمة ستحذف؟
**ج:** نعم، لكن بعد اكتمال الترحيل والاختبار فقط.

### س: كيف أتأكد من صحة الترجمة؟
**ج:** افتح الصفحة في المتصفح واختبر اللغتين.

---

## 📞 الدعم (Support)

إذا واجهت أي مشاكل:
1. راجع `README.md`
2. راجع `translation_mapper.json`
3. تواصل مع فريق التطوير

---

**آخر تحديث:** 2026-02-08  
**الإصدار:** 1.0.0
