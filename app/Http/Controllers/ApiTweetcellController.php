<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tweetcell;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;

class ApiTweetcellController extends Controller
{   protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
 

   public function show($id)
    {
       $app = Tweetcell::where('id',$id)->where('status',1)->first();
       $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 
       return response()->json(['app'=>$app ]);
    }

}
