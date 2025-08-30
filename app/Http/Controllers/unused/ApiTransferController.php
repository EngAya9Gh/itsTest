<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransferOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApiTransferController extends Controller
{
    public function index()
    {
        $transferOrder=DB::table('transfer_orders')->select('*')->orderBy('id', 'desc')->get();
        foreach ($apps as $app) {
            $app->image_url = asset('assets/images/transfers/' . $trans->image);  // إنشاء رابط للصورة
        
        }
        return response()->json(['transferOrder'=>$transferMoneyFirms]);
    }


    public function show($id)
    {
       $transferOrder = TransferOrder::findOrFail($id);
       return response()->json(['transferorder'=>$transferOrder]);
    }
}
