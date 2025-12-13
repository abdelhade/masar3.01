# 🚗 خطة تنفيذ نظام إدارة الأسطول - المستوى الأبسط

## 📋 نظرة عامة

تنفيذ المرحلة 1 (المستوى الأبسط) من نظام إدارة الأسطول يتضمن:
1. **Vehicles** (المركبات)
2. **VehicleTypes** (أنواع المركبات)
3. **Drivers** (السائقين) - استخدام Driver من موديول Shipping
4. **Trips** (الرحلات)
5. **FuelRecords** (سجل الوقود)

---

## 🏗️ البنية المقترحة

### 1. هيكل الموديول

```
Modules/Fleet/
├── database/
│   ├── migrations/
│   │   ├── create_vehicle_types_table.php
│   │   ├── create_vehicles_table.php
│   │   ├── create_trips_table.php
│   │   └── create_fuel_records_table.php
│   └── seeders/
│       └── FleetPermissionsSeeder.php
├── Enums/
│   ├── VehicleStatus.php
│   ├── TripStatus.php
│   └── FuelType.php
├── Http/
│   ├── Controllers/
│   │   ├── VehicleController.php
│   │   ├── VehicleTypeController.php
│   │   ├── TripController.php
│   │   ├── FuelRecordController.php
│   │   └── FleetDashboardController.php
│   └── Requests/
│       ├── VehicleRequest.php
│       ├── VehicleTypeRequest.php
│       ├── TripRequest.php
│       └── FuelRecordRequest.php
├── Models/
│   ├── Vehicle.php
│   ├── VehicleType.php
│   ├── Trip.php
│   └── FuelRecord.php
├── Providers/
│   ├── FleetServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── Resources/
│   ├── lang/
│   │   └── ar.json
│   └── views/
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── vehicles/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       ├── vehicle-types/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       ├── trips/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       └── fuel-records/
│           ├── index.blade.php
│           ├── create.blade.php
│           ├── edit.blade.php
│           └── show.blade.php
├── routes/
│   └── web.php
├── module.json
└── README.md
```

---

## 📊 قاعدة البيانات

### 1. جدول `vehicle_types`
```sql
- id (bigint, primary)
- name (string) - اسم النوع
- description (text, nullable)
- is_active (boolean, default: true)
- created_at, updated_at
```

### 2. جدول `vehicles`
```sql
- id (bigint, primary)
- code (string, unique) - رقم المركبة (auto-generated)
- plate_number (string, unique) - رقم اللوحة
- vehicle_type_id (foreign key -> vehicle_types)
- driver_id (foreign key -> shipping_drivers, nullable)
- branch_id (foreign key -> branches)
- name (string) - اسم/وصف المركبة
- model (string, nullable) - الموديل
- year (integer, nullable) - سنة الصنع
- color (string, nullable) - اللون
- chassis_number (string, nullable) - رقم الشاصي
- engine_number (string, nullable) - رقم المحرك
- current_mileage (decimal) - عداد المسافة الحالي
- status (enum: available, in_use, maintenance, out_of_service)
- purchase_date (date, nullable)
- purchase_cost (decimal, nullable)
- notes (text, nullable)
- is_active (boolean, default: true)
- created_by, updated_by (foreign key -> users)
- created_at, updated_at
- deleted_at (soft deletes)
```

### 3. جدول `trips`
```sql
- id (bigint, primary)
- trip_number (string, unique) - رقم الرحلة (auto-generated)
- vehicle_id (foreign key -> vehicles)
- driver_id (foreign key -> shipping_drivers)
- branch_id (foreign key -> branches)
- start_location (string) - نقطة البداية
- end_location (string) - نقطة النهاية
- start_date (datetime) - تاريخ ووقت البداية
- end_date (datetime, nullable) - تاريخ ووقت النهاية
- start_mileage (decimal) - قراءة العداد عند البداية
- end_mileage (decimal, nullable) - قراءة العداد عند النهاية
- distance (decimal, nullable) - المسافة (محسوبة تلقائياً)
- purpose (string, nullable) - الغرض من الرحلة
- status (enum: scheduled, in_progress, completed, cancelled)
- notes (text, nullable)
- created_by, updated_by (foreign key -> users)
- created_at, updated_at
- deleted_at (soft deletes)
```

### 4. جدول `fuel_records`
```sql
- id (bigint, primary)
- vehicle_id (foreign key -> vehicles)
- trip_id (foreign key -> trips, nullable) - ربط مع رحلة معينة
- branch_id (foreign key -> branches)
- fuel_date (date) - تاريخ التزود
- fuel_type (enum: gasoline, diesel, electric) - نوع الوقود
- quantity (decimal) - الكمية (باللتر)
- cost (decimal) - التكلفة
- mileage_at_fueling (decimal) - قراءة العداد عند التزود
- station_name (string, nullable) - اسم المحطة
- receipt_number (string, nullable) - رقم الفاتورة
- notes (text, nullable)
- created_by, updated_by (foreign key -> users)
- created_at, updated_at
- deleted_at (soft deletes)
```

---

## 🎯 المميزات الأساسية

### 1. Vehicles (المركبات)
- ✅ CRUD كامل
- ✅ Auto-generate code
- ✅ ربط مع Branch Scope
- ✅ ربط مع Driver (من Shipping)
- ✅ تتبع حالة المركبة
- ✅ تتبع عداد المسافة

### 2. VehicleTypes (أنواع المركبات)
- ✅ CRUD كامل
- ✅ تفعيل/تعطيل النوع

### 3. Trips (الرحلات)
- ✅ CRUD كامل
- ✅ Auto-generate trip_number
- ✅ حساب المسافة تلقائياً (end_mileage - start_mileage)
- ✅ ربط مع Vehicle و Driver
- ✅ تتبع حالة الرحلة
- ✅ تحديث عداد المركبة عند إتمام الرحلة

### 4. FuelRecords (سجل الوقود)
- ✅ CRUD كامل
- ✅ ربط مع Vehicle و Trip (اختياري)
- ✅ تتبع قراءة العداد عند التزود
- ✅ حساب متوسط الاستهلاك

### 5. Dashboard
- ✅ إحصائيات أساسية:
  - عدد المركبات (متاحة/قيد الاستخدام/قيد الصيانة)
  - عدد الرحلات (اليوم/الشهر)
  - إجمالي التكاليف (الوقود)
  - متوسط استهلاك الوقود

---

## 🔗 العلاقات (Relationships)

### Vehicle Model
```php
- belongsTo(VehicleType::class)
- belongsTo(Driver::class) // من Shipping
- belongsTo(Branch::class)
- hasMany(Trip::class)
- hasMany(FuelRecord::class)
```

### Trip Model
```php
- belongsTo(Vehicle::class)
- belongsTo(Driver::class) // من Shipping
- belongsTo(Branch::class)
- hasMany(FuelRecord::class)
```

### FuelRecord Model
```php
- belongsTo(Vehicle::class)
- belongsTo(Trip::class, nullable)
- belongsTo(Branch::class)
```

---

## 🔐 الصلاحيات (Permissions)

### Permissions Structure
```
Fleet:
  - Fleet Dashboard
  - Vehicle Types
  - Vehicles
  - Trips
  - Fuel Records

Actions لكل permission:
  - view
  - create
  - edit
  - delete
  - print
```

---

## 📝 الخطوات التنفيذية

### المرحلة 1: إعداد الموديول الأساسي
1. ✅ إنشاء الموديول باستخدام artisan
2. ✅ إعداد Service Provider
3. ✅ إعداد Route Provider
4. ✅ إعداد module.json

### المرحلة 2: قاعدة البيانات
1. ✅ إنشاء Enums (VehicleStatus, TripStatus, FuelType)
2. ✅ إنشاء Migrations
3. ✅ إنشاء Models مع Relationships
4. ✅ إضافة Branch Scope للموديلات

### المرحلة 3: Controllers & Requests
1. ✅ إنشاء Form Request Classes
2. ✅ إنشاء Controllers (CRUD)
3. ✅ إضافة Validation Rules

### المرحلة 4: Permissions
1. ✅ إنشاء FleetPermissionsSeeder
2. ✅ إضافة الصلاحيات للمستخدم الافتراضي
3. ✅ تسجيل Seeder في DatabaseSeeder

### المرحلة 5: Views (Livewire Volt)
1. ✅ Dashboard
2. ✅ Vehicle Types (CRUD)
3. ✅ Vehicles (CRUD)
4. ✅ Trips (CRUD)
5. ✅ Fuel Records (CRUD)

### المرحلة 6: Routes
1. ✅ إعداد Routes
2. ✅ إضافة Middleware (auth, permissions)

### المرحلة 7: Localization
1. ✅ إضافة ملفات الترجمة (ar.json)
2. ✅ استخدام __() في جميع النصوص

### المرحلة 8: Testing
1. ✅ إنشاء Feature Tests
2. ✅ اختبار CRUD Operations
3. ✅ اختبار Relationships
4. ✅ اختبار Permissions

---

## 🎨 UI/UX Considerations

- استخدام **Bootstrap 5** للتصميم
- استخدام **Livewire Volt** (Class-based) للمكونات التفاعلية
- استخدام **Flux UI** components عند الإمكان
- إضافة **Search & Filter** في صفحات Index
- إضافة **Pagination**
- إضافة **Loading States** مع wire:loading
- استخدام **Modals** للإنشاء/التعديل
- إضافة **Confirm Dialogs** للحذف

---

## 📌 ملاحظات مهمة

1. **Auto-numbering**: 
   - Vehicle code: `VEH-0001`, `VEH-0002`, ...
   - Trip number: `TRIP-0001`, `TRIP-0002`, ...

2. **Branch Scope**: 
   - جميع الموديلات تستخدم BranchScope
   - المستخدم يرى فقط بيانات فرعه

3. **Driver Integration**:
   - استخدام `Modules\Shipping\Models\Driver` الموجود
   - لا حاجة لإنشاء Driver جديد

4. **Mileage Tracking**:
   - عند إتمام Trip، يتم تحديث `current_mileage` في Vehicle
   - عند إضافة FuelRecord، يتم حفظ `mileage_at_fueling`

5. **Distance Calculation**:
   - في Trip: `distance = end_mileage - start_mileage`
   - يتم حسابها تلقائياً عند إتمام الرحلة

---

## ✅ Checklist قبل البدء

- [ ] مراجعة الخطة والموافقة
- [ ] التأكد من وجود موديول Shipping (للسائقين)
- [ ] التأكد من وجود Branch Scope
- [ ] التأكد من إعداد Permissions System
- [ ] التأكد من إعداد Localization

---

## 🚀 بعد التنفيذ

1. تشغيل Migrations
2. تشغيل Seeders
3. إضافة الصلاحيات للمستخدم الافتراضي
4. اختبار النظام
5. إضافة إلى Sidebar/Menu

