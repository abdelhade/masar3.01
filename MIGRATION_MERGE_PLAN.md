# 📋 خطة دمج الـ Migrations - Progress Module

## 🎯 الهدف
دمج جميع الأعمدة من الـ migrations القديمة في `Modules/Progress/database/old_migrations/` مع الـ migrations الموجودة في المشروع، مع تجنب التكرار.

---

## 📊 تحليل الجداول

### ✅ جداول موجودة في المشروع الأصلي (نستخدمها كما هي)

#### 1. **clients** 
- **المكان**: `database/migrations/2025_05_10_225713_create_clients_table.php`
- **القرار**: ✅ نستخدم الجدول الأصلي (أكثر تفصيلاً من Progress)
- **الأعمدة الموجودة**: cname, email, phone, phone2, company, address, contact_person, etc.
- **الأعمدة في Progress**: name, contact_person, phone, email, address
- **الإجراء**: لا حاجة لتعديل - الأصلي يحتوي على كل شيء

#### 2. **projects**
- **المكان**: `database/migrations/2025_05_15_113133_create_projects_table.php`
- **القرار**: ✅ تم دمجه مسبقاً
- **الإجراء**: لا حاجة لتعديل

#### 3. **project_types**
- **المكان**: `database/migrations/2025_05_10_214548_create_project_types_table.php`
- **القرار**: ⚠️ نحتاج إضافة `deleted_at`
- **الإجراء**: إضافة soft deletes

---

### 🔧 جداول Progress تحتاج تحديث

#### 4. **employees** ✅
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000000_create_employees_table.php`
- **الأعمدة الحالية**: id, name, position, phone, email, user_id, timestamps, deleted_at
- **الأعمدة المطلوبة**: ✅ كاملة
- **الإجراء**: لا حاجة لتعديل

#### 5. **work_item_categories** ✅
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000001_create_work_item_categories_table.php`
- **الأعمدة الحالية**: id, name, description, order, timestamps, deleted_at
- **الأعمدة المطلوبة من القديم**: id, name, timestamps
- **الإجراء**: ✅ الحالي أفضل - لا حاجة لتعديل

#### 6. **work_items** ⚠️ يحتاج تحديث
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000002_create_work_items_table.php`
- **الأعمدة الحالية**: id, name, description, work_item_category_id, unit, estimated_daily_qty, timestamps, deleted_at
- **الأعمدة المطلوب إضافتها**:
  - `shift` (enum: 'single', 'double', 'triple') - من migration `2025_09_25_125831`
  - `order` (integer) - من migration `2025_10_07_112302`
  - `item_status_id` (foreign key) - من migration `2025_12_04_230857`
- **الإجراء**: ✅ تحديث المطلوب

#### 7. **item_statuses** ✅
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000003_create_item_statuses_table.php`
- **الأعمدة الحالية**: id, name, color, icon, order, is_active, timestamps
- **الأعمدة المطلوبة من القديم**: id, name, color, icon, order, is_active, timestamps
- **الإجراء**: ✅ كامل - لا حاجة لتعديل

#### 8. **project_templates** ⚠️ يحتاج تحديث
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000004_create_project_templates_table.php`
- **الأعمدة الحالية**: id, name, description, project_type_id, timestamps, deleted_at
- **الأعمدة المطلوب إضافتها**:
  - `settings` (json) - من migration `2025_10_09_112956`
  - `weekly_holidays` (string, nullable) - من migration `2025_12_10_124633`
  - `working_days` (tinyInteger)
  - `daily_work_hours` (tinyInteger)
- **الإجراء**: ✅ تحديث المطلوب

#### 9. **subprojects** ⚠️ يحتاج تحديث كامل
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000005_create_subprojects_table.php`
- **الأعمدة الحالية**: id, project_id, name, description, weight, order, timestamps, deleted_at
- **الأعمدة المطلوب إضافتها من القديم**:
  - `start_date` (date, nullable)
  - `end_date` (date, nullable)
  - `total_quantity` (decimal 12,2)
  - `unit` (string) - من migration `2025_11_15_134551`
  - `project_template_id` (foreign key) - من migration `2025_12_01_000001`
- **الإجراء**: ✅ تحديث المطلوب

#### 10. **daily_progress** ⚠️ يحتاج تحديث
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000006_create_daily_progress_table.php`
- **الأعمدة الحالية**: id, project_item_id, employee_id, progress_date, quantity, notes, timestamps
- **الأعمدة المطلوب إضافتها**:
  - `completion_percentage` (decimal 5,2) - من migration `2025_08_25_132225`
  - `deleted_at` - من migration `2025_09_16_183658`
  - indexes إضافية - من migration `2025_11_05_000001`
- **الإجراء**: ✅ تحديث المطلوب

#### 11. **issues** ✅
- **المكان**: `Modules/Progress/database/migrations/2025_07_01_000007_create_issues_table.php`
- **الأعمدة الحالية**: كاملة
- **الإجراء**: ✅ لا حاجة لتعديل

---

### 🆕 جداول مفقودة تحتاج إنشاء

#### 12. **project_items** ⚠️ يحتاج تحديث كامل
- **المكان**: `database/migrations/2025_05_15_113134_create_project_items_table.php` (موجود لكن يحتاج تحديث)
- **الأعمدة الحالية**: موجودة لكن ناقصة
- **الأعمدة المطلوب التأكد منها**:
  - ✅ project_id, project_template_id, work_item_id, item_status_id
  - ✅ item_label, subproject_name
  - ✅ total_quantity, completed_quantity, remaining_quantity
  - ✅ daily_quantity, estimated_daily_qty
  - ✅ start_date, end_date, planned_end_date
  - ✅ duration, shift, lag
  - ✅ predecessor, dependency_type
  - ✅ notes, item_order, is_measurable
- **الإجراء**: ✅ موجود بالكامل

#### 13. **template_items** ❌ مفقود
- **المكان**: يجب إنشاءه في `Modules/Progress/database/migrations/`
- **الأعمدة المطلوبة** (من migration `2025_08_17_203945`):
  - id, project_template_id, work_item_id
  - total_quantity, start_date, end_date, daily_quantity
  - item_order, subproject_name
  - timestamps
- **الإجراء**: ✅ إنشاء مطلوب

#### 14. **issue_comments** ❌ مفقود
- **المكان**: يجب إنشاءه في `Modules/Progress/database/migrations/`
- **الأعمدة المطلوبة** (من migration `2025_12_04_220505`):
  - id, issue_id, user_id, comment
  - timestamps, deleted_at
- **الإجراء**: ✅ إنشاء مطلوب

#### 15. **issue_attachments** ❌ مفقود
- **المكان**: يجب إنشاءه في `Modules/Progress/database/migrations/`
- **الأعمدة المطلوبة** (من migration `2025_12_04_220506`):
  - id, issue_id, file_name, file_path, file_type, file_size
  - uploaded_by, timestamps, deleted_at
- **الإجراء**: ✅ إنشاء مطلوب

---

## 🔄 خطة التنفيذ (خطوة بخطوة)

### المرحلة 1: تحديث الجداول الموجودة ✅

1. ✅ **تحديث work_items** - إضافة: shift, order, item_status_id
2. ✅ **تحديث project_templates** - إضافة: settings, weekly_holidays, working_days, daily_work_hours
3. ✅ **تحديث subprojects** - إضافة: start_date, end_date, total_quantity, unit, project_template_id
4. ✅ **تحديث daily_progress** - إضافة: completion_percentage, deleted_at, indexes
5. ✅ **تحديث project_types** (في database/migrations) - إضافة: deleted_at

### المرحلة 2: إنشاء الجداول المفقودة ✅

6. ✅ **إنشاء template_items**
7. ✅ **إنشاء issue_comments**
8. ✅ **إنشاء issue_attachments**

### المرحلة 3: التنظيف ✅

9. ✅ حذف الـ migrations المؤقتة التي أنشأناها (إذا كانت مكررة)
10. ✅ الاحتفاظ بـ migrations الـ soft deletes الموجودة في Progress

### المرحلة 4: الاختبار ✅

11. ✅ تشغيل `php artisan migrate:fresh --seed --force`
12. ✅ التحقق من إنشاء جميع الجداول
13. ✅ اختبار الوصول لصفحة `/progress/projects`

---

## 📝 ملاحظات مهمة

1. **الجداول المشتركة**: نستخدم الجداول الموجودة في `database/migrations` (clients, projects, project_types)
2. **الجداول الخاصة بـ Progress**: تبقى في `Modules/Progress/database/migrations/`
3. **التسلسل الزمني**: نستخدم تواريخ `2025_07_01_000xxx` للجداول الأساسية في Progress
4. **Foreign Keys**: نتأكد من إنشاء الجداول بالترتيب الصحيح
5. **Soft Deletes**: نحتفظ بالـ migrations الموجودة التي تضيف soft deletes

---

## ✅ الحالة النهائية المتوقعة

### في `database/migrations/`:
- ✅ clients (موجود)
- ✅ projects (موجود ومحدث)
- ✅ project_types (موجود - يحتاج soft deletes)
- ✅ project_items (موجود ومحدث)

### في `Modules/Progress/database/migrations/`:
- ✅ 2025_07_01_000000_create_employees_table.php
- ✅ 2025_07_01_000001_create_work_item_categories_table.php
- ✅ 2025_07_01_000002_create_work_items_table.php (محدث)
- ✅ 2025_07_01_000003_create_item_statuses_table.php
- ✅ 2025_07_01_000004_create_project_templates_table.php (محدث)
- ✅ 2025_07_01_000005_create_subprojects_table.php (محدث)
- ✅ 2025_07_01_000006_create_daily_progress_table.php (محدث)
- ✅ 2025_07_01_000007_create_issues_table.php
- ✅ 2025_07_01_000008_create_template_items_table.php (جديد)
- ✅ 2025_07_01_000009_create_issue_comments_table.php (جديد)
- ✅ 2025_07_01_000010_create_issue_attachments_table.php (جديد)
- ✅ الـ migrations الموجودة للـ soft deletes (نحتفظ بها)

---

## 🚀 جاهز للتنفيذ؟

الخطة جاهزة! هل تريد أن أبدأ بتنفيذ المراحل؟
