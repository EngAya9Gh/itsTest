<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\AppOrder;
use App\Models\App;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class AppOrderController extends Controller
{   protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
      
        $currentUser = auth()->user();

        // جلب كافة طلبات المستخدمين الذين لهم نفس الـ agent_id الخاص بالمستخدم المسجل
        $appOrders = DB::table('app_orders')
            ->join('users', 'app_orders.user_id', '=', 'users.id')
            ->join('apps', 'app_orders.app_id', '=', 'apps.id')
            ->where('users.agent_id', '=', $currentUser->id) // إضافة الشرط هنا
            ->select('app_orders.*', 'users.name as user_name', 'apps.name as app_name')
            ->get();
        // Pass the data to the view
        return view('backend.app.appOrders.index', compact('appOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        AppOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function update(Request $request,  $id)
    {
        $appOrder = AppOrder::findOrFail($id);
        $input = $request->all();
       
        $appOrder->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }
    public function reject( $id)
    {
        $appOrder= AppOrder::findOrFail($id);
        $appOrder->status="مرفوض";
        $appOrder->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $appOrder= AppOrder::findOrFail($id);
        $appOrder->status="مقبول";
        $appOrder->save();
        $this->profitService->calculateProfit($order, App::class);
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function destroy( $id)
    {
        $appOrder= AppOrder::findOrFail($id);
        $appOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
