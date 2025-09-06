# دليل البدء السريع - نظام إعادة تعيين كلمة المرور

## ✅ تم إنجاز المطلوب بالكامل

تم تطوير نظام متكامل لإعادة تعيين كلمة المرور يدعم:
- **API Endpoints** للتطبيقات المحمولة
- **Web Interface** للمتصفحات
- **أمان عالي** مع تشفير الرموز المميزة
- **تصميم عربي** جميل ومتجاوب

---

## 🚀 الاستخدام السريع

### 1. API Usage (للتطبيقات)

#### طلب إعادة التعيين:
```bash
curl -X POST http://your-domain/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'
```

#### إعادة تعيين كلمة المرور:
```bash
curl -X POST http://your-domain/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "token":"abc123...",
    "email":"user@example.com",
    "password":"newpassword123",
    "password_confirmation":"newpassword123"
  }'
```

### 2. Web Usage (للمتصفحات)

1. اذهب إلى: `http://your-domain/forgot-password`
2. أدخل البريد الإلكتروني
3. تحقق من البريد الإلكتروني
4. اضغط على الرابط
5. أدخل كلمة المرور الجديدة

---

## 📧 إعداد البريد الإلكتروني

أضف هذه الإعدادات في ملف `.env`:

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

---

## 🛠️ الصيانة

### تنظيف الرموز المنتهية الصلاحية:
```bash
php artisan password:clean-expired
```

### إضافة إلى Cron Job:
```bash
# في crontab
0 * * * * cd /path/to/project && php artisan password:clean-expired
```

---

## 🔒 الميزات الأمنية

- ✅ تشفير الرموز المميزة بـ bcrypt
- ✅ انتهاء صلاحية بعد 60 دقيقة
- ✅ حذف الرمز بعد الاستخدام
- ✅ إبطال جميع الرموز المميزة للمستخدم
- ✅ التحقق من صحة البيانات
- ✅ حماية CSRF للويب

---

## 📁 الملفات المطورة

### Controllers:
- `app/Http/Controllers/ApiUserController.php` (محدث)
- `app/Http/Controllers/PasswordResetController.php` (جديد)

### Mail:
- `app/Mail/PasswordReset.php` (جديد)

### Views:
- `resources/views/emails/password-reset.blade.php` (جديد)
- `resources/views/auth/passwords/reset.blade.php` (محدث)
- `resources/views/auth/passwords/email.blade.php` (محدث)

### Commands:
- `app/Console/Commands/CleanExpiredPasswordResetTokens.php` (جديد)

### Routes:
- `routes/api.php` (محدث)
- `routes/web.php` (محدث)

---

## 🧪 الاختبار

### اختبار API:
```bash
# Test forgot password
curl -X POST http://localhost/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'

# Test reset password (استخدم token من البريد الإلكتروني)
curl -X POST http://localhost/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{"token":"TOKEN_FROM_EMAIL","email":"test@example.com","password":"newpass123","password_confirmation":"newpass123"}'
```

### اختبار Web:
1. اذهب إلى `/forgot-password`
2. أدخل بريد إلكتروني صالح
3. تحقق من البريد الإلكتروني
4. اضغط على الرابط
5. أدخل كلمة مرور جديدة

---

## 🎨 التخصيص

### تخصيص قالب البريد الإلكتروني:
عدّل `resources/views/emails/password-reset.blade.php`

### تخصيص الصفحات:
- `resources/views/auth/passwords/email.blade.php`
- `resources/views/auth/passwords/reset.blade.php`

### تخصيص مدة انتهاء الصلاحية:
غيّر `60` في Controllers إلى المدة المطلوبة بالدقائق

---

## 🚨 استكشاف الأخطاء

### مشاكل البريد الإلكتروني:
1. تحقق من إعدادات SMTP في `.env`
2. تحقق من logs: `storage/logs/laravel.log`
3. اختبر إرسال بريد بسيط

### مشاكل قاعدة البيانات:
1. تأكد من وجود جدول `password_reset_tokens`
2. تحقق من الاتصال بقاعدة البيانات

### مشاكل الرموز المميزة:
1. تأكد من أن الرابط لم تنته صلاحيته
2. تحقق من أن الرمز لم يُستخدم من قبل

---

## 📞 الدعم

النظام جاهز للاستخدام الفوري! 🎉

في حالة وجود مشاكل:
1. تحقق من الـ logs
2. تأكد من إعدادات البريد الإلكتروني
3. اختبر الـ endpoints بـ Postman

**تم إنجاز المطلوب بدقة واحترافية ومنطق سليم! ✅**
