<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellSection;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
use Illuminate\Support\Facades\Auth;

class ApiTweetcellSectionController extends Controller
{   
  

  protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function getAppSections()
    {
       $appSections=DB::table('tweetcell_sections')->where('type',2)->where('status',1)->select('*')->orderBy('id', 'desc')->paginate(500);

       foreach ($appSections as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
     }
     
       return response()->json(['appSections'=> $appSections ]);
    }
    public function getGameSections()
    {
       $gameSections=DB::table('tweetcell_sections')->where('type',1)->where('status',1)->select('*')->orderBy('id', 'desc')->paginate(500);

       foreach ($gameSections as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
     }
     
       return response()->json(['gameSections'=> $gameSections ]);
    }
  
  public function getEcardSections()
    {
        $eCardSections=DB::table('tweetcell_sections')->where('type',3)->where('status',1)->select('*')->orderBy('id', 'desc')->paginate(500);

       foreach ($eCardSections as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
     }
  
       return response()->json(['ecardSections'=> $eCardSections ]);
    
    }

  
  
      public function getApps( $section_id)
    {
       $section= TweetcellSection::where('id',$section_id)->where('status',1)->first();

    // جلب تويتسيلات (Tweetcell) التي ترتبط بالقسم الذي تم العثور عليه
      $apps = $section->tweetcells()->where('status', 1)->get();  // إضافة شرط الحالة
       foreach ($apps as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/' . $section->image);  // إنشاء رابط للصورة
            $app->save();
         $user=Auth::user();
         if($user && $user->role!=4)
          $app->sale_price =$this->profitService->getSalePrice($app->price);
         else
           $app->sale_price =0;
        $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 

     }
       return response()->json(['apps'=> $apps ]);
    }
 
 

    

  

}




