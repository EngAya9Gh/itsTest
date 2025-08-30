<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransferMoneyFirmOrder;
class TransactionController extends Controller
{
   
    public function getUserTransactions($userId)
    {
        // الحصول على كافة العمليات المرتبطة بالمستخدم كمرسل
        $transactions = Transaction::where('from_user_id', $userId)
            ->with(['receiver', 'sender']) // جلب بيانات المرسل والمستلم
            ->get();
            return view('backend.users.transferRequest',compact('transactions'));
      
    }  
    public function getUserTransactionsDone($userId)
    {
        // الحصول على كافة العمليات المرتبطة بالمستخدم كمرسل
        $transactions = Transaction::where('from_user_id', $userId)->where('payment_done','!=',0)
            ->with(['receiver', 'sender']) // جلب بيانات المرسل والمستلم
            ->get();
            return view('backend.users.transferRequest',compact('transactions'));
      
    }   

    public function getUserTransactionsDebts($userId)
    {
        // الحصول على كافة العمليات المرتبطة بالمستخدم كمرسل
        $transactions = Transaction::where('from_user_id', $userId)->where('payment_done',0)
            ->with(['receiver', 'sender']) // جلب بيانات المرسل والمستلم
            ->get();
     // dd($transactions);
            return view('backend.users.transferRequest',compact('transactions'));
      
    }  
    
    public function getUserTransactionsMyDebts($userId)
    {
        // الحصول على كافة العمليات المرتبطة بالمستخدم كمرسل
        $transactions = Transaction::where('to_user_id', $userId)->where('payment_done',0)
            ->with(['receiver', 'sender']) // جلب بيانات المرسل والمستلم
            ->get();
            return view('backend.users.transferRequest',compact('transactions'));
      
    }  
public function setPaymentDone($id)
{
    

    $transactions = Transaction::findOrFail($id);
    $transactions->payment_done=1;
    $transactions->save();
   
  $order=TransferMoneyFirmOrder::findOrFail($transactions->order_id);
  $order->status="مقبول";
  $order->save();
  
  return back()->with('message',"تمت العملية بنجاح");
}
  public function setPartialPaymentDone(Request $request,$id)
{
    

    $transactions = Transaction::findOrFail($id);
    $transactions->remain_amount=$transactions->remain_amount-$request->amount;
       $transactions->save();
    if($transactions->remain_amount==0)
      { $transactions->payment_done=1;
        $transactions->save();
        $order=TransferMoneyFirmOrder::findOrFail($transactions->order_id);
        $order->status="مقبول";
        $order->save();
    }
  return back()->with('message',"تمت العملية بنجاح");
}
}
