<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TurkificationOrder;

use Illuminate\Support\Facades\Auth;
class ApiTurkificationOrderController extends Controller
{
    public function store(Request $request)
    {
        $input = $request->all();

       $order= TurkificationOrder::create($input);
       
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
                $user->balance=$user->balance-$order->price;
                $user->save();
                $order->status="قيد المراجعة";
                $order->save();
                return "تم تسجيل طلبك";

             }
             $order->status="مرفوض";
             $order->save();
             return "فشل عملية الشراء:الرصيد غير كافي   ";
         }
         return "فشل عملية الشراء:الرصيد غير كافي   ";
    }
}
