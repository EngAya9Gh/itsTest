<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweetcell;
use Illuminate\Support\Facades\DB;
use App\Models\Vip;
class ApiGameController extends Controller
{
    public function index()
    {
       $games=DB::table('tweetcell')->select('*')->orderBy('id', 'desc')->paginate(500);
       foreach ($games as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $trans->image);  // إنشاء رابط للصورة
       
      }
       return response()->json(['games'=>$games ]);
    }
    public function show($id)
    {
       $game = Tweetcell::findOrFail($id);
       return response()->json(['game'=>$game ]);
    }
}
