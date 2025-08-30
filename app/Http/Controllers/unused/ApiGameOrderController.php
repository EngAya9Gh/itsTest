<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameOrder;
use App\Models\Game;
use App\Models\Vip;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Utils\ProfitCalculationService;
class ApiGameOrderController extends Controller
{   protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
   public function test()
    {  
     
   
     $data = [
            'kod' => "5534060015",
            'sifre' => "cayli831",
            'oyun' => 10,
            'kupur' => 20,
            'referans' =>1,
            'musteri_tel' =>   "999999",
            'oyuncu_bilgi' =>22,
        ];

        // إرسال الطلب إلى الرابط
        $response = Http::get('https://1kanal.pro/b2c-api/market/turktelekom/getPackages?phone=05534060015&password=cayli831&gsm=5389899096&category=tam');
      
 dd($response->body());

        // معالجة الاستجابة
        if ($response->successful()) {
            return  response()->json(['message'=>$response]);
        }else
        {
            return  response()->json(['message'=>' wrong  ']);
        }
   }

    public function store(Request $request)
    {   
      $input = $request->all();
        $user=Auth::user();
        $input['user_id']=$user->id;
      
        $order=GameOrder::create($input);
     
      
    
    $result=$this->operation($order, $input);
    return response()->json(['message'=>$result]);
    }

       
    public function operation($order, $input)
    {
        $user=Auth::user();
         

        if($user )
         {
            if($user->balance>=$order->price)
             {   
                $data = [
                  'kod' => "5534060015",
                  'sifre' => "cayli831",
                  'oyun' => $input['oyun_id'],
                  
                  'kupur' =>$input['count'],
                  'referans' =>$order->id,
                  'musteri_tel' => $user->mobile,
                  'oyuncu_bilgi' => $input['user_id_game'],
              ];
      		//  $response = Http::get('https://bayi.tweetpin.net/servis/pin_ekle.php', $data);
                $response="OK|16|77";
              if (Str::startsWith($response, 'OK')) { 
             
                $user->balance=$user->balance-$order->price;
                $user->save();
                $order->status="مقبول ";
                $order->save();
                  $this->profitService->calculateProfit($order, Game::class,$input['game_id']);
                   return "تمت عملية الشراء بنجاح  ";
              }  
              else {
                        return "   فشل عملية الشراء :خطا بالمعطيات  ";

              }

             }
          
             $order->status="مرفوض";
             $order->save();
             return "فشل عملية الشراء:الرصيد غير كافي   ";
         }
         return "فشل عملية الشراء   ";
    }
    
    public function calculateProfit($order)
    { $calculatedProfit=0;
       $user=Auth::user();
       $service=Game::findOrFail($order->game_id);
       $agent=User::find($user->agent_id);
       $vip=Vip::where('id',$agent->vip_id)->first();
       if ($agent->role == 2|| $agent->role == 3)  {
           $calculatedProfit=$service->price*$vip->commession_percent/100; 
       
       $order1 = GameOrder::find($order->id); // الحصول على الطلب من نموذج Program
          $order1->profits()->create([
              'user_id' => $agent->id,
              'profit_amount' => $calculatedProfit,
          ]);
        }
    }
}
