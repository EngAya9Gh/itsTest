<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcardOrder;
use App\Models\Ecard;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class EcardOrderController extends Controller
{  protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    { 
        $currentUser = auth()->user();
        $ecardOrders = DB::table('ecard_orders')
        ->join('users', 'ecard_orders.user_id', '=', 'users.id')
        ->join('ecards', 'ecard_orders.ecard_id', '=', 'ecards.id')
        ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
        ->select('ecard_orders.*', 'users.name as user_name', 'ecards.name as ecard_name')
        ->get();
        return view('backend.ecard.ecardOrders.index', compact('ecardOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        EcardOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }
    public function reject( $id)
    {
        $order= EcardOrder::findOrFail($id);
        $order->status="مرفوض";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $order= EcardOrder::findOrFail($id);
        $order->status="مقبول";
        $order->save();
        $this->profitService->calculateProfit($order, Ecard::class);

        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function update(Request $request, $id)
    {
        $ecardOrder = EcardOrder::findOrFail($id);
        $input = $request->all();
        $ecardOrder->update($input);
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy($id)
    { 
        $ecardOrder= EcardOrder::findOrFail($id);
        
        $ecardOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
