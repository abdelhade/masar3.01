# خطة مقارنة ودمج الـ Migrations

## الجداول المكررة (موجودة في المشروع الأصلي و Progress)

### 1. **clients** ✅
- **الموقع الأصلي**: `database/migrations/2025_05_10_225713_create_clients_table.php`
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_07_06_142218_create_clients_table.php`
- **الحل**: نستخدم الجدول الأصلي (أكثر تفصيلاً)
- **الأعمدة الزيادة في Progress**: لا يوجد (الأصلي أشمل)

### 2. **employees** ⚠️
- **الموقع الأصلي**: لا يوجد في database/migrations
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_07_06_145346_create_employees_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000000_create_employees_table.php` (أنشأناه)
- **الحل**: نستخدم الـ migration الجديد في Progress

### 3. **projects** ✅ (تم دمجه مسبقاً)
- **الموقع الأصلي**: `database/migrations/2025_05_15_113133_create_projects_table.php`
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_08_06_142121_create_projects_table.php`
- **الحل**: تم دمج الأعمدة الزيادة مسبقاً

### 4. **project_items** ⚠️
- **الموقع الأصلي**: `database/migrations/2025_05_15_113134_create_project_items_table.php` (أنشأناه)
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_08_06_142359_create_project_items_table.php`
- **الحل**: نحتاج دمج كل الأعمدة الزيادة من الـ migrations القديمة

### 5. **work_items** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_08_06_142315_create_work_items_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000002_create_work_items_table.php`
- **الحل**: نحتاج دمج الأعمدة الزيادة

### 6. **daily_progress** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_08_06_142436_create_daily_progress_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000006_create_daily_progress_table.php`
- **الحل**: نحتاج دمج الأعمدة الزيادة

### 7. **project_types** ✅
- **الموقع الأصلي**: `database/migrations/2025_05_10_214548_create_project_types_table.php`
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_08_24_145101_create_project_types_table.php`
- **الحل**: نستخدم الأصلي ونضيف الأعمدة الزيادة

### 8. **work_item_categories** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_10_07_094009_create_work_item_categories_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000001_create_work_item_categories_table.php`
- **الحل**: نستخدم الجديد

### 9. **subprojects** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_10_25_131721_create_subprojects_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000005_create_subprojects_table.php`
- **الحل**: نحتاج دمج الأعمدة الزيادة

### 10. **project_templates** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_08_17_203900__create_project_templates_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000004_create_project_templates_table.php`
- **الحل**: نحتاج دمج الأعمدة الزيادة

### 11. **item_statuses** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_12_04_215648_create_item_statuses_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000003_create_item_statuses_table.php`
- **الحل**: نستخدم الجديد

### 12. **issues** ⚠️
- **الموقع الأصلي**: لا يوجد
- **Progress القديم**: `Modules/Progress/database/old_migrations/2025_12_04_220504_create_issues_table.php`
- **Progress الجديد**: `Modules/Progress/database/migrations/2025_07_01_000007_create_issues_table.php`
- **الحل**: نستخدم الجديد

## الجداول الإضافية في Progress (غير موجودة في الأصلي)

1. **template_items** - نحتاج إنشاءه
2. **issue_comments** - نحتاج إنشاءه
3. **issue_attachments** - نحتاج إنشاءه
4. **employee_project** - تم إنشاءه في migration الـ employees

## خطة العمل

### المرحلة 1: قراءة وتحليل الأعمدة الزيادة
1. قراءة كل الـ migrations القديمة التي تضيف أعمدة للجداول الموجودة
2. تجميع قائمة بكل الأعمدة المطلوبة لكل جدول

### المرحلة 2: تحديث الـ Migrations الموجودة
1. تحديث `project_items` migration بكل الأعمدة
2. تحديث `work_items` migration بكل الأعمدة
3. تحديث `daily_progress` migration بكل الأعمدة
4. تحديث `subprojects` migration بكل الأعمدة
5. تحديث `project_templates` migration بكل الأعمدة
6. تحديث `project_types` migration بكل الأعمدة (في database/migrations)

### المرحلة 3: إنشاء الجداول المفقودة
1. إنشاء `template_items` migration
2. إنشاء `issue_comments` migration
3. إنشاء `issue_attachments` migration

### المرحلة 4: حذف الـ Migrations المكررة
1. حذف كل الـ migrations الموجودة حالياً في `Modules/Progress/database/migrations/` (ما عدا الـ soft deletes)
2. الاحتفاظ فقط بالـ migrations التي تضيف soft deletes

### المرحلة 5: التنفيذ
1. تشغيل `php artisan migrate:fresh --seed --force`
2. اختبار الوصول لصفحة Progress

## الأعمدة المطلوب دمجها (من الـ migrations القديمة)

### project_items
- `remaining_quantity`
- `planned_end_date`
- `shift`
- `predecessor`
- `dependency_type`
- `notes`
- `subproject_name`
- `item_order`
- `item_label`
- `duration`
- `lag`
- `is_measurable`
- `item_status_id`
- `project_template_id`
- `estimated_daily_qty`

### work_items
- `work_item_category_id`
- `unit`
- `estimated_daily_qty`
- `shift` (enum)
- `order`
- `item_status_id`
- `deleted_at`

### daily_progress
- `completion_percentage`
- `deleted_at`
- indexes

### subprojects
- `unit`
- `weight`
- `order`
- `project_template_id`

### project_templates
- `settings` (json)
- `weekly_holidays`
- `deleted_at`

### project_types
- `deleted_at`
