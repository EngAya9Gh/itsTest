<?php

namespace App\Http\Controllers;

use App\Models\TransferMoneyFirm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiTransferMoneyFirmController extends Controller
{
    public function index()
    {
$transferMoneyFirms = DB::table('transfer_money_firms')
    ->select('*')
    ->where('id', '!=', 0)  // إضافة فاصلة بين select و where
    ->orderBy('id', 'desc')
    ->paginate(500);        foreach ($transferMoneyFirms as $trans) {
            $trans->image_url = asset('assets/images/transferMoneyFirm/' . $trans->image);  // إنشاء رابط للصورة
          //  $trans->save();
        }
        
        return response()->json(['companies'=>$transferMoneyFirms]);
    }

    public function show($id)
    {
       $transferMoneyFirm = TransferMoneyFirm::findOrFail($id);
       return response()->json(['transferMoneyFirm'=>$transferMoneyFirm]);
    }
}
