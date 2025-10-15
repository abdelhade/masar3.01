# نظام تتبع الموقع - دليل العمل التفصيلي

## 📋 نظرة عامة

نظام تتبع الموقع هو نظام متكامل لتتبع موقع المستخدمين تلقائياً عند تسجيل الدخول، مع إمكانية التتبع المستمر لمدة 10 ساعات كل 30 دقيقة.

---

## 🛣️ Routes (المسارات)

### Routes المستخدمة في النظام

#### **في `routes/web.php`:**
```php
// API routes لتتبع الموقع
Route::post('/api/location/track', [LocationController::class, 'storeTracking'])
    ->name('api.location.track')
    ->middleware(['auth:web', 'throttle:60,1']);

Route::get('/api/location/history', [LocationController::class, 'getHistory'])
    ->name('api.location.history')
    ->middleware(['auth:web', 'throttle:60,1']);
```

**شرح الـ Routes:**
- **`/api/location/track`** (POST): لحفظ بيانات الموقع الجديدة
- **`/api/location/history`** (GET): لاسترجاع تاريخ المواقع
- **`auth:web`**: يتطلب تسجيل دخول المستخدم
- **`throttle:60,1`**: يسمح بـ 60 طلب في الدقيقة الواحدة

---

## 🔄 تدفق عمل النظام بالتفصيل

### **المرحلة 1: تحميل الصفحة**

عند دخول المستخدم إلى `/admin/dashboard`:

```html
<!-- في admin/main-dashboard.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ auth()->id() }}">

<script src="{{ asset('assets/js/location-tracker.js') }}"></script>
<script>
    @auth
        document.addEventListener('DOMContentLoaded', async function() {
            const googleApiKey = '{{ config("services.google.maps_api_key") }}';
            
            if (typeof LocationTracker !== 'undefined') {
                const locationTracker = new LocationTracker();
                localStorage.removeItem('location_tracking');
                await locationTracker.init(googleApiKey);
            }
        });
    @endauth
</script>
```

**ما يحدث:**
1. تحميل meta tags للـ CSRF token و User ID
2. تحميل ملف `location-tracker.js`
3. إنشاء مثيل جديد من `LocationTracker`
4. مسح أي تتبع سابق من localStorage
5. بدء تهيئة النظام

---

### **المرحلة 2: تهيئة LocationTracker**

```javascript
// في location-tracker.js
async init(googleApiKey = null) {
    this.googleApiKey = googleApiKey;
    
    try {
        await this.registerServiceWorker();  // تسجيل Service Worker
        const permissionGranted = await this.requestPermission();  // طلب إذن الموقع
        
        if (permissionGranted) {
            await this.startTracking();  // بدء التتبع
        }
    } catch (error) {
        console.error('LocationTracker: خطأ في التهيئة:', error);
    }
}
```

**ما يحدث:**
1. حفظ Google API Key
2. تسجيل Service Worker للتتبع في الخلفية
3. طلب إذن الموقع من المستخدم
4. إذا تم منح الإذن، بدء التتبع

---

### **المرحلة 3: تسجيل Service Worker**

```javascript
async registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.register('/service-worker.js');
        this.serviceWorker = registration.active || registration.installing || registration.waiting;
    }
}
```

**ما يحدث:**
1. التحقق من دعم المتصفح لـ Service Worker
2. تسجيل `service-worker.js`
3. حفظ reference للـ Service Worker

---

### **المرحلة 4: طلب إذن الموقع**

```javascript
async requestPermission() {
    try {
        if (!navigator.geolocation) {
            return false;
        }
        
        if ('permissions' in navigator) {
            const permission = await navigator.permissions.query({ name: 'geolocation' });
            
            if (permission.state === 'granted') {
                return true;
            } else if (permission.state === 'prompt') {
                return await this.tryDirectPrompt();
            } else {
                return false;
            }
        } else {
            return await this.tryDirectPrompt();
        }
    } catch (error) {
        return await this.tryDirectPrompt();
    }
}
```

**ما يحدث:**
1. التحقق من دعم المتصفح لـ Geolocation API
2. فحص حالة إذن الموقع الحالية
3. إذا كان `granted`: إرجاع true
4. إذا كان `prompt`: طلب إذن مباشر
5. إذا كان `denied`: إرجاع false

---

### **المرحلة 5: طلب إذن مباشر**

```javascript
async tryDirectPrompt() {
    return new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition(
            () => resolve(true),
            () => resolve(false),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });
}
```

**ما يحدث:**
1. طلب الموقع مباشرة من المتصفح
2. إذا نجح: إرجاع true
3. إذا فشل: إرجاع false
4. استخدام `maximumAge: 0` لضمان الحصول على موقع جديد

---

### **المرحلة 6: بدء التتبع**

```javascript
async startTracking() {
    if (this.isTracking) {
        return;
    }
    
    const userId = document.querySelector('meta[name="user-id"]')?.content;
    if (!userId) {
        console.error('LocationTracker: لم يتم العثور على User ID');
        return;
    }
    
    this.isTracking = true;
    
    // التقاط الموقع فوراً عند بدء التتبع
    try {
        const position = await this.getCurrentPosition();
        await this.sendLocationToServer(position, 'login');
    } catch (error) {
        console.error('LocationTracker: فشل في التقاط الموقع الأول:', error);
    }
    
    // إرسال رسالة للـ Service Worker لبدء التتبع
    if (this.serviceWorker) {
        this.serviceWorker.postMessage({
            type: 'START_TRACKING',
            interval: this.trackingInterval,  // 30 دقيقة
            duration: this.trackingDuration   // 10 ساعات
        });
    }
    
    // حفظ حالة التتبع
    localStorage.setItem('location_tracking', JSON.stringify({
        sessionId: this.sessionId,
        startTime: Date.now(),
        isTracking: true
    }));
    
    // إيقاف التتبع بعد المدة المحددة
    setTimeout(() => {
        this.stopTracking();
    }, this.trackingDuration);
}
```

**ما يحدث:**
1. التحقق من عدم وجود تتبع نشط
2. الحصول على User ID من meta tag
3. تعيين حالة التتبع إلى true
4. التقاط الموقع الأول فوراً (نوع: login)
5. إرسال رسالة للـ Service Worker لبدء التتبع المستمر
6. حفظ حالة التتبع في localStorage
7. جدولة إيقاف التتبع بعد 10 ساعات

---

### **المرحلة 7: التقاط الموقع**

```javascript
async getCurrentPosition() {
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
            resolve,
            reject,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}
```

**ما يحدث:**
1. طلب الموقع الحالي من المتصفح
2. استخدام `enableHighAccuracy: true` للحصول على دقة عالية
3. استخدام `timeout: 10000` (10 ثواني) كحد أقصى للانتظار
4. استخدام `maximumAge: 0` لضمان الحصول على موقع جديد

---

### **المرحلة 8: إرسال البيانات للخادم**

```javascript
async sendLocationToServer(position, type = 'tracking') {
    const userId = document.querySelector('meta[name="user-id"]')?.content;
    if (!userId) return;
    
    let locationData = {
        user_id: userId,
        session_id: this.sessionId,
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy,
        tracked_at: new Date().toISOString(),
        type: type
    };
    
    // إضافة بيانات Google Maps إذا كان API Key متاح
    if (this.googleApiKey) {
        const googleData = await this.getGoogleLocationData(
            position.coords.latitude,
            position.coords.longitude
        );
        if (googleData) {
            locationData.address = googleData.address;
            locationData.place_id = googleData.place_id;
        }
    }
    
    try {
        const response = await fetch('/api/location/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(locationData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
    } catch (error) {
        console.error('LocationTracker: فشل في حفظ الموقع:', error);
    }
}
```

**ما يحدث:**
1. الحصول على User ID من meta tag
2. إنشاء object يحتوي على بيانات الموقع
3. إضافة بيانات Google Maps (العنوان و Place ID) إذا كان API Key متاح
4. إرسال POST request إلى `/api/location/track`
5. إضافة CSRF token في headers
6. معالجة الأخطاء إذا فشل الطلب

---

### **المرحلة 9: الحصول على بيانات Google Maps**

```javascript
async getGoogleLocationData(latitude, longitude) {
    if (!this.googleApiKey) return null;
    
    try {
        const response = await fetch(
            `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${this.googleApiKey}&language=ar`
        );
        
        if (!response.ok) return null;
        
        const data = await response.json();
        
        if (data.status === 'OK' && data.results.length > 0) {
            return {
                address: data.results[0].formatted_address,
                place_id: data.results[0].place_id
            };
        }
        
        return null;
    } catch (error) {
        return null;
    }
}
```

**ما يحدث:**
1. التحقق من وجود Google API Key
2. إرسال request إلى Google Geocoding API
3. تحويل الإحداثيات إلى عنوان
4. إرجاع العنوان و Place ID
5. معالجة الأخطاء وإرجاع null في حالة الفشل

---

### **المرحلة 10: معالجة البيانات في الخادم**

#### **في `LocationController.php`:**

```php
public function storeTracking(Request $request)
{
    $validator = Validator::make($request->all(), [
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'accuracy' => 'nullable|numeric|min:0',
        'session_id' => 'required|string',
        'tracked_at' => 'required|date',
        'type' => 'nullable|string|in:login,tracking,attendance',
        'address' => 'nullable|string|max:500',
        'place_id' => 'nullable|string|max:255'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        $tracking = UserLocationTracking::create([
            'user_id' => Auth::id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'session_id' => $request->session_id,
            'tracked_at' => $request->tracked_at,
            'type' => $request->type ?? 'tracking',
            'address' => $request->address,
            'place_id' => $request->place_id,
            'additional_data' => $request->additional_data ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location tracked successfully',
            'data' => $tracking
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to save location',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

**ما يحدث:**
1. التحقق من صحة البيانات المرسلة
2. التحقق من أن الإحداثيات في النطاق الصحيح
3. التحقق من وجود المستخدم المسجل دخول
4. حفظ البيانات في قاعدة البيانات
5. إرجاع استجابة JSON بنجاح أو خطأ

---

### **المرحلة 11: Service Worker للتتبع في الخلفية**

#### **في `public/service-worker.js`:**

```javascript
self.addEventListener('message', function(event) {
    if (event.data.type === 'START_TRACKING') {
        const interval = event.data.interval;  // 30 دقيقة
        const duration = event.data.duration;  // 10 ساعات
        
        // بدء التتبع كل 30 دقيقة
        const trackingInterval = setInterval(() => {
            // إرسال رسالة للصفحة لتحديث الموقع
            self.clients.matchAll().then(clients => {
                clients.forEach(client => {
                    client.postMessage({
                        type: 'CAPTURE_LOCATION',
                        sessionId: event.data.sessionId
                    });
                });
            });
        }, interval);
        
        // إيقاف التتبع بعد 10 ساعات
        setTimeout(() => {
            clearInterval(trackingInterval);
        }, duration);
    }
});
```

**ما يحدث:**
1. استقبال رسالة بدء التتبع من الصفحة
2. إنشاء interval للتتبع كل 30 دقيقة
3. إرسال رسالة للصفحة لتحديث الموقع
4. جدولة إيقاف التتبع بعد 10 ساعات

---

## 🗄️ قاعدة البيانات

### **جدول `user_location_tracking`:**

```sql
CREATE TABLE user_location_tracking (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    accuracy DECIMAL(8, 2) NULL,
    tracked_at TIMESTAMP NOT NULL,
    type ENUM('login', 'tracking', 'attendance') DEFAULT 'tracking',
    address TEXT NULL,
    place_id VARCHAR(255) NULL,
    additional_data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_session (user_id, session_id),
    INDEX idx_tracked_at (tracked_at),
    INDEX idx_type (type)
);
```

**شرح الحقول:**
- **`user_id`**: معرف المستخدم
- **`session_id`**: معرف الجلسة الفريدة
- **`latitude`**: خط العرض (من -90 إلى 90)
- **`longitude`**: خط الطول (من -180 إلى 180)
- **`accuracy`**: دقة الموقع بالمتر
- **`tracked_at`**: وقت تسجيل الموقع
- **`type`**: نوع التتبع (login, tracking, attendance)
- **`address`**: العنوان من Google Maps
- **`place_id`**: معرف المكان من Google Maps
- **`additional_data`**: بيانات إضافية (JSON)

---

## 📊 أنواع التتبع

### **1. `login`**
- **متى**: عند تسجيل الدخول
- **كم مرة**: مرة واحدة فقط
- **الغرض**: تسجيل موقع تسجيل الدخول

### **2. `tracking`**
- **متى**: كل 30 دقيقة
- **كم مرة**: لمدة 10 ساعات
- **الغرض**: التتبع المستمر للموقع

### **3. `attendance`**
- **متى**: عند تسجيل الحضور
- **كم مرة**: حسب الحاجة
- **الغرض**: ربط الموقع بالحضور

---

## ⏰ مدة التتبع

### **الإعدادات الحالية:**
- **فترة التتبع**: كل 30 دقيقة
- **مدة التتبع**: 10 ساعات
- **التتبع الأولي**: فوراً عند تسجيل الدخول

### **تغيير الإعدادات:**
```javascript
// في location-tracker.js
this.trackingInterval = 15 * 60 * 1000; // 15 دقيقة بدلاً من 30
this.trackingDuration = 8 * 60 * 60 * 1000; // 8 ساعات بدلاً من 10
```

---

## 🔒 الأمان

### **1. CSRF Protection**
```javascript
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
```

### **2. Authentication**
```php
->middleware(['auth:web', 'throttle:60,1'])
```

### **3. Data Validation**
```php
'latitude' => 'required|numeric|between:-90,90',
'longitude' => 'required|numeric|between:-180,180',
```

### **4. Rate Limiting**
- **60 طلب في الدقيقة الواحدة**
- **منع spam requests**

---

## 🔧 التكوين

### **1. متغيرات البيئة**
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

### **2. إعدادات Laravel**
```php
// في config/services.php
'google' => [
    'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
],
```

---

## 📱 دعم المتصفحات

### **المتطلبات:**
- **Geolocation API**: لتحديد الموقع
- **Service Worker**: للتتبع في الخلفية
- **Fetch API**: لإرسال البيانات
- **Promises**: للعمليات غير المتزامنة

### **المتصفحات المدعومة:**
- ✅ Chrome 50+
- ✅ Firefox 44+
- ✅ Safari 11.1+
- ✅ Edge 17+

---

## 🐛 استكشاف الأخطاء

### **مشاكل شائعة:**

#### **1. النظام لا يطلب إذن الموقع**
- تحقق من وجود Google API Key في `.env`
- تحقق من أن المستخدم مسجل دخول
- تحقق من Console للأخطاء

#### **2. البيانات لا تُحفظ**
- تحقق من أن جدول `user_location_tracking` موجود
- تحقق من أن المستخدم له صلاحية الكتابة
- تحقق من سجلات Laravel

#### **3. الإحداثيات غير صحيحة**
- تأكد من أن المتصفح يدعم Geolocation API
- تحقق من إعدادات الموقع في المتصفح
- تأكد من أن المستخدم منح إذن الموقع

---

## 📈 مراقبة الأداء

### **1. حجم قاعدة البيانات**
```sql
SELECT COUNT(*) as total_records FROM user_location_tracking;
SELECT COUNT(*) as records_today FROM user_location_tracking WHERE DATE(tracked_at) = CURDATE();
```

### **2. استخدام Google API**
- راقب quota في Google Cloud Console
- تحقق من عدد requests اليومية

### **3. استجابة API**
- راقب وقت استجابة `/api/location/track`
- تحقق من معدل الأخطاء

---

## 🧹 الصيانة

### **1. تنظيف البيانات القديمة**
```sql
-- حذف البيانات الأقدم من 30 يوم
DELETE FROM user_location_tracking 
WHERE tracked_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### **2. النسخ الاحتياطي**
- تأكد من تضمين جدول `user_location_tracking` في النسخ الاحتياطية
- احتفظ بنسخة احتياطية من ملفات JavaScript

---

## 📚 المراجع

- **Laravel Documentation**: https://laravel.com/docs
- **Geolocation API**: https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API
- **Service Workers**: https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API
- **Google Maps API**: https://developers.google.com/maps/documentation

---

**آخر تحديث**: يناير 2025  
**الإصدار**: 1.0  
**المطور**: فريق تطوير Massar ERP
