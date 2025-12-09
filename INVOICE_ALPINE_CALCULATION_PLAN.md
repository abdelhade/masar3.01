# خطة نقل العمليات الحسابية للفواتير إلى Alpine.js

## 📋 نظرة عامة
الهدف: نقل جميع العمليات الحسابية التي يمكن تنفيذها على الـ Client-Side إلى Alpine.js لتحسين الأداء وتقليل طلبات السيرفر.

---

## ✅ العمليات الحسابية التي سيتم نقلها إلى Alpine.js

### 1. حساب القيمة الفرعية لكل صف (Sub Value)
**الوظيفة الحالية:**
- `recalculateSubValues()` في `CreateInvoiceForm.php` و `EditInvoiceForm.php`
- **الصيغة:** `sub_value = (quantity × price) - discount`

**في Alpine.js:**
```javascript
calculateSubValue(item) {
    const qty = parseFloat(item.quantity) || 0;
    const price = parseFloat(item.price) || 0;
    const discount = parseFloat(item.discount) || 0;
    return Math.round((qty * price) - discount, 2);
}
```

---

### 2. حساب المجموع الفرعي (Subtotal)
**الوظيفة الحالية:**
- جزء من `calculateTotals()`
- **الصيغة:** `subtotal = sum(all sub_values)`

**في Alpine.js:**
```javascript
get subtotal() {
    return this.invoiceItems.reduce((sum, item) => {
        return sum + this.calculateSubValue(item);
    }, 0);
}
```

---

### 3. حساب قيمة الخصم من النسبة (Discount Value)
**الوظيفة الحالية:**
- `updatedDiscountPercentage()` و `updatedDiscountValue()`
- **الصيغة:** `discount_value = (subtotal × discount_percentage) / 100`

**في Alpine.js:**
```javascript
get discountValue() {
    const percentage = parseFloat(this.discountPercentage) || 0;
    return Math.round((this.subtotal * percentage) / 100, 2);
}
```

---

### 4. حساب قيمة الإضافة من النسبة (Additional Value)
**الوظيفة الحالية:**
- `updatedAdditionalPercentage()` و `updatedAdditionalValue()`
- **الصيغة:** `additional_value = (subtotal × additional_percentage) / 100`

**في Alpine.js:**
```javascript
get additionalValue() {
    const percentage = parseFloat(this.additionalPercentage) || 0;
    return Math.round((this.subtotal * percentage) / 100, 2);
}
```

---

### 5. حساب الإجمالي النهائي (Total After Additional)
**الوظيفة الحالية:**
- جزء من `calculateTotals()`
- **الصيغة:** `total_after_additional = subtotal - discount_value + additional_value`

**في Alpine.js:**
```javascript
get totalAfterAdditional() {
    return Math.round(
        this.subtotal - this.discountValue + this.additionalValue,
        2
    );
}
```

---

### 6. حساب الباقي على العميل (Remaining)
**الوظيفة الحالية:**
- في `invoice-footer.blade.php` (السطر 347)
- **الصيغة:** `remaining = total_after_additional - received_from_client`

**في Alpine.js:**
```javascript
get remaining() {
    const total = this.totalAfterAdditional;
    const received = parseFloat(this.receivedFromClient) || 0;
    return Math.max(total - received, 0);
}
```

---

### 7. حساب الكمية من الأبعاد (Dimensions Calculation)
**الوظيفة الحالية:**
- `calculateQuantityFromDimensions($index)` في `CreateInvoiceForm.php`
- **الصيغة:** `quantity = length × width × height × density` (مع تحويل الوحدة)

**في Alpine.js:**
```javascript
calculateQuantityFromDimensions(item) {
    const length = parseFloat(item.length) || 0;
    const width = parseFloat(item.width) || 0;
    const height = parseFloat(item.height) || 0;
    const density = parseFloat(item.density) || 1;
    
    if (length > 0 && width > 0 && height > 0) {
        let quantity = length * width * height * density;
        
        // تحويل من سم³ إلى م³
        if (this.dimensionsUnit === 'cm') {
            quantity = quantity / 1000000;
        }
        
        return Math.round(quantity, 3);
    }
    return 0;
}
```

---

### 8. حساب الكمية من القيمة الفرعية (Quantity from Sub Value)
**الوظيفة الحالية:**
- `calculateQuantityFromSubValue($index)` 
- **الصيغة:** `quantity = (sub_value + discount) / price`

**في Alpine.js:**
```javascript
calculateQuantityFromSubValue(item) {
    const subValue = parseFloat(item.sub_value) || 0;
    const discount = parseFloat(item.discount) || 0;
    const price = parseFloat(item.price) || 0;
    
    if (price <= 0) return 0;
    
    return Math.round((subValue + discount) / price, 3);
}
```

---

## ❌ العمليات التي ستبقى في Livewire (Server-Side)

### 1. حساب الرصيد بعد الفاتورة (Balance After Invoice)
**السبب:** يعتمد على بيانات من قاعدة البيانات (`currentBalance` من `JournalDetail`)

**الوظيفة:** `calculateBalanceAfterInvoice()` في `CreateInvoiceForm.php` (السطر 535)

**التعامل:**
- يبقى في Livewire
- يتم استدعاؤه عند:
  - تغيير `acc1_id`
  - تغيير `received_from_client` (إذا كان `showBalance = true`)
  - حفظ الفاتورة

---

### 2. التحقق من الحساب النقدي (Check Cash Account)
**السبب:** يعتمد على قوائم `cashClientIds` و `cashSupplierIds` من قاعدة البيانات

**الوظيفة:** `checkCashAccount($accountId)` في `CreateInvoiceForm.php` (السطر 459)

**التعامل:**
- يبقى في Livewire
- يتم استدعاؤه في `calculateTotals()` بعد حساب `total_after_additional`

---

### 3. حساب السعر من قاعدة البيانات
**السبب:** يحتاج إلى:
- جلب آخر سعر شراء
- جلب أسعار من اتفاقيات التسعير
- جلب آخر سعر للعميل
- حساب الأسعار بناءً على الوحدة

**الوظيفة:** `calculateItemPrice()` في `HandlesInvoiceData.php` (السطر 452)

**التعامل:**
- يبقى في Livewire
- يتم استدعاؤه عند:
  - إضافة صنف جديد
  - تغيير الوحدة (`updatePriceForUnit`)
  - تغيير نوع السعر (`updatedSelectedPriceType`)

---

### 4. التحقق من الرصيد المتاح (Stock Validation)
**السبب:** يعتمد على قاعدة البيانات (`OperationItems`)

**الوظيفة:** في `updatedInvoiceItems()` و `addItemFromSearch()`

**التعامل:**
- يبقى في Livewire
- يتم استدعاؤه عند تغيير الكمية

---

### 5. التحقق من الصلاحيات (Permissions)
**السبب:** يعتمد على صلاحيات المستخدم من Laravel

**الأماكن:**
- `allow_price_change`
- `allow_discount_change`
- `allow_edit_invoice_value`
- وغيرها

**التعامل:**
- يبقى في Livewire
- يمكن تمرير الحالة إلى Alpine.js كـ `x-bind:readonly`

---

## 🔄 التغييرات المطلوبة

### أ. ملفات Blade (الواجهة)

#### 1. `create-invoice-form.blade.php` و `edit-invoice-form.blade.php`
**التغييرات:**
- إضافة `x-data="invoiceCalculations"` للـ form
- تمرير البيانات الأولية من Livewire إلى Alpine.js
- تحديث الحقول لاستخدام Alpine.js computed properties
- استبدال `wire:model.live` بـ `x-model` للحقول الحسابية فقط
- إبقاء `wire:model` للحقول التي تحتاج للسيرفر (مثل السعر)

---

#### 2. `invoice-item-table.blade.php`
**التغييرات:**
- إضافة `x-init` لكل صف لتهيئة الحسابات
- تحديث حقل `sub_value` ليعرض القيمة المحسوبة من Alpine.js
- إضافة `@input` listeners لحساب القيمة الفرعية عند تغيير الكمية/السعر/الخصم

---

#### 3. `invoice-footer.blade.php`
**التغييرات:**
- تحديث عرض `subtotal` لاستخدام Alpine.js
- تحديث عرض `discount_value` و `additional_value`
- تحديث عرض `total_after_additional`
- تحديث عرض `remaining`
- إضافة `x-model` لحقول الخصم والإضافة مع `wire:model.blur` للتصدير للسيرفر

---

### ب. ملفات Livewire (Backend)

#### 1. `CreateInvoiceForm.php`
**التغييرات:**
- إزالة أو تبسيط `recalculateSubValues()` - ستصبح للتحقق فقط
- إزالة أو تبسيط `calculateTotals()` - ستصبح للتحقق والتحسين
- إبقاء `calculateBalanceAfterInvoice()` كما هي
- إبقاء `checkCashAccount()` كما هي
- إضافة دالة `syncCalculationsFromClient()` لاستقبال القيم من Alpine.js

---

#### 2. `EditInvoiceForm.php`
**نفس التغييرات** مثل `CreateInvoiceForm.php`

---

#### 3. `HandlesInvoiceData.php`
**لا تغييرات** - كل الدوال هنا تعتمد على قاعدة البيانات

---

## 📦 البيانات المطلوبة تمريرها إلى Alpine.js

```javascript
{
    invoiceItems: [...], // array of items with quantity, price, discount
    discountPercentage: 0,
    discountValue: 0,
    additionalPercentage: 0,
    additionalValue: 0,
    receivedFromClient: 0,
    dimensionsUnit: 'cm', // or 'm'
    enableDimensionsCalculation: false,
    invoiceType: 10, // for conditional logic
}
```

---

## 🔐 الحفاظ على الميزات الأساسية

### 1. التحقق من الصلاحيات
- يتم في Livewire قبل السماح بالتعديل
- يتم تمرير حالة `readonly` إلى Alpine.js

---

### 2. التحقق من الرصيد
- يبقى في Livewire
- يتم استدعاؤه عند تغيير الكمية
- يتم إظهار رسالة خطأ إذا كان الرصيد غير كافي

---

### 3. حساب السعر من قاعدة البيانات
- يبقى في Livewire
- يتم استدعاؤه عند إضافة صنف أو تغيير الوحدة
- يتم تحديث القيمة في Alpine.js بعد الحصول عليها من السيرفر

---

### 4. التحقق من الحساب النقدي
- يبقى في Livewire
- يتم استدعاؤه بعد حساب `total_after_additional`
- يتم تحديث `received_from_client` تلقائياً

---

### 5. حساب الرصيد بعد الفاتورة
- يبقى في Livewire
- يتم استدعاؤه عند تغيير العميل أو المبلغ المدفوع
- يتم عرض النتيجة في الواجهة

---

## 📝 ملخص التغييرات

### ✅ ما سيتم إزالته/تبسيطه في Livewire:
1. `recalculateSubValues()` → تبقى للتحقق فقط
2. `calculateTotals()` → تبقى للتحقق والتحسين
3. الحسابات الفورية في `updatedInvoiceItems()` → تنتقل إلى Alpine.js
4. `updatedDiscountPercentage()` → تبقى للتحقق
5. `updatedDiscountValue()` → تبقى للتحقق
6. `updatedAdditionalPercentage()` → تبقى للتحقق
7. `updatedAdditionalValue()` → تبقى للتحقق

---

### ✅ ما سيتم إضافته في Alpine.js:
1. `invoiceCalculations` Alpine component
2. Computed properties للقيم الحسابية
3. Methods لحساب القيم الفرعية
4. Reactive updates للحقول

---

### ✅ ما سيبقى كما هو:
1. `calculateBalanceAfterInvoice()` - يعتمد على قاعدة البيانات
2. `checkCashAccount()` - يعتمد على قاعدة البيانات
3. `calculateItemPrice()` - يعتمد على قاعدة البيانات
4. جميع عمليات التحقق من الصلاحيات
5. جميع عمليات التحقق من الرصيد

---

## 🎯 الفوائد المتوقعة

1. **تحسين الأداء:** 
   - تقليل طلبات AJAX من ~10-15 طلب/ثانية إلى ~1-2 طلب/ثانية
   - تحديث فوري للواجهة بدون انتظار السيرفر

2. **تحسين تجربة المستخدم:**
   - استجابة فورية للحسابات
   - عدم ظهور loading states للحسابات البسيطة

3. **تقليل الحمل على السيرفر:**
   - تقليل عدد الطلبات
   - تقليل استهلاك CPU

4. **الحفاظ على الأمان:**
   - جميع التحققات الحساسة تبقى في السيرفر
   - التحقق النهائي قبل الحفظ

---

## ⚠️ نقاط مهمة

1. **مزامنة البيانات:**
   - يجب مزامنة القيم من Alpine.js إلى Livewire قبل الحفظ
   - استخدام `wire:model.blur` للحقول الحسابية

2. **التحقق النهائي:**
   - يجب التحقق من القيم في Livewire قبل الحفظ
   - إعادة حساب القيم للتأكد من صحتها

3. **الاختبار:**
   - اختبار جميع سيناريوهات الحساب
   - اختبار التكامل بين Alpine.js و Livewire
   - اختبار الحالات الاستثنائية (قيم صفرية، سالبة، إلخ)

---

## 📅 خطة التنفيذ المقترحة

### المرحلة 1: إعداد Alpine.js Component
- إنشاء `invoiceCalculations` component
- تنفيذ جميع الحسابات الأساسية
- اختبار الحسابات منفصلة

### المرحلة 2: تكامل مع Blade Templates
- تحديث `invoice-item-table.blade.php`
- تحديث `invoice-footer.blade.php`
- ربط Alpine.js مع Livewire

### المرحلة 3: تحديث Livewire Components
- تبسيط `recalculateSubValues()`
- تبسيط `calculateTotals()`
- إضافة مزامنة البيانات

### المرحلة 4: الاختبار والتحسين
- اختبار جميع السيناريوهات
- تحسين الأداء
- معالجة الأخطاء

---

## 🔍 مثال على التنفيذ

### Alpine.js Component Structure:
```javascript
Alpine.data('invoiceCalculations', () => ({
    // Data (passed from Livewire)
    invoiceItems: @js($invoiceItems),
    discountPercentage: @js($discount_percentage),
    additionalPercentage: @js($additional_percentage),
    receivedFromClient: @js($received_from_client),
    dimensionsUnit: @js($dimensionsUnit),
    
    // Computed Properties
    get subtotal() { ... },
    get discountValue() { ... },
    get additionalValue() { ... },
    get totalAfterAdditional() { ... },
    get remaining() { ... },
    
    // Methods
    calculateSubValue(item) { ... },
    calculateQuantityFromDimensions(item) { ... },
    
    // Watchers
    init() {
        // Sync with Livewire on mount
    }
}))
```

---

**ملاحظة:** هذه خطة أولية. يمكن تعديلها حسب المتطلبات الفعلية أثناء التنفيذ.

