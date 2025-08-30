<?php


namespace App\Http\Controllers;

  use Illuminate\Support\Facades\Notification;
use App\Models\Notification as NotificationModel;
use Illuminate\Http\Request;
use App\Notifications\RecordAddedNotification;

class NotificationController extends Controller
{
    public function getUnreadNotifications()
    {
        $admin = auth()->user();
        if ($admin) {
            return response()->json($admin->unreadNotifications);
    
        return response()->json([]);
    }
      
      
}
      public function getNotifications()
    {
        $admin = auth()->user();
            if ($admin) {
            return response()->json($admin->notifications);
    
        return response()->json([]);
        }
        }
      


    public function markAsRead($id)
    {
        // استرجاع الإشعار باستخدام Eloquent
        $notification = NotificationModel::findOrFail($id);

        // تحديث تاريخ القراءة
        $notification->read_at = now();
        $notification->save();

        return response()->json(['message' => 'Notification marked as read']);
    }
  
}
