<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use App\Models\TransferMoneyFirmOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Notifications\RecordAddedNotification;

class ApiTransferMoneyFirmOrderController extends Controller
{
    


    public function store(Request $request)
    {  
        $input = $request->all();
       
       $order= TransferMoneyFirmOrder::create($input);
        $user=Auth::user();

        $requestDetails = [ 
          'request_id' => $order->id,
          'status' => 1,
          'message' => 'طلب تعبئة رصيد '. $user->name
        ];
        // عند إضافة طلب جديد
        $user = User::find($user->agent_id);  // أو تحديد المستخدم المستهدف للإشعار

// إرسال الإشعار
$user->notify(new RecordAddedNotification($requestDetails)); 
        
    return response()->json(['message' => '  جاري العمل على طلبك']);
        
    }
  

 public function myPayments($id, Request $request)
{
    // الحصول على التواريخ من المعلمات (startDate, endDate)
    $startDate = $request->query('startDate');
    $endDate = $request->query('endDate');

    // استعلام جلب الدفعات بدون استخدام الكاش
    $query = DB::table('transfer_money_firm_orders')
        ->join('transfer_money_firms', 'transfer_money_firm_orders.transfer_money_firm_id', '=', 'transfer_money_firms.id')
        ->join('users', 'transfer_money_firm_orders.user_id', '=', 'users.id')
        ->leftJoin('transactions', 'transactions.order_id', '=', 'transfer_money_firm_orders.id') // الربط مع جدول المعاملات
        ->select(
            'transfer_money_firm_orders.id as order_id',
            'transfer_money_firm_orders.sender',
            'transfer_money_firm_orders.value',
            'transfer_money_firm_orders.currency',
            'transfer_money_firm_orders.created_at',
            'transfer_money_firm_orders.status',
            'transactions.remain_amount',  
            'transfer_money_firms.name as firm_name',
            'users.name as user_name'
        )
        ->where('users.id', $id);

    // إضافة شرط التصفية حسب startDate إذا كان موجودًا
    if ($startDate) {
        $query->whereDate('transfer_money_firm_orders.created_at', '>=', $startDate);
    }

    // إضافة شرط التصفية حسب endDate إذا كان موجودًا
    if ($endDate) {
        $query->whereDate('transfer_money_firm_orders.created_at', '<=', $endDate);
    }

    // جلب البيانات بعد تطبيق التصفية
    $transferMoneyOrders = $query->get();

    // إعادة النتائج كـ JSON
    return response()->json(['orders' => $transferMoneyOrders]);
}




}
