# تحليل أداء صفحة إنشاء الفاتورة (Create Invoice Performance Analysis)

## 📊 ملخص تنفيذي

هذا التقرير يركز على **مشاكل الأداء** في صفحة إنشاء الفاتورة (`CreateInvoiceForm`) ويقدم حلول عملية لتحسينها.

---

## 🔴 المشاكل الحرجة (Critical Performance Issues)

### 1. **تحميل البيانات عند Mount - Multiple Queries**

#### المشكلة:
```php
// CreateInvoiceForm.php:162-177
public function mount($type, $hash)
{
    // ... permission check ...
    
    $this->initializeInvoice($type, $hash); // ❌ يستدعي 10+ استعلامات!
    $this->loadTemplatesForType();
}
```

**التأثير:**
- عند فتح صفحة إنشاء فاتورة: يتم تنفيذ **10-15 استعلام** من قاعدة البيانات
- وقت التحميل: **2-5 ثواني** (حسب حجم البيانات)
- استهلاك عالي للذاكرة

#### الاستعلامات المنفذة:
```php
// في initializeInvoice() -> loadBranchFilteredData():
1. getAccountsByCodeAndBranch('1103%') - العملاء
2. getAccountsByCodeAndBranch('2101%') - الموردين  
3. getAccountsByCodeAndBranch('2102%') - الموظفين
4. getAccountsByCodeAndBranch('55%') - التوالف
5. getAccountsByCodeAndBranch('1108%') - الحسابات
6. getAccountsByCodeAndBranch('1104%') - المخازن
7. AccHead::where('is_fund', 1) - الحسابات النقدية
8. AccHead::where('code', 'like', '110301%') - عملاء نقديين
9. AccHead::where('code', 'like', '210101%') - موردين نقديين
10. Item::with(['units', 'prices'])->take(20) - الأصناف
11. Price::pluck('name', 'id') - أنواع الأسعار
12. InvoiceTemplate::getForType() - القوالب
```

#### الحل المقترح:

**أ. استخدام Cache للقوائم الثابتة:**
```php
protected function loadBranchFilteredData($branchId)
{
    if (!$branchId) return;

    // ✅ Cache جميع القوائم لمدة 5 دقائق
    $cacheKey = "invoice_accounts_{$branchId}_{$this->type}";
    
    $data = Cache::remember($cacheKey, 300, function () use ($branchId) {
        return [
            'clients' => $this->getAccountsByCodeAndBranch('1103%', $branchId),
            'suppliers' => $this->getAccountsByCodeAndBranch('2101%', $branchId),
            'employees' => $this->getAccountsByCodeAndBranch('2102%', $branchId),
            'stores' => $this->getAccountsByCodeAndBranch('1104%', $branchId),
            'cashAccounts' => AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('is_fund', 1)
                ->where('branch_id', $branchId)
                ->select('id', 'aname')
                ->get(),
            'cashClientIds' => AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '110301%')
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->toArray(),
            'cashSupplierIds' => AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '210101%')
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->toArray(),
        ];
    });
    
    // تعيين البيانات من Cache
    $this->acc1List = $data['clients'];
    $this->acc2List = $data['stores'];
    $this->employees = $data['employees'];
    $this->cashAccounts = $data['cashAccounts'];
    $this->cashClientIds = $data['cashClientIds'];
    $this->cashSupplierIds = $data['cashSupplierIds'];
}
```

**ب. Lazy Loading للأصناف:**
```php
// ❌ بدلاً من تحميل 20 صنف في mount
$this->items = Item::with(['units', 'prices'])->take(20)->get();

// ✅ لا تحميل الأصناف في mount - يتم تحميلها عبر API عند الحاجة
// في mount:
$this->items = []; // فارغ

// البحث يتم عبر API endpoint (موجود بالفعل)
// /api/items/lite?branch_id=...&type=...
```

**ج. Cache لأنواع الأسعار:**
```php
// ❌ في loadInvoiceData()
$this->priceTypes = Price::pluck('name', 'id')->toArray();

// ✅ استخدام Cache
$this->priceTypes = Cache::remember('price_types_all', 3600, function () {
    return Price::pluck('name', 'id')->toArray();
});
```

---

### 2. **N+1 Queries في getRecommendedItems()**

#### المشكلة:
```php
// CreateInvoiceForm.php:571-596
private function getRecommendedItems($clientId)
{
    return OperationItems::whereHas('operhead', function ($query) use ($clientId, $sourceType) {
        $query->where('pro_type', $sourceType)
            ->where('acc1', $clientId);
    })
        ->groupBy('item_id')
        ->selectRaw('item_id, SUM(qty_out) as total_quantity')
        ->with(['item' => function ($query) {
            $query->select('id', 'name'); // ✅ جيد لكن...
        }])
        ->orderByDesc('total_quantity')
        ->take(5)
        ->get();
}
```

**المشكلة:**
- `whereHas()` يسبب subquery معقد
- قد يكون بطيئاً مع بيانات كثيرة

#### الحل المقترح:
```php
private function getRecommendedItems($clientId)
{
    $cacheKey = "recommended_items_{$clientId}_{$this->type}";
    
    return Cache::remember($cacheKey, 600, function () use ($clientId) {
        $sourceType = $this->type == 26 ? 26 : 10;
        
        // ✅ استخدام Join بدلاً من whereHas (أسرع)
        return OperationItems::join('oper_head', 'operation_items.op_id', '=', 'oper_head.id')
            ->where('oper_head.pro_type', $sourceType)
            ->where('oper_head.acc1', $clientId)
            ->where('oper_head.isdeleted', 0)
            ->groupBy('operation_items.item_id')
            ->selectRaw('operation_items.item_id, SUM(operation_items.qty_out) as total_quantity')
            ->with(['item:id,name']) // ✅ Eager loading
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get()
            ->map(function ($operationItem) {
                return [
                    'id' => $operationItem->item_id,
                    'name' => $operationItem->item->name ?? 'غير معروف',
                    'total_quantity' => $operationItem->total_quantity,
                ];
            })
            ->toArray();
    });
}
```

---

### 3. **استعلامات متكررة في getAccountBalance()**

#### المشكلة:
```php
// HandlesInvoiceData.php:348-364
protected function getAccountBalance($accountId)
{
    $balance = JournalDetail::where('account_id', $accountId)
        ->where('isdeleted', 0)
        ->selectRaw('SUM(debit) - SUM(credit) as balance')
        ->value('balance') ?? 0;
    
    return $balance;
}
```

**المشكلة:**
- يتم استدعاؤه في كل مرة يتغير فيها `acc1_id`
- لا يوجد cache
- مع 1000+ حركة: استعلام بطيء

#### الحل المقترح:
```php
protected function getAccountBalance($accountId)
{
    if (!$accountId) return 0;
    
    // ✅ Cache لمدة دقيقة واحدة (الرصيد قد يتغير)
    $cacheKey = "account_balance_{$accountId}";
    
    return Cache::remember($cacheKey, 60, function () use ($accountId) {
        return JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;
    });
}

// ✅ إضافة method لمسح Cache عند حفظ فاتورة
public function clearAccountBalanceCache($accountId)
{
    Cache::forget("account_balance_{$accountId}");
}
```

---

### 4. **استعلامات متعددة في calculateItemPrice()**

#### المشكلة:
```php
// HandlesInvoiceData.php:510-619
protected function calculateItemPrice($item, $unitId, $priceTypeId = 1, $currentPrice = 0, $oldUnitId = null)
{
    // ... منطق معقد ...
    
    // ❌ استعلامات متعددة حسب نوع الفاتورة:
    if (in_array($this->type, [11, 15])) {
        $lastPurchasePrice = OperationItems::where('item_id', $item->id)
            ->where('unit_id', $unitId)
            ->where('is_stock', 1)
            ->whereIn('pro_tybe', [11, 20])
            ->where('qty_in', '>', 0)
            ->orderBy('created_at', 'desc')
            ->value('item_price'); // ❌ استعلام في كل مرة!
    }
    
    // ❌ استعلامات أخرى لاتفاقيات التسعير
    if ($this->type == 10 && $this->acc1_id) {
        $pricingAgreementPrice = OperationItems::whereHas('operhead', ...)
            ->where('item_id', $item->id)
            ->where('unit_id', $unitId)
            ->orderBy('created_at', 'desc')
            ->value('item_price'); // ❌ استعلام آخر!
    }
}
```

**المشكلة:**
- يتم استدعاؤه عند إضافة كل صنف
- مع 10 أصناف = 10-20 استعلام إضافي

#### الحل المقترح:
```php
protected function calculateItemPrice($item, $unitId, $priceTypeId = 1, $currentPrice = 0, $oldUnitId = null)
{
    if (!$item || !$unitId) return 0;
    
    // ✅ Cache key بناءً على المعايير
    $cacheKey = sprintf(
        'item_price_%d_%d_%d_%d_%d',
        $item->id,
        $unitId,
        $this->type,
        $priceTypeId,
        $this->acc1_id ?? 0
    );
    
    return Cache::remember($cacheKey, 300, function () use ($item, $unitId, $priceTypeId, $currentPrice, $oldUnitId) {
        // ... نفس المنطق لكن مع Cache ...
        
        // ✅ Batch loading للأسعار السابقة
        if (in_array($this->type, [11, 15])) {
            // جلب آخر سعر شراء (مع Cache)
            $lastPurchasePrice = $this->getLastPurchasePrice($item->id, $unitId);
            // ...
        }
        
        return $price;
    });
}

// ✅ Method منفصل مع Cache
protected function getLastPurchasePrice($itemId, $unitId)
{
    $cacheKey = "last_purchase_price_{$itemId}_{$unitId}";
    
    return Cache::remember($cacheKey, 600, function () use ($itemId, $unitId) {
        return OperationItems::where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->where('is_stock', 1)
            ->whereIn('pro_tybe', [11, 20])
            ->where('qty_in', '>', 0)
            ->orderBy('created_at', 'desc')
            ->value('item_price') ?? 0;
    });
}
```

---

### 5. **تحميل الأصناف في mount() - غير ضروري**

#### المشكلة:
```php
// HandlesInvoiceData.php:316-328
$this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
    ->where(function ($query) use ($branchId) {
        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
    })
    ->when(in_array($this->type, [11, 13, 15, 17]), function ($query) {
        $query->where('type', ItemType::Inventory->value);
    })
    ->when($this->type == 24, function ($query) {
        $query->where('type', ItemType::Service->value);
    })
    ->take(20)
    ->get()
    ->toArray(); // ❌ تحميل 20 صنف في mount!
```

**المشكلة:**
- يتم تحميل 20 صنف في mount (غير مستخدمين)
- البحث يتم عبر API `/api/items/lite` (client-side)
- تحميل مزدوج للبيانات

#### الحل المقترح:
```php
// ✅ لا تحميل الأصناف في mount
$this->items = []; // فارغ

// البحث يتم عبر:
// 1. API endpoint: /api/items/lite (موجود بالفعل)
// 2. Client-side search مع Fuse.js (موجود بالفعل)
// 3. Livewire searchItems() method (للتوافق)
```

---

### 6. **استعلامات متكررة عند تغيير الفرع**

#### المشكلة:
```php
// CreateInvoiceForm.php:420-452
public function handleBranchChange($branchId)
{
    $this->loadBranchFilteredData($branchId); // ❌ 10+ استعلامات من جديد!
    $this->resetSelectedValues();
    $this->acc1_id = $this->acc1List->first()->id ?? null;
    
    if ($this->showBalance && $this->acc1_id) {
        $this->currentBalance = $this->getAccountBalance($this->acc1_id); // ❌ استعلام
    }
    
    if ($this->type == 10 && $this->acc1_id) {
        $this->recommendedItems = $this->getRecommendedItems($this->acc1_id); // ❌ استعلام
    }
    
    // ❌ تحميل الأصناف من جديد
    $this->items = Item::with(['units', 'prices'])
        ->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)->orWhereNull('branch_id');
        })
        ->take(20)
        ->get();
}
```

#### الحل المقترح:
```php
public function handleBranchChange($branchId)
{
    // ✅ استخدام Cache (موجود في loadBranchFilteredData بعد التحسين)
    $this->loadBranchFilteredData($branchId);
    $this->resetSelectedValues();
    $this->acc1_id = $this->acc1List->first()->id ?? null;
    
    // ✅ استخدام Cache للرصيد والتوصيات
    if ($this->showBalance && $this->acc1_id) {
        $this->currentBalance = $this->getAccountBalance($this->acc1_id);
    }
    
    if ($this->type == 10 && $this->acc1_id) {
        $this->recommendedItems = $this->getRecommendedItems($this->acc1_id);
    }
    
    // ✅ لا تحميل الأصناف - يتم عبر API
    $this->items = [];
    
    // ✅ إرسال event للـ Alpine.js لإعادة تحميل الأصناف
    $this->dispatch('branch-changed', ['branchId' => $branchId]);
}
```

---

## 🟡 المشاكل المتوسطة (Medium Priority)

### 7. **Client-Side Search Performance**

#### المشكلة:
```javascript
// invoice-scripts.blade.php:369-440
async loadItems(isBackground = false, showNotification = false) {
    // ❌ تحميل كل الأصناف من API
    const response = await fetch(`/api/items/lite?branch_id=${this.branchId}&type=${this.invoiceType}`);
    const newData = await response.json();
    this.allItems = newData; // ❌ قد يكون آلاف الأصناف!
    
    // ❌ إعادة تهيئة Fuse.js في كل مرة
    this.fuse = new Fuse(this.allItems, options);
}
```

**المشكلة:**
- تحميل كل الأصناف دفعة واحدة (قد يكون 1000+ صنف)
- حجم البيانات كبير (JSON)
- إعادة تهيئة Fuse.js بطيئة

#### الحل المقترح:
```javascript
async loadItems(isBackground = false, showNotification = false) {
    if (!isBackground) this.loading = true;
    
    try {
        // ✅ استخدام pagination أو limit
        const response = await fetch(
            `/api/items/lite?branch_id=${this.branchId}&type=${this.invoiceType}&limit=500`
        );
        
        const newData = await response.json();
        
        // ✅ تحديث تدريجي بدلاً من استبدال كامل
        if (Array.isArray(newData)) {
            // دمج مع البيانات الموجودة (تجنب التكرار)
            const existingIds = new Set(this.allItems.map(item => item.id));
            const newItems = newData.filter(item => !existingIds.has(item.id));
            this.allItems = [...this.allItems, ...newItems];
            
            // ✅ إعادة تهيئة Fuse.js فقط عند الحاجة
            if (this.fuse && newItems.length > 0) {
                this.fuse = new Fuse(this.allItems, options);
            } else if (!this.fuse) {
                this.fuse = new Fuse(this.allItems, options);
            }
        }
    } catch (error) {
        console.error('Error loading items:', error);
    } finally {
        if (!isBackground) this.loading = false;
    }
}
```

---

### 8. **Alpine.js Watchers - Performance**

#### المشكلة:
```javascript
// invoice-scripts.blade.php:853-910
this.$watch('invoiceItems', () => {
   this.calculateTotalsFromData(); // ❌ يتم استدعاؤه في كل تغيير!
}, { deep: true }); // ❌ deep watch بطيء!

this.$watch('discountPercentage', () => {
    this.calculateFinalTotals(); // ❌ استدعاء متكرر
});

this.$watch('discountValue', () => {
    this.calculateFinalTotals(); // ❌ استدعاء متكرر
});
// ... 10+ watchers!
```

**المشكلة:**
- Deep watch على `invoiceItems` بطيء جداً
- Watchers متعددة تستدعي نفس الدالة
- لا يوجد debounce

#### الحل المقترح:
```javascript
// ✅ استخدام debounce للـ watchers
this.$watch('invoiceItems', () => {
    clearTimeout(this._calculateDebounce);
    this._calculateDebounce = setTimeout(() => {
        this.calculateTotalsFromData();
    }, 100); // ✅ debounce 100ms
}, { deep: true });

// ✅ دمج watchers متعددة
this.$watch(() => [
    this.discountPercentage,
    this.discountValue,
    this.additionalPercentage,
    this.additionalValue
], () => {
    clearTimeout(this._totalsDebounce);
    this._totalsDebounce = setTimeout(() => {
        this.calculateFinalTotals();
    }, 150);
});
```

---

## 📊 ملخص التحسينات المقترحة

### الأولوية العالية (يجب تنفيذها فوراً):

1. ✅ **Cache للقوائم الثابتة** (الحسابات، المخازن، إلخ)
   - **التأثير:** تقليل 10+ استعلامات إلى 0 (بعد أول تحميل)
   - **الوقت المتوقع:** -80% من وقت التحميل

2. ✅ **Cache للرصيد والتوصيات**
   - **التأثير:** تقليل استعلامات متكررة
   - **الوقت المتوقع:** -50% من وقت التحديثات

3. ✅ **إزالة تحميل الأصناف من mount**
   - **التأثير:** تقليل استعلام واحد كبير
   - **الوقت المتوقع:** -20% من وقت التحميل

### الأولوية المتوسطة:

4. ✅ **تحسين calculateItemPrice() مع Cache**
   - **التأثير:** تقليل استعلامات عند إضافة الأصناف
   - **الوقت المتوقع:** -60% من وقت إضافة الأصناف

5. ✅ **Debounce للـ Alpine.js watchers**
   - **التأثير:** تحسين استجابة الواجهة
   - **الوقت المتوقع:** -40% من استهلاك CPU

---

## 🛠️ خطة التنفيذ

### المرحلة 1 (يوم 1):
- [ ] إضافة Cache للقوائم الثابتة
- [ ] إضافة Cache للرصيد
- [ ] إزالة تحميل الأصناف من mount

### المرحلة 2 (يوم 2):
- [ ] تحسين getRecommendedItems() مع Cache
- [ ] تحسين calculateItemPrice() مع Cache
- [ ] إضافة debounce للـ watchers

### المرحلة 3 (يوم 3):
- [ ] تحسين client-side search
- [ ] إضافة pagination للأصناف
- [ ] اختبار شامل

---

## 📈 النتائج المتوقعة

### قبل التحسين:
- **وقت التحميل:** 3-5 ثواني
- **عدد الاستعلامات:** 15-20 استعلام
- **استهلاك الذاكرة:** عالي

### بعد التحسين:
- **وقت التحميل:** 0.5-1 ثانية (بعد أول تحميل)
- **عدد الاستعلامات:** 2-3 استعلامات (بعد Cache)
- **استهلاك الذاكرة:** منخفض

**تحسين الأداء: 70-80%** 🚀

---

**تاريخ التقرير:** 2026-02-11  
**الإصدار:** 1.0
