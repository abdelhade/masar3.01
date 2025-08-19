# نظام إدارة رصيد الإجازات

## نظرة عامة

نظام إدارة رصيد الإجازات هو نظام متكامل مبني على Laravel 11 + Livewire v3 لإدارة إجازات الموظفين مع دعم كامل للعربية واللغة الإنجليزية.

## المميزات الرئيسية

### 🎯 إدارة رصيد الإجازات
- إنشاء وإدارة أنواع الإجازات المختلفة
- تتبع رصيد كل موظف حسب نوع الإجازة والسنة
- حساب تلقائي للرصيد المتبقي
- دعم التراكم الشهري للأيام
- نقل الرصيد المتبقي للعام الجديد

### 📋 إدارة طلبات الإجازة
- إنشاء طلبات إجازة جديدة
- تتبع حالة الطلبات (مسودة، مقدم، معتمد، مرفوض، ملغي)
- التحقق من تداخل الطلبات
- التحقق من كفاية الرصيد
- ربط مع سجلات الحضور

### 🔐 نظام الصلاحيات
- أدوار مختلفة (admin, hr-admin, manager, employee)
- صلاحيات محددة لكل دور
- الموظف يرى طلباته فقط
- المدير يرى مرؤوسيه
- HR يرى الجميع

### 🔗 التكامل مع الأنظمة الأخرى
- ربط مع نظام البصمات والحضور
- ربط مع نظام الرواتب
- نقاط توسع للأنظمة المستقبلية

## الهيكل التقني

### الجداول الرئيسية

#### `leave_types`
- أنواع الإجازات (سنوي، مرضي، عارض، إلخ)
- إعدادات كل نوع (مدفوع، يتطلب موافقة، معدل التراكم)

#### `employee_leave_balances`
- رصيد كل موظف حسب نوع الإجازة والسنة
- تتبع الأيام (افتتاحي، متراكم، مستخدم، معلق، محول)

#### `leave_requests`
- طلبات الإجازة
- تتبع الحالة والموافقات
- حساب مدة الأيام تلقائياً

### الموديلات (Models)

#### `LeaveType`
```php
// العلاقات
public function employeeLeaveBalances(): HasMany
public function leaveRequests(): HasMany

// الخصائص المحسوبة
public function isPaid(): bool
public function requiresApproval(): bool
public function hasAccrualPolicy(): bool
```

#### `EmployeeLeaveBalance`
```php
// العلاقات
public function employee(): BelongsTo
public function leaveType(): BelongsTo

// الخصائص المحسوبة
public function getRemainingDaysAttribute(): float
public function hasSufficientBalance(float $days): bool

// العمليات
public function reservePending(float $days): void
public function consumeApproved(float $days): void
public function releasePending(float $days): void
```

#### `LeaveRequest`
```php
// العلاقات
public function employee(): BelongsTo
public function leaveType(): BelongsTo
public function approver(): BelongsTo

// Scopes
public function scopeApproved(Builder $query): Builder
public function scopePending(Builder $query): Builder
public function scopeForYear(Builder $query, int $year): Builder

// الخصائص المحسوبة
public function calculateDurationDays(): float
public function checkAttendanceOverlap(): bool
```

### الخدمات (Services)

#### `LeaveBalanceService`
```php
// العمليات الأساسية
public function reservePending(int $employeeId, int $leaveTypeId, int $year, float $days): bool
public function consumeApproved(int $employeeId, int $leaveTypeId, int $year, float $days): void
public function releasePending(int $employeeId, int $leaveTypeId, int $year, float $days): void

// العمليات المتقدمة
public function applyMonthlyAccrual(int $employeeId, int $leaveTypeId, int $year, int $month): void
public function carryOverToNextYear(int $employeeId, int $leaveTypeId, int $currentYear): void
public function calculateWorkingDays(string $startDate, string $endDate, bool $excludeHolidays = true): float
```

### الأحداث والمستمعين (Events & Listeners)

#### الأحداث
- `LeaveRequestSubmitted` - عند تقديم طلب
- `LeaveRequestApproved` - عند الموافقة
- `LeaveRequestRejected` - عند الرفض
- `LeaveRequestCancelled` - عند الإلغاء

#### المستمعين
- `UpdateLeaveBalanceOnSubmitted` - حجز الأيام المعلقة
- `UpdateLeaveBalanceOnApproved` - استهلاك الأيام المعتمدة
- `UpdateLeaveBalanceOnRejected` - إطلاق الأيام المعلقة
- `UpdateLeaveBalanceOnCancelled` - إطلاق الأيام المعلقة

### السياسات (Policies)

#### `LeaveRequestPolicy`
- التحكم في الوصول لطلبات الإجازة
- صلاحيات مختلفة حسب الدور
- التحقق من إمكانية الإجراءات

#### `EmployeeLeaveBalancePolicy`
- التحكم في الوصول لرصيد الإجازات
- صلاحيات إدارة الرصيد

## التثبيت والإعداد

### 1. تشغيل الميجريشنز
```bash
php artisan migrate
```

### 2. تشغيل الـ Seeders
```bash
php artisan db:seed --class=LeaveTypeSeeder
```

### 3. تسجيل السياسات
```php
// في bootstrap/app.php
Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
Gate::policy(EmployeeLeaveBalance::class, EmployeeLeaveBalancePolicy::class);
```

### 4. تسجيل الأحداث والمستمعين
```php
// في bootstrap/app.php
Event::listen(LeaveRequestSubmitted::class, UpdateLeaveBalanceOnSubmitted::class);
Event::listen(LeaveRequestApproved::class, UpdateLeaveBalanceOnApproved::class);
Event::listen(LeaveRequestRejected::class, UpdateLeaveBalanceOnRejected::class);
Event::listen(LeaveRequestCancelled::class, UpdateLeaveBalanceOnCancelled::class);
```

## الراوتس (Routes)

```php
Route::prefix('hr/leaves')->middleware(['auth'])->group(function () {
    // Leave Balances
    Route::get('/balances', \App\Livewire\Leaves\LeaveBalances\Index::class)->name('leaves.balances.index');
    Route::get('/balances/create', \App\Livewire\Leaves\LeaveBalances\CreateEdit::class)->name('leaves.balances.create');
    Route::get('/balances/{balanceId}/edit', \App\Livewire\Leaves\LeaveBalances\CreateEdit::class)->name('leaves.balances.edit');

    // Leave Requests
    Route::get('/requests', \App\Livewire\Leaves\LeaveRequests\Index::class)->name('leaves.requests.index');
    Route::get('/requests/create', \App\Livewire\Leaves\LeaveRequests\Create::class)->name('leaves.requests.create');
    Route::get('/requests/{requestId}', \App\Livewire\Leaves\LeaveRequests\Show::class)->name('leaves.requests.show');
});
```

## تدفق العمل (Workflow)

### 1. إنشاء طلب إجازة
```
إنشاء طلب → حالة "مسودة" → التحقق من الرصيد → التحقق من التداخل
```

### 2. تقديم الطلب
```
تقديم الطلب → حالة "مقدم" → حجز الأيام في "معلق" → إطلاق حدث LeaveRequestSubmitted
```

### 3. الموافقة على الطلب
```
الموافقة → حالة "معتمد" → نقل من "معلق" إلى "مستخدم" → إطلاق حدث LeaveRequestApproved
```

### 4. رفض الطلب
```
الرفض → حالة "مرفوض" → إطلاق "معلق" → إطلاق حدث LeaveRequestRejected
```

## الاختبارات

### تشغيل الاختبارات
```bash
php artisan test --filter=LeaveManagementTest
```

### الاختبارات المتاحة
- إنشاء أنواع الإجازات
- إنشاء رصيد الموظفين
- إنشاء طلبات الإجازة
- اختبار خدمة رصيد الإجازات
- اختبار انتقالات حالة الطلبات

## التوسعات المستقبلية

### 1. نظام العطلات الرسمية
- جدول `official_holidays`
- استثناء العطلات من حساب أيام العمل

### 2. نظام البصمات
- استيراد سجلات البصمات
- التحقق من تداخل الحضور مع الإجازات

### 3. نظام الرواتب
- ربط الإجازات المدفوعة مع الرواتب
- حساب خصومات الإجازات غير المدفوعة

### 4. الإشعارات
- إشعارات للموظفين عند تغيير حالة الطلب
- إشعارات للمديرين عن الطلبات المعلقة

### 5. التقارير
- تقارير رصيد الإجازات
- تقارير استخدام الإجازات
- تقارير إحصائية

## الدعم التقني

### المتطلبات
- PHP 8.2+
- Laravel 11
- Livewire v3
- MySQL 8.0+

### المتصفحات المدعومة
- Chrome (الأحدث)
- Firefox (الأحدث)
- Safari (الأحدث)
- Edge (الأحدث)

## المساهمة

1. Fork المشروع
2. إنشاء branch للميزة الجديدة
3. Commit التغييرات
4. Push إلى Branch
5. إنشاء Pull Request

## الترخيص

هذا المشروع مرخص تحت رخصة MIT.
