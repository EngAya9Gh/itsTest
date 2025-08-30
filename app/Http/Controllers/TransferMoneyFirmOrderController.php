<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\TransferMoneyFirmOrder;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Currency;
use App\Notifications\RecordAddedNotification;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Auth;
use App\Utils\ProfitCalculationService;
  
class TransferMoneyFirmOrderController extends Controller
{ protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    } 
    public function index()
    {
          // التحقق من تسجيل المستخدم
    $currentAgentId = auth()->user()->id;

    // جلب الطلبات مع التحقق من أن agent_id يطابق المستخدم الحالي
    $transferMoneyFirmOrders = DB::table('transfer_money_firm_orders')
        ->join('users', 'transfer_money_firm_orders.user_id', '=', 'users.id')
        ->join('transfer_money_firms', 'transfer_money_firm_orders.transfer_money_firm_id', '=', 'transfer_money_firms.id')
        ->where('users.agent_id', $currentAgentId) // إضافة شرط agent_id
        ->select('transfer_money_firm_orders.*', 'users.name as user_name', 'transfer_money_firms.name as transfer_money_firm_name')
        ->orderBy('transfer_money_firm_orders.id', 'desc') // ترتيب النتائج
        ->paginate(500); // استخدام التصفح
         $baseCurrency=Currency::where('is_base',true)->first();
         $currencies = Currency::all()->keyBy('name'); // نحولها إلى associative array للاستدعاء السريع

        foreach ($transferMoneyFirmOrders as $order) { 
        $orderCurrencyCode = $order->currency ?? $baseCurrency->name;

        // استخدم الدالة لتحويل السعر إلى العملة الأساسية
        $convertedAmount =   $this->profitService->convertToBasicPrice($order->value, $orderCurrencyCode);

        // حفظ القيمة الجديدة في الموديل وفي قاعدة البيانات
        $order->amount = $convertedAmount;

        \DB::table('transfer_money_firm_orders')
            ->where('id', $order->id)
            ->update(['amount' => $convertedAmount]);
        }

      return view('backend.transferMoneyFirm.transferMoneyFirmOrders.index', compact('transferMoneyFirmOrders','baseCurrency'));
    }

     public function paymentDone(Request $request, $id)
    {
        $order = TransferMoneyFirmOrder::findOrFail($id);
        $order->remain_amount = $order->remain_amount  - $request->remain_amount;
        $order->save();

        return back()->with('success', 'تمت العملية بنجاح');
    }


    public function reject( $id)
    {   $agent=Auth::user();
        $order= TransferMoneyFirmOrder::findOrFail($id);
         $order->status="الغاء";
        $order->save();
             $customer=User::where('id',$order->user_id)->first();

       Transaction::create([
            'from_user_id' => $agent->id,
            'to_user_id' =>$customer->id,
            'order_id'=> $order->id,
            'amount' => $order->amount,
            'payment_done'=>-1
        
        ]);
          return back()->with('message', 'تمت العملية  بنجاح');
   
    }
    public function debt( $id)
    {   $agent=Auth::user();
        $order= TransferMoneyFirmOrder::findOrFail($id);
        $customer=User::where('id',$order->user_id)->first();
        if($agent->balance>=floatval($order->value))
       { $customer->balance+=floatval($order->value);
        $customer->save();
        $agent->balance-=floatval($order->amount);
        $agent->save();
        $order->status="دين";
        $order->save();
      Transaction::create([
            'from_user_id' => $agent->id,
            'to_user_id' =>$customer->id,
            'order_id'=> $order->id,
            'amount' => floatval($order->value),
            'remain_amount' => floatval($order->value),
            'base_amount'=> $this->profitService->convertToBasicPrice($order->value,  $order->currency),
            'rate_at_time'=> $this->profitService->getRateAtTime( $order->currency),
            'currency' => $order->currency,
            'base_currency_id' => Currency::where('is_base',1)->first()->id,
            'currency_id'=>  $targetCurrency = Currency::Where('name', $order->currency)->first()->id,
             'payment_done'=>0
        
        ]);
     // $agent = User::find($user->agent_id);
                $requestDetails = [ 
                    'request_id' => $order->id,
                    'status' => 1,
                    'message' => ' تم قبول كدين طلب اضافة الرصيد من قبل  ' . $agent->name . ' (  ' . $order->value.$order->currency. ' )  '
                ];

                // إرسال الإشعار
                $customer->notify(new RecordAddedNotification($requestDetails));
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    return back()->with('message', 'الرصيد غير كافي   ');
    }
    public function accept( $id)
    {    
        $agent=Auth::user();
        $order= TransferMoneyFirmOrder::findOrFail($id);
        $customer=User::where('id',$order->user_id)->first();
        if($agent->balance>=floatval($order->value))
       { $customer->balance+=floatval($order->value);
        $customer->save();
        $agent->balance-=floatval($order->amount);
        $agent->save();
        $order->status="مقبول";
        $order->save();
       Transaction::create([
            'from_user_id' => $agent->id,
            'to_user_id' =>$customer->id,
            'order_id'=> $order->id,
            'amount' => floatval($order->value),
            'base_amount'=> $this->profitService->convertToBasicPrice($order->value,  $order->currency),
            'rate_at_time'=> $this->profitService->getRateAtTime( $order->currency),
            'currency' => $order->currency,
            'base_currency_id' => Currency::where('is_base',1)->first()->id,
            'currency_id'=>  $targetCurrency = Currency::Where('name', $order->currency)->first()->id,
             'payment_done'=>1
        
        ]);
         $requestDetails = [ 
                    'request_id' => $order->id,
                    'status' => 1,
                    'message' => ' تم قبول طلب اضافة الرصيد   ' . $agent->name . ' (  ' . $order->value.$order->currency.' )  '
                ];

                // إرسال الإشعار
                $customer->notify(new RecordAddedNotification($requestDetails));
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    return back()->with('message', 'الرصيد غير كافي   ');
   }
    
   public function store(Request $request)
    {
        $input = $request->all();
       
        TransferMoneyFirmOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function show( $id)
    {
    }

    public function edit( $id)
    {
    }

    public function update(Request $request,  $id)
    {
        $transferMoneyFirmOrder = TransferMoneyFirmOrder::findOrFail($id);
        $input = $request->all();
        
        $transferMoneyFirmOrder->update( $input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy( $id)
    {
        $transferMoneyFirmOrder= TransferMoneyFirmOrder::findOrFail($id);
        $transferMoneyFirmOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
