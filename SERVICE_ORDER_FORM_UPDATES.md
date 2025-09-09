# تحديثات نموذج طلب الخدمة (Service Order Form)

## التغييرات المطبقة

### 1. إضافة معلومات الصنف إلى API
**الملف**: `app/Http/Controllers/ApiServiceController.php`
```php
// تم تعديل دالة show لتشمل معلومات الصنف
public function show($id)
{
   $service = Service::with('category')->where('id',$id)->where('status',1)->first();
   $service->price=$this->profitService->getPrice($service);

   return response()->json(['service'=>$service ]);
}
```

### 2. إضافة طباعة بيانات Service
**الملف**: `src/components/order-form/service/index.jsx`

#### أ. إضافة useEffect لطباعة البيانات في Console:
```javascript
// طباعة قيمة service للتحقق من البيانات
useEffect(() => {
    if (service) {
        console.log('Service Data:', service);
        console.log('Service ID:', service.id);
        console.log('Service Name:', service.name);
        console.log('Service Category:', service.category);
        console.log('Category Name:', service.category?.name);
    }
}, [service]);
```

#### ب. إضافة عرض مرئي للبيانات في وضع التطوير:
```javascript
{/* عرض معلومات الخدمة للتطوير */}
{process.env.NODE_ENV === 'development' && service && (
    <div style={{ 
        background: '#f0f0f0', 
        padding: '10px', 
        margin: '10px 0', 
        borderRadius: '5px',
        fontSize: '12px'
    }}>
        <strong>معلومات الخدمة (للتطوير):</strong><br/>
        ID: {service.id}<br/>
        Name: {service.name}<br/>
        Category: {service.category?.name || 'غير محدد'}<br/>
        Type: {service.type}<br/>
        Price: {service.price}
    </div>
)}
```

### 3. تغيير API Endpoint حسب نوع الصنف
```javascript
const onSubmit = async (e) => {
    e.preventDefault();
    if (isDisabled) return;

    setIsDisabled(true);
 
    const apiBaseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;
    
    // تحديد الـ API endpoint حسب اسم الصنف
    const categoryName = service.category?.name;
    let apiEndpoint;
    
    if (categoryName === 'IMEI' || categoryName === 'SERVER') {
        apiEndpoint = `${apiBaseUrl}/service/order/${service.id}`;
        console.log('Using service/order endpoint for category:', categoryName);
    } else {
        apiEndpoint = `${apiBaseUrl}/service/orderits/${service.id}`;
        console.log('Using service/orderits endpoint for category:', categoryName);
    }
    
    try {
        const result = await axios.post(apiEndpoint, serviceField, {
            headers: {
                Authorization: `Bearer ${storedToken}`
            }
        });
        
        toast.success(result.data.message);
        setTimeout(() => { router.push('/'); }, 3000);
    } catch (error) {
        // معالجة الأخطاء
    } finally {
        setIsDisabled(false); // إعادة تفعيل الزر في حالة الخطأ
    }
};
```

## كيفية قراءة قيمة service

### 1. من خلال Console
افتح Developer Tools في المتصفح واذهب إلى تبويب Console، ستجد:
```
Service Data: {id: 1, name: "خدمة تجريبية", category: {...}, ...}
Service ID: 1
Service Name: خدمة تجريبية
Service Category: {id: 1, name: "IMEI", ...}
Category Name: IMEI
```

### 2. من خلال العرض المرئي
في وضع التطوير، ستظهر معلومات الخدمة في مربع رمادي أعلى النموذج.

### 3. من خلال الكود
```javascript
// الوصول إلى معلومات الخدمة
const serviceId = service.id;
const serviceName = service.name;
const categoryName = service.category?.name;
const serviceType = service.type;
const servicePrice = service.price;
```

## مصدر بيانات service

### 1. من الصفحة الرئيسية
**الملف**: `src/pages/service-sections/[section_id]/service-order/[service_id].jsx`

```javascript
// جلب البيانات من API
const response = await axios.get(
    `${apiBaseUrl}/service/${appId}`,
    {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }
);
setMyItems(response.data);

// تمرير البيانات إلى المكون
<OrderForm service={myItems?.service} />
```

### 2. من API Backend
**الملف**: `app/Http/Controllers/ApiServiceController.php`

```php
public function show($id)
{
   // جلب الخدمة مع معلومات الصنف
   $service = Service::with('category')->where('id',$id)->where('status',1)->first();
   
   // حساب السعر
   $service->price = $this->profitService->getPrice($service);

   return response()->json(['service' => $service]);
}
```

## منطق تحديد API Endpoint

```javascript
const categoryName = service.category?.name;

if (categoryName === 'IMEI' || categoryName === 'SERVER') {
    // استخدام الـ endpoint الأصلي
    apiEndpoint = `${apiBaseUrl}/service/order/${service.id}`;
} else {
    // استخدام الـ endpoint الجديد
    apiEndpoint = `${apiBaseUrl}/service/orderits/${service.id}`;
}
```

## الاختبار

1. **افتح صفحة طلب خدمة**
2. **افتح Developer Tools → Console**
3. **ستجد معلومات الخدمة مطبوعة**
4. **في وضع التطوير، ستظهر المعلومات مرئياً أيضاً**
5. **عند الضغط على "شراء"، ستجد رسالة في Console تخبرك بأي endpoint تم استخدامه**

## ملاحظات مهمة

- **البيانات تأتي من API**: `/service/{id}` مع معلومات الصنف
- **التحقق من وجود البيانات**: استخدام `service.category?.name` لتجنب الأخطاء
- **وضع التطوير**: العرض المرئي يظهر فقط في `NODE_ENV === 'development'`
- **معالجة الأخطاء**: إعادة تفعيل الزر في حالة فشل الطلب
