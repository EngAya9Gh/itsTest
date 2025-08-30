<?php

namespace App\Http\Controllers;

use App\Models\EcardSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TweetcellSection; 
use App\Utils\ProfitCalculationService;

class ApiEcardSectionController extends Controller
{   
   protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
  public function index() 
    {
        $ecardSections=DB::table('tweetcell_sections')->where('type',3)->where('status',1)->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($ecardSections as $data) {
         $data->image_url = asset('assets/images/tweetcellSections/' . $data->image);  // إنشاء رابط للصورة
         
        }
       return response()->json(['ecardSections'=> $ecardSections]);
    }

 


    public function getEcards( $section_id)
    {
       $section = TweetcellSection::where('id',$section_id)->where('status',1)->first();
       $ecards = $section->tweetcells;
       foreach ($ecards as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
         $app->save();
    
         $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 

        }
       return response()->json(['ecards'=> $ecards]);
    }
}
   
