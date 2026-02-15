# 📘 دليل Bootstrap 5 للمشروع

## ✅ تم التثبيت بنجاح

المشروع الآن يستخدم Bootstrap 5 كإطار عمل أساسي للواجهات.

## 📦 ما تم تثبيته

- ✅ Bootstrap 5.3.2
- ✅ Bootstrap JavaScript (Modals, Dropdowns, etc.)
- ✅ Popper.js (للـ Tooltips و Dropdowns)

## 🚀 البدء

### تشغيل التطوير
```bash
npm run dev
```

### بناء للإنتاج
```bash
npm run build
```

## 📝 الاستخدام في Blade

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- المحتوى -->
</body>
</html>
```

## 🎨 أمثلة Bootstrap

### الأزرار
```html
<button class="btn btn-primary">حفظ</button>
<button class="btn btn-secondary">إلغاء</button>
<button class="btn btn-success">نجاح</button>
<button class="btn btn-danger">حذف</button>
<button class="btn btn-warning">تحذير</button>
<button class="btn btn-info">معلومات</button>

<!-- أحجام -->
<button class="btn btn-primary btn-sm">صغير</button>
<button class="btn btn-primary">عادي</button>
<button class="btn btn-primary btn-lg">كبير</button>

<!-- زر خاص (مع تدرج لوني) -->
<button class="btn btn-main">إرسال</button>
```

### البطاقات
```html
<div class="card">
    <div class="card-header">
        <h5 class="card-title">العنوان</h5>
    </div>
    <div class="card-body">
        <p class="card-text">المحتوى هنا</p>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">إجراء</button>
    </div>
</div>
```

### النماذج
```html
<form>
    <!-- حقل نصي -->
    <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" placeholder="أدخل الاسم">
    </div>

    <!-- قائمة منسدلة -->
    <div class="mb-3">
        <label for="category" class="form-label">الفئة</label>
        <select class="form-select" id="category">
            <option selected>اختر...</option>
            <option value="1">خيار 1</option>
            <option value="2">خيار 2</option>
        </select>
    </div>

    <!-- منطقة نصية -->
    <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <textarea class="form-control" id="description" rows="3"></textarea>
    </div>

    <!-- Checkbox -->
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="agree">
        <label class="form-check-label" for="agree">
            أوافق على الشروط
        </label>
    </div>

    <button type="submit" class="btn btn-primary">إرسال</button>
</form>
```

### الجداول
```html
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>محمد أحمد</td>
                <td>mohamed@example.com</td>
                <td><span class="badge bg-success">نشط</span></td>
                <td>
                    <button class="btn btn-sm btn-primary">
                        <i class="las la-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger">
                        <i class="las la-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### النوافذ المنبثقة (Modals)
```html
<!-- زر الفتح -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
    فتح النافذة
</button>

<!-- النافذة -->
<div class="modal fade" id="myModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">عنوان النافذة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>محتوى النافذة هنا</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary">حفظ</button>
            </div>
        </div>
    </div>
</div>
```

### التنبيهات
```html
<!-- نجاح -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="las la-check-circle"></i> تم الحفظ بنجاح!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- خطأ -->
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="las la-exclamation-circle"></i> حدث خطأ!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- تحذير -->
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="las la-exclamation-triangle"></i> تحذير!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- معلومات -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="las la-info-circle"></i> معلومة مهمة!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

### الشبكات (Grid System)
```html
<!-- صف مع عمودين متساويين -->
<div class="row">
    <div class="col-md-6">
        <div class="card">عمود 1</div>
    </div>
    <div class="col-md-6">
        <div class="card">عمود 2</div>
    </div>
</div>

<!-- صف مع 3 أعمدة -->
<div class="row">
    <div class="col-md-4">عمود 1</div>
    <div class="col-md-4">عمود 2</div>
    <div class="col-md-4">عمود 3</div>
</div>

<!-- صف مع 4 أعمدة -->
<div class="row">
    <div class="col-md-3">عمود 1</div>
    <div class="col-md-3">عمود 2</div>
    <div class="col-md-3">عمود 3</div>
    <div class="col-md-3">عمود 4</div>
</div>

<!-- Responsive -->
<div class="row">
    <div class="col-12 col-md-6 col-lg-4">
        <!-- عمود واحد على Mobile، نصف على Tablet، ثلث على Desktop -->
    </div>
</div>
```

### القوائم المنسدلة (Dropdowns)
```html
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        القائمة
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#">خيار 1</a></li>
        <li><a class="dropdown-item" href="#">خيار 2</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">خيار 3</a></li>
    </ul>
</div>
```

### التبويبات (Tabs)
```html
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#home">
            الرئيسية
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile">
            الملف الشخصي
        </button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home">
        محتوى الرئيسية
    </div>
    <div class="tab-pane fade" id="profile">
        محتوى الملف الشخصي
    </div>
</div>
```

### الشارات (Badges)
```html
<span class="badge bg-primary">جديد</span>
<span class="badge bg-success">نشط</span>
<span class="badge bg-warning text-dark">قيد الانتظار</span>
<span class="badge bg-danger">غير نشط</span>
<span class="badge bg-info text-dark">معلومات</span>
```

### Tooltips
```html
<button type="button" class="btn btn-secondary" 
        data-bs-toggle="tooltip" 
        data-bs-placement="top" 
        title="نص التلميح">
    مرر فوقي
</button>

<!-- تفعيل Tooltips في JavaScript -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
```

## 🎭 استخدام Alpine.js مع Bootstrap

### Modal مع Alpine.js
```html
<div x-data="{ showModal: false }">
    <button @click="showModal = true" class="btn btn-primary">
        فتح النافذة
    </button>

    <!-- يمكنك استخدام Alpine للتحكم في حالة العرض -->
    <div x-show="showModal" class="modal d-block" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">العنوان</h5>
                    <button @click="showModal = false" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    المحتوى
                </div>
            </div>
        </div>
    </div>
    <div x-show="showModal" class="modal-backdrop fade show"></div>
</div>
```

### Tabs مع Alpine.js
```html
<div x-data="{ activeTab: 'home' }">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a @click="activeTab = 'home'" 
               :class="activeTab === 'home' ? 'nav-link active' : 'nav-link'" 
               href="#">
                الرئيسية
            </a>
        </li>
        <li class="nav-item">
            <a @click="activeTab = 'profile'" 
               :class="activeTab === 'profile' ? 'nav-link active' : 'nav-link'" 
               href="#">
                الملف الشخصي
            </a>
        </li>
    </ul>
    <div class="tab-content mt-3">
        <div x-show="activeTab === 'home'">محتوى الرئيسية</div>
        <div x-show="activeTab === 'profile'">محتوى الملف الشخصي</div>
    </div>
</div>
```

## 📐 Utility Classes

### Spacing
```html
<!-- Margin -->
<div class="m-0">No margin</div>
<div class="m-3">Medium margin</div>
<div class="mt-3">Margin top</div>
<div class="mb-4">Margin bottom</div>
<div class="mx-auto">Center horizontally</div>

<!-- Padding -->
<div class="p-3">Padding</div>
<div class="pt-4">Padding top</div>
<div class="px-5">Padding horizontal</div>
```

### Display
```html
<div class="d-none">Hidden</div>
<div class="d-block">Block</div>
<div class="d-flex">Flexbox</div>
<div class="d-inline">Inline</div>
<div class="d-md-block">Responsive display</div>
```

### Flexbox
```html
<div class="d-flex justify-content-center align-items-center">
    Centered content
</div>

<div class="d-flex justify-content-between">
    Space between
</div>

<div class="d-flex flex-column">
    Column direction
</div>
```

### Text
```html
<p class="text-center">Center text</p>
<p class="text-end">Right align (RTL: left)</p>
<p class="text-primary">Primary color</p>
<p class="text-muted">Muted text</p>
<p class="fw-bold">Bold text</p>
<p class="fs-4">Font size 4</p>
```

## 🎨 الزر الخاص (btn-main)

المشروع يحتوي على زر خاص بتدرج لوني:

```html
<button class="btn btn-main">زر خاص</button>
```

هذا الزر له تصميم خاص مع تدرج لوني من Mint Green إلى Teal Blue.

## 📚 المراجع

- **Bootstrap 5 Docs**: https://getbootstrap.com/docs/5.3/
- **Bootstrap RTL**: https://getbootstrap.com/docs/5.3/getting-started/rtl/
- **Bootstrap Icons**: https://icons.getbootstrap.com/
- **Alpine.js**: https://alpinejs.dev/

## 💡 نصائح

1. **استخدم Bootstrap classes**: `row`, `col-md-6`, `btn btn-primary`
2. **Alpine.js للتفاعل البسيط**: Show/Hide, Tabs, State management
3. **Livewire للسيرفر**: Forms, Data fetching, Real-time updates
4. **RTL Support**: Bootstrap يدعم RTL بشكل كامل
5. **Responsive**: استخدم `col-md-*`, `d-md-*` للتصميم المتجاوب

---

**Bootstrap 5 جاهز للاستخدام! 🎉**
