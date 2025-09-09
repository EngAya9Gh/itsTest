# تحليل API Calls في مكون OrderForm

## 📊 ملخص API Calls المستخدمة

### 1. **جلب بيانات الخدمة** 🔍
**المصدر**: `src/pages/service-sections/[section_id]/service-order/[service_id].jsx`

```javascript
// API Call لجلب بيانات الخدمة
const response = await axios.get(
    `${apiBaseUrl}/service/${appId}`,
    {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }
);
```

**Backend Endpoint**: `/api/service/{id}`  
**Controller**: `ApiServiceController@show`  
**البيانات المُرجعة**:
```json
{
    "service": {
        "id": 1,
        "name": "اسم الخدمة",
        "section_id": 18,
        "type": 1,
        "price": 100,
        "category": {
            "id": 18,
            "name": "SERVER",
            "description": "وصف الصنف"
        }
    }
}
```

### 2. **جلب بيانات المستخدم المسجل** 👤
**المصدر**: `src/components/order-form/service/index.jsx`

```javascript
// API Call لجلب بيانات المستخدم
const response = await axios.get(
    `${apiBaseUrl}/logged-in-user`,
    {
        headers: {
            Authorization: `Bearer ${storedToken}`
        }
    }
);
```

**Backend Endpoint**: `/api/logged-in-user`  
**Controller**: `ApiUserController@getLoggedInUser`  
**الكود**:
```php
public function getLoggedInUser()
{
    return response()->json(Auth::user());
}
```

**البيانات المُرجعة**:
```json
{
    "id": 1,
    "name": "اسم المستخدم",
    "email": "user@example.com",
    "balance": 1000,
    "created_at": "2024-01-01T00:00:00.000000Z",
    // ... باقي بيانات المستخدم
}
```

### 3. **إرسال طلب الشراء** 💳
**المصدر**: `src/components/order-form/service/index.jsx`

```javascript
// منطق تحديد API endpoint
const categoryName = service.category?.name;
let apiEndpoint;

if (categoryName === 'IMEI' || categoryName === 'SERVER') {
    apiEndpoint = `${apiBaseUrl}/service/order/${service.id}`;
} else {
    apiEndpoint = `${apiBaseUrl}/service/orderits/${service.id}`;
}

// API Call لإرسال الطلب
const result = await axios.post(
    apiEndpoint,
    serviceField,
    {
        headers: {
            Authorization: `Bearer ${storedToken}`
        }
    }
);
```

**Backend Endpoints**:
- `/api/service/order/{id}` → `ApiServiceOrderController@store`
- `/api/service/orderits/{id}` → `ApiOrderitsController@store`

## 🔄 تدفق البيانات (Data Flow)

### 1. **تحميل الصفحة**
```
صفحة الطلب → جلب بيانات الخدمة → تمرير للمكون
     ↓
/api/service/{id} → service data → OrderForm component
```

### 2. **تحميل المكون**
```
OrderForm → جلب بيانات المستخدم → ملء الحقول
     ↓
/api/logged-in-user → user data → setUser & setServiceField
```

### 3. **إرسال الطلب**
```
Form Submit → تحديد API endpoint → إرسال البيانات
     ↓
تحديد حسب category.name → /api/service/order أو /api/service/orderits
```

## 📝 البيانات المُرسلة في الطلب

### serviceField Object:
```javascript
{
    username: '',      // اسم المستخدم (للخدمات التي تتطلب ذلك)
    email: '',         // البريد الإلكتروني
    password: '',      // كلمة المرور (للخدمات التي تتطلب ذلك)
    note: '',          // ملاحظات إضافية
    ime: '',           // IMEI أو SN (للخدمات من نوع 1)
    count: 1,          // العدد (للخدمات من نوع 4)
    price: 0,          // السعر المحسوب
    user_id: 0,        // معرف المستخدم (من API logged-in-user)
    service_id: 0      // معرف الخدمة (من props)
}
```

## 🎯 أنواع الخدمات وحقولها

### Type 1: IMEI Services
```javascript
// الحقول المطلوبة
{
    ime: 'IMEI أو SN أو رقم الجوال',
    user_id: 'من API',
    service_id: 'من props',
    price: 'من service.price'
}
```

### Type 2: Email Services
```javascript
// الحقول المطلوبة
{
    email: 'البريد الإلكتروني',
    username: 'اسم المستخدم',
    password: 'كلمة المرور',
    note: 'معلومات إضافية',
    user_id: 'من API',
    service_id: 'من props',
    price: 'من service.price'
}
```

### Type 3: Username Services
```javascript
// الحقول المطلوبة
{
    username: 'اسم المستخدم',
    note: 'معلومات إضافية',
    user_id: 'من API',
    service_id: 'من props',
    price: 'من service.price'
}
```

### Type 4: Count-based Services
```javascript
// الحقول المطلوبة
{
    count: 'العدد',
    email: 'البريد الإلكتروني',
    note: 'معلومات إضافية',
    price: 'count * service.price', // محسوب تلقائياً
    user_id: 'من API',
    service_id: 'من props'
}
```

### Type 5: Username + Password Services
```javascript
// الحقول المطلوبة
{
    username: 'اسم المستخدم',
    password: 'كلمة المرور',
    note: 'معلومات إضافية',
    user_id: 'من API',
    service_id: 'من props',
    price: 'من service.price'
}
```

## 🔐 Authentication & Authorization

### Token Management:
```javascript
// جلب التوكن من localStorage
const storedToken = localStorage.getItem('token');

// استخدام التوكن في جميع API calls
headers: {
    Authorization: `Bearer ${storedToken}`
}
```

### Protected Routes:
- `/api/service/{id}` - يتطلب تسجيل دخول
- `/api/logged-in-user` - يتطلب تسجيل دخول
- `/api/service/order/{id}` - يتطلب تسجيل دخول
- `/api/service/orderits/{id}` - يتطلب تسجيل دخول

## 🧪 كيفية اختبار API Calls

### 1. **في Console المتصفح**:
```javascript
// مراقبة API calls
// افتح Network tab في Developer Tools
// ستجد جميع الطلبات مع تفاصيلها
```

### 2. **Console Logs المضافة**:
```javascript
// في OrderForm component
console.log('Service Data:', service);
console.log('User Data:', user);
console.log('Using endpoint:', apiEndpoint);
```

### 3. **اختبار مباشر**:
```bash
# اختبار جلب بيانات المستخدم
curl -H "Authorization: Bearer YOUR_TOKEN" \
     "http://localhost:8082/api/logged-in-user"

# اختبار جلب بيانات الخدمة
curl -H "Authorization: Bearer YOUR_TOKEN" \
     "http://localhost:8082/api/service/1"
```

## ⚠️ نقاط مهمة

1. **جميع API calls تتطلب Authentication**
2. **بيانات الخدمة تأتي من الصفحة الأب وتُمرر كـ props**
3. **بيانات المستخدم تُجلب داخل المكون نفسه**
4. **API endpoint يتم تحديده حسب نوع الصنف**
5. **السعر يُحسب تلقائياً للخدمات من نوع 4**

## 🔄 تحديثات مطلوبة

✅ **تم**: إضافة `with('category')` في `ApiServiceController`  
✅ **تم**: إضافة console logs للتطوير  
✅ **تم**: إضافة منطق تحديد API endpoint  
✅ **تم**: إضافة معالجة الأخطاء وإعادة تفعيل الزر
