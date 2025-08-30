<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceCategories;
use App\Models\Service;
use App\Models\Vip;
use App\Utils\ProfitCalculationService;

class ApiServiceCategoryController extends Controller
{
      protected $profitService;

    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $categories=DB::table('service_categories')->select('*')->where('type',1)->where('status',1)->orderBy('id', 'desc')->paginate(500);

       foreach ($categories as $app) {
         $app->image_url = asset('assets/images/service/' . $app->image);  // إنشاء رابط للصورة
     }
     
       return response()->json(['categories'=> $categories ]);
    }
  
      public function getIts()
    {
       $categories=DB::table('service_categories')->select('*')->where('type',2)->where('status',1)->orderBy('id', 'desc')->paginate(500);

       foreach ($categories as $app) {
         $app->image_url = asset('assets/images/service/' . $app->image);  // إنشاء رابط للصورة
     }
     
       return response()->json(['categories'=> $categories ]);
    }

    public function getServices (string $section_id)
    {
       $cat = ServiceCategories::where('id',$section_id)->where('status',1)->first();
       $services = $cat->services()->where('status', 1)->get();

       foreach ($services as $app) {
         $app->image_url = asset('assets/images/service/' . $app->image);  // إنشاء رابط للصورة
           
        $user=Auth::user();
         if($user && $user->role!=4)
          $app->sale_price =$this->profitService->getSalePrice($app->price);
         else
           $app->sale_price =0;
        $app->price = $this->profitService->getPrice($app);    // إنشاء رابطe($app);
     }
       return response()->json(['services'=> $services ]);
    }

  
}
