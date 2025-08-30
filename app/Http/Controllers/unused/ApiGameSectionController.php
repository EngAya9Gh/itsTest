<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TweetcellSection;
use App\Models\Tweetcell;
use App\Utils\ProfitCalculationService;

class ApiGameSectionController extends Controller
{  protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    {
       $gameSections=DB::table('tweetcell_sections')->where('type',1)->where('status',1)->select('*')->orderBy('id', 'desc')->paginate(500);
       foreach ($gameSections as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/'.$app->image);  // إنشاء رابط للصورة
     
        }
       return response()->json(['gameSections'=> $gameSections ]);
    }

    public function getGames(string $section_id)
    {
       $section = TweetcellSection::where('id',$section_id)->where('status',1)->first();
       $games = $section->tweetcells;
       foreach ($games as $app) {
         $app->image_url = asset('assets/images/tweetcellSections/'.$section->image);  // إنشاء رابط للصورة
         $app->save();    
         $app->price = $this->profitService->getPrice($app);    // إنشاء رابط 

        }
       return response()->json(['games'=> $games ]);
    }
}
