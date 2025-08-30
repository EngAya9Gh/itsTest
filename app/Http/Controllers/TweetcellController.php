<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellSection;
use App\Models\Tweetcell;
use Illuminate\Support\Facades\DB;
  use App\Utils\ProfitCalculationService;

class TweetcellController extends Controller
{

    protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    
    public function update(Request $request, string $id)
    {
        $s = Tweetcell::findOrFail($id);
        $input = $request->all();
    
        $s->update( $input);
       
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function showGames($id)
    {   $sec=TweetcellSection::where('id',$id)->first();
        $services=DB::table('tweetcells')->select('*')->where('section_id',$id)->get();
   
        $sections=DB::table('tweetcell_sections')->select('*')->get();
       foreach ($services as $service) {
            $service->basic_price = $this->profitService->getPrice($service);
         $service->image = $sec->image;
        }
        return view('backend.tweetcell.tweetcells.index',compact('services','sections'));
    }
    public function changeStatus(string $id)
    {

        $myservice= Tweetcell::findOrFail($id);
       
        if($myservice->status)
         { $myservice->status=0;
           $myservice->save();
            return back()->with('message', 'تم الغاء تفعيل الخدمة  بنجاح');
         }
        else
         { $myservice->status=1;
            $myservice->save();
          return back()->with('message', 'تم تفعيل الخدمة  بنجاح');
         }
    }
   public function offStatus()
    {

        $services=DB::table('tweetcells')->select('*')->where('status',2)->get();
   
        $sections=DB::table('tweetcell_sections')->select('*')->get();
       foreach ($services as $service) {
            $service->basic_price = $this->profitService->getPrice($service);
     $service->image = TweetcellSection::where('id',$service->section_id)->first()->image;
        }
        return view('backend.tweetcell.tweetcells.index',compact('services','sections'));
    }
  
    public function destroy(string $id)
    {
        $game= Tweetcell::findOrFail($id);
        $game->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }

}
