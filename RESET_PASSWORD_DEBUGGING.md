# تشخيص مشكلة Reset Password API - 422 Error

## المشكلة
```
POST https://api.its-server.online/api/reset-password 422 (Unprocessable Content)
```

## الأسباب المحتملة

### 1. مشكلة في Validation
- Laravel يتطلب `password_confirmation` field
- كلمة المرور يجب أن تكون 8 أحرف على الأقل
- Token أو Email قد يكونان غير صحيحين

### 2. مشكلة في URL Parameters
- Token أو Email قد لا يصلان من URL بشكل صحيح
- Next.js router.query قد يحتاج وقت للتحميل

## الحلول المطبقة

### 1. تحسين معالجة الأخطاء
```javascript
// إضافة console.log للتشخيص
console.log('Reset password data:', {
    token: token ? 'present' : 'missing',
    email: email || 'missing',
    password: password ? 'present' : 'missing',
    password_confirmation: confirmPassword ? 'present' : 'missing'
});

// تحسين معالجة الأخطاء
if (error.response?.data?.errors) {
    const errors = error.response.data.errors;
    Object.keys(errors).forEach((key) => {
        errors[key].forEach((message) => toast.error(message));
    });
} else if (error.response?.status === 422) {
    toast.error('البيانات المدخلة غير صحيحة. يرجى التحقق من جميع الحقول.');
}
```

### 2. إضافة Frontend Validation
```javascript
if (password.length < 8) {
    toast.error('يجب ألا تقل كلمة المرور عن 8 أحرف.');
    return;
}
```

### 3. إضافة Debug Logging للـ URL Parameters
```javascript
useEffect(() => {
    console.log('URL Parameters:', { token, email, type, redirect_to });
}, [token, email, type, redirect_to]);
```

### 4. تحسين UI
- إضافة متطلبات كلمة المرور
- تحسين رسائل الخطأ

## خطوات التشخيص

### 1. فحص Console Logs
افتح Developer Tools وتحقق من:
- `URL Parameters:` - هل token و email موجودان؟
- `Reset password data:` - هل جميع البيانات present؟
- `Reset password error:` - ما هو الخطأ المحدد؟

### 2. فحص Network Tab
- تحقق من Request payload
- تحقق من Response body للحصول على تفاصيل الخطأ

### 3. استخدام ملف الاختبار
افتح `test_reset_api.html` في المتصفح لاختبار API مباشرة:
1. أدخل البريد الإلكتروني واضغط "Generate Token"
2. انسخ Token من قاعدة البيانات
3. أدخل البيانات واضغط "Reset Password"

## Laravel Validation Rules
```php
$request->validate([
    'token' => 'required|string',
    'email' => 'required|email|exists:users,email',
    'password' => 'required|string|min:8|confirmed',
]);
```

## الحقول المطلوبة في API Request
```json
{
    "token": "string (required)",
    "email": "email (required, must exist in users table)",
    "password": "string (required, min 8 chars)",
    "password_confirmation": "string (required, must match password)"
}
```

## خطوات إضافية للتشخيص

### 1. فحص قاعدة البيانات
```sql
SELECT email, token, user_type, created_at 
FROM password_reset_tokens 
ORDER BY created_at DESC LIMIT 5;
```

### 2. فحص Laravel Logs
```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

### 3. اختبار API مع cURL
```bash
curl -X POST https://api.its-server.online/api/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "token": "YOUR_TOKEN",
    "email": "info.eng.123@gmail.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

## الملفات المحدثة
- `src/components/reset-password-form/index.jsx` - تحسين معالجة الأخطاء والتشخيص
- `test_reset_api.html` - ملف اختبار مستقل
