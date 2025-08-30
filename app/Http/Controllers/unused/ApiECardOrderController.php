<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcardOrder;
use App\Models\Ecard;
use App\Models\Vip;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Utils\ProfitCalculationService;
class ApiECardOrderController extends Controller
{  protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
     public function store(Request $request)
    {  
       
        $input = $request->all();
     
   
        $order=EcardOrder::create($input);
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
              //  $this->profitService->calculateProfit($order, Ecard::class);

                return "تم تسجيل طلبك  ";

             }
             $order->status="مرفوض";
             $order->save();
             return "فشل عملية الشراء:الرصيد غير كافي   ";
         }
         return "فشل عملية الشراء:الرصيد غير كافي   ";
    }
    
    public function calculateProfit($order)
    { $calculatedProfit=0;
       $user=Auth::user();
       $service=Ecard::findOrFail($order->ecard_id);
       $agent=User::find($user->agent_id);
       $vip=Vip::where('id',$agent->vip_id)->first();
       if ($agent->role == 2|| $agent->role == 3)  {
           $calculatedProfit=$service->price*$vip->commession_percent/100; 
       
       $order1 = EcardOrder::find($order->id); // الحصول على الطلب من نموذج Program
          $order1->profits()->create([
              'user_id' => $agent->id,
              'profit_amount' => $calculatedProfit,
          ]);
        }
    }
}
