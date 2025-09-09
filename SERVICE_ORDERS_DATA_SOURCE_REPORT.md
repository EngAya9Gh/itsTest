# تقرير مصدر بيانات `$serviceOrders` 📊

## 🎯 **المصدر الرئيسي للبيانات**

### 📍 **الـ Controller المسؤول**:
```php
ServiceOrderController::class
المسار: /app/Http/Controllers/ServiceOrderController.php
```

### 🔗 **الـ Routes المرتبطة**:
```php
// في ملف routes/web.php
Route::resource('service-order', ServiceOrderController::class);
Route::get('service-order/{type}/type', [ServiceOrderController::class, 'getOrder']);
Route::post('service-order/reject/{id}', [ServiceOrderController::class, 'reject'])->name('service-order.reject');
Route::get('service-order/accept/{id}', [ServiceOrderController::class, 'accept']);
```

## 🗄️ **مصدر البيانات من قاعدة البيانات**

### 📋 **الاستعلام الأساسي** (في `index()` method):
```php
$serviceOrders = DB::table('service_orders')
    ->join('users as order_users', 'service_orders.user_id', '=', 'order_users.id')
    ->join('services', 'service_orders.service_id', '=', 'services.id')
    ->where('order_users.agent_id', '=', $currentUser->id)
    ->select('service_orders.*', 'order_users.name as user_name', 'services.name as service_name')
    ->orderBy('service_orders.created_at', 'desc')
    ->get();
```

### 🎯 **الاستعلام المفلتر** (في `getOrder($type)` method):
```php
$serviceOrders = DB::table('service_orders')
    ->join('users as order_users', 'service_orders.user_id', '=', 'order_users.id')
    ->join('services', 'service_orders.service_id', '=', 'services.id')
    ->join('service_categories', 'services.section_id', '=', 'service_categories.id')
    ->where('order_users.agent_id', '=', $currentUser->id)
    ->where('service_categories.type', '=', $type)
    ->select('service_orders.*', 'order_users.name as user_name', 'services.name as service_name')
    ->orderBy('service_orders.created_at', 'desc')
    ->get();
```

## 🏗️ **الجداول المشاركة في الاستعلام**

### 1. **الجدول الرئيسي**: `service_orders`
```sql
-- يحتوي على:
- id, user_id, service_id, price, status
- ime, username, email, password, count
- kimlik, line_photo, mobile, last_mobile
- reject_reason, provider_reference
- created_at, updated_at, uuid
```

### 2. **جدول المستخدمين**: `users` (as order_users)
```sql
-- يُستخدم للحصول على:
- name as user_name (اسم صاحب الطلب)
- agent_id (للفلترة حسب الوكيل)
```

### 3. **جدول الخدمات**: `services`
```sql
-- يُستخدم للحصول على:
- name as service_name (اسم الخدمة)
- section_id (للربط مع الفئات)
```

### 4. **جدول فئات الخدمات**: `service_categories` (في getOrder فقط)
```sql
-- يُستخدم للفلترة حسب:
- type (نوع الفئة)
```

## 🔐 **منطق الفلترة والأمان**

### 🛡️ **فلترة حسب الوكيل**:
```php
->where('order_users.agent_id', '=', $currentUser->id)
```
**المعنى**: يعرض فقط الطلبات الخاصة بالمستخدمين التابعين للوكيل المسجل دخوله حالياً

### 📊 **الترتيب**:
```php
->orderBy('service_orders.created_at', 'desc')
```
**المعنى**: ترتيب الطلبات من الأحدث إلى الأقدم

## 🌐 **مصادر إنشاء الطلبات**

### 1. **من الـ API** (للتطبيق الأمامي):
```php
ApiServiceOrderController::store()
المسار: /app/Http/Controllers/ApiServiceOrderController.php
```

### 2. **من الواجهة الإدارية** (إن وجدت):
```php
ServiceOrderController::store() // إذا كان موجود
```

## 📱 **الحقول المعروضة في الـ View**

### 🔍 **البيانات المعروضة**:
```php
- $serviceOrder->user_name        // اسم صاحب الطلب
- $serviceOrder->service_name     // اسم الخدمة
- $serviceOrder->created_at       // تاريخ الطلب
- $serviceOrder->price           // السعر
- $serviceOrder->reject_reason   // سبب الرفض
- $serviceOrder->status          // الحالة (1=انتظار, 2=نجح, 3=ملغي)
```

### 🔧 **العمليات المتاحة**:
```php
- عرض التفاصيل (viewModal)
- حذف الطلب (deleteModal)
- رفض الطلب (rejectModal) - للحالة 1 فقط
- قبول الطلب (accept route) - للحالة 1 فقط
```

## 🎛️ **حالات الطلب**

### 📊 **الحالات المختلفة**:
```php
1 => "قيد الانتظار"    // pending
2 => "تمت بنجاح"       // completed
3 => "الغاء"           // cancelled/rejected
```

## 🔄 **تدفق البيانات**

### 📥 **إنشاء الطلب**:
```
1. المستخدم يرسل طلب عبر API
2. ApiServiceOrderController::store() ينشئ السجل
3. يتم خصم الرصيد من المستخدم
4. يتم إرسال إشعار للوكيل
```

### 📋 **عرض الطلبات**:
```
1. الوكيل يدخل لوحة التحكم
2. ServiceOrderController::index() يجلب الطلبات
3. يتم فلترة الطلبات حسب agent_id
4. عرض البيانات في الـ View
```

### ⚡ **معالجة الطلب**:
```
1. الوكيل يقبل/يرفض الطلب
2. تحديث حالة الطلب في قاعدة البيانات
3. إرسال إشعار للمستخدم
4. في حالة الرفض: إرجاع الرصيد
```

## 🎯 **الخلاصة**

### ✅ **مصدر البيانات**:
- **الجدول الرئيسي**: `service_orders`
- **الـ Controller**: `ServiceOrderController`
- **الـ Method**: `index()` أو `getOrder($type)`
- **الفلترة**: حسب `agent_id` للمستخدم المسجل دخوله

### 🔗 **الـ API المرتبط**:
- **إنشاء الطلبات**: `ApiServiceOrderController::store()`
- **معالجة الطلبات**: `ServiceOrderController::accept/reject()`

### 📊 **البيانات المعروضة**:
- طلبات الخدمات الخاصة بعملاء الوكيل المسجل دخوله
- مع تفاصيل المستخدم والخدمة والحالة
- مرتبة من الأحدث إلى الأقدم
