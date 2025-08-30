<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceCategories;
use Illuminate\Support\Facades\DB;
use App\Models\Vip;
use App\Utils\ProfitCalculationService;

class ApiServiceController extends Controller
{protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    

    public function index()
    {
       $services=Service::where('status',1)->get();
       foreach ($services as $s) {
         $s->image_url = asset('assets/images/service/' . $s->image);  // إنشاء رابط للصورة
  
     }
       return response()->json(['services'=>$services ]);
    }
    public function show($id)
    {
       $service = Service::where('id',$id)->where('status',1)->first();
       $service->price=$this->profitService->getPrice($service);
   
       return response()->json(['service'=>$service ]);
    }
 
}
