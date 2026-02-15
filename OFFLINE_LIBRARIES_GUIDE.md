# 📦 دليل المكتبات المحلية (Offline)

## ✅ المكتبات المثبتة محلياً

تم تثبيت جميع المكتبات محلياً لتعمل بدون اتصال بالإنترنت:

### 1. Bootstrap 5.3.2
- ✅ CSS و JavaScript
- ✅ Popper.js (للـ Tooltips و Dropdowns)

### 2. Chart.js 4.4.1
- ✅ جميع أنواع الرسوم البيانية
- ✅ مسجل عالمياً كـ `window.Chart`

### 3. SweetAlert2 11.10.5
- ✅ CSS و JavaScript
- ✅ مسجل عالمياً كـ `window.Swal`

## 🚀 التثبيت

```bash
npm install
```

## 🏗️ البناء

```bash
# للتطوير
npm run dev

# للإنتاج
npm run build
```

## 📝 الاستخدام في Blade

### الطريقة الأساسية (في Layout)

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    
    {{-- CSS Files --}}
    @vite(['resources/css/app.css'])
</head>
<body>
    <!-- المحتوى -->
    
    {{-- JavaScript Files --}}
    @vite([
        'resources/js/app.js',
        'resources/js/chart-setup.js',
        'resources/js/sweetalert-setup.js'
    ])
    
    @stack('scripts')
</body>
</html>
```

### استخدام Chart.js (بدون CDN)

```blade
{{-- في الصفحة --}}
<canvas id="myChart"></canvas>

@push('scripts')
<script>
    // Chart.js متاح عالمياً
    const ctx = document.getElementById('myChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            datasets: [{
                label: 'المبيعات',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: 'rgba(52, 211, 163, 0.2)',
                borderColor: 'rgba(52, 211, 163, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
```

### استخدام SweetAlert2 (بدون CDN)

```blade
@push('scripts')
<script>
    // Swal متاح عالمياً
    Swal.fire({
        title: 'نجح!',
        text: 'تم الحفظ بنجاح',
        icon: 'success',
        confirmButtonText: 'حسناً'
    });
</script>
@endpush
```

## 🔄 استبدال روابط CDN

### قبل (CDN):
```html
<!-- ❌ لا تستخدم هذا -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### بعد (Local):
```blade
<!-- ✅ استخدم هذا -->
@vite(['resources/js/chart-setup.js'])

<script>
    // Chart متاح عالمياً
    new Chart(ctx, {...});
</script>
```

## 📋 قائمة الملفات المحلية

### JavaScript
- `resources/js/app.js` - Bootstrap و المكونات الأساسية
- `resources/js/chart-setup.js` - Chart.js
- `resources/js/sweetalert-setup.js` - SweetAlert2
- `resources/js/components/employee-form-scripts.js` - سكريبتات مخصصة

### CSS
- `resources/css/app.css` - Bootstrap و SweetAlert2 CSS

## 🎨 أمثلة كاملة

### مثال 1: صفحة مع Chart.js

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h5>إحصائيات المبيعات</h5>
        </div>
        <div class="card-body">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل'],
            datasets: [{
                label: 'المبيعات',
                data: [12000, 19000, 15000, 25000],
                borderColor: '#34d3a3',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });
</script>
@endpush
```

### مثال 2: نموذج مع SweetAlert2

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <form id="myForm">
        <div class="mb-3">
            <label class="form-label">الاسم</label>
            <input type="text" class="form-control" name="name">
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('myForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // محاكاة حفظ البيانات
        setTimeout(() => {
            Swal.fire({
                title: 'نجح!',
                text: 'تم حفظ البيانات بنجاح',
                icon: 'success',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#34d3a3'
            });
        }, 500);
    });
</script>
@endpush
```

### مثال 3: تأكيد الحذف مع SweetAlert2

```blade
<button onclick="confirmDelete({{ $item->id }})" class="btn btn-danger btn-sm">
    <i class="las la-trash"></i> حذف
</button>

@push('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من التراجع عن هذا!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e61717',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، احذف!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // تنفيذ الحذف
                fetch(`/items/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => {
                    Swal.fire(
                        'تم الحذف!',
                        'تم حذف العنصر بنجاح.',
                        'success'
                    );
                });
            }
        });
    }
</script>
@endpush
```

## 🎯 أنواع الرسوم البيانية المتاحة

Chart.js يدعم جميع الأنواع:

### 1. Line Chart (خط)
```javascript
new Chart(ctx, {
    type: 'line',
    data: {...}
});
```

### 2. Bar Chart (أعمدة)
```javascript
new Chart(ctx, {
    type: 'bar',
    data: {...}
});
```

### 3. Pie Chart (دائري)
```javascript
new Chart(ctx, {
    type: 'pie',
    data: {...}
});
```

### 4. Doughnut Chart (دونات)
```javascript
new Chart(ctx, {
    type: 'doughnut',
    data: {...}
});
```

### 5. Radar Chart (رادار)
```javascript
new Chart(ctx, {
    type: 'radar',
    data: {...}
});
```

### 6. Polar Area Chart (منطقة قطبية)
```javascript
new Chart(ctx, {
    type: 'polarArea',
    data: {...}
});
```

### 7. Bubble Chart (فقاعات)
```javascript
new Chart(ctx, {
    type: 'bubble',
    data: {...}
});
```

### 8. Scatter Chart (مبعثر)
```javascript
new Chart(ctx, {
    type: 'scatter',
    data: {...}
});
```

## 🎨 SweetAlert2 - أنواع التنبيهات

### Success
```javascript
Swal.fire({
    icon: 'success',
    title: 'نجح!',
    text: 'تم العملية بنجاح'
});
```

### Error
```javascript
Swal.fire({
    icon: 'error',
    title: 'خطأ!',
    text: 'حدث خطأ ما'
});
```

### Warning
```javascript
Swal.fire({
    icon: 'warning',
    title: 'تحذير!',
    text: 'انتبه لهذا الأمر'
});
```

### Info
```javascript
Swal.fire({
    icon: 'info',
    title: 'معلومة',
    text: 'هذه معلومة مهمة'
});
```

### Question
```javascript
Swal.fire({
    icon: 'question',
    title: 'سؤال؟',
    text: 'هل تريد المتابعة؟'
});
```

## 🔧 إعدادات متقدمة

### Chart.js - تخصيص الألوان
```javascript
const chartColors = {
    primary: '#34d3a3',
    secondary: '#1aa1c4',
    success: '#17b860',
    danger: '#e61717',
    warning: '#e6a817',
    info: '#0075e6'
};

new Chart(ctx, {
    type: 'bar',
    data: {
        datasets: [{
            backgroundColor: chartColors.primary,
            borderColor: chartColors.primary
        }]
    }
});
```

### SweetAlert2 - تخصيص الألوان
```javascript
Swal.fire({
    title: 'مخصص',
    text: 'تنبيه مخصص',
    icon: 'success',
    confirmButtonColor: '#34d3a3',
    cancelButtonColor: '#e61717',
    background: '#fff',
    color: '#000'
});
```

## 📚 المراجع

- **Chart.js Docs**: https://www.chartjs.org/docs/latest/
- **SweetAlert2 Docs**: https://sweetalert2.github.io/
- **Bootstrap Docs**: https://getbootstrap.com/docs/5.3/

## ⚠️ ملاحظات مهمة

1. **لا تستخدم CDN**: جميع المكتبات محلية الآن
2. **استخدم @vite**: دائماً استخدم `@vite()` لتحميل الملفات
3. **@push('scripts')**: ضع السكريبتات في `@push('scripts')`
4. **window.Chart و window.Swal**: متاحان عالمياً بعد تحميل الملفات

## ✅ قائمة التحقق

- [x] تثبيت Bootstrap محلياً
- [x] تثبيت Chart.js محلياً
- [x] تثبيت SweetAlert2 محلياً
- [x] إنشاء ملفات setup
- [x] تحديث vite.config.js
- [x] إضافة CSS imports
- [ ] استبدال جميع روابط CDN في الملفات القديمة

## 🔄 الخطوات التالية

1. قم بتشغيل `npm install`
2. قم بتشغيل `npm run build`
3. استبدل روابط CDN في الملفات القديمة بـ `@vite()`
4. اختبر جميع الصفحات

---

**جميع المكتبات الآن محلية وتعمل بدون إنترنت! 🎉**
