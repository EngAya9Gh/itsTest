# تحديثات نظام إعادة تعيين كلمة المرور

## التحديثات المطبقة

### 1. إضافة عمود user_type لجدول password_reset_tokens
- تم إنشاء migration جديد: `2025_09_07_124854_add_user_type_to_password_reset_tokens_table.php`
- العمود يحدد نوع المستخدم (admin/user) لتوجيه صحيح بعد إعادة التعيين

### 2. تحديث ApiUserController
- **forgotPassword method**: إضافة حفظ user_type في قاعدة البيانات
- **resetPassword method**: إضافة إرجاع user_type في الاستجابة
- إضافة دعم redirect_to parameter للعودة للصفحة المطلوبة

### 3. تحديث PasswordResetController
- **showResetForm method**: إضافة دعم redirect_to parameter
- **reset method**: تحسين منطق التوجيه بعد إعادة التعيين

### 4. تحديث React Components

#### login-form component
- إضافة إرسال redirect_to مع طلب forgot-password
- يرسل المسار الحالي للعودة إليه بعد إكمال إعادة التعيين

#### login-form-test component  
- إضافة إرسال redirect_to مع طلب forgot-password للإدارة
- يرسل المسار الحالي للعودة إليه بعد إكمال إعادة التعيين

#### reset-password-form component
- قراءة جميع المعاملات من URL (token, email, type, redirect_to)
- إرسال البيانات الكاملة لـ API
- تحسين معالجة الأخطاء
- توجيه ذكي بناءً على redirect_to أو user_type

### 5. تحديث Blade Templates
- **reset.blade.php**: إضافة redirect parameter كحقل مخفي
- **email.blade.php**: user_type موجود مسبقاً للإدارة

## كيفية عمل النظام الجديد

### للمستخدمين العاديين:
1. المستخدم يطلب إعادة تعيين من صفحة `/login`
2. يتم إرسال user_type: 'user' و redirect_to: المسار الحالي
3. رابط إعادة التعيين يوجه للـ frontend: `/reset-password?token=...&email=...&type=user&redirect_to=...`
4. بعد إعادة التعيين، يتم التوجيه للـ redirect_to أو `/login?reset=done`

### للإدارة:
1. الإداري يطلب إعادة تعيين من صفحة `/login-test` أو `/password/reset`
2. يتم إرسال user_type: 'admin' و redirect_to: المسار الحالي
3. رابط إعادة التعيين يوجه للـ backend: `/reset-password?token=...&email=...&type=admin&redirect_to=...`
4. بعد إعادة التعيين، يتم التوجيه للـ redirect_to أو `/login`

## API Endpoints

### POST /api/forgot-password
```json
{
  "email": "user@example.com",
  "user_type": "user|admin",
  "redirect_to": "/dashboard" // اختياري
}
```

### POST /api/reset-password  
```json
{
  "token": "...",
  "email": "user@example.com", 
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

**Response:**
```json
{
  "success": true,
  "message": "تم تغيير كلمة المرور بنجاح...",
  "user_type": "user|admin"
}
```

## الاختبار

تم اختبار النظام بنجاح:
- ✅ إرسال forgot-password للمستخدمين العاديين
- ✅ إرسال forgot-password للإدارة  
- ✅ حفظ user_type في قاعدة البيانات
- ✅ إعادة تعيين كلمة المرور عبر API
- ✅ إرجاع user_type في الاستجابة

## الملفات المحدثة

### Backend (Laravel):
- `database/migrations/2025_09_07_124854_add_user_type_to_password_reset_tokens_table.php`
- `app/Http/Controllers/ApiUserController.php`
- `app/Http/Controllers/PasswordResetController.php`
- `resources/views/auth/passwords/reset.blade.php`

### Frontend (React):
- `its-server.online/src/components/login-form/index.jsx`
- `its-server.online/src/components/login-form-test/index.jsx`
- `its-server.online/src/components/reset-password-form/index.jsx`

## ملاحظات مهمة

1. **الأمان**: جميع tokens محمية بـ Hash وتنتهي صلاحيتها خلال 60 دقيقة
2. **التوافق**: النظام متوافق مع الكود الموجود ولا يكسر الوظائف الحالية
3. **المرونة**: يدعم التوجيه المخصص أو التوجيه الافتراضي
4. **معالجة الأخطاء**: تحسين معالجة الأخطاء في جميع المكونات
