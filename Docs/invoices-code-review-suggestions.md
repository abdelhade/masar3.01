# مراجعة كود الفواتير — اقتراحات وتنفيذ

## ما تم تنفيذه في هذه المراجعة

### 1. توحيد الـ Sidebar لنوع 26 (اتفاقية تسعير)
- **المشكلة**: في `edit.blade.php` و `show.blade.php` كانت قائمة أنواع المبيعات للـ sidebar `[10, 12, 14, 16, 22]` بدون **26**، فصفحة تعديل/عرض فاتورة من نوع "اتفاقية تسعير" كانت تعرض sidebar غير صحيح.
- **التعديل**: تمت إضافة **26** في كلا الملفين ليتوافقا مع `create` و `index` و `view-invoice`.

### 2. ترتيب الـ sections في edit.blade.php
- تم توحيد ترتيب الـ sections مع باقي صفحات الفواتير: `body_class` → `hide_footer` → `push('styles')` → `sidebar` → `content` لتحسين القراءة والصيانة.

### 3. إزالة كود غير مستخدم من view-invoice.blade.php
- المتغيران `$titles` و `$permissionName` كانا يُحسبان ولا يُستخدمان (الصلاحية تُفحص في الـ Controller قبل عرض الصفحة).
- تم حذف الـ `@php` block بالكامل من الصفحة.

---

## اقتراحات للمستقبل (لم تُنفذ)

### 1. مصفوفة أسماء أنواع الفواتير ($titles)
- **الوضع الحالي**: المصفوفة مكررة في عدة أماكن: `invoice-head.blade.php`, `InvoiceController`, `CreateInvoiceForm`, `EditInvoiceForm`, `InvoiceWorkflowController`, `show.blade.php`.
- **الاقتراح**: نقلها لمصدر واحد (مثلاً `App\Enums\OperationTypeEnum` أو config أو View Composer) واستخدامها من كل الـ views والـ controllers لتقليل التكرار وتسهيل الصيانة.

### 2. إخفاء الـ footer المكرر في Livewire
- **الوضع الحالي**: في `create-invoice-form.blade.php` و `edit-invoice-form.blade.php` يوجد `<style> footer.footer { display: none !important; } </style>` بينما الـ layout يخفي الـ footer أصلاً عند وجود `@section('hide_footer')`.
- **الاقتراح**: إزالة هذين الـ style blocks من الـ Livewire لتقليل التكرار (اختياري؛ الإبقاء عليهما لا يضر ويعمل كـ safety).

### 3. تكرار منطق "صفحة فاتورة" (body_class + hide_footer + styles)
- **الوضع الحالي**: نفس الثلاثة أسطر (`body_class`, `hide_footer`, `@push('styles')` مع include الأنماط) مكررة في create, edit, index, view-invoice.
- **الاقتراح**: إنشاء Blade Component أو @include جزئي مثل `@include('invoices::partials.invoice-page-layout')` يضع هذه الـ sections مرة واحدة، ثم كل صفحة تضع فقط `sidebar` و `content` (يتطلب تعديل بسيط في الهيكل).

### 4. صفحة show.blade.php
- **الوضع الحالي**: لم تُطبَّق عليها تحسينات "صفحة فاتورة كاملة" (لا `invoice-page` ولا إخفاء footer ولا أنماط مساحة كاملة).
- **الاقتراح**: إذا كانت `show` تُستخدم كصفحة عرض تفاصيل الفاتورة بشكل منفصل عن `view-invoice` وترغب بنفس تجربة "ملء الشاشة"، يمكن إضافة `body_class`, `hide_footer`, و `invoice-page-styles` لها؛ وإلا يمكن تركها كما هي.

### 5. أنواع الفواتير في النماذج (Create/Edit)
- في `create-invoice-form.blade.php` و `edit-invoice-form.blade.php` شرط "نوع السعر" يستخدم `[10, 12, 14, 16, 22]` بدون 26. إن كان نوع 26 يحتاج أيضاً لاختيار نوع السعر، يُفضّل إضافة 26 لهذا الشرط؛ وإن كان سلوك 26 مختلفاً فالأفضل ترك الشرط كما هو مع توثيق السبب.

---

## ملخص الملفات التي تم تعديلها

| الملف | التعديل |
|-------|---------|
| `Modules/Invoices/Resources/views/invoices/edit.blade.php` | إضافة 26 للـ sidebar، توحيد ترتيب الـ sections |
| `Modules/Invoices/Resources/views/invoices/show.blade.php` | إضافة 26 للـ sidebar |
| `Modules/Invoices/Resources/views/invoices/view-invoice.blade.php` | إزالة كود غير مستخدم (`$titles`, `$permissionName`) |
