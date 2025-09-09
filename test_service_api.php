<?php

require_once 'vendor/autoload.php';

use App\Models\Service;
use App\Models\ServiceCategories;

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== اختبار جلب الخدمات مع أصنافها ===\n\n";

// اختبار جلب خدمة واحدة مع الصنف
echo "1. اختبار جلب خدمة واحدة مع الصنف:\n";
$service = Service::with('category')->where('status', 1)->first();

if ($service) {
    echo "ID: " . $service->id . "\n";
    echo "Name: " . $service->name . "\n";
    echo "Section ID: " . $service->section_id . "\n";
    echo "Type: " . $service->type . "\n";

    if ($service->category) {
        echo "Category Name: " . $service->category->name . "\n";
        echo "Category Description: " . $service->category->description . "\n";
    } else {
        echo "Category: لا يوجد صنف مرتبط\n";
    }
} else {
    echo "لا توجد خدمات نشطة\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// اختبار جلب جميع الخدمات مع الأصناف
echo "2. اختبار جلب جميع الخدمات مع الأصناف:\n";
$services = Service::with('category')->where('status', 1)->limit(5)->get();

foreach ($services as $s) {
    echo "Service: " . $s->name . " | Category: " . ($s->category ? $s->category->name : 'غير محدد') . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// اختبار الأصناف المتاحة
echo "3. الأصناف المتاحة:\n";
$categories = ServiceCategories::where('status', 1)->get();

foreach ($categories as $cat) {
    echo "ID: " . $cat->id . " | Name: " . $cat->name . " | Services Count: " . $cat->services()->count() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
