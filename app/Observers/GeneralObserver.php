<?php

namespace App\Observers;

use App\Notifications\RecordAddedNotification;
use App\Models\User;

class GeneralObserver
{
    public function created($model)
    {
         // العثور على المسؤولين
     /*   $admins = User::where('role', 1)->get();

        // الحصول على الخدمة (tweetcell) المرتبطة بالطلب
        $tweetcell = $order->tweetcell;

        // الحصول على القسم (tweetcellSection) المرتبط بالخدمة
        $tweetcellSection = $tweetcell->tweetcellSection;

        // تحقق من نوع القسم
        $message = '';
        switch ($tweetcellSection->type) {
            case 1:
                $message = "تم إضافة لعبة جديدة";
                break;
            case 2:
                $message = "تم إضافة تطبيق جديد";
                break;
            case 3:
                $message = "تم إضافة بطاقات رقمية جديدة";
                break;
            default:
                $message = "تم إضافة طلب جديد";
                break;
        }

        // إرسال الإشعار إلى جميع المسؤولين
        foreach ($admins as $admin) {
            $admin->notify(new RecordAddedNotification($message));
        }*/
    }

    private function translateTableName($tableName)
    {
        // ترجمة الجداول
        $tableNamesInArabic = [
            'app_orders' => 'التطبيقات',
            'data_communication_orders' => 'اتصالات البيانات',
            'game_orders' => 'الألعاب',
            'ecard_orders' => 'البطاقات الإلكترونية',
            'program_orders' => 'البرامج',
            'card_orders' => 'بطاقاتنا',
            'transfer_orders' => 'نقل رصيد',
            'transfer_money_firm_orders' => 'اضافة رصيد',
        ];

        return $tableNamesInArabic[$tableName] ?? $tableName;
    }
}
