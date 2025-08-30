<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\TransferOrder;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class ApiTransferOrderController extends Controller
{ protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function store(Request $request)
    {
        $input = $request->all();
      $order=  TransferOrder::create($input);
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
                $order->status="قيدالمراجعة";
                
          //      $this->profitService->calculateProfit($order, TransferOrder::class);
                $order->save();
                return "تم تسجيل طلبك  ";

             }
             $order->status="مرفوض";
             $order->save();
             return "فشل عملية الشراء:الرصيد غير كافي   ";
         }
         return "فشل عملية الشراء:الرصيد غير كافي   ";
    }



    
}
 