<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FaturaSection;
use App\Models\Fatura;
use App\Models\Vip;
use App\Utils\ProfitCalculationService;

class ApiFaturaSectionController extends Controller
{
      protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $categories=DB::table('fatura_sections')->select('*')->where('status',1)->orderBy('id', 'desc')->paginate(500);

       foreach ($categories as $app) {
         $app->image_url = asset('assets/images/fatura/' . $app->image);  // إنشاء رابط للصورة
     }
     
       return response()->json(['faturaSections'=> $categories ]);
    }
  
   
     
     

    public function getFaturas (string $section_id)
    {
       $cat = FaturaSection::where('id',$section_id)->where('status',1)->first();
       $faturas = $cat->faturas()->where('status', 1)->get();

       foreach ($faturas as $app) {
         $app->image_url = asset('assets/images/fatura/' . $app->image);  // إنشاء رابط للصورة
           
        $user=Auth::user();
         if($user && $user->role!=4)
          $app->sale_price = $app->price;
         else
           $app->sale_price =0;
        $app->price = $this->profitService->getPrice($app);    // إنشاء رابطe($app);
     }
       return response()->json(['faturas'=> $faturas ]);
    }

  
}
