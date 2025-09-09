<?php

// اختبار بسيط للتحقق من بنية البيانات
echo "=== فحص بنية جداول الخدمات ===\n\n";

// محاكاة البيانات التي يجب أن تأتي من API
$mockService = [
    'id' => 1,
    'name' => 'خدمة تجريبية',
    'section_id' => 18,
    'type' => 1,
    'price' => 100,
    'category' => [
        'id' => 18,
        'name' => 'SERVER',
        'description' => 'Gsm Auth Tool'
    ]
];

echo "1. بنية البيانات المتوقعة للخدمة:\n";
echo json_encode($mockService, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "2. اختبار منطق تحديد API Endpoint:\n";
$categoryName = $mockService['category']['name'];

if ($categoryName === 'IMEI' || $categoryName === 'SERVER') {
    $endpoint = '/service/order/' . $mockService['id'];
    echo "Category: $categoryName → Endpoint: $endpoint (الأصلي)\n";
} else {
    $endpoint = '/service/orderits/' . $mockService['id'];
    echo "Category: $categoryName → Endpoint: $endpoint (الجديد)\n";
}

echo "\n3. اختبار مع أصناف مختلفة:\n";
$testCategories = ['IMEI', 'SERVER', 'GAMES', 'APPS', 'OTHER'];

foreach ($testCategories as $cat) {
    if ($cat === 'IMEI' || $cat === 'SERVER') {
        $endpoint = '/service/order/1';
        echo "Category: $cat → $endpoint (الأصلي)\n";
    } else {
        $endpoint = '/service/orderits/1';
        echo "Category: $cat → $endpoint (الجديد)\n";
    }
}

echo "\n=== انتهى الفحص ===\n";
