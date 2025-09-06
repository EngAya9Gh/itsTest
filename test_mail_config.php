<?php

/**
 * اختبار إعدادات البريد الإلكتروني
 * تشغيل: php artisan tinker
 * ثم: include 'test_mail_config.php'
 */

echo "=== اختبار إعدادات البريد الإلكتروني ===\n\n";

// قراءة إعدادات البريد من .env
$mailDriver = env('MAIL_MAILER', 'smtp');
$mailHost = env('MAIL_HOST', 'localhost');
$mailPort = env('MAIL_PORT', 587);
$mailUsername = env('MAIL_USERNAME');
$mailFromAddress = env('MAIL_FROM_ADDRESS');

echo "الإعدادات الحالية:\n";
echo "- Driver: $mailDriver\n";
echo "- Host: $mailHost\n";
echo "- Port: $mailPort\n";
echo "- Username: $mailUsername\n";
echo "- From Address: $mailFromAddress\n\n";

if ($mailDriver === 'log') {
    echo "✅ تم تعيين البريد الإلكتروني على 'log' driver\n";
    echo "سيتم حفظ البريد في: storage/logs/laravel.log\n\n";

    echo "لاختبار النظام:\n";
    echo "1. اذهب إلى /forgot-password\n";
    echo "2. أدخل أي بريد إلكتروني موجود في قاعدة البيانات\n";
    echo "3. تحقق من ملف storage/logs/laravel.log\n";
    echo "4. ستجد محتوى البريد الإلكتروني والرابط هناك\n\n";
} else {
    echo "⚠️  البريد الإلكتروني معين على SMTP\n";
    echo "إذا كانت هناك مشاكل في المصادقة:\n\n";

    echo "حلول مقترحة:\n";
    echo "1. تحقق من صحة اسم المستخدم وكلمة المرور\n";
    echo "2. تأكد من تفعيل 'Less secure app access' (Gmail)\n";
    echo "3. استخدم App Password بدلاً من كلمة المرور العادية\n";
    echo "4. تحقق من إعدادات الـ firewall\n";
    echo "5. جرب port 465 مع SSL بدلاً من 587 مع TLS\n\n";

    echo "للتبديل إلى log driver مؤقتاً:\n";
    echo "غيّر MAIL_MAILER=smtp إلى MAIL_MAILER=log في ملف .env\n\n";
}

echo "=== نصائح لإصلاح مشاكل SMTP ===\n\n";

echo "1. إعدادات Gmail:\n";
echo "   MAIL_HOST=smtp.gmail.com\n";
echo "   MAIL_PORT=587\n";
echo "   MAIL_ENCRYPTION=tls\n";
echo "   استخدم App Password من Google Account Settings\n\n";

echo "2. إعدادات Hostinger:\n";
echo "   MAIL_HOST=smtp.hostinger.com\n";
echo "   MAIL_PORT=587 أو 465\n";
echo "   MAIL_ENCRYPTION=tls أو ssl\n";
echo "   تأكد من أن البريد الإلكتروني موجود في cPanel\n\n";

echo "3. للاختبار المحلي:\n";
echo "   استخدم Mailtrap.io أو MailHog\n";
echo "   أو استخدم log driver\n\n";

echo "4. اختبار الاتصال:\n";
echo "   telnet smtp.hostinger.com 587\n";
echo "   إذا لم يتصل، فهناك مشكلة في الشبكة\n\n";

echo "=== انتهى الاختبار ===\n";
