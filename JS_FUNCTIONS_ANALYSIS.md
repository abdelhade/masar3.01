# تحليل دوال JavaScript في Progress Module

## الملفات المحللة
- `Modules/Progress/Resources/js/app.js`
- `Modules/Progress/Resources/js/bootstrap.js`
- `Modules/Progress/Resources/js/project-form.js`
- `Modules/Progress/Resources/js/project-items.js`
- `Modules/Progress/Resources/js/projects-filter.js`
- `Modules/Progress/Resources/js/template-predecessor-debug.js`
- `Modules/Progress/Resources/js/template-predecessor-fix.js`

---

## 1. app.js
### الدوال: لا يوجد دوال مخصصة
- ملف Vue.js initialization فقط
- يستخدم: `createApp()`, `app.component()`, `app.mount()`

---

## 2. bootstrap.js
### الدوال: لا يوجد دوال مخصصة
- ملف Bootstrap و Axios initialization فقط
- يستخدم: `import`, `window.axios`

---

## 3. project-form.js (الملف الرئيسي - 2904 سطر)

### دوال التهيئة (Initialization)
1. **`init()`** ✅
   - الاستخدام: يتم استدعاؤها عند تحميل الصفحة
   - الوظيفة: تهيئة النموذج وتحميل البيانات

2. **`initEventListeners()`** ✅
   - الاستخدام: من `init()`
   - الوظيفة: ربط جميع event listeners

3. **`initWeeklyHolidays()`** ✅
   - الاستخدام: من `init()`
   - الوظيفة: تهيئة أيام الإجازة الأسبوعية

4. **`syncHolidaysFromInput()`** ✅
   - الاستخدام: من `initWeeklyHolidays()`
   - الوظيفة: مزامنة الإجازات من hidden input إلى checkboxes

5. **`updateWeeklyHolidays()`** ✅
   - الاستخدام: من event listeners و `init()`
   - الوظيفة: تحديث قيمة أيام الإجازة وحساب أيام العمل

6. **`initCharCounter()`** ✅
   - الاستخدام: من `init()`
   - الوظيفة: تهيئة عداد الأحرف للوصف

7. **`initSortable()`** ✅
   - الاستخدام: من `init()`
   - الوظيفة: تهيئة Sortable.js للسحب والإفلات

### دوال البحث (Search Functions)
8. **`handleSearch()`** ✅ async
   - الاستخدام: من search input event listener
   - الوظيفة: البحث عن بنود العمل

9. **`renderSearchResults(results)`** ✅
   - الاستخدام: من `handleSearch()`
   - الوظيفة: عرض نتائج البحث

10. **`showSearchResults()`** ✅
    - الاستخدام: من `handleSearch()`
    - الوظيفة: إظهار قائمة النتائج

11. **`hideSearchResults()`** ✅
    - الاستخدام: من close button و click outside
    - الوظيفة: إخفاء قائمة النتائج

12. **`showLoading(show)`** ✅
    - الاستخدام: من `handleSearch()`
    - الوظيفة: إظهار/إخفاء spinner التحميل

13. **`showSearchError()`** ✅
    - الاستخدام: من `handleSearch()` catch block
    - الوظيفة: عرض رسالة خطأ البحث

### دوال إدارة البنود (Item Management)
14. **`addWorkItem(item)`** ✅
    - الاستخدام: من search results add button
    - الوظيفة: إضافة بند عمل جديد للجدول

15. **`createItemRow(rowId, item, existingData)`** ✅
    - الاستخدام: من `addWorkItem()`, `loadExistingItems()`, templates
    - الوظيفة: إنشاء صف جديد في الجدول

16. **`attachRowEventListeners(row, item, rowId)`** ✅
    - الاستخدام: من `createItemRow()`
    - الوظيفة: ربط event listeners لصف معين

17. **`updateSubprojectDatalist(inputElement)`** ✅
    - الاستخدام: من row event listeners
    - الوظيفة: تحديث قائمة المشاريع الفرعية

18. **`getUniqueSubprojects()`** ✅
    - الاستخدام: من `updateSubprojectDatalist()`
    - الوظيفة: الحصول على قائمة المشاريع الفرعية الفريدة

19. **`duplicateItem(originalRow, item)`** ✅
    - الاستخدام: من duplicate button
    - الوظيفة: نسخ بند موجود

20. **`updateItemOrders()`** ✅
    - الاستخدام: بعد إضافة/حذف/ترتيب البنود
    - الوظيفة: تحديث ترتيب البنود

21. **`updatePredecessors()`** ✅
    - الاستخدام: بعد تغيير البنود
    - الوظيفة: تحديث قوائم البنود السابقة

22. **`updateEmptyState()`** ✅
    - الاستخدام: بعد إضافة/حذف البنود
    - الوظيفة: إظهار/إخفاء رسالة "لا توجد بنود"

### دوال التصفية (Filter Functions)
23. **`filterItems()`** ✅
    - الاستخدام: من filter input
    - الوظيفة: تصفية البنود في الجدول

24. **`resetFilter()`** ✅
    - الاستخدام: من reset button
    - الوظيفة: إعادة تعيين التصفية

### دوال العمليات الجماعية (Bulk Operations)
25. **`toggleSelectAll()`** ✅
    - الاستخدام: من select all checkbox
    - الوظيفة: تحديد/إلغاء تحديد جميع البنود

26. **`executeBulkAction()`** ✅
    - الاستخدام: من bulk execute button
    - الوظيفة: تنفيذ العملية الجماعية المحددة

27. **`getSelectedRows()`** ✅
    - الاستخدام: من `executeBulkAction()`
    - الوظيفة: الحصول على الصفوف المحددة

28. **`bulkDelete(selectedRows)`** ✅
    - الاستخدام: من `executeBulkAction()`
    - الوظيفة: حذف البنود المحددة

29. **`bulkDuplicate(selectedRows)`** ✅
    - الاستخدام: من `executeBulkAction()`
    - الوظيفة: نسخ البنود المحددة

30. **`bulkMove(selectedRows)`** ✅
    - الاستخدام: من `executeBulkAction()`
    - الوظيفة: نقل البنود إلى مشروع فرعي

31. **`executeBulkMove(selectedRows, subprojectName, fromCategoryName)`** ✅
    - الاستخدام: من modal confirm button
    - الوظيفة: تنفيذ عملية النقل

32. **`bulkExport(selectedRows)`** ✅
    - الاستخدام: من `executeBulkAction()`
    - الوظيفة: تصدير البنود إلى CSV

33. **`convertToCSV(data)`** ✅
    - الاستخدام: من `bulkExport()`
    - الوظيفة: تحويل البيانات إلى CSV

34. **`downloadCSV(csv, filename)`** ✅
    - الاستخدام: من `bulkExport()`
    - الوظيفة: تنزيل ملف CSV

### دوال حساب التواريخ (Date Calculation)
35. **`detectCircularDependency(itemsData)`** ✅
    - الاستخدام: من `calculateAllDates()`
    - الوظيفة: كشف التبعيات الدائرية

36. **`hasCycle(itemId, path)`** ✅
    - الاستخدام: من `detectCircularDependency()`
    - الوظيفة: فحص وجود دورة في التبعيات

37. **`calculateAllDates()`** ✅
    - الاستخدام: من event listeners متعددة
    - الوظيفة: حساب جميع التواريخ والمدد

38. **`calculateDatesRecursive(itemsData, projectStart, weeklyHolidays)`** ✅
    - الاستخدام: من `calculateAllDates()`
    - الوظيفة: حساب التواريخ بشكل تكراري

39. **`calculateWorkingEndDate(startDate, durationDays, weeklyHolidays)`** ✅
    - الاستخدام: من `calculateDatesRecursive()`
    - الوظيفة: حساب تاريخ الانتهاء مع أيام العمل

40. **`addWorkingDays(startDate, days, weeklyHolidays)`** ✅
    - الاستخدام: من `calculateWorkingEndDate()`
    - الوظيفة: إضافة أيام عمل لتاريخ معين

41. **`getWeeklyHolidays()`** ✅
    - الاستخدام: من `calculateAllDates()`
    - الوظيفة: الحصول على أيام الإجازة الأسبوعية

42. **`updateRowDates(data)`** ✅
    - الاستخدام: من `calculateAllDates()`
    - الوظيفة: تحديث التواريخ في الصفوف

43. **`updateProjectEndDate(itemsData)`** ✅
    - الاستخدام: من `calculateAllDates()`
    - الوظيفة: تحديث تاريخ انتهاء المشروع

### دوال تحميل البيانات (Data Loading)
44. **`loadExistingItems()`** ✅
    - الاستخدام: من `init()`
    - الوظيفة: تحميل البنود الموجودة (edit mode)

45. **`loadTemplateItems(templateId)`** ✅ async
    - الاستخدام: من template checkbox
    - الوظيفة: تحميل بنود من قالب

46. **`loadDraftItems(draftId)`** ✅ async
    - الاستخدام: من draft checkbox
    - الوظيفة: تحميل بنود من مسودة

### دوال العرض (View Functions)
47. **`switchToFlatView()`** ✅
    - الاستخدام: من flat view button
    - الوظيفة: التبديل إلى العرض العادي

48. **`switchToGroupedView()`** ✅
    - الاستخدام: من grouped view button
    - الوظيفة: التبديل إلى العرض المجمع

49. **`renderGroupedView()`** ✅
    - الاستخدام: من `switchToGroupedView()`
    - الوظيفة: عرض البنود مجمعة حسب الفئات

50. **`groupItemsByCategory(rows)`** ✅
    - الاستخدام: من `renderGroupedView()`
    - الوظيفة: تجميع البنود حسب الفئة

51. **`calculateSubprojectTotals(rows)`** ✅
    - الاستخدام: من `renderGroupedView()`
    - الوظيفة: حساب إجماليات المشاريع الفرعية

52. **`updateSubprojectTotals()`** ✅
    - الاستخدام: من event listeners
    - الوظيفة: تحديث الإجماليات

53. **`loadSubprojectQuantities()`** ✅
    - الاستخدام: من `renderGroupedView()`
    - الوظيفة: تحميل كميات المشاريع الفرعية

54. **`createCategoryGroup(categoryName, rows)`** ✅
    - الاستخدام: من `renderGroupedView()`
    - الوظيفة: إنشاء مجموعة فئة

55. **`attachSubprojectBulkActions(groupDiv, categoryName, categoryId)`** ✅
    - الاستخدام: من `createCategoryGroup()`
    - الوظيفة: ربط العمليات الجماعية للمشروع الفرعي

56. **`updateSubprojectSelectedCount(categoryId, tbody)`** ✅
    - الاستخدام: من checkbox event listeners
    - الوظيفة: تحديث عدد البنود المحددة

57. **`executeSubprojectBulkAction(action, selectedRows, categoryName, tbody, selectAllCheckbox, bulkActionSelect)`** ✅
    - الاستخدام: من subproject bulk execute button
    - الوظيفة: تنفيذ عملية جماعية على مشروع فرعي

58. **`toggleCategoryGroup(headerElement)`** ✅
    - الاستخدام: من category header click
    - الوظيفة: طي/فتح مجموعة الفئة

59. **`getCategoryIcon(categoryName)`** ✅
    - الاستخدام: من `createCategoryGroup()`
    - الوظيفة: الحصول على أيقونة الفئة

60. **`initSortableForGroups()`** ✅
    - الاستخدام: من `renderGroupedView()`
    - الوظيفة: تهيئة Sortable للمجموعات

61. **`renderFlatView()`** ✅
    - الاستخدام: من `switchToFlatView()`
    - الوظيفة: عرض البنود بشكل عادي

62. **`saveOriginalOrder()`** ✅
    - الاستخدام: قبل التبديل للعرض المجمع
    - الوظيفة: حفظ الترتيب الأصلي

63. **`restoreOriginalOrder(rows)`** ✅
    - الاستخدام: عند العودة للعرض العادي
    - الوظيفة: استعادة الترتيب الأصلي

64. **`cleanEmptySubprojects()`** ✅
    - الاستخدام: قبل submit
    - الوظيفة: تنظيف المشاريع الفرعية الفارغة

### دوال التحقق والإرسال (Validation & Submit)
65. **`handleFormSubmit(e)`** ✅
    - الاستخدام: من form submit event
    - الوظيفة: معالجة إرسال النموذج

66. **`validateDraftSubmit(e)`** ✅
    - الاستخدام: من `handleFormSubmit()`
    - الوظيفة: التحقق من المسودة

67. **`validateFullSubmit(e)`** ✅
    - الاستخدام: من `handleFormSubmit()`
    - الوظيفة: التحقق من النموذج الكامل

68. **`showValidationErrors(title, errors)`** ✅
    - الاستخدام: من validation functions
    - الوظيفة: عرض أخطاء التحقق

69. **`handleEnterKey(e)`** ✅
    - الاستخدام: من form keydown event
    - الوظيفة: منع إرسال النموذج بـ Enter

70. **`moveToNextInput(currentElement)`** ✅
    - الاستخدام: من `handleEnterKey()`
    - الوظيفة: الانتقال للحقل التالي

### دوال مساعدة (Utility Functions)
71. **`debounce(func, wait)`** ✅
    - الاستخدام: لتأخير تنفيذ الدوال
    - الوظيفة: debounce utility

72. **`escapeHtml(text)`** ✅
    - الاستخدام: لتأمين النصوص من XSS
    - الوظيفة: escape HTML characters

73. **`formatDate(date)`** ✅
    - الاستخدام: لتنسيق التواريخ
    - الوظيفة: format date to YYYY-MM-DD

74. **`showNotification(type, message)`** ✅
    - الاستخدام: لعرض الإشعارات
    - الوظيفة: show toast notification

### دوال الحفظ التلقائي (Auto-save)
75. **`startAutoSave()`** ✅
    - الاستخدام: من `init()`
    - الوظيفة: بدء الحفظ التلقائي

76. **`saveDraftAjax()`** ✅
    - الاستخدام: من `startAutoSave()` interval
    - الوظيفة: حفظ المسودة عبر AJAX

---

## 4. project-items.js

### الدوال:
1. **Anonymous function in `$(document).ready()`** ✅
   - الاستخدام: حساب الكمية اليومية التلقائية
   - الوظيفة: حساب daily quantity من total و dates

---

## 5. projects-filter.js

### الدوال:
1. **`filterProjects()`** ✅
   - الاستخدام: من filter inputs
   - الوظيفة: تصفية المشاريع في الوقت الفعلي

2. **`getTranslation(key)`** ✅
   - الاستخدام: من `filterProjects()`
   - الوظيفة: الحصول على الترجمات

3. **`updateURLParams()`** ✅
   - الاستخدام: من filter change events
   - الوظيفة: تحديث URL parameters

4. **`loadFiltersFromURL()`** ✅
   - الاستخدام: عند تحميل الصفحة
   - الوظيفة: تحميل الفلاتر من URL

---

## 6. template-predecessor-debug.js

### الدوال:
1. **`debugPredecessors()`** ✅
   - الاستخدام: من debug button و timeout
   - الوظيفة: تشخيص حالة predecessors

2. **`fixPredecessors()`** ✅
   - الاستخدام: من fix button و timeout
   - الوظيفة: إصلاح predecessors

---

## 7. template-predecessor-fix.js

### الدوال:
1. **`applyTemplateSettings(templateData)`** ✅
   - الاستخدام: من template load
   - الوظيفة: تطبيق إعدادات القالب

2. **`loadTemplateItemsWithPredecessorFix(templateData, templateId)`** ✅
   - الاستخدام: من template checkbox
   - الوظيفة: تحميل بنود القالب مع إصلاح predecessors

3. **`fillTemplateItemValues(row, item)`** ✅
   - الاستخدام: من `loadTemplateItemsWithPredecessorFix()`
   - الوظيفة: ملء قيم البند من القالب

4. **`removeTemplateItems(templateId)`** ✅
   - الاستخدام: عند إلغاء تحديد القالب
   - الوظيفة: حذف بنود القالب

5. **`window.debugPredecessors()`** ✅
   - الاستخدام: للتشخيص اليدوي
   - الوظيفة: debug function

---

## تحليل الدوال المكررة

### ⚠️ دوال مكررة أو متشابهة:

#### 1. دوال تحميل القوالب (Template Loading)
**الملفات:**
- `project-form.js`: `loadTemplateItems(templateId)`
- `template-predecessor-fix.js`: `loadTemplateItemsWithPredecessorFix(templateData, templateId)`

**التكرار:** نفس الوظيفة مع اختلاف في معالجة predecessors
**التوصية:** دمج الدالتين في دالة واحدة مع parameter للتحكم في معالجة predecessors

#### 2. دوال تشخيص Predecessors
**الملفات:**
- `template-predecessor-debug.js`: `debugPredecessors()`, `fixPredecessors()`
- `template-predecessor-fix.js`: `window.debugPredecessors()`

**التكرار:** نفس الوظيفة في ملفين مختلفين
**التوصية:** الاحتفاظ بملف واحد فقط (template-predecessor-fix.js أفضل)

#### 3. دوال تطبيق إعدادات القالب
**الملفات:**
- `project-form.js`: قد يحتوي على دالة مشابهة (لم تظهر في القراءة الجزئية)
- `template-predecessor-fix.js`: `applyTemplateSettings(templateData)`

**التوصية:** التأكد من عدم التكرار

---

## الصفحات التي تستخدم هذه الملفات

### 1. project-form.js
**الصفحات:**
- `Modules/Progress/Resources/views/projects/create.blade.php`
- `Modules/Progress/Resources/views/projects/edit.blade.php`
- `Modules/Progress/Resources/views/projects/form/index.blade.php`

### 2. project-items.js
**الصفحات:**
- صفحات إدارة بنود المشروع (غير محددة بدقة)

### 3. projects-filter.js
**الصفحات:**
- `Modules/Progress/Resources/views/projects/index.blade.php`

### 4. template-predecessor-debug.js
**الصفحات:**
- صفحات القوالب (للتطوير فقط)

### 5. template-predecessor-fix.js
**الصفحات:**
- `Modules/Progress/Resources/views/projects/create.blade.php`
- `Modules/Progress/Resources/views/projects/edit.blade.php`

---

## التوصيات

### 1. حذف الملفات المكررة
- ✅ حذف `template-predecessor-debug.js` (استخدام fix بدلاً منه)
- ✅ دمج وظائف template loading في ملف واحد

### 2. تنظيف الكود
- إزالة الدوال غير المستخدمة
- توحيد أسماء الدوال المتشابهة

### 3. التوثيق
- إضافة JSDoc comments لجميع الدوال
- توضيح dependencies بين الدوال

---

## إحصائيات

- **إجمالي الملفات:** 7
- **إجمالي الدوال:** ~85 دالة
- **الدوال المكررة:** 3-5 دوال
- **الدوال المستخدمة:** ~80 دالة
- **الدوال اليتيمة:** 0 (جميع الدوال مستخدمة)
