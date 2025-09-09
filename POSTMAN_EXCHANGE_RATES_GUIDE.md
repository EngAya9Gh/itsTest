# دليل استخدام Postman Collection لـ UpdateExchangeRates API

## الملفات المطلوبة

1. **UpdateExchangeRates_Postman_Collection.json** - مجموعة الطلبات
2. **UpdateExchangeRates_Environment.json** - متغيرات البيئة

## كيفية الاستيراد في Postman

### 1. استيراد Collection
1. افتح Postman
2. اضغط على "Import" في الزاوية العلوية اليسرى
3. اسحب ملف `UpdateExchangeRates_Postman_Collection.json` أو اضغط "Upload Files"
4. اضغط "Import"

### 2. استيراد Environment
1. اضغط على أيقونة الترس ⚙️ في الزاوية العلوية اليمنى
2. اختر "Import"
3. اسحب ملف `UpdateExchangeRates_Environment.json`
4. اضغط "Import"
5. اختر البيئة من القائمة المنسدلة

## المتغيرات المطلوبة

### متغيرات البيئة:
- `base_url`: https://api.its-server.online
- `local_url`: http://localhost:8082
- `exchange_api_key`: 2ae6ba4b45bdc5e750ac7312
- `base_currency`: USD
- `auth_token`: (يجب الحصول عليه من تسجيل الدخول)
- `admin_token`: (يجب الحصول عليه من تسجيل دخول الإدارة)

## مجموعات الطلبات

### 1. Currency Management
- **Get All Currencies**: جلب جميع العملات مع أسعارها
- **Get Base Currency**: جلب العملة الأساسية
- **Set Base Currency**: تعيين عملة كأساسية (يتطلب تسجيل دخول)

### 2. Exchange Rate Updates
- **Manual Update Exchange Rates**: تحديث أسعار الصرف يدوياً
- **Get Exchange Rate History**: جلب تاريخ أسعار الصرف

### 3. External API Testing
- **Test ExchangeRate-API.com**: اختبار API الخارجي مباشرة
- **Test API Status**: فحص حالة API والطلبات المتبقية

### 4. Admin Commands
- **Run Artisan Command**: تشغيل أوامر artisan عبر API

## خطوات الاختبار

### 1. اختبار أساسي
```
1. GET /api/currencies - جلب جميع العملات
2. GET /api/currencies/base - جلب العملة الأساسية
3. GET External API - اختبار API الخارجي
```

### 2. اختبار التحديث (يتطلب تسجيل دخول)
```
1. POST /api/login - تسجيل الدخول للحصول على token
2. POST /api/currencies/update-rates - تحديث الأسعار
3. GET /api/currencies - التحقق من التحديث
```

### 3. اختبار الإدارة
```
1. POST /api/currencies/set-base - تعيين عملة أساسية
2. POST /api/admin/artisan - تشغيل أمر currencies:update
```

## أمثلة على الطلبات

### جلب جميع العملات
```http
GET {{base_url}}/api/currencies
Accept: application/json
```

### تحديث أسعار الصرف
```http
POST {{base_url}}/api/currencies/update-rates
Authorization: Bearer {{auth_token}}
Accept: application/json
```

### تعيين عملة أساسية
```http
POST {{base_url}}/api/currencies/set-base
Authorization: Bearer {{auth_token}}
Content-Type: application/json

{
    "currency_code": "USD"
}
```

### تشغيل أمر artisan
```http
POST {{base_url}}/api/admin/artisan
Authorization: Bearer {{admin_token}}
Content-Type: application/json

{
    "command": "currencies:update"
}
```

## الاستجابات المتوقعة

### نجح التحديث
```json
{
    "success": true,
    "message": "تم تحديث أسعار الصرف بنجاح",
    "output": "Updated rate for USD to 1\nUpdated rate for EUR to 0.85\n..."
}
```

### فشل التحديث
```json
{
    "success": false,
    "message": "فشل في تحديث أسعار الصرف",
    "output": "Base currency not set!"
}
```

## نصائح للاستخدام

1. **تأكد من تعيين العملة الأساسية** قبل تحديث الأسعار
2. **احصل على auth_token** من تسجيل الدخول قبل استخدام الطلبات المحمية
3. **راقب الـ Console** في Postman لرؤية التفاصيل
4. **استخدم Environment Variables** لسهولة التبديل بين البيئات

## استكشاف الأخطاء

### خطأ 401 Unauthorized
- تأكد من وجود auth_token صحيح
- تحقق من انتهاء صلاحية الـ token

### خطأ 500 Internal Server Error
- تحقق من logs الخادم
- تأكد من وجود العملة الأساسية
- تحقق من صحة API key الخارجي

### خطأ في API الخارجي
- تحقق من صحة exchange_api_key
- تأكد من وجود اتصال بالإنترنت
- راجع حدود الاستخدام للـ API

## الملفات ذات الصلة

- `app/Console/Commands/UpdateExchangeRates.php` - الأمر الأساسي
- `app/Http/Controllers/ApiCurrencyExchangeController.php` - Controller للـ API
- `routes/api.php` - تعريف الـ routes
- `app/Models/Currency.php` - نموذج العملة
