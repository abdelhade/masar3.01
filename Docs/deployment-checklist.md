# قائمة التحقق من الـ Deployment - Deployment Checklist

## ✅ قبل الـ Deployment

### 1. التحقق من ملف `.env` للإنتاج
```bash
# تأكد من تحديث هذه القيم
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-actual-domain.com

# تأكد من قوة مفتاح التطبيق
APP_KEY=base64:xxxx...
```

### 2. التحقق من إعدادات قاعدة البيانات
```bash
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password
```

### 3. التحقق من إعدادات الصور
```bash
FILESYSTEM_DISK=public
MEDIA_DISK=public
```

---

## ✅ بعد رفع الملفات على السيرفر

### 1. تثبيت الـ Dependencies
```bash
# Composer dependencies
composer install --no-dev --optimize-autoloader

# NPM dependencies (إذا لزم الأمر)
npm ci --production
npm run build
```

### 2. إعدادات Laravel الأساسية
```bash
# إنشاء symbolic link للصور
php artisan storage:link

# مسح وإعادة بناء الـ cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# تشغيل الـ migrations
php artisan migrate --force
```

### 3. صلاحيات المجلدات
```bash
# منح صلاحيات الكتابة
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# تغيير المالك (على حسب السيرفر)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 4. التحقق من عمل الـ symbolic link
```bash
# التحقق من وجود الـ link
ls -la public/storage

# يجب أن يظهر شيء مثل:
# storage -> /path/to/your-project/storage/app/public
```

---

## ✅ اختبار بعد الـ Deployment

### 1. اختبار رفع الصور
1. قم بتسجيل الدخول للنظام
2. اذهب إلى صفحة الموظفين
3. أضف موظف جديد مع صورة
4. تأكد من ظهور الصورة بشكل صحيح
5. افتح الصورة في tab جديد وتأكد من رابط الصورة:
   ```
   ✅ يجب أن يكون: https://your-domain.com/storage/1/image-name.png
   ❌ يجب ألا يكون: http://localhost:8000/storage/...
   ```

### 2. اختبار تعديل الصور
1. قم بتعديل موظف موجود
2. ارفع صورة جديدة
3. تأكد من استبدال الصورة القديمة
4. تأكد من ظهور الصورة الجديدة

### 3. اختبار حذف الصور
1. قم بحذف موظف لديه صورة
2. تأكد من حذف الصورة من السيرفر
3. تأكد من عدم ظهور أخطاء

---

## 🔧 حل المشاكل السريع

### المشكلة: "File not found" عند رفع صورة
```bash
# الحل
mkdir -p storage/app/public
chmod -R 775 storage
php artisan storage:link
```

### المشكلة: الصور لا تظهر
```bash
# الحل
# 1. تأكد من APP_URL في .env
nano .env
# تأكد من: APP_URL=https://your-actual-domain.com

# 2. امسح الـ cache
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# 3. أعد إنشاء الـ symbolic link
rm -f public/storage
php artisan storage:link

# 4. تأكد من الصلاحيات
chmod -R 775 storage
```

### المشكلة: خطأ "The stream or file could not be opened"
```bash
# الحل
chmod -R 775 storage/logs
chown -R www-data:www-data storage
```

### المشكلة: الروابط تظهر بـ localhost
```bash
# الحل
# 1. تحديث APP_URL في .env
APP_URL=https://your-actual-domain.com

# 2. مسح الـ config cache
php artisan config:clear
php artisan config:cache
```

---

## 📝 ملاحظات مهمة

1. **لا تنسى** تشغيل `php artisan storage:link` بعد كل deployment جديد
2. **تأكد دائماً** من صحة `APP_URL` في `.env`
3. **لا تستخدم** `php artisan config:cache` في البيئة المحلية (Local)
4. **احفظ نسخة احتياطية** من قاعدة البيانات قبل تشغيل migrations في الإنتاج

---

## 📞 في حالة المشاكل

إذا واجهت أي مشكلة:
1. راجع ملف `storage/logs/laravel.log` للأخطاء
2. راجع ملف التوثيق: `Docs/image-upload-setup.md`
3. تأكد من تطبيق جميع خطوات هذه القائمة

