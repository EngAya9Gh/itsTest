<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RecordAddedNotification extends Notification
{
    use Queueable;
 protected $requestDetails;

    // تمرير التفاصيل عند إنشاء الإشعار
    public function __construct($requestDetails)
    {
        $this->requestDetails = $requestDetails;
    }

    public function via($notifiable)
    {
        return ['database'];  // سنستخدم قاعدة البيانات لتخزين الإشعار
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->requestDetails['message'],  // إرجاع الرسالة التي تم إرسالها
            'request_id' => $this->requestDetails['request_id'],
            'status' => $this->requestDetails['status'],
        ];
    }
}
