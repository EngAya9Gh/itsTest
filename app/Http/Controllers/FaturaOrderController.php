<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaturaOrder;
use Illuminate\Support\Facades\DB;
use App\Models\Fatura;
use App\Models\User;
use App\Utils\ProfitCalculationService;

class FaturaOrderController extends Controller
{ protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index($type)
    {   $currentUser = auth()->user();
       $serviceOrders = DB::table('fatura_orders')
    ->join('users', 'fatura_orders.user_id', '=', 'users.id')
    ->join('faturas', 'fatura_orders.fatura_id', '=', 'faturas.id')
    ->join('fatura_sections', 'faturas.section_id', '=', 'fatura_sections.id')
    ->where('fatura_sections.type', '=', $type) // إضافة شرط type
    ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
    ->select('fatura_orders.*', 'users.name as user_name', 'faturas.name as service_name')
    ->orderBy('fatura_orders.created_at', 'desc') // ترتيب حسب created_at من الأحدث إلى الأقدم
    ->get();
     
        return view('backend.fatura.faturaOrders.index', compact('serviceOrders'));
    }
 public function reject( $id,Request $request)
    {
        $order= FaturaOrder::findOrFail($id);
        $user=User::findOrFail($order->user_id);
        $order->status=3;
        $order->reject_reason=$request->reject_reason;
        $order->save();
        $user->balance=$user->balance+$order->price;
        $user->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
	public function accept( $id)
    {
        $order= FaturaOrder::findOrFail($id);
        $service=Fatura::find($order->service_id);
        $user=User::findOrFail($order->user_id);
        $order->status=2;
        $order->save();
        $this->profitService->calculateProfit($order, Fatura::class,$order->fatura_id);


               return back()->with('message', 'تمت العملية  بنجاح');
      }
 

}
