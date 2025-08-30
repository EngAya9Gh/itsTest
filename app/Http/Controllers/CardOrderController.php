<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\CardOrder;
use App\Models\Card;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class CardOrderController extends Controller
{   protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
        $currentUser = auth()->user(); // الحصول على المستخدم الحالي
    
        $cardOrders = DB::table('card_orders')
            ->join('users', 'card_orders.user_id', '=', 'users.id')
            ->join('cards', 'card_orders.card_id', '=', 'cards.id')
            ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
            ->select('card_orders.*', 'users.name as user_name', 'cards.name as card_name')
            ->get();
        return view('backend.card.cardOrders.index', compact('cardOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        CardOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }
    public function reject( $id)
    {
        $order= CardOrder::findOrFail($id);
        $order->status="مرفوض";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $order= CardOrder::findOrFail($id);
        $order->status="مقبول";
        $order->save();
        $this->profitService->calculateProfit($order, Card::class);
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function update(Request $request,  $id)
    {
        $cardOrder = CardOrder::findOrFail($id);
        $input = $request->all();
        $cardOrder->update($input);
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy( $id)
    {
        $cardOrder= CardOrder::findOrFail($id);
        $cardOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
