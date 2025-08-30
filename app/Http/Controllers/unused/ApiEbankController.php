<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ebank;


class ApiEbankController extends Controller
{
    public function index()
    { 
        $ebanks=DB::table('ebanks')->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($ebanks as $app) {
            $app->image_url = asset('assets/images/ebanks/' . $app->image);  // إنشاء رابط للصورة
        
        }
        return response()->json(['ebanks'=>$ebanks]);
    }
  
    public function show($id)
    {
       $ebank = Ebank::findOrFail($id);
       return response()->json(['ebank'=>$ebank ]);
    }
}
