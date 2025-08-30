<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TweetcellKontorSection;
use App\Jobs\UpdateKontorPackeges;

use Illuminate\Support\Facades\DB;
class ApiTweetcellKontorSectionController extends Controller
{ public function index() 
    {  
        $dataSections=DB::table('tweetcell_kontor_sections')->where('id',4)->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($dataSections as $data) {
         $data->image_url = asset('assets/images/tweetcellKontorSections/' . $data->image);  // إنشاء رابط للصورة
         
        }
       return response()->json(['dataSections'=> $dataSections]);
    }

    public function getData(string $section_id)
    {
       $section = TweetcellKontorSection::find($section_id);
       $dataCommunication = $section->tweetcellKontors;
       foreach ($dataCommunication as $app) {
         $app->image_url = asset('assets/images/tweetcellKontorSections/' . $app->image);  // إنشاء رابط للصورة
         $app->save();
        }
       return response()->json(['data-communications'=> $dataCommunication ]);
    }


    public function indexKontor() 
    { //dispatch(new UpdateKontorPackeges());

        $dataSections=DB::table('tweetcell_kontor_sections')->where('id','!=',4)->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($dataSections as $data) {
         $data->image_url = asset('assets/images/tweetcellKontorSections/' . $data->image);  // إنشاء رابط للصورة
         
        }
       return response()->json(['dataSections'=> $dataSections]);
    }

   
}
