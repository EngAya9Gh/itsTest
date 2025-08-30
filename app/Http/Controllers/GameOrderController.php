<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameOrder;
use Illuminate\Support\Facades\DB;
use App\Models\Game;
use App\Utils\ProfitCalculationService;
class GameOrderController extends Controller
{protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {   $currentUser = auth()->user();
        $gameOrders = DB::table('game_orders')
        ->join('users', 'game_orders.user_id', '=', 'users.id')
        ->join('games', 'game_orders.game_id', '=', 'games.id')
        ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
        ->select('game_orders.*', 'users.name as user_name', 'games.name as game_name')
        ->get();
        return view('backend.game.gameOrders.index', compact('gameOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        GameOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function reject( $id)
    {
        $order= GameOrder::findOrFail($id);
        $order->status="مرفوض";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $order= GameOrder::findOrFail($id);
        $order->status="مقبول";
        $order->save();
        $this->profitService->calculateProfit($order, Game::class);

        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function update(Request $request,  $id)
    {
        $gameOrder = GameOrder::findOrFail($id);
        $input = $request->all();
        $gameOrder->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy( $id)
    {
        $gameOrder= GameOrder::findOrFail($id);
        $gameOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
