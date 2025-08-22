# دليل نظام الترجمة - Masaar ERP

## نظرة عامة

تم إعادة هيكلة نظام الترجمة في المشروع ليكون أكثر تنظيماً وقابلية للصيانة. تم تقسيم ملفات الترجمة إلى ملفات منفصلة حسب الوظائف والوحدات.

## هيكل ملفات الترجمة

```
resources/lang/
├── ar/                          # اللغة العربية
│   ├── common.php              # النصوص المشتركة
│   ├── navigation.php          # القوائم والتنقل
│   ├── forms.php               # النماذج
│   ├── messages.php            # الرسائل
│   ├── errors.php              # رسائل الخطأ
│   ├── hr.php                  # الموارد البشرية
│   ├── reports.php             # التقارير
│   ├── items.php               # الأصناف
│   └── ar.json                 # ملف JSON للتوافق مع النظام القديم
└── en/                          # اللغة الإنجليزية
    ├── common.php              # النصوص المشتركة
    ├── navigation.php          # القوائم والتنقل
    ├── forms.php               # النماذج
    ├── messages.php            # الرسائل
    ├── errors.php              # رسائل الخطأ
    ├── hr.php                  # الموارد البشرية
    ├── reports.php             # التقارير
    ├── items.php               # الأصناف
    └── en.json                 # ملف JSON للتوافق مع النظام القديم
```

## كيفية الاستخدام

### 1. في ملفات Blade

```php
{{-- النصوص المشتركة --}}
{{ __('common.save') }}
{{ __('common.update') }}
{{ __('common.delete') }}

{{-- القوائم والتنقل --}}
{{ __('navigation.home') }}
{{ __('navigation.master_data') }}
{{ __('navigation.clients') }}

{{-- النماذج --}}
{{ __('forms.full_name') }}
{{ __('forms.email') }}
{{ __('forms.phone') }}

{{-- الرسائل --}}
{{ __('messages.created_successfully') }}
{{ __('messages.updated_successfully') }}

{{-- رسائل الخطأ --}}
{{ __('errors.page_not_found') }}
{{ __('errors.access_denied') }}

{{-- الموارد البشرية --}}
{{ __('hr.departments') }}
{{ __('hr.employees') }}
{{ __('hr.shifts') }}

{{-- التقارير --}}
{{ __('reports.sales_report') }}
{{ __('reports.inventory_report') }}

{{-- الأصناف --}}
{{ __('items.item_management') }}
{{ __('items.add_new_item') }}
```

### 2. في ملفات PHP

```php
// النصوص المشتركة
__('common.save')
__('common.update')
__('common.delete')

// القوائم والتنقل
__('navigation.home')
__('navigation.master_data')

// النماذج
__('forms.full_name')
__('forms.email')

// الرسائل
__('messages.created_successfully')
__('messages.updated_successfully')

// رسائل الخطأ
__('errors.page_not_found')
__('errors.access_denied')
```

### 3. في ملفات Livewire

```php
// في الكلاس
public function save()
{
    // ... logic
    session()->flash('message', __('messages.saved_successfully'));
}

// في Blade template
@if (session()->has('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
@endif
```

## إضافة ترجمات جديدة

### 1. إضافة ترجمة للنصوص المشتركة

```php
// في resources/lang/ar/common.php
return [
    // ... existing translations
    'new_common_text' => 'نص مشترك جديد',
];
```

### 2. إضافة ترجمة للقوائم

```php
// في resources/lang/ar/navigation.php
return [
    // ... existing translations
    'new_menu_item' => 'عنصر قائمة جديد',
];
```

### 3. إضافة ترجمة للنماذج

```php
// في resources/lang/ar/forms.php
return [
    // ... existing translations
    'new_form_field' => 'حقل نموذج جديد',
];
```

## أفضل الممارسات

### 1. تسمية المفاتيح

- استخدم أسماء وصفية وواضحة
- استخدم snake_case
- اجعل المفاتيح قصيرة ومفهومة

```php
// جيد
'customer_name' => 'اسم العميل'
'order_total' => 'إجمالي الطلب'

// سيء
'cust_nm' => 'اسم العميل'
'ord_tot' => 'إجمالي الطلب'
```

### 2. تنظيم الترجمات

- ضع الترجمات في الملف المناسب
- استخدم التعليقات لتنظيم الترجمات
- اجعل الترجمات مرتبة منطقياً

```php
return [
    // الأزرار والإجراءات الأساسية
    'save' => 'حفظ',
    'update' => 'تحديث',
    'delete' => 'حذف',
    
    // الحقول الأساسية
    'name' => 'الاسم',
    'email' => 'البريد الإلكتروني',
    'phone' => 'الهاتف',
];
```

### 3. استخدام المتغيرات

```php
// في ملف الترجمة
'welcome_message' => 'مرحباً :name، أهلاً وسهلاً بك في :system',

// في الاستخدام
__('messages.welcome_message', ['name' => $user->name, 'system' => 'Masaar ERP'])
```

## إضافة لغة جديدة

### 1. إنشاء مجلد اللغة

```bash
mkdir resources/lang/fr
```

### 2. نسخ ملفات الترجمة

```bash
cp resources/lang/en/* resources/lang/fr/
```

### 3. تحديث الترجمات

قم بتحديث جميع الملفات في المجلد `fr` بالترجمات الفرنسية.

### 4. تحديث إعدادات التطبيق

```php
// في config/app.php
'available_locales' => [
    'ar' => 'العربية',
    'en' => 'English',
    'fr' => 'Français',
],
```

## اختبار الترجمات

### 1. تغيير اللغة في التطبيق

```php
// في Controller أو Middleware
App::setLocale('ar'); // العربية
App::setLocale('en'); // الإنجليزية
```

### 2. اختبار في المتصفح

```php
// في routes/web.php
Route::get('/test-lang/{locale}', function ($locale) {
    App::setLocale($locale);
    return view('test-lang');
});
```

## استكشاف الأخطاء

### 1. مشاكل شائعة

- **مفتاح غير موجود**: تأكد من وجود المفتاح في ملف الترجمة
- **ملف غير موجود**: تأكد من وجود ملف الترجمة للغة المطلوبة
- **خطأ في التسمية**: تأكد من صحة اسم الملف والمفتاح

### 2. أدوات المساعدة

```php
// عرض جميع الترجمات المتاحة
dd(__('common'));

// البحث عن ترجمة
dd(trans('common.save'));

// التحقق من وجود ترجمة
if (Lang::has('common.save')) {
    echo __('common.save');
}
```

## الصيانة والتطوير

### 1. تحديث الترجمات

- راجع الترجمات بانتظام
- أضف الترجمات الجديدة عند إضافة ميزات جديدة
- تأكد من اتساق الترجمات بين اللغات

### 2. التوثيق

- وثق جميع الترجمات الجديدة
- احتفظ بقائمة بالمفاتيح المستخدمة
- وضح سياق استخدام كل ترجمة

### 3. المراجعة

- راجع الترجمات مع متحدثي اللغة الأصليين
- تأكد من دقة الترجمة
- تحقق من ملاءمة الترجمة للسياق

## أمثلة عملية

### 1. إضافة صفحة جديدة

```php
// في Controller
public function index()
{
    return view('new-page', [
        'title' => __('navigation.new_page'),
        'description' => __('forms.page_description'),
    ]);
}

// في Blade template
@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container">
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    
    <button class="btn btn-primary">
        {{ __('common.save') }}
    </button>
</div>
@endsection
```

### 2. إضافة رسائل خطأ مخصصة

```php
// في ملف الترجمة
'custom_error' => 'حدث خطأ مخصص: :message',

// في الاستخدام
throw new Exception(__('errors.custom_error', ['message' => 'تفاصيل الخطأ']));
```

## الخلاصة

نظام الترجمة الجديد يوفر:

- **تنظيم أفضل**: تقسيم منطقي للترجمات
- **سهولة الصيانة**: تحديث سريع وبسيط
- **قابلية التوسع**: إضافة لغات جديدة بسهولة
- **أداء محسن**: تحميل الترجمات حسب الحاجة
- **تعاون أفضل**: عمل متوازي على ملفات مختلفة

باتباع هذا الدليل، يمكنك الحفاظ على نظام ترجمة منظم وقابل للصيانة في مشروع Masaar ERP. 