# نظام إعادة تعيين كلمة المرور - Password Reset System

## نظرة عامة
تم تطوير نظام متكامل لإعادة تعيين كلمة المرور يدعم كل من API و Web interfaces مع أمان عالي وتجربة مستخدم ممتازة.

## المكونات الرئيسية

### 1. Controllers
- **ApiUserController**: يحتوي على methods للـ API
  - `forgotPassword()`: إرسال رابط إعادة التعيين
  - `resetPassword()`: إعادة تعيين كلمة المرور
  - `checkEmailForReset()`: التحقق من وجود البريد الإلكتروني

- **PasswordResetController**: يحتوي على methods للـ Web
  - `showResetForm()`: عرض صفحة إعادة التعيين
  - `reset()`: معالجة طلب إعادة التعيين

### 2. Mail System
- **PasswordReset Mailable**: فئة البريد الإلكتروني
- **password-reset.blade.php**: قالب البريد الإلكتروني المصمم باللغة العربية

### 3. Views
- **email.blade.php**: صفحة طلب إعادة التعيين
- **reset.blade.php**: صفحة إعادة تعيين كلمة المرور

### 4. Database
- **password_reset_tokens**: جدول تخزين الرموز المميزة

## الميزات الأمنية

### 1. Token Security
- استخدام `bcrypt` لتشفير الرموز المميزة
- رموز عشوائية بطول 60 حرف
- انتهاء صلاحية بعد 60 دقيقة

### 2. Validation
- التحقق من صحة البريد الإلكتروني
- التحقق من وجود المستخدم
- التحقق من قوة كلمة المرور (8 أحرف على الأقل)
- تأكيد كلمة المرور

### 3. Security Measures
- حذف الرمز المميز بعد الاستخدام
- إبطال جميع الرموز المميزة للمستخدم بعد إعادة التعيين
- تنظيف الرموز المنتهية الصلاحية تلقائياً

## API Endpoints

### 1. طلب إعادة التعيين
```
POST /api/forgot-password
Content-Type: application/json

{
    "email": "user@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني."
}
```

### 2. إعادة تعيين كلمة المرور
```
POST /api/reset-password
Content-Type: application/json

{
    "token": "abc123...",
    "email": "user@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم تغيير كلمة المرور بنجاح. يرجى تسجيل الدخول مرة أخرى."
}
```

### 3. التحقق من البريد الإلكتروني
```
POST /api/check-email-for-reset
Content-Type: application/json

{
    "email": "user@example.com"
}
```

## Web Routes

### 1. صفحة طلب إعادة التعيين
```
GET /forgot-password
```

### 2. معالجة طلب إعادة التعيين
```
POST /forgot-password
```

### 3. صفحة إعادة التعيين
```
GET /reset-password?token=abc123&email=user@example.com
```

### 4. معالجة إعادة التعيين
```
POST /reset-password
```

## إعداد البريد الإلكتروني

تأكد من إعداد البريد الإلكتروني في ملف `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="Your App Name"
```

## Commands

### تنظيف الرموز المنتهية الصلاحية
```bash
php artisan password:clean-expired
```

يمكن إضافة هذا الأمر إلى cron job للتشغيل التلقائي:
```bash
# في crontab
0 * * * * cd /path/to/your/project && php artisan password:clean-expired
```

## Error Handling

### API Errors
- **422**: خطأ في التحقق من البيانات
- **400**: رابط غير صالح أو منتهي الصلاحية
- **500**: خطأ في الخادم

### Web Errors
- إعادة توجيه مع رسائل خطأ
- عرض الأخطاء في الصفحة

## التخصيص

### 1. تخصيص قالب البريد الإلكتروني
عدّل الملف: `resources/views/emails/password-reset.blade.php`

### 2. تخصيص الصفحات
- `resources/views/auth/passwords/email.blade.php`
- `resources/views/auth/passwords/reset.blade.php`

### 3. تخصيص مدة انتهاء الصلاحية
غيّر القيمة في Controllers من 60 دقيقة إلى المدة المطلوبة.

## الاختبار

### 1. اختبار API
```bash
# طلب إعادة التعيين
curl -X POST http://your-domain/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'

# إعادة التعيين
curl -X POST http://your-domain/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{"token":"abc123","email":"test@example.com","password":"newpass123","password_confirmation":"newpass123"}'
```

### 2. اختبار Web
1. اذهب إلى `/forgot-password`
2. أدخل البريد الإلكتروني
3. تحقق من البريد الإلكتروني
4. اضغط على الرابط
5. أدخل كلمة المرور الجديدة

## الصيانة

### 1. مراقبة الأداء
- مراقبة عدد الطلبات
- مراقبة حالة إرسال البريد الإلكتروني
- مراقبة حجم جدول password_reset_tokens

### 2. التنظيف الدوري
- تشغيل أمر تنظيف الرموز المنتهية الصلاحية
- مراقبة logs الأخطاء

## الأمان

### 1. Rate Limiting
يُنصح بإضافة rate limiting للـ endpoints:
```php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/forgot-password', ...);
    Route::post('/reset-password', ...);
});
```

### 2. CSRF Protection
تم تفعيل CSRF protection للـ web routes تلقائياً.

### 3. Input Sanitization
جميع المدخلات يتم التحقق منها وتنظيفها.

## الدعم الفني

في حالة وجود مشاكل:
1. تحقق من logs Laravel
2. تحقق من إعدادات البريد الإلكتروني
3. تحقق من قاعدة البيانات
4. تحقق من صلاحيات الملفات
