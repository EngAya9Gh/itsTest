<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use App\Models\Service;
use App\Models\Vip;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Utils\ProfitCalculationService;
use App\Notifications\RecordAddedNotification;

class ApiServiceOrderController extends Controller
{   protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    } 
    public function store(Request $request)
    {  
        $input = $request->all();
          

        $order=ServiceOrder::create($input);
       
        $service = Service::find($order->service_id);  // جلب الخدمة بناءً على 
        $user=Auth::user();

        $requestDetails = [ 
          'request_id' => $order->id,
          'status' => 1,
          'message' => ' طلب ' . $service->name . ' من  ' . $user->name
        ];
        // عند إضافة طلب جديد
        $user = User::find($user->agent_id);  // أو تحديد المستخدم المستهدف للإشعار

// إرسال الإشعار
        $user->notify(new RecordAddedNotification($requestDetails)); 
        $result=$this->operation($order);

        return response()->json(['message'=>$result]);
    }
    public function operation($order)
    {
        $user=Auth::user();
        if($user )
         {
            if($user->balance>=$order->price)
             {
                $user->balance =$user->balance-$order->price;
                $user->save();
                $order->status=1;
                $order->save();
            
                return "تم تسجيل طلبك  ";
             }
             $order->status=3;
             $order->reject_reason="فشل عملية الشراء:الرصيد غير كافي ";
             $order->save();
             $order->delete();
             return "فشل عملية الشراء:الرصيد غير كافي   ";
         }
         return "فشل عملية الشراء:الرصيد غير كافي   ";
    }



}
