<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TweetcellSection;
use App\Models\Tweetcell;
use App\Utils\ProfitCalculationService;

class ApiAppSectionController extends Controller
{ 

 
  protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $appSections=DB::table('tweetcell_sections')->where('type',2)->where('status',1)->select('*')->orderBy('id', 'desc')->paginate(500);

       foreach ($appSections as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
     }
     
       return response()->json(['appSections'=> $appSections ]);
    }

    public function getApps(string $section_id)
    {
       $section= TweetcellSection::where('id',$section_id)->where('status',1)->first();
       $apps = $section->tweetcells;
       foreach ($apps as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $section->image);  // إنشاء رابط للصورة
            $app->save();
        $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 

     }
       return response()->json(['apps'=> $apps ]);
    }
}
