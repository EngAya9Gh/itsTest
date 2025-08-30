<?php

namespace App\Http\Controllers;

use App\Models\TurkificationOrder;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class TurkificationOrderController extends Controller
{  protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $turkificationOrders=DB::table('turkification_orders')->select('*')->orderBy('id', 'desc')->paginate(500);
       return view('backend.turkification.turkificationOrders.index', compact('turkificationOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        TurkificationOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function reject( $id)
    {
        $order= TurkificationOrder::findOrFail($id);
        $order->status="مرفوض";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $order= TurkificationOrder::findOrFail($id);
        $order->status="مقبول";
        $order->save();
        $this->profitService->calculateProfit($order, TurkificationOrder::class);
    
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function update(Request $request, $id)
    {
        $turkificationOrder = TurkificationOrder::findOrFail($id);
        $input = $request->all();
        $turkificationOrder->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');

    }

    public function destroy(string $id)
    {
        $turkificationOrder= TurkificationOrder::findOrFail($id);
        $turkificationOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
