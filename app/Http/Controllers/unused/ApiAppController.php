<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweetcell;
use Illuminate\Support\Facades\DB;
use App\Models\Vip;

class ApiAppController extends Controller
{
   
    public function index()
    {
       $apps=DB::table('tweetcells')->select('*')->orderBy('id', 'desc')->paginate(500);
  
       foreach ($apps as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
     
     }
       return response()->json(['apps'=>$apps ]);
    }
    public function show($id)
    {
       $app = Tweetcell::findOrFail($id);

       return response()->json(['app'=>$app ]);
    }
    
}
