# دليل استخدام Bootstrap Gradient Theme

## نظرة عامة
هذا الـ theme يستبدل جميع الألوان العادية في Bootstrap 5 بـ gradients جميلة ومتناسقة مع هوية massar1.02 ERP.

## التثبيت والاستخدام

### 1. التفعيل
الـ theme مفعّل تلقائياً في `vite.config.js`. لتطبيق التغييرات:

```bash
npm run build
# أو للتطوير
npm run dev
```

### 2. الاستخدام في Blade Templates

#### الأزرار (Buttons)
```blade
<!-- Primary Button مع gradient -->
<button class="btn btn-primary">{{ __('common.save') }}</button>

<!-- Success Button مع gradient -->
<button class="btn btn-success">{{ __('common.submit') }}</button>

<!-- Danger Button مع gradient -->
<button class="btn btn-danger">{{ __('common.delete') }}</button>

<!-- Warning Button مع gradient -->
<button class="btn btn-warning">{{ __('common.warning') }}</button>

<!-- Info Button مع gradient -->
<button class="btn btn-info">{{ __('common.info') }}</button>
```

#### البطاقات (Cards)
```blade
<!-- Card عادية مع gradient خفيف -->
<div class="card">
    <div class="card-header">{{ __('common.title') }}</div>
    <div class="card-body">
        <!-- المحتوى -->
    </div>
</div>

<!-- Card ملونة بالكامل -->
<div class="card bg-primary">
    <div class="card-body text-white">
        <!-- محتوى أبيض على gradient أزرق -->
    </div>
</div>
```

#### الشارات (Badges)
```blade
<span class="badge bg-primary">{{ __('common.new') }}</span>
<span class="badge bg-success">{{ __('common.active') }}</span>
<span class="badge bg-danger">{{ __('common.inactive') }}</span>
<span class="badge bg-warning">{{ __('common.pending') }}</span>
```

#### التنبيهات (Alerts)
```blade
<div class="alert alert-success">
    {{ __('common.success_message') }}
</div>

<div class="alert alert-danger">
    {{ __('common.error_message') }}
</div>
```

#### Progress Bars
```blade
<div class="progress">
    <div class="progress-bar" style="width: 75%">75%</div>
</div>

<div class="progress">
    <div class="progress-bar bg-success" style="width: 50%">50%</div>
</div>
```

#### الجداول (Tables)
```blade
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>{{ __('common.name') }}</th>
            <th>{{ __('common.status') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $item->name }}</td>
            <td><span class="badge bg-success">Active</span></td>
        </tr>
    </tbody>
</table>
```

### 3. Gradient Utilities المخصصة

#### Background Gradients
```blade
<!-- Brand Gradient (Mint + Teal) -->
<div class="bg-gradient-brand p-4 text-white">
    محتوى مع gradient العلامة التجارية
</div>

<!-- Sunset Gradient -->
<div class="bg-gradient-sunset p-4 text-white">
    محتوى مع gradient الغروب
</div>

<!-- Ocean Gradient -->
<div class="bg-gradient-ocean p-4 text-white">
    محتوى مع gradient المحيط
</div>

<!-- Forest Gradient -->
<div class="bg-gradient-forest p-4 text-white">
    محتوى مع gradient الغابة
</div>
```

#### Text Gradients
```blade
<h1 class="text-gradient-primary">
    عنوان مع gradient في النص
</h1>

<h2 class="text-gradient-brand">
    عنوان مع gradient العلامة التجارية
</h2>
```

#### Animated Gradients
```blade
<div class="bg-gradient-brand gradient-animated p-4 text-white">
    محتوى مع gradient متحرك
</div>
```

### 4. الألوان المتاحة

#### Primary (Mint Green)
- `btn-primary` - زر أساسي
- `bg-primary` - خلفية أساسية
- `badge bg-primary` - شارة أساسية
- `alert-primary` - تنبيه أساسي

#### Secondary (Teal Blue)
- `btn-secondary` - زر ثانوي
- `bg-secondary` - خلفية ثانوية
- `badge bg-secondary` - شارة ثانوية
- `alert-secondary` - تنبيه ثانوي

#### Success (Green)
- `btn-success` - زر نجاح
- `bg-success` - خلفية نجاح
- `badge bg-success` - شارة نجاح
- `alert-success` - تنبيه نجاح

#### Danger (Red)
- `btn-danger` - زر خطر
- `bg-danger` - خلفية خطر
- `badge bg-danger` - شارة خطر
- `alert-danger` - تنبيه خطر

#### Warning (Yellow)
- `btn-warning` - زر تحذير
- `bg-warning` - خلفية تحذير
- `badge bg-warning` - شارة تحذير
- `alert-warning` - تنبيه تحذير

#### Info (Blue)
- `btn-info` - زر معلومات
- `bg-info` - خلفية معلومات
- `badge bg-info` - شارة معلومات
- `alert-info` - تنبيه معلومات

### 5. Dark Mode Support

الـ theme يدعم الوضع الداكن تلقائياً:

```blade
<div class="dark">
    <!-- جميع المكونات ستتكيف مع الوضع الداكن -->
    <div class="card">
        <div class="card-body">
            محتوى في الوضع الداكن
        </div>
    </div>
</div>
```

### 6. التأثيرات الحركية

جميع الأزرار والبطاقات تحتوي على:
- ✅ Hover effects مع رفع العنصر
- ✅ Shadow effects ديناميكية
- ✅ Smooth transitions
- ✅ Shine effect على الأزرار

### 7. أمثلة عملية

#### نموذج تسجيل دخول
```blade
<div class="card">
    <div class="card-header bg-gradient-brand text-white">
        <h4>{{ __('auth.login') }}</h4>
    </div>
    <div class="card-body">
        <form>
            <div class="mb-3">
                <label class="form-label">{{ __('auth.email') }}</label>
                <input type="email" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('auth.password') }}</label>
                <input type="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">
                {{ __('auth.login') }}
            </button>
        </form>
    </div>
</div>
```

#### Dashboard Card
```blade
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted">{{ __('dashboard.total_sales') }}</h6>
                <h2 class="text-gradient-brand">$125,430</h2>
            </div>
            <div class="bg-gradient-success p-3 rounded">
                <i class="las la-chart-line text-white fs-2"></i>
            </div>
        </div>
        <div class="progress mt-3">
            <div class="progress-bar bg-success" style="width: 75%"></div>
        </div>
    </div>
</div>
```

#### Action Buttons Group
```blade
<div class="btn-group">
    <button class="btn btn-success">
        <i class="las la-check"></i> {{ __('common.approve') }}
    </button>
    <button class="btn btn-danger">
        <i class="las la-times"></i> {{ __('common.reject') }}
    </button>
    <button class="btn btn-info">
        <i class="las la-eye"></i> {{ __('common.view') }}
    </button>
</div>
```

## المتغيرات المتاحة (CSS Variables)

يمكنك استخدام المتغيرات التالية في CSS المخصص:

```css
/* Primary Gradients */
var(--gradient-primary)
var(--gradient-primary-hover)
var(--gradient-primary-light)
var(--gradient-primary-dark)

/* Secondary Gradients */
var(--gradient-secondary)
var(--gradient-secondary-hover)

/* Success Gradients */
var(--gradient-success)
var(--gradient-success-hover)

/* Danger Gradients */
var(--gradient-danger)
var(--gradient-danger-hover)

/* Warning Gradients */
var(--gradient-warning)
var(--gradient-warning-hover)

/* Info Gradients */
var(--gradient-info)
var(--gradient-info-hover)

/* Special Gradients */
var(--gradient-brand)
var(--gradient-sunset)
var(--gradient-ocean)
var(--gradient-forest)
```

## التخصيص

لتخصيص الـ gradients، قم بتعديل المتغيرات في `:root`:

```css
:root {
    --gradient-primary: linear-gradient(135deg, #your-color-1 0%, #your-color-2 100%);
}
```

## الأداء

- ✅ جميع الـ gradients محسّنة للأداء
- ✅ استخدام CSS Variables لسهولة التخصيص
- ✅ Transitions سلسة وسريعة
- ✅ متوافق مع جميع المتصفحات الحديثة

## الدعم

للمزيد من المعلومات أو المساعدة، راجع:
- `resources/css/design-system.css` - نظام التصميم الأساسي
- `resources/css/themes/bootstrap-gradient-theme.css` - ملف الـ theme
