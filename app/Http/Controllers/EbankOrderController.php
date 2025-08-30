<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\EbankOrder;
use App\Models\Ebank;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class EbankOrderController extends Controller
{ protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {   $currentUser = auth()->user();
        $ebankOrders = DB::table('ebank_orders')
        ->join('users', 'ebank_orders.user_id', '=', 'users.id')
        ->join('ebanks', 'ebank_orders.ebank_id', '=', 'ebanks.id')
        ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
        ->select('ebank_orders.*', 'users.name as user_name', 'ebanks.name as ebank_name')
        ->get();
        return view('backend.ebank.ebankOrders.index', compact('ebankOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        EbankOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function reject( $id)
    {
        $order= EbankOrder::findOrFail($id);
        $order->status="مرفوض";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $order= EbankOrder::findOrFail($id);
        $order->status="مقبول";
        $order->save();
        $this->profitService->calculateProfit($order, Ebank::class);

        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function update(Request $request,  $id)
    {
        $ebankOrder = EbankOrder::findOrFail($id);
        $input = $request->all();
        $ebankOrder->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy( $id)
    {
        $ebankOrder= EbankOrder::findOrFail($id);
        $ebankOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
