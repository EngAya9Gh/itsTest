<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellOrder;
use Illuminate\Support\Facades\DB;
use App\Models\Tweetcell;
use App\Models\User;
use App\Utils\ProfitCalculationService;

class TweetcellOrderController extends Controller
{ protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index($type)
    {   $currentUser = auth()->user();
       $orders = DB::table('tweetcell_orders')
    ->join('users', 'tweetcell_orders.user_id', '=', 'users.id')
    ->join('tweetcells', 'tweetcell_orders.tweetcell_id', '=', 'tweetcells.id')
    ->join('tweetcell_sections', 'tweetcells.section_id', '=', 'tweetcell_sections.id')
    ->where('tweetcell_sections.type', '=', $type) // إضافة شرط type
    ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
    ->select('tweetcell_orders.*', 'users.name as user_name', 'tweetcells.name as service_name')
    ->orderBy('tweetcell_orders.created_at', 'desc') // ترتيب حسب created_at من الأحدث إلى الأقدم
    ->get();
     
        return view('backend.tweetcell.tweetcellOrders.index', compact('orders'));
    }
   public function reject( $id ,Request $request)
    {
        $order= TweetcellOrder::findOrFail($id);
        $order->status="الغاء";
        $order->reject_reason = $request->reject_reason;
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
public function accept( $id)
    {
        $order= TweetcellOrder::findOrFail($id);
        $service=Tweetcell::find($order->service_id);
        $user=User::findOrFail($order->user_id);

      if($user->balance>=$service->price)
          {  $user->balance=$user->balance-$service->price;
            $user->save();
            $order->status="مقبول";
            $order->save();
            $this->profitService->calculateProfit($order, Tweetcell::class,$order->service_id);


               return back()->with('message', 'تمت العملية  بنجاح');
      }
  else
    return back()->with('message', 'رصيد الزبون غير كاف');
    
    }

}
